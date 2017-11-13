<?php

// Base file for other webfiles to import.
// Instantiates connections, globals and configuration.

/**
 * The *TTS_Framework* class, inherited by all subclasses for access to important constants.
 * 
 * This class defines multiple important constants.
 */
  class TTS_Framework {
    const VERSION = "0.2";
    const DIRECTORY = "";
    const BASE_PATH = "/var/www/tomtecinternal";
    const DEBUG = true;
    const SITE_NAME = "TomTec Internal";
    
    /**
     * Initialiser function
     */
    public static function initialise() {
      session_start();
    }
    
    /**
     * Chainloader
     *
     * Attempt to load in (require) every PHP file within the assets/php directory
     */
    public static function chainload() {
      require_once self::BASE_PATH . "/vendor/autoload.php";
      foreach (scandir(dirname(__FILE__)) as $filename) {
        $path = dirname(__FILE__) . "/" . $filename;
        if (is_file($path) && substr_compare($path, ".php", strlen($path) - strlen(".php"), strlen(".php")) === 0) {
          require_once $path;
        }
      }
    }
  }
  
  TTS_Framework::initialise();
  TTS_Framework::chainload();
  
  use Tracy\Debugger;
  
  if (TTS_Framework::DEBUG) {
    // Enable warnings/messages/debug stuff
    Debugger::enable(Debugger::DEVELOPMENT);
    ini_set("display_errors", 1);
    ini_set("display_startup_errors", 1);
    ini_set("html_errors", 1);
    error_reporting(E_ALL);
    foreach ($_GET as $item => $value) {
      switch ($item) {
        case "phpinfo":
          exit(phpinfo());
        case "plaintext":
          header("Content-Type: text/plain");
      }
    }
  }
