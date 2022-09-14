<?php

namespace Bot\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $guarded = [];
    protected $dates = ['expired_at'];

    public function games()
    {
        return $this->hasMany(Game::class);
    }

    public function getUpdatedAtColumn() {
        return null;
    }

    public function hasIncompleteGame()
    {
        return $this->games()->where('status', '=', 1)
            ->whereNull('won_at')
            ->exists();
    }

    public function isVip()
    {
        return $this->expired_at->isFuture();
    }
}