<?php

namespace Bot\Controllers;

use Bot\Models\User;
use Bot\Util\KeyboardStack;

class StatisticsController
{
    public static function show($tg)
    {
        return function () use ($tg) {
            $message = ($tg->getUpdate())->message;
            $tgUser = $message->from;

            User::where('telegram_id', $tgUser->id)->first()->update(['last_action' => 'statistics']);

            $tg->sendMessage([
                'chat_id' => $tgUser->id,
                'text' => 'به زودی...',
                'reply_to_message_id' => $message->message_id
            ]);
        };
    }
}