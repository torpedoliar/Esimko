<?php

namespace App\Support;

use App\Anggota;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use LogicException;

final class MobileSessionGuard
{
    /**
     * Re-read the bearer owner under the member lock. Call only inside the same
     * transaction as the protected mutation; middleware authentication is stale
     * once another login/logout has been allowed to run.
     */
    public static function lockedMember(Request $request): ?Anggota
    {
        self::requireTransaction();
        $token = $request->bearerToken();
        if (!is_string($token) || $token === '') {
            return null;
        }

        // Resolve identity without trusting no_anggota supplied by the client.
        // Lock by primary key, then compare again after any lock wait.
        $id = Anggota::where('token', $token)->value('id');
        if ($id === null) {
            return null;
        }
        $member = Anggota::whereKey($id)->lockForUpdate()->first();
        if (!$member || !is_string($member->token) || !hash_equals($member->token, $token)) {
            return null;
        }

        // Preserve the middleware's legacy null-login_at behavior, but reject
        // malformed dates and all sessions older than its 30-day cutoff.
        if (!empty($member->login_at)) {
            $loginAt = strtotime($member->login_at);
            if ($loginAt === false || $loginAt < now()->subDays(30)->getTimestamp()) {
                return null;
            }
        }

        return $member;
    }

    /**
     * Serialize device claims before locking the caller member. device_tokens
     * has no unique token constraint, so locking only an existing device row
     * cannot protect a token's first registration by two different members.
     * Use the lowest member row as a short database-local registration mutex.
     * All registration writers must use this order: mutex, member, device rows.
     */
    public static function lockDeviceRegistration(): void
    {
        self::requireTransaction();
        Anggota::orderBy('id')->lockForUpdate()->first();
    }

    private static function requireTransaction(): void
    {
        if (DB::connection()->transactionLevel() < 1) {
            throw new LogicException('MobileSessionGuard requires an active database transaction.');
        }
    }
}
