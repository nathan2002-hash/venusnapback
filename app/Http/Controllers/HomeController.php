<?php

namespace App\Http\Controllers;

use App\Models\LinkShare;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function terms()
    {
        return view('terms', [
        ]);
    }

    public function privacy()
    {
        return view('policy', [
        ]);
    }

    public function home()
    {
        $posts = $this->getFeaturedPostsForCarousel(6)->values()->all();
        return view('welcome', [
            'posts' => $posts
        ]);
    }


    public function blocked()
    {
        return view('auth.blocked', [
        ]);
    }

    public function childsafety()
    {
        return view('child', [
        ]);
    }

    public function wdelcome()
    {
        return view('landing', [
        ]);
    }

    public function welcome()
    {
        $posts = $this->getFeaturedPostsForCarousel();

        return view('landing', [
            'posts' => $posts
        ]);
    }

    private function getFeaturedPostsForCarousel($limit = 6)
    {
        // Hardcoded array of specific post IDs
        $featuredPostIds = [469, 411, 423, 397, 409, 437]; // Replace with your actual post IDs

        // Get featured posts with their first media and album info
        $posts = Post::with([
                'postmedias' => function ($query) {
                    $query->orderBy('sequence_order')
                        ->limit(1); // Only get first media
                },
                'album.supporters' // Include supporters relation
            ])
            ->whereIn('id', $featuredPostIds) // Use whereIn with hardcoded IDs
            ->where('status', 'active')
            ->where('visibility', 'public')
            ->orderByRaw('FIELD(id, ' . implode(',', $featuredPostIds) . ')') // Maintain the order
            ->limit($limit)
            ->get();

        // Format the posts for carousel
        $formattedPosts = $posts->map(function ($post) {
            $album = $post->album;
            $firstMedia = $post->postmedias->first();

            // Get image URL
            $imageUrl = $firstMedia && $firstMedia->file_path_compress
                ? generateSecureMediaUrl($firstMedia->file_path_compress)
                : 'https://images.unsplash.com/photo-1579546929662-711aa81148cf?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80'; // Fallback image

            // Truncate description
            $description = $post->description ?: ($album ? "Check out this snap from the {$album->name} album!" : 'Amazing creation on Venusnap');
            $truncatedDescription = Str::limit($description, 120);

            // Album info
            $albumName = $album ? $album->name : 'Unknown Album';
            $supporterCount = $album ? $album->supporters->count() : 0;

            return [
                'id' => $post->id,
                'description' => $truncatedDescription,
                'image_url' => $imageUrl,
                'album_name' => $albumName,
                'supporters' => $supporterCount, // ✅ added
                'created_at' => $post->created_at->diffForHumans(),
            ];
        });

        return $formattedPosts;
    }

}
