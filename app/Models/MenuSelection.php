<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuSelection extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'daily_menu_id',
        'menu_option_id',
        'selected_at',
    ];

    protected function casts(): array
    {
        return [
            'selected_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function dailyMenu(): BelongsTo
    {
        return $this->belongsTo(DailyMenu::class);
    }

    public function menuOption(): BelongsTo
    {
        return $this->belongsTo(MenuOption::class);
    }
}
