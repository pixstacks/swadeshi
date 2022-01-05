<?php

namespace App\Http\Controllers\Admin;

use App\Models\State;
use App\Models\Country;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\{
    StateStoreRequest, StateUpdateRequest
};

class StateController extends Controller
{
    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->authorize('view-any', State::class);

        $search = $request->get('search', '');

        $states = State::search($search)
            ->latest()
            ->paginate();

        return view('admin.states.index', compact('states', 'search'));
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $this->authorize('create', State::class);

        $countries = Country::pluck('name', 'id');

        return view('admin.states.create', compact('countries'));
    }

    /**
     * @param \App\Http\Requests\StateStoreRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(StateStoreRequest $request)
    {
        $this->authorize('create', State::class);

        $validated = $request->validated();

        $state = State::create($validated);

        return redirect()
            ->route('admin.states.index')
            ->withSuccess(__('crud.general.created'));
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\State $state
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, State $state)
    {
        $this->authorize('view', $state);

        return view('admin.states.show', compact('state'));
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\State $state
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, State $state)
    {
        $this->authorize('update', $state);

        $countries = Country::pluck('name', 'id');

        return view('admin.states.edit', compact('state', 'countries'));
    }

    /**
     * @param \App\Http\Requests\StateUpdateRequest $request
     * @param \App\Models\State $state
     * @return \Illuminate\Http\Response
     */
    public function update(StateUpdateRequest $request, State $state)
    {
        $this->authorize('update', $state);

        $validated = $request->validated();

        $state->update($validated);

        return redirect()
            ->route('admin.states.index')
            ->withSuccess(__('crud.general.updated'));
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\State $state
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, State $state)
    {
        $this->authorize('delete', $state);

        try {
            $state->delete();
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
            ->route('admin.states.index')
            ->withSuccess(__('crud.general.deleted'));
    }
}
