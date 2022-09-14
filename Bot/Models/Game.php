<?php

namespace Bot\Models;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    protected $guarded = [];

    public function levels()
    {
        return $this->hasMany(Level::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getUpdatedAtColumn() {
        return null;
    }

    public function isClosed()
    {
        return $this->status == 0 || $this->won_at != null;
    }
}