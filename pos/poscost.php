<?php
/**
 * poscost.php
 *
 * Ermittlung der Kosten für POS-Fuel sowie berechnung was ein Copyslot pro Stunde kostet.
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
 * 17.02.2014: Erstellung und Test
 */
error_reporting(E_ALL);
include ("../includes/inc_connect.php");

$fuelStunde = 40;
$fuelTag = 960;
$fuelWoche = 6720;
$fuelMonat = 28800;


# Alte Daten löschen
$sql = "DELETE FROM pos_fuel_cost";
$stmt = $dbh->prepare($sql);
if ( ! $stmt->execute() )
	{
		echo $sql;
		print_r($stmt->errorInfo());
		exit;
	}

# Nun die aktuellen Werte für die benötigten Items abfragen.
$typeids=array(17888,16272,16273,3683,9832,3689,44,9848,4051);
$url="http://api.eve-central.com/api/marketstat?usesystem=30002187&typeid=".join('&typeid=',$typeids);
#$url="marketstat.xml";
$pricexml=file_get_contents($url);
$xml=new SimpleXMLElement($pricexml);
foreach($typeids as $typeid)
{
    $item=$xml->xpath('/evec_api/marketstat/type[@id='.$typeid.']');
    $price= (float) $item[0]->sell->median;
    $price=round($price,2);

    $sql = "INSERT INTO pos_fuel_cost VALUES (:typeid, :cost)";
		$stmt = $dbh->prepare($sql);
		$stmt->bindParam(':typeid', $typeid, PDO::PARAM_INT);
		$stmt->bindParam(':cost', $price, PDO::PARAM_INT);
		if ( ! $stmt->execute() )
			{
				echo $sql;
				print_r($stmt->errorInfo());
				exit;
			}
} # Ende ForEach


#Jetzt die Berechnungen für jeden Block
$sql = "SELECT * FROM pos_fuel_cost WHERE typeID != 4247";
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

	# Kosten mit der benötigten Anzahl multiplizieren
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
}

# Berechnung der Kosten bei der Eigenproduktion pro Block
$blockProduktion = $summe / 40;

# Nun die Kosten eines Blockes beim Direktkauf auslesen
$sql = "SELECT * FROM pos_fuel_cost WHERE typeID = 4051";
$stmt = $dbh->prepare($sql);
if ( ! $stmt->execute() )
	{
		echo $sql;
		print_r($stmt->errorInfo());
		exit;
	}
$info = $stmt->fetch(PDO::FETCH_OBJ);
$blockKauf = $info->price;

# Vergleich was geringer ist
if ( $blockProduktion > $blockKauf ) 
{
	$blockEndpreis = $blockKauf;
}
else 
{
	$blockEndpreis = $blockProduktion;
}


# Eintragen was ein Copyjob pro Stunde kostet.
$posID = YOURPOSID;
$sql = "SELECT copy FROM pos_jobs WHERE itemID=:posid";
$stmt = $dbh->prepare($sql);
$stmt->bindParam(':posid', $posID, PDO::PARAM_INT);
if ( ! $stmt->execute() )
	{
		echo $sql;
		print_r($stmt->errorInfo());
		exit;
	}
$info = $stmt->fetch(PDO::FETCH_OBJ);
$countCopySlot = $info->copy;

# Kosten eines Copyslots pro Stunde
$copyCost = $blockEndpreis * 40 / $countCopySlot;

# Nun die Kosten pro Stunde wieder in die DB schreiben.
$sql = "UPDATE pos_jobs SET copy_cost=:cost WHERE itemID=:posid";
$stmt = $dbh->prepare($sql);
$stmt->bindParam(':cost', $copyCost, PDO::PARAM_INT);
$stmt->bindParam(':posid', $posID, PDO::PARAM_INT);
if ( ! $stmt->execute() )
	{
		echo $sql;
		print_r($stmt->errorInfo());
		exit;
	}

// MySQL Ressourcen freigeben und Verbindung beenden


$link->close();
?>
