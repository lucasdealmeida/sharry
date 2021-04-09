<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EventPolicy
{
    use HandlesAuthorization;

    public function update(User $user, Event $event)
    {
        return $event->user_id === $user->id;
    }

    public function destroy(User $user, Event $event)
    {
        return $event->user_id === $user->id;
    }
}
