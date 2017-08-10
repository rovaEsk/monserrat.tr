<?php
include "includes/header.php";

$scriptcourant = "gestion_onglets.php";

if(isset($action) && $action == "org"){
	if($admin_droit[$scriptcourant][ecriture]){
		$DB_site->query("DELETE FROM topmenu WHERE siteid='$idSite'");
		if($ordre != ""){
			$ordre = explode("|", $ordre);
			for($i=0;$i<sizeof($ordre);$i++){
				$position = $i+1;
				$DB_site->query("INSERT INTO topmenu (artid, position, siteid) VALUES ('$ordre[$i]','$position', '$idSite')");
			}
		}
	}else{
		header("HTTP/1.1 503 $multilangue[action_page_refuse]");
		exit;
	}
}


?>