<?php

namespace app\Notifications\Channels;

use Blacky0892\Max\Facades\Max;
use Illuminate\Notifications\Notification;

class MaxBotChannel
{
    public function send($notifiable, Notification $notification)
    {
        // Проверяем, есть ли в уведомлении метод toMaxBot
        if (!method_exists($notification, 'toMaxBot')) {
            throw new \Exception('Notification must have a toMaxBot() method.');
        }

        // Получаем данные сообщения из уведомления
        $message = $notification->toMaxBot($notifiable);

        // Получаем Chat ID (из маршрутизации или из конфига по умолчанию)
        $chatId = $notifiable->routeNotificationFor('max_bot') ?? config('max.token');
        // Примечание: лучше явно передавать chat_id через Notification::route()

        // Отправляем сообщение через Facade пакета
        Max::client()->sendMessageToChat(
            chatId: $chatId,
            text: $message['text'],
            format: $message['format'] ?? 'markdown'
        );
    }
}
