<?php
  require_once dirname(dirname(__FILE__)) . "/base.php";

  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
      $db = new Database;
      if (!empty(Functions::getPostVarSafely("username")) && !empty(Functions::getPostVarSafely("password"))) {
        if (!User::exists(Functions::getPostVarSafely("username"))) {
          if (hash("sha256", Functions::getPostVarSafely("password")) == hash("sha256", Functions::getPostVarSafely("password_verify"))) {
            $data = array(
              "name" => Functions::getPostVarSafely("fullname"),
              "email" => Functions::getPostVarSafely("email"),
              "password" => hash("sha256", Functions::getPostVarSafely("password")),
              "permission" => Functions::getPostVarSafely("permission"),
            );
            $db->hMSet(("internal:users:" . Functions::getPostVarSafely("username")), $data);
            $db->sAdd("internal:users", Functions::getPostVarSafely("username"));
            $rend->setAlert("success", "Added user \"" . $data["name"] . "\" successfully. Username: " . Functions::getPostVarSafely("username") . ".");
          } else {
            $rend->setAlert("warning", "Passwords do not match.");
          }
        } else {
          $rend->setAlert("danger", "Username already exists.");
        }
      } else {
        $rend->setAlert("warning", "Some fields are empty. Please fill out all fields.");
      }
    } catch (Throwable $e) {
      $rend->setAlert("warning", "Failed to add new user \"" . Functions::getPostVarSafely("username") . "\". Error: " . $e->getMessage());
    }
  }
