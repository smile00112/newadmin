@component('shop::emails.layout')
    <div style="margin-bottom: 34px;">
        <p style="font-weight: bold;font-size: 20px;color: #121A26;line-height: 24px;margin-bottom: 24px">
            Новый пользователь зарегистрирован в системе
        </p>

        <p style="font-size: 16px;color: #384860;line-height: 24px;">
            В системе зарегистрирован новый пользователь. Ниже указаны детали регистрации:
        </p>
    </div>

    <div style="background: #F9FAFB;padding: 20px;border-radius: 8px;margin-bottom: 30px;">
        <p style="font-size: 16px;color: #384860;line-height: 24px;margin-bottom: 10px;">
            <strong>Имя:</strong> {{ $newAdmin->name }}
        </p>
        <p style="font-size: 16px;color: #384860;line-height: 24px;margin-bottom: 10px;">
            <strong>Email (логин):</strong> {{ $newAdmin->email }}
        </p>
        <p style="font-size: 16px;color: #384860;line-height: 24px;margin-bottom: 10px;">
            <strong>Пароль:</strong> {{ $password }}
        </p>
        <p style="font-size: 16px;color: #384860;line-height: 24px;margin-bottom: 10px;">
            <strong>Компания:</strong> {{ $companyName }}
        </p>
        @if($plan)
        <p style="font-size: 16px;color: #384860;line-height: 24px;margin-bottom: 10px;">
            <strong>Тарифный план:</strong> 
            @if($plan === 'start')
                Start
            @elseif($plan === 'pro')
                Pro
            @elseif($plan === 'corporate')
                Corporate
            @else
                {{ ucfirst($plan) }}
            @endif
        </p>
        @endif
        <p style="font-size: 16px;color: #384860;line-height: 24px;">
            <strong>Дата регистрации:</strong> {{ $newAdmin->created_at->format('d.m.Y H:i') }}
        </p>
    </div>

    <p style="font-size: 14px;color: #6b7280;line-height: 20px;">
        Вы получили это уведомление, так как являетесь администратором системы.
    </p>
@endcomponent

