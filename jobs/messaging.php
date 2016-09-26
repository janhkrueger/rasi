<?php
/**
 * messaging.php
 *
 * Benachrichtigung über abgeschlossene Forschungsaufträge
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
 * 20.01.2014: Umstellung auf mysqli
 * 21.01.2014: Korrektur beim BPO-Namen aufgrund Umstellungsfehler vom 20.01.
 * 21.01.2014: Aufnahme der Lizenzen in die Kopiemeldung
 * 01.02.2014: Ausschluss von Pen Pecunia aus der Aktualisierung
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

include ("../includes/inc_connect.php");
include ("../xmpphp/XMPPHP/XMPP.php");


# Aktualisierung  BPO
# Es kann jedoch sein das eine BPO direkt in die Forschung gegeben wurde und daher
# Nicht in den Assets auftauchte.
# Daher muss eine BPO ggf. auch per INSERT bekannt gemacht werden, nicht nur per
# UPDATE aktualisiert.
function updateBPO($info, $me, $pe) {

	$itemID = $info['installedItemID'];

	# Prüfen ob die BPO bereits eingetragen wurde.
	$sql = "SELECT * from bpolevels WHERE itemID = $itemID";
	global $dbh;
	$stmt = $dbh->prepare($sql);
	$stmt->execute();
	$anzahl = $stmt->rowCount();

	$typeID = $info['installedItemTypeID'];
	$jetzt = date("Y-m-d H:i:s", time());

	# Nicht vorhanden dann Insert
	if ( $anzahl == 0 ) {
			$sql = "INSERT INTO bpolevels (itemID, typeID, me, pme, pe, ppe, runs, added, stock, ek, vk, sold, income, profit, chaching, chachingupdate, history_id, hist_date, bpocomment, owner) VALUES (:itemid, :typeid, :me, 0, :pe, 0, 0, '$jetzt', 0, 0, 50000, 0, 0, 0, NULL, '0000-00-00 00:00:00', 1, '$jetzt', NULL, 1676205336)";
			$stmt = $dbh->prepare($sql);
			$stmt->bindParam(':itemid', $itemID, PDO::PARAM_INT);
			$stmt->bindParam(':typeid', $typeID, PDO::PARAM_INT);
			$stmt->bindParam(':me', $me, PDO::PARAM_INT);
			$stmt->bindParam(':pe', $pe, PDO::PARAM_INT);
			
			# Ausgabe der SQL-Fehlermeldungen sollte der Insert nicht
			# erfolgreich gewesen sein.
			if ( ! $stmt->execute() )
			{
				print "State: ".$stmt->errorInfo()."<br>";
				#print "Driver: ".$stmt->errorInfo()[1]."<br>";
				#print "message: ".$stmt->errorInfo()[2]."<br><br>";
				print $sql;
				exit;
			}
	} # Ende IF
	# Update des BPOs wenn er bereits in bpolevels existiert
	else {
		$sql = "UPDATE bpolevels SET me = :me, pe = :pe WHERE itemID = :itemid";
		$stmt = $dbh->prepare($sql);
		$stmt->bindParam(':itemid', $itemID, PDO::PARAM_INT);
		$stmt->bindParam(':me', $me, PDO::PARAM_INT);
		$stmt->bindParam(':pe', $pe, PDO::PARAM_INT);
		# Ausgabe der SQL-Fehlermeldungen sollte der Insert nicht
		# erfolgreich gewesen sein.
		if ( ! $stmt->execute() )
		{
			print "State: ".$stmt->errorInfo()."<br>";
			#print "Driver: ".$stmt->errorInfo()[1]."<br>";
			#print "message: ".$stmt#>errorInfo()[2]."<br><br>";
			print $sql;
			exit;
		}
		
	} # Ende ELSE
} # Ende Function updateBPO()


# Aktualisieren der Lagerzahl einer BPO
function updateStock($info) {
	$itemID = $info['installedItemID'];
	$runs = $info['runs'];
	$sql = "UPDATE bpolevels SET stock = stock+:runs WHERE itemID = :itemid";	
	
	global $dbh;
	$stmt = $dbh->prepare($sql);
	$stmt->bindParam(':runs', $runs, PDO::PARAM_INT);
	$stmt->bindParam(':itemid', $itemID, PDO::PARAM_INT);	
		# Ausgabe der SQL-Fehlermeldungen sollte der Insert nicht
		# erfolgreich gewesen sein.
		if ( ! $stmt->execute() )
		{
			print "State: ".$stmt->errorInfo()."<br>";
			#print "Driver: ".$stmt->errorInfo()[1]."<br>";
			#print "message: ".$stmt->errorInfo()[2]."<br><br>";
			print $sql;
			exit;
		}
	}

# Default für welche Corp die Werte ausgelesen werden.
$corp = "98190387";


# Fertige Jobs ermitteln
$sql = "SELECT j.*, c.name as charname FROM jobs j, chars c WHERE j.installerID=c.charID AND (CONVERT_TZ(j.endProductionTime,'+00:00','+1:00') < now()) AND j.completedSuccessfully = 0";
#$sql = "SELECT j.*, c.name as charname FROM jobs j, chars c WHERE j.installerID=c.charID AND (CONVERT_TZ(j.endProductionTime,'+00:00','+1:00') < now())";
$stmt = $dbh->prepare($sql);
$stmt->execute();


# jeden fertigen Job durchgehen.
while($info = $stmt->fetch(PDO::FETCH_ASSOC) )
{
	# BPO-Namen ermitteln
	$typeid = $info['installedItemTypeID'];
	$sql = "select typeName FROM invTypes WHERE typeID = $typeid";
	$dataname = $link->query($sql);
	$bponame = $dataname->fetch_assoc();
	$bponame = $bponame['typeName'];
	$dataname->free();

	# Nun eine Unterscheidung zwischen Forschung und Copyjob
	# 3,'Researching Time Productivity'
        # 4,'Researching Material Productivity'
        # 5,'Copying'
	$activityID = $info['activityID'];
	$lizenzen = $info['licensedProductionRuns'];


  # Auftraggeber ermitteln
  $charname = $info['charname'];
  $me = $info['installedItemMaterialLevel'];
  $pe = $info['installedItemProductivityLevel'];
 	$runs = $info['runs'];

	# Text für PE-Forschung
	if ($activityID == 3) {
		$title = "PE: ".$bponame." ist erforscht";
		$message = "Die PE-Stufe beträgt nun ".($pe+$runs).". Vorher: ".$pe.".";
		# PE für die spätere Aktualisierung anpassen
		$pe = $pe+$runs;
	}
	# Text für ME-Forschung
	if ($activityID == 4) {
		$title = "ME: ".$bponame." ist erforscht";
		$message = "Die ME-Stufe beträgt nun ".($me+$runs).". Vorher: ".$me.".";
		# ME für die spätere Aktualisierung anpassen
		$me = $me + $runs;
	}
	# Text für Copys
	if ($activityID == 5) {
		$title = "Kopie von ".$bponame." ist erstellt.";
		$message = "Es wurden ".$runs." Kopien mit je ".$lizenzen." Lizenzen angefertigt.";
	}

  # Noch anfügen welcher Charakter den Job gestaret hat
  $message .= "<br>Gestartet von: ".$charname;

	# Nun die Nachricht rausjagen
	try {
        $conn = new XMPPHP_XMPP('jabber.host', 5222, 'user', 'PASS', 'xmpphp', 'host', $printlog=false,     $loglevel=XMPPHP_Log::LEVEL_INFO);	    
        $conn->connect();
	    $conn->processUntil('session_start');
	    $conn->presence();
	    $conn->useEncryption(TRUE);
	    $conn->message('to', '<b>'.$title.'</b><br><br>'.$message);
	    $conn->disconnect();
	} catch(XMPPHP_Exception $e) {
	    print ("Konnte Nachricht nicht versenden.");
	    die($e->getMessage());
	}
	

	# Job als bereits gemeldet markieren damit er nicht erneut gemeldet wird
	$jobid = $info['jobID'];
	$updatesql = "UPDATE jobs SET completedSuccessfully=99 WHERE jobid=$jobid";
	$link->query($updatesql);

	# Aktualisiere die BPO in der zentralen Liste.
	# BPOs des Charakters Pen Pecunia werden ausgeschlossen
#  if ( $info['installerID'] != 94145064) {
		# Ermittle ob die BPO bereits existiert.
		updateBPO($info, $me, $pe);
		
		# Nun noch die Lageranzahl aktualisieren
		if ($activityID == 5) {
			updateStock($info);
		}	
#	}

}

# MySQL Ressourcen freigeben und Verbindung beenden
include ("../includes/close.php");


# Sonstige Ressourcen freigeben
unset($count);
unset($xmlfile);
unset($waittime);
unset($i);
unset($link);
unset($apiurl);
unset ($charname);
unset($all_api_call);
unset($info);
unset($preis);
unset($updatesql);
?>
