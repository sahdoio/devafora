<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Support\Posts\MarkdownPost;
use App\Support\Posts\PostRepository;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PostController extends Controller
{
    public function index(PostRepository $posts): Response
    {
        return Inertia::render('Post/Index', [
            'posts' => $posts->published()
                ->map(fn (MarkdownPost $post): array => $post->toListArray())
                ->values()
                ->all(),
        ]);
    }

    public function show(string $slug, PostRepository $posts): Response
    {
        return Inertia::render('Post/Show', [
            'post' => $posts->findPublishedOrFail($slug)->toDetailArray(),
        ]);
    }

    /**
     * Serve a media file (image, PDF, ...) stored inside a post folder.
     * Media is shared across languages, so any published translation exposes it.
     */
    public function asset(string $slug, string $path, PostRepository $posts): BinaryFileResponse
    {
        if (! $posts->isPublishedInAnyLocale($slug)) {
            throw new NotFoundHttpException("Post [{$slug}] not found.");
        }

        $file = $posts->assetPath($slug, $path);

        if ($file === null) {
            throw new NotFoundHttpException("Asset [{$path}] not found.");
        }

        return response()->file($file);
    }
}
