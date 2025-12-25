<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Helpers\GlobalHelper;
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
      if ($request->password == decrypt($anggota->password)) {
        $token = Str::random(32);
        $anggota->token = $token;
        $anggota->login_at = date('Y-m-d H:i:s');
        $anggota->save();
        $return = array('token' => $anggota->token, 'no_anggota' => $anggota->no_anggota);
      } else {
        $return = array('msg' => 'Password yang anda masukkan salah');
      }
    } else {
      $return = array('msg' => 'Anggota tidak Ditemukan');
    }
    return $return;
  }

  public function register(Request $request)
  {
    $field = new Anggota;
    $field->created_at = date('Y-m-d H:i:s');
    $field->no_anggota = GlobalHelper::get_nomor_anggota($request->lokasi_kerja);
    $field->nama_lengkap = $request->nama_lengkap;
    $field->password = encrypt($field->no_anggota);
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
    return array('data' => $field);
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

      $anggota->total_saldo_simpanan = GlobalHelper::saldo_tabungan($anggota->no_anggota, 'Total Simpanan'); //Total Simpanan
      $anggota->saldo_simpanan_pokok = GlobalHelper::saldo_tabungan($anggota->no_anggota, 1); //Simpanan Pokok
      $anggota->saldo_simpanan_wajib = GlobalHelper::saldo_tabungan($anggota->no_anggota, 2); //Simpanan Wajib
      $anggota->saldo_simpanan_sukarela = GlobalHelper::saldo_tabungan($anggota->no_anggota, 'Simpanan Sukarela'); //Simpanan Hari Raya
      $anggota->saldo_simpanan_hari_raya = GlobalHelper::saldo_tabungan($anggota->no_anggota, 'Simpanan Hari Raya'); //Simpanan Sukarela


      $anggota->bunga_pinjaman = intval($anggota->sisa_pinjaman * 0.01); //Bunga Semua Pinjaman

      $anggota->angsuran_jangka_panjang = GlobalHelper::sisa_pinjaman($anggota->no_anggota, 9); //Sisa Jangka Panjang
      $anggota->angsuran_jangka_pendek = GlobalHelper::sisa_pinjaman($anggota->no_anggota, 10); //Sisa Jangka Pendek
      $anggota->angsuran_barang = GlobalHelper::sisa_pinjaman($anggota->no_anggota, 11); //Sisa Barangx

        $anggota->sisa_pinjaman = $anggota->angsuran_jangka_panjang + $anggota->angsuran_jangka_pendek + $anggota->angsuran_barang;
        $anggota->total_angsuran_pinjaman = GlobalHelper::angsuran_pinjaman($anggota->no_anggota, 'all'); //Total Angsuran Pinjaman

      $anggota->total_angsuran_belanja = GlobalHelper::total_angsuran_belanja($anggota->no_anggota); //Total Angsuran Belanja
      $anggota->angsuran_belanja_toko = GlobalHelper::angsuran_belanja($anggota->no_anggota, 'toko'); //Total Angsuran Belanja Toko
      $anggota->angsuran_belanja_konsinyasi = GlobalHelper::angsuran_belanja($anggota->no_anggota, 'konsinyasi'); //Total Angsuran Belanja konsinyasi
      $anggota->angsuran_belanja_online = GlobalHelper::angsuran_belanja($anggota->no_anggota, 'online'); //Total Angsuran Belanja Online

      $anggota->setoran_berkala = GlobalHelper::setoran_berkala($anggota->no_anggota); //Setoran Berkala
      $anggota->setoran_simpanan_anggota = 350000;

      return $anggota;
    } else {
      return array('msg' => 'Anggota tidak Ditemukan');
    }
  }

  public function ubah_password(Request $request)
  {
    $anggota = Anggota::where('no_anggota', $request->no_anggota)->first();
    if (!empty($anggota)) {
      if ($request->password_lama == decrypt($anggota->password)) {
        if ($request->password_baru == $request->ulangi_password_baru) {
          $field = Anggota::find($anggota->id);
          $field->password = encrypt($request->password_baru);
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
    return array('msg' => $msg);
  }

  public function jenis_transaksi($modul)
  {
    if ($modul == 'simpanan') {
      $data = DB::table('jenis_transaksi')->whereIn('id', array(1, 2, 3, 4, 5, 6, 7, 8))->get();
    } else {
      $data = DB::table('jenis_transaksi')->whereIn('id', array(9, 10, 11))->get();
    }
    return $data;
  }

  public function status_transaksi($modul)
  {
    if ($modul == 'simpanan') {
      $data = DB::table('status_transaksi')->where('id', '<>', 6)->get();
    } else {
      $data = DB::table('status_transaksi')->get();
    }
    return $data;
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

    $result = $query->orderBy('transaksi.tanggal', 'DESC')
      ->orderBy('transaksi.created_at', 'DESC')
      ->limit($request->limit)
      ->get();


    foreach ($result as $key => $value) {
      $nominal = str_replace('-', '', $value->nominal);
      if ($modul == 'pinjaman') {
        $angsuran = Angsuran::where('fid_transaksi', $value->id)->first();
        if (!empty($angsuran)) {
          $result[$key]->total_angsuran = $angsuran->angsuran_pokok + $angsuran->angsuran_bunga;
          $sisa_pinjaman = Angsuran::where('fid_transaksi', $value->id)->where('fid_status', '!=', 6)->first();
          $result[$key]->sisa_pinjaman = (!empty($sisa_pinjaman) ? $sisa_pinjaman->sisa_hutang  : 0);
          $result[$key]->sisa_tenor = Angsuran::where('fid_transaksi', $value->id)->where('fid_status', '!=', 6)->count();
          $result[$key]->nominal_tampil = 'Rp ' . number_format($nominal, 0, ',', '.');
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
    return $result;
  }

  public function upload_bukti_transaksi(Request $request)
  {
    $field = Transaksi::find($request->id);
    if (!empty($field)) {
      if ($request->hasFile('bukti_transaksi')) {
        if (!empty($field->bukti_transaksi)) {
          unlink(storage_path('app/' . $field->bukti_transaksi));
        }
        $uploadedFile = $request->file('bukti_transaksi');
        $path = $uploadedFile->store('bukti_transaksi');
        $field->bukti_transaksi = $path;
        $field->save();
        return array('msg' => 'success');
      } else {
        return array('msg' => 'file tidak ditemukan');
      }
    } else {
      return array('msg' => 'transaksi tidak ditemukan');
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
      return array('msg' => 'success');
    } else {
      return array('msg' => 'not found');
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
      return $data;
    } else {
      return array('msg' => 'Data tidak Ditemukan');
    }
  }

  public function validasi_transaksi($request, $jenis)
  {
    if ($jenis == 'setoran') {
      $msg = 'success';
    } elseif ($jenis == 'penarikan') {
      $saldo = GlobalHelper::saldo_tabungan($request->no_anggota, 'Simpanan Sukarela');
      if (str_replace('.', '', $request->nominal) > $saldo) {
        $msg = 'Saldo simpanan tidak mencukupi';
      } else {
        $msg = 'success';
      }
    } elseif ($jenis == 'pinjaman') {
      $tenor = array(9 => 50, 10 => 18, 11 => 18);
      if ($request->tenor > $tenor[$request->jenis_pinjaman]) {
        $msg = 'Tenor melebihi maksimal yaitu ' . $tenor[$request->jenis_pinjaman] . ' bulan';
      } else {
        $angsuran_pinjaman = GlobalHelper::angsuran_pinjaman($request->no_anggota, 'all') + str_replace('.', '', $request->total_angsuran);
        $angsuran_belanja = GlobalHelper::total_angsuran_belanja($request->no_anggota);
        $angsuran_simpanan = GlobalHelper::setoran_berkala($request->no_anggota) + 350000;
        $total_angsuran = $angsuran_pinjaman + $angsuran_belanja + $angsuran_simpanan;

        // $total_angsuran_pinjaman = $angsuran_pinjaman + 350000 + str_replace('.', '', $request->total_angsuran);
        $total_angsuran_pinjaman = $angsuran_pinjaman + str_replace('.', '', $request->total_angsuran);

        $sisa_tenor = GlobalHelper::sisa_tenor_pinjaman($request->no_anggota, $request->jenis_pinjaman)['sisa'];
        $sisa_pinjaman = GlobalHelper::sisa_pinjaman($request->no_anggota, $request->jenis_pinjaman);

        $gaji_pokok = str_replace('.', '', $request->gaji_pokok);

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
    return array('data' => $data, 'bulan' => $gaji_pokok[0], 'gaji_pokok' => $gaji_pokok[1]);
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
        $field->updated_at = date('Y-m-d H:i:s');
      }

      if ($request->action == 'delete') {
        $field->delete();
        return array('msg' => $validasi);
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
          $this->update_riwayat_gaji($request);
          $this->proses_angsuran($field->id, $request);
        }
        return array('id' => $field->id);
      }
    } else {
      return array('msg' => $validasi);
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
      $field->bunga = 0.01;
      $field->sisa_hutang = $this->sisa_hutang($id, $n);
      $field->angsuran_pokok = ROUND(str_replace('.', '', $request->nominal) / $request->tenor, 0);
      $field->angsuran_bunga = ROUND(0.01 * str_replace('.', '', $request->nominal));
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
    // $data = $query->limit($request->limit)->get();
    $data = $query->get();
    foreach ($data as $key => $value) {
      $data[$key]->nama_bulan = (!empty($request->id) ? null : GlobalHelper::nama_bulan($value->bulan));
    }
    return $data;
  }

  public function update_riwayat_gaji($request)
  {
    $bulan = GlobalHelper::get_bulan(date('m-Y'))[0];
    $riwayat_gaji = GajiPokok::where('fid_anggota', $request->no_anggota)
      ->where('bulan', $bulan)
      ->first();
    if (!empty($riwayat_gaji)) {
      $field = GajiPokok::find($riwayat_gaji->id);
      $field->updated_at = date('Y-m-d H:i:s');
    } else {
      $field = new GajiPokok;
      $field->created_at = date('Y-m-d H:i:s');
      $field->created_by = $request->no_anggota;
      $field->bulan = $bulan;
      $field->fid_anggota = $request->no_anggota;
    }
    $field->gaji_pokok = str_replace('.', '', $request->gaji_pokok);
    $field->save();
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

    $result = $query->orderBy('produk.nama_produk', 'DESC')
      ->limit($request->limit)
      ->get();
    foreach ($result as $key => $value) {
      $foto = FotoProduk::where('fid_produk', $value->id)->first();
      $result[$key]->foto = (!empty($foto) ? asset('storage/' . $foto->foto) : asset('assets/images/produk-default.jpg'));
      $kategori = explode('.', $value->kode_kategori);
      if ($kategori[0] == 0) {
        $result[$key]->kelompok = GlobalHelper::detail_kategori_produk($kategori[1]);
        $result[$key]->kategori = GlobalHelper::detail_kategori_produk($kategori[2]);
        $result[$key]->sub_kategori = '';
      } else {
        $result[$key]->kelompok = GlobalHelper::detail_kategori_produk($kategori[0]);
        $result[$key]->kategori = GlobalHelper::detail_kategori_produk($kategori[1]);
        $result[$key]->sub_kategori = GlobalHelper::detail_kategori_produk($kategori[2]);
      }
    }
    return $result;
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

      $stok = GlobalHelper::stok_barang($data->id);
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
    }
    return $data;
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
      $result[$key]->foto = (!empty($foto) ? asset('storage/' . $foto->foto) : asset('assets/images/produk-default.jpg'));
      $barang = GlobalHelper::stok_barang($value->fid_produk);
      $result[$key]->terjual = $barang['terjual'];
      $result[$key]->sisa = $barang['sisa'];
    }
    return $result;
  }

  public function proses_keranjang(Request $request)
  {
    $produk = Produk::find($request->id);
    if (!empty($produk)) {
      $stok = GlobalHelper::stok_barang($produk->id);
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
        return array('msg' => 'success');
      } else {
        $field->harga = $produk->harga_jual;
        $field->total = $field->jumlah * $field->harga;
        if ($field->jumlah <= $stok['sisa']) {
          $field->save();
          return array('msg' => 'success');
        } else {
          return array('msg' => 'Jumlah melebih stok');
        }
      }
    } else {
      return array('msg' => 'Produk not Found');
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
    $id = $this->add_penjualan($request->no_anggota);
    $total = 0;
    foreach ($request->barang as $key => $keranjang_id) {
      $keranjang = KeranjangBelanja::select('keranjang_belanja.*', 'produk.*')
        ->join('produk', 'produk.id', 'keranjang_belanja.fid_produk')
        ->where('keranjang_belanja.id', $keranjang_id)
        ->first();
      if (!empty($keranjang)) {
        $stok = GlobalHelper::stok_barang($keranjang->fid_produk);
        $field = new ItemPenjualan;
        $field->fid_penjualan = $id;
        $field->fid_produk = $keranjang->fid_produk;
        $field->harga_beli = $keranjang->harga_beli;
        $field->margin = $keranjang->margin;
        $field->margin_nominal = $keranjang->margin_nominal;
        $field->harga = $keranjang->harga_jual;
        $field->jumlah = $request->jumlah[$key];
        $field->total = $field->harga * $field->jumlah;
        if ($field->jumlah <= $stok['sisa']) {
          $field->save();
        }
        $total = $total + $field->total;
      } else {
        $total = $total + 0;
      }
    }
    if ($total == 0) {
      Penjualan::find($id)->delete();
    } else {
      $this->update_total_pembayaran($id, $total);
      $this->hapus_keranjang($id);
    }
    return array('msg' => 'success');
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

  public function belanja(Request $request, $jenis)
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

    $result = $query->orderBy('penjualan.created_at')->limit($request->limit)->get();
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
        $items->foto = asset('assets/images/produk-default.jpg');
      }
      $result[$key]->produk = $items;
      $result[$key]->jumlah = ItemPenjualan::where('fid_penjualan', $value->id)->sum('item_penjualan.jumlah');
    }
    return $result;
  }

  public function detail_belanja(Request $request, $jenis)
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
      $penjualan->label_status = str_replace('Konsinyasi', ucfirst($jenis), $keterangan->label);
      $penjualan->keterangan_status = str_replace('Konsinyasi', ucfirst($jenis), $keterangan->keterangan);
    }
    return $penjualan;
  }

  public function riwayat_transaksi(Request $request)
  {
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
    return $merged->all();
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
  //     return array('msg'=>'Data tidak Ditemukan');
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
    $data = $query->limit($request->limit)->get();
    foreach ($data as $key => $value) {
      $data[$key]->nama_bulan = (!empty($request->id) ? null : GlobalHelper::nama_bulan($value->bulan));
    }
    return $data;
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
    $result = $query->orderBy('retur_penjualan.tanggal')->limit($request->limit)->get();
    foreach ($result as $key => $value) {
      $foto = FotoProduk::where('fid_produk', $value->fid_produk)->first();
      $result[$key]->foto = (!empty($foto) ? asset('storage/' . $foto->foto) : asset('assets/images/produk-default.jpg'));
    }
    return $result;
  }

  public function batalkan_belanja(Request $request)
  {
    $field = Penjualan::find($request->id);
    if (!empty($field)) {
      $field->fid_status = ($request->jenis == 'toko' ? 3 : 5);
      $field->save();
      $this->add_riwayat_transaksi('penjualan', $field->id, 'Transaksi dibatalkan oleh', $request->no_anggota);
      return array('msg' => 'success');
    } else {
      return array('msg' => 'not found');
    }
  }

  public function berita(Request $request)
  {
    $query = Berita::select('*');
    if (!empty($request->search)) {
      $query = $query->where('judul', 'like', "%{$request->search}%");
    }
    $result = $query->orderBy('created_at')->limit($request->limit)->get();
    foreach ($result as $key => $value) {
      $result[$key]->jumlah_attachment = AttachmentBerita::where('fid_berita', $value->id)->count();
      $result[$key]->gambar = (!empty($value->gambar) ? asset('storage/' . $value->gambar) : asset('assets/images/produk-default.jpg'));
    }
    return $result;
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
      return $berita;
    } else {
      return array('msg' => 'Berita tidak ditemukan');
    }
  }

  public function upload_avatar(Request $request)
  {

    $field = Anggota::where("no_anggota", $request->no_anggota)->first();

    if (!empty($field)) {
      if ($request->hasFile('avatar')) {
        if (!empty($field->avatar)) {
          unlink(storage_path('app/' . $field->avatar));
        }
        $uploadedFile = $request->file('avatar');
        $path = $uploadedFile->store('avatar');
        $field->avatar = $path;
        $field->save();
        return array('msg' => 'success');
      } else {
        return array('msg' => 'File kosong');
      }
    } else {
      return array('msg' => 'Anggota tidak ditemukan');
    }
  }
}
