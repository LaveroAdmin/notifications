# Notifications for LaveroAdmin

Send alerts to Telegram, Webhook, or Email on WAFU blocks, exceptions, backups, and custom events.

## Requirements

- PHP >= 8.1
- `laveroadmin/laveroadmin`: `^1.0`

## Installation

```bash
composer require laveroadmin/notifications
php artisan migrate
php artisan admin:import notifications
```

## Features

- Configure multiple notification channels (Telegram Bot API, Email, Webhook)
- Test delivery directly from the admin panel
- Delivery history log (`notification_logs` table)
- Full English and Russian localization

## License

The MIT License (MIT). See [LICENSE](LICENSE).
