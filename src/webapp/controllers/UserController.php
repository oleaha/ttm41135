<?php

namespace ttm4135\webapp\controllers;

use ttm4135\webapp\models\User;
use ttm4135\webapp\Auth;

class UserController extends Controller
{
    function __construct()
    {
        parent::__construct();
    }

    function index()
    {
	$allowed = ["Student CA", "Staff CA"];
	if (!in_array($_SERVER["REDIRECT_SSL_CLIENT_I_DN_CN"], $allowed)) {
		$this->app->flashNow('error', 'You do not have access to this resource!');
		$this->render('error.twig', []);
		return;
	}
        if (Auth::guest()) {

            // Check if CSRF token exists
            if (empty($_SESSION['token'])) {
                // Create CSRF token
                if (function_exists('mcrypt_create_iv')) {
                    $_SESSION['token'] = bin2hex(mcrypt_create_iv(32, MCRYPT_DEV_URANDOM));
                } else {
                    $_SESSION['token'] = bin2hex(openssl_random_pseudo_bytes(32));
                }
            }

            $this->render('newUserForm.twig', ['token' => $_SESSION['token']]);
        } else {
            $username = Auth::user()->getUserName();
            $this->app->flash('info', 'You are already logged in as ' . $username);
            $this->app->redirect('/');
        }
    }



    function create()
    {
        $request = $this->app->request;
        $username = htmlspecialchars(stripslashes(trim($request->post('username'))));
        $password = htmlspecialchars(stripslashes(trim($request->post('password'))));
        $token = htmlspecialchars(stripslashes(trim($request->post('csrf'))));
        $cert_cn = $_SERVER['REDIRECT_SSL_CLIENT_S_DN_CN'];

        if(isset($token) && hash_equals($token, $_SESSION['token'])) {
            if(!$this->validateString($username) || !$this->validateString($password)){
                $this->app->flash('error', 'Username and Password can not be empty');
                $this->app->redirect('/register');
            }

            if (User::findByUser($username) != null) {
                $this->app->flash('error', 'Username already exists. Be more creative!');
                $this->app->redirect('/register');
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $user = User::makeEmpty();
                $user->setUsername($username);
                $user->setPassword($hashedPassword);

                if ($request->post('email')) {
                    $email = htmlspecialchars(stripslashes(trim($request->post('email'))));
                    $user->setEmail($email);
                }
                if ($request->post('bio')) {
                    $bio = htmlspecialchars(stripslashes(trim($request->post('bio'))));
                    $user->setBio($bio);
                }
                $user->setCn($cert_cn);

                // If a user has certificate that is issued by Staff CA they should automatically be set as admin
                if($_SERVER['REDIRECT_SSL_CLIENT_I_DN_CN'] == "Staff CA") {
                    $user->setIsAdmin(true);
                }


                $user->save();
                $this->app->flash('info', 'Thanks for creating a user. You may now log in.');
                $this->app->redirect('/login');
            }
        } else {
            $this->app->flash('error', 'Mr. Willhelmsen, you sir, are not welcome here! YOU SHALL NOT PASS!');
            $this->app->redirect('/register');
        }


    }

    function delete($tuserid)
    {
        if(Auth::userAccess($tuserid))
        {
            $user = User::findById($tuserid);
            $user->delete();
            $this->app->flash('info', 'User ' . $user->getUsername() . '  with id ' . $tuserid . ' has been deleted.');
            $this->app->redirect('/admin');
        } else {
            $username = Auth::user()->getUserName();
            $this->app->flash('info', 'You do not have access this resource. You are logged in as ' . $username);
            $this->app->redirect('/');
        }
    }

    function deleteMultiple()
    {
        if(Auth::isAdmin()){
            $request = $this->app->request;
            $userlist = $request->post('userlist');
            $deleted = [];

            if($userlist == NULL){
                $this->app->flash('info','No user to be deleted.');
            } else {
                foreach( $userlist as $duserid)
                {
                    $user = User::findById($duserid);
                    if(  $user->delete() == 1) { //1 row affect by delete, as expect..
                        $deleted[] = $user->getId();
                    }
                }
                $this->app->flash('info', 'Users with IDs  ' . implode(',',$deleted) . ' have been deleted.');
            }

            $this->app->redirect('/admin');
        } else {
            $username = Auth::user()->getUserName();
            $this->app->flash('info', 'You do not have access this resource. You are logged in as ' . $username);
            $this->app->redirect('/');
        }
    }


    function show($tuserid)
    {
        if(Auth::userAccess($tuserid))
        {
            $user = User::findById($tuserid);
            $this->render('showuser.twig', [
                'user' => $user
            ]);
        } else {
            if(Auth::guest()) {
                // Hack
                $this->app->flash('info', 'You are not logged in!');
                $this->app->redirect('/');
            } else {
                $username = Auth::user()->getUserName();
                $this->app->flash('info', 'You do not have access this resource. You are logged in as ' . $username);
                $this->app->redirect('/');
            }
        }
    }

    function newuser()
    {

        $user = User::makeEmpty();

        if (Auth::isAdmin()) {


            $request = $this->app->request;

            $username = $request->post('username');
            $password = $request->post('password');
            $email = $request->post('email');
            $bio = $request->post('bio');

            $isAdmin = ($request->post('isAdmin') != null);


            $user->setUsername($username);
            $user->setPassword($password);
            $user->setBio($bio);
            $user->setEmail($email);
            $user->setIsAdmin($isAdmin);

            $user->save();
            $this->app->flashNow('info', 'Your profile was successfully saved.');

            $this->app->redirect('/admin');


        } else {
            $username = $user->getUserName();
            $this->app->flash('info', 'You do not have access this resource. You are logged in as ' . $username);
            $this->app->redirect('/');
        }
    }

    function edit($tuserid)
    {

        $user = User::findById($tuserid);

        if (! $user) {
            throw new \Exception("Unable to fetch logged in user's object from db.");
        } elseif (Auth::userAccess($tuserid)) {


            $request = $this->app->request;

            $username = $request->post('username');
            $password = $request->post('password');
            $email = $request->post('email');
            $bio = $request->post('bio');

            $isAdmin = ($request->post('isAdmin') != null);

            $password = password_hash($password, PASSWORD_DEFAULT);

            $user->setUsername($username);
            $user->setPassword($password);
            $user->setBio($bio);
            $user->setEmail($email);
            $user->setIsAdmin($isAdmin);

            $user->save();
            $this->app->flashNow('info', 'Your profile was successfully saved.');

            $user = User::findById($tuserid);

            $this->render('showuser.twig', ['user' => $user]);


        } else {
            $username = $user->getUserName();
            $this->app->flash('info', 'You do not have access this resource. You are logged in as ' . $username);
            $this->app->redirect('/');
        }
    }

    function validateString($string){
        return $string != null and strlen($string) > 0;
    }

}
