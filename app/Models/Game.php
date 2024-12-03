<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    /** @use HasFactory<\Database\Factories\GameFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'board_state' => 'json',
        ];
    }

    protected $fillable = [
        'player_one_id',
        'player_two_id',
        'board_state',
        'game_state',
        'game_type',
    ];

    public function playerOne()
    {
        return $this->belongsTo(User::class, 'player_one_id');
    }

    public function playerTwo()
    {
        return $this->belongsTo(User::class, 'player_two_id');
    }
}
