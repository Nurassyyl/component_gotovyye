<?php

namespace App;
use Aura\SqlQuery\QueryFactory;
use PDO;

class QueryBuilder {

  private $db;
  private $queryFactory;
  private $auth;

  public function __construct() {
    $this->pdo = new PDO('mysql:host=localhost;dbname=app;', 'root', '');
    $this->db = new PDO('mysql:host=localhost;dbname=users;', 'root', '');
    $this->queryFactory = new QueryFactory('mysql');
    $this->auth = new \Delight\Auth\Auth($this->pdo);
  }

  public function getAll() {
    $select = $this->queryFactory->newSelect();
    $select->cols(['*'])->from('users');
    $sth = $this->pdo->prepare($select->getStatement());
    $sth->execute($select->getBindValues());
    $users = $sth->fetchAll(PDO::FETCH_ASSOC);
    $templates = new \League\Plates\Engine('../app/path');
    $images = $this->getAllAvatar();

    if ($this->auth->isLoggedIn()) {
        $id = $this->auth->getUserId();
        $email = $this->auth->getEmail();
        $username = $this->auth->getUsername();
        echo $templates->render('users', ['users' => $users, 'id' => $id,'email' => $email, 'username' => $username, 'images' => $images  ]);
    }
    else {
        echo 'User is not signed in yet';
        echo $templates->render('users', ['users' => $users]);
    }
  }

  public function insert() {
    $templates = new \League\Plates\Engine('../app/path');
    echo $templates->render('create_user', ['users' => $users]);
  }

  public function update() {
    $id = $_GET['id'];
    $select = $this->queryFactory->newSelect();
    $select->cols(['*'])->from('users')->where('id = :id')->bindValues(['id'=> $id]);
    $sth = $this->pdo->prepare($select->getStatement());
    $sth->execute($select->getBindValues());
    $idUsers = $sth->fetch(PDO::FETCH_ASSOC);
    $templates = new \League\Plates\Engine('../app/path');
    echo $templates->render('edit', ['idUsers' => $idUsers]);
  }

  public function handlerUpdate() {
    $id = $_POST['id'];
    $col_value = $_POST['username'];
    $update = $this->queryFactory->newUpdate();
    $update->table('users')->cols(['username' => $col_value])->where('id = :id')->bindValues(['id' => $id]);
    $sth = $this->pdo->prepare($update->getStatement());
    $sth->execute($update->getBindValues());
    header('Location: users');
    flash()->success('Профиль успешно обновлен.');
  }

  public function security() {
    $id = $_GET['id'];
    $select = $this->queryFactory->newSelect();
    $select->cols(['*'])->from('users')->where('id = :id')->bindValues(['id' => $id]);
    $sth = $this->pdo->prepare($select->getStatement());
    $sth->execute($select->getBindValues());
    $idUsers = $sth->fetch(PDO::FETCH_ASSOC);
    $templates = new \League\Plates\Engine('../app/path');
    echo $templates->render('security', ['idUsers' => $idUsers]);
  }

  public function status() {
    $templates = new \League\Plates\Engine('../app/path');
    echo $templates->render('status');
  }

  public function avatar() {
    $templates = new \League\Plates\Engine('../app/path');
    echo $templates->render('media');
  }

  public function avatar_handler() {
    $insert = $this->queryFactory->newInsert();
    $extension = pathinfo($_FILES['avatar']['name']);
    $avatar = uniqid() .".". $extension['extension']; 
    $insert->into('create_users')             
           ->cols(['img_name' => $avatar]);
    
    $sth = $this->db->prepare($insert->getStatement());
    $sth->execute($insert->getBindValues());

    $uploads_dir = '../img/demo/avatars';
    $tmp_name = $_FILES['avatar']['tmp_name'];
    move_uploaded_file($tmp_name, "$uploads_dir/$avatar");
    
    header("Location: users");
  }

  public function getAllAvatar() {
    $select = $this->queryFactory->newSelect();
    $select->cols(['*'])->from('create_users');
    $sth = $this->db->prepare($select->getStatement());
    $sth->execute($select->getBindValues());
    $images = $sth->fetchAll(PDO::FETCH_ASSOC);
    return $images;
    // $templates = new \League\Plates\Engine('../app/path');
    // echo $templates->render('users', ['images' => $images]);
  }

   public function delete() {
    if ($this->auth->hasRole(\Delight\Auth\Role::ADMIN)) {
      try {
          $this->auth->admin()->deleteUserById($_GET['id']);
          header("Location: users");
      }
      catch (\Delight\Auth\UnknownIdException $e) {
          die('Unknown ID');
      }
    }
  }

}