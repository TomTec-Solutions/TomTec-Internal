<?php
  require_once dirname(dirname(__FILE__)) . "/base.php";

  if (!Authentication::isLoggedIn()) {
    header("HTTP/1.0 403 Forbidden");
    die();
  }

  if (isset($_POST["client_id"]) || !empty($_POST["client_id"])) {
    $client_id = Functions::getPostVarSafely("client_id");
    if (!Client::exists($client_id)) {
      die("Invalid Client ID: " . $client_id);
    } else {
      $client = new Client($client_id);
      $client->info_br = nl2br($client->info);
    }
    if (isset($_POST["type"]) || !empty($_POST["type"])) {
      $type = Functions::getPostVarSafely("type");
      $ajaxRenderer = new Renderer;
      $ajaxRenderer->setTemplateVariable("Client", $client);
      if ($type == "details") {
        $ajaxRenderer->render("clientdetails.tmpl");
      } elseif ($type == "edit") {
        $ajaxRenderer->render("clientedit.tmpl");
      } else {
        echo "Type '" . $type .  "' not supported.";
      }
    } else {
      echo "Type parameter not provided.";
    }
  } else {
    echo "Invoice ID parameter not provided.";
  }
