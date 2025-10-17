<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Post\CreatePostAction;
use App\Actions\Post\DeletePostAction;
use App\Actions\Post\SendPostNewsletterAction;
use App\Actions\Post\UpdatePostAction;
use App\Data\PostData;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Models\Profile;
use Inertia\Inertia;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $posts = Post::with('profile')
            ->latest('created_at')
            ->get();

        return Inertia::render('Admin/Posts/Index', [
            'posts' => PostResource::collection($posts)->resolve(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $profiles = Profile::all();

        return Inertia::render('Admin/Posts/Create', [
            'profiles' => $profiles,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePostRequest $storePostRequest, CreatePostAction $createPostAction)
    {
        $data = $storePostRequest->validated();

        // Add image file if present
        if ($storePostRequest->hasFile('image')) {
            $data['image'] = $storePostRequest->file('image');
        }

        $createPostAction->execute(PostData::from($data));

        return redirect()->route('admin.posts.index')
            ->with('success', 'Post created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        return Inertia::render('Admin/Posts/Show', [
            'post' => (new PostResource($post->load('profile')))->resolve(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Post $post)
    {
        $profiles = Profile::all();

        return Inertia::render('Admin/Posts/Edit', [
            'post' => (new PostResource($post->load('profile')))->resolve(),
            'profiles' => $profiles,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePostRequest $updatePostRequest, Post $post, UpdatePostAction $updatePostAction)
    {
        $data = $updatePostRequest->validated();

        // Add image file if present
        if ($updatePostRequest->hasFile('image')) {
            $data['image'] = $updatePostRequest->file('image');
        }

        $updatePostAction->execute($post, PostData::from($data));

        return redirect()->route('admin.posts.index')
            ->with('success', 'Post updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post, DeletePostAction $deletePostAction)
    {
        $deletePostAction->execute($post);

        return redirect()->route('admin.posts.index')
            ->with('success', 'Post deleted successfully.');
    }

    /**
     * Preview the post.
     */
    public function preview(Post $post)
    {
        return Inertia::render('Admin/Posts/Preview', [
            'post' => (new PostResource($post->load('profile')))->resolve(),
        ]);
    }

    /**
     * Send newsletter for the post.
     */
    public function sendNewsletter(Post $post, SendPostNewsletterAction $sendPostNewsletterAction)
    {
        // If newsletter was already sent, force resend (user already confirmed)
        $force = $post->newsletter_sent_at !== null;

        $result = $sendPostNewsletterAction->execute($post, $force);

        if ($result['success']) {
            return redirect()->back()->with('success', $result['message']);
        }

        return redirect()->back()->with('error', $result['message']);
    }
}
