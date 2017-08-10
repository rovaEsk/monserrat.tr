<?php
include "includes/header.php";
$scriptcourant = "lettres_en_relief.php";

if(isset($action) && $action == "add_regle_prix"){
	/*if($admin_droit[$scriptcourant][ecriture]){
        $DB_site->query("INSERT INTO prixarticlemodule (prixmodule, regleprix) VALUES ('$new_prixmodulehtc', '$new_regle_prix')");
        $lastPrix = $DB_site->insert_id();
        $DB_site->query("INSERT INTO articlemodule_prix (articlemoduleid, prixmoduleid) VALUES ('$id_module', '$lastPrix')");
        header('Location: '.$_SERVER['PHP_SELF']);
        die;    
	}else{
		header("HTTP/1.1 503 $multilangue[action_page_refuse]");
		exit;
	}*/
	
	if($admin_droit[$scriptcourant][ecriture]){
		$lettreMatiere = $DB_site->query_first("SELECT * FROM lettres_matieres WHERE id=$categorie_matiere");
		$couleur = $lettreMatiere[couleur];
		$nom = $new_epaisseur." mm";
        $DB_site->query("INSERT INTO epaisseurs_lettres (id_matiere, nom, epaisseur, couleurs, prix) VALUES ('$categorie_matiere', '$nom', '$new_epaisseur', '$couleur', '$new_prixmodulehtc')");
        /*$lastPrix = $DB_site->insert_id();
        $DB_site->query("INSERT INTO articlemodule_prix (articlemoduleid, prixmoduleid) VALUES ('$id_module', '$lastPrix')");*/
        header('Location: '.$_SERVER['PHP_SELF']);
        die;    
	}else{
		header("HTTP/1.1 503 $multilangue[action_page_refuse]");
		exit;
	}
}