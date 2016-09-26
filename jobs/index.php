<?php
/**
 * index.php
 *
 * Anzeige der aktuell laufenden Forschungsjobs
 * Nutzt http://www.php.net/manual/en/class.datetimeinterface.php
 * http://www.php.net/manual/en/dateinterval.format.php
 *
 * @author Jan H. Krueger <jan@janhkrueger.de>
 * @copyright Jan H. Krueger
 * @package EVE
 * @category EVE-FrontEnd-Skript
 * @version 1.0
 * @since 12.01.2014
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 *
 * Letzte Aenderung:
 * 12.01.2014: Erstellung und Test
 * 18.01.2014: Farbliche Kennzeichnung abgeschlossener Jobs
 * 18.01.2014: AnfÃ¼gen einer Legende
 * 18.01.2014: Farbliche Markierung des aktuellen Forschungsgebietes.
 * 20.01.2014: Umstellung auf MysqlI
 * 24.01.2014: Aufnahme der Restzeit
 * 24.01.2014: Farbliche Markierung der Restlaufzeiten wenn kleiner einem Tag / kleiner einer Woche
 */
error_reporting(E_ALL);
include ("../includes/inc_connect.php");
 

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="de">
    <head>
        <title>RASI - Job Liste</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="../css/jquery.dataTables.css">
	<link rel="stylesheet" type="text/css" href="../css/bpo.css">
	<style type="text/css" class="init">

	</style>
	<script type="text/javascript" language="javascript" src="../js/jquery-2.1.4.min.js"></script>
	<script type="text/javascript" language="javascript" src="../js/jquery.dataTables.min.js"></script>
	<script type="text/javascript" class="init">


$(document).ready(function() {
    $('#jobslisting4').dataTable( {
        "order": [[ 3, "asc" ]],
    } );

    $('#jobslisting5').dataTable( {
        "order": [[ 3, "asc" ]]
    } );
    
    $('#jobslisting3').dataTable( {
        "order": [[ 3, "asc" ]]
    } );
    
} );


	</script>
</head>
    <body id="body">

<?php
# Menü einbinden
include ("../includes/menu.php");

function listJobs ($activity) {

global $link;

$sql = "SELECT * FROM jobs WHERE activityID = ".$activity." ORDER BY endDate asc";
$data = $link->query($sql);

// Nun eine Unterscheidung zwischen Forschung und Copyjob
// 3,'Researching Time Productivity'
// 4,'Researching Material Productivity'
// 5,'Copying'

if ($activity == '3') {
	$activityName = 'Produktionseffizienz ('.$data->num_rows.')';
	$activityShort = 'PE+';
}

if ($activity == '4') {
	$activityName = 'Materialeffizienz ('.$data->num_rows.')';
	$activityShort = 'ME+';
}

if ($activity == '5') {
	$activityName = 'Copys ('.$data->num_rows.')';
	$activityShort = 'Jobs(Runs)';
}

echo '<table id="jobslisting'.$activity.'" class="display" cellspacing="0">';
echo '        <thead>';
echo '            <tr>';
echo '                <th width="310px">'.$activityName.'</th>';
echo '                <th width="25px">'.$activityShort.'</th>';
echo '                <th width="125px">Starter</th>';
echo '                <th width="105px">Endzeit</th>';
echo '                <th width="105px">Restzeit date</th>';
echo '                <th>Jobkosten</th>';
echo '            </tr>';
echo '        </thead>';

#echo '        <tfoot>';
#echo '            <tr>';
#echo '                <th width="310px">'.$activityName.'</th>';
#echo '                <th width="25px">'.$activityShort.'</th>';
#echo '                <th width="125px">Starter</th>';
#echo '                <th width="105px">Endzeit</th>';
#echo '                <th width="105px">Restzeit date</th>';
#echo '                <th>Jobkosten</th>';
#echo '            </tr>';
#echo '        </tfoot>';



$endtime = "";
$endtime2 = "";
$now = date("Y-m-d H:i:s");

// Definieren das die weiteren Angaben in UTC sind.

date_default_timezone_set("UTC");

while ($info = $data->fetch_assoc()) {
  	date_default_timezone_set("UTC");
  	$ende = "";
  	$ende = null;

    // Beginnzeit auslesen und auf die neue Zeitzone umrechnen

    $begintime = new DateTime($info['startDate']);
    $begintime = $begintime->setTimeZone(new DateTimeZone("Europe/Berlin"))->format('Y-m-d H:i:s');

    // Endzeit auslesen und auf die neue Zeitzone umrechnen

    $ende = new DateTime($info['endDate']);
    $ende = $ende->setTimeZone(new DateTimeZone("Europe/Berlin"))->format('Y-m-d H:i:s');
    $activityID = $info['activityID'];
    $runs = $info['runs'];
    $style = "tdname";
    if ($ende < $now) $style.= " green";

	    if ($activityID == '5')
	      {
	        $style.= " bold orange";
	        $copies = $runs . " (" . number_format($info['licensedRuns'], 0, 1000, '.') . ")";
	      }


    // Endzeit
    $endezeit = date('m-d H:i', strtotime($ende));

    // Restzeit
    // Jetzt ermitteln
    date_default_timezone_set("Europe/Berlin");
    $nowDate = date("Y-m-d H:i:s");
    $endeDate = date('Y-m-d H:i:s', strtotime($ende));

    // DateTime Objekte daraus erzeugen
    $datetime = new DateTime($endeDate);
    $datetimenow = new DateTime($nowDate);
    $interval = $datetimenow->diff($datetime);
    $restzeit = $interval->format('%r%a Tage %H:%I St.');
    $monat = $interval->format('%m');
    $tage = $interval->format('%d');
    $tage += $tage + ($monat * 30);
    $style = "tdjobresttime";
    if ($tage < 1) $style.= " blue";
    if ($tage < 13 && $tage > 0) $style.= " yellow";
    if ($ende < $now) $style.= " green greentext";

    // Jobkosten
    $kosten = number_format($info['cost'] , 2, '.', '.');
    


echo '            <tr>';
echo '                <td data-search="'.substr($info['blueprintTypeName'], 0, -10).'" width="310px"><a class="silentLink" href="../bpo/#'.$info['blueprintID'].'"><img class="clean" width="22" src="../Types/'.$info['blueprintTypeID'].'_32.png"">' . substr($info['blueprintTypeName'], 0, -10) . '</a></td>';
echo '                <td class="orange bold" width="25px">'.$runs.'</td>';
echo '                <td width="125px">'.$info['installerName'].'</td>';
echo '                <td width="105px">'.$endezeit.'</td>';

echo '                <td data-order="'.$info['endDate'].'" class="' . $style . '" width="105px">'.$restzeit.'</td>';
echo '                <td data-order="'.$info['cost'].'" class="' . $style . '">'.$kosten.'</td>';
echo '            </tr>';
 
  }

echo '		</tbody>';

$data->free();

echo '</<table>';

} # Ende Funktion



listJobs (4);
echo "<br>";
listJobs (3);
echo "<br>";
listJobs (5);
echo "<br>";



//echo 'Die Daten werden einmal stündlich, kurz nach der vollen Stunde aktualisiert.';
echo '    </body>';
echo '</html>';

// MySQL Ressourcen freigeben und Verbindung beenden
include ("../includes/close.php");

?>
