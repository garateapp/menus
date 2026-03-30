<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WeeklyMenu;

class WeeklyMenuPolicy
{
    public function view(User $user, WeeklyMenu $weeklyMenu): bool
    {
        return $user->hasRole('SuperAdmin') || $weeklyMenu->supplier_id === $user->id;
    }

    public function update(User $user, WeeklyMenu $weeklyMenu): bool
    {
        return $this->view($user, $weeklyMenu);
    }
}
