<?php

  if(!isset($_GET['q']) || !preg_match('/([A-Za-z0-9_]+)/', $_GET['q'])) {
    header('Location: /');
    exit();
  }

  $category = htmlspecialchars($_GET['q']);

  include('trending.html');

?>