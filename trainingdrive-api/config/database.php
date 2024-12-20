<?php
    //meta data
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=utf-8");
    header("Access-Control-Allow-Methods: POST, GET, PATCH, OPTIONS");
    header("Access-Control-Max-Age: 3600");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Auth-User");

    if ($_SERVER['REQUEST_METHOD'] == "OPTIONS") {
      header('Access-Control-Allow-Origin: *');
      header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Auth-User");
      header("HTTP/1.1 200 OK");
      die();
    }

    define("SERVER", "localhost");
    define("DBASE", "drivingschool_db"); //enter your own databasename
    define("USER", "root");
    define("PWORD", "");
    define("TOKEN_KEY","Susing_Token");
    define("SECRET_KEY", "Se_cr_et");

    class Connection {
      protected $connectionString = "mysql:host=" . SERVER . ";dbname=" .DBASE. ";charset=utf8";
      protected $options = [
        \PDO::ATTR_ERRMODE =>\PDO::ERRMODE_EXCEPTION,  
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        \PDO::ATTR_EMULATE_PREPARES => false
      ];

      public function connect(){
        return new \PDO($this->connectionString, USER, PWORD, $this->options);
      }
  }
?>
