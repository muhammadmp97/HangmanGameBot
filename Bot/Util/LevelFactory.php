<?php

namespace Bot\Util;

use Bot\Models\Level;
use Bot\Models\Word;
use Bot\Util\Helpers;

class LevelFactory
{
    public static function make($gameId, $level)
    {
        $length = $level + 3; // 1+3=4, we start with 4letter words
        $word = Word::where('length', $length)->inRandomOrder()->limit(1)->first();
        $level = Level::create(['game_id' => $gameId, 'word' => $word->title]);

        $screen = self::getASCII();
        $screen .= str_repeat('___  ', $length);

        return [
            'id' => $level->id,
            'screen' => $screen
        ];
    }

    public static function load($levelId)
    {
        $level = Level::find($levelId);

        $wrongAnswers = Helpers::calcWrongAnswers($level->word, $level->user_answers);
        $correctAnswers = Helpers::calcCorrectAnswers($level->word, $level->user_answers);
        $screen = static::getASCII($wrongAnswers);
        $screen .= static::getUserAnswerForm($level->word, $level->user_answers);
        $screen .= "\n";
        $screen .= static::getWrongAnswers($level->word, $level->user_answers);

        if ($wrongAnswers == MAX_WRONG || $correctAnswers == strlen($level->word)) {
            $word = Word::where('title', $level->word)->first();

            if ($wrongAnswers == MAX_WRONG) {
                $screen .= "\n\n🙁 جواب درست <b>{$word->title}</b> به معنای <b>«{$word->translation}»</b> بود.";
            }

            if ($correctAnswers == strlen($level->word)) {
                $screen .= "\n\n😎 تبریک! واژه‌ی <b>{$word->title}</b> به معنای <b>«{$word->translation}»</b> است!";
            }
        }

        return [
            'id' => $level->id,
            'screen' => $screen
        ];
    }

    private static function getASCII($mistakes = 0)
    {
        $arr = [
            "_________
    |/        
    |              
    |                
    |                 
    |               
    |                   
    |___\n\n",
            "_________
    |/   |      
    |              
    |                
    |                 
    |               
    |                   
    |___\n\n",
            "_________       
    |/   |              
    |   (_)
    |                         
    |                       
    |                         
    |                          
    |___\n\n",
            "________               
    |/   |                   
    |   (_)                  
    |    |                     
    |    |                    
    |                           
    |                            
    |___\n\n",
            "_________             
    |/   |               
    |   (_)                   
    |   /|                     
    |    |                    
    |                        
    |                          
    |___\n\n",
            "_________              
    |/   |                     
    |   (_)                     
    |   /|\                    
    |    |                       
    |                             
    |                            
    |___\n\n",
            "________                   
    |/   |                         
    |   (_)                      
    |   /|\                             
    |    |                          
    |   /                            
    |                                  
    |___\n\n",
            "________
    |/   |     
    |   (_)    
    |   /|\           
    |    |        
    |   / \        
    |               
    |___\n\n"
        ];

        return $arr[$mistakes];
    }

    private static function getUserAnswerForm($word, $answers)
    {
        $answers = str_split($answers);
        $output = '';

        foreach (str_split($word) as $letter) {
            if (in_array($letter, $answers)) {
                $output .= "<u> " . strtoupper($letter) . " </u> ";
            } else {
                $output .= " __  ";
            }
        }

        return $output;
    }

    private static function getWrongAnswers($word, $answers)
    {
        $word = str_split($word);
        $output = '';

        foreach (str_split($answers) as $letter) {
            if (! in_array($letter, $word)) {
                $output .= "<s> " . strtoupper($letter) . " </s> ";
            }
        }

        return $output;
    }
}