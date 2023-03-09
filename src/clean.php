<?php

  if(isset($_GET['category'])) {
    if(!preg_match('/([A-Za-z0-9_]+)/', $_GET['category'])) {
      header('Location: /');
      exit();
    }
    $category = htmlspecialchars($_GET['category']);
    include('trending.html');
    exit();
  }

  if(isset($_GET['artist'])) {
    // if(!preg_match('/([A-Za-z0-9_]+)/', $_GET['artist'])) {
    //   header('Location: /');
    //   exit();
    // }
    $artist = htmlspecialchars($_GET['artist']);
    include('trending.html');
    exit();
  }

  if(isset($_GET['song'])) {
    // if(!preg_match('/([A-Za-z0-9_]+)/', $_GET['song'])) {
    //   header('Location: /');
    //   exit();
    // }
    $song = htmlspecialchars($_GET['song']);
    include('trending.html');
    exit();
  }

  if(isset($_GET['search'])) {
    $query = htmlspecialchars($_GET['search']);
    $page = intval($_GET['page']);
    if($page < 1) {
      header('Location: /');
      exit();
    }
    include('results-search.html');
    exit();
  }


?>
