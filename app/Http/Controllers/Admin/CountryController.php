<?php

namespace App\Http\Controllers\Admin;

use App\Models\Country;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\{
    CountryStoreRequest, CountryUpdateRequest
};

class CountryController extends Controller
{
    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->authorize('view-any', Country::class);

        $search = $request->get('search', '');

        $countries = Country::search($search)
            ->latest()
            ->paginate();

        return view('admin.countries.index', compact('countries', 'search'));
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $this->authorize('create', Country::class);

        return view('admin.countries.create');
    }

    /**
     * @param \App\Http\Requests\CountryStoreRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(CountryStoreRequest $request)
    {
        $this->authorize('create', Country::class);

        $validated = $request->validated();

        $country = Country::create($validated);

        return redirect()
            ->route('admin.countries.index')
            ->withSuccess(__('crud.general.created'));
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Country $country
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Country $country)
    {
        $this->authorize('view', $country);

        return view('admin.countries.show', compact('country'));
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Country $country
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, Country $country)
    {
        $this->authorize('update', $country);

        return view('admin.countries.edit', compact('country'));
    }

    /**
     * @param \App\Http\Requests\CountryUpdateRequest $request
     * @param \App\Models\Country $country
     * @return \Illuminate\Http\Response
     */
    public function update(CountryUpdateRequest $request, Country $country)
    {
        $this->authorize('update', $country);

        $validated = $request->validated();

        $country->update($validated);

        return redirect()
            ->route('admin.countries.index')
            ->withSuccess(__('crud.general.updated'));
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Country $country
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Country $country)
    {
        $this->authorize('delete', $country);

        try {
            $country->delete();
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
            ->route('admin.countries.index')
            ->withSuccess(__('crud.general.deleted'));
    }
}
