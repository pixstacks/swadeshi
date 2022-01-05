<?php

namespace App\Http\Controllers\User;

use App\Models\Faq;
use App\Models\Blog;
use App\Models\Setting;
use App\Models\GeoFencing;
use App\Models\ServiceType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\User\UpdateProfileRequest;

class HomeController extends Controller
{
    /**
     * Get the user home.
     * 
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $set = Setting::select('key', 'value')->where('key', 'like', 'home_%')->get();
        
        $settings = array();
        if($set) {
            foreach($set as $key => $value) {
                $settings[$value->key] = $value->value;
            }
        }
        
        $geoFences = GeoFencing::all();
        return view('index', compact('geoFences', 'settings'));
    }
    
    public function drive()
    {
        $results = Setting::select('key', 'value')
            ->where('key', 'like', 'drive_%')
            ->get()->toArray();
        $settings = [];
        foreach($results as $result) {
            $settings[$result['key']] = $result['value'];
        }
        return view('drive', compact('settings'));
    }
    
    public function ride()
    {
        $results = Setting::select('key', 'value')
            ->where('key', 'like', 'ride_%')
            ->get()->toArray();
        $settings = [];
        foreach($results as $result) {
            $settings[$result['key']] = $result['value'];
        }
        return view('ride', compact('settings'));
    }

    /**
     * Get the FAQ's page.
     * 
     * @return \Illuminate\Http\Response
     */
    public function faq()
    {
        $faqs = Faq::where('status', 1)->get();
        return view('faq', compact('faqs'));
    }
    
    /**
     * Get the Contact Us.
     * 
     * @return \Illuminate\Http\Response
     */
    public function contactUs()
    {
        $results = Setting::select('key', 'value')
            ->where('key', 'like', 'contact_%')
            ->get()->toArray();
        $settings = [];
        foreach($results as $result) {
            $settings[$result['key']] = $result['value'];
        }
        return view('contactUs', compact('settings'));
    }

    /**
     * Get the Terms & Conditions.
     * 
     * @return \Illuminate\Http\Response
     */
    public function tnc()
    {
        return view('tnc');
    }

    /**
     * Get the Privacy Policy.
     * 
     * @return \Illuminate\Http\Response
     */
    public function privacy()
    {
        return view('privacy');
    }

    public function blog()
    {
        $blogs = Blog::all();

        return view('blog', compact('blogs'));
    }

    public function showBlog($blog)
    {
        // Type Hint this
        return view('viewBlog', compact('blog'));
    }

    public function notification()
    {
        return view('user.notification');
    }

    public function settings()
    {
        return view('user.settings');
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        $credentials = $request->validated();
        
        $user = $request->user('web');

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::delete($user->avatar);
            }

            $credentials['avatar'] = $request->file('avatar')->store('public/user/avatars');
        }

        $user->update($credentials);

        return redirect(route('user.settings')."#general")
            ->withSuccess(__('crud.navlinks.profile')." ".__('crud.general.updated'));
    }

    /**
     * Updates the Provider Password
     * 
     * @param ChangePasswordRequest $request
     * @return Illuminate\Http\Response
     */  
    public function changePassword(ChangePasswordRequest $request)
    {
        $credentials = $request->only('password');
        if (!Hash::check($request->old_password, $request->user('web')->password)) {
            return redirect(route('user.settings')."#changePassword")
                ->withErrors([
                    'old_password' => 'Incorrect Old Password',
                ]);
        }

        $credentials['password'] = Hash::make($request['password']);
        $user = $request->user('web');
        $user->update($credentials);

        return redirect(route('user.settings')."#changePassword")
            ->withSuccess(__('crud.inputs.password')." ".__('crud.general.updated'));
    }


    public function service(GeoFencing $geoFencing, ServiceType $serviceType)
    {
        $subServices = $geoFencing->serviceType()->where('parent_id', $serviceType->id)->wherePivot('status', '1')->get();
        return view('service', compact('serviceType', 'geoFencing', 'subServices'));
    }

    public function getServiceAjax(Request $request, ServiceType $serviceType)
    {
        if (!$request->ajax()) {
            abort(404);
        }
        
        return json_encode($serviceType);

    }

    public function activity(Request $request)
    {
        return view('user.activity');
    }
}