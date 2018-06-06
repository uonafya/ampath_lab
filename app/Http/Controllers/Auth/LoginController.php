<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

use App\User;
use App\Batch;
use App\Viralbatch;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    // protected $redirectTo = '/sample/create';
    // protected $redirectTo = '/home';

    protected function redirectTo()
    {
        return $this->set_session();
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function fac_login()
    {
        return view('auth.fac-login', ['login_error' => session()->pull('login_error')]);
    }


    public function facility_login(Request $request)
    {
        $facility_id = $request->input('facility_id');
        $batch_no = $request->input('batch_no');

        $batch = Batch::find($batch_no);

        if($batch){
            if($batch->outdated()) return $this->failed_facility_login(); 
            if($batch->facility_id == $facility_id){
                $user = User::where(['facility_id' => $facility_id, 'user_type_id' => 5])->get()->first();
                
                if($user){
                    Auth::login($user);
                    return redirect($this->set_session(true));                    
                }
            }
        }

        $batch = Viralbatch::find($batch_no);

        if($batch){
            if($batch->outdated()) return $this->failed_facility_login(); 
            if($batch->facility_id == $facility_id){
                $user = User::where(['facility_id' => $facility_id, 'user_type_id' => 5])->get()->first();

                if($user){
                    Auth::login($user);
                    return redirect($this->set_session(true));                    
                }

                // if(Auth::attempt(['email' => $user->email, 'password' => 'password'])){
                //     return redirect('/viralsample/create');
                // }

            }
        }
        return $this->failed_facility_login(); 
    }

    public function failed_facility_login()
    {
        session(['login_error' => 'There was no batch for that facility']);
        return redirect('/login/facility');
    }

    private function set_session($facility = false)
    {
        // Checking for pending tasks if user is Lab user before redirecting to the respective page
        if (Auth()->user()->user_type_id == 1)
        {
            $tasks = $this->pendingTasks();
            // dd($tasks);

            if ($tasks['submittedstatus'] == 0 OR $tasks['labtracker'] == 0) {
                session(['pendingTasks' => true]); 
                return '/pending';
            }
        }
        // Checking for pending tasks if user is Lab user before redirecting to the respective page

        $batch = Batch::editing()->withCount(['sample'])->get()->first();
        if($batch){
            if($batch->sample_count > 9){
                $batch->full_batch();
            }
            else{
                $fac = \App\Facility::find($batch->id);
                session(['batch' => $batch, 'facility_name' => $fac->name]);
                session(['toast_message' => "The batch {$batch->id} is still awaiting release. You can add more samples or release it."]);
                return '/sample/create';
            }
        }

        $viralbatch = Viralbatch::editing()->withCount(['sample'])->get()->first();
        if($viralbatch){
            if($viralbatch->sample_count > 9){
                $viralbatch->full_batch();
            }
            else{
                $fac = \App\Facility::find($viralbatch->id);
                session(['viral_batch' => $viralbatch, 'viral_facility_name' => $fac->name]);
                session(['toast_message' => "The batch {$viralbatch->id} is still awaiting release. You can add more samples or release it."]);
                return '/viralsample/create';
            }
        }
        if($facility) return '/sample/create';
        return '/home';        
    }
}

