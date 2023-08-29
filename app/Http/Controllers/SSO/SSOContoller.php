<?php

namespace App\Http\Controllers\SSO;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Illuminate\Support\Facades\Auth;
class SSOContoller extends Controller
{
    public function getLogin(Request $request){
        $request->session()->put('state', $state = Str::random(40)); // To block CSRF Attacks
        $query                  = http_build_query([
            'client_id'               => env('CLIENT_ID'), // 99fbdf7c-77df-46f6-9fb3-45cfb4ec553d
            'redirect_uri'            => env('REDIRECT_URI'), // http://127.0.0.1:8080/callback
            'response_type'           => env('RESPONSE_TYPE'), // code
            'scope'                   => 'view-user',
            'state'                   => $state,
            'prompt'                  => true
        ]);
        return redirect('http://127.0.0.1:8000/oauth/authorize?'. $query);
    }

    public function getCallback(Request $request){
        $state = $request->session()->pull('state');
        throw_unless(strlen($state) > 0 && $state == $request->state, InvalidArgumentException::class);
        $response = Http::asForm()->post('http://127.0.0.1:8000/oauth/token',
        [
            'grant_type'            => 'authorization_code',
            'client_id'             => env('CLIENT_ID'),
            'client_secret'         => env('CLIENT_SECRET'),
            'redirect_uri'          => env('REDIRECT_URI'),
            'code'                  => $request->code
        ]);
        // For sake of seamlessness, store token data in the user session and using it everytime to try to connect to sso server
        $request->session()->put($response->json());
        return redirect()->route('sso.authuser');
    }
    public function getLogout(Request $request){
        $request->session()->flush();
        return redirect('/');
    }
    public function getAuthUser(Request $request){
        $access_token           = $request->session()->get('access_token');
        $response               = Http::withHeaders([
            'Authorization' => 'Bearer '. $access_token,
            'Accept' => 'application/json',
            // 'Content-Type' => 'application/json'
            ])->get('http://127.0.0.1:8000/api/user');

        $userArray              = $response->json();
        try{
            $email              = $userArray['email'];
            // $exists             = User::where('email', $email)->first();
            // if($exists) {
            //     Auth::loginUsingId($exists->id);
            //     return redirect()->route('home');
            // }
        } catch(\Throwable $th){
            return redirect('login')->withErrors('Failed To get login information! Try again');
        }
        $user             = User::where('email', $email)->first();

        if(!$user){
            $user                       = new User;
            $user->email                = $userArray['email'];
            $user->name                 = $userArray['name'];
            $user->email_verified_at    = $userArray['email_verified_at'];
            $user->save();
        }
        Auth::login($user);

        return redirect(route('home'));
    }
    function getClients(Request $request){
        $access_token           = $request->session()->get('access_token');
        $response               = Http::withHeaders([
            'Authorization' => 'Bearer '. $access_token,
            'Accept' => 'application/json',
            // 'Content-Type' => 'application/json'
            ])->get('http://127.0.0.1:8000/oauth/clients');

        $clientsArray              = $response->json();
        dd($clientsArray);
        return $clientsArray;
    }
}
