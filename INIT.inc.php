<?php 

class INIT extends DBI {

	public function initialize(){
            try {
		 parent::exec("DROP DATABASE IF EXISTS ".$GLOBALS['dbname'].";");
		parent::exec("CREATE DATABASE IF NOT EXISTS ".$GLOBALS['dbname'].";");
                
		self::EinlesenStellenArten($GLOBALS['StellenArtenFile']);

                trigger_error("Datenbank ".$GLOBALS['dbname']." erfolgreich angelegt.", E_USER_NOTICE);
                
            } catch (Exception $e) {
        echo "<pre>" . $e . "</pre>";
    }
	}

	private function EinlesenStellenArten($defsfile){
		$d = new DISP;
		try {
			/*
			 * Anlegen der Tabellenstruktur
			 */
			$newtable = $d->exec('newtable');
			$newtable->getXmlFile($defsfile);
			$newtable->writeNewTable();
			unset($newtable);
			
			/*
			 * Daten aus XML-Datei in Tabelle einf�gen
			 */
			$dbi = new DBI;
			$data = GH::getXmlFile($defsfile);
			foreach ($data->HS as $HS){
				$sqlfieldlist = "";
				$sqlvalues = "";
				$sql="INSERT INTO ".$data->filetype." (";
					foreach ($data->sdawfieldset->field as $field){				
						$sqlfieldlist .= $field -> title . ",";				
					}
				$sqlvalues = "'".$HS->kurz."','".$HS->lang."','  '";
				$sql .= substr($sqlfieldlist,0,-1) .") VALUES (".$sqlvalues.");";
				$dbi->exec($sql);
				
				foreach ($HS->US as $US){
					$sqlfieldlist = "";
					$sqlvalues = "";
					$sql="INSERT INTO ".$data->filetype." (";
						foreach ($data->sdawfieldset->field as $field){				
							$sqlfieldlist .= $field -> title . ",";		
						}
					$sqlvalues = "'".$HS->kurz."','".$US->lang."','".$US->kurz."'";
					$sql .= substr($sqlfieldlist,0,-1) .") VALUES (".$sqlvalues.");";
					$dbi->exec($sql);
				}				
				
			}
			
			trigger_error("Tabelle ".$data->filetype." erfolgreich angelegt.", E_USER_NOTICE);
		} catch (Exception $e) {echo "<pre>" . $e . "</pre>";}
		
	}
}

?>