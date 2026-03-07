{{--
    Store Switcher Component
    ========================
    Displays the current active store name in the navbar and allows
    the logged-in user to switch between their accessible stores.

    Usage: Include in your navbar/header blade layout.
    <x-store-switcher />

    CHANGES (multi-store):
    - New component, shows active store and allows switching
--}}

@php
    $user        = auth()->user();
    $activeStoreId = session('active_store_id');
    $activeStore   = $activeStoreId ? \App\Models\Store::find($activeStoreId) : null;
    $userStores    = $user?->stores()->active()->get() ?? collect();
@endphp

@if($userStores->count() > 0)
<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle d-flex align-items-center gap-1" href="#"
       data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bx bx-store-alt font-18"></i>
        <span class="d-none d-md-inline">
            {{ $activeStore?->name ?? __('app.select_store') }}
        </span>
    </a>
    <ul class="dropdown-menu dropdown-menu-end">
        <li><h6 class="dropdown-header">{{ __('app.switch_store') }}</h6></li>

        @foreach($userStores as $store)
        <li>
            <a class="dropdown-item d-flex align-items-center justify-content-between
                       {{ $activeStoreId == $store->id ? 'active fw-semibold' : '' }}"
               href="{{ route('store.switch', ['store_id' => $store->id]) }}">
                <span>
                    <i class="bx bx-building-house me-1"></i>
                    {{ $store->name }}
                </span>
                @if($activeStoreId == $store->id)
                    <i class="bx bx-check text-success ms-2"></i>
                @endif
                @if($store->is_default)
                    <span class="badge bg-secondary ms-1" style="font-size:10px">
                        {{ __('app.default') }}
                    </span>
                @endif
            </a>
        </li>
        @endforeach

        {{-- Link to manage stores for super admin --}}
        @if(is_null(auth()->user()->company_id))
        <li><hr class="dropdown-divider"></li>
        <li>
            <a class="dropdown-item text-muted" href="{{ route('client.list') }}">
                <i class="bx bx-cog me-1"></i> {{ __('app.manage_clients_stores') }}
            </a>
        </li>
        @endif
    </ul>
</li>
@endif
