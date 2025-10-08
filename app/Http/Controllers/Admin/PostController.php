<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Post\CreatePostAction;
use App\Actions\Post\DeletePostAction;
use App\Actions\Post\UpdatePostAction;
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
    public function store(StorePostRequest $request, CreatePostAction $action)
    {
        $data = $request->validated();

        // Add image file if present
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image');
        }

        $action->execute($data);

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
    public function update(UpdatePostRequest $request, Post $post, UpdatePostAction $action)
    {
        $data = $request->validated();

        // Add image file if present
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image');
        }

        $action->execute($post, $data);

        return redirect()->route('admin.posts.index')
            ->with('success', 'Post updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post, DeletePostAction $action)
    {
        $action->execute($post);

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
}
