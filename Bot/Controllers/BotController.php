<?php

namespace Bot\Controllers;

use Bot\Models\User;
use Bot\Util\KeyboardStack;

class BotController
{
    public static function start($tg)
    {
        return function () use ($tg) {
            $update = $tg->getUpdate();
            $tgUser = $update->message->from;

            $dbUser = User::where('telegram_id', $tgUser->id)->first();
            if ($dbUser) {
                $hasIncompleteGame = $dbUser->hasIncompleteGame() ? true : false;
                $tg->sendMessage([
                    'chat_id' => $tgUser->id,
                    'text' => "از دوباره دیدن شما خوشحالیم! 😃\nبازی جلاد، یک بازی جذاب و مفید است که از تک تک لحظات آن لذت خواهید برد!",
                    'reply_to_message_id' => $update->message->message_id,
                    'reply_markup' => KeyboardStack::startKeyboard($hasIncompleteGame)
                ]);
            } else {
                User::create([
                    'telegram_id' => $tgUser->id,
                    'telegram_username' => $tgUser->username,
                    'full_name' => $tgUser->first_name . ' ' . $tgUser->last_name,
                    'settings' => json_encode(json_decode("{}"))
                ]);

                $tg->sendMessage([
                    'chat_id' => $tgUser->id,
                    'text' => "به بازی جلاد خوش آمدید! 😎\nبازی کنید، واژگان انگلیسی را مرور کنید و با دیگر کاربران وارد رقابت شوید!",
                    'reply_to_message_id' => $update->message->message_id,
                    'reply_markup' => KeyboardStack::startKeyboard()
                ]);
            }
        };
    }
}