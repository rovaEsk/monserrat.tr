<?php
include "includes/header.php";

$referencepage="utilisateurs_admin";
$pagetitle = "Utilisateurs Admin - $host - Admin Arobases";

$titrepage=$multilangue[utilisateurs_admin];
$lienpagebase="utilisateurs_admin.php";
$niveaunavigsup="";

//$mode = "test_modules";

if(!parse_template($referencepage, $langue)){
	echo "erreur de chargement de template";
}

// AFFICHAGE ALERTES SUCCES ET ERREUR
if(isset($succes)){
	switch ($succes){
		case 1:
			$user_suppr = $DB_site->query_first("SELECT * FROM admin_utilisateur WHERE userid = '$userid'");
			$texteSuccess = "$multilangue[l_utilisateur] <strong>$user_suppr[username]</strong> $multilangue[a_bien_ete_supprime]";
		break;
		case 2:
			$user_ajoute = $DB_site->query_first("SELECT * FROM admin_utilisateur WHERE userid = '$userid'");
			$texteSuccess = "$multilangue[l_utilisateur] <strong>$user_ajoute[username]</strong> $multilangue[a_bien_ete_cre]";
		break;
		case 3:
			$user_modifie = $DB_site->query_first("SELECT * FROM admin_utilisateur WHERE userid = '$userid'");
			$texteSuccess = "$multilangue[l_utilisateur] <strong>$user_modifie[username]</strong> $multilangue[a_bien_ete_modifie]";
		break;
	}
	
	eval(charge_template($langue, $referencepage, "Success"));
}

if(isset($erreur)){
	switch ($erreur){
		case 1:
			$texteErreur = "$multilangue[mdp_suppression_incorrect]";
			break;
		case 2:
			$texteErreur = "$multilangue[selection_maximum] <strong>$params[nombre_article_diapositive]</strong> $multilangue[articles]";
			break;
	}
	eval(charge_template($langue, $referencepage, "Erreur"));
}

if(isset($erreurdroits) and $erreurdroits == 1){
	$texteErreur = $multilangue[action_page_refuse];
	eval(charge_template($langue, $referencepage, "Erreur"));
}

// AJOUTER GROUPE
if (isset($action) && $action == "ajouter_groupe"){
	if($admin_droit[$scriptcourant][ecriture]){
		if($nom_groupe != ""){
			$DB_site->query("INSERT INTO admin_groupe (parentid, libelle) VALUES('".addslashes($user_info[groupeid])."', '".addslashes($nom_groupe)."')");
		}
		header("location: utilisateurs_admin.php?action=droits");
	}else{
		header('location: utilisateurs_admin.php?erreurdroits=1');	
	}
}
// AJOUTER GROUPE

// MAJ DROITS
if (isset($action) && $action == "maj_droits"){		
	if($admin_droit[$scriptcourant][ecriture]){	

		$groupes = $DB_site->query("SELECT * FROM admin_groupe WHERE groupeid != '".$administrateur_arobases."' AND groupeid != '".$user_info[groupeid]."' ");
		while($groupe = $DB_site->fetch_array($groupes)){			
			if($user_info[groupeid] == $administrateur_arobases || estAdmin($DB_site, $groupe[groupeid])){				
				$DB_site->query("DELETE FROM admin_droit WHERE groupeid = '".$groupe[groupeid]."'");
			}
		}
				
		// Les pages accessibles uniquement en lecture
		if(is_array($_POST[lecture])){
			foreach($_POST[lecture] as $groupeid => $tabPages){
				foreach($tabPages as $pageid => $value){
					$DB_site->query("INSERT INTO admin_droit (groupeid, pageid, lecture) VALUES('".$groupeid."', '".$pageid."', '1')");
				}
			}
		}
		// Les pages accessibles en lecture, ecriture
		if(is_array($_POST[ecriture])){
			foreach($_POST[ecriture] as $groupeid => $tabPages){
				foreach($tabPages as $pageid => $value){
					$DB_site->query("INSERT INTO admin_droit (groupeid, pageid, lecture, ecriture) VALUES('".$groupeid."', '".$pageid."', '1', '1')");
				}
			}
		}
		// Les pages accessibles en lecture, ecriture, suppression
		if(is_array($_POST[suppression])){
			foreach($_POST[suppression] as $groupeid => $tabPages){
				foreach($tabPages as $pageid => $value){
					$DB_site->query("INSERT INTO admin_droit (groupeid, pageid, lecture, ecriture, suppression) VALUES('".$groupeid."', '".$pageid."', '1', '1', '1')");
				}
			}
		}
		header("location: utilisateurs_admin.php?action=droits");
	}else{
		header('location: utilisateurs_admin.php?erreurdroits=1');	
	}
}
// MAJ DROITS

// DEBUT DROITS
if (isset($action) && $action == "droits"){
	if($admin_droit[$scriptcourant][ecriture]){
		$groupes = $DB_site->query("SELECT * FROM admin_groupe WHERE groupeid != '".$administrateur_arobases."' AND groupeid != '".$user_info[groupeid]."' ");
		while($groupe = $DB_site->fetch_array($groupes)){
			$TemplateUtilisateurs_adminCategorieBit = "";
			$TemplateUtilisateurs_adminCategorieBit2 = "";
			if($user_info[groupeid] == $administrateur_arobases || estAdmin($DB_site, $groupe[groupeid])){	
				$categories = $DB_site->query("SELECT DISTINCT(categorieid), ac.libelle FROM admin_page INNER JOIN admin_categorie ac USING(categorieid) ORDER BY ac.libelle");
				$i=1;
				while($categorie = $DB_site->fetch_array($categories)){
					$categorie[libelle] = $multilangue["$categorie[libelle]"];
					if($user_info[groupeid] == $administrateur_arobases){
						$pages = $DB_site->query("SELECT * FROM admin_page WHERE categorieid = '".$categorie[categorieid]."' ");
					}else{
						$pages = $DB_site->query("SELECT * FROM admin_droit INNER JOIN admin_page USING(pageid) 
									WHERE groupeid = '".$user_info[groupeid]."' AND categorieid = '".$categorie[categorieid]."'");
					}
					while($page = $DB_site->fetch_array($pages)){
						$droits = $DB_site->query_first("SELECT * FROM admin_droit WHERE groupeid = '".$groupe[groupeid]."' AND pageid = '".$page[pageid]."' ");
						$checked_suppression = $checked_ecriture = $checked_lecture = $disabled_lecture = $disabled_ecriture = "";
						if($droits[suppression] == 1){
							$checked_suppression = "checked";
							$disabled_lecture = "disabled";
							$disabled_ecriture = "disabled";
						}
						if($droits[ecriture] == 1){
							$checked_ecriture = "checked";
							$disabled_lecture = "disabled";
						}
						if($droits[lecture] == 1){
							$checked_lecture = "checked";
						}
						$page[libelle] = $multilangue["$page[scriptname]"];
						eval(charge_template($langue,$referencepage,"PageBit"));
					}
					if($i == 1){
						eval(charge_template($langue,$referencepage,"CategorieBit"));
					}else{
						eval(charge_template($langue,$referencepage,"CategorieBit2"));
					}
					$i++;
					if($i == 3) $i = 1;
					$TemplateUtilisateurs_adminPageBit = "";
				}
				eval(charge_template($langue,$referencepage,"DroitsGroupeBit"));
				$TemplateUtilisateurs_adminPageBit = "";
				$TemplateUtilisateurs_adminCategorieBit = "";
			}
		}

		$libNavigSupp=$multilangue[gestion_des_droits];
		eval(charge_template($langue,$referencepage,"NavigSupp"));
		eval(charge_template($langue,$referencepage,"Droits"));
	}else{
		header('location: utilisateurs_admin.php?erreurdroits=1');	
	}
}
// FIN DROITS

// SUPPRESSION UTILISATEUR
if (isset($action) and $action == "supprimer"){
	if($admin_droit[$scriptcourant][suppression]){
		$DB_site->query("UPDATE admin_utilisateur SET deleted = '1' WHERE userid = '".$userid."' ");
		header("location: utilisateurs_admin.php?userid=$userid&succes=1");
	}else{
		header('location: utilisateurs_admin.php?erreurdroits=1');	
	}
}

//AJOUT CLIENT DANS LA BDD
if (isset($action) && $action == "ajoututilisateurbdd"){
	if($admin_droit[$scriptcourant][ecriture]){
		//verification de l'email...
		$erreur = "";
	
		if (!isset($Uusername) || $Uusername == ""){
			$erreur .= "$multilangue[login_obligatoire]<br>" ;
		}	
		
		// if (!isset($Unom) || $Unom == ""){
			// $erreur .= "$multilangue[nom_obligatoire]<br>" ;
		// }		
		
		// if (!isset($Uprenom) || $Uprenom == ""){
			// $erreur .= "$multilangue[prenom_obligatoire]<br>" ;
		// }		
		
		// if (!isset($Umail) || $Umail == ""){
			// $erreur .= "$multilangue[email_obligatoire]<br>" ;
		// }
		if($Umail != "" && !preg_match('/^[-!#$%&\'*+\\.\/0-9=?A-Z^_{|}~]+@([-0-9A-Z]+\.)+([0-9A-Z]){2,4}$/i', $Umail)){
			$erreur .= "$multilangue[email_incorrect]<br>" ;
		}
		
		// if (!isset($Utelephone) || $Utelephone == ""){
			// $erreur .= "$multilangue[telephone_obligatoire]<br>" ;
		// }	
		
		if (!isset($Ugroupeid) || $Ugroupeid == ""){
			$erreur .= "$multilangue[groupe_obligatoire]<br>" ;
		}		
		
		if (!isset($passField) || $passField == ""){
			$erreur .= "$multilangue[mdp_obligatoire]<br>" ;
		}elseif(strlen($passField) < 6){
			$erreur .= "$multilangue[mdp_6_caracteres]<br>" ;
		}
	
		if($erreur == ""){
			 
			$DB_site->query("INSERT INTO admin_utilisateur (username, password, nom, prenom, mail, telephone, groupeid) VALUES	
					(
					 '".addslashes($Uusername)."',
					 MD5('$passField'),
					 '".addslashes($Unom)."',
					 '".addslashes($Uprenom)."',
					 '".addslashes($Umail)."',
					 '".addslashes($Utelephone)."',
					 '".addslashes($Ugroupeid)."'
					)");
					
			$userid=$DB_site->insert_id();
			
			
			
			if ($_FILES[Uphoto][name]){
				$listeTypesAutorise = array("image/pjpeg", "image/jpeg", "image/gif");
				erreurUpload("Uphoto", $listeTypesAutorise, 1048576);
				if (!$erreur){
					$type_fichier = pathinfo($_FILES[Uphoto][name], PATHINFO_EXTENSION);
					$type_fichier = ($type_fichier == "jpeg" ? "jpg" : $type_fichier);
					$nom_fichier = md5(time());
					$DB_site->query("UPDATE admin_utilisateur SET photo = '".$nom_fichier.".".$type_fichier."' WHERE userid = '$userid'");
					$path = $rootpath . "admin/includes/images/utilisateurs_admin/".$nom_fichier.".".$type_fichier;
					copier_image($path, "Uphoto");
					redimentionner_image($path, $path, 200, 200);
				}
			}
	
			if(isset($Unotificationmail) && preg_match('/^[-!#$%&\'*+\\.\/0-9=?A-Z^_{|}~]+@([-0-9A-Z]+\.)+([0-9A-Z]){2,4}$/i', $Umail)){	
				$sujet = "$title1 : Création compte d'administration $host";
				$htmlmess = "Félicitations  $Fprenom $Fnom,<br>
				<br>On vient de vous créer un compte administrateur sur $host.
				<br><br>Rappel de vos codes d'accès
				<br>Login : $Uusername
				<br>Mot de passe : $passField
				<br>Page de connexion : http://$host/admin/
				<br><br>A bientôt sur $host.";
				email($DB_site, $Umail, stripslashes($sujet), stripslashes($htmlmess), $params[mail_contact]);
			}		
			header("location: utilisateurs_admin.php?action=modifier&userid=$userid&succes=2");
		}else{
			$texteErreur = $erreur;
			eval(charge_template($langue, $referencepage, "Erreur"));
			$action = "ajoututilisateur";
		}
	}else{
		header('location: utilisateurs_admin.php?erreurdroits=1');	
	}
}

// DEBUT AJOUT UTILISATEUR
if (isset($action) && $action == "ajoututilisateur"){
	if(isset($Ugroupeid)){
		$optionsGroupes=retournerListeGroupes($DB_site, $Ugroupeid);
	}else{
		$optionsGroupes=retournerListeGroupes($DB_site);
	}
	
	eval(charge_template($langue, $referencepage, "AjoutInsertPhoto"));
	
	$action_form = "ajoututilisateurbdd";
	
	$libNavigSupp=$multilangue[ajt_utilisateur];
	eval(charge_template($langue,$referencepage,"NavigSupp"));
	eval(charge_template($langue,$referencepage,"Ajout"));
}
// FIN AJOUT UTILISATEUR

// DEBUT SUPPRIMER PHOTO UTILISATEUR
if (isset($action) && $action == "supprimerPhoto"){
	if($admin_droit[$scriptcourant][suppression]){
		$fichier = $DB_site->query_first("SELECT photo FROM admin_utilisateur WHERE userid = '".$userid."' ");
		$fichier = $rootpath . "admin/includes/images/utilisateurs_admin/".$fichier[photo];
		@unlink($fichier);
		$DB_site->query("UPDATE admin_utilisateur SET photo = '' WHERE userid = '".$userid."' ");
		$action = "modifier";
	}else{
		header('location: utilisateurs_admin.php?erreurdroits=1');	
	}
}
// FIN SUPPRIMER PHOTO UTILISATEUR

//AJOUT CLIENT DANS LA BDD
if (isset($action) && $action == "modifutilisateurbdd"){
	if($admin_droit[$scriptcourant][ecriture]){	
		//verification de l'email...
		$erreur = "";
	
		if (!isset($Uusername) || $Uusername == ""){
			$erreur .= "$multilangue[login_obligatoire]<br>" ;
		}	
		
		// if (!isset($Unom) || $Unom == ""){
			// $erreur .= "$multilangue[nom_obligatoire]<br>" ;
		// }		
		
		// if (!isset($Uprenom) || $Uprenom == ""){
			// $erreur .= "$multilangue[prenom_obligatoire]<br>" ;
		// }		
		
		// if (!isset($Umail) || $Umail == ""){
			// $erreur .= "$multilangue[email_obligatoire]<br>" ;
		// }
		if($Umail != "" && !preg_match('/^[-!#$%&\'*+\\.\/0-9=?A-Z^_{|}~]+@([-0-9A-Z]+\.)+([0-9A-Z]){2,4}$/i', $Umail)){
			$erreur .= "$multilangue[email_incorrect]<br>" ;
		}
		
		// if (!isset($Utelephone) || $Utelephone == ""){
			// $erreur .= "$multilangue[telephone_obligatoire]<br>" ;
		// }	
		
		if (!isset($Ugroupeid) || $Ugroupeid == ""){
			$erreur .= "$multilangue[groupe_obligatoire]<br>" ;
		}		
		
		if($passField != "" && strlen($passField) < 6){
			$erreur .= "$multilangue[mdp_6_caracteres]<br>" ;
		}
	
		if($erreur == ""){
			if($passField != ""){
				$updatePassword = "password = MD5('$passField'), ";
			}
			$DB_site->query("UPDATE admin_utilisateur SET 
			username = '".addslashes($Uusername)."', 
			$updatePassword 
			nom = '".addslashes($Unom)."', 
			prenom = '".addslashes($Uprenom)."', 
			mail = '".addslashes($Umail)."', 
			telephone = '".addslashes($Utelephone)."', 
			groupeid = '".addslashes($Ugroupeid)."'
			WHERE userid = '".$userid."' 
			");
	
			if ($_FILES[Uphoto][name]){
				$listeTypesAutorise = array("image/pjpeg", "image/jpeg", "image/gif");
				erreurUpload("Uphoto", $listeTypesAutorise, 1048576);
				if (!$erreur){
					$type_fichier = pathinfo($_FILES[Uphoto][name], PATHINFO_EXTENSION);
					$type_fichier = ($type_fichier == "jpeg" ? "jpg" : $type_fichier);
					$nom_fichier = md5(time());
					$DB_site->query("UPDATE admin_utilisateur SET photo = '".$nom_fichier.".".$type_fichier."' WHERE userid = '$userid'");
					$path = $rootpath . "admin/includes/images/utilisateurs_admin/".$nom_fichier.".".$type_fichier;
					copier_image($path, "Uphoto");
					redimentionner_image($path, $path, 200, 200);
				}
			}
	
			if(isset($Unotificationmail) && preg_match('/^[-!#$%&\'*+\\.\/0-9=?A-Z^_{|}~]+@([-0-9A-Z]+\.)+([0-9A-Z]){2,4}$/i', $Umail)){	
				$sujet = "$title1 : Modification compte d'administration $host";
				$htmlmess = "Félicitations  $Fprenom $Fnom,<br>
				<br>Votre compte administrateur sur $host a été mis à jour.
				<br><br>Rappel de vos codes d'accès
				<br>Login : $Uusername
				<br>Mot de passe : $passField
				<br>Page de connexion : http://$host/admin/
				<br><br>A bientôt sur $host.";
				email($DB_site, $Umail, stripslashes($sujet), stripslashes($htmlmess), $params[mail_contact]);
			}		
			header("location: utilisateurs_admin.php?action=modifier&userid=$userid&succes=3");
		}else{
			$texteErreur = $erreur;
			eval(charge_template($langue, $referencepage, "Erreur"));
			$action = "modifier";
		}
	}else{
		header('location: utilisateurs_admin.php?erreurdroits=1');	
	}
}

// DEBUT MODIFIER UTILISATEUR
if (isset($action) && $action == "modifier"){
	$utilisateur = $DB_site->query_first("SELECT * FROM admin_utilisateur WHERE userid = '".$userid."' ");
	if($user_info[groupeid] == $administrateur_arobases || ($user_info[groupeid] != $administrateur_arobases && estAdmin($DB_site, $utilisateur[groupeid]))){
		$optionsGroupes=retournerListeGroupes($DB_site, $utilisateur[groupeid]);
		$Uusername = $utilisateur[username];
		$Unom = $utilisateur[nom];
		$Uprenom = $utilisateur[prenom];
		$Umail = $utilisateur[mail];
		$Utelephone = $utilisateur[telephone];
		
		if($utilisateur[photo]){
			eval(charge_template($langue, $referencepage, "AjoutPhoto"));
		}else{
			eval(charge_template($langue, $referencepage, "AjoutInsertPhoto"));
		}
		
		$action_form = "modifutilisateurbdd";
		
		$libNavigSupp=$multilangue[modif_utilisateur];
		eval(charge_template($langue,$referencepage,"NavigSupp"));
		eval(charge_template($langue,$referencepage,"Ajout"));
	}else{
		header('location: utilisateurs_admin.php?erreurdroits=1');	
	}
}
// FIN MODIFIER UTILISATEUR

// DEBUT AFFICHER LES UTILISATEURS
if (!isset($action) || $action == "" || $action == "rechercher"){
	if (isset ($lst_trietat) && $lst_trietat != "" && $lst_trietat != "-1") {
		$selected0 = "";
		$selected1 = "";
		switch ($lst_trietat) {
			case 0:
				$selected0 = "selected";
				$orderby = "ORDER BY nom, prenom";
				break;
			case 1:
				$selected1 = "selected";
				$orderby = "ORDER BY groupeid ASC";
				break;
			}
		}
	else
		$orderby = "ORDER BY userid ASC";
		
	// Pour getpagenav
	if (!isset($pagenumber) or $pagenumber == "") {
		$pagenumber = 1;
		unset($_SESSION['perpageSessionAdmin']);
	}	
	if (isset($perpageAdmin) && $perpageAdmin != "") {
		if($_SESSION['perpageSessionAdmin']!=$perpageAdmin) {
			$_SESSION['perpageSessionAdmin'] = $perpageAdmin;
			$pagenumber = 1;
		}
	}else{
		if(!isset($_SESSION['perpageSessionAdmin'])){
			$_SESSION['perpageSessionAdmin'] = $perpage;
		}
	}
		
	$perpageSelect = $_SESSION['perpageSessionAdmin'];
	
	$limitlower=($pagenumber-1)*$perpageSelect;
	$limitsql = " LIMIT $limitlower, $perpageSelect";
	
	$selectPerPageAdminBit = "";
	
	if (isset($nf) and $nf != "") {
		$where .= " AND nom LIKE '%$nf%' OR prenom LIKE '%$nf%'" ;
	}
	if (isset($useridrech) and $useridrech != "" && is_numeric ($useridrech)) {
		$where .= " AND userid = '$useridrech'" ;
	}	
	if (isset($mail) and $mail != ""){
		$where .= " AND mail LIKE '%$mail%'" ;
	}
	
	//compte le nombre d'utilisateurs	
	$utilisateurcount = $DB_site->query_first("SELECT count(*) FROM admin_utilisateur WHERE userid>0 $where");
	
	eval(charge_template($langue,$referencepage,"Liste"));
	eval(charge_template($langue,$referencepage,"InitTable"));
	$libNavigSupp=$multilangue[liste_users_admin];
	eval(charge_template($langue,$referencepage,"NavigSupp"));
}
// FIN AFFICHER LES UTILISATEURS

$TemplateIncludejavascript = eval(charge_template($langue, $referencepage,"Includejavascript"));
eval(charge_template($langue,"commun","header"));
eval(charge_template($langue,"commun","footer"));
eval(charge_template($langue,$referencepage,"index"));

$nomtemplateindex="Template".ucfirst($referencepage)."Index";
echo ${$nomtemplateindex};

$DB_site->close();
flush();
?>