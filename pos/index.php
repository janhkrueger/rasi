<?php
/**
 * index.php
 *
 * Ausgabe der Kosten für Fuel
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
 * 17.02.2014: Erstellung und Test
 * 17.02.2014: Umstellung auf eine reine Ausgabe der Werte aus der Datenbank
 */
error_reporting(E_ALL);
include ("../includes/inc_connect.php");

$fuelStunde = 40;
$fuelTag = 960;
$fuelWoche = 6720;
$fuelMonat = 28800;

function idToName($typeID) {

	$name = "";

	switch ($typeID) {
	    case 17888:
	        $name = "﻿Nitrogen Isotopes";
	        break;
	    case 16272:
	        $name = "Heavy Water";
	        break;
	    case 16273:
	        $name = "Liquid Ozone";
	        break;
	    case 3683:
	        $name = "Oxygen";
	        break;
	    case 9832:
	        $name = "Coolant";
	        break;
	    case 3689:
	        $name = "Mechanical Parts";
	        break;
	    case 44:
	        $name = "Enriched Uranium";
	        break;
	    case 9848:
	        $name = "Robotics";
	        break;
	    case 4247:
	        $name = "Amarr Fuel Block";
	        break;
	}

	return $name;

}


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <title>RASI - Fuel Buy</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <link rel="stylesheet" type="text/css" href="../css/bpo.css">
    </head>
    <body id="body">

<?php
# Menü einbinden
include ("../includes/menu.php");



echo '<div class="tableheader" align="center">';
echo '  <div class="tdjobstarter">Rohstoff</div>';
echo '  <div class="tdek">Median</div>';
echo '</div>';


#Jetzt die Berechnungen für jeden Block
$sql = "SELECT * FROM pos_fuel_cost";
$stmt = $dbh->prepare($sql);
if ( ! $stmt->execute() )
	{
		echo $sql;
		print_r($stmt->errorInfo());
		exit;
	}

$summe = 0;

while ($info = $stmt->fetch(PDO::FETCH_OBJ) )
{
	$price = 0;
	$typeID = 0;

	$typeID = $info->typeID;
	$price = $info->price;

	if ($typeID == 17888) {
		$price = $price * 400;
		$summe += $price;
	}
	if ($typeID == 16272) {
		$price = $price * 150;
		$summe += $price;
	}
	if ($typeID == 16273) {
		$price = $price * 150;
		$summe += $price;
	}
	if ($typeID == 3683) {
		$price = $price * 20;
		$summe += $price;
	}
	if ($typeID == 9832) {
		$price = $price * 8;
		$summe += $price;
	}
	if ($typeID == 3689) {
		$price = $price * 4;
		$summe += $price;
	}
	if ($typeID == 44) {
		$price = $price * 4;
		$summe += $price;
	}
	if ($typeID == 9848) {
		$price = $price * 1;
		$summe += $price;
	}
	if ($typeID == 4247) {
		# Den Kaufpreis ermitteln
		$blockKauf = $price;
	}
	# Ausgabe der Werte
	echo '<div class="table">'. "\n";
	$style = "tdjobstarter";
	echo '  <div class="' . $style . '" onclick="CCPEVE.showMarketDetails('.$typeID.')" >'.idToName($typeID).'</div>' . "\n";
	$style = "tdek";
	echo '  <div class="' . $style . '">' . number_format ($price, 2) . '</div>' . "\n";
	echo '</div>' . "\n" . "\n";
}

# Freiraum zwischen beiden Tabellen
echo "<br>". "\n";


echo '<div class="tableheader" align="center">'. "\n";
echo '  <div class="tdjobstarter">Variable</div>'. "\n";
echo '  <div class="tdjobstarter">Ergebnis</div>'. "\n";
echo '</div>'. "\n";

# Berechnung der Kosten pro Block bei Eigenproduktion
$blockProduktion = $summe / $fuelStunde;
echo '<div class="table">'. "\n";
$style = "tdjobstarter";
echo '  <div class="' . $style . '">Baukosten pro Block</div>' . "\n";
echo '  <div class="' . $style . '">' . number_format ($blockProduktion, 2) . '</div>' . "\n";
echo '</div>' . "\n" . "\n";

# Kosten pro Block bei direktem Kauf
echo '<div class="table">'. "\n";
$style = "tdjobstarter";
echo '  <div class="' . $style . '">Einkaufspreis / Block</div>' . "\n";
echo '  <div class="' . $style . '">' . number_format ($blockKauf, 2) . '</div>' . "\n";
echo '</div>' . "\n" . "\n";


# Vergleich was geringer ist
echo '<div class="table">'. "\n";
$style = "tdjobstarter";
echo '  <div class="' . $style . '">Empfehlung:</div>' . "\n";
if ( $blockProduktion > $blockKauf )
{
	$style.= " red";
	echo '  <div class="' . $style . '">Fuel kaufen</div>' . "\n";
	$blockEndpreis = $blockKauf;
}
else
{
	$ersparnis = $blockKauf - $blockProduktion;
	$ersparnisMonat = $ersparnis * 28800;
	
	$style.= " green";
	echo '  <div class="' . $style . '">Fuel selbst herstellen</div>' . "\n";
	echo '</div>' . "\n" . "\n";

	echo '<div class="table">'. "\n";
	$style = "tdjobstarter";
	echo '  <div class="' . $style . '">Ersparnis pro Monat:</div>' . "\n";
	echo '  <div class="' . $style . '">'.number_format ($ersparnisMonat, 2 ).'</div>' . "\n";
	$blockEndpreis = $blockProduktion;
}
echo '</div>' . "\n" . "\n";


echo '</body>'."\n";
echo '</html>';

// MySQL Ressourcen freigeben und Verbindung beenden

?>
