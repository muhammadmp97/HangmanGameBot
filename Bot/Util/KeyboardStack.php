<?php

namespace Bot\Util;

use TeleBot\InlineKeyboard;
use TeleBot\ReplyKeyboard;

class KeyboardStack
{
    public static function startKeyboard($hasOpenGame = false)
    {
        $firstButtonLabel = $hasOpenGame ? '🕓 ادامه‌ی بازی قبلی' : '🏁 شروع بازی';

        return (new ReplyKeyboard(true, true))
            ->addButtons($firstButtonLabel, '📊 آمارها', '⚙️ تنظیمات', '🔑 ارتقای حساب', '📬 تماس با پشتیبانی')
            ->chunk(2)
            ->rightToLeft()
            ->get();
    }

    public static function gameKeyboard($levelId)
    {
        $keyboard = (new InlineKeyboard());

        foreach (range('A', 'Z') as $letter) {
            $keyboard->addButton($letter, null, null, "lvl_{$levelId}_char_{$letter}");
        }

        $keyboard->addButton('❓', null, null, "lvl_{$levelId}_hint");
        return $keyboard->chunk(8)->get();
    }

    public static function nextLevelKeyboard($levelId)
    {
        return (new InlineKeyboard())
            ->addButton('⬅️ مرحله بعد', null, null, "goto_lvl_{$levelId}")
            ->get();
    }

    public static function settingKeyboard()
    {
        return (new InlineKeyboard())
            ->addButton('🔄 به‌روزرسانی نام از پروفایل', null, null, 'setting:update_fullname_from_profile')
            ->addButton('🔗 لینک‌کردن نام به پروفایل (ویژه)', null, null, 'setting:link_name_to_profile')
            ->chunk(1)
            ->get();
    }

    public static function emptyKeyboard()
    {
        return (new InlineKeyboard())->get();
    }
}