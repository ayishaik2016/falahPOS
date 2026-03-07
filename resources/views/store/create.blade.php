{{-- resources/views/store/create.blade.php --}}
@extends('layouts.app')

@section('title', __('app.add_store'))

@section('content')
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">{{ __('app.stores') }}</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item">
                    <a href="{{ route('store.list', ['client_id' => $client->id]) }}">
                        {{ $client->first_name }} {{ $client->last_name }}
                    </a>
                </li>
                <li class="breadcrumb-item active">{{ __('app.add_store') }}</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <h5 class="card-title">
            <i class="bx bx-store me-2 text-primary"></i>
            {{ __('app.add_store') }} — {{ $client->first_name }} {{ $client->last_name }}
        </h5>
        <hr>

        <form id="storeCreateForm">
            @csrf
            <input type="hidden" name="client_id" value="{{ $client->id }}">

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">{{ __('app.store_name') }} <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ __('app.store_code') }}</label>
                    <input type="text" name="code" class="form-control" placeholder="e.g. STR-001">
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ __('app.status') }} <span class="text-danger">*</span></label>
                    <select name="status" class="form-select" required>
                        <option value="1">{{ __('app.active') }}</option>
                        <option value="0">{{ __('app.inactive') }}</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('app.mobile') }}</label>
                    <input type="text" name="mobile" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('app.email') }}</label>
                    <input type="email" name="email" class="form-control">
                </div>
                <div class="col-12">
                    <label class="form-label">{{ __('app.address') }}</label>
                    <textarea name="address" class="form-control" rows="3"></textarea>
                </div>
                <div class="col-12">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_default"
                               id="isDefault" value="1">
                        <label class="form-check-label" for="isDefault">
                            {{ __('app.set_as_default_store') }}
                        </label>
                    </div>
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="button" id="saveStoreBtn" class="btn btn-primary px-4">
                    <i class="bx bx-save me-1"></i> {{ __('app.save') }}
                </button>
                <a href="{{ route('store.list', ['client_id' => $client->id]) }}"
                   class="btn btn-outline-secondary px-4">
                    {{ __('app.cancel') }}
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
$('#saveStoreBtn').on('click', function () {
    const formData = new FormData($('#storeCreateForm')[0]);

    $.ajax({
        url: '{{ route('store.store') }}',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function (res) {
            if (res.status) {
                window.location.href = '{{ route('store.list', ['client_id' => $client->id]) }}';
            }
        },
        error: function (xhr) {
            // Handle validation errors
            const errors = xhr.responseJSON?.errors;
            if (errors) {
                Object.keys(errors).forEach(function (field) {
                    $('[name="'+field+'"]').addClass('is-invalid');
                });
            }
        }
    });
});
</script>
@endpush
