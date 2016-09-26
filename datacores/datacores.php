<?php
/**
 * datacores.php
 *
 * Ermittelt die DataCore-Produktion der Corp
 *
 * @author	    Jan H. Krueger <jan@janhkrueger.de>
 * @copyright 	Jan H. Krueger
 * @package	   	EVE
 * @category	  EVE-BackEnd-Skript
 * @version		  1.0
 * @since		    09.02.2014
 * @license		  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 *
 * Letzte Aenderung:
 * 09.02.2014: Erstellung und Test
 */
error_reporting(E_ALL);
include ("../includes/inc_connect.php");

# CorpID aktuell hart hinterlegt
$corpID = "YOURCORPID";

# Datacore Tabelle leeren
$dbh->beginTransaction();
$sql = "DELETE FROM datacores";
$stmt = $dbh->prepare($sql);
$stmt->execute();
$dbh->commit();

# Hier alle Chars dieser Corp ermitteln für welche API-Daten 
# hinterlegt sind.
$sql = "SELECT * from chars WHERE corpID=:corpid AND keyid IS NOT NULL AND vcode IS NOT NULL AND inCorp = 'y' ORDER BY name";
$stmt = $dbh->prepare($sql);
$stmt->bindParam(':corpid', $corpID, PDO::PARAM_INT);
if ( ! $stmt->execute() )
	{
		echo $sql;
		print_r($stmt->errorInfo());
		exit;
	}

# Jeden Char der Corp mit gültiger API bearbeiten
while ($info = $stmt->fetch(PDO::FETCH_OBJ) )
{	
	# API-Adresse
	$xmlfile = "https://api.eveonline.com/char/Research.xml.aspx?keyID=KEYID&vCode=VCODE&characterID=CHARACTERID";
	
	# keyID
	$keyid = $info->keyid;
	# vCode
	$vcode = $info->vcode;
	# characterID
	$characterID = $info->charID;
	# name
	$charname = $info->name;


	# API-String zusammenbauen
	$xmlurl = str_replace("KEYID", $keyid, $xmlfile);
	$xmlurl = str_replace("VCODE", $vcode, $xmlurl);
	$xmlurl = str_replace("CHARACTERID", $characterID, $xmlurl);
	#$xmlurl = "Research.xml.aspx";
	
	# Daten abfragen
	try {
		$xml = simpleXML_load_file($xmlurl,"SimpleXMLElement",LIBXML_NOCDATA);
	} catch (Exception $e) {
		# Hier die Fehlerbehandlung bei einem falschen Abbruch der API
	  echo 'Caught exception: ',  $e->getMessage(), "\n";
	}
	
	# Daten extrahieren
	try {
		# Jeden Eintrag im XML durcharbeiten
		foreach($xml as $key0 => $value)
		{
			foreach($value as $key => $value2)
			{
				# Ab hier die Job-Eintraege
				foreach($value2 as $key2 => $value3)
				{
					foreach($value3->attributes() as $attributeskey2 => $attributesvalue3)
					{
		
						# Alle Werte auslesen
		
						# researchStartDate
						if ($attributeskey2 == "researchStartDate") $researchStartDate = $attributesvalue3;
		
						# pointsPerDay
						if ($attributeskey2 == "pointsPerDay") $pointsPerDay = $attributesvalue3;
						
						# remainderPoints
						if ($attributeskey2 == "remainderPoints") $remainderPoints = $attributesvalue3;
						
						# skillTypeID
						if ($attributeskey2 == "skillTypeID") $skillTypeID = $attributesvalue3;
						
						# skillTypeID
						if ($attributeskey2 == "agentID") $agentID = $attributesvalue3;						
						
		
					} # Ende eines Eintrages
					
					
					# Nun prüfen ob der Char für diesen Agent bereits einen Eintrag hat
					$bla = -99;
					$sql = "SELECT count(charID) FROM datacores WHERE charID=:charID AND agentID=:agentID";
					$stmtcount = $dbh->prepare($sql);
					$stmtcount->bindParam(':charID', $characterID, PDO::PARAM_INT);
					$stmtcount->bindParam(':agentID', $agentID, PDO::PARAM_INT);
					if ( ! $stmtcount->execute() )
					{
					    echo $sql;
						  print_r($stmtcount->errorInfo());
						 	exit;
					}
					else
					{
						$bla = $stmtcount->fetch();
					}						
					
					# Wenn nein, einfügen
					if ( $bla[0] == 0 ) {
						$dbh->beginTransaction();
						$sql = "INSERT INTO datacores VALUES (:charID, :researchStartDate, :pointsPerDay, :remainderPoints, :skillTypeID, :agentID)";
						$stmtinsert = $dbh->prepare($sql);
						$stmtinsert->bindParam(':charID', $characterID, PDO::PARAM_INT);
						$stmtinsert->bindParam(':skillTypeID', $skillTypeID, PDO::PARAM_INT);
						$stmtinsert->bindParam(':remainderPoints', $remainderPoints, PDO::PARAM_INT);
						$stmtinsert->bindParam(':pointsPerDay', $pointsPerDay, PDO::PARAM_INT);
						$stmtinsert->bindParam(':researchStartDate', $researchStartDate, PDO::PARAM_INT);
					  $stmtinsert->bindParam(':agentID', $agentID, PDO::PARAM_INT);
					
					
						if ( ! $stmtinsert->execute() )
						{
						    echo $sql;
						    print_r($stmtinsert->errorInfo());
						    exit;
						}
						$dbh->commit();
					} # Ende IF
						
				}
			}
			
			
		} # Ende ForEach
		
	} catch (Exception $e) {
	  echo 'Fehler bei Datenextraktion: ',  $e->getMessage(), "\n";
	} # Ende Catch
	
} # Ende While


# MySQL Ressourcen freigeben und Verbindung beenden
include ("../includes/close.php");

# Sonstige Ressourcen freigeben

unset($count);
unset($xmlfile);
unset($waittime);
unset($i);
unset($link);
unset($data);
unset($apiurl);
unset($all_api_call);
unset($info);
unset($preis);
unset($updatesql);
?>
