<?php

namespace App\Controller;
use PDO;
use App\exceptions\successful;
use Exception;


class HomeController {
  private $auth;

  public function __construct() {
    $pdo = new PDO('mysql:host=localhost;dbname=app;', 'root', '');
    $this->auth = new \Delight\Auth\Auth($pdo);
  }

  public function register() {
    $templates = new \League\Plates\Engine('../app/path');
    echo $templates->render('page_register', ['users' => $users]);
  }

  public function handlerRegister() {
    try {
        $userId = $this->auth->register($_POST['email'], $_POST['password'], function ($selector, $token) {
        });

        flash()->success('Регистрация успешна ');
        header("Location: login");
    }
    catch (\Delight\Auth\InvalidEmailException $e) {
      header("Location: register");
      flash()->error('Invalid email address'); 
    }
    catch (\Delight\Auth\InvalidPasswordException $e){ 
      header("Location: register");
      flash()->error('Invalid password');
    }
    catch (\Delight\Auth\UserAlreadyExistsException $e) {     
      header("Location: register");
      flash()->error('User already exists');
    }
    catch (\Delight\Auth\TooManyRequestsException $e) { 
      header("Location: register");
      flash()->error('Too many requests');
    }
  }

  public function login() {
    $templates = new \League\Plates\Engine('../app/path');
    echo $templates->render('page_login', ['users' => $users]);
  }

  public function handlerLogin() {
        try {
        $this->auth->login($_POST['email'], $_POST['password']);

        echo 'User is logged in';
        header("Location: users");
    }
    catch (\Delight\Auth\InvalidEmailException $e) {
        flash()->error('Wrong email address');
        header("Location: login");
    }
    catch (\Delight\Auth\InvalidPasswordException $e) {
      header("Location: login");
      flash()->error('Wrong password');
    }
    catch (\Delight\Auth\EmailNotVerifiedException $e) {
      header("Location: login");
      flash()->error('Email not verified');  
      die('Email not verified');
    }
    catch (\Delight\Auth\TooManyRequestsException $e) {
      header("Location: login");
      flash()->error('Too many requests');  
    }
  }

  public function logOut() {
    $this->auth->logOut();
    header("Location: login");
    //     try {
    //     $this->auth->logOutEverywhereElse();
    //     header("Location: login");
    // }
    // catch (\Delight\Auth\NotLoggedInException $e) {
    //   header("Location: login");
    //   flash()->error("Not logged in");
    //     // die('Not logged in');
    // }
  }

  public function insertHandler() {
    try {
        $userId = $this->auth->admin()->createUser($_POST['email'], $_POST['password'], $_POST['username']);
        flash()->success('We have signed up a new user with the ID ');
        header("Location: users");
    }
    catch (\Delight\Auth\InvalidEmailException $e) {
        header("Location: insert");
        flash()->error('Invalid email address');
    }
    catch (\Delight\Auth\InvalidPasswordException $e) {
        header("Location: insert");
        flash()->error('Invalid password');
    }
    catch (\Delight\Auth\UserAlreadyExistsException $e) {
        header("Location: insert");
        flash()->error('User already exists');
    }
  }

  public function security_handler() {

    if ($this->auth->hasRole(\Delight\Auth\Role::ADMIN)) {
      try {
          $this->auth->admin()->changePasswordForUserById($_POST['id'], $_POST['newPassword']);
          header("Location: users");
          flash()->success('Password has been changed');
      }
      catch (\Delight\Auth\UnknownIdException $e) {
          die('Unknown ID');
      }
      catch (\Delight\Auth\InvalidPasswordException $e) {
          die('Invalid password');
      }
    } else {
      try {
        $this->auth->changePassword($_POST['oldPassword'], $_POST['newPassword']);
        header("Location: users");
        flash()->success('Password has been changed');
      }
      catch (\Delight\Auth\NotLoggedInException $e) {
          header('location: security?id='.$_POST['id']);
          flash()->error('Not logged in');
      }
      catch (\Delight\Auth\InvalidPasswordException $e) {
          header('location: security?id='.$_POST['id']);
          flash()->error('Invalid password(s)');
      }
      catch (\Delight\Auth\TooManyRequestsException $e) {
          header('location: security?id='.$_POST['id']);
          flash()->error('Too many requests');
      }
    }
  }

}