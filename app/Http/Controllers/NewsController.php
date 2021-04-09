<?php

namespace App\Http\Controllers;

use App\Http\Requests\NewsRequest;
use App\Http\Resources\NewsCollection;
use App\Http\Resources\NewsResource;
use App\Models\News;
use Illuminate\Support\Facades\Auth;

class NewsController extends Controller
{
    public function index()
    {
        return NewsResource::collection(News::whereDate('created_at', now())->get());
    }

    public function store(NewsRequest $request)
    {
        News::create([
            'title'   => $request->get('title'),
            'content' => $request->get('content'),
            'user_id' => Auth::user()->id,
        ]);

        return response()->json([], 201);
    }

    public function update(NewsRequest $request, News $news)
    {
        $this->authorize('update', $news);

        $news->update([
            'title'   => $request->get('title'),
            'content' => $request->get('content'),
        ]);

        return response()->json([], 204);
    }

    public function destroy(News $news)
    {
        $this->authorize('destroy', $news);

        if ($news->comments()->count()){
            return response()->json([
                'error' => true,
                'message' => 'Can not delete news when it has comments.'
            ]);
        }

        $news->delete();

        return response()->json([], 204);
    }
}
