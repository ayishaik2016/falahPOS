{{-- resources/views/store/edit.blade.php --}}
@extends('layouts.app')

@section('title', __('app.edit_store'))

@section('content')
    <!--start page wrapper -->
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumb :langArray="[
                                    'app.stores',
                                    'app.stores_list',
                                    'app.edit_store',
                                ]"/>

            <div class="row">
                <div class="col-12 col-lg-12">
                    <div class="card">
                        <div class="card-header px-4 py-3">
                            <h5 class="mb-0">{{ __('app.edit_store') }} — {{ $client->first_name }} {{ $client->last_name }}</h5>
                        </div>
                        <div class="card-body p-4">
                            <form class="row g-3 needs-validation" id="storeForm" action="{{ route('store.update') }}" enctype="multipart/form-data">
                                {{-- CSRF Protection --}}
                                @csrf
                                @method('PUT')

                                <input type="hidden" id="operation" name="operation" value="update">
                                <input type="hidden" name="id" value="{{ $store->id }}">
                                <input type="hidden" name="client_id" value="{{ $client->id }}">
                                <input type="hidden" name="company_id" value="{{ $client->company_id }}">
                                <input type="hidden" id="base_url" value="{{ url('/') }}">

                                {{-- Store Info --}}
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">{{ __('app.store_name') }} <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control" value="{{ $store->name }}" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">{{ __('app.store_code') }} <span class="text-danger">*</span></label>
                                        <input type="text" name="code" class="form-control" value="{{ $store->code }}" placeholder="e.g. STR-001" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">{{ __('app.status') }} <span class="text-danger">*</span></label>
                                        <select name="status" class="form-select" required>
                                            <option value="1" {{ $store->status == 1 ? 'selected' : '' }}>{{ __('app.active') }}</option>
                                            <option value="0" {{ $store->status == 0 ? 'selected' : '' }}>{{ __('app.inactive') }}</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">{{ __('app.mobile') }}</label>
                                        <input type="text" name="mobile" class="form-control" value="{{ $store->mobile }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">{{ __('app.email') }}</label>
                                        <input type="email" name="email" class="form-control" value="{{ $store->email }}">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">{{ __('app.address') }}</label>
                                        <textarea name="address" class="form-control" rows="3">{{ $store->address }}</textarea>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="is_default"
                                                id="isDefault" value="1" {{ $store->is_default ? 'checked' : '' }}>
                                            <label class="form-check-label" for="isDefault">
                                                {{ __('app.set_as_default') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="d-md-flex d-grid align-items-center gap-3">
                                        <x-button type="submit" class="primary px-4" text="{{ __('app.update') }}" />
                                        <x-anchor-tag href="{{ route('store.list', ['client_id' => $client->id]) }}" text="{{ __('app.close') }}" class="btn btn-light px-4" />
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
<script src="{{ versionedAsset('custom/js/store/store.js') }}"></script>
@endsection
