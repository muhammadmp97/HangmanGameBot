<?php

namespace Bot\Controllers;

use Bot\Models\Game;
use Bot\Models\Level;
use Bot\Models\User;
use Bot\Util\LevelFactory;
use Bot\Util\KeyboardStack;
use Bot\Util\Helpers;
use Carbon\Carbon;

class GameController
{
    public static function new($tg)
    {
        return function () use ($tg) {
            $tgUserId = ($tg->getUpdate())->message->from->id;

            $dbUser = User::where('telegram_id', $tgUserId)->first();
            if ($dbUser->hasIncompleteGame()) {
                $tg->sendMessage([
                    'chat_id' => $tgUserId,
                    'text' => '❕ شما یک بازی تمام‌نشده دارید، آن را تمام کنید!',
                    'reply_to_message_id' => $tg->update->message->id,
                    'reply_markup' => KeyboardStack::startKeyboard(true)
                ]);
            } else {
                $game = Game::create(['user_id' => $dbUser->id, 'hints' => ($dbUser->isVip() ? VIP_HINTS : FREE_HINTS)]);
                $dbUser->increment('games_count');
                $dbUser->update(['last_action' => 'new_game']);

                $level = LevelFactory::make($game->id, 1);

                $tg->sendMessage([
                    'chat_id' => $tgUserId,
                    'text' => $level['screen'],
                    'parse_mode' => 'html',
                    'reply_markup' => KeyboardStack::gameKeyboard($level['id'])
                ]);
            }
        };
    }

    public static function continue($tg)
    {
        return function () use ($tg) {
            $tgUserId = ($tg->getUpdate())->message->from->id ?? ($tg->getUpdate())->callback_query->from->id();

            $dbUser = User::where('telegram_id', $tgUserId)->first();
            if (! $dbUser->hasIncompleteGame()) {
                $tg->sendMessage([
                    'chat_id' => $tgUserId,
                    'text' => '❕ شما هیچ بازی تمام نشده‌ای ندارید!',
                    'reply_to_message_id' => $tg->update->message->id,
                    'reply_markup' => KeyboardStack::startKeyboard()
                ]);
            } else {
                $dbUser->update(['last_action' => 'continue_game']);
                $levelRecord = Game::where('user_id', $dbUser->id)->where('status', '=', 1)->whereNull('won_at')->first()->levels()->latest()->first();
                $level = LevelFactory::load($levelRecord->id);

                $tg->sendMessage([
                    'chat_id' => $tgUserId,
                    'text' => $level['screen'],
                    'parse_mode' => 'html',
                    'reply_markup' => KeyboardStack::gameKeyboard($level['id'])
                ]);
            }
        };
    }

    public static function sendChar($tg)
    {
        return function ($levelId, $char) use ($tg) {
            $update = $tg->getUpdate();
            $level = Level::find($levelId);
            $game = $level->game;

            if (! $level) die(); // I will delete the level records!

            if ($game->isClosed()) {
                $tg->answerCallbackQuery(['callback_query_id' => $update->callback_query->id]);
                die();
            }

            // Reject duplicated answer
            if (in_array(strtolower($char), str_split($level->user_answers))) {
                $tg->answerCallbackQuery(['callback_query_id' => $update->callback_query->id]);
                die();
            }

            $level->update(['user_answers' => $level->user_answers . strtolower($char)]);

            // Check losers
            $wrongAnswers = Helpers::calcWrongAnswers($level->word, $level->user_answers);
            if ($wrongAnswers == MAX_WRONG) {
                $level->game->update(['status' => 0]);
            }

            // Check winners
            $gameIsCompleted = false;
            $correctAnswers = Helpers::calcCorrectAnswers($level->word, $level->user_answers);
            if ($correctAnswers == strlen($level->word)) {
                $score = Helpers::calculateScore($game->current_level, $wrongAnswers);
                $game->increment('score', $score);

                if ($game->current_level < 7) {
                    $game->increment('current_level');
                    $newLevel = LevelFactory::make($game->id, $game->current_level);
                } else {
                    $rounedScore = round($game->score);
                    $game->update(['won_at' => Carbon::now()->toDateTimeString()]);
                    User::where('telegram_id', $update->callback_query->from->id)->first()->increment('score', $rounedScore);
                    $gameIsCompleted = true;
                }
            }

            $updatedLevel = LevelFactory::load($level->id);

            // Prepare the inline keyboard
            if ($wrongAnswers == MAX_WRONG) {
                $keyboard = KeyboardStack::emptyKeyboard();
            } elseif ($correctAnswers == strlen($level->word) && ! $gameIsCompleted) {
                $keyboard = KeyboardStack::nextLevelKeyboard($newLevel['id']);
            } elseif ($gameIsCompleted) {
                $keyboard = KeyboardStack::emptyKeyboard($level->id);
            } else {
                $keyboard = KeyboardStack::gameKeyboard($level->id);
            }

            $tg->editMessageText([
                'chat_id' => $update->callback_query->from->id,
                'message_id' => $update->callback_query->message->message_id,
                'text' => $updatedLevel['screen'],
                'parse_mode' => 'html',
                'reply_markup' => $keyboard
            ]);

            // You win, message!
            if ($gameIsCompleted) {
                $tg->answerCallbackQuery(['callback_query_id' => $update->callback_query->id, 'text' => "✌️ شما برنده شدید و {$rounedScore} امتیاز کسب کردید!"]);
            }

            // Delete the levels after a game
            if ($gameIsCompleted || $game->status == 0) {
                Level::where('game_id', $game->id)->delete();
            }
        };
    }

    public static function nextLevel($tg)
    {
        return function($levelId) use ($tg) {
            $update = $tg->getUpdate();
            $level = Level::find($levelId);

            if ($level->game->isClosed()) {
                $tg->answerCallbackQuery(['callback_query_id' => $update->callback_query->id]);
            } else {
                $levelScreen = LevelFactory::load($levelId);

                $tg->editMessageText([
                    'chat_id' => $update->callback_query->from->id,
                    'message_id' => $update->callback_query->message->message_id,
                    'text' => $levelScreen['screen'],
                    'parse_mode' => 'html',
                    'reply_markup' => KeyboardStack::gameKeyboard($level->id)
                ]);
            }
        };
    }

    public static function hint($tg)
    {
        return function($levelId) use ($tg) {
            $update = $tg->getUpdate();
            $level = Level::find($levelId);
            $hints = $level->game->hints;

            if ($level->game->isClosed()) {
                $tg->answerCallbackQuery(['callback_query_id' => $update->callback_query->id]);
                die();
            }

            if ($hints > 0) {
                $level->game->decrement('hints');
            }

            $text = ($level->game->hints > 0) ? 'یک راهنمایی: ' . Helpers::getHint($level->word, $level->user_answers) : '😑 کاری از دست ما ساخته نیست!';
            $tg->answerCallbackQuery([
                'callback_query_id' => $update->callback_query->id,
                'text' => $text
            ]);
        };
    }

}