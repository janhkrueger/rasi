<?php
/**
 * index.php
 *
 * BPC-Daten per API ausgeben
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
 * 22.01.2014: Erstellung und Test
 */
error_reporting(E_ALL);
include ("../includes/inc_connect.php");

// // Ausgabe der BPOs
// ErhÃ¶hung des Sold-Counters

if (isset($_REQUEST["typeID"]))
  {
    $typeID = $_REQUEST["typeID"];
  }

// ErhÃ¶hung des Sold-Counters

if (isset($_REQUEST["typeid"]))
  {
    $typeID = $_REQUEST["typeid"];
  }

// Daten der BPO abfragen

$sql = "select materialEfficiency, timeEfficiency, runs, vk, stock from bpos WHERE typeID = $typeID";
$data = $link->query($sql);
$info = $data->fetch_assoc();
ini_set('default_mimetype', 'text/xml'); //MIME-TYPE auf text/xml festlegen.
header('Content-type: text/xml'); //Header senden um sich als XML auszugeben.
$config = '<?xml version="1.0" encoding="utf-8"?>';
echo $config;
echo "<bpc>" . "\n";
echo "  <typeID>" . $typeID . "</typeID>" . "\n";
echo "  <me>" . $info['me'] . "</me>" . "\n";
echo "  <pe>" . $info['pe'] . "</pe>" . "\n";
echo "  <price>" . $info['vk'] . "</price>" . "\n";
echo "  <stock>" . $info['stock'] . "</stock>" . "\n";
echo "</bpc>";

// MySQL Ressourcen freigeben und Verbindung beenden

unset($typeID);
$data->free();
$link->close();
?>
