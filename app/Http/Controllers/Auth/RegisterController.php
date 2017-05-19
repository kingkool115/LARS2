<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Str;
use Mail;
use App\Mail\verifyEmail;
use Session;
use Illuminate\Auth\Events\Registered;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/login';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * This method will send and activation email for user's account.
     *
     * @param $thisUser user to send email to.
     */
    public function sendEmail($thisUser)
    {
        Mail::to($thisUser['email'])->send(new verifyEmail($thisUser));
    }

    /**
     * This method handles route /verify/{email}/{verifytoken}
     * This route is used by a user to activate his account when clicking on activation link in his email.
     *
     * @param $email email of user to register
     * @param $verifyToken verifyToken to verify if it's really the user.
     * @return if registration was successful or not.
     */
    public function sendEmailDone($email, $verifyToken)
    {
        $user =  User::where(['email' => $email, 'verifyToken' => $verifyToken, 'status'=>'0'])->first();
        if($user)
        {
           user::where(['email' => $email, 'verifyToken' => $verifyToken])->update(['status'=>'1', 'verifyToken'=>NULL]);
           return 'You are successfully registered!!';
            // TODO: create a view for this message
        } else {
            // TODO: create a view for this message
            return 'No user found to register';
        }
    }

    /**
     * Handle a registration request for the application.
     * Overrides method of super class.
     * Comment out auto login after registration.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        event(new Registered($user = $this->create($request->all())));

        // $this->guard()->login($user);

        return $this->registered($request, $user)
            ?: redirect($this->redirectPath());
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
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
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
        // show message on login page after your registration.
        Session::flash('status', 'Please check your emails to activate your account');

        // create a user into db
        $user =  User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'verifyToken' => Str::random(40)
        ]);
        // find our user in databsse and send him a confirmation email
        $thisUser = User::findOrFail($user->id);
        $this->sendEmail($thisUser);
        return $user;
    }
}
