@component('shop::emails.layout')
    <div style="margin-bottom: 34px;">
        <p style="font-weight: bold;font-size: 20px;color: #121A26;line-height: 24px;margin-bottom: 24px">
            Новая регистрация в TargetX
        </p>
        <p style="font-size: 16px;color: #384860;line-height: 24px;">
            Зарегистрирован новый пользователь (Менеджер рассылок):
        </p>
    </div>

    <div style="background: #F9FAFB;padding: 20px;border-radius: 8px;margin-bottom: 30px;">
        <p style="font-size: 16px;color: #384860;line-height: 24px;margin-bottom: 10px;">
            <strong>Имя:</strong> {{ $admin->name }}
        </p>
        <p style="font-size: 16px;color: #384860;line-height: 24px;">
            <strong>Email:</strong> {{ $admin->email }}
        </p>
    </div>

    <p style="font-size: 14px;color: #6b7280;line-height: 20px;">
        Регистрация выполнена после успешной оплаты через внешнюю систему.
    </p>
@endcomponent
