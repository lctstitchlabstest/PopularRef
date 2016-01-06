<?php
$IDDir = dirname(__FILE__);
//include_once($IDDir . "/../incl/conf.php"); 
include_once($IDDir . "/DBConn.php");
// bind SOAP/Client.php -> path of the php file

class PetData extends DBConn {
	/**********************************************************************************************************************************************************/
	//  Please note:  This class is specific to the Apple Homework Assignment and provides the necessary specification for applying the DBConn abstraction to the specific problems.
	/**********************************************************************************************************************************************************/
    private static $petArray;
    private static $PET_Database;  //  This is set when an object to this class is instantiated and is passed in whenever a database call is made
	
	public function __construct($database) {
		  
	    PetData::SingleDB($database);
		PetData::$PET_Database = $database; 
	}
  
	public static function get_pet_rows($DB_PET_TABLE_ROW, $sql = '') {
		//  This is NOT completely implemented and not used
		$dbh = DBConn::SingleDB(PetData::$PET_Database);
		
		$sql = "
			SELECT * 
			  FROM Pets" . $sql . "";
			  
		$sthselect = $dbh->PrepareSQL($sql);
		$dbh->ExecuteSQL($sthselect, array());

		if(!($results = $dbh->GetSQLResultSet($sthselect)))
			$results = "could not retrieve records";
        //echo "      \n       " . $sql . "      \n       ";

		return $results;  

	}


	public static function delete_pet_rows($DB_PET_TABLE_ROW, $sql = '') {
		//  This is NOT completely implemented and not used
		$dbh = DBConn::SingleDB(PetData::$PET_Database);
		
		$sql = "
			DELETE 
			  FROM Pets" . $sql . "";
			  
		$sthselect = $dbh->PrepareSQL($sql);
		$dbh->ExecuteSQL($sthselect, array());

		if(!($results = $dbh->GetSQLResultSet($sthselect)))
			$results = "could not retrieve records";
        //echo "      \n       " . $sql . "      \n       ";

		return $results;  

	}
	
    public static function set_pet_array()  {
		//  This is set whenever an insert is made so that a data structure is available to translate between Pet Type IDs and Pet Types for inserting into the Pets table
		$petTypeResults = PetData::get_pet_types();
		
		foreach($petTypeResults as $PetRow)  {
			
		    PetData::$petArray[$PetRow['NameType']] = $PetRow['PId'];	
		}
	}
	
	public static function InsertPets($DB_PET_TYPE, $insertObject)
{ //  This is used in both Parts I and II
    $piece1 = '(';
	$piece2 = '';
    if (is_object($insertObject))
    {
		PetData::set_pet_array();
		$petName = $insertObject->getName();
		$petAge = $insertObject->getAge();
		$petFavoriteFood = $insertObject->getFavoriteFood();
		
		if(isset($petName)) {
			$piece1 .= 'PetName,';
			$piece2 .= "'" . $petName . "'";
            $piece2 .= ',';
		}

		
		if(isset($petFavoriteFood)) {
			$piece1 .= 'PetFavoriteFood,';
			$piece2 .= "'" . $petFavoriteFood . "'";
            $piece2 .= ',';
		}
		
		$piece1 .= 'PetAge,';
		$piece2 .= $petAge;
		$piece2 .= ',';
		
		if(isset($DB_PET_TYPE)) {
			$piece1 .= 'PetTypeId';
			$piece2 .= PetData::$petArray[$DB_PET_TYPE];
		}
        $piece2 .= ')';
		
		$piece1 .= ') VALUES (';

        $insertPiece = $piece1 . $piece2;
		
 		$dbh = DBConn::SingleDB(PetData::$PET_Database);
 		
        $sql = " INSERT INTO Pets" .  $insertPiece."";
		
		$sthselect = $dbh->PrepareSQL($sql);
		$dbh->ExecuteSQL($sthselect, array());
		$recordID = $dbh->LastInsertId();
		if(!(empty($recordID)))
			$results = "ID:  " . $recordID . PHP_EOL . "One record for pets has been successfully inserted.";
		else
			$results = false;     // "could not insert pet record";

		return $results;  
	}
	else  {
		
        return null;     // "There are no pets being inserted";
		
	}
	
}




	public static function get_table_dump() {
		//  This is used in Part II to check persistence
		$dbh = DBConn::SingleDB(PetData::$PET_Database);
		
		try {
			//$dbh->beginTransaction();
			$sql = "
				SELECT * 
				  FROM Pets";
				  
			$sthselect = $dbh->PrepareSQL($sql);
			$dbh->ExecuteSQL($sthselect, array());

			if(!($results = $dbh->GetSQLResultSet($sthselect)))
				$results = "could not retrieve records";

			return $results;  
		} catch (PDOException $e) {
			//$dbh->rollBackSQL($sthselect);
            die ($e->getMessage() . PHP_EOL); 
		}
	}

	public static function get_pet_types() {
		$dbh = DBConn::SingleDB(PetData::$PET_Database);
		
		try {
			//$dbh->beginTransaction();
			$sql = "
				SELECT * 
				  FROM PetType";
				  
			$sthselect = $dbh->PrepareSQL($sql);
			$dbh->ExecuteSQL($sthselect, array());
			if(!($results = $dbh->GetSQLResultSet($sthselect)))

				$results = "could not retrieve records";

			return $results;  
		} catch (PDOException $e) {
			//$dbh->rollBackSQL($sthselect);
            die ($e->getMessage() . PHP_EOL); 
		}
	}

}
