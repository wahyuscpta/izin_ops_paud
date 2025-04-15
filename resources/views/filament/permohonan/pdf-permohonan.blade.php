<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Formulir Permohonan Izin Operasional</title>
    <style>
        @page {
            margin: 2cm 2cm 2cm 2.5cm;
        }

        body {
            font-family: 'Times New Roman', serif;
            font-size: 12pt;
            color: #000;
            line-height: 2;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1cm;
        }

        td {
            vertical-align: top;
            padding: 0px 4px;
        }

        .title-table{
            font-weight: bold;
            padding-bottom: 10px;
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
    @endphp

    <table width="100%" style="margin-bottom: 30px;">
        <tr>
            <!-- Kolom kiri -->
            <td style="vertical-align: top; width: 60%; padding: 0px">
                <table>
                    <tr>
                        <td style="width: 80px; padding: 0px">Nomor</td>
                        <td style="width: 10px; padding: 0px">:</td>
                        <td style="padding: 0px">{{ $permohonan->no_permohonan ?? '...' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 0px">Perihal</td>
                        <td style="padding: 0px">:</td>
                        <td style="padding: 0px">Permohonan Ijin Operasional PAUD & PNF</td>
                    </tr>
                </table>
            </td>

            <!-- Kolom kanan -->
            <td style="vertical-align: top;line-height: 1.8; padding: 0px">
                Kepada Yth.<br>
                Kepala Dinas Pendidikan, Kepemudaan dan Olahraga
                Kabupaten Badung
            </td>
        </tr>
    </table>

    <!-- IDENTITAS -->

    <table>
        <tr>
            <td class="title-table">A. IDENTITAS</td>
        </tr>
        
        @foreach ([
            'Nama Lembaga' => $identitas->nama_lembaga,
            'Alamat Jalan' => $identitas->alamat_identitas,
            'Telepon' => $identitas->no_telepon_identitas,
            'Desa/Kelurahan' => $identitas->village->name,
            'Kecamatan' => $identitas->village->district->name,
            'Kabupaten' => $identitas->village->district->regency->name,
            'Didirikan Pada Tanggal' => $identitas->tgl_didirikan,
            'Penyelenggaraan Terdaftar Sejak' => $identitas->tgl_terdaftar,
            'Nomor Registrasi' => $identitas->no_registrasi,
            'Nomor Surat Keputusan' => $identitas->no_surat_keputusan,
        ] as $label => $value)
            @php
                if ($value instanceof \Carbon\Carbon || \Carbon\Carbon::hasFormat($value, 'Y-m-d')) {
                    $value = \Carbon\Carbon::parse($value)->translatedFormat('d F Y');
                } elseif (is_string($value)) {
                    $value = ucwords(strtolower($value));
                }
            @endphp
            <tr>
                <td style="width: 30%;">{{ $label }}</td>
                <td style="width: 5%;">:</td>
                <td style="width: 65%;">{{ $value }}</td>
            </tr>
        @endforeach

        <tr>
            <td>Rumpun Pendidikan</td>
            <td>:</td>
            <td>{{ $identitas->rumpun_pendidikan }}</td>
        </tr>
        <tr>
            <td>Jenis Pendidikan</td>
            <td>:</td>
            <td>{{ ucwords(strtolower($identitas->jenis_pendidikan)) }}</td>
        </tr>
        <tr>
            <td>Lembaga Ini Merupakan</td>
            <td>:</td>
            <td>{{ $identitas->jenis_lembaga === 'induk' ? 'Induk' : 'Cabang' }}</td>
        </tr>

        @if ($identitas->jenis_lembaga === 'induk')
            <tr>
                <td>Mempunyai Cabang</td>
                <td>:</td>
                <td>{{ $identitas->has_cabang ? 'Ya' : 'Tidak' }}</td>
            </tr>

            @if ($identitas->has_cabang)
                <tr>
                    <td>Jumlah Cabang</td>
                    <td>:</td>
                    <td>{{ $identitas->jumlah_cabang }} lembaga</td>
                </tr>
                <tr>
                    <td colspan="3" style="padding-top: 10px">
                        Daftar Nama dan Alamat Cabang :
                        <table class="sub-table">
                            @foreach ($identitas->cabangs as $index => $cabang)
                                <tr>
                                    <td style="width: 5%;">{{ $index + 1 }}.</td>
                                    <td>{{ $cabang->nama_lembaga_cabang }}</td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td>{{ $cabang->alamat_lembaga_cabang }}</td>
                                </tr>
                            @endforeach
                        </table>
                    </td>
                </tr>
            @endif

        @else
            <tr>
                <td>Nama Lembaga Induk</td>
                <td>:</td>
                <td>{{ $identitas->nama_lembaga_induk }}</td>
            </tr>
            <tr>
                <td>Alamat Lembaga Induk</td>
                <td>:</td>
                <td>{{ $identitas->alamat_lembaga_induk }}</td>
            </tr>
        @endif
    </table>

    <!-- PENYELENGGARA -->

    <table>
        <tr>
            <td class="title-table" colspan="3">B. PENYELENGGARA/YAYASAN</td>
        </tr>

        <tr>            
            <td><strong>Perorangan</strong></td>
        </tr>

        @foreach ([
            'Nama Lengkap' => $penyelenggara->nama_perorangan,
            'Agama' => $penyelenggara->agama_perorangan,
            'Kewarganegraan' => $penyelenggara->kewarganegaraan_perorangan,
            'No KTP' => $penyelenggara->ktp_perorangan,
            'Tanggal' => $penyelenggara->tanggal_perorangan,
            'Alamat Lengkap' => $penyelenggara->alamat_perorangan,
            'Telepon' => $penyelenggara->telepon_perorangan,
            'Kabupaten/Kota' => $penyelenggara->regencyPerorangan->name,
        ] as $label => $value)
            <tr>
                <td style="width: 30%;">{{ $label }}</td>
                <td style="width: 5%;">:</td>
                <td style="width: 65%;">{{ ucwords(strtolower($value)) }}</td>
            </tr>
        @endforeach

        <tr>            
            <td style="padding-top: 20px"><strong>Badan Hukum</strong></td>
        </tr>

        @foreach ([
            'Nama Lengkap' => $penyelenggara->nama_badan,
            'Agama' => $penyelenggara->agama_badan,
            'Akte' => $penyelenggara->akte_badan,
            'Nomor' => $penyelenggara->nomor_badan,
            'Tanggal' => $penyelenggara->tanggal_badan,
            'Alamat Lengkap' => $penyelenggara->alamat_badan,
            'Telepon' => $penyelenggara->telepon_badan,
            'Kabupaten/Kota' => $penyelenggara->regencyBadan->name,
        ] as $label => $value)
            <tr>
                <td style="width: 30%;">{{ $label }}</td>
                <td style="width: 5%;">:</td>
                <td style="width: 65%;">{{ ucwords(strtolower($value)) }}</td>
            </tr>
        @endforeach
    </table>

    <!-- PENGELOLA -->

    <table>
        <tr>
            <td class="title-table" colspan="3">C. PENGELOLA/PENANGGUNG JAWAB TEKNIS EDUKATIF</td>
        </tr>

        @foreach ([
            'Nama Lengkap' => $pengelola->nama_pengelola,
            'Agama' => $pengelola->agama_pengelola,
            'Jenis Kelamin' => $pengelola->jenis_kelamin_pengelola,
            'Kewarganegraan' => $pengelola->kewarganegaraan_pengelola,
            'No KTP' => $pengelola->ktp_pengelola,
            'Tanggal' => $pengelola->tanggal_pengelola,
            'Alamat Lengkap' => $pengelola->alamat_pengelola,
            'Telepon' => $pengelola->telepon_pengelola,
            'Kabupaten/Kota' => $pengelola->regency->name,
        ] as $label => $value)
            <tr>
                <td style="width: 30%;">{{ $label }}</td>
                <td style="width: 5%;">:</td>
                <td style="width: 65%;">{{ ucwords(strtolower($value)) }}</td>
            </tr>
        @endforeach
    </table>

    <!-- PESERTA DIDIK -->
    
    <table>
        <tr>
            <td class="title-table" colspan="4">D. WARGA BELAJAR/PESERTA DIDIK</></td>
        </tr>
        <tr>
            <td style="width: 65%;">Penerimaan melalui tes</td>
            <td style="width: 5%;">:</td>
            <td style="width: 30%;">{{ ucfirst($pesertaDidik->jalur_penerimaan_tes) }}</td>
        </tr>
        <tr>                    
            <td style="width: 65%;">Tata Usaha Penerimaan</td>
            <td style="width: 5%;">:</td>
            <td style="width: 30%;">{{ ucfirst($pesertaDidik->tata_usaha_penerimaan) }}</td>
        </tr>
        <tr>
            <td style="width: 65%;">Jumlah Setiap Kelompok/Angkatan</td>
            <td style="width: 5%;">:</td>
            <td style="width: 30%;">Rata-Rata {{ $pesertaDidik->jumlah_tiap_angkatan }}&nbsp;Orang</td>
        </tr>
        <tr>
            <td style="width: 65%;">Yang menyelesaikan Program Pendidikan sampai akhir</td> 
            <td style="width: 5%;">:</td>
            <td style="width: 30%;">Rata-Rata {{ $pesertaDidik->jumlah_menyelesaikan }}&nbsp;%</td>
        </tr>
    </table>

    <table border="1">
        <tr>
            <td rowspan="2" align="center">Tingkat</td>
            <td colspan="3" align="center">Keadaan Sekarang</td>
            <td colspan="3" align="center">Yang Telah Tamat</td>
        </tr>
        <tr>
            <td align="center">Laki-laki</td>
            <td align="center">Perempuan</td>
            <td align="center">Jumlah</td>
            <td align="center">Laki-laki</td>
            <td align="center">Perempuan</td>
            <td align="center">Jumlah</td>
        </tr>
        <tr>
            <td align="center">Jumlah Seluruhnya</td>
            <td align="center">{{ $pesertaDidik->jumlah_sekarang_lk }}</td>
            <td align="center">{{ $pesertaDidik->jumlah_sekarang_pr }}</td>
            <td align="center">{{ $pesertaDidik->jumlah_sekarang_total }}</td>
            <td align="center">{{ $pesertaDidik->jumlah_tamat_lk }}</td>
            <td align="center">{{ $pesertaDidik->jumlah_tamat_pr }}</td>
            <td align="center">{{ $pesertaDidik->jumlah_tamat_total }}</td>
        </tr>
    </table>

    <!-- PERSONALIA -->

    <table style="margin-bottom: 20px">
        <tr>
            <th colspan="4" style="text-align: left;">
                <h4 style="margin: 0;">E. PERSONALIA (PERINCIAN TERLAMPIR)</h4>
            </th>
        </tr>
    </table>

    <table border="1" style="margin-bottom: 20px">
        <tr>
            <td colspan="4" style="padding: 10px; line-height: 1">A. Warga Negara Indonesia</td>
        </tr>

        <tr>
            <td align="center" style="padding: 10px; line-height: 1; width: 25%; font-size: 12px; font-weight: normal">Sumber Belajar/Guru/Pengasuh</td>
            <td align="center" style="padding: 10px; line-height: 1; width: 25%; font-size: 12px; font-weight: normal">Asisten Sumber Belajar/Guru</td>
            <td align="center" style="padding: 10px; line-height: 1; width: 25%; font-size: 12px; font-weight: normal">Pegawai Tata Usaha</td>
            <td align="center" style="padding: 10px; line-height: 1; width: 25%; font-size: 12px; font-weight: normal">Pesuruh</td>
        </tr>

        <tr>
            {{-- GURU --}}
            <td style="vertical-align: top; padding: 10px">
                <table border="0" style="font-size: 12px; margin: 0;">
                    <tr>
                        <td style="padding: 0px">Laki-Laki</td>
                        <td style="padding: 0px">{{ $personalia->guru_wni_lk }}</td>
                        <td style="padding: 0px">Orang</td>
                    </tr>
                    <tr>
                        <td style="padding: 0px">Perempuan</td>
                        <td style="padding: 0px">{{ $personalia->guru_wni_pr }}</td>
                        <td style="padding: 0px">Orang</td>
                    </tr>
                    <tr>
                        <td style="padding: 0px">Jumlah</td>
                        <td style="padding: 0px">{{ $personalia->guru_wni_jumlah }}</td>
                        <td style="padding: 0px">Orang</td>
                    </tr>
                </table>
            </td>

            {{-- ASISTEN --}}
            <td style="vertical-align: top; padding: 10px">
                <table border="0" style="font-size: 12px; margin: 0;">
                    <tr>
                        <td style="padding: 0px">Laki-Laki</td>
                        <td style="padding: 0px">{{ $personalia->asisten_wni_lk }}</td>
                        <td style="padding: 0px">Orang</td>
                    </tr>
                    <tr>
                        <td style="padding: 0px">Perempuan</td>
                        <td style="padding: 0px">{{ $personalia->asisten_wni_pr }}</td>
                        <td style="padding: 0px">Orang</td>
                    </tr>
                    <tr>
                        <td style="padding: 0px">Jumlah</td>
                        <td style="padding: 0px">{{ $personalia->asisten_wni_jumlah }}</td>
                        <td style="padding: 0px">Orang</td>
                    </tr>
                </table>
            </td>

            {{-- TATA USAHA --}}
            <td style="vertical-align: top; padding: 10px">
                <table border="0" style="font-size: 12px; margin: 0;">
                    <tr>
                        <td style="padding: 0px">Laki-Laki</td>
                        <td style="padding: 0px">{{ $personalia->tata_usaha_wni_lk }}</td>
                        <td style="padding: 0px">Orang</td>
                    </tr>
                    <tr>
                        <td style="padding: 0px">Perempuan</td>
                        <td style="padding: 0px">{{ $personalia->tata_usaha_wni_pr }}</td>
                        <td style="padding: 0px">Orang</td>
                    </tr>
                    <tr>
                        <td style="padding: 0px">Jumlah</td>
                        <td style="padding: 0px">{{ $personalia->tata_usaha_wni_jumlah }}</td>
                        <td style="padding: 0px">Orang</td>
                    </tr>
                </table>
            </td>

            {{-- PESURUH --}}
            <td style="vertical-align: top; padding: 10px">
                <table border="0" style="font-size: 12px; margin: 0;">
                    <tr>
                        <td style="padding: 0px">Laki-Laki</td>
                        <td style="padding: 0px">{{ $personalia->pesuruh_wni_lk }}</td>
                        <td style="padding: 0px">Orang</td>
                    </tr>
                    <tr>
                        <td style="padding: 0px">Perempuan</td>
                        <td style="padding: 0px">{{ $personalia->pesuruh_wni_pr }}</td>
                        <td style="padding: 0px">Orang</td>
                    </tr>
                    <tr>
                        <td style="padding: 0px">Jumlah</td>
                        <td style="padding: 0px">{{ $personalia->pesuruh_wni_jumlah }}</td>
                        <td style="padding: 0px">Orang</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table border="1">
        <tr>
            <td colspan="4" style="padding: 10px; line-height: 1">B. Warga Negara Asing</td>
        </tr>

        <tr>
            <td align="center" style="padding: 10px; line-height: 1; width: 25%; font-size: 12px; font-weight: normal">Sumber Belajar/Guru/Pengasuh</td>
            <td align="center" style="padding: 10px; line-height: 1; width: 25%; font-size: 12px; font-weight: normal">Asisten Sumber Belajar/Guru</td>
            <td align="center" style="padding: 10px; line-height: 1; width: 25%; font-size: 12px; font-weight: normal">Pegawai Tata Usaha</td>
            <td align="center" style="padding: 10px; line-height: 1; width: 25%; font-size: 12px; font-weight: normal">Pesuruh</td>
        </tr>

        <tr>
            {{-- GURU --}}
            <td style="vertical-align: top; padding: 10px">
                <table border="0" style="font-size: 12px; margin: 0;">
                    <tr>
                        <td style="padding: 0px">Laki-Laki</td>
                        <td style="padding: 0px">{{ $personalia->guru_wna_lk }}</td>
                        <td style="padding: 0px">Orang</td>
                    </tr>
                    <tr>
                        <td style="padding: 0px">Perempuan</td>
                        <td style="padding: 0px">{{ $personalia->guru_wna_pr }}</td>
                        <td style="padding: 0px">Orang</td>
                    </tr>
                    <tr>
                        <td style="padding: 0px">Jumlah</td>
                        <td style="padding: 0px">{{ $personalia->guru_wna_jumlah }}</td>
                        <td style="padding: 0px">Orang</td>
                    </tr>
                </table>
            </td>

            {{-- ASISTEN --}}
            <td style="vertical-align: top; padding: 10px">
                <table border="0" style="font-size: 12px; margin: 0;">
                    <tr>
                        <td style="padding: 0px">Laki-Laki</td>
                        <td style="padding: 0px">{{ $personalia->asisten_wna_lk }}</td>
                        <td style="padding: 0px">Orang</td>
                    </tr>
                    <tr>
                        <td style="padding: 0px">Perempuan</td>
                        <td style="padding: 0px">{{ $personalia->asisten_wna_pr }}</td>
                        <td style="padding: 0px">Orang</td>
                    </tr>
                    <tr>
                        <td style="padding: 0px">Jumlah</td>
                        <td style="padding: 0px">{{ $personalia->asisten_wna_jumlah }}</td>
                        <td style="padding: 0px">Orang</td>
                    </tr>
                </table>
            </td>

            {{-- TATA USAHA --}}
            <td style="vertical-align: top; padding: 10px">
                <table border="0" style="font-size: 12px; margin: 0;">
                    <tr>
                        <td style="padding: 0px">Laki-Laki</td>
                        <td style="padding: 0px">{{ $personalia->tata_usaha_wna_lk }}</td>
                        <td style="padding: 0px">Orang</td>
                    </tr>
                    <tr>
                        <td style="padding: 0px">Perempuan</td>
                        <td style="padding: 0px">{{ $personalia->tata_usaha_wna_pr }}</td>
                        <td style="padding: 0px">Orang</td>
                    </tr>
                    <tr>
                        <td style="padding: 0px">Jumlah</td>
                        <td style="padding: 0px">{{ $personalia->tata_usaha_wna_jumlah }}</td>
                        <td style="padding: 0px">Orang</td>
                    </tr>
                </table>
            </td>

            {{-- PESURUH --}}
            <td style="vertical-align: top; padding: 10px">
                <table border="0" style="font-size: 12px; margin: 0;">
                    <tr>
                        <td style="padding: 0px">Laki-Laki</td>
                        <td style="padding: 0px">{{ $personalia->pesuruh_wna_lk }}</td>
                        <td style="padding: 0px">Orang</td>
                    </tr>
                    <tr>
                        <td style="padding: 0px">Perempuan</td>
                        <td style="padding: 0px">{{ $personalia->pesuruh_wna_pr }}</td>
                        <td style="padding: 0px">Orang</td>
                    </tr>
                    <tr>
                        <td style="padding: 0px">Jumlah</td>
                        <td style="padding: 0px">{{ $personalia->pesuruh_wna_jumlah }}</td>
                        <td style="padding: 0px">Orang</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- PROGRAM PENDIDIKAN -->

    <table width="100%" style="margin-top: 20px; border-collapse: collapse;">
        <tr>
            <th colspan="4" style="text-align: left;">
                <h4 style="margin: 0;">F. PROGRAM PENDIDIKAN</h4>
            </th>
        </tr>
    </table>

    <table border="1" cellpadding="4" cellspacing="0" style="width: 100%; border-collapse: collapse; margin-top: 10px;">
        <tr>
            <td style="padding: 5px 10px">Bahan Pembelajaran Berdasarkan Program</td>
            <td>Cara Penyampaian/Penyajian Pelajaran</td>
        </tr>

        <tr>
            <td>
                <ul style="margin: 0; padding-left: 10px;">
                    @foreach ($programPendidikan->bahan_pembelajaran as $item)
                        <li style="list-style-type: none">{{ ucwords(str_replace('_', ' ', $item)) }}</li>
                    @endforeach
                </ul>
            </td>

            <td>
                <ul style="margin: 0; padding-left: 0px">
                    @foreach ($programPendidikan->cara_penyampaian as $item)
                        <li style="list-style-type: none">{{ ucwords(str_replace('_', ' ', $item)) }}</li>
                    @endforeach
                </ul>
            </td>
        </tr>
    </table>

    <div class="page-break"></div>

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

    <table width="100%" style="margin-top: 20px; border-collapse: collapse;">
        <tr>
            <th colspan="4" style="text-align: left;">
                <h4 style="margin: 0;">F. SARANA BELAJAR</h4>
            </th>
        </tr>
    </table>

    <table border="1" cellpadding="4" cellspacing="0" style="width: 100%; border-collapse: collapse; margin-top: 10px;">
        <tr>
            <td style="padding: 5px 10px">A. Prasarana</td>
            <td align="center">Milik Sendiri</td>
            <td align="center">Kontrak</td>
            <td align="center">Sewa</td>
            <td align="center">Pinjam</td>
            <td align="center">Beli - Sewa</td>
            <td align="center">Jumlah Luas Ruangan</td>
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
                    <td style="padding: 5px 10px">{{ $label }}</td>
                    <td align="center">{{ $data['milik_sendiri'] }}</td>
                    <td align="center">{{ $data['kontrak'] }}</td>
                    <td align="center">{{ $data['sewa'] }}</td>
                    <td align="center">{{ $data['pinjam'] }}</td>
                    <td align="center">{{ $data['beli_sewa'] }}</td>
                    <td align="center">{{ $data['jumlah_luas'] }}&nbsp;m<sup>2</sup></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table border="1" cellpadding="4" cellspacing="0" style="width: 100%; border-collapse: collapse; margin-top: 20px;">
        <thead>
            <tr>
                <td style="padding: 5px 10px; width: 50%">B. Sarana</td>
                <td align="center">Keterangan</td>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="padding: 5px 10px">Buku Pelajaran/Sesuai Kurikulum</td>
                <td>{{ ucfirst(str_replace('_', ' ', $sarana->buku_pelajaran)) }}</td>
            </tr>
            <tr>
                <td style="padding: 5px 10px">Alat Permainan Edukatif</td>
                <td>{{ ucfirst(str_replace('_', ' ', $sarana->alat_permainan_edukatif)) }}</td>
            </tr>
            <tr>
                <td style="padding: 5px 10px">Meja+Kursi/Bangku Untuk Belajar</td>
                <td>{{ ucfirst(str_replace('_', ' ', $sarana->meja_kursi)) }}</td>
            </tr>
            <tr>
                <td style="padding: 5px 10px">Papan Tulis</td>
                <td>{{ ucfirst(str_replace('_', ' ', $sarana->papan_tulis)) }}</td>
            </tr>
            <tr>
                <td style="padding: 5px 10px">Alat Perlengkapan Tata Usaha</td>
                <td>{{ ucfirst(str_replace('_', ' ', $sarana->alat_tata_usaha)) }}</td>
            </tr>
            <tr>
                <td style="padding: 5px 10px">Listrik</td>
                <td>{{ ucfirst(str_replace('_', ' ', $sarana->listrik)) }}</td>
            </tr>
            <tr>
                <td style="padding: 5px 10px">Air Bersih</td>
                <td>{{ ucfirst(str_replace('_', ' ', $sarana->air_bersih)) }}</td>
            </tr>
        </tbody>

    </table>

</body>
</html>