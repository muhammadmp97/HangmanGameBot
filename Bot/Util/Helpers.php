<?php

namespace Bot\Util;

class Helpers
{
    public static function calcWrongAnswers($word, $tries)
    {
        $tries = str_split($word . $tries);
        return count(array_diff($tries, str_split($word)));
    }

    public static function calcCorrectAnswers($word, $tries)
    {
        $correctAnswers = 0;
        foreach(str_split($word) as $letter) {
            if (in_array($letter, str_split($tries))) {
                $correctAnswers++;
            }
        }

        return $correctAnswers;
    }

    public static function calculateScore($level, $mistakes)
    {
        return ($level * LEVEL_SCORE) - ($mistakes * MISTAKE_DUES);
    }

    public static function getHint($word, $tries)
    {
        $hint = '';

        foreach (str_split($word) as $letter) {
            if (! in_array($letter, str_split($tries))) {
                $hint = $letter;
                break;
            }
        }

        return strtoupper($hint);
    }
}