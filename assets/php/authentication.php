<?php

/**
 * The *Authentication* class, responsible for authenticating users, validating passwords, and retrieving current user data.
 * 
 * Authentication requires the Database and User classes to function.
 */
class Authentication extends TTS_Framework {

  /**
   * Checks if a user is currently logged in.
   *
   * This will check if the _UserData_ session key is set, and make sure that sessions are in existance in the first place.
   *
   * @return bool
   */
  final public static function isLoggedIn() {
    if (isset($_SESSION["UserData"]) && Functions::doesSessionExist()) {
      return true;
    } return false;
  }

  /**
   * Logs a user in.
   *
   * This function will validate the password of the user against the user record in the database, if the password matches and the username exists, it will log the user in and set the last_login timestamp.
   * 
   * @param string $username The username of the user to log in with.
   * @param string $password The password of the user to log in with. Do not supply a SHA-256 hash as this method will hash the plaintext password you provide to it automatically.
   * 
   * @return bool
   */
  public function login($username, $password) {
    if (!self::isLoggedIn()) {
      if (User::exists($username)) {
        if ($this->validatePassword($username, $password)) {
          $user = new User($username);
          $db = new Database;
          $_SESSION["UserData"] = array(
              "name" => $user->fullname,
              "email" => $user->email,
              "username" => $user->username,
              "timestamp" => time(),
          );
          $db->hSet("internal:users:" . $_SESSION["UserData"]["username"], "last_login", $_SESSION["UserData"]["timestamp"]);
          return true;
        } else {
          throw new Error("Password for user is incorrect.");
        }
      } else {
        throw new Error("Username does not exist.");
      }
    } else {
      throw new Error("Cannot log in when there is a user already logged in.");
    }
  }

  /**
   * Logs a user out.
   *
   * This _will_ throw an error if you try to logout while no user is currently logged in.
   * 
   * @return void
   */
  public static function logout() {
    if (self::isLoggedIn()) {
      session_unset();
      session_destroy();
    } else {
      throw new Error("Cannot log out when there is no user logged in.");
    }
  }

  /**
   * Returns the currently logged-in user's username.
   *
   * This _will_ throw an error if you try to get the username while no user is logged in.
   * 
   * @return string
   */
  public static function getCurrentUserName() {
    if (self::isLoggedIn()) {
      return $_SESSION["UserData"]["username"];
    } else {
      throw new Error("Cannot get current user data when there is no user logged in.");
    }
  }

  /**
   * Validates a provided password against the stored password for a user.
   *
   * This will check to ensure the username exists, and if so will attempt to validate the plaintext password provided against the stored hash.
   * Returns true on success.
   * 
   * @param string $username The username to check against.
   * @param string $password The password to check against.
   * 
   * @return bool
   */
  private function validatePassword($username, $password) {
    if (User::exists($username)) {
      $user = new User($username);
      if (hash("sha256", $password) == $user->password) {
        return true;
      }
    }
    return false;
  }

}
