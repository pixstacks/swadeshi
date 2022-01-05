<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Notification;

class NotificationPage extends Component
{
    public $notifications;
    public $currNotification = NULL;
    public $lastNotification;
    public $firstNotification;
    public $notificationId;
    public $haveMoreResults = true;
    public $userType = 'user';
    protected $listeners = ['newNotification'];

    protected $queryString = ['notificationId'];

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

    public function getNotification($notificationId)
    {
        $this->notificationId = $notificationId;
        $notify = Notification::where('id', $notificationId)
            ->whereIn('notify_type', [$this->userType, 'all'])
            ->first();

        if(!$notify) {
            $this->emit('livewire_error', 'Notification Does Not Exist.');
        }
        else {
            $this->currNotification = $notify;
        }
    }

    public function notificationList()
    {
        $this->currNotification = NULL;
        $this->notificationId = NULL;
    }

    public function loadMoreNotification()
    {
        $oldNotifications =  Notification::whereIn('notify_type', [$this->userType, 'all'])
            ->where('id', '<', $this->firstNotification)
            ->where('status', '1')
            ->limit(3)
            ->latest()
            ->orderBy('id', 'desc')
            ->get();

        $this->notifications = $this->notifications->merge($oldNotifications);

        if($oldNotifications->count() < 3) {
            $this->haveMoreResults = false;
        }
        else {
            $this->firstNotification = $oldNotifications->last()->id;
        }
    }
    
    public function mount($notificationId = null, $userType = null)
    {
        if($userType) {
            $this->userType = $userType;
        }

        $this->notificationId = $notificationId;
        if($notificationId) {
            $this->getNotification($notificationId);
        }
        $this->notifications =  Notification::whereIn('notify_type', [$this->userType, 'all'])
            ->where('status', '1')
            ->limit(15)
            ->latest()
            ->orderBy('id', 'desc')
            ->get();
            
        if($this->notifications->count() < 15) {
            $this->haveMoreResults = false;
        }

        if($this->notifications->count()) {
            $this->firstNotification = $this->notifications->last()->id;
            $this->lastNotification = $this->notifications->first()->id;
        }
    }
    
    public function render()
    {
        return view('livewire.notification-page');
    }
}
