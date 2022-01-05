<?php

namespace App\Http\Controllers\Admin;

use App\Models\City;
use App\Models\State;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Http\Requests\Admin\{
    CityStoreRequest, CityUpdateRequest
};

class CityController extends Controller
{
    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->authorize('view-any', City::class);

        $search = $request->get('search', '');

        $cities = City::search($search)
            ->latest()
            ->paginate();

        return view('admin.cities.index', compact('cities', 'search'));
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $this->authorize('create', City::class);

        $states = State::all();

        return view('admin.cities.create', compact('states'));
    }

    /**
     * @param \App\Http\Requests\CityStoreRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(CityStoreRequest $request)
    {
        $this->authorize('create', City::class);

        $validated = $request->validated();

        $city = City::create($validated);

        return redirect()
            ->route('admin.cities.index')
            ->withSuccess(__('crud.general.created'));
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\City $city
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, City $city)
    {
        $this->authorize('view', $city);

        return view('admin.cities.show', compact('city'));
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\City $city
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, City $city)
    {
        $this->authorize('update', $city);

        $states = State::all();

        return view('admin.cities.edit', compact('city', 'states'));
    }

    /**
     * @param \App\Http\Requests\CityUpdateRequest $request
     * @param \App\Models\City $city
     * @return \Illuminate\Http\Response
     */
    public function update(CityUpdateRequest $request, City $city)
    {
        $this->authorize('update', $city);

        $validated = $request->validated();

        $city->update($validated);

        return redirect()
            ->route('admin.cities.index')
            ->withSuccess(__('crud.general.updated'));
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\City $city
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, City $city)
    {
        $this->authorize('delete', $city);

        try {
            $city->delete();
        } catch (\Illuminate\Database\QueryException $e) {
            if($e->getCode() == 23000)
            {
                return redirect()
                    ->back()
                    ->withErrors("Integrity Constraint Violation.");
            }
            return redirect()
                ->back()
                ->withErrors("Some Error Occurred.");
        }

        return redirect()
            ->route('admin.cities.index')
            ->withSuccess(__('crud.general.deleted'));
    }
}