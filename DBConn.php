<?php
/** Author(s): Louis Taylor
 * Creation date: 12/23/2015
 */
$IDDir = dirname(__FILE__);
//require_once($IDDir . "/../incl/conf.php"); 

class DBConn
{
  private static $SQLHandle = null;
  private $SQLHost = "";
  private $SQLUser = "";
  private $SQLPassword = "";
  private $DBName = "DatabaseName";
  private static $DBObject = null;
  private $SQLCharset = "utf8mb4";
  


  #public static function openDB($dbname, $user = PET_User, $password = PET_Password, $host = PET_Server)
  public static function OpenDB($dbname = "stitch", $user = "XXXX", $password = "XXXX", $host = "localhost")
  {
    $obj = new self($dbname, $host, $user, $password);
    
    if ($obj->SQLConnect())
    {
	  $obj->SetDBCharset();  // Please note:  This method has been added here to propogate the specified database character set everywhere throughout the application (plugin & report database connections)    
      return null;
    }
    $obj->UseDB($dbname);

    return $obj;
  }

  public function __construct($dbname, $host, $user, $password)
  {
    $this->DBName = $dbname;
    $this->SQLHost = $host;
    $this->SQLUser = $user;
    $this->SQLPassword = $password;
  }

  public function SetDBCharset()
  {  #  Added method for specifying the character set of database connection
      mysql_set_charset(self::$SQLCharset, self::$DBObject);
  }

  public static function SingleDB($dbname)
  {  #  Added method for providing singleton of database connection
    if (!is_object(self::$DBObject))
    {
      self::$DBObject = DBConn::OpenDB($dbname);
    }

    return self::$DBObject;
  }

  public function SQLConnect()
  {
    try {
      self::$SQLHandle = new PDO (
        "mysql:host=" . $this->SQLHost . ";dbname=" . $this->DBName . "",
        $this->SQLUser,
        $this->SQLPassword,
        array(PDO::ATTR_PERSISTENT => true)      );
		
    }
    catch (PDOException $e) {
      echo 'Connection failed: ' . $e->getMessage();
      exit (1);
    }
    self::$SQLHandle->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
  }

  public function UseDB($dbname)
  {
    if (self::$SQLHandle)
    {
      $this->DBName = $dbname;
	  self::$SQLHandle->query("use " . $this->DBName);
    }
  }

  public function beginTransactionSQL()
  {
    if (self::$SQLHandle)
    {
      self::$SQLHandle->beginTransaction();
	}
  }

  public function PrepareSQL($sqlcmd)
  {
    if (self::$SQLHandle)
    {
      try {
		$sthselect = self::$SQLHandle->prepare($sqlcmd);
		return $sthselect;
      }
      catch (PDOException $e) {
				echo 'Invalid query: ' . "\n     " . $e->getMessage() . "\n     " . $sqlcmd . "\n";
	  }
    }

    return null;
  }

  public function ExecuteSQL($sthselect, $argArray = array())
  {
    if (self::$SQLHandle)
    {
      $sthselect->execute($argArray);
	}
  }

  public function CommitSQL()
  {
    if (self::$SQLHandle->inTransaction())
    {
      self::$SQLHandle->commit();
	}
  }

  public function RollbackSQL()
  {
    if (self::$SQLHandle->inTransaction())
    {
      self::$SQLHandle->Rollback();
	}
  }


  public function GetSQLResultSet($sthselect)
  {
    $results = null;
    if(!($results = $sthselect->fetchAll(PDO::FETCH_ASSOC)))  {
        $results = "could not retrieve any records";

    }

	return $results;  
  }


  public function SQLAffectedRows($sthselect)
  {
    $affectedrows = null;

    if ($sthselect)
    {
      $affectedrows = $sthselect->rowCount(); 
    }

    return $affectedrows;
  }

  public function LastInsertId()
  {
    if (self::$SQLHandle)
    {
      return self::$SQLHandle->lastInsertId();
    }
  }
}

