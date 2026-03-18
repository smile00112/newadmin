<?php

namespace Webkul\RestApi\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class WebSocketConfig
{
    public static function server(Request $request): array
    {
        $key = Config::get('reverb.apps.0.key')
            ?? env('REVERB_APP_KEY')
            ?? env('PUSHER_APP_KEY')
            ?? 'your-app-key';

        $host = Config::get('reverb.apps.0.host')
            ?? env('REVERB_HOST')
            ?? self::parseHostFromAppUrl()
            ?? $request->getHost();

        $port = Config::get('reverb.apps.0.port')
            ?? env('REVERB_PORT')
            ?? ($request->secure() ? 443 : 80);

        $scheme = Config::get('reverb.apps.0.scheme')
            ?? env('REVERB_SCHEME')
            ?? ($request->secure() ? 'https' : 'http');

        $path = '/app';

        $wsScheme = $scheme === 'https' ? 'wss' : 'ws';
        $portPart = in_array((int) $port, [80, 443], true) ? '' : ":{$port}";
        $url = "{$wsScheme}://{$host}{$portPart}{$path}";

        return [
            'url'      => $url,
            'protocol' => 'pusher',
            'key'      => $key,
            'host'     => $host,
            'port'     => (int) $port,
            'path'     => $path,
        ];
    }

    private static function parseHostFromAppUrl(): ?string
    {
        $appUrl = env('APP_URL');

        if (empty($appUrl)) {
            return null;
        }

        $parsed = parse_url($appUrl);

        return $parsed['host'] ?? null;
    }
}

