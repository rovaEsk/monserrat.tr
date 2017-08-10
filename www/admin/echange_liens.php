<?php
include "./includes/header.php";

$referencepage="echange_liens";
$pagetitle = "Echange de liens - $host - Admin Arobases";

if(!parse_template($referencepage, $langue)){
	echo "erreur de chargement de template";
}

if(isset($erreurdroits) and $erreurdroits == 1){
	$texteErreur = $multilangue[action_page_refuse];
	eval(charge_template($langue, $referencepage, "Erreur"));
}

if (isset($action) and $action == "modifier2"){
	if($admin_droit[$scriptcourant][ecriture]){
		if ($lienid == ""){
			foreach($_POST[siteid] as $key){
				$DB_site->query("INSERT INTO liens(lienid, siteid) VALUES ('', '$key')");
				$lienid = $DB_site->insert_id();
				$DB_site->query("UPDATE liens SET libelle = '" . securiserSql($_POST[libelle]) ."',
						lien = '" . securiserSql($_POST[lien]) ."',
						title = '" . securiserSql($_POST[title]) ."',
						description = '" . securiserSql($_POST[description]) ."',
						script = '" . securiserSql($_POST[script]) . "',
						siteid = '$key' WHERE lienid = '$lienid'");
				
				if (!empty($_FILES['logo']['name'])) {
					$largeur_max= "240";
					$hauteur_max = "170";
					$listeTypesAutorise = array("image/pjpeg", "image/jpeg", "image/gif", "image/png");
					erreurUpload("logo", $listeTypesAutorise, 1048576);
					$taille = GetImageSize($_FILES['logo']['tmp_name']);
					if ($taille[0] <= $largeur_max && $taille[1] <= $hauteur_max && $erreur == ""){
						$type_fichier = define_extention($_FILES['logo']['name']);
						$DB_site->query("UPDATE liens SET logo = '$type_fichier' WHERE lienid = '$lienid'");
						$nom_fichier = $rootpath . "configurations/$host/images/liens/" . $lienid . "." . $type_fichier;
						copier_image($nom_fichier, 'logo');
					}else{
						$texteErreur = "$multilangue[taille_image_non_conforme].";
						eval(charge_template($langue, $referencepage, "Erreur"));
						$action = "modifier";
					}
				}
			}
		}else{
			$DB_site->query("UPDATE liens SET libelle = '" . securiserSql($_POST[libelle]) ."',
							lien = '" . securiserSql($_POST[lien]) ."',
							title = '" . securiserSql($_POST[title]) ."',
							description = '" . securiserSql($_POST[description]) ."',
							script = '" . securiserSql($_POST[script]) . "' WHERE lienid = '$lienid'");
		
			if (!empty($_FILES['logo']['name'])) {
				$largeur_max= "240";
				$hauteur_max = "170";
				$listeTypesAutorise = array("image/pjpeg", "image/jpeg", "image/gif", "image/png");
				erreurUpload("logo", $listeTypesAutorise, 1048576);
				$taille = GetImageSize($_FILES['logo']['tmp_name']);
				if ($taille[0] <= $largeur_max && $taille[1] <= $hauteur_max && $erreur == ""){
					$type_fichier = define_extention($_FILES['logo']['name']);
					$DB_site->query("UPDATE liens SET logo = '$type_fichier' WHERE lienid = '$lienid'");
					$nom_fichier = $rootpath . "configurations/$host/images/liens/" . $lienid . "." . $type_fichier;
					copier_image($nom_fichier, 'logo');
				}else{
					$texteErreur = "$multilangue[taille_image_non_conforme].";
					eval(charge_template($langue, $referencepage, "Erreur"));
					$action = "modifier";
				}
			}
		}
		if ($action != "modifier")
			header('location: echange_liens.php');
	}else{
		header('location: echange_liens.php?erreurdroits=1');	
	}
}

if (isset($action) and $action == "modifier"){
	if (isset($lienid)){
		$lien = $DB_site->query_first("SELECT * FROM liens WHERE lienid = '$lienid'");
		if ($lien[logo] != "")
			$img = "../configurations/$host/images/liens/" . $lien[lienid] . "." . $lien[logo];
		else
			$img = "";
		$texte_entete = "$multilangue[modif_lien] : $lien[libelle]";
		$libNavigSupp = "$multilangue[modif_lien] : <b>\"$lien[libelle]\"</b>";
		eval(charge_template($langue,$referencepage,"NavigSupp"));
	}else{
		$texte_entete = $multilangue[ajt_lien];
		$libNavigSupp = $multilangue[ajt_lien];
		eval(charge_template($langue,$referencepage,"NavigSupp"));
		$sites = $DB_site->query("SELECT * FROM site");
		while ($site = $DB_site->fetch_array($sites)){
			$selected = ($site[siteid] == $lien[siteid] ? "selected" : "");
			eval(charge_template($langue, $referencepage, "ModificationSiteBit"));
		}
		eval(charge_template($langue, $referencepage, "ModificationSite"));
	}
	
	eval(charge_template($langue, $referencepage, "Modification"));
}

if (isset($action) and $action == "supprimer") {
	if($admin_droit[$scriptcourant][suppression]){
		$DB_site->query("DELETE FROM liens WHERE lienid = '$lienid'");
		header('location: echange_liens.php');
	}else{
		header('location: echange_liens.php?erreurdroits=1');	
	}
}

if (!isset($action) or $action == ""){
	$liens = $DB_site->query("SELECT * FROM liens ORDER BY position");
	while ($lien = $DB_site->fetch_array($liens)){
		$site = $DB_site->query_first("SELECT * FROM site WHERE siteid = '$lien[siteid]'");
		$lien[site] = $site[libelle];
		$flag = $DB_site->query_first("SELECT diminutif FROM pays WHERE paysid = '$site[paysid]'");
		$flag[diminutif] = strtolower($flag[diminutif]);
		if ($lien[encart] == 1){
			$color_aff = "green";
			$ico_aff = "fa-check-square-o";
			$tooltip_visible = $multilangue[passer_invisible];
		}else{
			$color_aff = "red";
			$ico_aff = "fa-square-o";
			$tooltip_visible=$multilangue[passer_visible];
		}
		eval(charge_template($langue,$referencepage,"ListeBit"));
	}
	eval(charge_template($langue,$referencepage,"Liste"));
}

$TemplateIncludejavascript = eval(charge_template($langue, $referencepage, "Includejavascript"));
eval(charge_template($langue, "commun", "header"));
eval(charge_template($langue, "commun", "footer"));
eval(charge_template($langue, $referencepage, "index"));

$nomtemplateindex="Template".ucfirst($referencepage)."Index";
echo ${$nomtemplateindex};

$DB_site->close();
flush();

?>