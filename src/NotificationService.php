<?php

namespace LaveroAdmin\Admin\Notifications;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use LaveroAdmin\Admin\Notifications\Models\NotificationChannel;
use LaveroAdmin\Admin\Notifications\Models\NotificationLog;

class NotificationService
{
    public static function dispatch(string $event, string $title, string $message): void
    {
        foreach (NotificationChannel::getActiveCached() as $channel) {
            $events = (array) ($channel['events'] ?? []);
            if (!empty($events) && !in_array($event, $events, true) && !in_array('*', $events, true)) {
                continue;
            }
            static::sendToChannel($channel, $event, $title, $message);
        }
    }

    public static function sendToChannel(array $channel, string $event, string $title, string $message): bool
    {
        $driver = $channel['driver'] ?? 'webhook';
        $cfg = (array) ($channel['config'] ?? []);

        try {
            if ($driver === 'telegram') {
                $botToken = trim((string) ($cfg['bot_token'] ?? ''));
                $chatId = trim((string) ($cfg['chat_id'] ?? ''));
                if ($botToken === '' || $chatId === '') {
                    throw new \RuntimeException('Missing Telegram bot_token or chat_id');
                }
                $resp = Http::timeout(10)->post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                    'chat_id'    => $chatId,
                    'text'       => "*{$title}*\n{$message}",
                    'parse_mode' => 'Markdown',
                ]);
                if (!$resp->successful()) {
                    throw new \RuntimeException('Telegram HTTP ' . $resp->status());
                }
            } elseif ($driver === 'webhook') {
                $url = trim((string) ($cfg['url'] ?? ''));
                if ($url === '') {
                    throw new \RuntimeException('Missing webhook URL');
                }
                $resp = Http::timeout(10)->post($url, [
                    'event'   => $event,
                    'title'   => $title,
                    'message' => $message,
                    'text'    => "{$title}: {$message}",
                ]);
                if (!$resp->successful()) {
                    throw new \RuntimeException('Webhook HTTP ' . $resp->status());
                }
            } elseif ($driver === 'email') {
                $to = trim((string) ($cfg['email'] ?? ''));
                if ($to === '') {
                    throw new \RuntimeException('Missing recipient email');
                }
                Mail::raw($message, static function ($mail) use ($to, $title) {
                    $mail->to($to)->subject($title);
                });
            }

            NotificationLog::create([
                'channel_id' => $channel['id'] ?? null,
                'event'      => $event,
                'title'      => $title,
                'message'    => $message,
                'status'     => 'sent',
            ]);

            return true;
        } catch (\Throwable $e) {
            try {
                NotificationLog::create([
                    'channel_id' => $channel['id'] ?? null,
                    'event'      => $event,
                    'title'      => $title,
                    'message'    => $message,
                    'status'     => 'failed',
                    'error'      => $e->getMessage(),
                ]);
            } catch (\Throwable) {
            }

            return false;
        }
    }
}
