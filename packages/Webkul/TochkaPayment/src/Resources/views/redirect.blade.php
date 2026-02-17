<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tochka Payment — {{ $type === 'success' ? trans('tochka-payment::app.redirect.success_title') : trans('tochka-payment::app.redirect.failure_title') }}</title>
    @if(!empty($redirect_url))
    <meta http-equiv="refresh" content="{{ (int) ($redirect_delay_ms / 1000) }};url={{ e($redirect_url) }}">
    @endif
    <style>
        body { font-family: system-ui, sans-serif; max-width: 800px; margin: 2rem auto; padding: 0 1rem; }
        h1 { font-size: 1.5rem; margin-bottom: 1rem; }
        .success { color: #059669; }
        .fail { color: #dc2626; }
    </style>
</head>
<body>
    <h1 class="{{ $type === 'success' ? 'success' : 'fail' }}">
        {{ $message }}
    </h1>
    @if(!empty($redirect_url))
    <p>{{ trans('tochka-payment::app.redirect.redirecting', ['seconds' => (int) ($redirect_delay_ms / 1000)]) }}</p>
    <script>
        setTimeout(function() { window.location.href = {{ json_encode($redirect_url) }}; }, {{ (int) $redirect_delay_ms }});
    </script>
    @endif
</body>
</html>
