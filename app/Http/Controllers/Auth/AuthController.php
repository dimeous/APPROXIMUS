<?php

namespace App\Http\Controllers\Auth;

use App\User;
use Illuminate\Support\Facades\Request;
use Validator;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Registration & Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users, as well as the
    | authentication of existing users. By default, this controller uses
    | a simple trait to add these behaviors. Why don't you explore it?
    |
    */

    use  ThrottlesLogins;

    /**
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new authentication controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest', ['except' => 'logout']);

    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'password' => 'required|confirmed|min:6',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);
    }

    public function getLogin()
    {
        return view('auth.login');
    }

    public function getLogout()
    {
        Auth::logout();
        return redirect('login');
    }

    public function postLogin(\Request $request)
    {
        if (!$users =User::all()->toArray()){
            $this->create([
                'name' => 'naesdfg',
                'email' => 'tes@gmail.com',
                'password' => bcrypt('laravelssdgsdfg')
            ]);
        }

       $pass =Request::input('password');
       if ($pass=='12345') {
           Auth::loginUsingId(1,1);
           return redirect()->intended();
       }
       else
           return view('auth.login');
    }

}
