<?php
/**
 * index.php
 *
 * Übersicht über die von der Corp generierten DataCores
 *
 * @author	    Jan H. Krueger <jan@janhkrueger.de>
 * @copyright 	Jan H. Krueger
 * @package	   	EVE
 * @category	  EVE-FrontEnd-Skript
 * @version		  1.0
 * @since		    26.01.2014
 * @license		  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 *
 * Letzte Aenderung:
 * 26.01.2014: Erstellung und Test
 * 09.02.2014: Umstellung auf Auslesen der Daten aus der Datenbank, nicht mehr per API
 */
 
 
#x3 for ship datacores
#x2 for weapon datacores (high energy, laser, rocket, etc.)
#x1 for all others
#
#base is 50 RP for a datacore.

error_reporting(E_ALL);
include ("../includes/inc_connect.php");

# CorpID aktuell hart hinterlegt
$corpID = "YOURCORPID";

function getDatacoreCost($datacoreTypeID){
	$datacoreCost = array(
		20171 => 50,
		20172 => 150,
		20410 => 150,
		20411 => 50,
		20412 => 100,
		20413 => 100,
		20414 => 50,
		20415 => 50,
		20416 => 50,
		20417 => 50,
		20418 => 50,
		20419 => 100,
		20420 => 50,
		20421 => 150,
		20423 => 100,
		20424 => 50,
		25887 => 150	
	);
	return $datacoreCost[$datacoreTypeID];
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
       <title>RASI - Datacore Übersicht</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <link rel="stylesheet" type="text/css" href="../css/bpo.css">
        
        </script>

    </head>
    <body id="body">

<?php
# Menü einbinden
include ("../includes/menu.php");
?>

<div class="tableheader" align="center">
  <div class="tdname">Character</div>
  <div class="tdme">RP / d</div>
  <div class="tddatacorecurrentrp">RP aktuell</div>
  <div class="tdname">Datacoretyp</div>
  <div class="tddatacorecostpercore">RP / C</div>
  <div class="tddatacoreCoresperMonth">C / M</div>
  <div class="tdname">ISK / Monat</div>
</div>

<?php

# Hier alle Chars dieser Corp ermitteln für welche API-Daten 
# hinterlegt sind.
$sql = "SELECT d.*, c.name from datacores d, chars c WHERE c.charID = d.charID ORDER BY c.name";
$data = $link->query($sql);

# Jeden Char der Corp mit gültiger API bearbeiten
while($info = $data->fetch_assoc())
{	

	$researchStartDate = $info['researchStartDate'];
	$pointsPerDay = $info['pointsPerDay'];
	$remainderPoints = $info['remainderPoints'];
	$skillTypeID = $info['skillTypeID'];
	$charname = $info['name'];
						
					echo '<div class="table">' . "\n";
					# Charactername
					echo '<div class="tdname">'.$charname.'</div>' . "\n";
					
					# RP / day
					$style = "tdme";
					$rpPerDay = round($pointsPerDay,2);
					echo '<div class="' . $style . '">' . $rpPerDay . '</div>' . "\n";	
					
					# RP gesamt
					# Definieren das die weiteren Angaben in UTC sind.
					date_default_timezone_set ( "UTC" );
					$now = date("Y-m-d H:i:s");
					$newformat = date('Y-m-d H:i:s',strtotime($researchStartDate));
					$datetime = new DateTime($newformat);
					$datetimenow = new DateTime($now);
					$interval = $datetime->diff($datetimenow);
					$tage =  $interval->format('%a');
					$a = $tage * $pointsPerDay;
					$ende = $a + $remainderPoints;
					
					$style = "tddatacorecurrentrp";
					echo '<div class="' . $style . '">' . $ende . '</div>' . "\n";
					
					# SkillName des DataCores ermitteln					
					$sql = "SELECT typeName FROM invTypes WHERE typeID = ".$skillTypeID." LIMIT 1";
					$dataDataCore = $link->query($sql);
					$infoDataCore = $dataDataCore->fetch_assoc();
					$dataCoreSkillName = $infoDataCore['typeName'];
					
					# DataCoreID ermitteln
					$sql = "select typeID FROM invTypes WHERE typeName = 'Datacore - ".$dataCoreSkillName."'";
					$dataDataCoreID = $link->query($sql);
					$infoDataCore = $dataDataCoreID->fetch_assoc();
					$dataCoreID = $infoDataCore['typeID'];		
				
					# "Datacore - " + Skillname
					$style = "tdname";
					echo '<div class="' . $style . '">' . $dataCoreSkillName . '</div>' . "\n";
					
					# Kosten pro Core
					$style = "tddatacorecostpercore";
					$dataCoreCost = getDatacoreCost($dataCoreID);
					echo '<div class="' . $style . '">'.$dataCoreCost.'</div>' . "\n";
					
					
					# Punkte pro Tag *30 / Corekosten
					$dateCoresProMonat = round($rpPerDay * 30 / $dataCoreCost, 0, PHP_ROUND_HALF_DOWN);
					$style = "tddatacoreCoresperMonth";
					echo '<div class="' . $style . '">'.$dateCoresProMonat.'</div>' . "\n";
					
					# Cores pro Monat * Erlös von eve - central
					# System 30002187 = Amarr
					
					
					$eveCentralAPI = "http://api.eve-central.com/api/marketstat?minQ=1&typeid=".$dataCoreID."&usesystem=30002187";
					
					$eveCentralCost = 95002;
					
					$fullcost = $dateCoresProMonat * $eveCentralCost;
					$style = "tdname";
  				echo '<div class="' . $style . '">'.number_format($fullcost, 0, 1000, '.').'</div>' . "\n";
  			
					
					echo '</div>' . "\n";

	
}


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
