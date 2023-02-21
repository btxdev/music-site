<?php

  if(!isset($_GET['q'])) {
    header('Location: /');
    exit();
  }

  $category = htmlspecialchars($_GET['q']);
  echo('link is ');
  echo($category);

?>