{{-- resources/views/store/list.blade.php --}}
@extends('layouts.app')

@section('title', __('app.stores') . ' — ' . $client->first_name . ' ' . $client->last_name)

@section('content')
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">{{ __('app.clients') }}</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item">
                    <a href="{{ route('client.list') }}"><i class="bx bx-home-alt"></i></a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">
                    {{ $client->first_name }} {{ $client->last_name }} — {{ __('app.stores') }}
                </li>
            </ol>
        </nav>
    </div>
    <div class="ms-auto">
        <a href="{{ route('store.create', ['client_id' => $client->id]) }}"
           class="btn btn-primary px-4">
            <i class="bx bx-plus"></i> {{ __('app.add_store') }}
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="d-flex align-items-center mb-3">
            <h5 class="mb-0">
                <i class="bx bx-store me-2 text-primary"></i>
                {{ __('app.stores_for') }}: <strong>{{ $client->first_name }} {{ $client->last_name }}</strong>
            </h5>
        </div>

        <div class="table-responsive">
            <table id="storesTable" class="table table-striped table-bordered" style="width:100%">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('app.name') }}</th>
                        <th>{{ __('app.code') }}</th>
                        <th>{{ __('app.mobile') }}</th>
                        <th>{{ __('app.email') }}</th>
                        <th>{{ __('app.default') }}</th>
                        <th>{{ __('app.status') }}</th>
                        <th>{{ __('app.created_at') }}</th>
                        <th>{{ __('app.action') }}</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function () {
    const table = $('#storesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('store.datatableList', ['client_id' => $client->id]) }}',
            type: 'GET',
        },
        columns: [
            { data: 'DT_RowIndex',      name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'name',             name: 'name' },
            { data: 'code',             name: 'code' },
            { data: 'mobile',           name: 'mobile' },
            { data: 'email',            name: 'email' },
            { data: 'is_default_badge', name: 'is_default', orderable: false },
            { data: 'status_badge',     name: 'status',     orderable: false },
            { data: 'created_at',       name: 'created_at' },
            { data: 'action',           name: 'action',     orderable: false, searchable: false },
        ],
    });

    // Delete handler
    $(document).on('click', '.deleteRequest', function () {
        const id = $(this).data('delete-id');
        if (confirm('{{ __('app.confirm_delete') }}')) {
            $.ajax({
                url: '{{ route('store.delete') }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    record_ids: [id],
                },
                success: function (res) {
                    table.ajax.reload();
                },
            });
        }
    });
});
</script>
@endpush
