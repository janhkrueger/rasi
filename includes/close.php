<?php

try {
#	$data->free();
} catch (Exception $e) {
}


try {
#	$stmt->closeCursor();
} catch (Exception $e) {
    	#echo 'Exception abgefangen: ',  $e->getMessage(), "\n";
}

try {
#	$dbh = null;
} catch (Exception $e) {
    	#echo 'Exception abgefangen: ',  $e->getMessage(), "\n";
}

?>
