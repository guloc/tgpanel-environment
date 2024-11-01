<?php defined('ROCKET_SCRIPT') OR die(header('Location: /not_found'));

// Auth
$lang['sign_to_continue'] = 'Войдите чтобы продолжить';
$lang['login'] = 'Логин';
$lang['password'] = 'Пароль';
$lang['enter_login'] = 'Введите логин';
$lang['enter_pass'] = 'Введите пароль';
$lang['sign_in'] = 'Войти';
$lang['logout'] = 'Выход';

$lang['activation'] = 'Активация';
$lang['license_key'] = 'Лицензионный ключ';

// Updates
$lang['update_note'] = 'Доступно обновление. Для установки перейдите в';
$lang['update_available'] = 'Доступно обновление';
$lang['version'] = 'Версия';
$lang['changelog'] = 'История изменений';
$lang['install'] = 'Установить';
$lang['update_success'] = 'Обновление успешно установлено';

// Menu
$lang['main_page'] = 'Главная';
$lang['channels_manage'] = 'Управление каналами';
$lang['parsing'] = 'Парсинг';
$lang['posting'] = 'Отложенный постинг';
$lang['groups'] = 'Группы';
$lang['settings'] = 'Настройки';
$lang['users'] = 'Пользователи';
$lang['error_log'] = 'Лог ошибок';

// Main
$lang['day'] = 'День';
$lang['week'] = 'Неделя';
$lang['month'] = 'Месяц';

// Channels
$lang['channels'] = 'Каналы';
$lang['sources'] = 'Источники';
$lang['channel_stats'] = 'Статистика каналов';
$lang['channel'] = 'Канал';
$lang['create_channel'] = 'Добавить канал';
$lang['channel_name'] = 'Название';
$lang['channel_link'] = 'Ссылка';
$lang['platform'] = 'Платформа';
$lang['access_token'] = 'Ключ доступа';
$lang['enter_access_token'] = 'vk1.a....';
$lang['enter_channel_name'] = 'Введите название';
$lang['enter_channel_link_tg'] = "https://t.me/somechannel либо @somechannel";
$lang['enter_channel_link_vk'] = "https://vk.com/somegroup либо vk.com/somegroup";
$lang['enter_channel_link_wp'] = "https://your-site.com либо your-site.com";
$lang['access_key_placeholder_vk'] = 'vk1.a....';
$lang['access_key_placeholder_wp'] = '5yCO Yak1 ...';
$lang['enter_channel_links'] = "Введите один или несколько каналов\nВ формате";
$lang['channel_created'] = 'Канал добавлен';
$lang['channel_exists'] = 'Такой канал уже есть';
$lang['channel_not_found'] = 'Канал не найден';
$lang['bot_not_in_channel'] = 'Сначала сделайте юзербота администратором канала';
$lang['stats_unavailable'] = 'Статистика для этого канала недоступна';
$lang['subscribes'] = 'Подписки';
$lang['unsubscribes'] = 'Отписки';
$lang['subscribers'] = 'Подписчики';
$lang['daily_subs'] = 'Подписки за день';
$lang['weekly_subs'] = 'Подписки за неделю';
$lang['source_deleted'] = 'Источник удален';
$lang['source_activated'] = 'Источник включен';
$lang['source_deactivated'] = 'Источник отключен';
$lang['userbot_not_in_channel'] = 'Для получения статистики необходимо чтобы юзербот был администратором канала';
$lang['vk_permissions_error'] = 'Ключ доступа должен иметь право доступа к стене';


// Parsing
$lang['parsing_active'] = 'Парсинг включен';
$lang['copy_data'] = 'Копировать данные';
$lang['text'] = 'Текст';
$lang['images'] = 'Изображения';
$lang['video'] = 'Видео';
$lang['audio'] = 'Аудио';
$lang['files'] = 'Файлы';
$lang['stop_words'] = 'Стоп слова';
$lang['start_words'] = 'Старт слова';
$lang['del_links'] = 'Удалять ссылки';
$lang['del_tags'] = 'Удалять хэштеги';
$lang['rephrase_text'] = 'Перефразировать через ChatGPT';
$lang['prompt'] = 'Промпт';
$lang['autoposting_channel'] = 'Автопостинг в канал';
$lang['cant_get_channel_info'] = 'Не удалось получить информацию о канале';
$lang['replacements'] = 'Автозамена';
$lang['replace_from'] = 'Что';
$lang['replace_to'] = 'Чем';
$lang['moderation'] = 'Модерация';
$lang['approve_or_decline'] = 'Одобрите или отклоните пост %s';
$lang['approve'] = 'Одобрить';
$lang['decline'] = 'Отклонить';
$lang['post_approved'] = 'Пост #%s одобрен';
$lang['post_declined'] = 'Пост #%s отклонен';
$lang['add_subscript'] = 'Добавить подпись';

// Posting
$lang['drafts'] = 'Черновики';
$lang['queue'] = 'Очередь';
$lang['posted'] = 'Опубликованные';
$lang['post_draft'] = 'Черновик';
$lang['post_queued'] = 'В очереди';
$lang['post_posted'] = 'Опубликован';
$lang['post_moderation'] = 'На модерации';
$lang['post'] = 'Пост';
$lang['create_post'] = 'Создать пост';
$lang['post_name'] = 'Название поста';
$lang['pub_date'] = 'Дата публикации';
$lang['status'] = 'Статус';
$lang['add_media'] = 'Добавить медиа';
$lang['pub_now'] = 'Опубликовать сейчас';
$lang['enter_post_name'] = 'Введите название поста';
$lang['post_not_found'] = 'Пост не найден';
$lang['post_name_empty'] = 'Чтобы сохранить пустой пост укажите название';
$lang['set_future_date'] = 'Дата публикации должна быть будущей';
$lang['set_channel'] = 'Выберите канал для публикации';
$lang['post_is_empty'] = 'Для публикации пост не должен быть пустым';
$lang['drop_files'] = 'Нажмите сюда чтобы загрузить файл';
$lang['supported_files'] = 'Макс. размер: 20 МБ для видео и 10МБ для изображений. Поддерживаемые типы: %s';
$lang['file_too_big'] = 'Слишком большой файл';
$lang['file_type_incorrect'] = 'Неподходящий тип файла';
$lang['symbols_total'] = 'Всего символов';
$lang['default_value'] = 'Значение по умолчанию';
$lang['rewrite'] = 'Переписать';
$lang['continue'] = 'Продолжить';
$lang['clear_all'] = 'Очистить все';
$lang['mute'] = 'Запретить комментарии';
$lang['unmute'] = 'Разрешить комментарии';
$lang['ban'] = 'Забанить';
$lang['unban'] = 'Разбанить';
$lang['too_long_text'] = 'Лимит символов превышен';

// Groups
$lang['stats'] = 'Статистика';
$lang['filtering'] = 'Фильтрация';
$lang['send_message'] = 'Отправить сообщение';
$lang['members_count'] = 'Количество участников';
$lang['joined'] = 'Вступили';
$lang['left'] = 'Покинули';
$lang['banned'] = 'Забанены';
$lang['muted'] = 'Заглушены';
$lang['name'] = 'Имя';
$lang['username'] = 'Ссылка';
$lang['last_visit'] = 'Последняя активность';
$lang['create_group'] = 'Добавить группу';
$lang['group_id'] = 'ID группы';
$lang['group_name'] = 'Название группы';
$lang['enter_group_id'] = 'Введите ID группы';
$lang['enter_group_name'] = 'Введите название группы';
$lang['group_exists'] = 'Такая группа уже есть';
$lang['bot_not_admin'] = 'Сначала сделайте бота администратором группы';
$lang['group_not_found'] = 'Группа не найдена';
$lang['filter_settings'] = 'Настройки фильтрации';
$lang['filter_active'] = 'Фильтрация включена';
$lang['members_messages'] = 'Сообщения участников';
$lang['del_bot_commands'] = 'Удалять сообщения содержащие команды бота';
$lang['groups_01'] = 'Удалять сообщения содержащие изображения';
$lang['groups_02'] = 'Удалять голосовые сообщения';
$lang['groups_03'] = 'Удалять сообщения содержащие файлы';
$lang['groups_04'] = 'Удалять стикеры и GIF';
$lang['groups_05'] = 'Удалять броски кубиков';
$lang['groups_06'] = 'Удалять сообщения содержащие ссылки';
$lang['groups_07'] = 'Репосты';
$lang['groups_08'] = 'Удалять репосты с медиа';
$lang['groups_09'] = 'Удалять репосты со ссылками';
$lang['groups_10'] = 'Удалять все репосты';
$lang['groups_11'] = 'При нарушении пользователь блокируется на';
$lang['groups_12'] = 'Минут';
$lang['groups_13'] = 'Часов';
$lang['groups_14'] = 'Дней';
$lang['groups_15'] = 'Месяцев';
$lang['groups_16'] = 'Ключевые слова';
$lang['groups_17'] = 'Удалять сообщения содержащие следующие слова';
$lang['groups_18'] = 'Сообщения администраторов';
$lang['groups_19'] = 'Фильтровать сообщения администраторов';
$lang['groups_20'] = 'Сервисные сообщения';
$lang['groups_21'] = 'Удалять сообщения "Пользователь вступил в группу"';
$lang['groups_22'] = 'Удалять сообщения "Пользователь покинул группу"';
$lang['groups_23'] = 'Ограничение новых участников';
$lang['groups_24'] = 'После вступления в группу пользователь ограничен на';

// Users
$lang['role'] = 'Роль';
$lang['date_register'] = 'Дата регистрации';
$lang['create_user'] = 'Создать нового пользователя';
$lang['user'] = 'Пользователь';
$lang['admin'] = 'Администратор';
$lang['user_created'] = 'Пользователь успешно создан';
$lang['change_pass'] = 'Смена пароля';
$lang['new_pass'] = 'Новый пароль';
$lang['confirm_pass'] = 'Подтвердите пароль';
$lang['pass_confirm_error'] = 'Подтверждение пароля не совпадает';
$lang['pass_changed'] = 'Пароль успешно изменен';

// Settings
$lang['settings_saved'] = 'Настройки успешно сохранены';
$lang['site_name'] = 'Название сайта';
$lang['authorized_as'] = 'Подключен Telegram-аккаунт';
$lang['telegram_bot_token'] = 'Telegram Bot API Token';
$lang['install_webhook'] = 'Установить webhook';
$lang['https_proxy'] = 'HTTPS Прокси';
$lang['proxy_ip'] = 'IP';
$lang['proxy_port'] = 'Порт';
$lang['proxy_login'] = 'Логин';
$lang['proxy_pass'] = 'Пароль';
$lang['ai_settings'] = 'Настройки нейросетей';
$lang['openai_key'] = 'OpenAI Ключ API';
$lang['openrouter_key'] = 'OpenRouter Ключ API';
$lang['select_model'] = 'Выберите модель';
$lang['select_img_size'] = 'Выберите разрешение изображения';
$lang['service'] = 'Сервис';
$lang['model'] = 'Модель';
$lang['create_model'] = 'Добавить модель';
$lang['model_created'] = 'Модель добавлена';
$lang['models_exists'] = 'Такая модель уже есть';
$lang['model_code'] = 'Код модели';
$lang['model_name'] = 'Название модели';
$lang['bot_users'] = 'Пользователи бота';
$lang['enable_unsafe_parsing'] = 'Включить небезопасный и медленный режим парсинга (не рекомендуется)';
$lang['restart_parsing'] = 'Перезапустить';
$lang['parsing_restarted'] = 'Парсинг перезапущен';
$lang['tg_logout_success'] = 'Сессия Telegram успешно завершена';
$lang['posts_moderator'] = 'Модератор постов';
$lang['user_id'] = 'ID пользователя';
$lang['vk_connect'] = 'Подключение VK';
$lang['app_id'] = 'ID приложения';
$lang['vk_private_key'] = 'Защищённый ключ';
$lang['vk_service_key'] = 'Сервисный ключ доступа';
$lang['vk_auth_success'] = 'VK успешно подключен';