<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Store;

/**
 * SetActiveStore Middleware
 *
 * After login, sets the active store in the session.
 * - Super admin (company_id = null): no store restriction, session key not set.
 * - Client user: defaults to their `store_id` or the client's default store.
 * - Allows switching stores via ?switch_store=<id> query parameter.
 *
 * Register in app/Http/Kernel.php under $middlewareGroups['web'].
 */
class SetActiveStore
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();

            // Super admin has no store restriction
            if (is_null($user->company_id)) {
                return $next($request);
            }

            // Allow store switching via query parameter (validated against user's accessible stores)
            if ($request->has('switch_store')) {
                $newStoreId = (int) $request->query('switch_store');
                $this->switchStore($user, $newStoreId);
                // Redirect back without the query param to keep URLs clean
                return redirect($request->url());
            }

            // Initialize session with default store on first request
            if (!session()->has('active_store_id')) {
                $defaultStoreId = $user->store_id
                    ?? Store::where('client_id', $this->getClientIdForUser($user))
                             ->where('is_default', 1)
                             ->value('id');

                if ($defaultStoreId) {
                    session(['active_store_id' => $defaultStoreId]);
                }
            }
        }

        return $next($request);
    }

    /**
     * Switch to a new store — validates the user actually has access.
     */
    private function switchStore($user, int $storeId): void
    {
        // Verify this user has access to the requested store
        $hasAccess = $user->stores()->where('stores.id', $storeId)->exists();

        if ($hasAccess) {
            session(['active_store_id' => $storeId]);
        }
    }

    /**
     * Get client_id for the given user via client_store_users pivot.
     */
    private function getClientIdForUser($user): ?int
    {
        return \DB::table('client_store_users')
            ->where('user_id', $user->id)
            ->value('client_id');
    }
}
