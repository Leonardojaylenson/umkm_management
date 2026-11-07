<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ManagesMasterData;
use App\Models\Category;
use App\Models\Item;
use App\Models\Unit;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class StokController extends Controller
{
    use ManagesMasterData;

    public function __construct(private StockService $stock)
    {
    }

    public function itemIndex(Request $request)
    {
        $items = Item::with(['category', 'unit'])
            ->when($request->q, fn ($q, $s) => $q->where('item_name', 'like', "%$s%"))
            ->when($request->category, fn ($q, $id) => $q->where('id_category', $id))
            ->when($request->filter === 'stok-menipis', fn ($q) => $q->where('is_service', 0)->whereColumn('stock', '<=', 'min_stock'))
            ->orderBy('item_name')
            ->paginate(10)->withQueryString();

        return view('stok.item', [
            'items'         => $items,
            'categories'    => Category::orderBy('category_name')->get(),
            'lowStockCount' => Item::where('is_service', 0)->whereColumn('stock', '<=', 'min_stock')->count(),
        ]);
    }

    public function itemCreate()
    {
        return view('stok.item-form', $this->itemFormData(new Item(['is_service' => false])));
    }

    public function itemStore(Request $request)
    {
        $data = $this->validateItem($request, withStock: true);

        DB::transaction(function () use ($request, $data) {
            $initialStock  = $data['stock'];
            $data['stock'] = 0;

            if ($request->hasFile('photo')) {
                $data['photo'] = $request->file('photo')->store('items', 'public');
            }

            $item = Item::create($data);

            if ($initialStock > 0) {
                $this->stock->change($item, 'in', $initialStock, 'Stok awal');
            }
        });

        return redirect()->route('item.index')->with('success', 'Item berhasil ditambahkan.');
    }

    public function itemShow(Item $item)
    {
        $item->load(['category', 'unit']);
        $histories = $item->stockHistories()->with('user')->paginate(10);

        return view('stok.item-detail', compact('item', 'histories'));
    }

    public function itemEdit(Item $item)
    {
        return view('stok.item-form', $this->itemFormData($item));
    }

    public function itemUpdate(Request $request, Item $item)
    {
        $data = $this->validateItem($request, withStock: false);

        if ($request->hasFile('photo')) {
            $this->deletePhoto($item);
            $data['photo'] = $request->file('photo')->store('items', 'public');
        }

        if ($data['is_service']) {
            $data['stock'] = 0;
        }

        $item->update($data);

        return redirect()->route('item.index')->with('success', 'Item berhasil diperbarui.');
    }

    public function itemDestroy(Item $item)
    {
        $this->deletePhoto($item);
        $item->delete();

        return redirect()->route('item.index')->with('success', 'Item berhasil dihapus.');
    }

    public function itemAdjustStock(Request $request, Item $item)
    {
        abort_if($item->is_service, 404);

        $data = $request->validate([
            'type'     => 'required|in:in,adjustment',
            'quantity' => 'required|numeric|min:0|max:9999999',
            'note'     => 'nullable|string|max:255',
        ]);

        if ($data['type'] === 'in') {
            if ($data['quantity'] <= 0) {
                return back()->withErrors(['quantity' => 'Jumlah stok masuk harus lebih dari 0.']);
            }
            $this->stock->change($item, 'in', (float) $data['quantity'], ($data['note'] ?? null) ?: 'Stok masuk');
        } else {
            $this->stock->correctTo($item, (float) $data['quantity'], $data['note'] ?? null);
        }

        return back()->with('success', 'Stok berhasil diperbarui.');
    }

    private function validateItem(Request $request, bool $withStock): array
    {
        $isService = $request->boolean('is_service');

        $data = $request->validate([
            'item_name'     => 'required|string|max:150',
            'id_category'   => ['nullable', $this->existsInBusiness('categories', 'id_category')],
            'id_unit'       => ['nullable', $this->existsInBusiness('units', 'id_unit')],
            'selling_price' => 'required|numeric|min:0',
            'cost_price'    => 'required|numeric|min:0',
            'stock'         => [$withStock && ! $isService ? 'required' : 'nullable', 'numeric', 'min:0'],
            'min_stock'     => 'nullable|numeric|min:0',
            'photo'         => 'nullable|image|max:2048',
        ]);

        $data['is_service'] = $isService;
        $data['stock']      = $isService ? 0 : ($data['stock'] ?? 0);
        $data['min_stock']  = $isService ? 0 : ($data['min_stock'] ?? 0);

        if (! $withStock) {
            unset($data['stock']);
        }

        return $data;
    }

    private function itemFormData(Item $item): array
    {
        return [
            'item'       => $item,
            'categories' => Category::orderBy('category_name')->get(),
            'units'      => Unit::orderBy('unit_name')->get(),
        ];
    }

    private function deletePhoto(Item $item): void
    {
        if ($item->photo) {
            Storage::disk('public')->delete($item->photo);
        }
    }

    private function categoryConfig(): array
    {
        return ['model' => Category::class, 'field' => 'category_name', 'base' => 'kategori', 'view' => 'stok.kategori',
                'usage' => 'items', 'usageLabel' => 'item', 'label' => 'Kategori'];
    }

    private function unitConfig(): array
    {
        return ['model' => Unit::class, 'field' => 'unit_name', 'base' => 'satuan', 'view' => 'stok.satuan',
                'usage' => 'items', 'usageLabel' => 'item', 'label' => 'Satuan'];
    }

    public function categoryIndex()                             { return $this->masterPage($this->categoryConfig()); }
    public function categoryEdit(Category $category)            { return $this->masterPage($this->categoryConfig(), $category); }
    public function categoryStore(Request $request)             { return $this->masterStore($request, $this->categoryConfig()); }
    public function categoryUpdate(Request $request, Category $category) { return $this->masterUpdate($request, $this->categoryConfig(), $category); }
    public function categoryDestroy(Category $category)         { return $this->masterDestroy($this->categoryConfig(), $category); }

    public function unitIndex()                                 { return $this->masterPage($this->unitConfig()); }
    public function unitEdit(Unit $unit)                        { return $this->masterPage($this->unitConfig(), $unit); }
    public function unitStore(Request $request)                 { return $this->masterStore($request, $this->unitConfig()); }
    public function unitUpdate(Request $request, Unit $unit)    { return $this->masterUpdate($request, $this->unitConfig(), $unit); }
    public function unitDestroy(Unit $unit)                     { return $this->masterDestroy($this->unitConfig(), $unit); }
}
