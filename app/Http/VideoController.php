<?php

namespace App\Http\Controllers;

use App\Models\Video;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class VideoController extends Controller
{
    public function index(Request $request)
    {
        $query = Video::with(['user', 'category'])
            ->where('is_public', true)
            ->orderBy('created_at', 'desc');

        if ($request->has('category') && $request->category !== 'semua') {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        $videos = $query->paginate(10);

        return response()->json($videos);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'category_id' => 'required|exists:categories,id',
            'video' => 'required|file|mimes:mp4,mov,avi,mkv|max:51200', // 50MB max
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048', // 2MB max
            'hashtags' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Create directories if they don't exist
            if (!Storage::disk('public')->exists('videos')) {
                Storage::disk('public')->makeDirectory('videos');
            }
            if (!Storage::disk('public')->exists('thumbnails')) {
                Storage::disk('public')->makeDirectory('thumbnails');
            }

            // Upload video file
            $videoFile = $request->file('video');
            $videoFileName = time() . '_' . uniqid() . '.' . $videoFile->getClientOriginalExtension();
            $videoPath = $videoFile->storeAs('videos', $videoFileName, 'public');
            $videoUrl = Storage::url($videoPath);

            // Upload thumbnail if provided
            $thumbnailUrl = null;
            if ($request->hasFile('thumbnail')) {
                $thumbnailFile = $request->file('thumbnail');
                $thumbnailFileName = time() . '_thumb_' . uniqid() . '.' . $thumbnailFile->getClientOriginalExtension();
                $thumbnailPath = $thumbnailFile->storeAs('thumbnails', $thumbnailFileName, 'public');
                $thumbnailUrl = Storage::url($thumbnailPath);
            }

            // Process hashtags
            $hashtags = [];
            if ($request->hashtags) {
                $hashtags = array_filter(array_map('trim', explode(',', $request->hashtags)));
            }

            // Get video duration (optional - requires FFmpeg)
            $duration = 0;
            try {
                $videoFullPath = storage_path('app/public/' . $videoPath);
                if (function_exists('shell_exec')) {
                    $ffprobe = shell_exec("ffprobe -v quiet -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 \"$videoFullPath\" 2>&1");
                    if ($ffprobe && is_numeric(trim($ffprobe))) {
                        $duration = (int) trim($ffprobe);
                    }
                }
            } catch (\Exception $e) {
                // Continue without duration if FFmpeg is not available
            }

            // Create video record
            $video = Video::create([
                'user_id' => $request->user()->id,
                'category_id' => $request->category_id,
                'title' => $request->title,
                'description' => $request->description,
                'video_url' => $videoUrl,
                'thumbnail_url' => $thumbnailUrl,
                'duration' => $duration,
                'hashtags' => $hashtags,
                'is_public' => true,
                'likes_count' => 0,
                'comments_count' => 0,
                'shares_count' => 0,
                'views_count' => 0,
            ]);

            return response()->json([
                'message' => 'Video uploaded successfully',
                'video' => $video->load(['user', 'category'])
            ], 201);

        } catch (\Exception $e) {
            // Clean up uploaded files if database insert fails
            if (isset($videoPath) && Storage::disk('public')->exists($videoPath)) {
                Storage::disk('public')->delete($videoPath);
            }
            if (isset($thumbnailPath) && Storage::disk('public')->exists($thumbnailPath)) {
                Storage::disk('public')->delete($thumbnailPath);
            }

            return response()->json([
                'message' => 'Upload failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $video = Video::with(['user', 'category', 'comments.user'])
            ->findOrFail($id);
        
        // Increment view count
        $video->increment('views_count');
        
        return response()->json($video);
    }

    public function like(Request $request, $id)
    {
        $video = Video::findOrFail($id);
        $user = $request->user();

        if ($video->isLikedBy($user->id)) {
            $video->likes()->where('user_id', $user->id)->delete();
            $video->decrement('likes_count');
            $liked = false;
        } else {
            $video->likes()->create(['user_id' => $user->id]);
            $video->increment('likes_count');
            $liked = true;
        }

        return response()->json([
            'liked' => $liked,
            'likes_count' => $video->likes_count
        ]);
    }

    public function userVideos($userId)
    {
        $videos = Video::with(['user', 'category'])
            ->where('user_id', $userId)
            ->where('is_public', true)
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return response()->json($videos);
    }

    public function destroy(Request $request, $id)
    {
        $video = Video::findOrFail($id);
        
        // Check if user owns the video
        if ($video->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        try {
            // Delete files from storage
            if ($video->video_url) {
                $videoPath = str_replace('/storage/', '', $video->video_url);
                if (Storage::disk('public')->exists($videoPath)) {
                    Storage::disk('public')->delete($videoPath);
                }
            }

            if ($video->thumbnail_url) {
                $thumbnailPath = str_replace('/storage/', '', $video->thumbnail_url);
                if (Storage::disk('public')->exists($thumbnailPath)) {
                    Storage::disk('public')->delete($thumbnailPath);
                }
            }

            // Delete video record
            $video->delete();

            return response()->json([
                'message' => 'Video deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete video',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function share(Request $request, $id)
    {
        $video = Video::findOrFail($id);
        
        // Increment share count
        $video->increment('shares_count');

        return response()->json([
            'message' => 'Video shared successfully',
            'shares_count' => $video->shares_count
        ]);
    }

    public function search(Request $request)
    {
        $query = $request->get('q', '');
        
        if (empty($query)) {
            return response()->json([
                'data' => [],
                'message' => 'Please provide a search query'
            ]);
        }

        $videos = Video::with(['user', 'category'])
            ->where('is_public', true)
            ->where(function ($q) use ($query) {
                $q->where('title', 'LIKE', "%{$query}%")
                  ->orWhere('description', 'LIKE', "%{$query}%")
                  ->orWhereJsonContains('hashtags', $query);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($videos);
    }

    public function trending()
    {
        $videos = Video::with(['user', 'category'])
            ->where('is_public', true)
            ->where('created_at', '>=', now()->subDays(7)) // Last 7 days
            ->orderByRaw('(likes_count + comments_count + shares_count + views_count) DESC')
            ->limit(20)
            ->get();

        return response()->json($videos);
    }

    
}