<?php
include "includes/header.php";
$scriptcourant = "plaques_professionnelles.php";

if(isset($action) && $action == "add_regle_prix"){
	if($admin_droit[$scriptcourant][ecriture]){
	   /** insert regle des prix plaque pro **/
        $prixmodulehtc = $new_prixmodulehtc;
        $categorieDimesion = $categorie_dimension_prix;
        $categorieregleprix = $categorie_regle_prix;
        #insert prix dans  tarifs_plaques >>  prix /dimension : long & haut / id_matiere
        $dimensionQuery = $DB_site->query_first("SELECT * FROM categorie_dimension_module WHERE categoriedimensionid='$categorieDimesion'"); 
        $dimension = $dimensionQuery[dimension_prix];
        $dimensionId = $dimensionQuery[categoriedimensionid];
        $arrayDimension = explode("x", $dimension);
        $longueur = $arrayDimension[0];
        $hauteur = $arrayDimension[1];
        $DB_site->query("INSERT INTO tarifs_plaques (prix_constate, longueur, hauteur, dimensions, id_matiere) VALUES ('$prixmodulehtc','$longueur', '$hauteur', '$dimensionId', '$categorieregleprix')");
       
        header("location: plaques_professionnelles.php");
        /** insert regle des prix plaque pro  **/      
	}else{
		header("HTTP/1.1 503 $multilangue[action_page_refuse]");
		exit;
	}
}

