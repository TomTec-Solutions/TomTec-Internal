<?php

class Client extends TTS_Framework {

  public $name;
  public $contact;
  public $address;
  public $phone_primary;
  public $phone_secondary;
  public $email;
  public $info;

  public function __construct($id) {
    if (self::exists($id)) {
      $client = $this->getClientDataById($id);
      $this->id = $id;
      $this->name = $client["name"];
      $this->contact = $client["contact"];
      $this->address = $client["address"];
      $this->phone_primary = $client["phone_primary"];
      $this->phone_secondary = $client["phone_secondary"];
      $this->email = $client["email"];
      $this->info = $client["info"];
    } else {
      throw new Error("Client ID #" . $id . " does not exist.");
    }
  }

  /**
   * Checks to see if a client record exists.
   * 
   * @param int|str $id The ID of the potential client record.
   *
   * @return bool
   */
  public static function exists($id) {
    $db = new Database;
    return $db->sIsMember("internal:clients", $id);
  }
  
  /**
   * Checks to see if a client record exists.
   * 
   * @param int|str $id The ID of the potential client record.
   *
   * @return bool
   */
  public static function getNewClientId() {
    $db = new Database;
    $highest = 0;
    foreach ($db->sMembers("internal:clients") as $id) {
      if (intval($id) > $highest) {
        $highest = intval($id);
      }
    }
    return ($highest + 1);
  }
  
  /**
   * Return the latest used client ID.
   * 
   * @return int
   */
  public static function getLatestClientId() {
    return (self::getNewClientId() - 1);
  }
  
  /**
   * Return the latest complete client data from the database.
   * 
   * @return array
   */
  private function getClientDataById($id) {
    $db = new Database;
    return $db->hGetAll("internal:clients:" . $id);
  }
  
  /**
   * Return all client objects in an array.
   * 
   * @return array
   */
  public static function allClients() {
    $db = new Database;
    $all_client_data = array();
    try {
      foreach ($db->sMembers("internal:clients") as $id) {
        $all_client_data[$id] = new Client($id);
      }
      return $all_client_data;
    } catch (Throwable $e) {
      return;
    }
  }
  
  /**
   * Deletes the client records in the database.
   * They will persist in memory though in the client object you're calling from.
   * 
   * @return bool
   */
  public function delete() {
    $db = new Database;
    $db->sRemove("internal:clients", $this->id);
    return $db->delete("internal:clients:" . $this->id);
  }
  
  /**
   * Creates a new client record out of data provided in an array.
   * 
   * @param array $data An array of data to inject into the database.
   * @param int $id The new client ID to create.
   * 
   * @return bool
   */
  public static function add($data, $id) {
    $db = new Database;
    if (!Client::exists($id)) {
      $db->sAdd("internal:clients", $id);
      return $db->hMSet(("internal:clients:" . $id), $data);
    } else {
      throw new Error("Client ID #" . $id . " already exists.");
    }
  }

  public function update($data) {
    $db = new Database;
    return $db->hMSet(("internal:clients:" . $this->id), $data);
  }

}
