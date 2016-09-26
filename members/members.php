<?php
/**
 * members.php
 *
 * Aktualisiert die Mitgliedsliste einer Corporation
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
 * 26.01.2014: Erstellung und Test
 */
error_reporting(E_ALL);
include ("../includes/inc_connect.php");


# API-Adresse
$xmlUrlBasis = "https://api.eveonline.com/corp/MemberTracking.xml.aspx?keyID=KEYID&vCode=VCODE&extended=0";


# Jede Corp abfragen. Allerdings nur wenn keyID und vCode hinterlegt sind.
$sql = "SELECT * from corp WHERE keyID IS NOT NULL AND vcode IS NOT NULL ORDER BY corpIDIntern";
$data = $link->query($sql);

# Nun jede Corp abarbeiten
while($info = $data->fetch_assoc())
{
	$xmlurl = $xmlUrlBasis;
	# keyID
  $keyid = $info['keyid'];
  # vCode
  $vcode = $info['vcode'];
  
  $corpID = $info['corpID'];
  
  # API-String zusammenbauen
  $xmlurl = str_replace("KEYID", $keyid, $xmlurl);
  $xmlurl = str_replace("VCODE", $vcode, $xmlurl);
  
  # Daten der API abholen
  try {	
		$xml = simpleXML_load_file($xmlurl,"SimpleXMLElement",LIBXML_NOCDATA);
		
		$characterID = "";
		$name = "";
		
		# Chars aus der Corp nehmen
		$sql = "UPDATE chars set inCorp='n' WHERE corpID=".$corpID;
		$dataUpdate = $link->query($sql);
		
		# Jeden Membereintrag im XML durcharbeiten
		foreach($xml as $key0 => $value)
		{
			foreach($value as $key => $value2)
			{
				# Ab hier die Member-Eintraege
				foreach($value2 as $key2 => $value3)
				{
					foreach($value3->attributes() as $attributeskey2 => $attributesvalue3)
					{
		
						# Alle Werte auslesen
		
						# researccharacterIDhStartDate
						if ($attributeskey2 == "characterID") $characterID = $attributesvalue3;
		
						# name
						if ($attributeskey2 == "name") $name = $attributesvalue3;
					} # Ende eines Eintrages, ab hier die individuelle Behandlung
					
					
					# Prüfen ob der Character bereits in der Datenbank ist.
					$sqlCharExist = "SELECT charID from chars WHERE charID = ".$characterID;
					$dataCharExists = $link->query($sqlCharExist);
					$count = $link->affected_rows;
					
					if ($count == 0)
					{
						# Char einfügen
						$sql = "INSERT into chars VALUES (".$characterID.", '".$name."', now(), ".$corpID.", NULL, NULL, 'y')";
						$dataInsert = $link->query($sql);
						
						# Weitere Doings bei einem neuen Charakter
						# Name ermitteln
						# per PushOver informieren
					}
					else
					{
						# Sicherheitshalber die CorpID bei den Chars dieser Corp aktualisieren
						# So bleiben die Chars den korrekten Corps zugeteilt.
						$sql = "UPDATE chars SET corpID=".$corpID.", inCorp='y' WHERE charID = ".$characterID;
						$dataUpdate = $link->query($sql);
					}
				}
			}
		} # Ende ForEach
		
		
		
	} catch (Exception $e) {
  	echo 'Caught exception: ',  $e->getMessage(), "\n";
	} # Ende Catch Daten der API abholen
}


# Charactere welche nicht mehr in einer Corp sind entfernen.
# Das tatsächliche Löschen ist bis komplett durchdacht
$sql = "DELETE FROM chars WHERE inCorp='n'";
#$data = $link->query($sql);

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