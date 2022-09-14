<?php

namespace Bot\Controllers;

use Bot\Models\User;
use Bot\Util\KeyboardStack;

class ContactController
{
    public static function show($tg)
    {
        return function () use ($tg) {
            $message = ($tg->getUpdate())->message;
            $tgUser = $message->from;

            User::where('telegram_id', $tgUser->id)->first()->update(['last_action' => 'contact']);

            $tg->sendMessage([
                'chat_id' => $tgUser->id,
                'text' => "📬 تماس با پشتیبانی\nاکنون هر متنی بنویسید، برای پشتیبانی ارسال خواهد شد. شما می‌توانید پیشنهاد یا انتقاد خود را بنویسید تا در ویرایش‌های بعدی در نظر گرفته شوند، یا اینکه اطلاعات تماس خود را بفرستید تا تبلیغات شما در بازی درج شود.",
                'reply_to_message_id' => $message->message_id
            ]);

            die();
        };
    }

    public static function sendMessage($tg)
    {
        $message = ($tg->getUpdate())->message;
        $user = $message->from;

        $formatedText = "پیام ارسالی از {$user->first_name} {$user->last_name} ({$user->id}):\n\n$message->text";

        $tg->sendMessage([
            'chat_id' => DEV_TG_ID,
            'text' => $formatedText
        ]);

        $tg->sendMessage([
            'chat_id' => $user->id,
            'text' => '✅ پیام شما برای پشتیبانی ارسال گردید.',
            'reply_to_message_id' => $message->message_id
        ]);

        die();
    }
}