<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>SK Izin Operasional</title>
    <style>
        @page {
            margin: 2cm;
            font-size: 12pt;
        }

        table{
            width: 100%;
        }

        .kop-surat{
            margin: -.7cm;
        }

        .kop-surat table{
            text-align: center;
        }

        .kop-surat img{
            max-width: 100%; 
            max-height: 120px; 
            width: auto; 
            height: auto; 
            object-fit: contain;
        }

        .kop-keputusan p{
            text-align: center;
        }

        .table-ketentuan td{
            vertical-align: top;
            text-align: justify;
            line-height: 1.4;
        }

        .table-keputusan td{
            vertical-align: top;
            text-align: justify;
            line-height: 1.5;
        }

        .table-lembaga{
            margin: 15px 0px 5px -2px;
        }

        .table-lembaga td{
            line-height: 1.1;
        }

        .table-lembaga td:nth-child(1){
            text-align: left;
            width: 140px;
        }
        
        .table-lembaga td:nth-child(2){
            padding: 0 10px;
            text-align: center;
        }

        @page {
            @top-center {
                content: "";
            }
            counter-increment: page;
        }

        @page:first {
            @top-center {
                content: "";
            }
        }

        body::after {
            content: "- " counter(page) " -";
            position: fixed;
            top: -25px;
            width: 100%;
            text-align: center;
            font-size: 12pt;
        }

        @media print {
            body::after {
                counter-increment: page;
            }
            
            body::first-line {
                counter-reset: page 1;
            }
            
            body::first-letter {
                counter-reset: page 1;
            }
            
            body:first-of-type::after {
                content: none;
            }
            
            .first-page-marker {
                counter-reset: page 1;
            }
            
            .first-page-marker ~ *::after {
                content: "- " counter(page) " - ";
            }
        }
    </style>
</head>
<body>
    <span class="first-page-marker" style="display: none;"></span>

    <div class="kop-surat">

        <table>
            <tr>
                <td>
                    <img src="{{ $logo }}" alt="Logo">
                    </td>
                <td>
                    <span style="font-size: 14pt; font-weight: bold">PEMERINTAH KABUPATEN BADUNG</span> <br>
                    <span style="font-size: 16pt; font-weight: bold">DINAS PENDIDIKAN, KEPEMUDAAN DAN OLAH RAGA</span> <br>
                    <span style="font-size: 14pt; font-weight: bold">PUSAT PEMERINTAHAN MANGUPRAJA MANDALA</span> <br>
                    <span style="font-size: 10pt">JALAN RAYA SEMPIDI MENGWI-BADUNG BALI (80351)</span> <br>
                    <span style="font-size: 10pt">TELP ( 0361 ) 9009265/9009267,FAX : (0361)9009267</span> <br>
                    <span style="font-size: 10pt">Website : www.badungkab.go.id</span>
                </td>
            </tr>
        </table>
        
        <hr style="border: .3px solid #333">
        <hr style="border: .3px solid #333; margin-top: -5px; margin-bottom: 20px">

    </div>

    <div class="kop-keputusan">
        <p>
            KEPUTUSAN <br>
            KEPALA DINAS PENDIDIKAN, KEPEMUDAAN DAN OLAH RAGA <br>
            KABUPATEN BADUNG
        </p>

        <p>NOMOR 351 TAHUN 2025</p>

        <p>TENTANG</p>

        <p>IZIN OPERASIONAL / PENYELENGGARAAN <br> PENDIDIKAN ANAK USIA DINI </p>

        <p>KEPALA DINAS PENDIDIKAN, KEPEMUDAAN DAN OLAH RAGA <br> KABUPATEN BADUNG,</p>
    </div>

    <table class="table-ketentuan">
        <tr>
            <td>Menimbang</td>
            <td style="padding: 0 15px 0 10px; text-align: center;">:</td>
            <td style="padding: 0 5px">a.</td>
            <td>bahwa sesuai dengan surat permohonan Izin Operasional/Penyelenggaraan Pendidikan Anak Usia Dini (PAUD) dari {{ $pengelola->nama_pengelola }}/Ketua {{ $penyelenggara->nama_badan }}, Nomor {{ $permohonan->no_permohonan }}, tanggal {{ \Carbon\Carbon::parse($permohonan->tgl_permohonan)->locale('id')->translatedFormat('d F Y') }} dan berdasarkan Surat Rekomendasi dari {{ $permohonan->pemberi_rekomendasi }} Nomor {{ $permohonan->no_surat_rekomendasi }}, tanggal {{ \Carbon\Carbon::parse($permohonan->tgl_surat_rekomendasi)->locale('id')->translatedFormat('d F Y') }}, beserta lampiran-lampirannya;</td>
        </tr>

        <tr>
            <td style="padding-top: 15px"></td>
            <td style="padding-top: 15px"></td>
            <td style="padding: 15px 5px 0px 5px">b.</td>
            <td style="padding-top: 15px">bahwa berdasarkan laporan hasil Verifikasi Tim Survei Nomor {{ $permohonan->no_verifikasi }}, hari {{ \Carbon\Carbon::parse($permohonan->tgl_verifikasi)->locale('id')->translatedFormat('l') }}, tanggal {{ \Carbon\Carbon::parse($permohonan->tgl_verifikasi)->locale('id')->translatedFormat('d F Y') }} terhadap Operasioanal/Penyelenggaraan Pendidikan Anak Usia Dini (PAUD) tersebut dapat diberikan izin penyelenggaraan pendidikan;</td>
        </tr>

        <tr>
            <td style="padding-top: 15px"></td>
            <td style="padding-top: 15px"></td>
            <td style="padding: 15px 5px 0px 5px">c.</td>
            <td style="padding-top: 15px">bahwa berdasarkan pertimbangan sebagaimana dimaksud dalam huruf a dan huruf b, perlu menetapkan Keputusan Kepala Dinas Pendidikan, Kepemudaan dan Olah Raga tentang izin Operasional Penyelenggaraan Pendidikan Anak Usia Dini; </td>
        </tr>

        <tr>
            <td style="padding-top: 15px">Mengingat</td>
            <td style="padding-top: 15px; text-align: center;">:</td>
            <td style="padding: 15px 5px 0px 5px">1.</td>
            <td style="padding-top: 15px">Undang-Undang Nomor 4 Tahun 1979 tentang Kesejahteraan Anak (Lembaran Negara Republik Indonesia Tahun 1979 Nomor 32, Tambahan Lembaran Negara Republik Indonesia Nomor 3143);</td>
        </tr>

        <tr>
            <td style="padding-top: 15px"></td>
            <td style="padding-top: 15px"></td>
            <td style="padding: 15px 5px 0px 5px">2.</td>
            <td style="padding-top: 15px">Undang-Undang Nomor 23 Tahun 2002 Tentang Perlindungan Anak (Lembaran Negara Republik Indonesia Tahun 2002 Nomor 109, Tambahan Lembaran Negara Republik Indonesia Nomor 4235), Sebagaimana telah diubah dengan Undang-Undang Republik Indonesia Nomor 35 Tahun 2014 Tentang Perubahan Atas Undang Undang Nomor 23 Tahun 2002 Tentang Perlindungan Anak (Lembaran Negara Republik Indonesia Tahun 2014 Nomor 297, Tambahan Lembaran Negara Republik Indonesia Nomor 5606);</td>
        </tr>

        <tr>
            <td style="padding-top: 15px"></td>
            <td style="padding-top: 15px"></td>
            <td style="padding: 15px 5px 0px 5px">3.</td>
            <td style="padding-top: 15px">Undang-Undang Nomor 20 Tahun 2003 Tentang Sistem Pendidikan Nasional (Lembaran Negara Republik Indonesia Tahun 2003 Nomor 78, Tambahan Lembaran Negara Republik Indonesia Nomor 4301);</td>
        </tr>

        <tr>
            <td style="padding-top: 15px"></td>
            <td style="padding-top: 15px"></td>
            <td style="padding: 15px 5px 0px 5px">4.</td>
            <td style="padding-top: 15px">Undang-Undang Nomor 23 Tahun 2014 tentang Pemerintahan Daerah (Lembaran Negara Republik Indonesia Tahun 2014 Nomor 244, Tambahan Lembaran Negara Republik Indonesia Nomor 5587), sebagaimana telah diubah beberapa kali terakhir dengan Undang-Undang Nomor 9 Tahun 2015 tentang Perubahan kedua Atas Undang-Undang Nomor 23 Tahun 2014 tentang Pemerintahan Daerah (Lembaran Negara Republik Indonesia Tahun 2015 Nomor 58, Tambahan Lembaran Negara Republik Indonesia Nomor 5679);</td>
        </tr>

        <tr>
            <td style="padding-top: 15px"></td>
            <td style="padding-top: 15px"></td>
            <td style="padding: 15px 5px 0px 5px">5.</td>
            <td style="padding-top: 15px">Peraturan Pemerintah Nomor 17 Tahun 2010 Tentang Pengelolaan dan Penyelenggaraan Pendidikan (Lembaran Negara Republik Indonesia Tahun 2010 Nomor 23, Tambahan Lembaran Negara Republik Indonesia Nomor 5105) sebagaimana telah diubah dengan Peraturan Pemerintah Nomor 66 Tahun 2010 tentang Pengelolaan dan Penyelenggaraan Pendidikan (Lembaran Negara Republik Indonesia Tahun 2010 Nomor 112, Tambahan Lembaran Negara Republik Indonesia Nomor 5157);</td>
        </tr>

        <tr>
            <td style="padding-top: 15px"></td>
            <td style="padding-top: 15px"></td>
            <td style="padding: 15px 5px 0px 5px">6.</td>
            <td style="padding-top: 15px">Peraturan Menteri Pendidikan dan Kebudayaan Nomor 137 Tahun 2014 tentang Standar Nasional Pendidikan Anak Usia Dini ;</td>
        </tr>

        <tr>
            <td style="padding-top: 15px"></td>
            <td style="padding-top: 15px"></td>
            <td style="padding: 15px 5px 0px 5px">7.</td>
            <td style="padding-top: 15px"> Peraturan Menteri Pendidikan dan Kebudayaan Nomor 84 Tahun2014 tentang Pendirian Satuan Pendidikan Anak Usia Dini;</td>
        </tr>

        <tr>
            <td style="padding-top: 15px"></td>
            <td style="padding-top: 15px"></td>
            <td style="padding: 15px 5px 0px 5px">8.</td>
            <td style="padding-top: 15px">Keputusan Menteri Pendidikan Nasional Nomor 13 Tahun 2005 tentang Organisasi dan Tata Kerja Direktorat Jendral Pendidikan Luar Sekolah Departemen Pendidikan Nasional;</td>
        </tr>

        <tr>
            <td style="padding-top: 15px"></td>
            <td style="padding-top: 15px"></td>
            <td style="padding: 15px 5px 0px 5px">9.</td>
            <td style="padding-top: 15px">Peraturan Daerah Kabupaten Badung Nomor 9 Tahun 2018 tentang Sistem Penyelenggaraan Pendidikan di Kabupaten Badung;</td>
        </tr>
    </table>

    <p style="text-align: center">MEMUTUSKAN:</p>
    
    <table class="table-keputusan">
        <tr>
            <td>Menetapkan</td>
            <td style="padding: 0 15px; text-align: center;">:</td>
            <td></td>
        </tr>

        <tr>
            <td style="padding-top: 15px">KESATU</td>
            <td style="padding: 0 15px; padding-top: 15px; text-align: center;">:</td>
            <td style="padding-top: 15px">Memberikan izin Operasional / Penyelenggaraan Pendidikan Anak Usia Dini ( PAUD ) Kepada :</td>
        </tr>

        <tr>
            <td colspan="2"></td>
            <td>
                <table class="table-lembaga">
                    <tr>
                        <td>Nama Lembaga</td>
                        <td>:</td>
                        <td><strong>{{ $identitas->nama_lembaga }}</strong></td>
                    </tr>

                    @php
                        $jenisPendidikanLabels = [
                            'tk' => 'Taman Kanak-Kanak',
                            'kb' => 'Kelompok Bermain',
                            'tpa' => 'Tempat Penitipan Anak',
                            'sps' => 'Satuan PAUD Sejenis',
                            'kursus' => 'Kursus',
                        ];
                    @endphp

                    <tr>
                        <td>Jenis Pendidikan</td>
                        <td>:</td>
                        <td>{{ $jenisPendidikanLabels[$identitas->jenis_pendidikan] ?? '-' }}</td>
                    </tr>

                    <tr>
                        <td>Rumpun Pendidikan</td>
                        <td>:</td>
                        <td>{{ $identitas->rumpun_pendidikan }}</td>
                    </tr>

                    <tr>
                        <td>Alamat</td>
                        <td>:</td>
                        <td>
                            {{ Str::title(Str::lower($identitas->alamat_identitas)) }},
                            {{ Str::title(Str::lower($identitas->village->name)) }},
                            {{ Str::title(Str::lower($identitas->district->name)) }},
                            {{ Str::title(Str::lower($identitas->regency->name)) }}.
                        </td>
                    </tr>

                    <tr>
                        <td>Penyelenggara</td>
                        <td>:</td>
                        <td>{{ $penyelenggara->nama_badan }}</td>
                    </tr>
                </table>
            </td>
        </tr>

        <tr>
            <td style="padding-top: 15px">KEDUA</td>
            <td style="padding: 0 15px; padding-top: 15px; text-align: center;">:</td>
            <td style="padding-top: 15px">Izin penyelenggaraan sebagaimana dimaksud dalam diktum KESATU berlaku mulai tanggal {{ \Carbon\Carbon::parse($permohonan->tgl_permohonan)->locale('id')->translatedFormat('d F Y') }} sampai dengan adanya pencabutan izin.</td>
        </tr>        

        <tr>
            <td style="padding-top: 15px">KETIGA</td>
            <td style="padding: 0 15px; padding-top: 15px; text-align: center;">:</td>
            <td style="padding-top: 15px" colspan="2">Kewajiban yang harus dilaksanakan oleh pemegang izin :</td>
        </tr> 

        <tr>
            <td colspan="2"></td>
            <table>
                <tr>
                    <td style="padding-right: 10px">1.</td>
                    <td>Wajib menyelenggarakan Pendidikan Anak Usia Dini ( PAUD ) tersebut sedemikian rupa, sehingga dapat memenuhi fungsi sosialnya terhadap masyarakat.</td>
                </tr>

                <tr>
                    <td style="padding-right: 10px; padding-top: 15px">2.</td>
                    <td style="padding-top: 15px">Wajib mentaati peraturan perundang-undangan yang berlaku atau yang akan ditentukan kemudian.</td>
                </tr>

                <tr>
                    <td style="padding-right: 10px; padding-top: 15px">3.</td>
                    <td style="padding-top: 15px">Wajib mengirimkan laporan perkembangan pengelolaan Pendidikan Anak Usia Dini ( PAUD ) secara rutin setiap bulan.</td>
                </tr>
            </table>
        </tr>
        
        <tr>
            <td style="padding-top: 15px">KEEMPAT</td>
            <td style="padding: 0 15px; padding-top: 15px; text-align: center;">:</td>
            <td style="padding-top: 15px" colspan="2">Dengan ketentuan apabila dikemudian hari ternyata terdapat kekeliruan akan ditinjau kembali dan diperbaiki sebagaimana mestinya.</td>
        </tr> 

        <tr>
            <td style="padding-top: 15px">KELIMA</td>
            <td style="padding: 0 15px; padding-top: 15px; text-align: center;">:</td>
            <td style="padding-top: 15px" colspan="2">Keputusan ini mulai berlaku pada tanggal ditetapkan</td>
        </tr> 
    </table>

    <div style="text-align: right; width: 100%; margin-top: 50px;">
        <div style="display: inline-block; text-align: left; width: 350px;">
            <div>Ditetapkan di Mangupura</div>
            <div style="margin-bottom: 20px;">Pada tanggal {{ \Carbon\Carbon::parse($permohonan->tgl_status_terakhir)->locale('id')->translatedFormat('d F Y') }}</div>
            
            <div style="margin-bottom: 5px;">
                KEPALA DINAS PENDIDIKAN, KEPEMUDAAN<br>
                DAN OLAH RAGA KABUPATEN BADUNG,
            </div>
            
            <div style="height: 100px;"></div>
            
            <div>
                <div style="text-decoration: underline;">I GUSTI MADE DWIPAYAMA, SH.M.Si.</div>
                <div>PEMBINA UTAMA MUDA, IV/c</div>
                <div>Nip. 19701106 199603 1 002</div>
            </div>
        </div>
    </div>
    
    <table style="margin-top: 50px">
        <tr>
            <td colspan="2"><u>Keputusan ini disampaikan kepada :</u></td>
        </tr>
        <tr>
            <td style="width: 10px; padding-right: 5px;">1.</td>
            <td>Dirjen PAUD dan DIKMAS di Jakarta.</td>
        </tr>
        <tr>
            <td style="width: 10px; padding-right: 5px;">2.</td>
            <td>Bupati Badung.</td>
        </tr>
        <tr>
            <td style="width: 10px; padding-right: 5px;">3.</td>
            <td>Kepala Dinas Pendidikan Propinsi Badung.</td>
        </tr>
        <tr>
            <td style="width: 10px; padding-right: 5px;">4.</td>
            <td>Camat Kuta Utara.</td>
        </tr>
        <tr>
            <td style="width: 10px; padding-right: 5px;">5.</td>
            <td>Kepala UPT.Dinas Pendidikan, Kepemudaan dan Olah Raga, Kec. Kuta Utara</td>
        </tr>
        <tr>
            <td style="width: 10px; padding-right: 5px;">6.</td>
            <td>Yang bersangkutan untuk dilaksanakan sebagaimana mestinya.</td>
        </tr>
    </table>
    
</body>
</html>