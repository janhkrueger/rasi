<?php

# Aufbau der POD Datenverbindung
# @TODO Muss noch parametrisiert werden.
include ("../includes/inc_connect.php");

$dbh = new PDO('mysql:host=localhost;dbname=', '', '');

$vcode="YOURAPIVCODE";
$keyid="YOURAPIKEY";
$xmlurl = "https://api.eveonline.com/Corp/Blueprints.xml.aspx?keyID=&vCode=";

# Pruefen wann der letzte Call war
$sql = "SELECT cacheduntil FROM apicalls WHERE apiname='Blueprints'";
foreach ($dbh->query($sql) as $info) {
    $cacheduntil = $info['cacheduntil'];
}

# Aktuelle UTC-Zeit ermitteln
date_default_timezone_set("UTC");
$timenow = date("Y-m-d H:i:s", time());

# Nur wenn der Cache abgelaufen ist, darf neu gearbeitet werden
if ( $cacheduntil <= $timenow ) {
	# Lokale Kopie einer BlueprintAusgabe fuer den Test
	$xml = simpleXML_load_file($xmlurl,"SimpleXMLElement",LIBXML_NOCDATA);
	#$xml = simpleXML_load_file("BlueprintsCorp.xml","SimpleXMLElement",LIBXML_NOCDATA);

	# BlueprintTabelle leeren
	$dbh->beginTransaction();
	$sql = "DELETE FROM bpos";
	$stmt = $dbh->prepare($sql);
	$stmt->execute();
	$dbh->commit();

	# Variablen Initiieren
	$itemID = "-999";
	$locationID = "-999";
	$typeID = "-999";
	$quantity = "";
	$flagID = "-999";

	# das neue CachedUntil ermitteln und in Datenbank schreiben
	foreach ($xml->cachedUntil as $cacheduntil)
	{
		$sql = "UPDATE apicalls SET cacheduntil = '$cacheduntil[0]', abgefragt=now() WHERE apiname='Blueprints'";
		$stmt = $dbh->prepare($sql);
		if ( ! $stmt->execute() )
			{
			    echo $sql;
			    print_r($stmt->errorInfo());
			    exit;
			}
	} # Ende ForEach CachedUntil

foreach ($xml->result->rowset->row as $container)
{
	$itemID = "-999";
	$locationID = "-999";
	$typeID = "-999";
	$typeName = "";
	$flagID = "-999";
	$quantity = "-999";
	$timeEfficiency = "-999";
	$materialEfficiency = "-999";
	$runs = "-999";


	$itemID = $container['itemID'];
	$locationID = $container['locationID'];
	$typeID = $container['typeID'];
	$typeName = addslashes($container['typeName']);
	$flag = $container['flag'];
	$quantity = $container['quantity'];
	$timeEfficiency = $container['timeEfficiency'];
	$materialEfficiency = $container['materialEfficiency'];
	$runs = $container['runs'];

	if ($locationID == "") $locationID = 0;

	$sql = "INSERT INTO bpos (itemID,  locationID,  typeID,  typeName, flagID,   quantity,  timeEfficiency,  materialEfficiency, runs) VALUES ($itemID, $locationID, $typeID, '$typeName', $flagID, $quantity, $timeEfficiency, $materialEfficiency, $runs)";
	$stmt = $dbh->prepare($sql);
	if ( ! $stmt->execute() )
	{
	    echo $sql;
	    print_r($stmt->errorInfo());
	    exit;
	}

  if (!empty($container->rowset))
  {
		foreach ($container->rowset->row as $item)
		{
			$itemID = "-999";
			$locationID = "-999";
			$typeID = "-999";
			$typeName = "";
			$flagID = "-999";
			$quantity = "-999";
			$timeEfficiency = "-999";
			$materialEfficiency = "-999";
			$runs = "-999";


			$itemID = $container['itemID'];
			$locationID = $container['locationID'];
			$typeID = $container['typeID'];
			$typeName = $container['typeName'];
			$flag = $container['flag'];
			$quantity = $container['quantity'];
			$timeEfficiency = $container['timeEfficiency'];
			$materialEfficiency = $container['materialEfficiency'];
			$runs = $container['runs'];

			if ($rawQuantity == "") $rawQuantity = 0;
			if ($locationID == "") $locationID = 0;

			# Jetzt alle BPOs wieder in die DB laden
			$sql = "INSERT INTO bpos (itemID,  locationID,  typeID,  typeName, flagID,   quantity,  timeEfficiency,  materialEfficiency, runs) VALUES ($itemID, $locationID, $typeID, '$typeName', $flagID, $quantity, $timeEfficiency, $materialEfficiency, $runs)";
			$stmt = $dbh->prepare($sql);

			if ( ! $stmt->execute() )
			{
				echo $sql;
				print_r($stmt->errorInfo());
				exit;
			}

		    if (!empty($item->rowset))
		    {
					$dbh->beginTransaction();
	        foreach ($item->rowset->row as $item2)
	        {
						$itemID = "-999";
						$locationID = "-999";
						$typeID = "-999";
						$typeName = "";
						$flag = "-999";
						$quantity = "-999";
						$timeEfficiency = "-999";
						$materialEfficiency = "-999";
						$runs = "-999";


						$itemID = $item2['itemID'];
						$locationID = $item2['locationID'];
						$typeID = $item2['typeID'];
						$typeName = $item2['typeName'];
						$flag = $item2['flag'];
						$quantity = $item2['quantity'];
						$timeEfficiency = $item2['timeEfficiency'];
						$materialEfficiency = $item2['materialEfficiency'];
						$runs = $item2['runs'];

						if ($rawQuantity == "") $rawQuantity = 0;
						if ($locationID == "") $locationID = 0;



	$sql = "INSERT INTO bpos (itemID,  locationID,  typeID,  typeName, flag,   quantity,  timeEfficiency,  materialEfficiency, runs) VALUES ($itemID, $locationID, $typeID, '$typeName', $flag, $quantity, $timeEfficiency, $materialEfficiency, $runs)";
	$stmt = $dbh->prepare($sql);

						if ( ! $stmt->execute() )
						{
						    echo $sql;
						    print_r($stmt->errorInfo());
						    exit;
						}
						createBPOvalues($itemID, $typeID, $rawQuantity);
	        }
	        $dbh->commit();
		    }


      }
  }
}


} # Ende ( $cacheduntil <= $timenow && $bla = FALSE)







include ("../includes/close.php");

?>
