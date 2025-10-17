<?php

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
        $subscriptions = NewsletterSubscription::query()
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return Inertia::render('Admin/Newsletter/Index', [
            'subscriptions' => NewsletterSubscriptionResource::collection($subscriptions),
        ]);
    }

    /**
     * Display the specified newsletter subscription.
     */
    public function show(NewsletterSubscription $newsletter)
    {
        return Inertia::render('Admin/Newsletter/Show', [
            'subscription' => (new NewsletterSubscriptionResource($newsletter))->resolve(),
        ]);
    }

    /**
     * Toggle subscription status.
     */
    public function toggleStatus(
        NewsletterSubscription $newsletter,
        ToggleNewsletterStatusAction $action
    ) {
        $subscription = $action->execute($newsletter);

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
        NewsletterSubscription $newsletter,
        DeleteNewsletterSubscriptionAction $action
    ) {
        $action->execute($newsletter);

        return redirect()->route('admin.newsletter.index')
            ->with('success', 'Subscription deleted successfully.');
    }
}