@component('shop::emails.layout')
    <div style="margin-bottom: 34px;">
        <p style="font-weight: bold;font-size: 20px;color: #121A26;line-height: 24px;margin-bottom: 24px">
            Здравствуйте, {{ $customer->first_name ?? $customer->name }}! 👋
        </p>

        <p style="font-size: 16px;color: #384860;line-height: 24px;">
            Добро пожаловать в MailingService! Мы рады, что вы присоединились к нашему сервису рассылок.
        </p>
    </div>

    <p style="font-size: 16px;color: #384860;line-height: 24px;margin-bottom: 20px">
        Ваш аккаунт успешно создан. Ниже указаны данные для входа:
    </p>

    <div style="background: #F9FAFB;padding: 20px;border-radius: 8px;margin-bottom: 30px;">
        <p style="font-size: 16px;color: #384860;line-height: 24px;margin-bottom: 10px;">
            <strong>Email:</strong> {{ $customer->email }}
        </p>
        <p style="font-size: 16px;color: #384860;line-height: 24px;">
            <strong>Пароль:</strong> {{ $password }}
        </p>
    </div>

    <p style="font-size: 16px;color: #384860;line-height: 24px;margin-bottom: 30px">
        <strong>Важно:</strong> Для активации аккаунта и получения доступа к системе, пожалуйста, подтвердите регистрацию, перейдя по ссылке ниже.
    </p>

    <div style="display: flex;margin-bottom: 30px">
        <a
            href="{{ route('newsletters.landing.activate', ['token' => $activationToken]) }}"
            style="padding: 16px 45px;justify-content: center;align-items: center;gap: 10px;border-radius: 8px;background: #2563eb;color: #FFFFFF;text-decoration: none;text-transform: uppercase;font-weight: 700;display: inline-block;"
        >
            Активировать аккаунт
        </a>
    </div>

    <p style="font-size: 14px;color: #6b7280;line-height: 20px;margin-bottom: 30px">
        Если кнопка не работает, скопируйте и вставьте следующую ссылку в браузер:<br>
        <a href="{{ route('newsletters.landing.activate', ['token' => $activationToken]) }}" style="color: #2563eb;word-break: break-all;">
            {{ route('newsletters.landing.activate', ['token' => $activationToken]) }}
        </a>
    </p>

    <p style="font-size: 14px;color: #6b7280;line-height: 20px;">
        После активации вы сможете войти в систему, используя указанные выше данные для входа.
    </p>
@endcomponent


