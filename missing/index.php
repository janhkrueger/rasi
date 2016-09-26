<?php
/**
 * index.php
 *
 * Anzeige fehlender BPOs der Corps
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
 * 19.01.2014: Entfernung von T2-BPOs aus der Ergebnisliste
 * 20.01.2014: Umstellung auf mysqli
 * 31.01.2014: Tabellenwechsel auf bpolevels
 */
 
error_reporting(E_ALL);
include ("../includes/inc_connect.php");
?>



<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <title>RASI - Fehlende BPOs</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <link rel="stylesheet" type="text/css" href="../css/bpo.css">

    </head>
    <body id="body">
<?php
# Menü einbinden
include ("../includes/menu.php");
?>

<div class="tableheader" align="center">
  <div class="tdname">Name</div>
  <div class="tdstock">EVE-ID</div>
  <div class="tdstock">EVE Central</div>  
</div>

<?php

$sql = "SELECT it.*
FROM invTypes it, industryBlueprints ib 
WHERE it.typeID = ib.typeID
AND it.published = 1
AND it.typeName NOT LIKE '%II Blueprint'
AND it.typeID NOT IN (SELECT b.typeID FROM bpos b)
AND it.marketGroupID IS NOT NULL
AND it.basePrice > 0.0
AND it.typeID NOT IN (SELECT be.typeID FROM bposexcluded be)
ORDER BY it.typeName";



$data = $link->query($sql);
while($info = $data->fetch_assoc())
{
		echo '<div class="table">'. "\n";

		# Name des BPO
		echo '<div class="tdname" onclick="CCPEVE.showMarketDetails('.$info['typeID'].')">'.substr($info['typeName'], 0, -10).'</div>'. "\n";


		# EVE-ID
		$style = "tdstock";
		echo '<div class="'.$style.'">'.$info['typeID'].'</div>'. "\n";
		
		# EVE-Central link
		echo '<div class="tdstock"><a href="http://eve-central.com/home/quicklook.html?typeid='.$info['typeID'].'" target="_blank">EVE Central</a></div>'. "\n";
	

		echo '</div>'. "\n";
		
}
echo '    </body>'. "\n";
echo '</html>'. "\n";

# MySQL Ressourcen freigeben und Verbindung beenden
$data->free();
$link->close();

?>
