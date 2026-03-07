<?php

namespace App\Http\Controllers;

use App\Enums\Item;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\View\View;
use App\Http\Requests\WarehouseRequest;
use App\Models\Items\ItemGeneralQuantity;
use App\Models\Items\ItemTransaction;
use App\Models\Purchase\Purchase;
use App\Models\User;
use App\Models\UserWarehouse;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Database\QueryException;
use App\Models\Warehouse;
use App\Traits\FormatNumber;
use Illuminate\Support\Facades\DB;
use App\Services\ItemTransactionService;

/**
 * CHANGES (multi-store):
 *  - store() auto-assigns store_id from logged-in user's active store
 *  - datatableList() and search bar scoped to active store's warehouses
 */
class WarehouseController extends Controller
{
    use FormatNumber;

    public $itemTransactionService;

    public function __construct(ItemTransactionService $itemTransactionService)
    {
        $this->itemTransactionService = $itemTransactionService;
    }

    public function create(): View
    {
        return view('warehouse.create');
    }

    public function edit(int $id): View
    {
        $warehouse = Warehouse::findOrFail($id);
        return view('warehouse.edit', compact('warehouse'));
    }

    /**
     * Store a new warehouse — auto-assigns active store_id.
     */
    public function store(WarehouseRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        // Inject the active store_id
        $validatedData['store_id'] = auth()->user()->getActiveStoreId();

        $warehouse = Warehouse::create($validatedData);

        return response()->json([
            'status'  => true,
            'message' => __('app.record_saved_successfully'),
            'data'    => ['id' => $warehouse->id, 'name' => $warehouse->name],
        ]);
    }

    public function update(WarehouseRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        Warehouse::where('id', $validatedData['id'])->update($validatedData);

        return response()->json([
            'message' => __('app.record_updated_successfully'),
        ]);
    }

    public function list(): View
    {
        session(['record' => [
            'type'    => 'info',
            'status'  => 'Information',
            'message' => "The warehouse serves the primary purpose of maintaining stock levels...",
        ]]);
        return view('warehouse.list');
    }

    /**
     * DataTable list — scoped to current user's active store.
     */
    public function datatableList(Request $request): mixed
    {
        $user        = auth()->user();
        $storeId     = $user->getActiveStoreId();
        $warehouseIds = $user->getAccessibleWarehouses()->pluck('id');

        $data = Warehouse::whereIn('id', $warehouseIds);

        // Further scope to active store (in case user has cross-store access)
        if ($storeId) {
            $data->where('store_id', $storeId);
        }

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('created_at', function ($row) {
                return $row->created_at->format(app('company')['date_format']);
            })
            ->addColumn('username', function ($row) {
                return $row->user->username ?? '';
            })
            ->addColumn('store_name', function ($row) {
                return $row->store->name ?? '—';
            })
            ->addColumn('total_items', function ($row) {
                return ItemGeneralQuantity::where('warehouse_id', $row->id)
                    ->where('quantity', '>', 0)->distinct('item_id')->count('item_id');
            })
            ->addColumn('available_stock', function ($row) {
                $quantity = ItemGeneralQuantity::where('warehouse_id', $row->id)->sum('quantity');
                return $this->formatQuantity($quantity);
            })
            ->addColumn('worth_cost', function ($row) {
                $details = $this->itemTransactionService->worthItemsDetails($row->id);
                $row->worthItemsDetails = $details;
                return $this->formatWithPrecision($details['totalPurchaseCost']);
            })
            ->addColumn('worth_sale_price', function ($row) {
                return $this->formatWithPrecision($row->worthItemsDetails['totalSalePrice']);
            })
            ->addColumn('worth_profit', function ($row) {
                return $this->formatWithPrecision(
                    $row->worthItemsDetails['totalSalePrice'] - $row->worthItemsDetails['totalPurchaseCost']
                );
            })
            ->addColumn('action', function ($row) {
                $editUrl   = route('warehouse.edit',   ['id' => $row->id]);
                $deleteUrl = route('warehouse.delete');

                $actionBtn = '
                    <div class="dropdown ms-auto">
                        <a class="dropdown-toggle dropdown-toggle-nocaret" href="#" data-bs-toggle="dropdown">
                            <i class="bx bx-dots-vertical-rounded font-22 text-option"></i>
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="'.$editUrl.'">
                                    <i class="bx bx-edit"></i> '.__('app.edit').'
                                </a>
                            </li>';

                if ($row->is_deletable) {
                    $actionBtn .= '
                            <li>
                                <button type="button" class="dropdown-item text-danger deleteRequest"
                                        data-delete-id="'.$row->id.'">
                                    <i class="bx bx-trash"></i> '.__('app.delete').'
                                </button>
                            </li>';
                }

                $actionBtn .= '</ul></div>';
                return $actionBtn;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function delete(Request $request): JsonResponse
    {
        $ids = $request->input('record_ids');

        try {
            Warehouse::whereIn('id', $ids)->where('is_deletable', 1)->delete();
        } catch (QueryException $e) {
            return response()->json(['message' => __('app.cannot_delete_records')], 422);
        }

        return response()->json([
            'status'  => true,
            'message' => __('app.record_deleted_successfully'),
        ]);
    }

    /**
     * Ajax search bar — scoped to active store's warehouses.
     */
    public function getAjaxWarehouseSearchBarList(): string
    {
        $search  = request('search');
        $user    = auth()->user();
        $storeId = $user->getActiveStoreId();

        $items = Warehouse::where(function ($query) use ($search) {
                        $query->whereRaw('UPPER(name) LIKE ?', ['%' . strtoupper($search) . '%']);
                    })
                    ->when($storeId, function ($query) use ($storeId) {
                        $query->where('store_id', $storeId);
                    })
                    ->when(!$user->is_allowed_all_warehouses, function ($query) use ($user) {
                        $warehouseIds = \App\Models\UserWarehouse::where('user_id', $user->id)->pluck('warehouse_id');
                        $query->whereIn('id', $warehouseIds);
                    })
                    ->select('id', 'name')
                    ->get();

        return json_encode([
            'results' => $items->map(fn($item) => ['id' => $item->id, 'text' => $item->name])->toArray(),
        ]);
    }
}
