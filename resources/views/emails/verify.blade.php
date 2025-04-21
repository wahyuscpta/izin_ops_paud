<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verifikasi - Disdikpora Kabupaten Badung</title>
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
            max-width: 600px;
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

        .button-container {
            text-align: center;
            margin: 28px 0;
        }

        .button {
            display: inline-block;
            background-color: #4361ee;
            color: white;
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
            
            .button-container {
                margin: 24px 0;
            }
            
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
                
                <h1>Yth. Bapak/Ibu {{ $user->name }},</h1>
                
                <p>Terima kasih telah melakukan pendaftaran pada <strong>Sistem Permohonan Izin Operasional PAUD</strong> 
                <span class="full-text">Dinas Pendidikan Pemuda dan Olahraga Kabupaten Badung</span>
                <span class="short-text">Dinas Pendidikan Pemuda dan Olahraga Kab Badung</span>.</p>
                
                <p>Sebagai langkah keamanan dan untuk memastikan keabsahan informasi, kami memerlukan verifikasi alamat email Anda. Silakan klik tombol di bawah ini untuk memverifikasi email dan melanjutkan proses permohonan izin operasional PAUD:</p>
                
                <div class="button-container">
                    <a href="{{ $url }}" class="button">Verifikasi Email</a>
                </div>

                <p>Jika Anda mengalami kendala dalam proses verifikasi, silakan klik tautan berikut atau salin ke browser Anda:</p>

                <div class="help">
                    <a href="{{ $url }}">{{ $url }}</a>
                </div>
                
                <div class="signature">
                    Jika Anda tidak melakukan pendaftaran ini, silakan abaikan email ini.
                </div>
            </div>
            
            <div class="footer">
                <small>&copy; {{ date('Y') }} 
                <span class="full-text">Dinas Pendidikan Pemuda dan Olahraga Kabupaten Badung</span>
                <span class="short-text">Dinas Pendidikan Pemuda dan Olahraga Kab Badung</span>. 
                All rights reserved.</small>
            </div>
        </div>
    </div>
</body>
</html>