<?php
  class Database extends Redis {
    private $redisPassword = "REDACTED";

    public function __construct() {
      $this->connect("127.0.0.1", 6379);
      $this->auth($this->redisPassword);
      $this->select(1); // internal db
    }
  }
