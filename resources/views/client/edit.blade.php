@extends('layouts.app')
@section('title', __('client.update_client'))

        @section('content')
        <!--start page wrapper -->
        <div class="page-wrapper">
            <div class="page-content">
                <x-breadcrumb :langArray="[
                                            'client.clients',
                                            'client.list',
                                            'client.update_client',
                                        ]"/>
                <div class="row">
                    <div class="col-12 col-lg-12">
                        <div class="card">
                            <div class="card-header px-4 py-3 d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">{{ __('client.details') }}</h5>
                                <a href="{{ route('store.list', ['client_id' => $client->id]) }}"
                                   class="btn btn-outline-primary btn-sm px-3">
                                    <i class="bx bx-store me-1"></i>
                                    {{ __('client.manage_stores') }}
                                    <span class="badge bg-primary ms-1">{{ $client->stores->count() }}</span>
                                </a>
                            </div>
                            <div class="card-body p-4">
                                <form class="row g-3 needs-validation" id="clientForm" action="{{ route('client.update') }}" enctype="multipart/form-data">
                                    {{-- CSRF Protection --}}
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name='id' value="{{ $client->id }}" />
                                    <input type="hidden" id="base_url" value="{{ url('/') }}">

                                    {{-- Client Info --}}
                                    <div class="col-12">
                                        <h6 class="text-muted text-uppercase fw-semibold mb-0">{{ __('client.client_info') }}</h6>
                                        <hr class="mt-2">
                                    </div>

                                    <div class="col-md-6">
                                        <x-label for="first_name" name="{{ __('app.first_name') }}" />
                                        <x-input type="text" name="first_name" :required="true" value="{{ $client->first_name }}"/>
                                    </div>
                                    <div class="col-md-6">
                                        <x-label for="last_name" name="{{ __('app.last_name') }}" />
                                        <x-input type="text" name="last_name" :required="false" value="{{ $client->last_name }}"/>
                                    </div>
                                    <div class="col-md-6">
                                        <x-label for="email" name="{{ __('app.email') }}" />
                                        <x-input type="email" name="email" :required="true" value="{{ $client->email }}"/>
                                    </div>
                                    <div class="col-md-6">
                                        <x-label for="mobile" name="{{ __('app.mobile') }}" />
                                        <x-input type="number" name="mobile" :required="true" value="{{ $client->mobile }}"/>
                                    </div>
                                    <div class="col-md-6">
                                        <x-label for="whatsapp" name="{{ __('app.whatsapp_number') }}" />
                                        <x-input type="number" name="whatsapp" :required="false" value="{{ $client->whatsapp }}"/>
                                    </div>
                                    <div class="col-md-6">
                                        <x-label for="status" name="{{ __('app.status') }}" />
                                        <x-dropdown-status selected="{{ $client->status }}" dropdownName='status'/>
                                    </div>

                                    {{-- Stores Summary --}}
                                    @if($client->stores->count() > 0)
                                    <div class="col-12 mt-2">
                                        <h6 class="text-muted text-uppercase fw-semibold mb-0">{{ __('client.stores') }}</h6>
                                        <hr class="mt-2">
                                        <div class="d-flex flex-wrap gap-2">
                                            @foreach($client->stores as $store)
                                            <span class="badge {{ $store->is_default ? 'bg-primary' : 'bg-secondary' }} px-3 py-2" style="font-size:13px">
                                                <i class="bx bx-store me-1"></i>
                                                {{ $store->name }}
                                                @if($store->is_default)
                                                    <span class="ms-1 opacity-75">({{ __('app.default') }})</span>
                                                @endif
                                            </span>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif

                                    <div class="col-md-12">
                                        <div class="d-md-flex d-grid align-items-center gap-3">
                                            <x-button type="submit" class="primary px-4" text="{{ __('app.submit') }}" />
                                            <x-anchor-tag href="{{ route('client.list') }}" text="{{ __('app.close') }}" class="btn btn-light px-4" />
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end row-->
            </div>
        </div>
        @endsection

@section('js')
<script src="{{ versionedAsset('custom/js/client/client.js') }}"></script>
@endsection
