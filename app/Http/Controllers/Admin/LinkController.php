<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Link\CreateLinkAction;
use App\Actions\Link\DeleteLinkAction;
use App\Actions\Link\UpdateLinkAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLinkRequest;
use App\Http\Requests\UpdateLinkRequest;
use App\Http\Resources\LinkResource;
use App\Models\Link;
use App\Models\Profile;
use Inertia\Inertia;

class LinkController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $links = Link::with('profile')->orderBy('order')->get();

        return Inertia::render('Admin/Links/Index', [
            'links' => LinkResource::collection($links)->resolve(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $profiles = Profile::all();

        return Inertia::render('Admin/Links/Create', [
            'profiles' => $profiles,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLinkRequest $request, CreateLinkAction $action)
    {
        $action->execute($request->validated());

        return redirect()->route('admin.links.index')
            ->with('success', 'Link created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Link $link)
    {
        return Inertia::render('Admin/Links/Show', [
            'link' => (new LinkResource($link->load('profile')))->resolve(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Link $link)
    {
        $profiles = Profile::all();

        return Inertia::render('Admin/Links/Edit', [
            'link' => (new LinkResource($link->load('profile')))->resolve(),
            'profiles' => $profiles,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLinkRequest $request, Link $link, UpdateLinkAction $action)
    {
        $action->execute($link, $request->validated());

        return redirect()->route('admin.links.index')
            ->with('success', 'Link updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Link $link, DeleteLinkAction $action)
    {
        $action->execute($link);

        return redirect()->route('admin.links.index')
            ->with('success', 'Link deleted successfully.');
    }
}
