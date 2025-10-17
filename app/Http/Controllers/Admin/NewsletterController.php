<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Newsletter\DeleteNewsletterSubscriptionAction;
use App\Actions\Newsletter\ToggleNewsletterStatusAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\NewsletterSubscriptionResource;
use App\Models\NewsletterSubscription;
use Inertia\Inertia;

class NewsletterController extends Controller
{
    /**
     * Display a listing of newsletter subscriptions.
     */
    public function index()
    {
        $lengthAwarePaginator = NewsletterSubscription::query()
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return Inertia::render('Admin/Newsletter/Index', [
            'subscriptions' => NewsletterSubscriptionResource::collection($lengthAwarePaginator),
        ]);
    }

    /**
     * Display the specified newsletter subscription.
     */
    public function show(NewsletterSubscription $newsletterSubscription)
    {
        return Inertia::render('Admin/Newsletter/Show', [
            'subscription' => (new NewsletterSubscriptionResource($newsletterSubscription))->resolve(),
        ]);
    }

    /**
     * Toggle subscription status.
     */
    public function toggleStatus(
        NewsletterSubscription $newsletterSubscription,
        ToggleNewsletterStatusAction $toggleNewsletterStatusAction
    ) {
        $subscription = $toggleNewsletterStatusAction->execute($newsletterSubscription);

        $message = $subscription->isSubscribed()
            ? 'Subscription reactivated successfully.'
            : 'Subscription deactivated successfully.';

        return redirect()->route('admin.newsletter.index')
            ->with('success', $message);
    }

    /**
     * Remove the specified newsletter subscription.
     */
    public function destroy(
        NewsletterSubscription $newsletterSubscription,
        DeleteNewsletterSubscriptionAction $deleteNewsletterSubscriptionAction
    ) {
        $deleteNewsletterSubscriptionAction->execute($newsletterSubscription);

        return redirect()->route('admin.newsletter.index')
            ->with('success', 'Subscription deleted successfully.');
    }
}
