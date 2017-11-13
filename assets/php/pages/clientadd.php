<?php
  require_once dirname(dirname(__FILE__)) . "/base.php";

  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = array(
      "name" => Functions::getPostVarSafely("client_name"),
      "contact" => Functions::getPostVarSafely("contact_person"),
      "address" => Functions::getPostVarSafely("client_address"),
      "phone_primary" => Functions::getPostVarSafely("client_phone_primary"),
      "phone_secondary" => Functions::getPostVarSafely("client_phone_secondary"),
      "email" => Functions::getPostVarSafely("client_email"),
      "info" => Functions::getPostVarSafely("client_info"),
    );
    $client_id = Client::getNewClientId();
    if (!Client::exists($client_id)) {
      Client::add($data, $client_id);
      $rend->setAlert("success", "Added client \"" . $data["name"] . "\" successfully. ID: " . $client_id . ".");
    } else {
      $rend->setAlert("danger", "Client #" . $client_id . " already exists in the database.");
    }
  }
