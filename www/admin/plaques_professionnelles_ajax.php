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

if(isset($action) && $action == "edit_regle_prix"){
    if($admin_droit[$scriptcourant][ecriture]){
        /** edit regle des prix plaque pro **/

        $idTarifPlaque = $id_tarif_plaque;
        $prixModuleHtc = $prix_module_htc;
        $categorieDimensionPrix = $categorie_dimension_prix;
        $categorieReglePrix = $categorie_regle_prix;
        //exit($idTarifPlaque ." ".$prixModuleHtc ." ".$categorieDimensionPrix ." ".$categorieReglePrix);
        $dimensionQuery = $DB_site->query_first("SELECT * FROM categorie_dimension_module WHERE categoriedimensionid='$categorieDimensionPrix'");
        $dimension = $dimensionQuery[dimension_prix];
        $dimensionId = $dimensionQuery[categoriedimensionid];
        $arrayDimension = explode("x", $dimension);
        $longueur = $arrayDimension[0];
        $hauteur = $arrayDimension[1];

        $DB_site->query("UPDATE tarifs_plaques SET
						prix_constate = '$prixModuleHtc',
						dimensions = '$categorieDimensionPrix',
						id_matiere = '$categorieReglePrix',
						longueur = '$longueur',
						hauteur = '$hauteur'
						WHERE id ='$idTarifPlaque'");
        header("location: plaques_professionnelles.php");
        /** edit regle des prix plaque pro  **/
    }else{
        header("HTTP/1.1 503 $multilangue[action_page_refuse]");
        exit;
    }
}

