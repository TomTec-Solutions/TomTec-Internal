<?php
  class SMS extends TTS_Framework {
    protected $message, $destination, $curl;
    const apiAuthentication = array("REDACTED", "REDACTED");
    const apiUrl = "https://api.smsbroadcast.com.au/api-adv.php";
    const sourceName = "TomTec";


    public function __construct($destination) {
      $this->destination = $destination;
      $this->curl = curl_init(self::apiUrl);
      curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($this->curl, CURLOPT_POST, true);
    }

    public function __destruct() {
      curl_close($this->curl);
    }

    public function setMessage($message) {
      $this->message = $message;
    }

    public function send() {
      if (!$this->message || $this->message == "") {
        throw new Error("Please set a message first.");
      }
      curl_setopt($this->curl, CURLOPT_POSTFIELDS, $this->getPostMessageContents());
      $output = curl_exec($this->curl);
      $this->handleOutput($output);
    }

    public static function getCreditBalance() {
      $curl = curl_init(self::apiUrl);
      curl_setopt($curl, CURLOPT_POST, true);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_POSTFIELDS, "username=" . rawurlencode(self::apiAuthentication[0]) . "&" .
                                             "password=" . rawurlencode(self::apiAuthentication[1]) . "&" .
                                             "action=balance");
      $output = curl_exec($curl);
      curl_close($curl);
      $message_data = explode(":", $output);
      if ($message_data[0] == "OK") {
        return $message_data[1];
      } else {
        throw new Error($message_data[1]);
      }
    }

    private function getPostMessageContents() {
      return "username=" . rawurlencode(self::apiAuthentication[0]) . "&" .
             "password=" . rawurlencode(self::apiAuthentication[1]) . "&" .
             "to="       . rawurlencode($this->destination)         . "&" .
             "from="     . rawurlencode(self::sourceName)           . "&" .
             "message="  . rawurlencode($this->message);
    }

    private function handleOutput($output) {
      $lines = explode("\n", $output);
      foreach ($lines as $line) {
        $message_data = explode(":", $line);
        if ($message_data[0] == "OK") {
          return $message_data[2];
        } else {
          throw new Error($message_data[1]);
        }
      }
    }
  }
