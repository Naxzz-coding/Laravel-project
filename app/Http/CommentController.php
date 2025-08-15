<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    public function index($videoId)
    {
        $comments = Comment::with(['user', 'replies.user'])
            ->where('video_id', $videoId)
            ->whereNull('parent_id')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($comments);
    }

    public function store(Request $request, $videoId)
    {
        $validator = Validator::make($request->all(), [
            'comment_text' => 'required|string|max:500',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $video = Video::findOrFail($videoId);

        $comment = Comment::create([
            'user_id' => $request->user()->id,
            'video_id' => $video->id,
            'parent_id' => $request->parent_id,
            'comment_text' => $request->comment_text,
        ]);

        $video->increment('comments_count');

        return response()->json([
            'message' => 'Comment added successfully',
            'comment' => $comment->load('user')
        ], 201);
    }
}