<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuOption extends Model
{
    use HasFactory;

    protected $appends = [
        'remaining_quota',
        'is_sold_out',
    ];

    protected $fillable = [
        'daily_menu_id',
        'title',
        'description',
        'image_path',
        'quota',
        'is_visible',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_visible' => 'boolean',
            'quota' => 'integer',
        ];
    }

    public function dailyMenu(): BelongsTo
    {
        return $this->belongsTo(DailyMenu::class);
    }

    public function selections(): HasMany
    {
        return $this->hasMany(MenuSelection::class);
    }

    public function getRemainingQuotaAttribute(): ?int
    {
        if ($this->quota === null) {
            return null;
        }

        $takenSeats = $this->selections_count
            ?? ($this->relationLoaded('selections') ? $this->selections->count() : $this->selections()->count());

        return max($this->quota - $takenSeats, 0);
    }

    public function getIsSoldOutAttribute(): bool
    {
        return $this->quota !== null && $this->remaining_quota <= 0;
    }
}
