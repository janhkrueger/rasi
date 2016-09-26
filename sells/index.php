<?php
/**
 * index.php
 *
 * SELLS - Liste der Verkäufe
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
 */
error_reporting(E_ALL);
include ("../includes/inc_connect.php");


$sql = "SELECT sum(vk) sells FROM bposells  WHERE month(selldate) = month(NOW()) AND year(selldate) = year(NOW()) ";
$data = $link->query($sql);
$info = $data->fetch_assoc();
$iskPerMonth = $info['sells'];


# Die drei BPOs mit den Umsatzstärksten verkäufen des Monats
$sql = "SELECT t.typeName, sum(s.vk) sells, t.typeID, count(s.itemID) menge FROM bposells s, bpolevels b, invTypes t WHERE month(s.selldate) = month(NOW()) AND year(s.selldate) = year(NOW()) AND b.itemID = s.itemID  AND t.typeID = b.typeID GROUP BY s.itemID ORDER BY sells desc LIMIT 3 ";
$data = $link->query($sql);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <title>RASI - BPO Liste</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <link rel="stylesheet" type="text/css" href="../css/bpo.css">
    </head>

<script>
// Global definierte Variablen
var fps = 30; // frames per second
var animationSpeed = 5; // je kleiner, desto schneller
var radius = 100; // Radius der Torte in Pixeln

<?php
$werte = "var werte = [";
while($info = $data->fetch_assoc())
{
$werte .= $info['sells'].", ";
}
$werte .= "];";
$werte = str_replace(', ]', ']',$werte);
$werte .= "\n";
#echo number_format($werte, 0, 1000, '.');
echo $werte;

# Beschriftung der Torte zusammen schnippeln


# Die drei BPOs mit den Umsatzstärksten verkäufen des Monats
$data->data_seek(0);

$beschriftung = "var beschriftung = [";
while($info = $data->fetch_assoc())
{
$beschriftung .= $info['typeName'].", ";
}
$beschriftung .= "];";

$beschriftung = str_replace(', ]', '"]',$beschriftung);
$beschriftung = str_replace('[', '["',$beschriftung);
$beschriftung = str_replace(', ', '", "',$beschriftung);
$beschriftung .= "\n";
echo $beschriftung;
?>
var colors = ["#e69138","#ffd966","#a4c2f4","#ff0000","#6aa84f"]

// Deklarationen
var wdh = 0; // Animationsdurchläufe
var total = 0;
var i = 0; // Zählvariable der Tortenstücke
var scale = 0;
var startwinkel = 0;
var endwinkel = 0;


function initPie() {
	context = document.getElementById("canvasElement").getContext('2d');

	// Summe aller Werte berechnen
	for (var z=0; z < werte.length; z++) {
	 total += werte [z];
	}
	//startAnimation();
	startAnimation();
}


function drawPie  () {
	// einen neuen Pfad initialisieren
	context.beginPath();
	// Startposition festlegen
	context.moveTo(200, 150);

	// Summe bisher gezeichneter Segmente
			summe = 0;
			for (var j=0; j <= i; j++) {
				 summe += werte [j];
			}

	startwinkel = toRad((summe - werte[i]) / total * 360);
	endwinkel = toRad(summe  / total * 360);
	aktuellerwinkel = startwinkel + (endwinkel - startwinkel) * scale;

	context.arc(200, 150, 100, startwinkel, aktuellerwinkel, false);
	context.closePath();

	context.fillStyle = colors[i];
	context.fill();

}


function looptime () {
	wdh++;
    scale = wdh / animationSpeed *  total / werte[i];

       // Ende?
       if(scale >= 1) {
		   drawPie(); // Animation ein letztes Mal durchführen
           scale = 0; // Am Ende zurücksetzen
		   wdh = 0;
           clearInterval(timer); // Schleife abbrechen
		   showLegend(); // Legende anzeigen
		   i++; // Segmentzähler hochzählen

		   // gibt es noch weitere Segmente zum Darstellen?
			if (i < werte.length){
				startAnimation ()
			}
      } else {
	      drawPie();
	  }
}


function startAnimation() {
       timer = setInterval(looptime, 1000 / fps );
}


function showLegend () {
	strCode = "<tr><td style='width: 25px; background-color:"+colors[i]+"'></td><td>"+beschriftung[i]+"</td><td> "+werte[i]+"</td></tr>";
	document.getElementById('legende').innerHTML += strCode;
}



// Umrechung Grad -> Radiant
function toRad (x) {
	return (x*Math.PI)/180;
}
</script>




    <body id="body">

<?php
# Menü einbinden
include ("../includes/menu.php");


# Header Bla
echo '<div class="tableheader" align="center">';
echo '  <div class="tdname">Abteilung</div>';
echo '  <div class="tdname">Wert</div>';
echo '</div>';	


# ISK diesen Monat
echo '<div class="table">';
$sql = "select count(bpoid) as anzahl from bpolevels";
$data = $link->query($sql);
echo '<div class="tdname">ISK diesen Monat:</div>';
echo '<div class="tdname">'.number_format($iskPerMonth, 0, 1000, '.').'</div>';
echo '</div>';


#Gesamtanzahl BPOs
echo '<div class="table">';
$sql = "select count(itemID) as anzahl from bpos WHERE quantity=-1";
$data = $link->query($sql);
$info = $data->fetch_assoc();
echo '<div class="tdname">Gesamtanzahl BPOs: </div>';
echo '<div class="tdname">'.number_format($info['anzahl'], 0, 1000, '.').'</div>';
echo '</div>';	

#Nicht perfekte BPOs
echo '<div class="table">';
$sql = "select count(itemID) as anzahl from bpos where (materialEfficiency!= 10 OR timeEfficiency != 20) AND quantity = -1 ";
$data = $link->query($sql);
$info = $data->fetch_assoc();
echo '<div class="tdname">Nicht perfekt erforschte BPOs:</div>';
echo '<div class="tdname">'.number_format($info['anzahl'], 0, 1000, '.').'</div>';
echo '</div>';	


echo "<br>";

####
# Die drei BPOs mit den Umsatzstärksten verkäufen des Monats
####

# 
echo '<div class="tableheader" align="center">';
echo '  <div class="tdname">BPO</div>';
echo '  <div class="tdname">Anzahl</div>';
echo '  <div class="tdname">Umsatz</div>';
echo '</div>';	


$sql = "SELECT t.typeName, sum(s.vk) sells, t.typeID, count(s.itemID) menge FROM bposells s, bpolevels b, invTypes t WHERE month(s.selldate) = month(NOW()) AND year(s.selldate) = year(NOW()) AND b.itemID = s.itemID  AND t.typeID = b.typeID GROUP BY s.itemID ORDER BY sells desc LIMIT 3 ";
$data = $link->query($sql);
while($info = $data->fetch_assoc())
{
echo '<div class="table">';
	echo '<div class="tdname">'.$info['typeName'].'</div>';
	echo '<div class="tdname">'.number_format($info['menge'], 0, 1000, '.').'</div>';
	echo '<div class="tdname">'.number_format($info['sells'], 0, 1000, '.').'</div>';
	echo '</div>';
}


echo "<br>";
echo "<br>";


# MySQL Ressourcen freigeben und Verbindung beenden
$data->free();
$link->close();

# Sonstige Ressourcen freigeben
unset($data);
unset($link);
unset($sql);
unset($info);

?>


<canvas id="canvasElement" width="500" height="500" style="float:left">
  Ihre Browser ist nicht HTML5 tauglich.
</canvas>


<div style="padding: 50px" >
  <table  cellpadding="3" cellspacing="1" >
      <tbody id="legende">
            <tr>
                <th>Farbe</th>
                <th>BPO</th>
                <th>Umsatz</th>
            </tr>
        </tbody>
    </table>
</div>

<br />

<script>initPie()</script>

<?php


echo '    </body>';
echo '</html>';
?>
