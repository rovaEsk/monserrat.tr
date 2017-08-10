<?php
include "./includes/header.php";

$referencepage="encarts_perso";
$pagetitle = "Encarts personnalisables - $host - Admin Arobases";

if(!parse_template($referencepage, $langue)){
	echo "erreur de chargement de template";
}

if(isset($erreurdroits) and $erreurdroits == 1){
	$texteErreur = $multilangue[action_page_refuse];
	eval(charge_template($langue, $referencepage, "Erreur"));
}

/******************* ACTION EDITION FICHIER ********************************/
if ($action == doeditfile) {
	if($admin_droit[$scriptcourant][ecriture]){
		$infos = $DB_site->query_first("SELECT libelle, url FROM autopromo WHERE autopromoid = '$autopromoid'");
		if ($_FILES['fichier']['name']!="") {
			$listeTypesAutorise = array("image/pjpeg","image/jpeg","image/gif","application/x-shockwave-flash" );
			erreurUpload("fichier",$listeTypesAutorise,2048576);
			if ($erreur == "") {				

				$file=define_extention($_FILES['fichier']['name']);
				$titre = $_FILES['fichier']['name'];
				$DB_site->query("UPDATE autopromo SET libelle = '".securiserSql($libelleFichier)."', fichier = '".securiserSql($file)."', url = '$formURL', siteid = '$siteid' WHERE autopromoid = '$autopromoid'");
				
				$nom_fic=$rootpath."configurations/$host/images/autopromo/".$autopromoid.".".$file ;
				if (file_exists($nom_fic)){
					@unlink($nom_fic);
				}
				copier_fichier($nom_fic,"fichier");
				header("location: encarts_perso.php?autopromoid=$autopromoid&alertSuccess1=success");
			} else {
				//echo $erreur;
				$action="editFile";
				$texteErreur="$erreur";
				eval(charge_template($langue,$referencepage,"Erreur"));
			}
		}else{
			$DB_site->query("UPDATE autopromo SET libelle = '".securiserSql($libelleFichier)."', url = '$formURL', siteid = '$siteid' WHERE autopromoid = '$autopromoid'");
			$texteSuccess = $multilangue[le_fichier].$multilangue[a_bien_ete_modifie];
			eval(charge_template($langue,$referencepage,"Success"));
			//$action="";
			header("location: encarts_perso.php?autopromoid=$autopromoid&alertSuccess1=success");
		}	
	}else{
		header('location: encarts_perso.php?erreurdroits=1');	
	}
}
/******************* ACTION AJOUT FICHIER ********************************/
if ($action == ajout){
	if($admin_droit[$scriptcourant][ecriture]){
		$message = "";
		if ($_FILES['fichier']['name']=="") {
			$action="ajout";
			$texteErreur = $multilangue[erreur_aucun_fichier_telecharg];
			eval(charge_template($langue,$referencepage,"Erreur"));
		} else {
			$erreur = "";
			if ($_FILES['fichier']['name']!="") {
				$listeTypesAutorise = array("image/pjpeg","image/jpeg","image/gif","application/x-shockwave-flash" );
				erreurUpload("fichier",$listeTypesAutorise,2048576);
				if ($erreur == "") {
					$file=define_extention($_FILES['fichier']['name']);
					$titre = $_FILES['fichier']['name'];
					$infos_upload=$DB_site->query("INSERT INTO autopromo (libelle, fichier, url, position, siteid) 
													VALUES ('".securiserSql($libelleFichier)."', '".securiserSql($file)."', '".securiserSql($formURL)."', '$position', '$siteid')") ;
					$insertid=$DB_site->insert_id();
					$nom_fic=$rootpath."configurations/$host/images/autopromo/".$insertid.".".$file ;
					copier_fichier($nom_fic,"fichier") ;
					header("location: encarts_perso.php?autopromoid=$autopromoid&alertSuccess2=success");
				} else {
					$texteErreur = $erreur;
					eval(charge_template($langue,$referencepage,"Erreur"));
				}
			}
		}
	}else{
		header('location: encarts_perso.php?erreurdroits=1');	
	}
}	

/******************* CHOIX SITE ********************************/
$sites = $DB_site->query("SELECT * FROM site ORDER BY siteid");
while ($site = $DB_site->fetch_array($sites)){
	eval(charge_template($langue,$referencepage,"SelectBit"));
}

/******************* EDITION FICHIER ********************************/
if(isset($action) && $action == "editFile" ){
	$infos = $DB_site->query_first("SELECT * FROM autopromo WHERE autopromoid = '$autopromoid'");
	$pathpic="http://$host/autopromo-".$infos[autopromoid].".".$infos[fichier]."?date=$datejs" ;
	eval(charge_template($langue,$referencepage,"Editfile"));
	$libNavigSupp=$multilangue[modif_fichier];
	eval(charge_template($langue,$referencepage,"NavigSupp"));
}	
/******************* SUPPRESSION FICHIER ********************************/

if(isset($action) && $action == "supprimer2" ){
	if($admin_droit[$scriptcourant][suppression]){
		$extension = $DB_site->query_first("SELECT fichier FROM autopromo");
		$cheminfic = $rootpath."/configurations/$host/images/autopromo/".$autopromoid.".".$extension[fichier];
		@unlink($cheminfic);
		$DB_site->query("DELETE FROM autopromo WHERE autopromoid = '$autopromoid'");
		header("location: encarts_perso.php?alertSuccess=success2");	
	}else{
		header('location: encarts_perso.php?erreurdroits=1');	
	}
}
/******************* AFFICHAGE FORM AJOUT AUTO-PROMO ********************************/
if ($action == ajoutFichier){
	
	$sites = $DB_site->query("SELECT * FROM site ORDER BY siteid");
		eval(charge_template($langue,$referencepage,"Addfile"));
	

}
/******************* ACTION EDITION DIMENSIONS AUTO-PROMO ********************************/
if ($action == enregistrer) {
	if($admin_droit[$scriptcourant][ecriture]){
		$erreursauv = "";
		if ($largeur == "") {
			$erreursauv .= "$multilangue[largeur_obligatoire]<br>";
		} elseif (!is_numeric($largeur)) {
			$erreursauv .= "$multilangue[largeur_px_positive]<br>";
		}
		elseif (is_numeric($largeur) and ($largeur == 0 or $largeur < 0)) {
			$erreursauv .= "$multilangue[largeur_px_positive]<br>";
		}
		if ($hauteur == "") {
			$erreursauv .= "$multilangue[hauteur_obligatoire]<br>";
		}
		elseif (!is_numeric($hauteur)) {
			$erreursauv .= "$multilangue[hauteur_px_positive]<br>";
		}
		elseif (is_numeric($hauteur) and ($hauteur == 0 or $hauteur < 0)) {
			$erreursauv .= "$multilangue[hauteur_px_positive]<br>";
		}
		if ($erreursauv == "") {
			$DB_site->query("UPDATE parametre SET valeur = '$largeur' WHERE parametre='autopromo_largeur' ");
			$DB_site->query("UPDATE parametre SET valeur = '$hauteur' WHERE parametre='autopromo_hauteur' ");
			header("location: encarts_perso.php");
		} else {
			header("location: encarts_perso.php");;
		}
	}else{
		header('location: balises_meta.php?erreurdroits=1');	
	}
}

/******************* ACTION EDITION TEXTES PERSONNALISABLES ********************************/
if ($action == doediter) {	
	if($admin_droit[$scriptcourant][ecriture]){
		foreach( $newText as $idSite => $contenu ){	
			if($contenu != ""){
				$testwysiwyg = $DB_site->query_first("SELECT * FROM textepersonnel WHERE textepersonnelid = '$idTexte'");			
				$testExiste = $DB_site->query_first("SELECT * FROM textepersonnel_site
														WHERE textepersonnelid = '$idTexte'
														AND siteid = '$idSite'");
				
				$titreTexte = $DB_site->query_first("SELECT *
														FROM textepersonnel_langue tl
														WHERE tl.textepersonnelid = $idTexte");
				
				if ($testwysiwyg[wysiwyg] != 1){	
					if($testExiste[textepersonnelid] != ""){				
						$DB_site->query("UPDATE textepersonnel_site
											SET contenu='".securiserSql($contenu)."'
											WHERE textepersonnelid = '$idTexte'
											AND siteid = '$idSite'");
					}else{
						$DB_site->query("INSERT INTO textepersonnel_site (textepersonnelid, siteid, contenu) 
											VALUES('$idTexte', '$idSite', '".securiserSql($contenu)."')");					
					}
				}else{
					if($testExiste[textepersonnelid] != ""){
						$DB_site->query("UPDATE textepersonnel_site
											SET contenu='".securiserSql($contenu, "html")."'
										WHERE textepersonnelid = '$idTexte'
										AND siteid = '$idSite'");					
					}else{
						$DB_site->query("INSERT INTO textepersonnel_site (textepersonnelid, siteid, contenu)
											VALUES('$idTexte', '$idSite', '".securiserSql($contenu, "html")."')");
										//echo "INSERT INTO textepersonnel_site (textepersonnelid, siteid, contenu) VALUES('$idTexte', '$idSite', '".securiserSql($contenu)."')<br>";
					}
				}
			}
		}
		$texteSuccess = $multilangue[le_texte]." <strong>\"$titreTexte[legende]\"</strong> ".$multilangue[a_bien_ete_modifie];
		eval(charge_template($langue,$referencepage,"Success"));
		header("location: encarts_perso.php?idTexte=$idTexte&alertSuccess=success");	
	}else{
		header('location: encarts_perso.php?erreurdroits=1');	
	}
}

/******************* AFFICHAGE FORMS EDITION TEXTES PERSONNALISABLES ********************************/
if ($action == editer){	
	$titreTexte = $DB_site->query_first("SELECT *
			FROM textepersonnel_langue tl
			WHERE tl.textepersonnelid = $idTexte");
	
	$sites = $DB_site->query("SELECT * FROM site ORDER BY siteid");
	while($site = $DB_site->fetch_array($sites)){
		$langueEditPage = $DB_site->query_first("SELECT * FROM langue l
				WHERE l.langueid = '$site[langueid]'");
		
		$contenuTexte = $DB_site->query_first("SELECT * from textepersonnel AS t
				INNER JOIN textepersonnel_site AS ts USING(textepersonnelid)
				WHERE t.textepersonnelid = '$idTexte'
				AND ts.siteid='$site[siteid]'");
		
		if($contenuTexte[wysiwyg]){
			$class_wysiwyg="editeur";
		}
		
		eval(charge_template($langue,$referencepage,"ModifTextBit"));
	}
	eval(charge_template($langue,$referencepage,"ModifText"));
	$libNavigSupp="$multilangue[modification_du] <b>\"$titreTexte[legende]\"</b>";
	eval(charge_template($langue,$referencepage,"NavigSupp"));
	
}

/******************* GESTION AFFICHAGE DE BASE ********************************/
if (!isset($action) || ($action =="")) {
	$donneesAutoPromo = $DB_site->query("SELECT * FROM autopromo");
	$reqTxtPerso = $DB_site->query("SELECT * 
									FROM textepersonnel_langue");
	while ($afficheListeTxtPerso = $DB_site->fetch_array($reqTxtPerso)){
		$legendeTexte = $DB_site->query_first("SELECT legende
												FROM textepersonnel_langue tl
												WHERE tl.textepersonnelid = '$afficheListeTxtPerso[textepersonnelid]' ");
		
		eval(charge_template($langue,$referencepage,"TextBit"));
	}
	
	eval(charge_template($langue,$referencepage,"Text"));

	while ($afficheAutoPromo = $DB_site->fetch_array($donneesAutoPromo)){
		
		$siteConcerne = $DB_site->query_first("SELECT libelle
												FROM site s
												WHERE s.siteid = '$afficheAutoPromo[siteid]'");
		
		if($afficheAutoPromo[active]==1){
			$color_aff = "vert";
			$color2_aff = "green";
			$ico_aff = "fa-check-square-o";	
		}else{
			$color_aff = "rouge";
			$color2_aff = "red";
			$ico_aff = "fa-square-o";			
		}
		
		eval(charge_template($langue,$referencepage,"FichiersBit"));
	}
	if ($alertSuccess == 'success'){
		$infosSuccess = $DB_site->query_first("SELECT * FROM textepersonnel_langue WHERE textepersonnelid = '$idTexte'");
		$texteSuccess = $multilangue[le_texte]." <strong>\"$infosSuccess[legende]\"</strong> ".$multilangue[a_bien_ete_modifie];
		eval(charge_template($langue,$referencepage,"Success"));
	}
	if ($alertSuccess == 'success2'){
		$texteSuccess = $multilangue[encarts_perso_supprime];
		eval(charge_template($langue,$referencepage,"Success"));
	}
	if ($alertSuccess1 == 'success'){
		$texteSuccess = $multilangue[le_fichier]." ".$multilangue[a_bien_ete_modifie];
		eval(charge_template($langue,$referencepage,"Success"));
	}
	if ($alertSuccess2 == 'success'){
		$texteSuccess = $multilangue[nouveau_fichier_enregistre];
		eval(charge_template($langue,$referencepage,"Success"));
	}
	eval(charge_template($langue,$referencepage,"Auto_promo"));
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
