<?php

namespace App\Models;

use App\Enums\MenuStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WeeklyMenu extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'title',
        'week_start_date',
        'week_end_date',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'week_start_date' => 'date:Y-m-d',
            'week_end_date' => 'date:Y-m-d',
            'status' => MenuStatus::class,
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supplier_id');
    }

    public function dailyMenus(): HasMany
    {
        return $this->hasMany(DailyMenu::class);
    }
}
