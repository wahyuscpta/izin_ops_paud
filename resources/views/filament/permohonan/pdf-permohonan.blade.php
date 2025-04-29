<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Formulir Permohonan Izin Operasional</title>
    <style>
        @page {
            margin: 1.5cm;
            font-size: 10pt;
            line-height: 1.4;
        }

        .title-page{
            text-align: center; 
            font-weight: bold; 
            font-size: 12pt; 
            margin-bottom: 20px;
            text-transform: uppercase;
        }

        tr {
            page-break-inside: avoid;
        }
        
        .form-header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .form-header-table td {
            border: .5px solid black;
            vertical-align: middle;
            padding: 7px 5px;
        }

        .title-table{
            border: none !important;
            padding: 10px 0;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .left-section {
            width: 50%;
        }
        
        .right-section {
            width: 50%;
        }
        
        .inner-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .inner-table td {
            border: none;
            vertical-align: middle;
        }
                
        .label-column {           
            background-color: #ececec;
        }
        
        .separator-column {
            width: 15px;
        }
        
        .content-column {
            text-transform: capitalize !important;
        }   

        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>

    @php
        $identitas = $permohonan->identitas;
        $penyelenggara = $permohonan->penyelenggara;
        $pengelola = $permohonan->pengelola;
        $pesertaDidik = $permohonan->peserta_didik;
        $personalia = $permohonan->personalia;
        $programPendidikan = $permohonan->program_pendidikan;
        $prasarana = $permohonan->prasarana;
        $sarana = $permohonan->sarana;

        function formatValue($value) {
            if ($value instanceof \Carbon\Carbon || \Carbon\Carbon::hasFormat($value, 'Y-m-d')) {
                return \Carbon\Carbon::parse($value)->translatedFormat('d F Y');
            } elseif (is_string($value)) {
                return ucwords(strtolower($value));
            }
            return $value;
        }
    @endphp

    <p class="title-page">Formulir Permohonan</p>

    <table class="form-header-table">
        <tr>
            <td class="left-section" style="width: 70%; padding-right: 70px; border: none;">
                <table class="inner-table">
                    <tr>
                        <td style="background-color: transparent; width: 60px; vertical-align: top" class="label-column">Nomor</td>
                        <td class="separator-column" style="vertical-align: top">:</td>
                        <td style="font-weight: normal; text-transform: unset; vertical-align: top" class="content-column">{{ $permohonan->no_permohonan }}</td>
                    </tr>
                    <tr>
                        <td style="background-color: transparent; width: 60px; vertical-align: top" class="label-column">Perihal</td>
                        <td class="separator-column" style="vertical-align: top">:</td>
                        <td style="font-weight: normal; text-transform: unset; vertical-align: top;" class="content-column">Permohonan Ijin Operasional PAUD & PNF</td>
                    </tr>
                </table>
            </td>
            
            <td class="right-section" style="padding-left: 20px; border: none;">
                <table class="inner-table">
                    <tr>
                        <td style="vertical-align: top">Kepada Yth.</td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top">Kepala Dinas Pendidikan, Kepemudaan dan Olah Raga Kabupaten</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- IDENTITAS -->
    <table class="form-header-table">
        <tr>
            <td colspan="4" class="title-table">A. <span style="margin-left: 10px">IDENTITAS</span></td>
        </tr>

        <tr>
            <td class="label-column" style="width: 10%;">Nama Lembaga</td>
            <td class="content-column" style="width: 30%;">{{ $identitas->nama_lembaga }}</td>
            <td class="label-column" style="width: 15%;">Rumpun Pendidikan</td>
            <td class="content-column" style="width: 30%;">{{ $identitas->rumpun_pendidikan }}</td>
        </tr>

        <tr>
            <td class="label-column">Alamat Jalan</td>
            <td class="content-column">{{ $identitas->alamat_identitas }}</td>
            <td class="label-column">Jenis Pendidikan</td>
            <td class="content-column">{{ $identitas->jenis_pendidikan }}</td>
        </tr>

        <tr>
            <td class="label-column">Telepon</td>
            <td class="content-column">{{ $identitas->no_telepon_identitas }}</td>
            <td class="label-column">Jenis Lembaga</td>
            <td class="content-column">{{ $identitas->jenis_lembaga }}</td>
        </tr>

        <tr>
            <td class="label-column">Desa/Kel</td>
            <td class="content-column">{{ ucwords(strtolower($identitas->village->name)) }}</td>
            <td class="label-column">Punya Cabang</td>
            <td class="content-column">{{ $identitas->has_cabang ? 'Ya' : 'Tidak' }}</td>
        </tr>

        <tr>
            <td class="label-column">Kecamatan</td>
            <td class="content-column">{{ ucwords(strtolower($identitas->district->name)) }}</td>
            <td class="label-column">Jumlah Cabang</td>
            <td class="content-column">{{ $identitas->jumlah_cabang }}</td>
        </tr>

        <tr>
            <td class="label-column">Kab/Kota</td>
            <td class="content-column">{{ ucwords(strtolower($identitas->regency->name)) }}</td>
            <td class="label-column" colspan="2">Nama dan Alamat Cabang</td>
        </tr>

        <tr>
            <td class="label-column">Didirikan Pada Tanggal</td>
            <td class="content-column">{{ formatValue($identitas->tgl_didirikan) }}</td>
            <td class="content-column" colspan="2">
                <table class="inner-table">
    @if ($identitas->cabangs->isNotEmpty())
        @foreach ($identitas->cabangs as $index => $cabang)
            <tr>
                <td>{{ $index + 1 }}.</td>
                <td style="padding: 0">{{ $cabang->nama_lembaga_cabang ?? '-' }}</td>
            </tr>
            <tr>
                <td></td>
                <td style="padding: 0">{{ ucwords(strtolower($cabang->alamat_lembaga_cabang ?? '-')) }}</td>
            </tr>
        @endforeach
    @else
        <tr>
            <td>-</td>
            <td style="padding: 0">-</td>
        </tr>
    @endif
                </table>
            </td>
        </tr>

        <tr>
            <td class="label-column">Penyelenggaraan Sejak Tanggal</td>
            <td class="content-column">{{ formatValue($identitas->tgl_terdaftar) }}</td>
            <td class="label-column">Lembaga Induk</td>
            <td class="content-column">{{ $identitas->nama_lembaga_induk ?? '-'}}</td>            
        </tr>

        <tr>
            <td class="label-column">Nomor Registrasi</td>
            <td class="content-column">{{ $identitas->no_registrasi }}</td>
            <td class="label-column">Alamat Lembaga Induk</td>
            <td class="content-column">{{ $identitas->alamat_lembaga_induk ?? '-'}}</td> 
        </tr>

        <tr>
            <td class="label-column">Nomor Surat Keputusan</td>
            <td class="content-column">{{ $identitas->no_surat_keputusan }}</td>
            <td class="label-column" colspan="2"></td>
        </tr>

    </table>

    <!-- PENYELENGGARA -->
    <table class="form-header-table">
        <tr>
            <td colspan="4" class="title-table">B. <span style="margin-left: 10px">PENYELENGGARA/YAYASAN</span></td>
        </tr>

        <tr>
            <td colspan="2" style="text-align: center; background-color: #ececec" class="content-column">PERORANGAN</td>
            <td colspan="2" style="text-align: center; background-color: #ececec" class="content-column">BADAN HUKUM</td>
        </tr>

        <tr>
            <td class="label-column" style="width: 15%;">Nama Lengkap</td>
            <td class="content-column" style="width: 35%;">{{ $penyelenggara->nama_perorangan }}</td>
            <td class="label-column" style="width: 15%;">Nama Lengkap</td>
            <td class="content-column" style="width: 30%;">{{ $penyelenggara->nama_badan }}</td>
        </tr>

        <tr>
            <td class="label-column" style="width: 15%;">Agama</td>
            <td class="content-column" style="width: 35%;">{{ $penyelenggara->agama_perorangan }}</td>
            <td class="label-column" style="width: 15%;">Agama</td>
            <td class="content-column" style="width: 30%;">{{ $penyelenggara->agama_badan }}</td>
        </tr>

        <tr>
            <td class="label-column" style="width: 15%;">Kewarganegaraan</td>
            <td class="content-column" style="width: 35%;">{{ $penyelenggara->kewarganegaraan_perorangan }}</td>
            <td class="label-column" style="width: 15%;">Akte</td>
            <td class="content-column" style="width: 30%;">{{ $penyelenggara->akte_badan }}</td>
        </tr>

        <tr>
            <td class="label-column" style="width: 15%;">No KTP</td>
            <td class="content-column" style="width: 35%;">{{ $penyelenggara->ktp_perorangan }}</td>
            <td class="label-column" style="width: 15%;">Nomor</td>
            <td class="content-column" style="width: 30%;">{{ $penyelenggara->nomor_badan }}</td>
        </tr>

        <tr>
            <td class="label-column" style="width: 15%;">Tanggal</td>
            <td class="content-column" style="width: 35%;">{{ formatValue($penyelenggara->tanggal_perorangan ) }}</td>
            <td class="label-column" style="width: 15%;">Tanggal</td>
            <td class="content-column" style="width: 30%;">{{ formatValue($penyelenggara->tanggal_badan) }}</td>
        </tr>

        <tr>
            <td class="label-column" style="width: 15%;">Alamat Jalan</td>
            <td class="content-column" style="width: 35%;">{{ $penyelenggara->alamat_perorangan }}</td>
            <td class="label-column" style="width: 15%;">Alamat Jalan</td>
            <td class="content-column" style="width: 30%;">{{ $penyelenggara->alamat_badan }}</td>
        </tr>

        <tr>
            <td class="label-column" style="width: 15%;">Telepon</td>
            <td class="content-column" style="width: 35%;">{{ $penyelenggara->telepon_perorangan }}</td>
            <td class="label-column" style="width: 15%;">Telepon</td>
            <td class="content-column" style="width: 30%;">{{ $penyelenggara->telepon_badan }}</td>
        </tr>

        <tr>
            <td class="label-column" style="width: 15%;">Kab/Kota</td>
            <td class="content-column" style="width: 35%;">{{ $penyelenggara->regencyPerorangan->name }}</td>
            <td class="label-column" style="width: 15%;">Kab/Kota</td>
            <td class="content-column" style="width: 30%;">{{ $penyelenggara->regencyBadan->name }}</td>
        </tr>
    </table>

    <!-- PENGELOLA -->
    <table class="form-header-table">
        <tr>
            <td colspan="4" class="title-table">C. <span style="margin-left: 10px">Pengelola/Penanggung Jawab Teknis Edukatif</span></td>
        </tr>

        <tr>
            <td class="label-column" style="width: 15%;">Nama Lengkap</td>
            <td class="content-column" style="width: 35%;">{{ $pengelola->nama_pengelola }}</td>
            <td class="label-column" style="width: 15%;">No KTP</td>
            <td class="content-column" style="width: 30%;">{{ $pengelola->ktp_pengelola }}</td>
        </tr>

        <tr>
            <td class="label-column">Agama</td>
            <td class="content-column">{{ $pengelola->agama_pengelola }}</td>
            <td class="label-column">Tanggal</td>
            <td class="content-column">{{ $pengelola->tanggal_pengelola }}</td>
        </tr>

        <tr>
            <td class="label-column">Jenis Kelamin</td>
            <td class="content-column">{{ $pengelola->jenis_kelamin_pengelola == 'l' ? 'Laki-Laki' : 'Perempuan' }}</td>
            <td class="label-column">Alamat Jalan</td>
            <td class="content-column">{{ $pengelola->alamat_pengelola }}</td>
        </tr>

        <tr>
            <td class="label-column">Kewarganegaraan</td>
            <td class="content-column">{{ $pengelola->kewarganegaraan_pengelola }}</td>
            <td class="label-column">Kab/Kota</td>
            <td class="content-column">{{ $pengelola->regency->name }}</td>
        </tr>

        <tr>
            <td class="label-column">Telepon</td>
            <td class="content-column">{{ $pengelola->telepon_pengelola }}</td>
            <td class="label-column" colspan="2"></td>
        </tr>
    </table>

    <!-- PESERTA DIDIK -->
    <table class="form-header-table">
        <tr>
            <td colspan="7" class="title-table">D. <span style="margin-left: 10px">WARGA BELAJAR/PESERTA DIDIK</span></td>
        </tr>

        <tr>
            <td class="label-column">Penerimaan Melalui Test </td>
            <td colspan="2" class="content-column" style="width: 25%;">{{ $pesertaDidik->jalur_penerimaan_tes }}</td>
            <td colspan="2" class="label-column">Tata Usaha Penerimaan</td>
            <td colspan="2" class="content-column" style="width: 25%;">{{ $pesertaDidik->tata_usaha_penerimaan = 'ada' ? 'Ada' : 'Tidak Ada' }}</td>
        </tr>

        <tr>
            <td class="label-column">Jumlah Setiap Kelompok/Angkatan </td>
            <td colspan="2" class="content-column" style="width: 25%;  text-transform: unset">Rata-rata {{ $pesertaDidik->jumlah_tiap_angkatan }} Orang</td>
            <td colspan="2" class="label-column">Yang Menyelesaikan Program Pendidikan Sampai Akhir</td>
            <td colspan="2" class="content-column" style="width: 25%; text-transform: unset">Rata-rata {{ $pesertaDidik->jumlah_menyelesaikan }} %</td>
        </tr>
        <tr>
            <td colspan="7" style="text-align: center" class="label-column">KEADAAN WARGA BELAJAR / PESERTA DIDIK</td>
        </tr>

        <tr>
            <td rowspan="2" align="center" class="label-column">Tingkat</td>
            <td colspan="3" align="center" class="label-column">Keadaan Sekarang</td>
            <td colspan="3" align="center" class="label-column">Yang Telah Tamat</td>
        </tr>
        <tr>
            <td style="width: 15%" align="center">Laki-laki</td>
            <td style="width: 15%" align="center">Perempuan</td>
            <td style="width: 10%" align="center">Jumlah</td>
            <td style="width: 20%" align="center">Laki-laki</td>
            <td style="width: 20%" align="center">Perempuan</td>
            <td style="width: 30%" align="center">Jumlah</td>
        </tr>
        <tr>
            <td align="center" class="label-column">Jumlah Seluruhnya</td>
            <td align="center">{{ $pesertaDidik->jumlah_sekarang_lk }}</td>
            <td align="center">{{ $pesertaDidik->jumlah_sekarang_pr }}</td>
            <td align="center">{{ $pesertaDidik->jumlah_sekarang_total }}</td>
            <td align="center">{{ $pesertaDidik->jumlah_tamat_lk }}</td>
            <td align="center">{{ $pesertaDidik->jumlah_tamat_pr }}</td>
            <td align="center">{{ $pesertaDidik->jumlah_tamat_total }}</td>
        </tr>
    </table>

    <!-- PERSONALIA -->
    <table class="form-header-table">
        <tr>
            <td colspan="4" class="title-table">E. <span style="margin-left: 10px">PERSONALIA (PERINCIAN TERLAMPIR)</span></td>
        </tr>
        
        <tr>
            <td colspan="4" class="label-column">a. <span style="margin-left: 10px">Warga Negara Indonesia</span></td>
        </tr>

        <tr>
            <td style="width: 25%; font-size: 8pt; text-align: center" class="label-column">Sumber Belajar/Guru/Pengasuh</td>
            <td style="width: 25%; font-size: 8pt; text-align: center" class="label-column">Asisten Sumber Belajar/Guru</td>
            <td style="width: 25%; font-size: 8pt; text-align: center" class="label-column">Pegawai Tata Usaha</td>
            <td style="width: 25%; font-size: 8pt; text-align: center" class="label-column">Pesuruh</td>
        </tr>

        <tr style="font-size: 9pt">
            {{-- GURU --}}
            <td>
                <table class="inner-table" style="page">
                    <tr>
                        <td>Laki-Laki</td>
                        <td>{{ $personalia->guru_wni_lk }}</td>
                        <td>Orang</td>
                    </tr>
                    <tr>
                        <td>Perempuan</td>
                        <td>{{ $personalia->guru_wni_pr }}</td>
                        <td>Orang</td>
                    </tr>
                    <tr>
                        <td>Jumlah</td>
                        <td>{{ $personalia->guru_wni_jumlah }}</td>
                        <td>Orang</td>
                    </tr>
                </table>
            </td>

            {{-- ASISTEN --}}
            <td style="font-size: 9pt">
                <table class="inner-table">
                    <tr>
                        <td>Laki-Laki</td>
                        <td>{{ $personalia->asisten_wni_lk }}</td>
                        <td>Orang</td>
                    </tr>
                    <tr>
                        <td>Perempuan</td>
                        <td>{{ $personalia->asisten_wni_pr }}</td>
                        <td>Orang</td>
                    </tr>
                    <tr>
                        <td>Jumlah</td>
                        <td>{{ $personalia->asisten_wni_jumlah }}</td>
                        <td>Orang</td>
                    </tr>
                </table>
            </td>

            {{-- TATA USAHA --}}
            <td style="font-size: 9pt">
                <table class="inner-table">
                    <tr>
                        <td>Laki-Laki</td>
                        <td>{{ $personalia->tata_usaha_wni_lk }}</td>
                        <td>Orang</td>
                    </tr>
                    <tr>
                        <td>Perempuan</td>
                        <td>{{ $personalia->tata_usaha_wni_pr }}</td>
                        <td>Orang</td>
                    </tr>
                    <tr>
                        <td>Jumlah</td>
                        <td>{{ $personalia->tata_usaha_wni_jumlah }}</td>
                        <td>Orang</td>
                    </tr>
                </table>
            </td>

            {{-- PESURUH --}}
            <td style="font-size: 9pt">
                <table class="inner-table">
                    <tr>
                        <td>Laki-Laki</td>
                        <td>{{ $personalia->pesuruh_wni_lk }}</td>
                        <td>Orang</td>
                    </tr>
                    <tr>
                        <td>Perempuan</td>
                        <td>{{ $personalia->pesuruh_wni_pr }}</td>
                        <td>Orang</td>
                    </tr>
                    <tr>
                        <td>Jumlah</td>
                        <td>{{ $personalia->pesuruh_wni_jumlah }}</td>
                        <td>Orang</td>
                    </tr>
                </table>
            </td>
        </tr>

        <tr>
            <td colspan="4" class="label-column">b. <span style="margin-left: 10px">Warga Negara Asing</span></td>
        </tr>

        <tr>
            <td style="width: 25%; font-size: 8pt; text-align: center" class="label-column">Sumber Belajar/Guru/Pengasuh</td>
            <td style="width: 25%; font-size: 8pt; text-align: center" class="label-column">Asisten Sumber Belajar/Guru</td>
            <td style="width: 25%; font-size: 8pt; text-align: center" class="label-column">Pegawai Tata Usaha</td>
            <td style="width: 25%; font-size: 8pt; text-align: center" class="label-column">Pesuruh</td>
        </tr>

        <tr style="font-size: 9pt">
            {{-- GURU --}}
            <td>
                <table class="inner-table">
                    <tr>
                        <td>Laki-Laki</td>
                        <td>{{ $personalia->guru_wna_lk }}</td>
                        <td>Orang</td>
                    </tr>
                    <tr>
                        <td>Perempuan</td>
                        <td>{{ $personalia->guru_wna_pr }}</td>
                        <td>Orang</td>
                    </tr>
                    <tr>
                        <td>Jumlah</td>
                        <td>{{ $personalia->guru_wna_jumlah }}</td>
                        <td>Orang</td>
                    </tr>
                </table>
            </td>

            {{-- ASISTEN --}}
            <td style="font-size: 9pt">
                <table class="inner-table">
                    <tr>
                        <td>Laki-Laki</td>
                        <td>{{ $personalia->asisten_wna_lk }}</td>
                        <td>Orang</td>
                    </tr>
                    <tr>
                        <td>Perempuan</td>
                        <td>{{ $personalia->asisten_wna_pr }}</td>
                        <td>Orang</td>
                    </tr>
                    <tr>
                        <td>Jumlah</td>
                        <td>{{ $personalia->asisten_wna_jumlah }}</td>
                        <td>Orang</td>
                    </tr>
                </table>
            </td>

            {{-- TATA USAHA --}}
            <td style="font-size: 9pt">
                <table class="inner-table">
                    <tr>
                        <td>Laki-Laki</td>
                        <td>{{ $personalia->tata_usaha_wna_lk }}</td>
                        <td>Orang</td>
                    </tr>
                    <tr>
                        <td>Perempuan</td>
                        <td>{{ $personalia->tata_usaha_wna_pr }}</td>
                        <td>Orang</td>
                    </tr>
                    <tr>
                        <td>Jumlah</td>
                        <td>{{ $personalia->tata_usaha_wna_jumlah }}</td>
                        <td>Orang</td>
                    </tr>
                </table>
            </td>

            {{-- PESURUH --}}
            <td style="font-size: 9pt">
                <table class="inner-table">
                    <tr>
                        <td>Laki-Laki</td>
                        <td>{{ $personalia->pesuruh_wna_lk }}</td>
                        <td>Orang</td>
                    </tr>
                    <tr>
                        <td>Perempuan</td>
                        <td>{{ $personalia->pesuruh_wna_pr }}</td>
                        <td>Orang</td>
                    </tr>
                    <tr>
                        <td>Jumlah</td>
                        <td>{{ $personalia->pesuruh_wna_jumlah }}</td>
                        <td>Orang</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- PROGRAM PENDIDIKAN -->
    <table class="form-header-table" style="page-break-inside: avoid">
        <tr>
            <td colspan="2" class="title-table">E. <span style="margin-left: 10px">PROGRAM PENDIDIKAN</span></td>
        </tr>

        <tr>
            <td style="width: 50%; text-align: center" class="label-column">Bahan Pembelajaran Berdasarkan Program</td>
            <td style="width: 50%; text-align: center" class="label-column">Cara Penyampaian Pelajaran</td>
        </tr>

        @php
            $bahan = $programPendidikan->bahan_pembelajaran ?? [];
            $cara = $programPendidikan->cara_penyampaian ?? [];
        @endphp

        <tr>
            <td>
                <table class="inner-table">
                    <tr>
                        <td class="content-column">Depdikbud</td>
                        <td><input type="checkbox" {{ in_array('depdikbud', $bahan) ? 'checked' : '' }} disabled></td>
                        <td><input type="checkbox" {{ in_array('lembaga_sendiri', $bahan) ? 'checked' : '' }} disabled></td>
                        <td class="content-column">Lembaga Sendiri</td>
                    </tr>

                    <tr>
                        <td class="content-column">Instansi Lain</td>
                        <td><input type="checkbox" {{ in_array('instansi_lain', $bahan) ? 'checked' : '' }} disabled></td>
                        <td><input type="checkbox" {{ in_array('lembaga_lain', $bahan) ? 'checked' : '' }} disabled></td>
                        <td class="content-column">Lembaga Lain</td>
                    </tr>
                </table>
            </td>

            <td>
                <table class="inner-table">
                    <tr>
                        <td class="content-column">Secara Langsung (Dengan Sumber Belajar/Guru)</td>
                        <td><input type="checkbox" {{ in_array('secara_langsung', $cara) ? 'checked' : '' }} disabled></td>
                    </tr>

                    <tr>
                        <td class="content-column">Korespondensi (Tertulis)</td>
                        <td><input type="checkbox" {{ in_array('korespondensi', $cara) ? 'checked' : '' }} disabled></td>
                    </tr>
                </table>
            </td>
        </tr>  
    </table>

    <!-- SARANA -->
    @php
        function renderRuang($data) {
            return [
                'milik_sendiri' => $data['milik_sendiri'] ?? '-',
                'kontrak' => $data['kontrak'] ?? '-',
                'sewa' => $data['sewa'] ?? '-',
                'pinjam' => $data['pinjam'] ?? '-',
                'beli_sewa' => $data['beli_sewa'] ?? '-',
                'jumlah_luas' => $data['jumlah_luas'] ?? '-',
            ];
        }
    @endphp

    <table class="form-header-table sarpras">
        <tr>
            <td colspan="7" class="title-table">G. <span style="margin-left: 10px">PRASARANA BELAJAR</span></td>
        </tr>

        <tr>
            <td style="padding: 5px 10px" class="label-column">a. <span style="margin-left: 10px">Prasarana</span</td>
            <td style="text-align: center" class="label-column">Milik Sendiri</td>
            <td style="text-align: center" class="label-column">Kontrak</td>
            <td style="text-align: center" class="label-column">Sewa</td>
            <td style="text-align: center" class="label-column">Pinjam</td>
            <td style="text-align: center" class="label-column">Beli - Sewa</td>
            <td style="text-align: center" class="label-column">Jumlah Luas Ruangan</td>
        </tr>

        <tbody>
            @php $ruangs = [
                'Ruang Belajar' => $prasarana->ruang_belajar,
                'Ruang Bermain' => $prasarana->ruang_bermain,
                'Ruang Pimpinan' => $prasarana->ruang_pimpinan,
                'Ruang Sumber Belajar' => $prasarana->ruang_sumber_belajar,
                'Ruang Guru' => $prasarana->ruang_guru,
                'Ruang Tata Usaha' => $prasarana->ruang_tata_usaha,
                'Kamar Mandi' => $prasarana->kamar_mandi,
                'Kamar Kecil' => $prasarana->kamar_kecil,
            ]; @endphp

            @foreach ($ruangs as $label => $item)
                @php $data = renderRuang($item); @endphp
                <tr>
                    <td style="padding: 5px 10px; width: 21%" class="label-column">{{ $label }}</td>
                    <td style="text-align: center; width: 12%">{{ $data['milik_sendiri'] }}</td>
                    <td style="text-align: center; width: 13%">{{ $data['kontrak'] }}</td>
                    <td style="text-align: center; width: 13%">{{ $data['sewa'] }}</td>
                    <td style="text-align: center; width: 13%">{{ $data['pinjam'] }}</td>
                    <td style="text-align: center; width: 13%">{{ $data['beli_sewa'] }}</td>
                    <td style="text-align: center; width: 15%">{{ $data['jumlah_luas'] }}&nbsp;m<sup style="font-size: 7pt">2</sup></td>
                </tr>
            @endforeach
        </tbody>
        
        <tr>
            <td colspan="2" style="padding: 5px 10px" class="label-column">b. <span style="margin-left: 10px">Sarana</span</td>
            <td style="text-align: center" class="label-column">Lebih Dari Cukup</td>
            <td style="text-align: center" class="label-column">Cukup</td>
            <td style="text-align: center" class="label-column">Sedang</td>
            <td style="text-align: center" class="label-column">Kurang</td>
            <td style="text-align: center" class="label-column">Tidak Ada</td>
        </tr>

        @php
            $opsi = ['lebih_dari_cukup', 'cukup', 'sedang', 'kurang', 'tidak_ada'];
            $fields = [
                'buku_pelajaran' => 'Buku Pelajaran/Sesuai Kurikulum',
                'alat_permainan_edukatif' => 'Alat Permainan Edukatif',
                'meja_kursi' => 'Meja+Kursi/Bangku untuk Belajar',
                'papan_tulis' => 'Papan Tulis',
                'alat_tata_usaha' => 'Alat Perlengkapan Tata Usaha',
                'listrik' => 'Listrik',
                'air_bersih' => 'Air Bersih',
            ];
        @endphp

        @foreach ($fields as $key => $label)
            @if (!empty($sarana->$key))
                <tr>
                    <td colspan="2" class="label-column">{{ $label }}</td>
                    @foreach ($opsi as $option)
                        <td style="text-align: center">
                            @if ($sarana->$key === $option)
                                <input class="checkbox-no-border" type="checkbox" checked disabled>
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endif
        @endforeach
    </table>

    {{-- TTD --}}
    {{-- <div style="margin-top: 100px; float: right; text-transform: uppercase">
        <div style="width: 100%; text-align: left;">
            <div>
                Badung, {{ formatValue($permohonan->tgl_permohonan) }}
            </div>
            <div>
                YAYASAN/PENGELOLA
            </div>
            <div style="margin-top: 80px; font-weight: bold;">
                {{ $permohonan->user->name }}
            </div>
        </div>
    </div> --}}

</body>
</html>