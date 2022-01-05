<?php

namespace App\Http\Livewire\User;

use App\Models\User;
use Livewire\Component;
use App\Models\UserRequest;

class Activity extends Component
{
    public $user; // current user
    public $showHistory; // variable that decides which section to show.
    public $show; // used to get value from component and set $showHistory Variable.
    public $updateRequestId; // 
    protected $queryString = ['show'];
    protected $listeners = ['updateCurrentRequest'];

    // Alpine variables
    public $showRequestDetails;

    // Rating Form Values
    public $ratingValue;
    public $comment;

    // Request Details;
    public $userRequest;

    public function updateCurrentRequest(UserRequest $request)
    {
        if($this->showRequestDetails == true) {
            $this->userRequest = $request;
        }
        $this->updateRequestId = $request->id;
    }
    
    public function mount(User $user, $show)
    {
        $this->updateRequestId = NULL;
        $this->user = $user;
        if($show == 'history') { 
            $this->showHistory = true;
        }
        else if($show == 'upcoming') {
            $this->showHistory = false;
        }
    }

    public function changeDisplay()
    {
        $this->show = $this->showHistory === true ? 'history' : 'upcoming';
    }

    public function render()
    {
        return view('livewire.user.activity');
    }
}