<?
header('Content-type: text/html; charset=iso-8859-1'); 
require_once("includes/admin_global.php");

$scriptcourant = "frais_port.php";

if ($action == "changedefaut" || $action == "changedefautzone") {
	$erreur = "";
	if (is_numeric($prix) && $prix > 0) {
		if ($action == "changedefaut"){
			if($admin_droit[$scriptcourant][ecriture]){
				$DB_site->query("UPDATE fraisport SET prix = '$prix' WHERE paysid = '$paysid' AND modelivraisonid = '$modelivraisonid' AND debut = '0' AND fin = '0'");
			}else{
				header("HTTP/1.1 503 $multilangue[action_page_refuse]");
				exit;
			}
		}else{
			if($admin_droit[$scriptcourant][suppression]){
				$DB_site->query("DELETE FROM fraisport 
								WHERE paysid IN (SELECT paysid FROM zone_pays
								WHERE zoneid = '$paysid') 
								AND modelivraisonid = '$modelivraisonid' AND debut = '0' AND fin = '0'");
				$DB_site->query("INSERT INTO fraisport(paysid, modelivraisonid, debut, fin, prix) 
								SELECT pays.paysid, '$modelivraisonid', '0', '0', '$prix'
								FROM pays INNER JOIN zone_pays USING(paysid)
								WHERE zoneid = '$paysid'"); 
				$DB_site->query("INSERT INTO pays_select ( paysid) 
								SELECT paysid
								FROM zone_pays
								WHERE zoneid = '$paysid' AND paysid NOT IN (SELECT paysid FROM pays_select)");
				$DB_site->query("UPDATE fraisport SET prix = '$prix'
								WHERE paysid IN
								(SELECT paysid FROM zone_pays WHERE zoneid = '$paysid')
								AND modelivraisonid = '$modelivraisonid' AND debut = '0' AND fin = '0'");
			}else{
				header("HTTP/1.1 503 $multilangue[action_page_refuse]");
				exit;
			}
		}
	}else{
		$erreur .= "$multilangue[erreur_montant]." ;
	}												
	if ($erreur != "") {
		echo $erreur;	
	}
}else{
	if ($params[type_tranches_port] == 0) {
		$unite = "gr.";
		$type_tranches_port = "poids";
	} else {
		$unite = "&euro;";
		$type_tranches_port = "prix";
	}
	if (isset($action) and ($action == "ajouter" || $action == "ajouterzone")) {
		if($admin_droit[$scriptcourant][ecriture]){
			$erreur = "";
			if ($debut == "" or $fin == "" or $prix == "") {
				$erreur = "$multilangue[devez_choisir_un] $type_tranches_port $multilangue[minimum_maximum_euro].";
			}else{
				if (!is_numeric($debut) or !is_numeric($fin) or !is_numeric($prix)) {
					$erreur = "$multilangue[devez_choisir_un] $type_tranches_port $multilangue[minimum_maximum_euro_numeriques].";
				}else{
					if ($debut < 0 or $fin < 0 or $prix < 0) {
						$erreur = "$multilangue[devez_choisir_un] $type_tranches_port $multilangue[minimum_maximum_euro_numeriques].";
					}else{
						if ($fin - $debut < 0)
							$erreur = "$multilangue[le] $type_tranches_port $multilangue[de_debut] $multilangue[doit_etre_inferieur_au] $type_tranches_port $multilangue[de_fin].";
					}	
				}	
			}	
			if ($erreur == "") {
				$debut = round($debut, 2);
				$fin = round($fin, 2);
				if ($action == "ajouter") {
					$requete = "SELECT count(*) count FROM fraisport 
								WHERE paysid = '$paysid' AND modelivraisonid = '$modelivraisonid' AND(
								(debut >= '$debut' AND fin <= '$fin')
								OR (debut <= '$debut' AND fin >= '$fin')
								OR (debut <= '$debut' AND fin BETWEEN '$debut' AND '$fin')
								OR (fin >= '$fin' AND debut BETWEEN '$debut' AND '$fin'))";
				}else{
					$requete = "SELECT COUNT(*) count FROM fraisport
								WHERE paysid IN (SELECT paysid FROM zone_pays WHERE zoneid = '$paysid')
								AND modelivraisonid = '$modelivraisonid'
								AND((debut >= '$debut' AND fin <= '$fin')
								OR (debut <= '$debut' AND fin >= '$fin') OR (debut <= '$debut' AND fin BETWEEN '$debut' AND '$fin')
								OR (fin >= '$fin' AND debut BETWEEN '$debut' AND '$fin'))";
				}
				$count = $DB_site->query_first($requete);
				if ($count[count] > 0) {
					$erreur = "$multilangue[l_intervalle] $type_tranches_port $multilangue[entre_en_conflit].";
				}else{
					if ($action == "ajouter") {
						$DB_site->query("INSERT INTO fraisport (debut, fin, prix, paysid, modelivraisonid) 
										VALUES ('$debut', '$fin', '$prix', '$paysid', '$modelivraisonid')");
						$fraisportid = $DB_site->insert_id();
					}else{
						$DB_site->query("DELETE FROM fraisport 
										WHERE paysid IN (SELECT paysid FROM zone_pays WHERE zoneid = '$paysid')  
										AND modelivraisonid = '$modelivraisonid'
										AND debut BETWEEN '$debut' AND '$fin' AND fin BETWEEN '$debut' AND '$fin'");
						$DB_site->query("INSERT INTO fraisport (paysid, modelivraisonid, debut, fin, prix) 
										SELECT pays.paysid, '$modelivraisonid', '$debut', '$fin', '$prix' 
										FROM pays INNER JOIN zone_pays USING(paysid)
										WHERE zoneid = '$paysid'");
						$fraisportid = $DB_site->insert_id();
						$DB_site->query("INSERT INTO pays_select (paysid) 
										SELECT paysid
										FROM zone_pays
										WHERE zoneid = '$paysid' AND paysid NOT IN (SELECT paysid FROM pays_select)");
					}
					$fraisport = $DB_site->query_first("SELECT * FROM fraisport WHERE fraisportid = '$fraisportid'");
					echo "$fraisport[fraisportid]_$fraisport[debut]_$fraisport[fin]_$fraisport[prix]";
				}
			}	
		}else{
			header("HTTP/1.1 503 $multilangue[action_page_refuse]");
			exit;
		}
	}
	if (isset($action) and ($action == "supprimer" || $action == "supprimerzone")) {
		if($admin_droit[$scriptcourant][suppression]){
			if (is_numeric($fraisportid)) {
				if ($action == "supprimer") { 
					$DB_site->query("DELETE FROM fraisport WHERE fraisportid = '$fraisportid'");
				}else{
					$DB_site->query("DELETE FROM fraisport WHERE paysid IN
									(SELECT paysid FROM zone_pays WHERE zoneid = '$paysid')
									AND modelivraisonid = '$modelivraisonid'
									AND debut = '$debut' AND fin = '$fin'");
				}
			}
		}else{
			header("HTTP/1.1 503 $multilangue[action_page_refuse]");
			exit;
		}
	}
	if (isset($action) and $action == "supprimermode") {
		if($admin_droit[$scriptcourant][suppression]){
			if (is_numeric($paysid) && is_numeric($modelivraisonid)) {
				$DB_site->query("DELETE FROM fraisport WHERE paysid = '$paysid' AND modelivraisonid = '$modelivraisonid' AND (debut != 0 OR fin != 0)");
				$DB_site->query("UPDATE fraisport SET prix = '0' WHERE paysid = '$paysid' AND modelivraisonid = '$modelivraisonid' AND debut = 0 AND fin = 0");
				$DB_site->query("DELETE FROM fraisport WHERE paysid IN (SELECT paysid FROM zone_pays WHERE zoneid = '$paysid') AND modelivraisonid = '$modelivraisonid'");
			}
		}else{
			header("HTTP/1.1 503 $multilangue[action_page_refuse]");
			exit;
		}
	}	
	if (isset($action) and ($action == "modifier" || $action == "modifierzone")) {
		if($admin_droit[$scriptcourant][ecriture]){
			if (is_numeric($prix) && is_numeric($fraisportid)) {
				if ($action == "modifier") {
					$DB_site->query("UPDATE fraisport SET prix = '$prix' WHERE fraisportid = '$fraisportid'");
				}else{
					$DB_site->query("UPDATE fraisport SET prix = '$prix' WHERE paysid IN
									(SELECT paysid FROM zone_pays WHERE zoneid = '$paysid')
									AND modelivraisonid = '$modelivraisonid' AND debut = '$debut' AND fin = '$fin'");
				}
			}
		}else{
			header("HTTP/1.1 503 $multilangue[action_page_refuse]");
			exit;
		}
	}	
	if ($erreur != "")
		echo $erreur;
}
	
?>