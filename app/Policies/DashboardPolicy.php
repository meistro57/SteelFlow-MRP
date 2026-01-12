<?php

namespace App\Policies;

use App\Models\Dashboard;
use App\Models\User;

class DashboardPolicy
{
    /**
     * Determine whether the user can view any dashboards.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the dashboard.
     */
    public function view(User $user, Dashboard $dashboard): bool
    {
        return $dashboard->user_id === $user->id || $dashboard->is_shared;
    }

    /**
     * Determine whether the user can create dashboards.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the dashboard.
     */
    public function update(User $user, Dashboard $dashboard): bool
    {
        return $dashboard->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the dashboard.
     */
    public function delete(User $user, Dashboard $dashboard): bool
    {
        return $dashboard->user_id === $user->id;
    }
}
