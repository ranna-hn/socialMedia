<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Http\Resources\UserResource;
use App\Http\Requests\StorePostRequest;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => new UserResource($request->user()),
            ],

            'locale' => app()->getLocale(),
            'translations' => fn () => __('econature.ui'),
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'attachmemtExtensions'=> StorePostRequest::$extensions,
            'attachmentExtensions'=> StorePostRequest::$extensions,

            'ziggy' => [
                'location' => $request->url(),
            ],
        ];
    }
}
