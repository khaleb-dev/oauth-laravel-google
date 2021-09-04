<?php

namespace App\Http\Controllers;

use Google\Service\Gmail\Profile;
use Google\Service\Oauth2;
use Google_Client;
use Google_Service_Drive;
use Google_Service_Oauth2;
use Illuminate\Http\Request;
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

        $client = new Google_Client();
        $file = Storage::path('public/client_secret.json');
        $client->setAuthConfig($file);
//         $client->addScope(Oauth2::USERINFO_PROFILE, );
        $client->addScope('email' );
        $client->addScope('profile' );
//        $client->setRedirectUri('http://' . $_SERVER['HTTP_HOST'] . '/oauth2callback.php');
        $client->setRedirectUri(''.env('APP_URL').'/auth/google/callback');
// offline access will give you both an access and refresh token so that
// your app can refresh the access token without user interaction.
        $client->setAccessType('offline');
// Using "consent" ensures that your application always receives a refresh token.
// If you are not using offline access, you can omit this.
//        $client->setApprovalPrompt('consent');
        $client->setIncludeGrantedScopes(true);   // incremental auth

        // create url
        $auth_url = $client->createAuthUrl();
//        echo $auth_url;
//        exit();
//        header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));

        return redirect(filter_var($auth_url, FILTER_SANITIZE_URL));
    }

    public function callback()
    {
        $client = new Google_Client();
        $file = Storage::path('public/client_secret.json');
        $client->setAuthConfig($file);
        $client->addScope('email' );
        $client->addScope('profile' );
        $client->fetchAccessTokenWithAuthCode($_GET['code']);
        $service = new Google_Service_Oauth2($client);
        $user = $service->userinfo->get();

        $save


    }

}
