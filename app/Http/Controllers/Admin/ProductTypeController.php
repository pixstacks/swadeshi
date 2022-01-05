<?php

namespace App\Http\Controllers\Admin;

use App\Models\ProductType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductTypeRequest;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Admin\UpdateProductTypeRequest;

class ProductTypeController extends Controller
{
    public function __construct()
    {
        $this->middleware('demo')->only(['store','update', 'destroy']);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->authorize('list productTypes', ProductType::class);
        $search = $request->get('search' ,'');
        $productTypes = ProductType::search($search)
            ->latest()
            ->paginate();

        return view('admin.productType.index', compact('productTypes', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->authorize('create productTypes', ProductType::class);
        $productTypes = ProductType::all();
        return view('admin.productType.create', compact('productTypes'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreProductTypeRequest $request)
    {
        $credentials = $request->validated();

        if ($request->hasFile('icon')) {
            $credentials['icon'] = $request->file('icon')->store('public/product/icon');
        }

        ProductType::create($credentials);

        return redirect()
            ->route('admin.productType.index')
            ->withSuccess(__('crud.general.created'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        abort(403);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(ProductType $productType)
    {
        $this->authorize('view productTypes', $productType);

        return view('admin.productType.edit', compact('productType'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateProductTypeRequest $request, ProductType $productType)
    {
        $credentials = $request->validated();

        if ($request->hasFile('icon')) {
            if($productType->icon) {
                Storage::delete($productType->icon);
            }
            $credentials['icon'] = $request->file('icon')->store('public/service/icon');
        }

        $productType->update($credentials);

        return redirect()
            ->route('admin.productType.index')
            ->withSuccess(__('crud.general.updated'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(ProductType $productType)
    {
        $this->authorize('delete productTypes', $productType);
        try {
            $productType->delete();
        } catch(\Illuminate\Database\QueryException $e) {
            if($e->getCode() == 23000)
            {
                return redirect()
                    ->back()
                    ->withErrors(__('crud.general.integrity_violation'));
            }
            return redirect()
                ->back()
                ->withErrors(__('crud.general.not_done'));
        }
        return redirect()
            ->back()
            ->withSuccess(__('crud.general.deleted'));
    }
}
