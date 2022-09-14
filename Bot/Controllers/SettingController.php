<?php

namespace Bot\Controllers;

use Bot\Models\User;
use Bot\Util\Helpers;
use Bot\Util\KeyboardStack;
use Bot\Util\SettingService;

class SettingController
{
    public static function show($tg)
    {
        return function () use ($tg) {
            $update = $tg->getUpdate();

            User::where('telegram_id', $update->message->from->id)
                ->first()->update(['last_action' => 'settings']);

            $tg->sendMessage([
                'chat_id' => $update->message->from->id,
                'text' => "<b>⚙️ تنظیمات</b>\nدر این بخش می‌توانید تنظیمات حساب خود را انجام دهید.",
                'parse_mode' => 'HTML',
                'reply_to_message_id' => $update->message->message_id,
                'reply_markup' => KeyboardStack::settingKeyboard()
            ]);
        };
    }

    public static function updateFullName($tg)
    {
        return function () use ($tg) {
            $callbackQuery = ($tg->getUpdate())->callback_query;
            $tgUser = $callbackQuery->from;
            $fullName = $tgUser->first_name . ' ' . $tgUser->last_name;

            $dbUser = User::where('telegram_id', $tgUser->id)->first()->update([
                'full_name' => $fullName,
                'telegram_username' => $tgUser->username
            ]);

            $tg->answerCallbackQuery([
                'callback_query_id' => $callbackQuery->id,
                'text' => 'نام و نام خانوادگی شما به‌روز گردید: ' . $fullName
            ]);
        };
    }

    public static function linkNameToProfile($tg)
    {
        return function () use ($tg) {
            $callbackQuery = ($tg->getUpdate())->callback_query;

            $dbUser = User::where('telegram_id', $callbackQuery->from->id)->first();

            if (! $dbUser->isVip()) {
                $tg->answerCallbackQuery(['callback_query_id' => $callbackQuery->id, 'text' => '❗️ این قابلیت تنها برای کاربران ویژه فعال است!']);
                die();
            }

            $settings = SettingService::handle($dbUser->settings);
            $settings->linked_name = ! $settings->linked_name;
            $dbUser->update(['settings' => json_encode($settings)]);

            $text = ($settings->linked_name) ? '✅ نام شما در آمارها لینک خواهد شد!' : '✅ دیگر نام شما در آمارها لینک نمی‌شود.';
            $tg->answerCallbackQuery([
                'callback_query_id' => $callbackQuery->id,
                'text' => $text
            ]);
        };
    }
}