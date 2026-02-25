<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Пополнение счёта — TargetX</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #1a1a1a;
            background: #f9fafb;
        }
        .container { max-width: 560px; margin: 0 auto; padding: 40px 20px; }
        .card {
            background: #fff;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
        h1 { font-size: 24px; font-weight: 700; margin-bottom: 16px; color: #1a1a1a; }
        .text { color: #4b5563; margin-bottom: 24px; }
        .form-group { margin-bottom: 20px; }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #374151;
        }
        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 16px;
        }
        .form-group input:focus {
            outline: none;
            border-color: #2563eb;
        }
        .form-group .error { color: #ef4444; font-size: 14px; margin-top: 5px; }
        .btn-primary {
            background: #2563eb;
            color: white;
            padding: 14px 24px;
            border-radius: 8px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            transition: background 0.3s;
        }
        .btn-primary:hover { background: #1d4ed8; }
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .alert-error { background: #fee2e2; color: #991b1b; }
        .alert-info { background: #dbeafe; color: #1e40af; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h1>Пополнение счёта</h1>
            <p class="text">
                Для начала работы необходимо пополнить счёт на любую сумму. Стоимость одного сообщения составляет 2 ₽.
                Введите сумму пополнения и нажмите «Оплатить».
            </p>

            @if (session('error'))
                <div class="alert alert-error">{{ session('error') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-error">
                    @foreach ($errors->all() as $err) {{ $err }} @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('newsletters.landing.create-registration-payment') }}">
                @csrf
                <div class="form-group">
                    <label for="amount">Сумма (₽)</label>
                    <input type="number" id="amount" name="amount" value="{{ old('amount', 100) }}" min="{{ config('tochka-payment.min_amount', 1) }}" step="1" required>
                    @error('amount')<div class="error">{{ $message }}</div>@enderror
                </div>
                <button type="submit" class="btn-primary">Оплатить</button>
            </form>
        </div>
    </div>
</body>
</html>
