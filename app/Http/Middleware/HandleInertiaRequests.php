<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'auth' => [
                'user' => $request->user() ? $request->user()->load('roles', 'permissions') : null,
                'can' => $request->user() ? [
                    'viewUsers' => $request->user()->can('view-users'),
                    'manageUsers' => $request->user()->can('manage-users'),
                    'viewTransactions' => $request->user()->can('view-transactions'),
                    'createTransactions' => $request->user()->can('create-transactions'),
                    'viewCategories' => $request->user()->can('view-categories'),
                    'manageCategories' => $request->user()->can('manage-categories'),
                    'viewBudgets' => $request->user()->can('view-budgets'),
                    'manageBudgets' => $request->user()->can('manage-budgets'),
                    'viewReports' => $request->user()->can('view-reports'),
                    'manageSettings' => $request->user()->can('manage-settings'),
                ] : [],
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],
        ];
    }
}
