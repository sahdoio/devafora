<?php

namespace App\Http\Controllers\Api;

use App\Actions\Newsletter\SubscribeToNewsletterAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\NewsletterSubscribeRequest;
use Illuminate\Http\JsonResponse;

class NewsletterController extends Controller
{
    public function subscribe(
        NewsletterSubscribeRequest $request,
        SubscribeToNewsletterAction $subscribe
    ): JsonResponse {
        $subscription = $subscribe->execute(
            email: $request->validated('email'),
            name: $request->validated('name')
        );

        return response()->json([
            'message' => 'Inscrição realizada com sucesso! Verifique seu e-mail.',
            'data' => [
                'email' => $subscription->email,
                'subscribed' => true,
            ]
        ], 201);
    }
}
