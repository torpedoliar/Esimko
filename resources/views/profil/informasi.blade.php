<div id="accordion">
  <div class="card mb-1 shadow-none">
    <div class="card-header">
      <div class="row">
        <div class="col-md-8 align-self-center">
          <h5 class="m-0">Informasi Personal</h5>
        </div>
        <div class="col-md-4">
          <button class="btn btn-primary btn-sm pull-right" onclick="edit_personal()">Ubah</button>
        </div>
      </div>
    </div>
    <div id="personal">
      <div class="card-body">
        <form action="{{url('main/profil/edit_profil')}}" method="post">
          {{ csrf_field() }}
          <table class="table table-informasi ">
            <tr>
              <th width="180px">No. Anggota</th>
              <th width="10px">:</th>
              <td>{{$data['profil']->no_anggota}}</td>
            </tr>
            <tr>
              <th>No. HIRS</th>
              <th>:</th>
              <td>{{$data['profil']->no_hirs}}</td>
            </tr>
            <tr>
              <th>Nama Lengkap</th>
              <th>:</th>
              <td>{{$data['profil']->nama_lengkap}}</td>
            </tr>
            <tr>
              <th>Nama Panggilan</th>
              <th>:</th>
              <td>
                <div class="show">{{$data['profil']->nama_panggilan}}</div>
                <div class="form">
                  <input type="text" class="form-control" name="nama_panggilan" id="nama_panggilan" value="{{$data['profil']->nama_panggilan}}">
                </div>
              </td>
            </tr>
            <tr>
              <th>Tempat, Tanggal Lahir</th>
              <th>:</th>
              <td>
                <div class="show">{{$data['profil']->tempat_lahir}}, {{\App\Helpers\GlobalHelper::tgl_indo($data['profil']->tanggal_lahir)}}</div>
                <div class="row form">
                  <div class="col-md-6">
                    <input type="text" class="form-control" name="tempat_lahir" id="tempat_lahir" value="{{$data['profil']->tempat_lahir}}" >
                  </div>
                  <div class="col-md-6">
                    <input type="text" class="form-control datepicker" name="tanggal_lahir" id="tanggal_lahir" value="{{\App\Helpers\GlobalHelper::dateFormat($data['profil']->tanggal_lahir,'d-m-Y')}}" >
                  </div>
                </div>
              </td>
            </tr>
            <tr>
              <th>Jenis Kelamin</th>
              <th>:</th>
              <td>
                <div class="show">{{(!empty($data['profil']->jenis_kelamin) ? $data['profil']->jenis_kelamin=='L' ? 'Laki-laki' : 'Perempuan' : '-')}}</div>
                <div class="form" style="text-align:left">
                  <select class="select2" style="width:100%;" name="jenis_kelamin" id="jenis_kelamin">
                    <option value="L" {{($data['profil']->jenis_kelamin=='L' ? 'selected' : '')}}>Laki-Laki</option>
                    <option value="P" {{($data['profil']->jenis_kelamin=='P' ? 'selected' : '')}}>Perempuan</option>
                  </select>
                </div>
              </td>
            </tr>
            <tr>
              <th>Level Jabatan</th>
              <th>:</th>
              <td>
                <div class="show">{{$data['profil']->level}}</div>
                <div class="form">
                  <input type="text" class="form-control" name="level_jabatan" id="level_jabatan" value="{{$data['profil']->level}}">
                </div>
              </td>
            </tr>
            <tr>
              <th>Divisi</th>
              <th>:</th>
              <td>
                <div class="show">{{$data['profil']->divisi}}</div>
                <div class="form">
                  <input type="text" class="form-control" name="divisi" id="divisi" value="{{$data['profil']->divisi}}">
                </div>
              </td>
            </tr>
            <tr>
              <th>Bagian</th>
              <th>:</th>
              <td>
                <div class="show">{{$data['profil']->bagian}}</div>
                <div class="form">
                  <input type="text" class="form-control" name="bagian" id="bagian" value="{{$data['profil']->bagian}}">
                </div>
              </td>
            </tr>
            <tr>
              <th>Lokasi Kerja</th>
              <th>:</th>
              <td>
                <div class="show">{{$data['profil']->lokasi}}</div>
                <div class="form" style="text-align:left">
                  <select class="select2" style="width:100%;" name="lokasi_kerja" id="lokasi_kerja">
                    <option value="SJA 1" {{($data['profil']->lokasi=='SJA 1' ? 'selected' : '')}}>SJA 1</option>
                    <option value="SJA 3" {{($data['profil']->lokasi=='SJA 3' ? 'selected' : '')}}>SJA 3</option>
                    <option value="KOPERASI" {{($data['profil']->lokasi=='KOPERASI' ? 'selected' : '')}}>KOPERASI</option>
                  </select>
                </div>
              </td>
            </tr>
            <tr>
              <th>No. KTP</th>
              <th>:</th>
              <td>
                <div class="show">{{$data['profil']->no_ktp}}</div>
                <div class="form">
                  <input type="text" class="form-control" name="no_ktp" id="no_ktp" value="{{$data['profil']->no_ktp}}">
                </div>
              </td>
            </tr>
            <tr>
              <th>ID. Karyawan / NIK</th>
              <th>:</th>
              <td>
                <div class="show">{{$data['profil']->id_karyawan}}</div>
                <div class="form">
                  <input type="text" class="form-control" name="id_karyawan" id="id_karyawan" value="{{$data['profil']->id_karyawan}}">
                </div>
              </td>
            </tr>
            <tr>
              <th>Mulai Bergabung</th>
              <th>:</th>
              <td>
                <div class="show">{{\App\Helpers\GlobalHelper::tgl_indo($data['profil']->tanggal_bergabung)}} <span style="font-weight:500">({{\App\Helpers\GlobalHelper::hitung_hari($data['profil']->tanggal_bergabung,date('Y-m-d'),'y')}} tahun)</span></div>
                <div class="form">
                  <input type="text" class="form-control datepicker" name="tanggal_bergabung" id="tanggal_bergabung" value="{{\App\Helpers\GlobalHelper::dateFormat($data['profil']->tanggal_bergabung,'d-m-Y')}}">
                </div>
              </td>
            </tr>
            <tr>
              <th>Mulai Bekerja</th>
              <th>:</th>
              <td>
                <div class="show">{{\App\Helpers\GlobalHelper::tgl_indo($data['profil']->tanggal_bekerja)}} <span style="font-weight:500">({{\App\Helpers\GlobalHelper::hitung_hari($data['profil']->tanggal_bekerja,date('Y-m-d'),'y')}} tahun)</span></div>
                <div class="form">
                  <input type="text" class="form-control datepicker" name="tanggal_bekerja" id="tanggal_bekerja" value="{{\App\Helpers\GlobalHelper::dateFormat($data['profil']->tanggal_bekerja,'d-m-Y')}}">
                </div>
              </td>
            </tr>
          </table>
          <div class="form pull-right mt-3 mb-5">
            <button type="button"  class="btn btn-dark" onclick="cancel_personal()">Cancel</button>
            <button class="btn btn-primary">Simpan</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <div class="card mb-1 shadow-none">
    <div class="card-header">
      <div class="row">
        <div class="col-md-8 align-self-center">
          <h5 class="m-0">Informasi Kontak dan Rekening</h5>
        </div>
        <div class="col-md-4">
          <button class="btn btn-primary btn-sm pull-right" onclick="edit_kontak()">Ubah</button>
        </div>
      </div>
    </div>
    <div id="kontak">
      <div class="card-body">
        <form action="{{url('main/profil/edit_kontak')}}" method="post">
          {{ csrf_field() }}
          <table class="table table-informasi">
            <tr>
              <th width="180px">Alamat</th>
              <th width="5px">:</th>
              <td>
                <div class="show">{{(!empty($data['profil']->alamat) ? $data['profil']->alamat : 'Belum Diketahui')}}</div>
                <div class="form">
                  <textarea class="form-control" id="alamat" name="alamat">{{(!empty($data['profil']->alamat) ? $data['profil']->alamat : '')}}</textarea>
                </div>
              </td>
            </tr>
            <tr>
              <th>No. Handphone</th>
              <th>:</th>
              <td>
                <div class="show">{{(!empty($data['profil']->no_handphone) ? $data['profil']->no_handphone : 'Belum Diketahui')}}</div>
                <div class="form">
                  <input type="text" class="form-control" id="no_handphone" name="no_handphone" value="{{(!empty($data['profil']->no_handphone) ? $data['profil']->no_handphone : '')}}">
                </div>
              </td>
            </tr>
            <tr>
              <th>Email</th>
              <th>:</th>
              <td>
                <div class="show">{{(!empty($data['profil']->email) ? $data['profil']->email : 'Belum Diketahui')}}</div>
                <div class="form">
                  <input type="text" class="form-control" id="email" name="email" value="{{(!empty($data['profil']->email) ? $data['profil']->email : '')}}">
                </div>
              </td>
            </tr>
            <tr>
              <th>No. Rekening</th>
              <th>:</th>
              <td>
                <div class="show">{{(!empty($data['profil']->no_rekening) ? $data['profil']->no_rekening : 'Belum Diketahui')}}</div>
                <div class="form">
                  <input type="text" class="form-control" id="no_rekening" name="no_rekening" value="{{(!empty($data['profil']->no_rekening) ? $data['profil']->no_rekening : '')}}">
                </div>
              </td>
            </tr>
            <tr>
              <th>Atas Nama Rekening</th>
              <th>:</th>
              <td>
                <div class="show">{{(!empty($data['profil']->an_rekening) ? $data['profil']->an_rekening : 'Belum Diketahui')}}</div>
                <div class="form">
                  <input type="text" class="form-control" id="an_rekening" name="an_rekening" value="{{(!empty($data['profil']->an_rekening) ? $data['profil']->an_rekening : $data['profil']->nama_lengkap)}}">
                </div>
              </td>
            </tr>
            <tr>
              <th>Nama Bank</th>
              <th>:</th>
              <td>
                <div class="show">{{(!empty($data['profil']->nama_bank) ? $data['profil']->nama_bank : 'Belum Diketahui')}}</div>
                <div class="form">
                  <input type="text" class="form-control" id="nama_bank" name="nama_bank" value="{{(!empty($data['profil']->nama_bank) ? $data['profil']->nama_bank : '')}}">
                </div>
              </td>
            </tr>
          </table>
          <div class="form pull-right mt-3 mb-5">
            <button type="button" class="btn btn-dark" onclick="cancel_kontak()">Cancel</button>
            <button class="btn btn-primary">Simpan</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
