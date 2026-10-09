<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class AutocompleteBreweries extends Controller
{
    /**
     * Search for breweries based on a search term.
     * The search performs partial, case-insensitive matching against brewery names.
     *
     * @deprecated Use the Search Breweries endpoint instead.
     */
    public function __invoke(Request $request): JsonResponse|RedirectResponse
    {
        $query = $request->query('query');

        Log::warning('Deprecated autocomplete endpoint called', [
            'query' => $query,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'origin' => $request->header('Origin'),
            'referer' => $request->header('Referer'),
            'accept' => $request->header('Accept'),
            'forwarded_for' => $request->header('X-Forwarded-For'),
        ]);

        $successorUrl = route('v1.breweries.search', ['query' => $query]);

        $headers = [
            'Link' => '<'.$successorUrl.'>; rel="successor-version"',
        ];

        if ($this->isInBrownout()) {
            return response()->json(
                data: [
                    'message' => 'The autocomplete endpoint has been removed. Use /v1/breweries/search instead.',
                    'successor' => $successorUrl,
                ],
                status: Response::HTTP_GONE,
                headers: $headers,
            );
        }

        return redirect()
            ->to($successorUrl, Response::HTTP_TEMPORARY_REDIRECT)
            ->withHeaders($headers);
    }

    /**
     * Determine if the endpoint is in a scheduled brownout window.
     */
    protected function isInBrownout(): bool
    {
        return config('platform.autocomplete_brownout') && now()->minute < 10;
    }
}
