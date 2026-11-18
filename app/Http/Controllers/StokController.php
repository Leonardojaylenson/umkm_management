<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class StokController extends Controller
{
    public function itemIndex(Request $request)
    {
        $bid = $this->businessId();

        $query = Item::with(['category', 'unit'])->where('id_business', $bid);

        if ($request->q) {
            $query->where('item_name', 'like', '%' . $request->q . '%');
        }

        if ($request->category) {
            $query->where('id_category', $request->category);
        }

        if ($request->filter == 'stok-menipis') {
            $query->where('is_service', 0)->whereColumn('stock', '<=', 'min_stock');
        }

        $items         = $query->orderBy('item_name')->paginate(10)->withQueryString();
        $categories    = Category::where('id_business', $bid)->orderBy('category_name')->get();
        $lowStockCount = Item::where('id_business', $bid)->where('is_service', 0)->whereColumn('stock', '<=', 'min_stock')->count();

        return view('stok.item', compact('items', 'categories', 'lowStockCount'));
    }

    public function itemCreate()
    {
        $bid        = $this->businessId();
        $item       = new Item(['is_service' => 0]);
        $categories = Category::where('id_business', $bid)->orderBy('category_name')->get();
        $units      = Unit::where('id_business', $bid)->orderBy('unit_name')->get();

        return view('stok.item-form', compact('item', 'categories', 'units'));
    }

    public function itemStore(Request $request)
    {
        $bid       = $this->businessId();
        $isService = $request->has('is_service');

        $request->validate([
            'item_name'     => 'required|max:150',
            'id_category'   => 'nullable|exists:categories,id_category,id_business,' . $bid,
            'id_unit'       => 'nullable|exists:units,id_unit,id_business,' . $bid,
            'selling_price' => 'required|numeric|min:0',
            'cost_price'    => 'required|numeric|min:0',
            'stock'         => 'nullable|numeric|min:0',
            'min_stock'     => 'nullable|numeric|min:0',
            'photo'         => 'nullable|image|max:2048',
        ]);

        $photo = null;
        if ($request->hasFile('photo')) {
            $photo = $request->file('photo')->store('items', 'public');
        }

        $initialStock = $isService ? 0 : (float) $request->stock;

        DB::beginTransaction();

        $item = Item::create([
            'id_business'   => $bid,
            'id_category'   => $request->id_category,
            'id_unit'       => $request->id_unit,
            'item_name'     => $request->item_name,
            'selling_price' => $request->selling_price,
            'cost_price'    => $request->cost_price,
            'is_service'    => $isService ? 1 : 0,
            'stock'         => 0,
            'min_stock'     => $isService ? 0 : (float) $request->min_stock,
            'photo'         => $photo,
        ]);

        if ($initialStock > 0) {
            $this->changeStock($item, 'in', $initialStock, 'Stok awal');
        }

        DB::commit();

        return redirect()->route('item.index')->with('success', 'Item berhasil ditambahkan.');
    }

    public function itemShow($id)
    {
        $item      = Item::with(['category', 'unit'])->where('id_business', $this->businessId())->findOrFail($id);
        $histories = $item->stockHistories()->with('user')->paginate(10);

        return view('stok.item-detail', compact('item', 'histories'));
    }

    public function itemEdit($id)
    {
        $bid        = $this->businessId();
        $item       = Item::where('id_business', $bid)->findOrFail($id);
        $categories = Category::where('id_business', $bid)->orderBy('category_name')->get();
        $units      = Unit::where('id_business', $bid)->orderBy('unit_name')->get();

        return view('stok.item-form', compact('item', 'categories', 'units'));
    }

    public function itemUpdate(Request $request, $id)
    {
        $bid  = $this->businessId();
        $item = Item::where('id_business', $bid)->findOrFail($id);

        $request->validate([
            'item_name'     => 'required|max:150',
            'id_category'   => 'nullable|exists:categories,id_category,id_business,' . $bid,
            'id_unit'       => 'nullable|exists:units,id_unit,id_business,' . $bid,
            'selling_price' => 'required|numeric|min:0',
            'cost_price'    => 'required|numeric|min:0',
            'min_stock'     => 'nullable|numeric|min:0',
            'photo'         => 'nullable|image|max:2048',
        ]);

        $isService = $request->has('is_service');

        $data = [
            'id_category'   => $request->id_category,
            'id_unit'       => $request->id_unit,
            'item_name'     => $request->item_name,
            'selling_price' => $request->selling_price,
            'cost_price'    => $request->cost_price,
            'is_service'    => $isService ? 1 : 0,
            'min_stock'     => $isService ? 0 : (float) $request->min_stock,
        ];

        if ($isService) {
            $data['stock'] = 0;
        }

        if ($request->hasFile('photo')) {
            if ($item->photo) {
                Storage::disk('public')->delete($item->photo);
            }
            $data['photo'] = $request->file('photo')->store('items', 'public');
        }

        $item->update($data);

        return redirect()->route('item.index')->with('success', 'Item berhasil diperbarui.');
    }

    public function itemDestroy($id)
    {
        $item = Item::where('id_business', $this->businessId())->findOrFail($id);

        if ($item->photo) {
            Storage::disk('public')->delete($item->photo);
        }

        $item->delete();

        return redirect()->route('item.index')->with('success', 'Item berhasil dihapus.');
    }

    public function itemAdjustStock(Request $request, $id)
    {
        $item = Item::where('id_business', $this->businessId())->findOrFail($id);

        if ($item->is_service) {
            abort(404);
        }

        $request->validate([
            'type'     => 'required|in:in,adjustment',
            'quantity' => 'required|numeric|min:0|max:9999999',
            'note'     => 'nullable|max:255',
        ]);

        if ($request->type == 'in') {
            if ($request->quantity <= 0) {
                return back()->withErrors(['quantity' => 'Jumlah stok masuk harus lebih dari 0.']);
            }

            $this->changeStock($item, 'in', (float) $request->quantity, $request->note ?: 'Stok masuk');
        } else {
            $selisih = (float) $request->quantity - $item->stock;

            $this->changeStock($item, 'adjustment', $selisih, $request->note ?: 'Koreksi stok');
        }

        return back()->with('success', 'Stok berhasil diperbarui.');
    }

    public function categoryIndex()
    {
        $rows    = Category::withCount('items')->where('id_business', $this->businessId())->orderBy('category_name')->get();
        $editing = null;

        return view('stok.kategori', compact('rows', 'editing'));
    }

    public function categoryEdit($id)
    {
        $bid     = $this->businessId();
        $rows    = Category::withCount('items')->where('id_business', $bid)->orderBy('category_name')->get();
        $editing = Category::where('id_business', $bid)->findOrFail($id);

        return view('stok.kategori', compact('rows', 'editing'));
    }

    public function categoryStore(Request $request)
    {
        $bid = $this->businessId();

        $request->validate([
            'category_name' => 'required|max:100|unique:categories,category_name,NULL,id_category,id_business,' . $bid,
        ]);

        Category::create(['id_business' => $bid, 'category_name' => $request->category_name]);

        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function categoryUpdate(Request $request, $id)
    {
        $bid      = $this->businessId();
        $category = Category::where('id_business', $bid)->findOrFail($id);

        $request->validate([
            'category_name' => 'required|max:100|unique:categories,category_name,' . $category->id_category . ',id_category,id_business,' . $bid,
        ]);

        $category->update(['category_name' => $request->category_name]);

        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function categoryDestroy($id)
    {
        $category = Category::where('id_business', $this->businessId())->findOrFail($id);

        $dipakai = $category->items()->count();

        if ($dipakai > 0) {
            return back()->with('error', 'Kategori tidak bisa dihapus karena masih dipakai oleh ' . $dipakai . ' item.');
        }

        $category->delete();

        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil dihapus.');
    }

    public function unitIndex()
    {
        $rows    = Unit::withCount('items')->where('id_business', $this->businessId())->orderBy('unit_name')->get();
        $editing = null;

        return view('stok.satuan', compact('rows', 'editing'));
    }

    public function unitEdit($id)
    {
        $bid     = $this->businessId();
        $rows    = Unit::withCount('items')->where('id_business', $bid)->orderBy('unit_name')->get();
        $editing = Unit::where('id_business', $bid)->findOrFail($id);

        return view('stok.satuan', compact('rows', 'editing'));
    }

    public function unitStore(Request $request)
    {
        $bid = $this->businessId();

        $request->validate([
            'unit_name' => 'required|max:50|unique:units,unit_name,NULL,id_unit,id_business,' . $bid,
        ]);

        Unit::create(['id_business' => $bid, 'unit_name' => $request->unit_name]);

        return redirect()->route('satuan.index')->with('success', 'Satuan berhasil ditambahkan.');
    }

    public function unitUpdate(Request $request, $id)
    {
        $bid  = $this->businessId();
        $unit = Unit::where('id_business', $bid)->findOrFail($id);

        $request->validate([
            'unit_name' => 'required|max:50|unique:units,unit_name,' . $unit->id_unit . ',id_unit,id_business,' . $bid,
        ]);

        $unit->update(['unit_name' => $request->unit_name]);

        return redirect()->route('satuan.index')->with('success', 'Satuan berhasil diperbarui.');
    }

    public function unitDestroy($id)
    {
        $unit = Unit::where('id_business', $this->businessId())->findOrFail($id);

        $dipakai = $unit->items()->count();

        if ($dipakai > 0) {
            return back()->with('error', 'Satuan tidak bisa dihapus karena masih dipakai oleh ' . $dipakai . ' item.');
        }

        $unit->delete();

        return redirect()->route('satuan.index')->with('success', 'Satuan berhasil dihapus.');
    }
}
