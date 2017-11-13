<?php
  require_once dirname(dirname(__FILE__)) . "/base.php";

  if (!Authentication::isLoggedIn()) {
    header("HTTP/1.0 403 Forbidden");
    die();
  }

  if (isset($_POST["username"]) || !empty($_POST["username"])) {
    $username = Functions::getPostVarSafely("username");
    if (!User::exists($username)) {
      die("Invalid Username: " . $client_id);
    } else {
      $user = new User($username);
    }
    if (isset($_POST["type"]) || !empty($_POST["type"])) {
      $type = Functions::getPostVarSafely("type");
      $ajaxRenderer = new Renderer;
      $ajaxRenderer->setTemplateVariable("User", $user);
      if ($type == "details") {
        $ajaxRenderer->render("userdetails.tmpl");
      } elseif ($type == "edit") {
        $ajaxRenderer->render("useredit.tmpl");
      } else {
        echo "Type '" . $type .  "' not supported.";
      }
    } else {
      echo "Type parameter not provided.";
    }
  } else {
    echo "Invoice ID parameter not provided.";
  }
