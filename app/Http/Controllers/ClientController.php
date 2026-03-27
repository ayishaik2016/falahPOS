<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Client;
use App\Models\User;
use App\Models\Company;
use App\Models\Store;
use App\Models\Role;
use App\Models\ClientStoreUsers;

/**
 * ClientController
 *
 * CHANGES (multi-store):
 *  - store() now also creates a default Store + Company + User for the client
 *  - datatableList() shows store count per client
 *  - Added stores() to show all stores for a client
 */
class ClientController extends Controller
{
    public function create(): View
    {
        return view('client.create');
    }

    public function edit(int $id): View
    {
        $client = Client::with(['stores', 'user'])->findOrFail($id);
        return view('client.edit', compact('client'));
    }

    public function list(): View
    {
        return view('client.list');
    }

    /**
     * Create a new client, its default company, default store, and login user.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:clients,email',
            'mobile' => 'required|string|max:255',
            'whatsapp' => 'nullable|string|max:55',
            'username' => 'required|string|unique:clients,username',
            'password' => 'required|string|min:6',
            'store_name' => 'required|string|max:255',  // first store name
            'store_code' => 'required|string|max:255',  // first store name
        ]);

        DB::beginTransaction();

        // 1. Create the Company profile for the default store
        $company = Company::create([
            'name' => $validated['first_name'] . ' ' . ($validated['last_name'] ?? ''),
            'email' => $validated['email'],
            'mobile' => $validated['mobile'],
            'address' => '',
        ]);

        // 2. Create the Client record
        $client = Client::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'] ?? null,
            'email' => $validated['email'],
            'mobile' => $validated['mobile'],
            'whatsapp' => $validated['whatsapp'] ?? null,
            'username' => $validated['username'],
            'company_id' => $company->id,
            'status' => 1,
        ]);

        // 3. Create the default Store
        $store = Store::create([
            'client_id' => $client->id,
            'company_id' => $company->id,
            'name' => $validated['store_name'],
            'code' => $validated['store_code'],
            'mobile' => $validated['mobile'],
            'email' => $validated['email'],
            'status' => 1,
            'is_default' => 1,
        ]);

        // 4. Create a login User for this client
        $clientRole = Role::where('name', 'Client')->first();
        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'] ?? null,
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'mobile' => $validated['mobile'],
            'company_id' => $company->id,
            'store_id' => $store->id,
            'role_id' => $clientRole->id,
            'status' => 1,
            'is_allowed_all_warehouses' => 1,
        ]);

        if ($clientRole) {
            $user->assignRole($clientRole);
            $user->givePermissionTo($clientRole->permissions);
        }

        // 5. Link user to store in pivot table
        ClientStoreUsers::create([
            'client_id' => $client->id,
            'store_id' => $store->id,
            'company_id' => $company->id,
            'user_id' => $user->id,
            'role_id' => $clientRole->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 6. Update client with user_id reference
        $client->update(['user_id' => $user->id]);

        DB::commit();

        return response()->json([
            'status' => true,
            'message' => __('app.record_saved_successfully'),
            'data' => ['id' => $client->id, 'name' => $client->first_name],
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => 'required|exists:clients,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'required|email',
            'mobile' => 'required|string|max:255',
            'whatsapp' => 'nullable|string|max:55',
            'status' => 'required|in:0,1',
        ]);

        $client = Client::findOrFail($validated['id']);
        $client->update($validated);

        return response()->json([
            'message' => __('app.record_updated_successfully'),
        ]);
    }

    public function datatableList(Request $request): mixed
    {
        $data = Client::withCount('stores');

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('stores_count_badge', function ($row) {
                $url = route('store.list', ['client_id' => $row->id]);
                return '<a href="' . $url . '" class="badge bg-info">' . $row->stores_count . ' ' . __('app.stores') . '</a>';
            })
            ->addColumn('status_badge', function ($row) {
                return $row->status
                    ? '<span class="badge bg-success">' . __('app.active') . '</span>'
                    : '<span class="badge bg-danger">' . __('app.inactive') . '</span>';
            })
            ->addColumn('created_at', function ($row) {
                return $row->created_at?->format(app('company')['date_format']);
            })
            ->addColumn('action', function ($row) {
                $editUrl = route('client.edit', ['id' => $row->id]);
                $storesUrl = route('store.list', ['client_id' => $row->id]);
                $deleteUrl = route('client.delete');

                return '
                    <div class="dropdown ms-auto">
                        <a class="dropdown-toggle dropdown-toggle-nocaret" href="#" data-bs-toggle="dropdown">
                            <i class="bx bx-dots-vertical-rounded font-22 text-option"></i>
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="' . $editUrl . '">
                                    <i class="bx bx-edit"></i> ' . __('app.edit') . '
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="' . $storesUrl . '">
                                    <i class="bx bx-store"></i> ' . __('app.manage_stores') . '
                                </a>
                            </li>
                            <li>
                            <button type="button" class="dropdown-item text-danger deleteRequest" data-delete-id="' . $row->id . '">
                                <i class="bx bx-trash"></i> ' . __('app.delete') . '
                            </button>
                            </li>
                        </ul>
                    </div>';
            })
            ->rawColumns(['action', 'stores_count_badge', 'status_badge'])
            ->make(true);
    }

    public function delete(Request $request): JsonResponse
    {
        $ids = $request->input('record_ids');

        try {
            Client::whereIn('id', $ids)->delete();
        } catch (QueryException $e) {
            return response()->json(['message' => __('app.cannot_delete_records')], 409);
        }

        return response()->json([
            'status' => true,
            'message' => __('app.record_deleted_successfully'),
        ]);
    }
}
