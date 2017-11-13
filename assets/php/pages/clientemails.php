<?php

$emails = [];

foreach (Client::allClients() as $client) {
  if (isset($client->email) && !empty($client->email)) {
    $emails[] = $client->email;
  }
}

$emails = array_unique($emails);

$rend->setTemplateVariable("clientemails", implode(", ", $emails));

