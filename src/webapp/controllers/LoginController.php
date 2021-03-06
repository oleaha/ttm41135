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

            // Check if CSRF token exists
            if (empty($_SESSION['token'])) {
                // Create CSRF token
                if (function_exists('mcrypt_create_iv')) {
                    $_SESSION['token'] = bin2hex(mcrypt_create_iv(32, MCRYPT_DEV_URANDOM));
                } else {
                    $_SESSION['token'] = bin2hex(openssl_random_pseudo_bytes(32));
                }
            }

            // (condition) ? true : false
            $username = (isset($_COOKIE['username'])) ? $_COOKIE['username'] : "";

            $this->render('login.twig', ['title'=>"Login", 'token' => $_SESSION['token'], 'username' => $username, ]);
        }
    }

    function login()
    {
        $request = $this->app->request;
        $username = htmlspecialchars(stripslashes(trim($request->post('username'))));
        $password = htmlspecialchars(stripslashes(trim($request->post('password'))));
        $token = htmlspecialchars(stripslashes(trim($request->post('csrf'))));


        // Check for CSRF
        if(isset($token) && hash_equals($token, $_SESSION['token'])) {
            // Validate input before processing
            if($this->validateAndStrip($username) && $this->validateAndStrip($password)) {
                // Validate user
                if(Auth::checkCredentials($username, $password)) {
                    $user = User::findByUser($username);
                    $_SESSION['userid'] = $user->getId();

                    // Create cookie with username for memory
                    setcookie('username', $username, time()+3600*24*30,"/", null, true, true);

                    $this->app->flash('info', "You are now successfully logged in as " . $user->getUsername() . ".");
                    $this->app->redirect('/');
                }
                else {
                    $username = (isset($_COOKIE['username'])) ? $_COOKIE['username'] : "";

                    $this->app->flashNow('error', 'Incorrect username/password combination.');
                    $this->render('login.twig', ['username' => $username, 'token' => $_SESSION['token']]);
                }
            }
            else {
                $username = (isset($_COOKIE['username'])) ? $_COOKIE['username'] : "";
                $this->app->flashNow('error', 'Invalid login credentials');
                $this->render('login.twig', ['username' => $username, 'token' => $_SESSION['token']]);
            }
        }
        else {
            $username = (isset($_COOKIE['username'])) ? $_COOKIE['username'] : "";
            $this->app->flashNow('error', 'Mr. Willhelmsen, you sir, are not welcome here! YOU SHALL NOT PASS!');
            $this->render('login.twig', ['username' => $username, 'token' => $_SESSION['token']]);
        }


    }

    function logout()
    {
        Auth::logout();
        $this->app->flashNow('info', 'Logged out successfully!!');
        $this->render('base.twig', []);
        return;

    }

    function validateAndStrip($string) {
        if(!empty($string)) {
            return htmlspecialchars(stripslashes(trim($string)));
        }
        return false;
    }
}
