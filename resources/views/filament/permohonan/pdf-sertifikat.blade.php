<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>SK Izin Operasional</title>
    <style>
        @page {
            margin: 1.5cm 7cm;
            font-size: 11pt;
        }

        .bg-sertifikat {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 80%;
            height: 80%;
            object-fit: cover;
            z-index: -1;
            opacity: 0.1;
        }

        .kop-surat{
            margin: 0 auto;
        }

        .kop-surat table{
            text-align: center;
            width: 100%;
        }

        .kop-surat img{
            max-width: 100%; 
            max-height: 120px; 
            width: auto; 
            height: auto; 
            object-fit: contain;
        }

        .kop-keputusan{
            margin-top: -20px;
        }

        .kop-keputusan p{
            text-align: center;
            line-height: 1.5;
            font-weight: bold;
        }

        .lembaga{
            width: 100%;
            text-align: justify;
        }

        .lembaga td:nth-child(2){
            width: 10%;
            text-align: center;
        }
    </style>
</head>
<body>
    <img src="{{ $background }}" alt="Background" class="bg-sertifikat">

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
            <span style="font-size: 16pt">IZIN PENYELENGGARAAN PAUD</span> <br>
            SURAT KEPUTUSAN KEPALA DINAS PENDIDIKAN, KEPEMUDAAN DAN OLAH RAGA KABUPATEN BADUNG <br> 
            NOMOR 351 TAHUN 2022
        </p>
    </div>

    <table class="lembaga" style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="font-style: italic; font-weight: bold;">Diberikan Kepada</td>
            <td style="width: 10px;">:</td> <!-- ini dibatasi lebarnya -->
            <td></td>
        </tr>

        <tr>
            <td style="width: 150px;">Nama PAUD</td>
            <td style="width: 30px;">:</td> <!-- width kecil -->
            <td style="font-weight: bold;">{{ $identitas->nama_lembaga }}</td>
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
            <td>Rumpun Pendidikan</td>
            <td style="width: 10px;">:</td>
            <td>{{ $identitas->rumpun_pendidikan }}</td>
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
            <td style="font-weight: bold">{{ $jenisPendidikanLabels[$identitas->jenis_pendidikan] ?? '-' }}</td>
        </tr>

        <tr>
            <td>Penyelenggara</td>
            <td style="width: 10px;">:</td>
            <td>{{ $penyelenggara->nama_badan }}</td>
        </tr>

        <tr>
            <td colspan="3" style="padding-top: 12px;">
                Dengan memperhatikan Permendikbud No.84 Tahun 2014 Tentang Pendirian Satuan Pendidikan Anak Usia Dini, serta syarat-syarat yang ditetapkan dalam surat Keputusan Nomor 351 Tahun 2022, maka izin operasionalnya berlaku terhitung mulai tanggal {{ \Carbon\Carbon::parse($permohonan->tgl_status_terakhir)->locale('id')->translatedFormat('d F Y') }}, dengan catatan bahwa izin ini sewaktu-waktu dapat dicabut bila menyimpang dari aturan yang berlaku.
            </td>
        </tr>
    </table>


    <div style="text-align: right; width: 100%; margin-top: 50px;">
        <div style="display: inline-block; text-align: center; width: 350px;">
            <div>Mangupura, {{ \Carbon\Carbon::parse($permohonan->tgl_status_terakhir)->locale('id')->translatedFormat('d F Y') }}</div>
            <div>Kepala Dinas Pendidikan, Kepemudaan dan Olah Raga Kabupaten Badung</div>

            <div style="height: 100px;"></div>
            
            <div>
                <div style="text-decoration: underline;">I Gusti Made Dwipayana, SH,M.Si.</div>
                <div>Pembina Utama Muda, IV/c</div>
                <div>NIP. 19701106 199603 1 002</div>
            </div>
        </div>
    </div>
    
</body>
</html>