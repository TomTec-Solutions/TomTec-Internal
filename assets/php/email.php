<?php
  class Email extends PHPMailer {
    public function __construct() {
      $useTurbo = false;
      //$this->SMTPDebug = 2;
      //$this->Debugoutput = "text";
      $this->IsSMTP();
      $this->SMTPAuth = true;
      if ($useTurbo) {
        $this->Host = "pro.turbo-smtp.com";
        $this->Port = 25;
        $this->Username = "REDACTED";
        $this->Password = "REDACTED";
      } else {
        $this->Host = "mail.noip.com";
        $this->Port = 587;
        $this->SMTPSecure = "tls";
        $this->Username = "REDACTED";
        $this->Password = "REDACTED";
      }
      $this->SetFrom("system@tomtecsolutions.com", "TomTec Solutions");
      $this->AddCC("thomas@tomtecsolutions.com", "Thomas Jones");
      return $this;
    }
  }
