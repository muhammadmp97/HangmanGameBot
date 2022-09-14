<?php

require './vendor/autoload.php';

use TeleBot\TeleBot;
use Bot\Controllers\BotController;
use Bot\Controllers\GameController;
use Bot\Controllers\SettingController;
use Bot\Controllers\UpgradeController;
use Bot\Controllers\ContactController;
use Bot\Controllers\StatisticsController;

$tg = new TeleBot(BOT_TOKEN);

try {
    ///////////////////////////
    ////  HANDLE COMMANDS  ////
    ///////////////////////////
    $tg->listen('/start', BotController::start($tg));
    $tg->listen('🏁 شروع بازی', GameController::new($tg));
    $tg->listen('🕓 ادامه‌ی بازی قبلی', GameController::continue($tg));
    $tg->listen('🔑 ارتقای حساب', UpgradeController::show($tg));
    $tg->listen('📬 تماس با پشتیبانی', ContactController::show($tg));
    $tg->listen('📊 آمارها', StatisticsController::show($tg));
    $tg->listen('lvl_%d_char_%c', GameController::sendChar($tg));
    $tg->listen('goto_lvl_%d', GameController::nextLevel($tg));
    $tg->listen('lvl_%d_hint', GameController::hint($tg));

    // Setting Routes
    $tg->listen('⚙️ تنظیمات', SettingController::show($tg));
    $tg->listen('setting:update_fullname_from_profile', SettingController::updateFullName($tg));
    $tg->listen('setting:link_name_to_profile', SettingController::linkNameToProfile($tg));


    ////////////////////////////
    ////     OTHER CODES    ////
    ////////////////////////////
    $dbUser = \Bot\Models\User::where('telegram_id', ($tg->getUpdate())->message->from->id)->first();
    if ($dbUser->last_action == 'contact') {
        ContactController::sendMessage($tg);
    }


    // TODO Broadcast
    // TODO Upgrade

} catch (\Exception $e) {
    tl($e->getMessage());
} catch (\TeleBot\Exceptions\TeleBotException $e) {
    tl($e->getMessage($e));
} catch (\Error $e) {
    tl($e->getMessage($e));
}