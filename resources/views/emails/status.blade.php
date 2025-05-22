<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Permohonan - Disdikpora Kabupaten Badung</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
            color: #333;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            -webkit-text-size-adjust: 100%;
        }

        .wrapper {
            width: 100%;
            padding: 40px 15px;
            margin: 0 auto;
        }

        .container {
            max-width: 750px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08), 0 2px 8px rgba(0, 0, 0, 0.04);
            overflow: hidden;
        }

        .header-bar {
            height: 6px;
            background: #4361ee;
            width: 100%;
        }

        .content {
            padding: 35px;
        }

        .logo {
            margin-bottom: 25px;
            display: flex;
            align-items: center;
        }

        .logo-icon {
            width: 24px;
            height: 24px;
            margin-right: 12px;
        }

        .logo-text {
            font-weight: bold;
            font-size: 18px;
        }
        
        .logo-text-mobile {
            display: none;
        }

        h1 {
            font-size: 22px;
            margin-bottom: 22px;
            color: #333;
            line-height: 1.4;
        }

        p {
            line-height: 1.6;
            margin-bottom: 22px;
            color: #555;
            font-size: 16px;
        }

        /* .button-container {
            text-align: center;
            margin: 28px 0; 
        } */

        .button {
            display: inline-block;
            background-color: #4361ee;
            color: #fff !important;
            padding: 14px 28px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            box-shadow: 0 2px 6px rgba(67, 97, 238, 0.3);
            transition: all 0.2s ease;
            font-size: 16px;
            min-width: 200px;
        }

        .button:hover {
            box-shadow: 0 4px 10px rgba(67, 97, 238, 0.4);
            background-color: #3651d4;
        }

        .help {
            background-color: #f9f9fb;
            border-radius: 8px;
            padding: 16px;
            margin: 22px 0;
            word-break: break-all;
        }

        .help p {
            font-size: 14px;
            color: #0056b3;
            margin-bottom: 0;
            word-break: break-all;
        }

        .signature {
            margin-top: 30px;
            font-size: 14px;
            color: #777;
            font-style: italic;
        }

        .footer {
            padding: 20px 30px;
            border-top: 1px solid #eee;
            color: #888;
            font-size: 13px;
            text-align: center;
        }
        
        .status-indicator {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 50px;
            color: white;
            font-weight: 500;
            font-size: 14px;
            margin-bottom: 15px;
        }
        
        .status-diajukan {
            background-color: #4361ee;
        }
        
        .status-verifikasi {
            background-color: #f9a826;
            color: #333;
        }
        
        .status-disetujui {
            background-color: #2ecc71;
        }
        
        .status-ditolak {
            background-color: #e74c3c;
        }
        
        .status-revisi {
            background-color: #9b59b6;
        }
        
        .status-detail {
            background-color: #f8f9fa;
            border-left: 4px solid #4361ee;
            padding: 18px;
            margin: 22px 0;
            border-radius: 6px;
        }
        
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        
        .info-table td {
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
            vertical-align: top;
        }
        
        .info-table td:first-child {
            color: #777;
            width: 40%;
            font-size: 15px;
        }
        
        .info-table td:last-child {
            font-weight: 500;
            color: #333;
            font-size: 15px;
        }
        
        @media only screen and (max-width: 768px) {
            .wrapper {
                padding: 30px 15px;
            }
            
            .content {
                padding: 30px 25px;
            }
        }

        @media only screen and (max-width: 600px) {
            .wrapper {
                padding: 20px 12px;
            }
            
            .container {
                border-radius: 8px;
            }
            
            .content {
                padding: 25px 20px;
            }
            
            .logo {
                margin-bottom: 22px;
            }
            
            .logo-text {
                font-size: 16px;
                display: none;
            }
            
            .logo-text-mobile {
                display: block;
                font-weight: bold;
                font-size: 16px;
            }
            
            h1 {
                font-size: 20px;
                margin-bottom: 18px;
            }
            
            p {
                font-size: 16px;
                margin-bottom: 18px;
                line-height: 1.5;
            }
            
            /* .button-container {
                margin: 24px 0;
            } */
            
            .button {
                display: block;
                width: 100%;
                padding: 16px 20px;
                text-align: center;
                font-size: 16px;
                border-radius: 6px;
                min-width: unset;
            }
            
            .help {
                padding: 15px;
                margin: 20px 0;
            }
            
            .help p {
                font-size: 14px;
            }
            
            .signature {
                margin-top: 25px;
                font-size: 14px;
            }
            
            .footer {
                padding: 18px 20px;
                font-size: 12px;
            }
            
            .footer small span.full-text {
                display: none;
            }
            
            .footer small span.short-text {
                display: inline;
            }
        }
        
        @media only screen and (max-width: 400px) {
            .wrapper {
                padding: 15px 10px;
            }
            
            .content {
                padding: 22px 16px;
            }
            
            h1 {
                font-size: 19px;
                margin-bottom: 16px;
            }
            
            p {
                font-size: 15px;
                margin-bottom: 16px;
            }
            
            .logo-icon {
                width: 22px;
                height: 22px;
                margin-right: 8px;
            }
            
            .button {
                padding: 15px 16px;
                font-size: 15px;
            }
            
            .help {
                padding: 14px;
            }
            
            .help p {
                font-size: 13px;
            }
        }
        
        .full-text {
            display: inline;
        }
        
        .short-text {
            display: none;
        }
        
        @media only screen and (max-width: 600px) {
            .full-text {
                display: none;
            }
            
            .short-text {
                display: inline;
            }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header-bar"></div>
            <div class="content">
                <div class="logo">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo" class="logo-icon">
                    <div class="logo-text">Disdikpora Kabupaten Badung</div>
                    <div class="logo-text-mobile">Disdikpora Kab Badung</div>
                </div>
                
                <!-- Konten dinamis akan diisi berdasarkan jenis notifikasi dan peran pengguna -->
                @if($notificationType == 'new_submission' && $userRole == 'pemohon')
                    <span class="status-indicator status-diajukan">Permohonan Diajukan</span>
                    <h1>Permohonan Izin Operasional PAUD Berhasil Diajukan</h1>
                    
                    <p>Yth. Bapak/Ibu {{ $user->name }},</p>
                    
                    <p>Terima kasih telah mengajukan permohonan Izin Operasional PAUD pada <strong>Sistem Permohonan Izin Operasional PAUD</strong> 
                    <span class="full-text">Dinas Pendidikan Pemuda dan Olahraga Kabupaten Badung</span>
                    <span class="short-text">Disdikpora Kabupaten Badung</span>.</p>
                    
                    <div class="status-detail">
                        <table class="info-table">
                            <tr>
                                <td>Nomor Registrasi</td>
                                <td>{{ $permohonan->no_permohonan }}</td>
                            </tr>
                            <tr>
                                <td>Nama Lembaga</td>
                                <td>{{ $permohonan->identitas->nama_lembaga }}</td>
                            </tr>
                            <tr>
                                <td>Tanggal Pengajuan</td>
                                <td>{{ $permohonan->tgl_permohonan }}</td>
                            </tr>
                            <tr>
                                <td>Status</td>
                                <td>{{ $formattedStatus  }}</td>
                            </tr>
                        </table>
                    </div>
                    
                    <p>Permohonan Anda telah diterima dan sedang dalam antrian untuk diverifikasi oleh tim kami. Anda dapat memantau status permohonan melalui dashboard akun Anda.</p>
                    
                    {{-- <div class="button-container">
                        <a href="{{ route('permohonan.detail', $permohonan->id) }}" class="button">Lihat Detail Permohonan</a>
                    </div> --}}
                    
                    <p>Jika ada pertanyaan atau memerlukan bantuan, silakan hubungi kami melalui:</p>
                    <p><strong>Email:</strong> info@disdikporabadung.go.id<br>
                    <strong>Telepon:</strong> (0361) 123456</p>

                @elseif($notificationType == 'new_submission' && $userRole == 'admin')
                    <span class="status-indicator status-diajukan">Permohonan Baru</span>
                    <h1>Permohonan Izin Operasional PAUD Baru Memerlukan Verifikasi</h1>
                    
                    <p>Yth. Bapak/Ibu {{ $user->name }},</p>
                    
                    <p>Terdapat permohonan izin operasional PAUD baru yang memerlukan verifikasi dari tim verifikator. Mohon untuk segera melakukan pemeriksaan kelengkapan dokumen dan kesesuaian persyaratan.</p>
                    
                    <div class="status-detail">
                        <table class="info-table">
                            <tr>
                                <td>Nomor Registrasi</td>
                                <td>{{ $permohonan->no_permohonan }}</td>
                            </tr>
                            <tr>
                                <td>Nama Lembaga</td>
                                <td>{{ $permohonan->identitas->nama_lembaga }}</td>
                            </tr>
                            <tr>
                                <td>Alamat Lembaga</td>
                                <td>{{ $permohonan->identitas->alamat_identitas }}</td>
                            </tr>
                            <tr>
                                <td>Nama Pemohon</td>
                                <td>{{ $permohonan->user->name }}</td>
                            </tr>
                            <tr>
                                <td>Tanggal Pengajuan</td>
                                <td>{{ $permohonan->tgl_permohonan }}</td>
                            </tr>
                        </table>
                    </div>
                    
                    {{-- <div class="button-container">
                        <a href="{{ route('admin.permohonan.verify', $permohonan->id) }}" class="button">Verifikasi Permohonan</a>
                    </div> --}}

                @elseif($notificationType == 'status_update' && $userRole == 'pemohon')
                    <span class="status-indicator status-{{ strtolower($formattedStatus ) }}">{{ $formattedStatus  }}</span>
                    <h1>Status Permohonan Izin Operasional PAUD Diperbarui</h1>
                    
                    <p>Yth. Bapak/Ibu {{ $user->name }},</p>
                    
                    <p>Status permohonan Izin Operasional PAUD Anda pada <strong>Sistem Permohonan Izin Operasional PAUD</strong> 
                    <span class="full-text">Dinas Pendidikan Pemuda dan Olahraga Kabupaten Badung</span>
                    <span class="short-text">Disdikpora Kabupaten Badung</span> telah diperbarui.</p>
                    
                    <div class="status-detail">
                        <table class="info-table">
                            <tr>
                                <td>Nomor Registrasi</td>
                                <td>{{ $permohonan->no_permohonan }}</td>
                            </tr>
                            <tr>
                                <td>Nama Lembaga</td>
                                <td>{{ $permohonan->identitas->nama_lembaga }}</td>
                            </tr>
                            <tr>
                                <td>Status Terbaru</td>
                                <td><strong>{{ $formattedStatus  }}</strong></td>
                            </tr>
                            <tr>
                                <td>Tanggal Pembaruan</td>
                                <td>{{ $permohonan->updated_at->format('d F Y H:i') }}</td>
                            </tr>
                            @if($permohonan->notes)
                            <tr>
                                <td>Catatan</td>
                                <td>{{ $permohonan->notes }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                    
                    @if($formattedStatus  == 'Disetujui')
                    <p>Selamat! Permohonan Izin Operasional PAUD Anda telah <strong>disetujui</strong>. Sertifikat Izin Operasional dapat diunduh melalui sistem.</p>
                    {{-- <div class="button-container">
                        <a href="{{ route('permohonan.certificate', $permohonan->id) }}" class="button">Unduh Sertifikat</a>
                    </div> --}}
                    @elseif($formattedStatus  == 'Ditolak')
                    <p>Mohon maaf, permohonan Anda <strong>tidak dapat disetujui</strong>. Silakan periksa catatan dari admin untuk detail alasan penolakan.</p>
                    {{-- <div class="button-container">
                        <a href="{{ route('permohonan.detail', $permohonan->id) }}" class="button">Lihat Detail</a>
                    </div> --}}
                    @else
                    <p>Status permohonan Anda telah diperbarui. Silakan periksa detail permohonan untuk informasi lebih lanjut.</p>
                    {{-- <div class="button-container">
                        <a href="{{ route('permohonan.detail', $permohonan->id) }}" class="button">Lihat Detail Permohonan</a>
                    </div> --}}
                    @endif
                    
                    <p>Jika ada pertanyaan atau memerlukan bantuan, silakan hubungi kami melalui:</p>
                    <p><strong>Email:</strong> info@disdikporabadung.go.id<br>
                    <strong>Telepon:</strong> (0361) 123456</p>

                @elseif($notificationType == 'status_update' && $userRole == 'kepala_dinas')
                    <span class="status-indicator status-verifikasi">Menunggu Persetujuan</span>
                    <h1>Permohonan Izin Operasional PAUD Memerlukan Persetujuan</h1>
                    
                    <p>Yth. Bapak/Ibu {{ $user->name }},</p>
                    
                    <p>Terdapat permohonan izin operasional PAUD yang telah diverifikasi dan memerlukan persetujuan Anda sebagai pimpinan.</p>
                    
                    <div class="status-detail">
                        <table class="info-table">
                            <tr>
                                <td>Nomor Registrasi</td>
                                <td>{{ $permohonan->no_permohonan }}</td>
                            </tr>
                            <tr>
                                <td>Nama Lembaga</td>
                                <td>{{ $permohonan->identitas->nama_lembaga }}</td>
                            </tr>
                            <tr>
                                <td>Alamat Lembaga</td>
                                <td>{{ $permohonan->identitas->alamat_identitas }}</td>
                            </tr>
                            <tr>
                                <td>Hasil Verifikasi</td>
                                <td>{{ $formattedStatus  }}</td>
                            </tr>
                        </table>
                    </div>
                    
                    {{-- <div class="button-container">
                        <a href="{{ route('admin.permohonan.approve', $permohonan->id) }}" class="button">Tinjau dan Setujui</a>
                    </div> --}}
                @endif
                
                <div class="signature">
                    Email ini dikirim secara otomatis oleh sistem. Mohon tidak membalas email ini.
                </div>
            </div>
            
            <div class="footer">
                <small>&copy; {{ date('Y') }} 
                <span class="full-text">Dinas Pendidikan Pemuda dan Olahraga Kabupaten Badung</span>
                <span class="short-text">Disdikpora Kab Badung</span>. 
                Hak Cipta Dilindungi.</small>
            </div>
        </div>
    </div>
</body>
</html>