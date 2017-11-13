<?php
  require_once dirname(dirname(__FILE__)) . "/base.php";
  
  if (!Authentication::isLoggedIn()) {
    header("HTTP/1.0 403 Forbidden");
    die();
  }
  
  $user = new User(Authentication::getCurrentUserName());
  if (Functions::wasRequestPost()) {
    if (isset($_POST["notepad"])) {
      $user->setNotepad(Functions::getPostVarSafely("notepad"));
    }
  } else {
    if (isset($user->notepad) && !empty($user->notepad)) {
      die(htmlspecialchars_decode($user->notepad));
    }
  }