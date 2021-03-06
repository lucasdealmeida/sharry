<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentRequest;
use App\Mail\NotifyNewsOwnerAboutNewComment;
use App\Models\Comment;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class NewsCommentController extends Controller
{
    public function store(CommentRequest $request, News $news)
    {
        $comment = $news->comments()->create([
            'nick_name' => $request->get('nick_name'),
            'content'   => $request->get('content'),
            'user_id'   => Auth::user()->id,
        ]);

        Mail::send(new NotifyNewsOwnerAboutNewComment($comment));

        return response()->json([], 201);
    }

    public function destroy(News $news, Comment $comment)
    {
        $this->authorize('destroy', $comment);

        $comment->delete();

        return response()->json([], 204);
    }
}
