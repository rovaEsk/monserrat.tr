<?php
include "./includes/header.php";

$referencepage="gestion_logos";
$pagetitle = "Gestion des logos - $host - Admin Arobases";


if(!parse_template($referencepage, $langue)){
	echo "erreur de chargement de template";
}

if(isset($erreurdroits) and $erreurdroits == 1){
	$texteErreur = $multilangue[action_page_refuse];
	eval(charge_template($langue, $referencepage, "Erreur"));
}

// AJOUTER UN FAVICON
if (isset($action) and $action == "ajoutFavicon") {
	if($admin_droit[$scriptcourant][ecriture]){
		if (!empty($_FILES['favicon']['name'])) {
			$listeTypesAutorise = array("image/png", "image/x-icon");
			erreurUpload("favicon",$listeTypesAutorise,1048576);
			echo $erreur;
			if($erreur==""){
				$nom_fic = $rootpath."configurations/$host/images/favicon.ico";
				copier_image($nom_fic,'favicon');
				$erreur = $multilangue[image_modifiee];
			}
		}else{
		$erreur .= "$multilangue[erreur_chargement_fichier]<br>" ;
		}
		header("location: gestion_logos.php?success2=success");
	}else{
		header('location: gestion_logos.php?erreurdroits=1');	
	}
}

// AJOUTER UNE IMAGE LOGO
if (isset($action) and $action == "ajoutLogoFacture") {
	if($admin_droit[$scriptcourant][ecriture]){
		$erreur = "" ;
		if (!empty($_FILES['logo']['name']))  {
			//indiquer dans ce tableau les type de fichier autoris� pour l'upload.
			$listeTypesAutorise = array("image/pjpeg","image/jpeg");
			erreurUpload("logo",$listeTypesAutorise,1048576);
			if($erreur=="") {
				$type_fichier=define_extention($_FILES['logo']['name']);
				$nom_fic = $rootpath."configurations/$host/factures/logotmp.jpg";
				$destination = $rootpath."configurations/$host/factures/logo.jpg";
				copier_image($nom_fic,'logo');
				redimentionner_image($nom_fic, $destination, 150, 80);
			}
		} else {
			$erreur .= "$multilangue[erreur_chargement_fichier]<br>" ;
		}
		$lastfile = "";
		header("location: gestion_logos.php?success1=success");
	}else{
		header('location: gestion_logos.php?erreurdroits=1');	
	}
}


if ( !isset($action) || $action == ""){
	if ($success1 == "success"){
		$texteSuccess = $multilangue[le_logo]." ".$multilangue[a_bien_ete_edite];
	eval(charge_template($langue,$referencepage,"Success"));
	}
	if ($success2 == "success"){
		$texteSuccess = $multilangue[le_favicon]." ".$multilangue[a_bien_ete_edite];
		eval(charge_template($langue,$referencepage,"Success"));
	}
}

$TemplateIncludejavascript = eval(charge_template($langue, $referencepage,"Includejavascript"));
eval(charge_template($langue,"commun","header"));
eval(charge_template($langue,"commun","footer"));
eval(charge_template($langue,$referencepage,"index"));

$nomtemplateindex="Template".ucfirst($referencepage)."Index";
echo ${$nomtemplateindex};



$DB_site->close();
flush();

?>