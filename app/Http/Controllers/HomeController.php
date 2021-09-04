<?php

namespace App\Http\Controllers;

use App\Models\User;
use Google_Client;
use Google_Service_Oauth2;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
//        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }

    public function googleAuth()
    {
        // google client instance
        $client = new Google_Client();
        // set client token and id
        $file = Storage::path('public/client_secret.json');
        $client->setAuthConfig($file);
        // scopes specifies google services of which the token should be used for.
        $client->addScope('email' );
        $client->addScope('profile' );
        // set callback url (it must match the one provided in google client console)
        $client->setRedirectUri(''.env('APP_URL').'/auth/google/callback');
        // offline access will give you both an access and refresh token so that
        // your app can refresh the access token without user interaction.
        $client->setAccessType('offline');
        // Using "consent" ensures that your application always receives a refresh token.
        // If you are not using offline access, you can omit this.
        // $client->setApprovalPrompt('consent');
        // incremental auth
        $client->setIncludeGrantedScopes(false);

        // build url
        $auth_url = $client->createAuthUrl();

        return redirect(filter_var($auth_url, FILTER_SANITIZE_URL));
    }

    public function callback()
    {
        // google client instance
        $client = new Google_Client();
        // set client token and id
        $file = Storage::path('public/client_secret.json');
        $client->setAuthConfig($file);
        // scopes specifies google services of which the token should be used for.
        $client->addScope('email' );
        $client->addScope('profile' );
        // trade code for access token
        $client->fetchAccessTokenWithAuthCode($_GET['code']);
        // now get oauth data from google oauth service
        $service = new Google_Service_Oauth2($client);
        $user = $service->userinfo->get();

        // now you should have gotten the authenticated user data, you can now search ur database for the user account
        if($finduser = User::where('google_id', $user->id)->first())
        {
            Auth::login($finduser);
        }
        else{
            $newUser = User::create([
                'name' => $user->name,
                'email' => $user->email,
                'google_id' => $user->id,
                'password' => encrypt('app_passKey_google')
            ]);

            Auth::login($newUser);
        }
        // redirect to dashboard
        return redirect('home');
    }

}
