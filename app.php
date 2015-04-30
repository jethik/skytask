<?php
//ini_set('memory_limit', '-1');
//echo ini_get('memory_limit');exit;
class sql
{
    public static $db = false;
    private $database_host ;
    private $database_user ;
    private $database_pass ;
    private $database_db ;
	private $database_type = 'mysql';
	
    function __construct($host,$user,$pass,$datb)
    {
		$this->database_host	=	$host;
		$this->database_user	=	$user;
		$this->database_pass	=	($pass) ? $pass : '';
		$this->database_db		=	$datb;
		
        if (self::$db === false) {
			$this->createDb();
			$this->createTable();
            $this->connect();
        }
    }
	private function createDb()
    {
		try {
			$dbh = new PDO("mysql:host=".$this->database_host, $this->database_user, $this->database_pass);
			$this->database_db = str_replace("`","``",$this->database_db);
			$dbh->exec("CREATE DATABASE IF NOT EXISTS `$this->database_db`;
				CREATE USER $this->database_user@$this->database_host IDENTIFIED BY $this->database_pass;
                GRANT ALL ON `$this->database_db`.* TO $this->database_user@$this->database_host;
                FLUSH PRIVILEGES;
			");// or die(print_r($dbh->errorInfo(), true));
			
		} catch (PDOException $e) {
			die("DB ERROR: ". $e->getMessage());
		}
		
	}
	private function createTable()
    {
		$table = "table1";
		try {
			 $dbh = new PDO($this->database_type . ":dbname=" . $this->database_db . ";host=" . $this->database_host,$this->database_user, $this->database_pass);
			 $dbh->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );//Error Handling
			 $sql ="CREATE table IF NOT EXISTS $table(
			 ID INT( 11 ) AUTO_INCREMENT PRIMARY KEY,
			 Name VARCHAR( 50 ) NOT NULL);" ;
			 $dbh->exec($sql);//or die(print_r($dbh->errorInfo(), true));
			 //print("Created $table Table.\n");

		} catch(PDOException $e) {
			echo $e->getMessage();
		}
		
	}
    private function connect()
    {
        $dsn = $this->database_type . ":dbname=" . $this->database_db . ";host=" . $this->database_host;
        try {
            self::$db = new PDO($dsn, $this->database_user, $this->database_pass, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''));
            self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            
            echo 'error';
			die(print_r(self::$db->errorInfo(), true));
        }
    }

}

$options = getopt("h:u:d:c:p:",array('host:','user:'));
var_dump($options);


$obj		=	new sql($options['host'],$options['user'],$options['p'],$options['d']);

$count		=	(int)$options['c'];
$insertArr	=	array();
$questArr	=	array();

for($i=1;$i<=$count;$i++){
	
	
	array_push($questArr,"(?)");
	array_push($insertArr,"fakeName".$i);
	
	
	if(($i%1000)==0){
		$query 	= "INSERT INTO table1 (Name) VALUES  ".implode(',',$questArr);
		$stmt 	= sql::$db->prepare($query,array(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true));

		try {
			$stmt->execute($insertArr);
		} catch (PDOException $e){
			echo $e->getMessage();
		}
		
		$stmt->closeCursor();
		$questArr	=	array();
		$insertArr	=	array();
		
	}
	
}


?>