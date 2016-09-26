<?php
/**
 * bposellone.php
 *
 * Erhöhung des SellCounters einer BPO
 *
 * @author     Jan H. Krueger <jan@janhkrueger.de>
 * @copyright  Jan H. Krueger
 * @package     EVE
 * @category   EVE-FrontEnd-Skript
 * @version   1.0
 * @since     24.01.2014
 * @license   http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 *
 * Letzte Aenderung:
 * 24.01.2014: Erstellung und Test
 * 30.01.2014: Umstellung auf itemID und Tabelle bpolevels
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);


include ("../includes/inc_connect.php");

// Ausgabe der BPOs
// Erhöhung des Sold-Counters

if (isset($_REQUEST["add"]))
  {
    $aktion = $_REQUEST["add"];
    $bpoid = $_REQUEST["bpo"];

    // Erhöhung Counter

    $sql = "UPDATE bpolevels set sold=sold+1, income=income+vk, profit=profit+vk*0.9 WHERE itemID = " . $bpoid;
    echo $sql;
    $link->query($sql);

    // vk ermitteln
    $sql = "select vk from bpolevels WHERE itemID = " . $bpoid;
    $data = $link->query($sql);
    $vk = $data->fetch_assoc();
    $sql = "INSERT INTO bposells (itemID, selldate, vk) values (" . $bpoid . ", now(), " . $vk['vk'] . ")";
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

    // Variablen freigeben

    unset($aktion);
    unset($bpo);
    unset($data);
    unset($sql);
    unset($_REQUEST["add"]);
    unset($_REQUEST["bpo"]);
  }

include ("../includes/close.php");

# Nun zur BPO in der entsprechenden Seite wechseln
header("Location: ../bpo/#".$bpoid);

?>
