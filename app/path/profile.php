<?php $this->layout('template', ['title' => 'User Profile']) ?>

<h1>User Profile</h1>
<p>Hello, <?php echo $this->e($name)?></p>
<?php

foreach ($users as $user) {
  echo $user['email'];
}

?>