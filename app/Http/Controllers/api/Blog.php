<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    /**
     * Get a single post by its key.
     */
    public function getPost(Request $request)
    {
        $key = $request->input('key');
        $post = Blog::where('key', $key)->first();

        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        return response()->json($post, 200);
    }

    /**
     * Get all posts.
     */
    public function getAllPosts()
    {
        $posts = Blog::all();

        return response()->json($posts, 200);
    }

    /**
     * Edit a post by its key.
     */
    public function editPost(Request $request)
    {
        $key = $request->input('key');
        $post = Blog::where('key', $key)->first();

        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        $content = base64_encode($request->input('content'));
        $hash = hash('sha256', $content);

        $post->content = $content;
        $post->hash = $hash;
        $post->save();

        return response()->json(['message' => 'Post updated successfully'], 200);
    }

    /**
     * Delete a post by its key.
     */
    public function deletePost(Request $request)
    {
        $key = $request->input('key');
        $post = Blog::where('key', $key)->first();

        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        $post->delete();

        return response()->json(['message' => 'Post deleted successfully'], 200);
    }
}
