@php
    $setting = \App\Models\Setting::find('00000000-0000-0000-0000-000000000001') ?? \App\Models\Setting::first();
    $storeName = $setting->store_name ?? config('app.name');

    // Recupera o caminho relativo do banco
    $logoRelativePath = $setting->logo_dark_path ?? $setting->logo_light_path;

    // Gera a URL pública absoluta
    $logoUrl = $logoRelativePath ? asset($logoRelativePath) : null;

    // Recupera a cor primária ou define o padrão #fe9a00
    $primaryColor = $setting->primary_color_hex ?? '#fe9a00';
@endphp

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    
    <!-- Forçar Light Mode -->
    <meta name="color-scheme" content="light only">
    <meta name="supported-color-schemes" content="light only">
    
    <style>
        /* =========================================
           CONFIGURAÇÕES GERAIS E DARK MODE BLOCK
           ========================================= */
        :root {
            color-scheme: light only;
            supported-color-schemes: light only;
        }

        /* Base */
        body {
            background-color: #edf2f7 !important;
            color: #454d5d !important;
            height: 100%;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            width: 100% !important;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol';
        }

        /* Layout Wrapper */
        .wrapper {
            background-color: #edf2f7 !important;
            margin: 0;
            padding: 0;
            width: 100%;
            -premailer-cellpadding: 0;
            -premailer-cellspacing: 0;
            -premailer-width: 100%;
        }

        .content {
            margin: 0;
            padding: 0;
            width: 100%;
            -premailer-cellpadding: 0;
            -premailer-cellspacing: 0;
            -premailer-width: 100%;
        }

        /* Header */
        .header {
            padding: 25px 0;
            text-align: center;
        }

        .header a {
            color: #3d4852;
            font-size: 19px;
            font-weight: bold;
            text-decoration: none;
        }

        /* Logo */
        .logo {
            max-height: 75px;
            width: auto;
        }

        /* Body (O Cartão Branco) */
        .body {
            background-color: #edf2f7 !important;
            border-bottom: 1px solid #edf2f7;
            border-top: 1px solid #edf2f7;
            margin: 0;
            padding: 0;
            width: 100%;
            -premailer-cellpadding: 0;
            -premailer-cellspacing: 0;
            -premailer-width: 100%;
        }

        .inner-body {
            background-color: #ffffff !important;
            border-color: #e8e5ef;
            border-radius: 20px;
            border-width: 1px;
            box-shadow: 0 2px 0 rgba(0, 0, 150, 0.025), 2px 4px 0 rgba(0, 0, 150, 0.015);
            margin: 0 auto;
            padding: 0;
            width: 570px;
            -premailer-cellpadding: 0;
            -premailer-cellspacing: 0;
            -premailer-width: 570px;
        }

        /* Conteúdo de Texto */
        .content-cell {
            max-width: 100vw;
            padding: 32px;
            color: #454d5d !important;
        }

        h1 {
            color: #111827 !important;
            font-size: 22px;
            font-weight: bold;
            margin-top: 0;
            text-align: left;
        }

        p {
            color: #4b5563 !important;
            font-size: 16px;
            line-height: 1.5em;
            margin-top: 0;
            text-align: left;
        }

        /* Botões com Cor Dinâmica */
        .action {
            margin: 30px auto;
            padding: 0;
            text-align: center;
            width: 100%;
            -premailer-cellpadding: 0;
            -premailer-cellspacing: 0;
            -premailer-width: 100%;
        }

        .button {
            border-radius: 8px;
            box-shadow: 0 2px 3px rgba(0, 0, 0, 0.16);
            color: #fff !important;
            display: inline-block;
            text-decoration: none;
            -webkit-text-size-adjust: none;
            background-color: {{ $primaryColor }} !important;
            border-top: 10px solid {{ $primaryColor }} !important;
            border-right: 18px solid {{ $primaryColor }} !important;
            border-bottom: 10px solid {{ $primaryColor }} !important;
            border-left: 18px solid {{ $primaryColor }} !important;
            font-weight: bold;
        }

        .button-blue,
        .button-primary {
            background-color: {{ $primaryColor }} !important;
            border-top: 10px solid {{ $primaryColor }} !important;
            border-right: 18px solid {{ $primaryColor }} !important;
            border-bottom: 10px solid {{ $primaryColor }} !important;
            border-left: 18px solid {{ $primaryColor }} !important;
        }

        /* Footer */
        .footer {
            margin: 0 auto;
            padding: 0;
            text-align: center;
            width: 570px;
            -premailer-cellpadding: 0;
            -premailer-cellspacing: 0;
            -premailer-width: 570px;
        }

        .footer p {
            color: #b0adc5 !important;
            font-size: 12px;
            text-align: center;
        }

        .footer a {
            color: #b0adc5 !important;
            text-decoration: underline;
        }

        /* Responsividade Mobile */
        @media only screen and (max-width: 600px) {
            .inner-body {
                width: 100% !important;
            }

            .footer {
                width: 100% !important;
            }
        }

        /* =========================================
           FORÇAR LIGHT MODE (OVERRIDE CLIENTES)
           ========================================= */
        @media (prefers-color-scheme: dark) {
            body, .wrapper, .body {
                background-color: #edf2f7 !important;
                color: #454d5d !important;
            }
            .inner-body {
                background-color: #ffffff !important;
                color: #454d5d !important;
            }
            h1, h2, h3 {
                color: #111827 !important;
            }
            p, span, div {
                color: #4b5563 !important;
            }
            .footer p, .footer a {
                color: #b0adc5 !important;
            }
        }
    </style>
</head>

<body>

    <table class="wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation">
        <tr>
            <td align="center">
                <table class="content" width="100%" cellpadding="0" cellspacing="0" role="presentation">

                    <tr>
                        <td class="header">
                            <a href="{{ url('/') }}" style="display: inline-block;">
                                @if($logoUrl)
                                    <img src="{{ $logoUrl }}" class="logo" alt="{{ $storeName }}" style="max-height: 60px; width: auto; border: 0;">
                                @else
                                    <span style="font-size: 24px; font-weight: 800; color: #111827;">{{ $storeName }}</span>
                                @endif
                            </a>
                        </td>
                    </tr>

                    <tr>
                        <td class="body" width="100%" cellpadding="0" cellspacing="0" role="presentation">
                            <table class="inner-body" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
                                <tr>
                                    <td class="content-cell">
                                        {!! $slot !!}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding-top: 25px;">
                            <table class="footer" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
                                <tr>
                                    <td class="content-cell" align="center">

                                        <p style="margin-bottom: 15px;">
                                            @if(!empty($setting->facebook_url))
                                            <a href="{{ $setting->facebook_url }}" style="margin: 0 5px; text-decoration: none; font-weight: bold; font-size: 12px;">FACEBOOK</a>
                                            @endif
                                            @if(!empty($setting->instagram_url))
                                            <a href="{{ $setting->instagram_url }}" style="margin: 0 5px; text-decoration: none; font-weight: bold; font-size: 12px;">INSTAGRAM</a>
                                            @endif
                                            @if(!empty($setting->whatsapp_url))
                                            <a href="{{ $setting->whatsapp_url }}" style="margin: 0 5px; text-decoration: none; font-weight: bold; font-size: 12px;">WHATSAPP</a>
                                            @endif
                                        </p>

                                        <p style="line-height: 1.5em;">
                                            &copy; {{ date('Y') }} <strong>{{ $storeName }}</strong>. Todos os direitos reservados.
                                            @if(!empty($setting->company_address))
                                            <br>{{ $setting->company_address }} - {{ $setting->company_city }}
                                            @endif
                                        </p>

                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>