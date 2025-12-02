@php
    // Busca dados
    $setting = \App\Models\Setting::find('00000000-0000-0000-0000-000000000001') ?? \App\Models\Setting::first();
    $storeName = $setting->store_name ?? config('app.name');
    
    // Preferência: Logo escura para fundo claro do email, ou logo clara se preferir fundo escuro
    // Assumindo fundo cinza claro padrão de email, a logo "dark" (texto preto) costuma ficar melhor
    $logo = $setting->logo_dark_path ?? $setting->logo_light_path ?? null;
@endphp

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <style>
        @media only screen and (max-width: 600px) {
            .inner-body { width: 100% !important; }
            .footer { width: 100% !important; }
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
                            @if($logo)
                                <img src="{{ $logo }}" class="logo" alt="{{ $storeName }}" style="max-height: 60px; width: auto; border: 0;">
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
                                    {{ $slot }}
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
                                            <a href="{{ $setting->facebook_url }}" style="margin: 0 5px; text-decoration: none; color: #718096; font-weight: bold; font-size: 12px;">FACEBOOK</a>
                                        @endif
                                        @if(!empty($setting->instagram_url))
                                            <a href="{{ $setting->instagram_url }}" style="margin: 0 5px; text-decoration: none; color: #718096; font-weight: bold; font-size: 12px;">INSTAGRAM</a>
                                        @endif
                                        @if(!empty($setting->whatsapp_url))
                                            <a href="{{ $setting->whatsapp_url }}" style="margin: 0 5px; text-decoration: none; color: #718096; font-weight: bold; font-size: 12px;">WHATSAPP</a>
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