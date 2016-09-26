<?php             
/**
 * index.php
 *
 * anzeige der zum Verkauf stehenden BPCs
 *
 * @author	    Jan H. Krueger <jan@janhkrueger.de>
 * @copyright 	Jan H. Krueger
 * @package	   	EVE
 * @category	  EVE-FrontEnd-Skript
 * @version		  1.0
 * @since		    12.01.2014
 * @license		  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 *
 * Letzte Aenderung:
 * 12.01.2014: Erstellung und Test
 * 20.01.2014: Umstellung auf mysqli
 * Reduktion des Lagerbestandes bei Verkauf
 * 01.02.2014: Umstellung auf bpolevels
 */

error_reporting(E_ALL);
include ("../includes/inc_connect.php");



?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
       <title>RASI - Sell List</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <link rel="stylesheet" type="text/css" href="../css/bpo.css">

    </head>
    <body id="body">


<div class="tableheader" align="center">
  <div class="tdname">Name</div>
  <div class="tdme">ME</div>
  <div class="tdpe">PE</div>
  <div class="tdruns">Runs</div>
  <div class="tdstock">Auf Lager</div>
  <div class="tdvk">VK in ISK</div>
</div>

<?php



$sql = "SELECT b.typeID, b.typeName, b.timeEfficiency, b.materialEfficiency, b.runs as runs, IFNULL(s.bpcs,0) AS bpcs, bl2.vk
FROM bpos b
LEFT JOIN
  (
  SELECT b2.typeID, COUNT(b2.itemID) AS bpcs
  FROM bpos b2
  WHERE b2.quantity = -2
  GROUP BY b2.typeID
) AS s
ON b.typeID = s.typeID
LEFT JOIN
  (
  SELECT bl2.typeID, bl2.vk AS vk
  FROM bpolevels bl2
) AS bl2
ON b.typeID = bl2.typeID
WHERE b.quantity = -2
AND b.typeID NOT IN (select itemID FROM bpc_nosell)
GROUP BY b.typeID, b.typeName, b.runs
ORDER BY b.typeName asc, b.itemID asc";


$data = $link->query($sql);
while($info = $data->fetch_assoc())
{
		echo '<div class="table">';


		echo '<div class="tdname showTip '.$info['typeID'].'">'.substr($info['typeName'], 0, -10).'</div>';

		$style = "tdme";
		if ( ($info['materialEfficiency'] < 10) OR $info['materialEfficiency'] == 0) {
			$style .= " orange";
		}
		echo '<div class="'.$style.'">'.number_format($info['materialEfficiency'], 0, 1000, '.').'</div>';

		# PME

		$style = "tdme";
		$style2 = "tdpme";
		if ( ($info['timeEfficiency'] < 20) OR $info['timeEfficiency'] == 0) {
			$style .= " orange";
			$style2 .= " orange";
		}
		echo '<div class="'.$style.'">'.number_format($info['timeEfficiency'], 0, 1000, '.').'</div>';

    # Runs
		$style = "tdruns";
		if ($info['runs'] == 0) $style .= " yellow";
		echo '<div class="'.$style.'">'.number_format($info['runs'], 0, 1000, '.').'</div>';

		echo '<div class="tdstock">'.number_format($info['bpcs'], 0, 1000, '.').'</div>';

    #VK
		$style = "tdvk";
		if  ($info['vk'] == 1 ) $style .= " yellow";
		echo '<div class="'.$style.'">'.number_format($info['vk'], 0, 1000, '.').'</div>';



	  echo '</div>';
}


echo '    </body>';
echo '</html>';

# MySQL Ressourcen freigeben und Verbindung beenden
$data->free();
$link->close();
?>
