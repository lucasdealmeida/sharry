<?php

namespace App\Policies;

use App\Models\News;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class NewsPolicy
{
    use HandlesAuthorization;

    public function update(User $user, News $news)
    {
        return $news->user_id === $user->id;
    }

    public function destroy(User $user, News $news)
    {
        return $news->user_id === $user->id;
    }
}
