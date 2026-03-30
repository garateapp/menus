<?php

namespace App\Models;

use App\Enums\MenuStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DailyMenu extends Model
{
    use HasFactory;

    protected $fillable = [
        'weekly_menu_id',
        'menu_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'menu_date' => 'date:Y-m-d',
            'status' => MenuStatus::class,
        ];
    }

    public function weeklyMenu(): BelongsTo
    {
        return $this->belongsTo(WeeklyMenu::class);
    }

    public function menuOptions(): HasMany
    {
        return $this->hasMany(MenuOption::class);
    }

    public function selections(): HasMany
    {
        return $this->hasMany(MenuSelection::class);
    }
}
