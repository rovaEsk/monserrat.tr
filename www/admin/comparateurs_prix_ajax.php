<?php
	include "includes/header.php";
	
	$scriptcourant = "comparateurs_prix.php";

	if (isset($action) && $action == "parametre"){
		if($admin_droit[$scriptcourant][ecriture]){
			$first = true;
			foreach($_POST as $key => $value){
				if(!$first){
					$parametre = $key;
					if($value == "false"){
						$valeur=0;
					}else{
						$valeur=1;
					}
					$DB_site->query("UPDATE parametre SET valeur = '$valeur' WHERE parametre = '$parametre'");
				}	
				$first=false;
			}
		}else{
			header("HTTP/1.1 503 $multilangue[action_page_refuse]");
			exit;
		}
	}

	if (isset($action) && $action == "auto"){
		if($admin_droit[$scriptcourant][ecriture]){
			$auto = ($color == "green" ? 0 : 1);
			$existe = $DB_site->query("SELECT * FROM comparateur_site WHERE comparateurid='$comparateurid' AND siteid='$comparateursiteid'");
			if($DB_site->num_rows($existe) == 0){
				$DB_site->query("INSERT INTO comparateur_site (comparateurid, siteid, auto, auto_sel) VALUES ('$comparateurid','$comparateursiteid','$auto','0')");
				$tooltipvisible = $multilangue[desactiver];
			}else{
				$DB_site->query("UPDATE comparateur_site SET auto = '$auto' WHERE comparateurid = '$comparateurid' AND siteid = '$comparateursiteid'");
				$color = ($color == "green" ? "red" : "green");
				$tooltipvisible = ($color == "green" ? $multilangue[desactiver] : $multilangue[activer]);
			}
			echo $action . "_" . $comparateurid . "_" . $comparateursiteid . "_" . $color . "_" . $tooltipvisible;
		}else{
			header("HTTP/1.1 503 $multilangue[action_page_refuse]");
			exit;
		}
	}
	
	if (isset($action) && $action == "autosel"){
		if($admin_droit[$scriptcourant][ecriture]){
			$autosel = ($color == "green" ? 0 : 1);
			$existe = $DB_site->query("SELECT * FROM comparateur_site WHERE comparateurid='$comparateurid' AND siteid='$comparateursiteid'");
			if($DB_site->num_rows($existe) == 0){
				$DB_site->query("INSERT INTO comparateur_site (comparateurid, siteid, auto, auto_sel) VALUES ('$comparateurid','$comparateursiteid','0','$autosel')");
				$tooltipvisible = $multilangue[desactiver];
			}else{
				$DB_site->query("UPDATE comparateur_site SET auto_sel = '$autosel' WHERE comparateurid = '$comparateurid' AND siteid = '$comparateursiteid'");
				$color = ($color == "green" ? "red" : "green");
				$tooltipvisible = ($color == "green" ? $multilangue[desactiver] : $multilangue[activer]);
			}
			echo $action . "_" . $comparateurid . "_" . $comparateursiteid . "_" . $color . "_" . $tooltipvisible;
		}else{
			header("HTTP/1.1 503 $multilangue[action_page_refuse]");
			exit;
		}
	}
?>