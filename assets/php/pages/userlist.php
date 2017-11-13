<?php
  require_once dirname(dirname(__FILE__)) . "/base.php";

  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
      $data = array(
        "name" => Functions::getPostVarSafely("fullname"),
        "email" => Functions::getPostVarSafely("email"),
        "permission" => Functions::getPostVarSafely("permission"),
      );
      $username = Functions::getPostVarSafely("username");
      if (User::exists($username)) {
        $user = new User($username);
        $user->update($data);
        $rend->setAlert("success", "Updated " . $data["name"] . " successfully.");
      } else {
        $rend->setAlert("warning", "Username " . $username . " does not exist in the database.");
      }
    } catch (Throwable $e) {
      $rend->setAlert("warning", "Failed updating user \"" . $username . "\". Error: " . $e->getMessage());
    }
  }

  if (isset($_GET["delete"])) {
    try {
      $user = new User($_GET["delete"]);
      $name = $user->fullname;
      if ($user->username != Authentication::getCurrentUserName()) {
        $user->delete();
        $rend->setAlert("success", "User \"" . $name . "\" has been deleted.");
      } else {
        $rend->setAlert("info", "You cannot delete yourself while you're logged in.");
      }
    } catch (Throwable $e) {
      $rend->setAlert("danger", $e->getMessage());
    }
  }

  $rend->setTemplateVariable("Users", User::allUsers());
