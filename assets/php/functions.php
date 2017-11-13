<?php
  class Functions extends TTS_Framework { // original functions made into a class
    // logging function
    public static function logEvent($event, $logfile) {
      if (isset($_SERVER["REMOTE_ADDR"])) { $logString = ("[" . date("d-m-Y H:i:s") . "/" . $_SERVER["REMOTE_ADDR"] . "]: " . $event . "\n"); }
      else { $logString = ("[" . date("d-m-Y H:i:s") . "/" . "Console" . "]: " . $event . "\n"); }
      $logFilePath = (TTS_Framework::BASE_PATH . "/assets/logs/". $logfile . ".txt");
      file_put_contents($logFilePath, $logString, FILE_APPEND | LOCK_EX);
    }

    // function to safely get $_POST array items/vars without raising an exception
    public static function getPostVarSafely($postvar) {
      if (isset($_POST[$postvar])) {
        return htmlspecialchars($_POST[$postvar]);
      } else {
        return "";
      }
    }

    public static function getWebPath() {
      if (isset($_SERVER["REQUEST_SCHEME"]) && isset($_SERVER["HTTP_HOST"])) {
        return ($_SERVER["REQUEST_SCHEME"] . "://" . $_SERVER["HTTP_HOST"]) . TTS_Framework::DIRECTORY;
      } else {
        $_SERVER["REMOTE_ADDR"] = "Console";
        return null;
      }
    }
  
    /**
     * @param string $filename
     */
    public static function loadPagePhp(string $filename) {
      global $rend;
      $path = (parent::BASE_PATH . "/assets/php/pages/" . $filename);
      if (is_readable($path)) {
        include $path;
      }
    }
    
    /**
     * @param string $filename
     *
     * @return bool|string
     */
    public static function doesJavaScriptFileExist(string $filename) {
      if (file_exists(parent::BASE_PATH . "/assets/pageassets/javascript/" . $filename)) {
        return $filename;
      }
    
      return false;
    }
  
    /**
     * @param string $filename
     *
     * @return bool|string
     */
    public static function doesStylesheetFileExist(string $filename) {
      if (file_exists(parent::BASE_PATH . "/assets/pageassets/stylesheets/" . $filename)) {
        return $filename;
      }
    
      return false;
    }
    
    public static function getCurrentUrl($text = false) {
      if (isset($_GET["url"])) {
        if ($_GET["url"] == "index.php") {
          $url = "dashboard";
        } else {
          $url = $_GET["url"];
        }
      } else {
        $url = "dashboard";
      }

      if (substr($url, -1) == "/") {
        $url = rtrim($url, "/"); // remove trailing slash
      }

      if ($text) {
        return $url . $text;
      } else {
        return $url;
      }
    }

    // returns true if a post request was made
    public static function wasRequestPost() {
      if ($_SERVER["REQUEST_METHOD"] == "POST") {
        return true;
      } else {
        return false;
      }
    }

    public static function getDeviceType($type = NULL) { // credit: http://stackoverflow.com/a/6524325
      $user_agent = strtolower($_SERVER["HTTP_USER_AGENT"]);
      if ($type == "bot") {
        if (preg_match("/googlebot|adsbot|yahooseeker|yahoobot|msnbot|watchmouse|pingdom\.com|feedfetcher-google/", $user_agent)) {
          return true;
        }
      } else if ($type == "browser") {
        if (preg_match("/mozilla\/|opera\//", $user_agent)) {
          return true;
        }
      } else if ($type == "mobile") {
        if (preg_match("/phone|iphone|itouch|ipod|symbian|android|htc_|htc-|palmos|blackberry|opera mini|iemobile|windows ce|nokia|fennec|hiptop|kindle|mot |mot-|webos\/|samsung|sonyericsson|^sie-|nintendo|mobile|pda;|avantgo|eudoraweb|minimo|netfront|brew|teleca|lg;|lge |wap;| wap/", $user_agent)) {
          return true;
        }
      }
      return false;
    }

    public static function doesSessionExist() {
      if (session_id() == "" || !isset($_SESSION)) {
        return false;
      } return true;
    }
    
    public static function map($value, $low1, $high1, $low2, $high2) {
      return ($value / ($high1 - $low1)) * ($high2 - $low2) + $low2;
    }
  }
