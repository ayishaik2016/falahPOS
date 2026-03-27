<!--start header -->
<header>
    <div class="topbar d-flex align-items-center">
        <nav class="navbar navbar-expand gap-3">
            <div class="mobile-toggle-menu"><i class='bx bx-menu'></i>
            </div>
              <div class="top-menu ms-auto">
                <ul class="navbar-nav align-items-center gap-1">
                    @can('purchase.bill.create')
                    <div class="d-flex">
                        <a href="{{ route('purchase.bill.create') }}" class="btn btn-sm btn-outline-primary radius-30 px-4" data-bs-toggle="tooltip" data-bs-placement="bottom" title="{{ __('purchase.add') }}">
                            <i class="bx bx-plus-circle mr-1"></i>{{ __('purchase.purchase') }}
                        </a>
                    </div>
                    @endcan

                    @can('sale.invoice.create')
                    <div class="d-flex">
                        <a href="{{ route('sale.invoice.create') }}" class="btn btn-sm btn-outline-danger radius-30 px-4" data-bs-toggle="tooltip" data-bs-placement="bottom" title="{{ __('sale.add') }}">
                            <i class="bx bx-plus-circle mr-1"></i>{{ __('sale.sale') }}
                        </a>
                    </div>
                    @endcan

                    @can('sale.invoice.create')
                    <div class="d-flex">
                        <a href="{{ route('pos.create') }}" class="btn btn-sm btn-success radius-30 px-4" data-bs-toggle="tooltip" data-bs-placement="bottom" title="{{ __('sale.pos') }}"><i class="bx bx-plus-circle mr-1"></i>{{ __('sale.pos') }}</a>
                    </div>
                    @endcan

                    <x-header-shortcut-menu />

                    <x-flag-toggle />

                    <li class="nav-item dark-mode d-none d-sm-flex">
                        <a class="nav-link dark-mode-icon theme-mode" data-base-url="{{ url('/') }}" href="javascript:;"><i class='bx bx-moon'></i>
                        </a>
                    </li>

                </ul>
            </div>
            
            @if(!app('isAdminRole'))
                @php
                    $activeStoreId = auth()->user()->getActiveStoreId();
                    $activeStore = \App\Models\Store::find($activeStoreId);
                    
                    if (is_null(auth()->user()->company_id)) {
                        $accessibleStores = \App\Models\Store::all();
                    } else {
                        $accessibleStores = \App\Models\Store::where('company_id', auth()->user()->company_id)->get();
                    }
                @endphp
                @if($accessibleStores->count() > 0)
                    <div class="user-box dropdown px-2" style="border-left: 1px solid #e2e8f0;">
                        <a class="d-flex align-items-center nav-link dropdown-toggle gap-3 dropdown-toggle-nocaret" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="user-info">
                                <p class="user-name mb-0">{{ $activeStore ? $activeStore->name : __('app.select_store') }}</p>
                                <!-- <p class="designattion mb-0">{{ __('app.store') }}</p> -->
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            @foreach($accessibleStores as $store)
                                <li>
                                    <a class="dropdown-item d-flex align-items-center {{ $activeStoreId == $store->id ? 'bg-primary text-white' : '' }}" href="{{ route('store.switch', ['store_id' => $store->id]) }}">
                                        <i class="bx bx-store fs-5"></i><span>{{ $store->name . ' - (' . $store->code . ')' }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            @endif

            <div class="user-box dropdown px-3">
                <a class="d-flex align-items-center nav-link dropdown-toggle gap-3 dropdown-toggle-nocaret" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="{{ url('/users/getimage/' . auth()->user()->avatar) }}" class="user-img" alt="user avatar">
                    <div class="user-info">
                        <p class="user-name mb-0">{{ auth()->user()->username }}</p>
                        <p class="designattion mb-0">{{ auth()->user()->role->name }}</p>
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item d-flex align-items-center" href="{{ route('user.profile') }}"><i class="bx bx-user fs-5"></i><span>{{ __('user.profile') }}</span></a>
                    </li>
                    <li>
                        <div class="dropdown-divider mb-0"></div>
                    </li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                        @csrf
                            <button type="submit" class="dropdown-item d-flex align-items-center"><i class="bx bx-log-out-circle"></i>{{ __('auth.logout') }}</button>
                        </form>
                    </li>
                </ul>
            </div>
        </nav>
    </div>
</header>
<!--end header -->
