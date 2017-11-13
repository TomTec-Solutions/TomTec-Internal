<?php
  class User extends TTS_Framework {
    public $username;
    public $email;
    public $fullname;
    public $last_login;
    public $permission;
    public $password;
    public $notepad;


    public function __construct($username) {
      if (self::exists($username)) {
        $user = $this->getUserByUserName($username);
        $this->username = $username;
        $this->fullname = $user["name"];
        $this->email = $user["email"];
        $this->password = $user["password"];
        $this->permission = $user["permission"];
        if (array_key_exists("last_login", $user)) {
          $this->last_login = $user["last_login"];
        }
        if (array_key_exists("notepad", $user)) {
          $this->notepad = $user["notepad"];
        }
      } else {
        throw new Error("User " . $username . " does not exist.");
      }
    }

    public static function exists($username) {
      $db = new Database;
      return $db->sIsMember("internal:users", $username);
    }

    private function getUserByUserName($username) {
      $db = new Database;
      return $db->hGetAll("internal:users:" . $username);
    }

    public function delete() {
      $db = new Database;
      $db->sRemove("internal:users", $this->username);
      return $db->delete("internal:users:" . $this->username);
    }

    public static function add($data, $username) {
      $db = new Database;
      if (!User::exists($username)) {
        $db->sAdd("internal:users", $username);
        return $db->hMSet(("internal:users:" . $username), $data);
      } else {
        throw new Error("User name " . $username . " already exists.");
      }
    }

    public function update($data) {
      $db = new Database;
      return $db->hMSet(("internal:users:" . $this->username), $data);
    }
  
    public function setNotepad($data) {
      $db = new Database;
      return $db->hSet("internal:users:" . $this->username, "notepad", $data);
    }
  
    public static function allUsers() {
      $db = new Database;
      $users = array();
      try {
        foreach ($db->sMembers("internal:users") as $username) {
          $users[$username] = new User($username);
        }
        return $users;
      } catch (Throwable $e) {
        return $e->getMessage();
      }
    }
  }
