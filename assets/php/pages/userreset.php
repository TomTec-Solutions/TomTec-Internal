<?php
  require_once dirname(dirname(__FILE__)) . "/base.php";

  $rend->setTemplateVariable("Users", User::allUsers());

  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $db = new Database;
    try {
      $data = array(
        "username" => Functions::getPostVarSafely("username"),
        "password1" => Functions::getPostVarSafely("password"),
        "password2" => Functions::getPostVarSafely("password_verify"),
      );
      if (User::exists($data["username"])) {
        if (hash("sha256", $data["password1"]) == hash("sha256", $data["password2"]) && !empty($data["password1"])) { // Brooke Groves wrote her first line of code here.
          $db->hSet("internal:users:" . $data["username"], "password", hash("sha256", $data["password1"]));
          $rend->setAlert("success", "Password for " . $data["username"] . " reset successfully.");
        } else {
          $rend->setAlert("warning", "Passwords do not match, or passwords are empty.");
        }
      } else {
        $rend->setAlert("warning", "Username " . $data["username"] . " does not exist in the database.");
      }
    } catch (Throwable $e) {
      $rend->setAlert("warning", "Failed to reset user password for user \"" . $data["username"] . "\". Error: " . $e->getMessage());
    }
  }
