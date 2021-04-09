<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CommentPolicy
{
    use HandlesAuthorization;

    public function destroy(User $user, Comment $comment)
    {
        return $comment->user_id === $user->id;
    }
}
