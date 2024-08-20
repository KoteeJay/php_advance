<?php
namespace App\Core;

class Session{
    // Start session

    public function __construct(){
        // Check if session has started
        if(session_status() == PHP_SESSION_NONE){
            session_start();

        }
    }

    //Set session
    public function set($key, $value){
        $_SESSION[$key] = $value;
        return $_SESSION[$key];
    }

    //Get session
    public function get($key){
        return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
    }

    //Delete session
    public function delete($key){
        if (isset($_SESSION[$key])){
            unset($_SESSION[$key]);
        }
    }

    //Destroy session
    public function destroy(){
        session_destroy();
    }
}