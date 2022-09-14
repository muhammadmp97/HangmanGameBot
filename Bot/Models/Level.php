<?php

namespace Bot\Models;

use Illuminate\Database\Eloquent\Model;

class Level extends Model
{
    protected $guarded = [];
    protected $with = ['game'];

    public function game()
    {
        return $this->belongsTo(Game::class, 'game_id');
    }

    public function getUpdatedAtColumn() {
        return null;
    }
}