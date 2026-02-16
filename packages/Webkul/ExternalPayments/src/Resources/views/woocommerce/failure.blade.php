<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ошибка оплаты</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 500px;
        }
        .error-icon {
            color: #dc3545;
            font-size: 64px;
            margin-bottom: 20px;
        }
        h1 {
            color: #dc3545;
            margin-bottom: 20px;
        }
        .info {
            color: #666;
            margin-bottom: 30px;
        }
        .payment-details {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 4px;
            margin-top: 20px;
            text-align: left;
        }
        .payment-details p {
            margin: 10px 0;
        }
        .payment-details strong {
            color: #333;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-icon">✗</div>
        <h1>Ошибка оплаты</h1>
        <div class="info">
            <p>К сожалению, произошла ошибка при обработке платежа.</p>
            <p>Пожалуйста, попробуйте еще раз или свяжитесь с поддержкой.</p>
        </div>
        
        @if(isset($payment))
        <div class="payment-details">
            <p><strong>ID платежа:</strong> {{ $payment->id }}</p>
            <p><strong>Сумма:</strong> {{ number_format($payment->amount, 2, '.', ' ') }} ₽</p>
            <p><strong>Статус:</strong> {{ $payment->status }}</p>
            @if($payment->order_id)
            <p><strong>Номер заказа:</strong> {{ $payment->order_id }}</p>
            @endif
        </div>
        @endif

        <p style="margin-top: 30px; color: #999; font-size: 14px;">
            Вы будете перенаправлены на страницу заказа...
        </p>
    </div>
</body>
</html>
