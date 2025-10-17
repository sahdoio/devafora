<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Newsletter\SubscribeToNewsletterAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\NewsletterSubscribeRequest;
use Illuminate\Http\JsonResponse;

class NewsletterController extends Controller
{
    public function subscribe(
        NewsletterSubscribeRequest $newsletterSubscribeRequest,
        SubscribeToNewsletterAction $subscribeToNewsletterAction
    ): JsonResponse {
        $newsletterSubscription = $subscribeToNewsletterAction->execute(
            email: $newsletterSubscribeRequest->validated('email'),
            name: $newsletterSubscribeRequest->validated('name')
        );

        return response()->json([
            'message' => 'Inscrição realizada com sucesso! Verifique seu e-mail.',
            'data' => [
                'email' => $newsletterSubscription->email,
                'subscribed' => true,
            ]
        ], 201);
    }
}
