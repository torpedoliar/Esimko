<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use App\Helpers\GlobalHelper;
use App\Helpers\MobileHelper;
use App\Support\ApiResponse;
use App\Anggota;
use App\Transaksi;
use App\Angsuran;
use App\GajiPokok;
use App\Produk;
use App\FotoProduk;
use App\KeranjangBelanja;
use App\Penjualan;
use App\ItemPenjualan;
use App\AngsuranBelanja;
use App\VerifikasiTransaksi;
use App\ItemReturPenjualan;
use App\Berita;
use App\AttachmentBerita;
use View;
use DB;
use DateTime;
use Redirect;
use Illuminate\Support\Str;
use Illuminate\Contracts\Encryption\DecryptException;


class MobileController extends Controller
{
  public function login(Request $request)
  {
    if (str_contains($request->input('username'), ' ')) {
      $no_anggota = $request->input('username');
    } else {
      $no_anggota = GlobalHelper::change_format_nomor($request->input('username'));
    }

    $anggota = Anggota::select('anggota.*', 'user_akses.fid_hak_akses as hak_akses')
      ->join('user_akses', 'user_akses.fid_anggota', '=', 'anggota.id')
      ->where('no_anggota', '=', $no_anggota)
      ->whereIn('fid_status', array('2', '3', '5'))
      ->first();
    if (!empty($anggota)) {
      $password_ok = Hash::check($request->password, $anggota->password);
      if (!$password_ok) {
        // Legacy plaintext-decrypt passwords: verify and re-hash on success
        try {
          $password_ok = ($request->password == decrypt($anggota->password));
          if ($password_ok) {
            $anggota->password = Hash::make($request->password);
            $anggota->save();
          }
        } catch (DecryptException $e) {
          $password_ok = false;
        }
      }
      if ($password_ok) {
        $token = Str::random(32);
        $anggota->token = $token;
        $anggota->login_at = date('Y-m-d H:i:s');
        $anggota->save();
        $return = array(
          'token' => $anggota->token,
          'no_anggota' => $anggota->no_anggota,
          'nama' => $anggota->nama_lengkap,
          'avatar' => (!empty($anggota->avatar) ? asset('storage/' . $anggota->avatar) : asset('assets/images/user-avatar-placeholder.png'))
        );
        return ApiResponse::success($return);
      } else {
        return ApiResponse::error('Password yang anda masukkan salah', 401);
      }
    } else {
      return ApiResponse::error('Anggota tidak Ditemukan', 404);
    }
  }

  public function logout(Request $request)
  {
    $anggota = Anggota::where('no_anggota', $request->no_anggota)->first();
    if (!empty($anggota)) {
      $anggota->token = null;
      $anggota->save();
    }
    return ApiResponse::success(null, 'Logout berhasil');
  }

  public function register(Request $request)
  {
    foreach (['nama_lengkap', 'no_ktp', 'no_handphone'] as $req) {
      if (empty($request->$req)) {
        return ApiResponse::error('Field ' . $req . ' wajib diisi', 422);
      }
    }
    if (!empty($request->password) && $request->password != $request->ulangi_password) {
      return ApiResponse::error('Password dan konfirmasi tidak sama', 422);
    }
    $field = new Anggota;
    $field->created_at = date('Y-m-d H:i:s');
    $field->no_anggota = GlobalHelper::get_nomor_anggota($request->lokasi_kerja);
    $field->nama_lengkap = $request->nama_lengkap;
    $field->password = Hash::make(!empty($request->password) ? $request->password : Str::random(8));
    $field->tempat_lahir = $request->tempat_lahir;
    $field->tanggal_lahir = GlobalHelper::dateFormat($request->tanggal_lahir, 'Y-m-d');
    $field->jenis_kelamin = $request->jenis_kelamin;
    $field->no_handphone = $request->no_handphone;
    $field->email = $request->email;
    $field->alamat = $request->alamat;
    $field->no_ktp = $request->no_ktp;
    $field->no_hirs = $request->no_hirs;
    $field->id_karyawan = $request->id_karyawan;
    $field->level = $request->level;
    $field->bagian = $request->bagian;
    $field->divisi = $request->divisi;
    $field->lokasi = $request->lokasi_kerja;
    $field->no_rekening = $request->no_rekening;
    $field->nama_bank = $request->nama_bank;
    $field->fid_status = 1;
    $field->tanggal_bekerja = (!empty($request->id_karyawan) ? GlobalHelper::bulan_bekerja($request->id_karyawan) : date('Y-m-d'));
    $field->tanggal_bergabung = date('Y-m-d');
    $field->save();
    $profil = Anggota::where('id', $field->id)
      ->select('id', 'no_anggota', 'nama_lengkap', 'no_ktp', 'avatar', 'no_handphone', 'email')
      ->first();
    return ApiResponse::success($profil);
  }

  public function profil_anggota(Request $request)
  {
    $anggota = Anggota::select('anggota.*', 'status_anggota.status_anggota', 'status_anggota.color')
      ->join('status_anggota', 'status_anggota.id', '=', 'anggota.fid_status')
      ->where('no_anggota', $request->no_anggota)
      ->first();
    if (!empty($anggota)) {

      $tab = (!empty($request->tab) ? $request->tab : 'profil');
      $anggota->avatar = (!empty($anggota->avatar) ? asset('storage/' . $anggota->avatar) : asset('assets/images/user-avatar-placeholder.png'));

      $anggota->total_saldo_simpanan = MobileHelper::saldoTabungan($anggota->no_anggota, 'Total Simpanan'); //Total Simpanan
      $anggota->saldo_simpanan_pokok = MobileHelper::saldoTabungan($anggota->no_anggota, 1); //Simpanan Pokok
      $anggota->saldo_simpanan_wajib = MobileHelper::saldoTabungan($anggota->no_anggota, 2); //Simpanan Wajib
      $anggota->saldo_simpanan_sukarela = MobileHelper::saldoTabungan($anggota->no_anggota, 'Simpanan Sukarela'); //Simpanan Hari Raya
      $anggota->saldo_simpanan_hari_raya = MobileHelper::saldoTabungan($anggota->no_anggota, 'Simpanan Hari Raya'); //Simpanan Sukarela


      $anggota->bunga_pinjaman = intval($anggota->sisa_pinjaman * GlobalHelper::getBungaPinjaman()); //Bunga Semua Pinjaman

      $anggota->angsuran_jangka_panjang = GlobalHelper::sisa_pinjaman($anggota->no_anggota, 9); //Sisa Jangka Panjang
      $anggota->angsuran_jangka_pendek = GlobalHelper::sisa_pinjaman($anggota->no_anggota, 10); //Sisa Jangka Pendek
      $anggota->angsuran_barang = GlobalHelper::sisa_pinjaman($anggota->no_anggota, 11); //Sisa Barangx

        $anggota->sisa_pinjaman = $anggota->angsuran_jangka_panjang + $anggota->angsuran_jangka_pendek + $anggota->angsuran_barang;
        $anggota->total_angsuran_pinjaman = MobileHelper::angsuranPinjamanSafe($anggota->no_anggota, 'all'); //Total Angsuran Pinjaman

      $anggota->total_angsuran_belanja = GlobalHelper::total_angsuran_belanja($anggota->no_anggota); //Total Angsuran Belanja
      $anggota->angsuran_belanja_toko = GlobalHelper::angsuran_belanja($anggota->no_anggota, 'toko'); //Total Angsuran Belanja Toko
      $anggota->angsuran_belanja_konsinyasi = GlobalHelper::angsuran_belanja($anggota->no_anggota, 'konsinyasi'); //Total Angsuran Belanja konsinyasi
      $anggota->angsuran_belanja_online = GlobalHelper::angsuran_belanja($anggota->no_anggota, 'online'); //Total Angsuran Belanja Online

      $anggota->setoran_berkala = GlobalHelper::setoran_berkala($anggota->no_anggota); //Setoran Berkala
      $anggota->setoran_simpanan_anggota = 350000;

      unset($anggota->password, $anggota->token); // jangan ekspos secret ke client

      return ApiResponse::success($anggota);
    } else {
      return ApiResponse::error('Anggota tidak Ditemukan', 404);
    }
  }

  public function ubah_password(Request $request)
  {
    $anggota = Anggota::where('no_anggota', $request->no_anggota)->first();
    if (!empty($anggota)) {
      $password_lama_ok = Hash::check($request->password_lama, $anggota->password);
      if (!$password_lama_ok) {
        try {
          $password_lama_ok = ($request->password_lama == decrypt($anggota->password));
        } catch (DecryptException $e) {
          $password_lama_ok = false;
        }
      }
      if ($password_lama_ok) {
        if ($request->password_baru == $request->ulangi_password_baru) {
          $field = Anggota::find($anggota->id);
          $field->password = Hash::make($request->password_baru);
          $field->save();
          $msg = 'Password baru berhasil diubah';
        } else {
          $msg = 'Password baru tidak sama';
        }
      } else {
        $msg = 'Password lama salah';
      }
    } else {
      $msg = 'Anggota tidak ditemukan';
    }
    return $msg === 'Password baru berhasil diubah' ? ApiResponse::success(null, $msg) : ApiResponse::error($msg);
  }

  public function jenis_transaksi($modul)
  {
    if ($modul == 'simpanan') {
      $data = DB::table('jenis_transaksi')->whereIn('id', array(1, 2, 3, 4, 5, 6, 7, 8))->get();
    } else {
      $data = DB::table('jenis_transaksi')->whereIn('id', array(9, 10, 11))->get();
    }
    return ApiResponse::success($data);
  }

  public function status_transaksi($modul)
  {
    if ($modul == 'simpanan') {
      $data = DB::table('status_transaksi')->where('id', '<>', 6)->get();
    } else {
      $data = DB::table('status_transaksi')->get();
    }
    return ApiResponse::success($data);
  }

  public function transaksi(Request $request, $modul)
  {

    $jenis = (!empty($request->jenis) ? $request->jenis : 'all');
    $status = (!empty($request->status) ? $request->status : 'all');

    $query = Transaksi::select(
      'transaksi.*',
      'jenis_transaksi.jenis_transaksi',
      'jenis_transaksi.operasi',
      'metode_pembayaran.metode_pembayaran',
      'status_transaksi.status',
      'status_transaksi.color'
    )
      ->join('anggota', 'anggota.no_anggota', '=', 'transaksi.fid_anggota')
      ->Join('status_transaksi', 'status_transaksi.id', '=', 'transaksi.fid_status')
      ->join('jenis_transaksi', 'jenis_transaksi.id', '=', 'transaksi.fid_jenis_transaksi')
      ->join('metode_pembayaran', 'metode_pembayaran.id', '=', 'transaksi.fid_metode_transaksi')
      ->where('transaksi.fid_anggota', $request->no_anggota);

    if ($modul == 'simpanan') {
      $query = $query->whereIn('transaksi.fid_jenis_transaksi', array(1, 2, 3, 4, 5, 6, 7, 8));
    } elseif ($modul == 'pinjaman') {

      $query = $query->whereIn('transaksi.fid_jenis_transaksi', array(9, 10, 11));
    }


    if ($jenis != 'all') {
      $query = $query->where('transaksi.fid_jenis_transaksi', $jenis);
    }

    if ($status != 'all') {
      $query = $query->where('transaksi.fid_status', $status);
    } else {
      $query = $query->where('transaksi.fid_status', '!=', 5);
    }


    if (!empty($request->tanggal_mulai) && !empty($request->tanggal_akhir)) {
      $query = $query->whereBetween('transaksi.tanggal', [GlobalHelper::dateFormat($request->tanggal_mulai, 'Y-m-d'), GlobalHelper::dateFormat($request->tanggal_akhir, 'Y-m-d')]);
    }

    $limit = $request->input('per_page', 20);
    $paginated = $request->has('page');
    if ($paginated) {
        $p = $query->orderBy('transaksi.tanggal', 'DESC')->orderBy('transaksi.created_at', 'DESC')->paginate($limit);
        $items = $p->items();
    } else {
        // Tanpa page = ambil semua (riwayat lengkap); pagination hanya saat request page
        $items = $query->orderBy('transaksi.tanggal', 'DESC')->orderBy('transaksi.created_at', 'DESC')->get();
    }

    // Manual eager load for angsuran is a bit complex, let's keep it N+1 or fix it
    // Transaksi query is already optimized? Angsuran lookup:
    $transaksi_ids = collect($items)->pluck('id')->toArray();
    $angsurans = Angsuran::whereIn('fid_transaksi', $transaksi_ids)->get()->groupBy('fid_transaksi');

    foreach ($items as $key => $value) {
      $nominal = str_replace('-', '', $value->nominal);
      if ($modul == 'pinjaman') {
        $trans_angsurans = $angsurans->get($value->id, collect());
        $angsuran = $trans_angsurans->first();
        if (!empty($angsuran)) {
          $value->total_angsuran = $angsuran->angsuran_pokok + $angsuran->angsuran_bunga;
          $sisa_pinjaman = $trans_angsurans->where('fid_status', '!=', 6)->first();
          $value->sisa_pinjaman = (!empty($sisa_pinjaman) ? $sisa_pinjaman->sisa_hutang  : 0);
          $value->sisa_tenor = $trans_angsurans->where('fid_status', '!=', 6)->count();
          $value->nominal_tampil = 'Rp ' . number_format($nominal, 0, ',', '.');
        }
      } else {
        $penarikan = array(6, 7, 8);
        $convert = (int) $nominal;
        if (in_array($value->fid_jenis_transaksi, $penarikan)) {
          $value->nominal_tampil = '-Rp ' . number_format($convert, 0, ',', '.');
        } else {
          $value->nominal_tampil = '+Rp ' . number_format($convert, 0, ',', '.');
        }
      }
    }
    return $paginated ? ApiResponse::success($items, 'OK', ['page'=>$p->currentPage(),'per_page'=>intval($p->perPage()),'total'=>$p->total(),'last_page'=>$p->lastPage()]) : ApiResponse::success($items);
  }

  public function upload_bukti_transaksi(Request $request)
  {
    $field = Transaksi::find($request->id);
    if (!empty($field)) {
      if ($request->hasFile('bukti_transaksi')) {
        if (!empty($field->bukti_transaksi)) {
          if (file_exists(storage_path('app/public/' . $field->bukti_transaksi))) { unlink(storage_path('app/public/' . $field->bukti_transaksi)); }
        }
        $uploadedFile = $request->file('bukti_transaksi');
        $path = $uploadedFile->store('bukti_transaksi', 'public');
        $field->bukti_transaksi = $path;
        $field->save();
        return ApiResponse::success(null, 'success');
      } else {
        return ApiResponse::error('file tidak ditemukan');
      }
    } else {
      return ApiResponse::error('transaksi tidak ditemukan', 404);
    }
  }

  public static function add_riwayat_transaksi($jenis, $id, $caption, $anggota)
  {
    $field = new VerifikasiTransaksi;
    $field->fid_transaksi = $id;
    $field->jenis = $jenis;
    $field->caption = $caption;
    $field->keterangan = null;
    $field->created_at = date('Y-m-d H:i:s');
    $field->created_by = $anggota;
    $field->save();
  }

  public function batalkan_transaksi(Request $request)
  {
    $field = Transaksi::find($request->id);
    if (!empty($field)) {
      $field->fid_status = 5;
      $field->save();
      $this->add_riwayat_transaksi('transaksi', $field->id, 'Transaksi dibatalkan oleh', $request->no_anggota);
      return ApiResponse::success(null, 'success');
    } else {
      return ApiResponse::error('not found', 404);
    }
  }

  public function detail_transaksi(Request $request, $modul)
  {
    $data = Transaksi::select(
      'transaksi.*',
      'anggota.no_anggota',
      'jenis_transaksi.jenis_transaksi',
      'jenis_transaksi.group as group_transaksi',
      'anggota.nama_lengkap',
      // 'metode_transaksi.metode_transaksi',
      'metode_pembayaran.metode_pembayaran',
      'anggota.avatar',
      'status_transaksi.status',
      'status_transaksi.color',
      'status_transaksi.icon'
    )
      ->join('anggota', 'anggota.no_anggota', '=', 'transaksi.fid_anggota')
      ->leftJoin('status_transaksi', 'status_transaksi.id', '=', 'transaksi.fid_status')
      // ->join('metode_transaksi', 'metode_transaksi.id', '=', 'transaksi.fid_metode_transaksi')
      ->join('metode_pembayaran', 'metode_pembayaran.id', '=', 'transaksi.fid_metode_transaksi')
      ->join('jenis_transaksi', 'jenis_transaksi.id', '=', 'transaksi.fid_jenis_transaksi')
      ->where('transaksi.id', $request->id)
      ->first();

    if (!empty($data)) {
      if (in_array($data->fid_jenis_transaksi, array(1, 2, 3, 4))) {
        $jenis = 'simpanan';
      } elseif (in_array($data->fid_jenis_transaksi, array(6, 7, 8))) {
        $jenis = 'penarikan';
      } elseif (in_array($data->fid_jenis_transaksi, array(9, 10, 11))) {
        $jenis = 'pinjaman';
      } else {
        $jenis = null;
      }

      $data->bukti_transaksi = ($data->bukti_transaksi != null ? asset('storage/' . $data->bukti_transaksi) : null);

      $keterangan = DB::table('keterangan_status_transaksi')
        ->where('jenis_transaksi', $jenis)
        ->where('fid_status', $data->fid_status)
        ->where('user_page', 'main')
        ->first();
      $data->status_label = (!empty($keterangan) ? $keterangan->label : null);
      $data->status_keterangan = (!empty($keterangan) ? $keterangan->label : null);

      $anggota = Anggota::where('no_anggota', $data->created_by)->first();
      $data->nama_petugas = (!empty($anggota) ? $anggota->nama_lengkap : 'Tidak Diketahui');
      return ApiResponse::success($data);
    } else {
      return ApiResponse::error('Data tidak Ditemukan', 404);
    }
  }

  public function validasi_transaksi($request, $jenis)
  {
    if ($jenis == 'setoran') {
      $msg = 'success';
    } elseif ($jenis == 'penarikan') {
      $saldo = MobileHelper::saldoTabungan($request->no_anggota, 'Simpanan Sukarela');
      if (str_replace('.', '', $request->nominal) > $saldo) {
        $msg = 'Saldo simpanan tidak mencukupi';
      } else {
        $msg = 'success';
      }
    } elseif ($jenis == 'pinjaman') {
      $tenor = array(9 => 50, 10 => 18, 11 => 18);
      if (!array_key_exists($request->jenis_pinjaman, $tenor)) {
        $msg = 'Jenis pinjaman tidak valid';
      } elseif ($request->tenor > $tenor[$request->jenis_pinjaman]) {
        $msg = 'Tenor melebihi maksimal yaitu ' . $tenor[$request->jenis_pinjaman] . ' bulan';
      } else {
        // Angsuran baru = pokok (nominal/tenor) + bunga (bunga% x nominal), sama seperti proses_angsuran
        $nominal = str_replace('.', '', $request->nominal ?? 0);
        $tenor = (int)$request->tenor;
        $angsuran_baru = ($tenor > 0) ? ROUND($nominal / $tenor) + ROUND(GlobalHelper::getBungaPinjaman() * $nominal) : 0;

        $angsuran_pinjaman = MobileHelper::angsuranPinjamanSafe($request->no_anggota, 'all');
        $angsuran_belanja = GlobalHelper::total_angsuran_belanja($request->no_anggota);
        $angsuran_simpanan = GlobalHelper::setoran_berkala($request->no_anggota) + 350000;
        $total_angsuran = $angsuran_pinjaman + $angsuran_belanja + $angsuran_simpanan + $angsuran_baru;

        // ponytail: total_angsuran_pinjaman semula = angsuran lama + angsuran baru (50% gaji rule)
        $total_angsuran_pinjaman = $angsuran_pinjaman + $angsuran_baru;

        $sisa_tenor = GlobalHelper::sisa_tenor_pinjaman($request->no_anggota, $request->jenis_pinjaman)['sisa'];
        $sisa_pinjaman = GlobalHelper::sisa_pinjaman($request->no_anggota, $request->jenis_pinjaman);

        // Gaji kelayakan: draft dari app (user isi + slip, diverifikasi admin sebelum approve)
        // kalau ada, fallback ke gaji resmi DB bulan lalu. Gaji draft TIDAK ditulis ke tabel
        // gaji_pokok — hanya dijadikan acuan aturan 50%, diresmikan saat admin approve.
        $gaji_draft = (float) str_replace('.', '', $request->gaji_pokok ?? 0);
        $gaji_db = GlobalHelper::gaji_pokok($request->no_anggota)[1];
        $gaji_pokok = ($gaji_draft > 0) ? $gaji_draft : $gaji_db;

        if ($sisa_tenor == 0) {
          if ($total_angsuran <= $gaji_pokok) {
            // return $total_angsuran_pinjaman;
            if ($total_angsuran_pinjaman > $gaji_pokok / 2) {

              $msg = 'Maaf anda belum bisa mengajukan pinjaman dengan total angsuran perbulan <b>Rp ' . $request->total_angsuran . '</b> karena melebihi 50% Gaji Pokok. Silahkan masukkan jumlah pinjaman dan tenor yang sesuai';
            } else {
              $msg = 'success';
            }
          } else {
            $msg = 'Maaf anda belum bisa mengajukan pinjaman dengan total angsuran perbulan Rp ' . $request->total_angsuran . ' karena total angsuran melebihi Gaji Pokok. Silahkan masukkan jumlah pinjaman dan tenor yang sesuai atau ubah kembali nominal setoran berkala';
          }
        } else {
          $msg = 'Maaf anda belum bisa mengajukan pinjaman ini, karena anda masih mempunyai sisa angsuran senilai <b>Rp ' . number_format($sisa_pinjaman, 0, ',', '.') . '</b> dan sisa tenor <b>' . $sisa_tenor . 'x </b>. Silahkan melunasi pinjaman anda atau melakukan pengajuan pinjaman yang lain.';
        }
      }
    } else {
      $msg = 'failed';
    }
    return $msg;
  }

  public function gaji_pokok(Request $request)
  {
    $data = GajiPokok::where('fid_anggota', $request->no_anggota)->get();
    $gaji_pokok = GlobalHelper::gaji_pokok($request->no_anggota);
    return ApiResponse::success(['list' => $data, 'bulan' => $gaji_pokok[0], 'gaji_pokok' => $gaji_pokok[1]]);
  }

  public function proses_transaksi(Request $request, $jenis)
  {
    $validasi = $this->validasi_transaksi($request, $jenis);
    if ($validasi == 'success') {
      if ($request->action == 'add') {
        $field = new Transaksi;
        $field->created_at = date('Y-m-d H:i:s');
        $field->created_by = $request->no_anggota;
        $field->fid_status = 1;
      } else {
        $field = Transaksi::find($request->id);
        if (empty($field)) {
          return ApiResponse::error('Data transaksi tidak ditemukan', 404);
        }
        $field->updated_at = date('Y-m-d H:i:s');
      }

      if ($request->action == 'delete') {
        $field->delete();
        return $validasi === 'success' ? ApiResponse::success(null, $validasi) : ApiResponse::error($validasi);
      } else {
        $field->fid_anggota = $request->no_anggota;
        $field->keterangan = $request->keterangan;
        $field->tanggal = date('Y-m-d');
        $field->fid_metode_transaksi = 1; // cash / tunai
        if ($jenis == 'setoran') {
          $field->fid_jenis_transaksi = 4;
          $field->nominal = str_replace('.', '', $request->nominal);
          $field->tenor = null;
        } elseif ($jenis == 'penarikan') {
          $field->fid_jenis_transaksi = 6;
          $field->nominal = '-' . str_replace('.', '', $request->nominal);
          $field->tenor = null;
        } elseif ($jenis == 'pinjaman') {
          $field->fid_jenis_transaksi = $request->jenis_pinjaman; // Sesuai jenis pinjaman yang dipilih
          $field->nominal = '-' . str_replace('.', '', $request->nominal);
          $field->tenor = $request->tenor;
        }
        $field->save();
        if ($jenis == 'pinjaman') {
          // Gaji + slip dari app disimpan sebagai DRAFT di keterangan (JSON).
          // Gaji resmi ditulis ke tabel gaji_pokok hanya saat admin approve (PinjamanController::verifikasi).
          $this->draft_gaji_pinjaman($field, $request);
          $this->proses_angsuran($field->id, $request);
        }
        return ApiResponse::success(['id' => $field->id]);
      }
    } else {
      return $validasi === 'success' ? ApiResponse::success(null, $validasi) : ApiResponse::error($validasi);
    }
  }

  public function sisa_hutang($id, $n)
  {
    $angsuran = Angsuran::where('angsuran_ke', $n - 1)->where('fid_transaksi', $id)->first();
    if (!empty($angsuran)) {
      $sisa_hutang = $angsuran->sisa_hutang - $angsuran->angsuran_pokok;
      return $sisa_hutang;
    } else {
      $pinjaman = Transaksi::find($id);
      return (!empty($pinjaman) ? str_replace('-', '', $pinjaman->nominal) : 0);
    }
  }

  public function proses_angsuran($id, $request)
  {
    Angsuran::where('fid_transaksi', $id)->delete();
    for ($n = 1; $n <= $request->tenor; $n++) {
      $field = new Angsuran;
      $field->angsuran_ke = $n;
      $field->fid_transaksi = $id;
      $field->bunga = GlobalHelper::getBungaPinjaman();
      $field->sisa_hutang = $this->sisa_hutang($id, $n);
      $field->angsuran_pokok = ROUND(str_replace('.', '', $request->nominal) / $request->tenor, 0);
      $field->angsuran_bunga = ROUND(GlobalHelper::getBungaPinjaman() * str_replace('.', '', $request->nominal));
      $field->fid_status = 2;
      $field->save();
    }
  }

  public function angsuran_pinjaman(Request $request)
  {
    $query = Angsuran::select('transaksi.id', 'jenis_transaksi.jenis_transaksi', 'payroll_angsuran.bulan', 'angsuran.angsuran_ke', 'angsuran.angsuran_pokok', 'angsuran.angsuran_bunga', 'status_angsuran.status_angsuran', 'status_angsuran.color')
      ->join('transaksi', 'transaksi.id', '=', 'angsuran.fid_transaksi')
      ->join('jenis_transaksi', 'jenis_transaksi.id', '=', 'transaksi.fid_jenis_transaksi')
      ->join('status_angsuran', 'status_angsuran.id', '=', 'angsuran.fid_status')
      ->leftJoin('payroll_angsuran', 'payroll_angsuran.id', '=', 'angsuran.fid_payroll');
    if (!empty($request->id)) {
      $query = $query->where('transaksi.id', $request->id);
    } else {
      $query = $query->where('transaksi.fid_anggota', $request->no_anggota)->whereIn('angsuran.fid_status', array(5, 6));
    }
    // $p = $query->paginate($request->input('per_page', 20)); $data = $p->items();
    $data = $query->get();
    foreach ($data as $key => $value) {
      $data[$key]->nama_bulan = (!empty($request->id) ? null : GlobalHelper::nama_bulan($value->bulan));
    }
    return ApiResponse::success($data);
  }

  // Draft gaji + slip untuk pinjaman baru (Opsi 1): disimpan di transaksi.keterangan
  // sebagai JSON, belum menyentuh tabel gaji_pokok. Commit resmi terjadi saat
  // admin menyetujui pinjaman (PinjamanController::verifikasi → status 3).
  public function draft_gaji_pinjaman($transaksi, $request)
  {
    $draft = ['gaji' => (float) str_replace('.', '', $request->gaji_pokok ?? 0)];
    if (!empty($transaksi->keterangan)) {
      $keterangan = json_decode($transaksi->keterangan, true);
      if (!empty($keterangan['slip'])) {
        $draft['slip'] = $keterangan['slip'];
      }
    }
    if ($request->hasFile('attachment')) {
      $draft['slip'] = $request->file('attachment')->store('slip_gaji', 'public');
    }
    $transaksi->keterangan = json_encode($draft);
    $transaksi->save();
  }

  public function upload_slip_gaji(Request $request)
  {
    $field = Transaksi::find($request->id);
    if (empty($field)) {
      return ApiResponse::error('transaksi tidak ditemukan', 404);
    }
    if (!$request->hasFile('attachment')) {
      return ApiResponse::error('file tidak ditemukan');
    }
    $keterangan = json_decode($field->keterangan, true);
    if (empty($keterangan)) {
      $keterangan = [];
    }
    if (!empty($keterangan['slip'])) {
      if (file_exists(storage_path('app/' . $keterangan['slip']))) { unlink(storage_path('app/' . $keterangan['slip'])); }
    }
    $keterangan['slip'] = $request->file('attachment')->store('slip_gaji', 'public');
    $field->keterangan = json_encode($keterangan);
    $field->save();
    return ApiResponse::success(null, 'success');
  }

    public function produk(Request $request)
  {
      $query = Produk::select('produk.*', 'satuan_barang.satuan')
          ->join('satuan_barang', 'satuan_barang.id', '=', 'produk.fid_satuan');
      if (!empty($request->search)) {
          $search = $request->search;
          $query = $query->where(function ($i) use ($search) {
              $i->where('produk.nama_produk', 'like', "%{$search}%")
                ->orWhere('produk.kode', 'like', "%{$search}%");
          });
      }

      $p = $query->orderBy('produk.nama_produk', 'DESC')->paginate($request->input('per_page', 20));
      $items = $p->items();
      $produk_ids = collect($items)->pluck('id')->toArray();
      $fotos = FotoProduk::whereIn('fid_produk', $produk_ids)->get()->keyBy('fid_produk');

      foreach ($items as $value) {
          $foto = $fotos->get($value->id);
          $value->foto = (!empty($foto) ? asset('storage/' . $foto->foto) : asset('assets/images/produk-default.jpg'));
          $kategori = explode('.', $value->kode_kategori);
          if ($kategori[0] == 0) {
              $value->kelompok = GlobalHelper::detail_kategori_produk($kategori[1]);
              $value->kategori = GlobalHelper::detail_kategori_produk($kategori[2]);
              $value->sub_kategori = '';
          } else {
              $value->kelompok = GlobalHelper::detail_kategori_produk($kategori[0]);
              $value->kategori = GlobalHelper::detail_kategori_produk($kategori[1]);
              $value->sub_kategori = GlobalHelper::detail_kategori_produk($kategori[2]);
          }
      }

      return ApiResponse::success($items, 'OK', [
          'page' => $p->currentPage(),
          'per_page' => intval($p->perPage()),
          'total' => $p->total(),
          'last_page' => $p->lastPage(),
      ]);
  }

  public function detail_produk(Request $request)
  {
    $data = Produk::select('produk.*', 'satuan_barang.satuan')
      ->join('satuan_barang', 'satuan_barang.id', '=', 'produk.fid_satuan')
      ->where('produk.kode', $request->id)
      ->first();
    if (!empty($data)) {
      $foto = FotoProduk::where('fid_produk', $data->id)->first();
      $data->foto = (!empty($foto) ? asset('storage/' . $foto->foto) : asset('assets/images/produk-default.jpg'));

      $stok = MobileHelper::stokBarang($data->id);
      $data->terjual = $stok['terjual'];
      $data->sisa = $stok['sisa'];

      $kategori = explode('.', $data->kode_kategori);
      if ($kategori[0] == 0) {
        $data->kelompok = GlobalHelper::detail_kategori_produk($kategori[1]);
        $data->kategori = GlobalHelper::detail_kategori_produk($kategori[2]);
        $data->sub_kategori = '';
      } else {
        $data->kelompok = GlobalHelper::detail_kategori_produk($kategori[0]);
        $data->kategori = GlobalHelper::detail_kategori_produk($kategori[1]);
        $data->sub_kategori = GlobalHelper::detail_kategori_produk($kategori[2]);
      }
      return ApiResponse::success($data);
    }
    return ApiResponse::error('Produk tidak ditemukan', 404);
  }

  public function keranjang(Request $request)
  {
    $query = KeranjangBelanja::select('keranjang_belanja.*', 'produk.nama_produk', 'produk.kode')
      ->join('produk', 'produk.id', 'keranjang_belanja.fid_produk')
      ->where('fid_anggota', $request->no_anggota);
    if (!empty($request->search)) {
      $search = $request->search;
      $query = $query->where(function ($i) use ($search) {
        $i->where('produk.nama_produk', 'like', "%{$search}%")
          ->orWhere('produk.kode', 'like', "%{$search}%");
      });
    }
    $result = $query->orderBy('produk.nama_produk')->get();
    foreach ($result as $key => $value) {
      $foto = FotoProduk::where('fid_produk', $value->fid_produk)->first();
      $value->foto = (!empty($foto) ? asset('storage/' . $foto->foto) : asset('assets/images/produk-default.jpg'));
      $barang = MobileHelper::stokBarang($value->fid_produk);
      $value->terjual = $barang['terjual'];
      $value->sisa = $barang['sisa'];
    }
    $total = KeranjangBelanja::where('fid_anggota', $request->no_anggota)->sum('total');
    return ApiResponse::success(['items' => $result, 'total' => intval($total)]);
  }

  public function proses_keranjang(Request $request)
  {
    $produk = Produk::find($request->id);
    if (!empty($produk)) {
      $stok = MobileHelper::stokBarang($produk->id);
      $cek_keranjang = KeranjangBelanja::where('fid_produk', $request->id)->where('fid_anggota', $request->no_anggota)->first();
      if (!empty($cek_keranjang)) {
        $field = KeranjangBelanja::find($cek_keranjang->id);
        $field->jumlah = $cek_keranjang->jumlah + $request->jumlah;
        $field->updated_at = date('Y-m-d H:i:s');
      } else {
        $field = new KeranjangBelanja;
        $field->created_at = date('Y-m-d H:i:s');
        $field->fid_anggota = $request->no_anggota;
        $field->fid_produk = $request->id;
        $field->jumlah = $request->jumlah;
      }
      if ($request->action == 'delete') {
        $field->delete();
        return ApiResponse::success(null, 'success');
      } else {
        $field->harga = $produk->harga_jual;
        $field->total = $field->jumlah * $field->harga;
        if ($field->jumlah <= $stok['sisa']) {
          $field->save();
          return ApiResponse::success(null, 'success');
        } else {
          return ApiResponse::error('Jumlah melebih stok');
        }
      }
    } else {
      return ApiResponse::error('Produk not Found', 404);
    }
  }

  public function add_penjualan($anggota)
  {
    $field = new Penjualan;
    $field->tanggal = date('Y-m-d');
    $field->created_at = date('Y-m-d H:i:s');
    $field->created_by = $anggota;
    $field->fid_anggota = $anggota;
    $field->no_transaksi = GlobalHelper::get_nomor_penjualan($field->created_at);
    $field->fid_status = 1;
    $field->jenis_belanja = 'toko';
    $field->fid_metode_pembayaran = 1;
    $field->save();
    return $field->id;
  }

  public function checkout_keranjang(Request $request)
  {
    if (empty($request->barang) || !is_array($request->barang) || count($request->barang) == 0) {
        return ApiResponse::error('Keranjang kosong');
    }

    $id = $this->add_penjualan($request->no_anggota);
    $total = 0;
    $failed_items = [];
    foreach ($request->barang as $key => $keranjang_id) {
      $keranjang = KeranjangBelanja::select('keranjang_belanja.*', 'produk.*')
        ->join('produk', 'produk.id', 'keranjang_belanja.fid_produk')
        ->where('keranjang_belanja.id', $keranjang_id)
        ->first();
      if (!empty($keranjang)) {
        $stok = MobileHelper::stokBarang($keranjang->fid_produk);
        $field = new ItemPenjualan;
        $field->fid_penjualan = $id;
        $field->fid_produk = $keranjang->fid_produk;
        $field->harga_beli = $keranjang->harga_beli;
        $field->margin = $keranjang->margin;
        $field->margin_nominal = $keranjang->margin_nominal;
        $field->harga = $keranjang->harga_jual;
        $field->jumlah = !empty($request->jumlah[$key]) ? $request->jumlah[$key] : $keranjang->jumlah;
        $field->total = $field->harga * $field->jumlah;
        if ($field->jumlah <= $stok['sisa']) {
          $field->save();
          $total = $total + $field->total;
        } else {
          $failed_items[] = ['fid_produk' => $keranjang->fid_produk, 'nama' => $keranjang->nama_produk];
        }
      }
    }
    if ($total == 0) {
      Penjualan::find($id)->delete();
    } else {
      $this->update_total_pembayaran($id, $total);
      $this->hapus_keranjang($id);
    }
    return ApiResponse::success(['failed_items' => $failed_items], count($failed_items) ? 'Sebagian item gagal (melebihi stok)' : 'Checkout berhasil');
  }

  public function update_total_pembayaran($id, $total)
  {
    $field = Penjualan::find($id);
    $field->total_pembayaran = $total;
    $field->save();
  }

  public function hapus_keranjang($id)
  {
    $penjualan = Penjualan::find($id);
    if (!empty($penjualan)) {
      $items = ItemPenjualan::where('fid_penjualan', $id)->get();
      foreach ($items as $key => $value) {
        KeranjangBelanja::where('fid_produk', $value->fid_produk)->where('fid_anggota', $penjualan->fid_anggota)->delete();
      }
    }
  }

  public function belanja(Request $request, $jenis = 'toko')
  {
    if ($jenis == 'toko') {
      $query = Penjualan::select('penjualan.*', 'status_belanja.status', 'status_belanja.color', 'metode_pembayaran.metode_pembayaran')
        ->join('status_belanja', 'status_belanja.id', '=', 'penjualan.fid_status');
    } else {
      $query = Penjualan::select('penjualan.*', 'status_transaksi.status', 'status_transaksi.color', 'metode_pembayaran.metode_pembayaran')
        ->join('status_transaksi', 'status_transaksi.id', '=', 'penjualan.fid_status');
    }
    $query = $query->join('metode_pembayaran', 'metode_pembayaran.id', '=', 'penjualan.fid_metode_pembayaran')
      ->where('jenis_belanja', $jenis)
      ->where('fid_anggota', '=', $request->no_anggota);

    $p = $query->orderBy('penjualan.created_at')->paginate($request->input('per_page', 20)); $result = $p->items();
    foreach ($result as $key => $value) {
      if ($jenis == 'toko') {
        $items = ItemPenjualan::select('item_penjualan.*', 'produk.nama_produk', 'produk.kode', 'satuan_barang.satuan')
          ->join('produk', 'produk.id', '=', 'item_penjualan.fid_produk')
          ->join('satuan_barang', 'satuan_barang.id', '=', 'produk.fid_satuan')
          ->where('fid_penjualan', $value->id)
          ->first();
        if (!empty($items)) {
          $foto = FotoProduk::where('fid_produk', $items->fid_produk)->first();
          $items->foto = (!empty($foto) ? asset('storage/' . $foto->foto) : asset('assets/images/produk-default.jpg'));
        }
      } else {
        $items = ItemPenjualan::select('item_penjualan.*')->where('item_penjualan.fid_penjualan', $value->id)->first();
        if (!empty($items)) {
          $items->foto = asset('assets/images/produk-default.jpg');
        }
      }
      $value->produk = $items;
      $value->jumlah = ItemPenjualan::where('fid_penjualan', $value->id)->sum('item_penjualan.jumlah');
    }
    return ApiResponse::success($result, 'OK', ['page'=>$p->currentPage(),'per_page'=>intval($p->perPage()),'total'=>$p->total(),'last_page'=>$p->lastPage()]);
  }

  public function detail_belanja(Request $request, $jenis = 'toko')
  {
    $penjualan = Penjualan::select('penjualan.*', 'status_belanja.icon', 'rekening_pembayaran.keterangan as metode_pembayaran', 'rekening_pembayaran.fid_metode_pembayaran', 'anggota.nama_lengkap', 'anggota.no_anggota', 'anggota.avatar')
      ->leftJoin('anggota', 'anggota.no_anggota', '=', 'penjualan.fid_anggota')
      ->join('rekening_pembayaran', 'rekening_pembayaran.id', '=', 'penjualan.fid_metode_pembayaran')
      ->join('status_belanja', 'status_belanja.id', '=', 'penjualan.fid_status')
      ->where('penjualan.id', $request->id)
      ->first();

    if (!empty($penjualan)) {
      $penjualan->jumlah = ItemPenjualan::where('fid_penjualan', $request->id)->sum('jumlah');
      $penjualan->subtotal = ItemPenjualan::where('fid_penjualan', $request->id)->sum('total');
      $penjualan->diskon_nominal = round($penjualan->subtotal * $penjualan->diskon / 100, 0);

      $penjualan->sisa_angsuran = AngsuranBelanja::where('fid_penjualan', $request->id)->where('fid_status', '!=', 6)->sum('total_angsuran');
      $penjualan->sisa_tenor = AngsuranBelanja::where('fid_penjualan', $request->id)->where('fid_status', '!=', 6)->count();

      if ($jenis == 'toko') {
        $status = DB::table('status_belanja')->find($penjualan->fid_status);
        $penjualan->icon = (!empty($status) ? $status->icon : '');
        $items = ItemPenjualan::select('item_penjualan.*', 'produk.nama_produk', 'produk.kode', 'satuan_barang.satuan')
          ->join('produk', 'produk.id', '=', 'item_penjualan.fid_produk')
          ->join('satuan_barang', 'satuan_barang.id', '=', 'produk.fid_satuan')
          ->where('item_penjualan.fid_penjualan', $request->id)
          ->get();
      } else {
        $status = DB::table('status_transaksi')->find($penjualan->fid_status);
        $penjualan->icon = (!empty($status) ? $status->icon : '');
        $items = ItemPenjualan::select('item_penjualan.*')->where('item_penjualan.fid_penjualan', $request->id)->get();
      }
      foreach ($items as $key => $value) {
        $jumlah = ($penjualan->fid_status == 3 ? $value->jumlah : 0);
        $foto = FotoProduk::where('fid_produk', $value->fid_produk)->first();
        $items[$key]->foto = (!empty($foto) ? asset('storage/' . $foto->foto) : asset('assets/images/produk-default.jpg'));
      }
      $penjualan->items = $items;
      $keterangan = DB::table('keterangan_status_transaksi')
        ->where('jenis_transaksi', ($jenis == 'toko' ? 'belanja' : 'kredit belanja'))
        ->where('fid_status', $penjualan->fid_status)
        ->where('user_page', 'main')
        ->first();
      if (!empty($keterangan)) {
        $penjualan->label_status = str_replace('Konsinyasi', ucfirst($jenis), $keterangan->label);
        $penjualan->keterangan_status = str_replace('Konsinyasi', ucfirst($jenis), $keterangan->keterangan);
      } else {
        $penjualan->label_status = '';
        $penjualan->keterangan_status = '';
      }
      return ApiResponse::success($penjualan);
    }
    return ApiResponse::error('Belanja tidak ditemukan', 404);
  }

  public function riwayat_transaksi(Request $request)
  {
    $transaksi = collect();
    if ($request->jenis == 'transaksi') {
      $transaksi = Transaksi::selectRaw("transaksi.created_at,concat('Transaksi dibuat oleh') as caption,anggota.no_anggota,anggota.nama_lengkap")
        ->join('anggota', 'anggota.no_anggota', '=', 'transaksi.created_by')
        ->where('transaksi.id', $request->id)
        ->get();
    } elseif ($request->jenis == 'penjualan') {
      $transaksi = Penjualan::selectRaw("penjualan.created_at,concat('Transaksi dibuat oleh') as caption,anggota.no_anggota,anggota.nama_lengkap")
        ->join('anggota', 'anggota.no_anggota', '=', 'penjualan.created_by')
        ->where('penjualan.id', $request->id)
        ->get();
    }
    $data = VerifikasiTransaksi::select('verifikasi_transaksi.created_at', 'verifikasi_transaksi.caption', 'anggota.no_anggota', 'anggota.nama_lengkap')
      ->leftJoin('anggota', 'anggota.no_anggota', '=', 'verifikasi_transaksi.created_by')
      ->where('verifikasi_transaksi.fid_transaksi', $request->id)
      ->where('verifikasi_transaksi.jenis', $request->jenis)
      ->get();
    $collection = collect($transaksi);
    $merged = $collection->merge($data);
    return ApiResponse::success($merged->all());
  }

  // public function riwayat_transaksi(Request $request){
  //   $transaksi=Transaksi::select('transaksi.*','anggota.nama_lengkap')
  //     ->join('anggota','anggota.no_anggota','=','transaksi.created_by')
  //     ->where('transaksi.id',$request->id)
  //     ->first();
  //   if(!empty($transaksi)){
  //     if(in_array($transaksi->fid_jenis_transaksi,array(4,5,8)) && $transaksi->fid_metode_transaksi != 2 ){
  //       $jenis='transaksi';
  //       $id=$request->id;
  //       $nama_petugas=$transaksi->nama_lengkap;
  //       $no_anggota=$transaksi->created_by;
  //       $waktu=$transaksi->created_at;
  //     }
  //     else{
  //       $jenis='payroll_simpanan';
  //       $id=$request->fid_payroll;
  //       $nama_petugas=$transaksi->nama_lengkap;
  //       $no_anggota=$transaksi->created_by;
  //       $waktu=$transaksi->created_at;
  //     }
  //     $riwayat=GlobalHelper::get_verifikasi_transaksi($id,$jenis)->toArray();
  //     $arr_transaksi=array("id"=>0,
  //                         "fid_transaksi"=>$id,
  //                         "jenis"=>$jenis,
  //                         "caption"=>($transaksi->fid_jenis_transaksi=='5' ? "Bunga Simpanan diposting oleh" : "Transaksi dibuat oleh"),
  //                         "keterangan"=>null,
  //                         "created_at"=>$transaksi->created_at,
  //                         "created_by"=>$transaksi->created_by,
  //                         "no_anggota"=>$transaksi->created_by,
  //                         "nama_lengkap"=>$transaksi->nama_lengkap);
  //     array_unshift($riwayat,$arr_transaksi);
  //     return $riwayat;
  //   }
  //   else{
  
  //   }
  // }

  public function angsuran_belanja(Request $request)
  {
    $query = AngsuranBelanja::select('penjualan.id', 'penjualan.no_transaksi', 'penjualan.jenis_belanja', 'penjualan.total_pembayaran', 'payroll_angsuran_belanja.bulan', 'angsuran_belanja.total_angsuran', 'angsuran_belanja.angsuran_ke', 'status_angsuran.status_angsuran', 'status_angsuran.color')
      ->join('penjualan', 'penjualan.id', '=', 'angsuran_belanja.fid_penjualan')
      ->join('status_angsuran', 'status_angsuran.id', '=', 'angsuran_belanja.fid_status')
      ->leftJoin('payroll_angsuran_belanja', 'payroll_angsuran_belanja.id', '=', 'angsuran_belanja.fid_payroll');
    if (!empty($request->id)) {
      $query = $query->where('penjualan.id', $request->id);
    } else {
      $query = $query->where('penjualan.fid_anggota', $request->no_anggota)
        ->whereIn('angsuran_belanja.fid_status', array(5, 6));
    }
    $p = $query->paginate($request->input('per_page', 20)); $data = $p->items();
    foreach ($data as $key => $value) {
      $data[$key]->nama_bulan = (!empty($request->id) ? null : GlobalHelper::nama_bulan($value->bulan));
    }
    return isset($p) ? ApiResponse::success($data, 'OK', ['page'=>$p->currentPage(),'per_page'=>intval($p->perPage()),'total'=>$p->total(),'last_page'=>$p->lastPage()]) : ApiResponse::success($data);
  }

  public function retur_barang(Request $request)
  {
    $query = ItemReturPenjualan::select('item_retur_penjualan.*', 'retur_penjualan.no_retur', 'retur_penjualan.created_at', 'retur_penjualan.created_by', 'retur_penjualan.tanggal', 'produk.nama_produk', 'produk.kode', 'satuan_barang.satuan')
      ->join('retur_penjualan', 'retur_penjualan.id', '=', 'item_retur_penjualan.fid_retur_penjualan')
      ->join('produk', 'produk.id', '=', 'item_retur_penjualan.fid_produk')
      ->join('satuan_barang', 'satuan_barang.id', '=', 'produk.fid_satuan')
      ->where('fid_anggota', $request->no_anggota);
    if (!empty($request->search)) {
      $search = $request->search;
      $query = $query->where(function ($i) use ($search) {
        $i->where('retur_penjualan.no_retur', 'like', "%{$search}%")
          ->orWhere('produk.nama_produk', 'like', "%{$search}%")
          ->orWhere('produk.kode', 'like', "%{$search}%");
      });
    }
    $p = $query->orderBy('retur_penjualan.tanggal')->paginate($request->input('per_page', 20)); $result = $p->items();
    foreach ($result as $key => $value) {
      $foto = FotoProduk::where('fid_produk', $value->fid_produk)->first();
      $value->foto = (!empty($foto) ? asset('storage/' . $foto->foto) : asset('assets/images/produk-default.jpg'));
    }
    return ApiResponse::success($result, 'OK', ['page'=>$p->currentPage(),'per_page'=>intval($p->perPage()),'total'=>$p->total(),'last_page'=>$p->lastPage()]);
  }

  public function batalkan_belanja(Request $request)
  {
    $field = Penjualan::find($request->id);
    if (!empty($field)) {
      $field->fid_status = ($request->jenis == 'toko' ? 3 : 5);
      $field->save();
      $this->add_riwayat_transaksi('penjualan', $field->id, 'Transaksi dibatalkan oleh', $request->no_anggota);
      return ApiResponse::success(null, 'success');
    } else {
      return ApiResponse::error('not found', 404);
    }
  }

  public function berita(Request $request)
  {
    $query = Berita::select('*');
    if (!empty($request->search)) {
      $query = $query->where('judul', 'like', "%{$request->search}%");
    }
    $p = $query->orderBy('created_at')->paginate($request->input('per_page', 20)); $result = $p->items();
    foreach ($result as $key => $value) {
      $value->jumlah_attachment = AttachmentBerita::where('fid_berita', $value->id)->count();
      $value->gambar = (!empty($value->gambar) ? asset('storage/' . $value->gambar) : asset('assets/images/produk-default.jpg'));
    }
    return ApiResponse::success($result, 'OK', ['page'=>$p->currentPage(),'per_page'=>intval($p->perPage()),'total'=>$p->total(),'last_page'=>$p->lastPage()]);
  }

  public function detail_berita(Request $request)
  {
    $berita = Berita::find($request->id);
    if (!empty($berita)) {
      $attachment = AttachmentBerita::where('fid_berita', $request->id)->get();
      foreach ($attachment as $key => $value) {
        $attachment[$key]->attachment = (!empty($value->attachment) ? asset('storage/' . $value->attachment) : null);
      }
      $berita->attachment = $attachment;
      $berita->gambar = (!empty($berita->gambar) ? asset('storage/' . $berita->gambar) : asset('assets/images/produk-default.jpg'));
      return ApiResponse::success($berita);
    } else {
      return ApiResponse::error('Berita tidak ditemukan', 404);
    }
  }

    public function upload_avatar(Request $request)
  {
      $field = Anggota::where("no_anggota", $request->no_anggota)->first();

      if (!empty($field)) {
          if ($request->hasFile('avatar')) {
              if (!empty($field->avatar)) {
                  if (file_exists(storage_path('app/public/' . $field->avatar))) {
                      unlink(storage_path('app/public/' . $field->avatar));
                  }
              }
              $uploadedFile = $request->file('avatar');
              $path = $uploadedFile->store('avatar', 'public');
              $field->avatar = $path;
              $field->save();
              return ApiResponse::success(null, 'success');
          } else {
              return ApiResponse::error('File kosong');
          }
      } else {
          return ApiResponse::error('Anggota tidak ditemukan', 404);
      }
  }
}
