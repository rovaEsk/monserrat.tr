<?php
include "includes/header.php";
$scriptcourant = "panneaux.php";

if(isset($action) && $action == "add_regle_prix"){
	if($admin_droit[$scriptcourant][ecriture]){
	   /** insert regle des prix **/
        $DB_site->query("INSERT INTO prixarticlemodule (prixmodule, regleprix, largeur, hauteur) VALUES ('$new_prixmodulehtc', '$new_regle_prix', '$largeur', '$hauteur')");
        $lastPrix = $DB_site->insert_id();
        $DB_site->query("INSERT INTO articlemodule_prix (articlemoduleid, prixmoduleid) VALUES ('$id_module', '$lastPrix')");
        header("location: panneaux.php");
        /** insert regle des prix **/      
	}else{
		header("HTTP/1.1 503 $multilangue[action_page_refuse]");
		exit;
	}
}
?>