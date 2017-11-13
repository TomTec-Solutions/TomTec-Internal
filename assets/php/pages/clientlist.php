<?php
  require_once dirname(dirname(__FILE__)) . "/base.php";


  if (isset($_GET["delete"])) {
    $id = $_GET["delete"];
    $client = new Client($id);
    if ($client->delete()) {
      $rend->setAlert("success", "Client \"" . $client->name . "\" has been deleted.");
    } else {
      $rend->setAlert("danger", "Client could not be deleted because no client record exists in the database for ID $id.");
    }
  }

  if (Functions::wasRequestPost()) {
    try {
      $data = array(
        "name" => Functions::getPostVarSafely("client_name"),
        "contact" => Functions::getPostVarSafely("contact_person"),
        "address" => Functions::getPostVarSafely("client_address"),
        "phone_primary" => Functions::getPostVarSafely("client_phone_primary"),
        "phone_secondary" => Functions::getPostVarSafely("client_phone_secondary"),
        "email" => Functions::getPostVarSafely("client_email"),
        "info" => Functions::getPostVarSafely("client_info"),
      );
      $client_id = intval();
      $client = new Client(Functions::getPostVarSafely("client_id"));
      $client->update($data);
      $rend->setAlert("success", "Updated client \"" . $data["name"] . "\" successfully.");
    } catch (Throwable $e) {
      $rend->setAlert("warning", "Failed updating client \"" . $data["name"] . "\". Error: " . $e->getMessage());
    }
  }

  $rend->setTemplateVariable("ClientData", Client::allClients());
