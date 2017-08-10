<?php
	include "includes/header.php";
	$scriptcourant = "ip_bloquees.php";

	if (isset($action) and $action == "pays"){
		if($admin_droit[$scriptcourant][ecriture]){
			if ($action2 != "tous" && $action2 != "aucun"){
				
				if($action2 == "select"){
					$cases_cochees_tab = explode("||",$cases_cochees);
					foreach ($cases_cochees_tab as $paysid){
						if($paysid != ""){
							$pays = $DB_site->query_first("SELECT * FROM pays_bloque WHERE paysid = '$paysid'");
							if ($pays[paysid] != ""){
								/*$DB_site->query("DELETE FROM pays_bloque WHERE paysid = '$paysid'");
								echo "DELETE FROM pays_bloque WHERE paysid = '$paysid'";*/
							}else{
								$DB_site->query("INSERT INTO pays_bloque SET paysid = '$paysid'");
								//echo "INSERT INTO pays_bloque SET paysid = '$paysid'";
							}
						}
					}
				}else{
					$pays = $DB_site->query_first("SELECT * FROM pays_bloque WHERE paysid = '$paysid'");
					if ($pays[paysid] != "")
						$DB_site->query("DELETE FROM pays_bloque WHERE paysid = '$paysid'");
					else
						$DB_site->query("INSERT INTO pays_bloque SET paysid = '$paysid'");
				}		
			}else{
				if ($action2 == "tous"){
					$payss = $DB_site->query("SELECT * FROM pays");
					while ($pays = $DB_site->fetch_array($payss)){
						$DB_site->query("INSERT INTO pays_bloque SET paysid = '$pays[paysid]'");
					}
				}else if ($action2 == "aucun"){
					$DB_site->query("DELETE FROM pays_bloque");
				}
			}
		}else{
			header("HTTP/1.1 503 $multilangue[action_page_refuse]");
			exit;
		}
	}
?>