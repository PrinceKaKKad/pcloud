<?php
Class Database{
 
  private $server = "mysql:host=localhost;dbname=pcloud";
  private $username = "root";
  private $password = "";
  private $options  = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,);
  protected $pdo;
  
  public function open(){
    try{
      $this->conn = new PDO($this->server, $this->username, $this->password, $this->options);
      return $this->conn;
    }
    catch (PDOException $e){
      echo "There is some problem in connection: " . $e->getMessage();
    }
 
    }
 
  public function close(){
      $this->conn = null;
  }
 
}

$db = new Database();
$pdo = $db->open();
if (!$pdo) {
  die("Connection failed: " . $db->error());
}
?>
