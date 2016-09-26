<?php
/**
 * bpoedit.php
 *
 * Formular zur îderung einer BPO
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
 * 20.01.2014: Lagerbestand editierbar
 * 24.01.2014: Beim Neuerwerb werden nun auch der Soldcounter und die Einnahmen auf 0 gesetzt
 */
error_reporting(E_ALL);
include ("../includes/inc_connect.php");
ini_set("display_errors", 1);

function factorCosts($kopierkosten, $factor) {
	$style = "tdname";
	$Fivekopierkosten = $kopierkosten * $factor;
	$FiveDifferenz = $Fivekopierkosten - $kopierkosten;
	echo '<div class="table">' . "\n";
	echo '<div class="' . $style . '">Kopierkosten mal '.($factor*100-100).'%</div>' . "\n";
	$style = "tdvk";
	echo '<div class="' . $style . '">' . number_format($Fivekopierkosten,2,',','.') . '</div>' . "\n";
	echo '</div>' . "\n";
	echo '<div class="table">' . "\n";
	$style = "tdname";
	echo '<div class="' . $style . '">Gewinn</div>' . "\n";
	$style = "tdvk";
	echo '<div class="' . $style . '">' . number_format($FiveDifferenz,2,',','.') . '</div>' . "\n";
	echo '</div>' . "\n";
}

$pos = YOURPOSID;

# Erh�g des Sold-Counters
if ( isset ($_REQUEST["bpoid"])) {
    $bpoid = $_REQUEST["bpoid"];
}


# Auslesen der Werte f�t
if ( isset ($_REQUEST["change"]) && $_REQUEST["change"] == "anpassen" ) {
   $pme = $_REQUEST["pme"];
   $ppe = $_REQUEST["ppe"];
   $runs = $_REQUEST["runs"];
   $ek = $_REQUEST["ek"];
   $vk = $_REQUEST["vk"];

   $profit = "";


   # Punkte entsorgen
   
   try {
     $fmt = numfmt_create( 'de_DE', NumberFormatter::DECIMAL );
   }
   catch (Exception $e) {
     echo $e;
     echo numfmt_get_error_code();
   }
   // echo "BLUB";
   $fmtDouble = numfmt_create( 'de_DE', NumberFormatter::TYPE_DOUBLE );
   $pme = str_replace(".", "", $pme);
   $ppe = str_replace(".", "", $ppe);
   $ek = numfmt_parse($fmt, $ek);
   $vk = numfmt_parse($fmt, $vk);
   $runs = numfmt_parse($fmt, $runs);   
   // echo "BL";

   if ( isset ($_REQUEST["setprofit"])) {
   	   $sql = "UPDATE bpolevels SET pme=".$pme.", ppe=".$ppe.", runs=".$runs.", ek=".$ek.", vk=".$vk.", income=0, sold=0, profit=".($ek*-1)." WHERE itemID = ".$bpoid."";
       $link->query($sql);

   }
   else {
   		 $sql = "UPDATE bpolevels SET pme=".$pme.", ppe=".$ppe.", runs=".$runs.", ek=".$ek.", vk=".$vk." WHERE itemID = ".$bpoid."";
   	   $link->query($sql);

   }


   # MySQL Ressourcen freigeben und Verbindung beenden
	 $link->close();

	 # Variablen freigeben
	 unset($sql);
   unset($update);
   unset($runs);
   unset($vk);
   unset($ek);
   unset($stock);
   unset($profit);
   unset($ppe);
   unset($pe);
   unset($pme);
   unset($me);

   header("Location: ../bpo/#".$bpoid);
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <title>RASI - BPO îdern</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <link rel="stylesheet" type="text/css" href="../css/bpo.css">

    </head>
    <body id="body">

<?php
# Men�inden
include ("../includes/menu.php");

?>

<br>

<div class="tableheader">
  <div class="tdname">Name</div>
  <div class="tdpme">PME</div>
  <div class="tdppe">PPE</div>
  <div class="tdruns">Runs</div>
  <div class="tdstock">Auf Lager</div>
  <div class="tdek">EK</div>
  <div class="tdvk">VK</div>
  <div class="tdname">Neukauf</div>  
  <div class="tdname">Absenden</div>
</div>

<?php

echo '<form action="bpoedit.php" method="post" accept-charset="ISO-8859-1">';
echo '<input type="hidden" name="bpoid" value="'.$bpoid.'">';
echo '<input type="hidden" name="change" value="anpassen">';

#$sql = "SELECT t.TypeName, t.typeID, bl . *, bt.researchCopyTime, bt.maxProductionLimit FROM bpolevels bl, invTypes t, invBlueprintTypes bt WHERE t.typeID = bl.typeID AND bt.blueprintTypeID = bl.typeID AND bl.itemID = :bpoid ORDER BY t.typeName, bl.itemID asc";
$sql = "SELECT t.TypeName, t.typeID, bl.* FROM bpolevels bl, invTypes t WHERE t.typeID = bl.typeID AND bl.itemID = :bpoid ORDER BY t.typeName, bl.itemID asc";

$stmt = $dbh->prepare($sql);
$stmt->bindParam(':bpoid', $bpoid, PDO::PARAM_INT);
if ( ! $stmt->execute() ) {
	echo $sql;
	print_r($stmt->errorInfo());
	exit;
}


$info = $stmt->fetch(PDO::FETCH_OBJ);
echo '<div class="table">';


echo '<div class="tdname showTip '.$info->itemID.'">'.substr($info->TypeName, 0, -10).'</div>';


$style = "tdme";
echo '<div class="'.$style.'"><input name="pme" class="blubbel" type="text" value='.number_format($info->pme, 0, 1000, '.').'></input></div>';

$style = "tdme";

echo '<div class="'.$style.'"><input name="ppe" class="blubbel" type="text" value='.number_format($info->ppe, 0, 1000, '.').'></input></div>';


$style = "tdruns";
echo '<div class="'.$style.'"><input name="runs" class="blubbel" type="text" value='.number_format($info->runs, 0, 1000, '.').'></input></div>';

# Lagerbestand
$sqlstock = "SELECT b2.typeID, IFNULL(COUNT(b2.itemID),0) AS stock
FROM bpos b2
WHERE b2.quantity = -2
AND b2.typeID = :typeid
GROUP BY b2.typeID";

$stmtstock = $dbh->prepare($sqlstock);
$stmtstock->bindParam(':typeid', $info->typeID, PDO::PARAM_INT);
if ( ! $stmtstock->execute() ) {
	echo $sqlstock;
	print_r($stmtstock->errorInfo());
	exit;
}
$infostock = $stmtstock->fetch(PDO::FETCH_OBJ);

if ($stmtstock->rowCount() == 0) $stock = 0;
else $stock = $infostock->stock;

$style = "tdstock";
echo '<div class="'.$style.'"><input name="stock" class="blubbel" type="text" value='.number_format($stock, 0, 1000, '.').'></input></div>';


$style = "tdek";
echo '<div class="'.$style.'"><input name="ek" class="blubbel" type="text" value='.number_format($info->ek, 0, 1000, '.').'></input></div>';


$style = "tdvk";
echo '<div class="'.$style.'"><input name="vk" type="text"  class="blubbel" value='.number_format($info->vk, 0, 1000, '.').'></input></div>';



$typeID = $info->typeID;

# Button zum Neueintragen
$style = "tdname";
echo '<div class="'.$style.'"><input class="blubbelbutton" type="checkbox" name="setprofit" value="salami"></input></div>';

echo '<div class="'.$style.'"><input class="btn" type="submit" value="Aktualisieren"></input></div>';

echo '</div>';
echo '</form>';

echo "<br>";

echo '<div class="tableheader">';
echo '  <div class="tdname">Zofu</div>';
echo '</div>';
echo '<div class="table">' . "\n";
echo '<div class="tdname"><a target="_blank" href="http://zofu.no-ip.de/bpo?type='.$typeID.';me=;skme=5;pe=;skpe=5;batch=;skin=5;.cgifields=skme;.cgifields=skin;.cgifields=skpe">Zofu</a></div>';
echo '</div>';

echo "<br>";

echo '<div class="tableheader">';
echo '  <div class="tdname">ChaChing Preis</div>';
echo '</div>';
echo '<div class="table">' . "\n";
echo '<div class="tdname">'.number_format($info->chaching, 0, 1000, '.').'</div>';
echo '</div>';


echo "<br>";

echo '<div class="tableheader">';
echo '  <div class="tdname">&nbsp;</div>';
echo '  <div class="tdvk"> </div>';
echo '</div>';


echo '    </body>';
echo '</html>';

# MySQL Ressourcen freigeben und Verbindung beenden
include ("../includes/close.php");

# Variablen freigeben
unset($sql);
unset($data);
unset($info);
unset($style);
unset($style2);
unset($link);


?>
