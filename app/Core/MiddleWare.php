<?php

namespace App\Core;

use App\Core\Session;

class MiddleWare
{
    //entry point
    public static function action($action = "")
    {
        if (method_exists(self::class, $action)) {
            return self::$action();
        } else {
            return ['error' => 'MiddleWare {' . $action . '} action not found', 'code' => 404];
        }
    }

    //MiddleWares
    //user
    public static function user()
    {
        $session = new Session;
        if (!$session->has('user')) {
            return ['error' => 'You are not logged in', 'code' => 401, 'redirect' => 'login'];
        } else {
            //check if user is admin
            $user = $session->get('user');
            if ($user->role == 'admin') {
                return ['error' => 'You are logged in as an Admin, please login as a User', 'code' => 401, 'redirect' => 'admin'];
            }
        }
        //return true
        return ['code' => 200];
    }

    //admin
    public static function admin()
    {
        $session = new Session;
        if (!$session->has('user')) {
            return ['error' => 'You are not logged in', 'code' => 401, 'redirect' => 'login'];
        }
        $user = $session->get('user');
        if ($user->role != 'admin') {
            return ['error' => 'You are not admin, please login as an Admin', 'code' => 401, 'redirect' => 'login'];
        }
        //return true
        return ['code' => 200];
    }

    //redirect if logged in
    public static function redirectIfLoggedIn()
    {
        $session = new Session;
        if ($session->has('user')) {
            //check if user is admin
            $user = $session->get('user');
            if ($user->role == 'admin') {
                return ['code' => 401, 'redirect' => 'admin', 'error' => 'You are already logged in as an Admin'];
            } else {
                return ['code' => 401, 'redirect' => 'user', 'error' => 'You are already logged in as a User'];
            }
        }
        //return true
        return ['code' => 200];
    }
}
