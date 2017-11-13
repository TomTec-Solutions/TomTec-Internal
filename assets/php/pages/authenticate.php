<?php
  require_once dirname(dirname(__FILE__)) . "/base.php";

  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $postdata = array(
      "username" => Functions::getPostVarSafely("username"),
      "password" => Functions::getPostVarSafely("password"),
    );
    $auth = new Authentication;
    if (User::exists($postdata["username"])) {
      try {
        $auth->login($postdata["username"], $postdata["password"]);
        $rend->setAlert("success", "Logged in.");
        $rend->setTemplateVariable("isLoggedIn", Authentication::isLoggedIn()); // have to update this as it's already been set
        Header("Location: dashboard"); // send user to the dashboard
      } catch (Throwable $e) {
        $rend->setAlert("warning", $e->getMessage());
      }
    } else {
      $rend->setAlert("danger", "Invalid username.");
    }
  }
