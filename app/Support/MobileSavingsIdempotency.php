<?php

namespace App\Support;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class MobileSavingsIdempotency
{
    // Deliberately below both DECIMAL(15,2)'s integer bound and JS's 2^53-1.
    const MAX_NOMINAL = '999999999999';

    /**
     * The callback receives an authenticated, normalized request and returns the
     * existing API JsonResponse (success must contain data.id). It must perform
     * validation AND creation here, on the default DB connection, not before run.
     * Never send notifications or perform nontransactional side effects here.
     * Missing headers deliberately preserve the old application's behavior.
     */
    public static function run(Request $request, string $jenis, callable $create)
    {
        if (!$request->headers->has('Idempotency-Key')) {
            return $create($request);
        }
        $input = self::validatedInput($request, $jenis, 'add');
        if ($input instanceof JsonResponse) {
            return $input;
        }
        [$key, $nominal, $description, $fingerprint] = $input;

        try {
            return DB::transaction(function () use ($request, $jenis, $create, $key, $nominal, $description, $fingerprint) {
                // Same lock used by login/logout: recheck bearer and expiry after
                // any wait, and never trust client-supplied no_anggota.
                $member = MobileSessionGuard::lockedMember($request);
                if (!$member) {
                    return ApiResponse::error('Sesi tidak valid atau telah berakhir', 401);
                }
                // Member lock serializes first use of an absent request key too.
                // This lookup MUST precede the callback's saldo validation.
                $prior = DB::table('mobile_savings_requests')
                    ->where('member_id', $member->id)->where('request_key', $key)
                    ->lockForUpdate()->first();
                if ($prior) {
                    if (!hash_equals($prior->fingerprint, $fingerprint)) {
                        return ApiResponse::error('Idempotency-Key telah digunakan untuk permintaan berbeda', 409);
                    }
                    return self::priorResponse($prior, $member->no_anggota, false);
                }

                $normalized = clone $request;
                $normalized->merge(['no_anggota' => $member->no_anggota, 'nominal' => $nominal, 'keterangan' => $description]);
                $response = $create($normalized);
                if (!$response instanceof JsonResponse) {
                    throw new \LogicException('Savings callback must return an API JsonResponse.');
                }
                $body = $response->getData(true);
                if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300
                    || !is_array($body) || ($body['success'] ?? null) !== true) {
                    // Roll back even if a failing callback already wrote rows.
                    throw new HttpResponseException($response);
                }
                $id = $body['data']['id'] ?? null;
                if ((!is_int($id) && !is_string($id)) || !ctype_digit((string) $id)
                    || !DB::table('transaksi')->where('id', $id)->where('fid_anggota', $member->no_anggota)->exists()) {
                    throw new HttpResponseException(ApiResponse::error('Referensi transaksi tidak tersedia', 409));
                }
                DB::table('mobile_savings_requests')->insert([
                    'member_id' => $member->id, 'request_key' => $key,
                    'jenis' => $jenis, 'nominal' => $nominal, 'keterangan' => $description,
                    'fingerprint' => $fingerprint, 'transaction_id' => $id, 'state' => 'completed',
                    'created_at' => now(), 'updated_at' => now(),
                ]);
                return $response;
            });
        } catch (HttpResponseException $exception) {
            return $exception->getResponse();
        }
    }

    /**
     * Explicit POST-only reconciliation/abandonment of an immutable pending draft.
     * The caller must send action=abandon plus the ORIGINAL key and payload. This
     * is a mutation, not an "absent" lookup: the same member lock used by run()
     * ensures a delayed create either wins first and returns its ID here, or sees
     * the committed tombstone and cannot create. Only state=abandoned authorizes
     * discarding the local draft; all errors must keep it for later resolution.
     */
    public static function reconcile(Request $request, string $jenis)
    {
        if (!$request->isMethod('POST')) {
            return ApiResponse::error('Rekonsiliasi harus menggunakan POST', 405);
        }
        $input = self::validatedInput($request, $jenis, 'abandon');
        if ($input instanceof JsonResponse) {
            return $input;
        }
        [$key, $nominal, $description, $fingerprint] = $input;

        return DB::transaction(function () use ($request, $jenis, $key, $nominal, $description, $fingerprint) {
            $member = MobileSessionGuard::lockedMember($request);
            if (!$member) {
                return ApiResponse::error('Sesi tidak valid atau telah berakhir', 401);
            }
            $prior = DB::table('mobile_savings_requests')
                ->where('member_id', $member->id)->where('request_key', $key)
                ->lockForUpdate()->first();
            if ($prior) {
                if (!hash_equals($prior->fingerprint, $fingerprint)) {
                    return ApiResponse::error('Idempotency-Key telah digunakan untuk permintaan berbeda', 409);
                }
                return self::priorResponse($prior, $member->no_anggota, true);
            }

            DB::table('mobile_savings_requests')->insert([
                'member_id' => $member->id, 'request_key' => $key,
                'jenis' => $jenis, 'nominal' => $nominal, 'keterangan' => $description,
                'fingerprint' => $fingerprint, 'transaction_id' => null, 'state' => 'abandoned',
                'created_at' => now(), 'updated_at' => now(),
            ]);
            return ApiResponse::success(['state' => 'abandoned', 'id' => null, 'jenis' => $jenis]);
        });
    }

    private static function priorResponse($prior, string $memberNumber, bool $reconcile): JsonResponse
    {
        if ($prior->state === 'abandoned' && $prior->transaction_id === null) {
            return $reconcile
                ? ApiResponse::success(['state' => 'abandoned', 'id' => null, 'jenis' => $prior->jenis])
                : ApiResponse::error('Permintaan telah ditinggalkan; gunakan Idempotency-Key baru', 409);
        }
        // An unknown/inconsistent ledger state must never authorize discard or
        // recreation. Do not silently repair it by converting it to a tombstone.
        if ($prior->state !== 'completed' || $prior->transaction_id === null) {
            return ApiResponse::error('Referensi permintaan tidak valid; permintaan tidak dibuat ulang', 409);
        }
        $transaction = DB::table('transaksi')->where('id', $prior->transaction_id)
            ->where('fid_anggota', $memberNumber)->lockForUpdate()->first();
        if (!$transaction) {
            return ApiResponse::error('Referensi transaksi tidak tersedia; permintaan tidak dibuat ulang', 409);
        }
        // Preserve the established successful replay response exactly. Only the
        // explicit reconcile endpoint adds state=existing; it never cancels a tx.
        $data = [
            'id' => (int) $transaction->id,
            'fid_status' => (int) $transaction->fid_status,
            'fid_jenis_transaksi' => (int) $transaction->fid_jenis_transaksi,
            'jenis' => [3 => 'setoran', 6 => 'penarikan'][(int) $transaction->fid_jenis_transaksi] ?? null,
        ];
        if ($reconcile) {
            $data['state'] = 'existing';
        }
        return ApiResponse::success($data);
    }

    /** Shared canonicalization: reconciliation must match creation byte-for-byte. */
    private static function validatedInput(Request $request, string $jenis, string $action)
    {
        if (!in_array($jenis, ['setoran', 'penarikan'], true) || $request->input('action') !== $action) {
            return ApiResponse::error('Jenis atau action permintaan simpanan tidak valid', 422);
        }
        $key = $request->header('Idempotency-Key');
        if (!is_string($key) || !preg_match('/\A[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}\z/i', $key)) {
            return ApiResponse::error('Idempotency-Key harus UUID yang valid', 422);
        }
        $nominal = self::normalizeNominal($request->input('nominal'));
        $description = $request->input('keterangan');
        if ($nominal === null || ($description !== null && !is_string($description))) {
            return ApiResponse::error('Nominal harus rupiah bulat positif dan keterangan harus teks', 422);
        }
        $description = $description === null ? '' : $description;
        if (mb_strlen($description, 'UTF-8') > 2400) {
            return ApiResponse::error('Keterangan maksimal 2400 karakter', 422);
        }
        // Serialize strings, not floats; preserve description whitespace/content.
        $payload = json_encode([$jenis, $nominal, $description], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($payload === false) {
            return ApiResponse::error('Keterangan tidak valid', 422);
        }
        return [strtolower($key), $nominal, $description, hash('sha256', $payload)];
    }

    /** Accept unformatted digits or Indonesian thousands groups; never floats. */
    private static function normalizeNominal($value): ?string
    {
        if (!is_string($value) && !is_int($value)) {
            return null;
        }
        $value = (string) $value;
        if (!preg_match('/\A(?:[0-9]+|[1-9][0-9]{0,2}(?:\.[0-9]{3})+)\z/', $value)) {
            return null;
        }
        $value = ltrim(str_replace('.', '', $value), '0');
        if ($value === '' || strlen($value) > strlen(self::MAX_NOMINAL)
            || (strlen($value) === strlen(self::MAX_NOMINAL) && strcmp($value, self::MAX_NOMINAL) > 0)) {
            return null;
        }
        return $value;
    }
}
