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
            // (condition) ? true : false
            $username = (isset($_COOKIE['username'])) ? $_COOKIE['username'] : "";

            $this->render('login.twig', ['title'=>"Login", 'username' => $username, ]);
        }
    }

    function login()
    {
        $request = $this->app->request;
        $username = $request->post('username');
        $password = $request->post('password');

        // Check if username is empty
        if(empty($username) || empty($password)) {
            $this->app->flashNow('error', 'Invalid login credentials!');
            $this->render('login.twig', []);
        }
        else {
            // Validate fields
            $username = htmlspecialchars(stripslashes(trim($username)));
            $password = htmlspecialchars(stripslashes(trim($password)));

            if ( Auth::checkCredentials($username, $password) ) {
                $user = User::findByUser($username);
                $_SESSION['userid'] = $user->getId();
                setcookie('username', $username, time()+3600*24*30,"/");
                $this->app->flash('info', "You are now successfully logged in as " . $user->getUsername() . ".");
                $this->app->redirect('/');
            } else {
                $this->app->flashNow('error', 'Incorrect username/password combination.');
                $this->render('login.twig', []);
            }
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
