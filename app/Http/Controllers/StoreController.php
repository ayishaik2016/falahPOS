<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Store;
use App\Models\Client;
use App\Models\Company;

/**
 * StoreController
 *
 * Manages stores for each client (customer).
 * Each client can have multiple stores.
 * When a store is created, a corresponding Company record is also created
 * to hold store-level settings (logo, address, invoice config, etc.).
 */
class StoreController extends Controller
{
    /**
     * Show form to create a new store for a client.
     */
    public function create(int $clientId): View
    {
        $client = Client::findOrFail($clientId);
        return view('store.create', compact('client'));
    }

    /**
     * Show form to edit an existing store.
     */
    public function edit(int $id): View
    {
        $store  = Store::with(['client', 'company'])->findOrFail($id);
        $client = $store->client;
        return view('store.edit', compact('store', 'client'));
    }

    /**
     * List all stores for a specific client.
     */
    public function list(int $clientId): View
    {
        $client = Client::findOrFail($clientId);
        return view('store.list', compact('client'));
    }

    /**
     * DataTable list of stores for a client (AJAX).
     */
    public function datatableList(Request $request, int $clientId): JsonResponse
    {
        $data = Store::where('client_id', $clientId)->with('company');

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('status_badge', function ($row) {
                return $row->status
                    ? '<span class="badge bg-success">'.__('app.active').'</span>'
                    : '<span class="badge bg-danger">'.__('app.inactive').'</span>';
            })
            ->addColumn('is_default_badge', function ($row) {
                return $row->is_default
                    ? '<span class="badge bg-primary">'.__('app.default').'</span>'
                    : '';
            })
            ->addColumn('created_at', function ($row) {
                return $row->created_at?->format(app('company')['date_format']);
            })
            ->addColumn('action', function ($row) {
                $editUrl   = route('store.edit',   ['id'       => $row->id]);
                $switchUrl = route('store.defaultStore', ['client_id' => $row->client_id, 'store_id' => $row->id]);

                return '
                    <div class="dropdown ms-auto">
                        <a class="dropdown-toggle dropdown-toggle-nocaret" href="#" data-bs-toggle="dropdown">
                            <i class="bx bx-dots-vertical-rounded font-22 text-option"></i>
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="'.$editUrl.'">
                                    <i class="bx bx-edit"></i> '.__('app.edit').'
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="'.$switchUrl.'">
                                    <i class="bx bx-store"></i> '.__('app.set_as_default').'
                                </a>
                            </li>
                            <li>
                                <button type="button" class="dropdown-item text-danger deleteRequest"
                                        data-delete-id="'.$row->id.'">
                                    <i class="bx bx-trash"></i> '.__('app.delete').'
                                </button>
                            </li>
                        </ul>
                    </div>';
            })
            ->rawColumns(['action', 'status_badge', 'is_default_badge'])
            ->make(true);
    }

    /**
     * Create a new store + associated Company record.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'name'      => 'required|string|max:255',
            'code'      => 'nullable|string|max:100',
            'address'   => 'nullable|string',
            'status'    => 'required|in:0,1',
            'is_default'=> 'sometimes|boolean',
        ]);

        // If this is set as default, unset others for the same client
        if (!empty($validated['is_default'])) {
            Store::where('client_id', $validated['client_id'])
                 ->update(['is_default' => 0]);
        }

        $store = Store::create([
            'client_id'  => $validated['client_id'],
            'company_id' => $validated['company_id'],
            'name'       => $validated['name'],
            'code'       => $validated['code']    ?? null,
            'address'    => $validated['address'] ?? null,
            'mobile'     => $validated['mobile']  ?? null,
            'email'      => $validated['email']   ?? null,
            'status'     => $validated['status'],
            'is_default' => $validated['is_default'] ?? 0,
        ]);

        return response()->json([
            'status'  => true,
            'message' => __('app.record_saved_successfully'),
            'data'    => ['id' => $store->id, 'name' => $store->name],
        ]);
    }

    /**
     * Update an existing store.
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id'        => 'required|exists:stores,id',
            'name'      => 'required|string|max:255',
            'code'      => 'nullable|string|max:100',
            'address'   => 'nullable|string',
            'mobile'    => 'nullable|string|max:255',
            'email'     => 'nullable|email|max:255',
            'status'    => 'required|in:0,1',
            'is_default'=> 'sometimes|boolean',
        ]);

        $store = Store::findOrFail($validated['id']);

        // If setting as default, clear other defaults for this client
        if (!empty($validated['is_default'])) {
            Store::where('client_id', $store->client_id)
                 ->where('id', '!=', $store->id)
                 ->update(['is_default' => 0]);
        }

        $store->update($validated);

        // Sync the Company profile name/email/mobile
        $store->company->update([
            'name'    => $validated['name'],
            'email'   => $validated['email']   ?? $store->company->email,
            'mobile'  => $validated['mobile']  ?? $store->company->mobile,
            'address' => $validated['address'] ?? $store->company->address,
        ]);

        return response()->json([
            'message' => __('app.record_updated_successfully'),
        ]);
    }

    /**
     * Delete stores.
     */
    public function delete(Request $request): JsonResponse
    {
        $ids = $request->input('record_ids');

        try {
            Store::whereIn('id', $ids)->delete();
        } catch (QueryException $e) {
            return response()->json(['message' => __('app.cannot_delete_records')], 409);
        }

        return response()->json([
            'status'  => true,
            'message' => __('app.record_deleted_successfully'),
        ]);
    }

    /**
     * Switch the active store for the current session.
     */
    public function defaultStore(int $clientId, int $storeId): \Illuminate\Http\RedirectResponse
    {
        $user = auth()->user();
        $companyId = app('company')['id'];
         Store::where('client_id', $clientId)
                 ->where('id', '=', $storeId)
                 ->update(['is_default' => 1]);

        Store::where('client_id', $clientId)
                 ->where('id', '!=', $storeId)
                 ->update(['is_default' => 0]);

        return redirect()->back()->with('success', __('app.default_store_updated_successfully'));
    }

    /**
     * Switch the active store for the current session.
     */
    public function switchStore(int $storeId): \Illuminate\Http\RedirectResponse
    {
        $user = auth()->user();


        // Verify access
        $hasAccess = $user->stores()->where('stores.id', $storeId)->exists();

        if ($hasAccess) {
            session(['active_store_id' => $storeId]);
        }

        return redirect()->back()->with('success', __('app.store_switched_successfully'));
    }
}
