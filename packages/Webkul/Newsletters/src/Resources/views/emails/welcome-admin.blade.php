@component('shop::emails.layout')
    <div style="margin-bottom: 34px;">
        <p style="font-weight: bold;font-size: 20px;color: #121A26;line-height: 24px;margin-bottom: 24px">
            Здравствуйте, {{ $admin->name }}! 👋
        </p>
        <p style="font-size: 16px;color: #384860;line-height: 24px;">
            Добро пожаловать в MailingService! Мы рады, что вы присоединились к нашему сервису рассылок.
        </p>
    </div>

    <p style="font-size: 16px;color: #384860;line-height: 24px;margin-bottom: 20px">
        Ваш аккаунт владельца компании успешно создан. Ниже указаны данные для входа в админ панель:
    </p>

    <div style="background: #F9FAFB;padding: 20px;border-radius: 8px;margin-bottom: 30px;">
        <p style="font-size: 16px;color: #384860;line-height: 24px;margin-bottom: 10px;">
            <strong>Email:</strong> {{ $admin->email }}
        </p>
        <p style="font-size: 16px;color: #384860;line-height: 24px;">
            <strong>Пароль:</strong> {{ $password }}
        </p>
    </div>

    <p style="font-size: 16px;color: #384860;line-height: 24px;margin-bottom: 30px">
        <strong>Важно:</strong> Сохраните эти данные в безопасном месте. Рекомендуем изменить пароль после первого входа.
    </p>

    <div style="display: flex;margin-bottom: 30px">
        <a
            href="{{ route('admin.session.create') }}"
            style="padding: 16px 45px;justify-content: center;align-items: center;gap: 10px;border-radius: 8px;background: #2563eb;color: #FFFFFF;text-decoration: none;text-transform: uppercase;font-weight: 700;display: inline-block;"
        >
            Войти в админ панель
        </a>
    </div>

    <p style="font-size: 14px;color: #6b7280;line-height: 20px;margin-bottom: 30px">
        Если кнопка не работает, скопируйте и вставьте следующую ссылку в браузер:<br>
        <a href="{{ route('admin.session.create') }}" style="color: #2563eb;word-break: break-all;">
            {{ route('admin.session.create') }}
        </a>
    </p>

    <p style="font-size: 14px;color: #6b7280;line-height: 20px;">
        Ваш аккаунт уже активен и готов к использованию. Вы можете войти в систему, используя указанные выше данные для входа.
    </p>
@endcomponent
