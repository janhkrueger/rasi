<?php
/**
 * index.php
 *
 * Anzeige der im Besitz befindlichen BPOs einer Corp
 *
 * @author     Jan H. Krueger <jan@janhkrueger.de>
 * @copyright  Jan H. Krueger
 * @package     EVE
 * @category   EVE-FrontEnd-Skript
 * @version   1.0
 * @since     12.01.2014
 * @license   http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 *
 * Letzte Aenderung:
 * 12.01.2014: Erstellung und Test
 * 20.01.2014: Umstellung auf mysqli
 * Reduktion des Lagerbestandes bei Verkauf
 * 24.01.2014: Auslagerung der SellFunktionalität in eine eigene Datei zur besseren
 * Rechtesteuerung
 */
error_reporting(E_ALL);
error_reporting(-1);
include ("../includes/inc_connect.php");
ini_set("display_errors", 1);

// // Ausgabe der BPOs
// Erhöhung des Sold-Counters

if (isset($_REQUEST["add"]))
  {
    $aktion = $_REQUEST["add"];
    $bpoid = $_REQUEST["bpo"];

    // Erhöhung Counter

    $sql = "UPDATE bpo set sold=sold+1, income=income+vk, profit=profit+vk*0.9, stock=(stock-1) WHERE bpoid = " . $bpoid;
    $link->query($sql);

    // vk ermitteln

    $sql = "select vk from bpo WHERE bpoid = " . $bpoid;
    $data = $link->query($sql);
    $vk = $data->fetch_assoc();
    $sql = "INSERT INTO bposells (bpoid, selldate, vk) values (" . $bpoid . ", now(), " . $vk['vk'] . ")";
    $link->query($sql);

    // Speicherung in Verkaufstabelle
    // BPOID, Datum, Preis
    // MySQL Ressourcen freigeben und Verbindung beenden

    $data->free();

    // Variablen freigeben

    unset($aktion);
    unset($update);
    unset($bpo);
    unset($data);
    unset($sql);
    unset($_REQUEST["add"]);
    unset($_REQUEST["bpo"]);
  }
else
  {
    $aktion = "";
    $bpoid = "";

    // Variablen freigeben

    unset($aktion);
    unset($bpo);
    unset($data);
    unset($sql);
    unset($_REQUEST["add"]);
    unset($_REQUEST["bpo"]);
  }


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="de">
    <head>
       <title>RASI - BPO Liste</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="../css/jquery.dataTables.css">
	<link rel="stylesheet" type="text/css" href="../css/bpo.css">
	<style type="text/css" class="init">

	</style>
	<script type="text/javascript" language="javascript" src="../js/jquery-2.1.4.min.js"></script>
	<script type="text/javascript" language="javascript" src="../js/jquery.dataTables.min.js"></script>
	<script type="text/javascript" language="javascript" src="//cdn.datatables.net/plug-ins/1.10.7/integration/bootstrap/3/dataTables.bootstrap.js"></script>	
	
	<script type="text/javascript" class="init">


$(document).ready(function() {
    $('#bpolisting').dataTable( {
        "pagingType": "full_numbers",
        "lengthMenu": [[-1, 10, 25, 50], ["All", 10, 25, 50]],
          "columns": [
			    { "width": "310px" },
			    { "width": "20px" },
			    { "width": "20px" },
			    { "width": "20px" },
         		    { "width": "20px" },
			    { "width": "80px" },
			    { "width": "75px" },
			    { "width": "75px" },
			    { "width": "85px" },
			    { "width": "85px" },
			    { "width": "90px" },
			    { "width": "140px" }
			  ]
    } );

    
} );


	</script>
</head>
    <body id="body">

<?php
# Menü einbinden
include ("../includes/menu.php");
?>

<?php

$sql = "SELECT b.itemID, b.typeID, b.typeName, b.timeEfficiency, b.materialEfficiency, iB.maxProductionLimit as runs, IFNULL(s.bpcs,0) AS bpcs, bl.ek, bl.vk, bl.sold as verkauft, bl.income as einnahmen, bl.profit as gewinn,
ROUND((bl.ek - bl.profit) / bl.vk+0.5) as BreakEven, 0 as researchCopyTime, 0 as maxProductionLimit, j.installerName
FROM bpos b
LEFT JOIN
  (
  SELECT b2.typeID, COUNT(b2.itemID) AS bpcs
  FROM bpos b2
  WHERE b2.quantity = -2
  GROUP BY b2.typeID
) AS s
ON b.typeID = s.typeID
LEFT JOIN bpolevels bl
ON b.itemID = bl.itemID
LEFT JOIN industryBlueprints iB
ON b.typeID = iB.typeID
LEFT OUTER JOIN jobs j
ON b.itemID = j.blueprintID
WHERE b.quantity = -1
ORDER BY b.typeName asc, b.itemID asc";


$data = $link->query($sql);

if (!$data) {
    throw new Exception("Database Error [{$link->errno}] {$link->error}");
}

echo '<table id="bpolisting" class="compact stripe hover order-column row-bordercell-border" cellspacing="0" width="80%">';
echo '        <thead>';
echo '            <tr>';
echo '                <th class="tdname"			>Name</th>';
echo '                <th class="tdme"				>ME</th>';
echo '                <th class="tdpe"				>PE</th>';
echo '                <th class="tdruns"		  >Runs</th>';
echo '                <th class="tdstock"		  >Lager</th>';
echo '                <th class="tdek"				>EK</th>';
echo '                <th class="tdvk"				>VK date</th>';
echo '                <th class="tdsold"			>Verkauft</th>';
echo '                <th class="tdincome"		>Einnahmen</th>';
echo '                <th class="tdprofit"		>Gewinn</th>';
echo '                <th class="tdbreakeven" >BreakEven</th>';
echo '                <th class="tdcopytime"	>Kopierzeit</th>';
echo '            </tr>';
echo '        </thead>';

echo '        <tbody>';

while ($info = $data->fetch_assoc()) {

echo '<tr>'. "\n";

    $style = '';
    if ( $info['installerName'] != "" )
      {
       	$style.= " yellow";
      }

echo '	<td class="tdname '.$style.'"><a class="silentLink" href="bpoedit.php?bpoid=' . $info['itemID'] . '" name="' . $info['itemID'] . '">' . substr($info['typeName'], 0, -10) . '</a></td>'. "\n";

$style = "tdme";
if (($info['materialEfficiency'] < 10 ) OR $info['materialEfficiency'] == 0) {
	$style.= " orange";
}
echo '	<td class="'.$style.'">'.number_format($info['materialEfficiency'], 0, 1000, '.').'</td>'. "\n";

// PE
$style = "tdpe";
if (($info['timeEfficiency'] < 20 ) OR $info['timeEfficiency'] == 0) {
	$style.= " orange";
}
echo '	<td class="' . $style . '" >' . number_format($info['timeEfficiency'], 0, 1000, '.') . '</td>'. "\n";


// Runs
$style = "tdruns";
if ( -999 == 0) $style.= " yellow";
echo '	<td class="' . $style . '" >' . number_format( $info['runs'], 0, 1000, '.') . '</td>'. "\n";

// Lagers
echo '	<td class="tdstock">' . $info['bpcs'] . '</td>'. "\n";

// Einkaufspreis
echo '	<td class="tdek blue"				>' . number_format($info['ek'], 0, 1000, '.') . '</td>'. "\n";

// Verkaufspreis
$style = "tdvk";
if ($info['vk'] == 1) $style.= " yellow";
echo '	<td class="' . $style . '"				>' . number_format($info['vk'], 0, 1000, '.') . '</td>'. "\n";

// Anzahl der Verkäufe
echo '	<td data-sort="'.$info['verkauft'].'" class="tdsold"			><a class="sellbpc" href="bposellone.php?add&bpo=' . $info['itemID'] . '" onclick="doRequest(\'index.php?add&bpo=' . $info['itemID'] . '\')"><input class="sellbpc" bpo="' . $info['itemID'] . '" type="submit" value="' . $info['verkauft'] . '"></a></div></td>'. "\n";

// Bisheriger Umsatz
$style = "tdincome";
if (($info['einnahmen']) > 0) $style.= " green";
echo '	<td data-sort="'.$info['einnahmen'].'" class="' . $style . '"		>' . number_format($info['einnahmen'], 0, 1000, '.') . '</td>'. "\n";

// Bisherigen Einkommen
$style = "tdprofit";
if ($info['gewinn'] < 0) $style.= " red";
if ($info['gewinn'] > 0) $style.= " green";
echo '	<td data-sort="'.$info['gewinn'].'" class="' . $style . '"		>' . number_format($info['gewinn'], 0, 1000, '.') . '</td>'. "\n";

// EK minus Einnahmen / vk
$breakeven = $info['BreakEven'];
$style = "tdbreakeven";
if ($breakeven < 0) $style.= " green greentext";
echo '	<td class="' . $style . '" >' . number_format($breakeven, 0, 1000, '.') . '</td>'. "\n";

// Kopierzeit
$style = "tdcopytime";
#$kopierzeit = ($info['researchCopyTime'] * 2 / $info['maxProductionLimit']) * ( 1 - (0.05 * 5)) * 0.65 * 0.95 * $info['maxProductionLimit'];
$kopierzeit = 0;
echo '	<td class="' . $style . '"	>'. secondsToTime(round($kopierzeit)) .'</td>'. "\n";

echo '</tr>'. "\n";
  }
  echo '        </tbody>';

echo '    </body>' . "\n";
echo '</html>' . "\n";

// MySQL Ressourcen freigeben und Verbindung beenden
include ("../includes/close.php");

?>
