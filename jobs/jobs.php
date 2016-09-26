<?php
/**
 * jobs.php
 *
 * Abfrage der laufenden Jobs wieEVE-API
 *
 * @author	    Jan H. Krueger <jan@janhkrueger.de>
 * @copyright 	Jan H. Krueger
 * @package	   	EVE
 * @category	  EVE-BackEnd-Skript
 * @version		  1.0
 * @since		    12.01.2014
 * @license		  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 *
 * Letzte Aenderung:
 * 12.01.2014: Erstellung und Test
 * 20.01.2014: Umstellung auf mysqli
 * 19.06.2015: Anpassung auf IndustryJobs.xml
 */

error_reporting( E_ALL );
ini_set( 'display_errors', 1 );


include ("../includes/inc_connect.php");

# API-Adresse
$xmlfile = "https://api.eveonline.com/corp/IndustryJobs.xml.aspx?keyID=KEYID&vCode=VCODE";

# keyID
$keyid = "YOURAPIKEY";

# vCode
$vcode = "YOURAPIVCODE";

# API-String zusammenbauen
$xmlurl = str_replace("KEYID", $keyid, $xmlfile);
$xmlurl = str_replace("VCODE", $vcode, $xmlurl);


ob_start();

# Pruefen wann der letzte Call war
$sql = "SELECT cacheduntil FROM apicalls WHERE apiname='IndustryJobsHistory'";
foreach ($dbh->query($sql) as $info) {
    $cacheduntil = $info['cacheduntil'];
}

# Aktuelle UTC-Zeit ermitteln
date_default_timezone_set("UTC");
$timenow = date("Y-m-d H:i:s", time());

#--------------------------
# Nur wenn der Cache abgelaufen ist, darf neu gearbeitet werden


if ( $cacheduntil <= $timenow ) {
try {
	global $dbh;
        $sql = "DELETE FROM jobs";
        $stmt = $dbh->prepare($sql);
        $stmt->execute();

	# Daten abfragen
	$xml = simpleXML_load_file($xmlurl,"SimpleXMLElement",LIBXML_NOCDATA);


        # das neue CachedUntil ermitteln und in Datenbank schreiben
        foreach ($xml->cachedUntil as $cacheduntil)
        {
                $sql = "UPDATE apicalls SET cacheduntil = '$cacheduntil[0]', abgefragt=now() WHERE apiname='IndustryJobsHistory'";
                $stmt = $dbh->prepare($sql);
                if ( ! $stmt->execute() )
                        {
                            echo $sql;
                            print_r($stmt->errorInfo());
                            exit;
                        }
        } # Ende ForEach CachedUntil



if($xml ===  FALSE)
{
   //deal with error
   echo "FEHLER beim Einlesen";
}

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

				# jobid
				if ($attributeskey2 == "jobID") $jobID = $attributesvalue3;

				# installerID
				if ($attributeskey2 == "installerID") $installerID = $attributesvalue3;
				
				# installerName
				if ($attributeskey2 == "installerName") $installerName = $attributesvalue3;

				# facilityID
				if ($attributeskey2 == "facilityID") $facilityID = $attributesvalue3;				
				
				# solarSystemID
				# ehemals: installedInSolarSystemID
				if ($attributeskey2 == "solarSystemID") $solarSystemID = $attributesvalue3;
				
				# solarSystemName
				if ($attributeskey2 == "solarSystemName") $solarSystemName = $attributesvalue3;

				# stationID
				if ($attributeskey2 == "stationID") $stationID = $attributesvalue3;

				# activityID
				if ($attributeskey2 == "activityID") $activityID = $attributesvalue3;
				
				# blueprintID
				if ($attributeskey2 == "blueprintID") $blueprintID = $attributesvalue3;
				
				# blueprintTypeID
				if ($attributeskey2 == "blueprintTypeID") $blueprintTypeID = $attributesvalue3;
				
				# blueprintTypeName
				if ($attributeskey2 == "blueprintTypeName") $blueprintTypeName = $attributesvalue3;
				
				# blueprintLocationID
				if ($attributeskey2 == "blueprintLocationID") $blueprintLocationID = $attributesvalue3;
				
				# outputLocationID
				if ($attributeskey2 == "outputLocationID") $outputLocationID = $attributesvalue3;
				
				# runs -> wieviele Kopien
				if ($attributeskey2 == "runs") $runs = $attributesvalue3;
				
				# cost
				if ($attributeskey2 == "cost") $cost = $attributesvalue3;
							
				# teamID
				if ($attributeskey2 == "teamID") $teamID = $attributesvalue3;

				# licensedRuns -> Wieviele Runs auf einer Kopie
				if ($attributeskey2 == "licensedRuns") $licensedRuns = $attributesvalue3;
				
				# probability
				if ($attributeskey2 == "probability") $probability = $attributesvalue3;
				
				# productTypeID
				if ($attributeskey2 == "productTypeID") $productTypeID = $attributesvalue3;
				
				# productTypeName
				if ($attributeskey2 == "productTypeName") $productTypeName = $attributesvalue3;
				
				# status
				if ($attributeskey2 == "status") $status = $attributesvalue3;
				
				# timeInSeconds
				if ($attributeskey2 == "timeInSeconds") $timeInSeconds = $attributesvalue3;
				
				# startDate
				if ($attributeskey2 == "startDate") $startDate = $attributesvalue3;
				
				# endDate
				if ($attributeskey2 == "endDate") $endDate = $attributesvalue3;
				
				# pauseDate
				if ($attributeskey2 == "pauseDate") $pauseDate = $attributesvalue3;
				
				# completedDate
				if ($attributeskey2 == "completedDate") $completedDate = $attributesvalue3;
				
				# completedCharacterID
				if ($attributeskey2 == "completedCharacterID") $completedCharacterID = $attributesvalue3;
				
				# successfulRuns
				if ($attributeskey2 == "successfulRuns") $successfulRuns = $attributesvalue3;
			
			}

		  if ( $activityID != 8 )
		  {
			$sql = "insert into jobs ( facilityID, probability, teamID, status, blueprintTypeName, jobID, installerID, solarSystemID, stationID, activityID, productTypeID, outputLocationID, licensedRuns, blueprintID, timeInSeconds, endDate, completedDate, pauseDate, productTypeName, completedCharacterID, runs, cost, installerName, successfulRuns, startDate, solarSystemName, blueprintTypeID, blueprintLocationID) values ( $facilityID, '$probability', $teamID, $status, '$blueprintTypeName', $jobID, $installerID, $solarSystemID, $stationID, $activityID, $productTypeID, $outputLocationID, $licensedRuns, $blueprintID, $timeInSeconds, '$endDate', '$completedDate', '$pauseDate', '$productTypeName', $completedCharacterID, $runs, '$cost', '$installerName', $successfulRuns, '$startDate', '$solarSystemName', $blueprintTypeID, $blueprintLocationID) ON DUPLICATE KEY UPDATE status=$status, completedDate='$completedDate', endDate='$endDate', completedCharacterID=$completedCharacterID";
			$link->query($sql);
		}
		}
		}
	}
} catch (Exception $e) {
	ob_end_clean();
  echo 'Caught exception: ',  $e->getMessage(), "\n";
}
}
#-----

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
