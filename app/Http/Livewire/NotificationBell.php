<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Notification;

class NotificationBell extends Component
{
    public $notifications;
    public $lastNotification = 0;
    public $userType = 'user';
    protected $listeners = ['newNotification'];

    public function newNotification()
    {
        // TODO: Put condition for date.
        $notifications = Notification::where('id', '>', $this->lastNotification)
            ->where('status', '1')
            ->whereIn('notify_type', [$this->userType, 'all'])
            ->limit(15)
            ->latest()
            ->orderBy('id', 'desc')
            ->get();
        
        if($notifications) {
            $this->lastNotification = $notifications->first()->id;
            $this->notifications = $notifications->merge($this->notifications);
        }

        
    }

    public function mount($userType = null)
    {
        if($userType) {
            $this->userType = $userType;
        }
        
        // TODO: Put condition for date.
        $this->notifications =  Notification::whereIn('notify_type', [$this->userType, 'all'])
            ->where('status', '1')
            ->limit(15)
            ->latest()
            ->orderBy('id', 'desc')
            ->get();
        $this->lastNotification = $this->notifications->first() ? $this->notifications->first()->id : 0;
    }

    public function render()
    {
        return view('livewire.notification-bell');
    }
}
