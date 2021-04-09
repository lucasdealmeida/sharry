<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentRequest;
use App\Mail\NotifyEventOwnerAboutNewComment;
use App\Models\Comment;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class EventCommentController extends Controller
{
    public function store(CommentRequest $request, Event $event)
    {
        $comment = $event->comments()->create([
            'nick_name' => $request->get('nick_name'),
            'content'   => $request->get('content'),
            'user_id'   => Auth::user()->id,
        ]);

        Mail::send(new NotifyEventOwnerAboutNewComment($comment));

        return response()->json([], 201);
    }

    public function destroy(Event $event, Comment $comment)
    {
        $this->authorize('destroy', $comment);

        $comment->delete();

        return response()->json([], 204);
    }
}
