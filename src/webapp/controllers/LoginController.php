<?php

namespace ttm4135\webapp\controllers;
use ttm4135\webapp\Auth;
use ttm4135\webapp\models\User;

class LoginController extends Controller
{
    function __construct()
    {
        parent::__construct();
    }

    function index()
    {
        if (Auth::check()) {
            $username = Auth::user()->getUserName();
            $this->app->flash('info', 'You are already logged in as ' . $username);
            $this->app->redirect('/');
        } else {

            if(empty($_SESSION['token'])) {
                if(function_exists('mcrypt_create_iv')) {
                    $_SESSION['token'] = bin2hex(mcrypt_create_iv(32, MCRYPT_DEV_URANDOM));
                } else {
                    $_SESSION['token'] = bin2hex(openssl_random_pseudo_bytes(32));
                }
            }
            $this->render('login.twig', ['title'=>"Login", 'token' => $_SESSION['token'], ]);
        }
    }

    function login()
    {
        $request = $this->app->request;
        $username = $request->post('username');
        $password = $request->post('password');

        if ( Auth::checkCredentials($username, $password) ) {
            $user = User::findByUser($username);
            $_SESSION['userid'] = $user->getId();
            $this->app->flash('info', "You are now successfully logged in as " . $user->getUsername() . ".");
            $this->app->redirect('/');
        } else {
            $this->app->flashNow('error', 'Incorrect username/password combination.');
            $this->render('login.twig', []);
        }
    }

    function logout()
    {   
        Auth::logout();
        $this->app->flashNow('info', 'Logged out successfully!!');
        $this->render('base.twig', []);
        return;
       
    }
}
