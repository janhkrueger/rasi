<?php
/**
 * jobs.php
 *
 * Abfrage der laufenden Jobs wieEVE-API
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
 */

#error_reporting(E_ALL);
include ("../includes/inc_connect.php");


$corpID = "98190387";

# API-Adresse
$xmlfile = "https://api.eveonline.com/corp/StarbaseList.xml.aspx?keyID=KEYID&vCode=VCODE";

# keyID
$keyid = "YOURAPIKEYID";

# vCode
$vcode = "YOURAPIVCODE";

# API-String zusammenbauen
$xmlurl = str_replace("KEYID", $keyid, $xmlfile);
$xmlurl = str_replace("VCODE", $vcode, $xmlurl);

# Alle POSem ermitteln
$xml = simpleXML_load_file($xmlurl,"SimpleXMLElement",LIBXML_NOCDATA);
if($xml ===  FALSE)
{
   //deal with error
   echo "FEHLER beim Einlesen";
}


# Chars aus der Corp nehmen
$sql = "UPDATE pos set active='n' WHERE corpID=".$corpID;
$data = $link->query($sql);

# Jeden Eintrag im XML durcharbeiten
foreach($xml as $key0 => $value)
{
	foreach($value as $key => $value2)
	{
		# Ab hier die POS-Eintraege
		foreach($value2 as $key2 => $value3)
		{
			foreach($value3->attributes() as $attributeskey2 => $attributesvalue3)
			{

				# Alle IDs der POSen auslesen

				# itemID
				if ($attributeskey2 == "itemID") $itemID = $attributesvalue3;
				
				# typeID
				if ($attributeskey2 == "typeID") $typeID = $attributesvalue3;
				
				# locationID
				if ($attributeskey2 == "locationID") $locationID = $attributesvalue3;
				
				# moonID
				if ($attributeskey2 == "moonID") $moonID = $attributesvalue3;

				# state
				if ($attributeskey2 == "state") $state = $attributesvalue3;
				
				# stateTimestamp
				if ($attributeskey2 == "stateTimestamp") $stateTimestamp = $attributesvalue3;
				
				# onlineTimestamp
				if ($attributeskey2 == "onlineTimestamp") $onlineTimestamp = $attributesvalue3;
				
				# standingOwnerID
				if ($attributeskey2 == "standingOwnerID") $standingOwnerID = $attributesvalue3;
			}


      # Nun jede POS eintragen
      
      # Prüfen ob der Character bereits in der Datenbank ist.
			$sqlPOSExists = "SELECT itemID from pos WHERE itemID = ".$itemID;
			$dataPOSExists = $link->query($sqlPOSExists);
			$count = $link->affected_rows;
			
			if ($count == 0)
			{
				# POS einfügen
				$sql = "INSERT into pos VALUES (".$itemID.", ".$typeID.", ".$locationID.", ".$moonID.", ".$state.", '".$stateTimestamp."', '".$onlineTimestamp."', ".$standingOwnerID.", 'j', ".$corpID.")";
				$dataInsert = $link->query($sql);
				echo $link->error;
				# Weitere Doings bei einer neuen POS
				# per PushOver informieren
			}
			else
			{
				# Sicherheitshalber die CorpID bei den Chars dieser Corp aktualisieren
				# So bleiben die Chars den korrekten Corps zugeteilt.
				$sql = "UPDATE pos SET state=".$state.", active='y', stateTimestamp=".$stateTimestamp.",onlineTimestamp=".$onlineTimestamp." WHERE intemID = ".$itemID;
			  $dataUpdate = $link->query($sql);
			}

			}
		}
	}



# nicht mehr existente POS löschen
$sql = "DELETE FROM pos WHERE active='n'";
$data = $link->query($sql);


# MySQL Ressourcen freigeben und Verbindung beenden
$link->close();

# Sonstige Ressourcen freigeben

unset($count);
unset($xmlfile);
unset($waittime);
unset($i);
unset($link);
unset($data);
unset($apiurl);
unset($all_api_call);
unset($info);
unset($preis);
unset($updatesql);
?>
