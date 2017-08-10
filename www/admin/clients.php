<?php
include "includes/header.php";

$referencepage="clients";
$pagetitle = "Gestion des clients - $host - Admin Arobases";

$titrepage=$multilangue[gestion_clients];
$lienpagebase="clients.php";
$niveaunavigsup="";

$class_menu_clients_active = "active";

//$mode = "test_modules";

if(!parse_template($referencepage, $langue)){
	echo "erreur de chargement de template";
}

// ENVOI D'UN MAIL AU CLIENT
if (isset($action) && $action == "envoyermail"){
	if (isset($sujet) and $sujet != "" and isset($message) and $message != "") {
		$user_send = $DB_site->query_first("SELECT * FROM utilisateur WHERE userid = '$userid'");		
		email($DB_site, $user_send[mail], $sujet, stripslashes(nl2br($message)), $params[mail_contact]);
	}	
	header("location: clients.php?action=editer&user=$userid#echanges");
}

// AJOUT D'UN COMMENTAIRE SUR LE CLIENT
if (isset($action) and $action == "ajoutcomentaire") {
	if($admin_droit[$scriptcourant][ecriture]){
		$DB_site->query("INSERT INTO utilisateur_commentaire (userid,adminid,date,commentaire) VALUES ('$userid','$user_info[userid]','".time()."','".securiserSql($_POST['commentaire'])."')");
		header("location: clients.php?action=editer&user=$userid#echanges");
	}else{
		header('location: clients.php?erreurdroits=1');	
	}
}

// SUPPRIMER UN COMMENTAIRE
if (isset($action) and $action == "supprimercommentaire") {
	if($admin_droit[$scriptcourant][suppression]){
		$DB_site->query("UPDATE utilisateur_commentaire SET deleted='1' WHERE commentaireid = '$_GET[commentaireid]'");
		header("location: clients.php?action=editer&user=$userid#echanges");
	}else{
		header('location: clients.php?erreurdroits=1');	
	}
}

// MAJ STATUT REPONSE FORMULAIRE
if (isset($action) and $action == "setstatut") {
	if($admin_droit[$scriptcourant][ecriture]){
		$DB_site->query("UPDATE formulaire_reponse SET statutid = '$_POST[statutid]' WHERE formulairereponseid = '$_POST[formulairereponseid]'");
		header("location: clients.php?action=editer&user=$userid#echanges");
	}else{
		header('location: clients.php?erreurdroits=1');	
	}
}

// SUPPRIMER UNE REPONSE
if (isset($action) and $action == "supprimerreponse") {
	if($admin_droit[$scriptcourant][suppression]){
		$DB_site->query("DELETE FROM formulaire_reponse WHERE formulairereponseid = '$formulairereponseid'");
		$DB_site->query("DELETE FROM formulaire_reponse_champ WHERE formulairereponseid = '$formulairereponseid'");
		$DB_site->query("DELETE FROM formulaire_reponse_contact WHERE formulairereponseid = '$formulairereponseid'");
		header("location: clients.php?action=editer&user=$userid#echanges");
	}else{
		header('location: clients.php?erreurdroits=1');	
	}
}

// AFFICHAGE ALERTES SUCCES ET ERREUR
if(isset($erreurdroits) and $erreurdroits == 1){
	$texteErreur = $multilangue[action_page_refuse];
	eval(charge_template($langue, $referencepage, "Erreur"));
}

if(isset($succes)){
	switch ($succes){
		case 1:
			$user_suppr = $DB_site->query_first("SELECT * FROM utilisateur WHERE userid = '$useridsuppr'");
			$texteSuccess = "$multilangue[le_client] <strong>$user_suppr[prenom] $user_suppr[nom] ($user_suppr[mail])</strong> $multilangue[a_bien_ete_supprime]";
		break;
		case 2:
			$user_ajoute = $DB_site->query_first("SELECT * FROM utilisateur WHERE userid = '$user'");
			$texteSuccess = "$multilangue[le_client] <strong>$user_ajoute[prenom] $user_ajoute[nom] ($user_ajoute[mail])</strong> $multilangue[a_bien_ete_cre]";
		break;
		case 3:
			$user_modifie = $DB_site->query_first("SELECT * FROM utilisateur WHERE userid = '$user'");
			$texteSuccess = "$multilangue[le_client] <strong>$user_modifie[prenom] $user_modifie[nom] ($user_modifie[mail])</strong> $multilangue[a_bien_ete_modifie]";
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

// SUPPRESSION UTILISATEUR
if (isset($action) and $action == "supprimer2"){
	if($admin_droit[$scriptcourant][suppression]){
		$activerSuppression = 0;
		if (in_array("5966", $modules)|| $mode == "test_modules") {
			if (isset($password_suppression) and $params[password_suppression] == md5($password_suppression)) {
				$activerSuppression = 1;
			}else{			
				header("location: clients.php?erreur=1");
			}		
		}else{
			$activerSuppression = 1;
		}
		if ($activerSuppression) {
			$DB_site->query("UPDATE utilisateur SET deleted = '1' WHERE userid = '$useridsuppr'");
			header("location: clients.php?succes=1&useridsuppr=$useridsuppr");
		}
	}else{
		header('location: clients.php?erreurdroits=1');	
	}
}

//AJOUT CLIENT DANS LA BDD
if (isset($action) && $action == "ajoutclientbdd"){
	if($admin_droit[$scriptcourant][ecriture]){
		//verification de l'email...
		$erreur = "";

		if (!isset($Fcivilite) || $Fcivilite == ""){
			$erreur .= "$multilangue[civilite_obligatoire]<br>";
		}		
		
		if (!isset($Fnom) || $Fnom == ""){
			$erreur .= "$multilangue[nom_obligatoire]<br>" ;
		}		
		
		if (!isset($Fprenom) || $Fprenom == ""){
			$erreur .= "$multilangue[prenom_obligatoire]<br>" ;
		}		
		
		if (!isset($Fmail) || $Fmail == ""){
			$erreur .= "$multilangue[email_obligatoire]<br>" ;
		}elseif(!preg_match('/^[-!#$%&\'*+\\.\/0-9=?A-Z^_{|}~]+@([-0-9A-Z]+\.)+([0-9A-Z]){2,4}$/i', $Fmail)){
			$erreur .= "$multilangue[email_incorrect]<br>" ;
		}
				
		if (!isset($passField) || $passField == ""){
			$erreur .= "$multilangue[mdp_obligatoire]<br>" ;
		}elseif(strlen($passField) < 6){
			$erreur .= "$multilangue[mdp_6_caracteres]<br>" ;
		}

		if (!isset($Fadresse) || $Fadresse == ""){
			$erreur .= "$multilangue[adresse_obligatoire]<br>" ;
		}		
		
		if (!isset($Fcodepostal) || $Fcodepostal == ""){
			$erreur .= "$multilangue[code_postal_obligatoire]<br>" ;
		}		
		
		if (!isset($Fville) || $Fville == ""){
			$erreur .= "$multilangue[ville_obligatoire]<br>" ;
		}		
		
		if (!isset($Ftelephone) || $Ftelephone == ""){
			$erreur .= "$multilangue[telephone_obligatoire]<br>" ;
		}		
		
		if ($params[siretobligatoire] == 1 && (!isset($Fsiret) || $Fsiret == "")){
			$erreur .= "$multilangue[siret_obligatoire]<br>" ;
		}		
		
		if ($params[tvaobligatoire] == 1 && (!isset($Ftva) || $Ftva == "")){
			$erreur .= "$multilangue[tva_intracommunautaire_obligatoire]<br>" ;
		}		
		
		if ($params[fonctionobligatoire] == 1 && (!isset($Ffonction) || $Ffonction == "")){
			$erreur .= "$multilangue[fonction_obligatoire]<br>" ;
		}		
		
		if (!isset($Fpays) || $Fpays == ""){
			$erreur .= "$multilangue[pays_obligatoire]<br>" ;
		}		
		
		$user=$DB_site->query_first("SELECT COUNT(*) FROM utilisateur WHERE mail = '$Fmail'");
		if ($user[0] == 1){
			$erreur .= "$multilangue[email_existe_deja]<br>" ;
		}		
		
		if ($Fnewsletter == true) {
			$Fnewsletter = 1;
		}else{
			$Fnewsletter = 0;
		}
		
		if ($Fnewsletter_partenaire == true) {
			$Fnewsletter_partenaire = 1;
		}else{
			$Fnewsletter_partenaire = 0;
		}
		
		if($erreur == ""){
			$DB_site->query("INSERT INTO utilisateur (mail, password, civilite, nom, prenom,adresse, adresse2, codepostal, ville,
					 telephone, telephone2, raisonsociale, dateinscription, paysid, recevoir, siret, tva, fonction) VALUES	
					(
					 '".addslashes($Fmail)."',
					 MD5('$passField'),
					 '$Fcivilite',
					 '".addslashes($Fnom)."',
					 '".addslashes($Fprenom)."',
					 '".addslashes($Fadresse)."',
					 '".addslashes($Fadresse2)."',
					 '".addslashes($Fcodepostal)."',
					 '".addslashes($Fville)."',
					 '".addslashes($Ftelephone)."',
					 '".addslashes($Ftelephone2)."', 
					 '".addslashes($Fraisonsociale)."', 
					 NOW(), 
					 '$Fpays', 
					 '$Fnewsletter', 
					 '".addslashes($Fsiret)."', 
					 '".addslashes($Ftva)."', 
					 '".addslashes($Ffonction)."'
					)");
			
			$userid=$DB_site->insert_id();
			$DB_site->query("UPDATE mails_newsletter SET allow_email = '0' WHERE adresse_mail = '$Fmail'");
			
			if (in_array(5817, $modules)|| $mode == "test_modules"){
				$DB_site->query("UPDATE utilisateur SET recevoir_partenaire = '$Fnewsletter_partenaire' WHERE userid = '$userid'");
			}
				
			if (in_array(5857, $modules)|| $mode == "test_modules"){// Gestion des encours
				$DB_site->query("UPDATE utilisateur SET encours_max = '$Fencours_max' WHERE userid = '$userid'");
			} 

			if(isset($envoieemailclient)){			
				if ((in_array(122, $modules)|| $mode == "test_modules") && $_POST['pro'] == 1){
					$email = $DB_site->query_first("SELECT * FROM mail_type_site
							WHERE emailid = '22'
							AND siteid='$utilRecup[siteid]'");
							$sujet=$mails[libelle];
							$htmlmess=$mails[contenu];
							$htmlmess = str_replace("[loginpass]", "Login : $Fmail<br>Mot de passe : $passField", $htmlmess); //ok
							$htmlmess = str_replace("[boutique]", "<strong>".${$title}."</strong>", $htmlmess);//ok
							$sujet = str_replace("[boutique]", ${$title}, $sujet);//ok
							$htmlmess = str_replace("[infosclientsmall]", " $Fprenom $Fnom", $htmlmess);//ok
							email($DB_site, $mail, $subject, stripslashes($htmlmess), $params[mail_contact]);
				}else{
					$mails=$DB_site->query_first("SELECT * from mail_type_site WHERE emailid = '21' AND siteid='1'");
					
					//declaration des variables suivant la langue du client
					if($mails[contenu]!=""){
						$sujet=$mails[libelle];
						$htmlmess=$mails[contenu];
						$htmlmess = str_replace("[loginpass]", "Login : $Fmail<br>Mot de passe : $passField", $htmlmess); //ok
						$htmlmess = str_replace("[boutique]", "<strong>".${$title}."</strong>", $htmlmess);//ok
						$sujet = str_replace("[boutique]", ${$title}, $sujet);//ok
						$htmlmess = str_replace("[infosclientsmall]", " $Fprenom $Fnom", $htmlmess);//ok
					}else{
						$sujet = "${$title} : Votre inscription sur $host";
						$htmlmess = "Félicitations  $Fprenom $Fnom,<br>
						<br>Un administrateur vient de vous créer un compte sur $host.
						<br><br>Rappel de vos codes d'accès
						<br>Login : $Fmail
						<br>Mot de passe : $passField
						<br><br>A bientôt sur $host.";
					}
					email($DB_site, $Fmail, stripslashes($sujet), stripslashes($htmlmess), $params[mail_contact]);
				}			
			}		
			header("location: clients.php?action=editer&user=$userid&succes=2");
		}else{
			$texteErreur = $erreur;
			eval(charge_template($langue, $referencepage, "Erreur"));
			$action = "ajoutclient";
		}
	}else{
		header('location: clients.php?erreurdroits=1');	
	}
}

// DEBUT AJOUT CLIENT
if (isset($action) && $action == "ajoutclient"){
	if($Fcivilite == '1' || $Fcivilite == '2'){
		$Fcivilite1 = "checked=\"checked\"";
	}else{
		$Fcivilite0 = "checked=\"checked\"";
	}
	
	if (in_array(5857, $modules) || $mode == "test_modules"){ // Gestion des encours
		eval(charge_template($langue,$referencepage,"AjoutEncours"));
	}
	
	if ($Fnewsletter == '0'){
		$Fnewsletter0 = "checked=\"checked\"";
	}else{
		$Fnewsletter1 = "checked=\"checked\"";
	}
	
	if (in_array(5817, $modules) || $mode == "test_modules") {
		if ($Fnewsletter_partenaire == '0'){
			$Fnewsletter_partenaire0 = "checked=\"checked\"";
		}else{
			$Fnewsletter_partenaire1 = "checked=\"checked\"";
		}
		eval(charge_template($langue,$referencepage,"AjoutNewsletterPart"));
	}

	if(isset($Fpays)){
		$optionsPays=retournerListePays($DB_site, $Fpays);
	}else{
		$optionsPays=retournerListePays($DB_site, 57);
	}
	
	if (in_array(122, $modules) || $mode == "test_modules") {
		if ($Fpro == '1'){
			$Fpro1 = "checked=\"checked\"";
		}else{
			$Fpro0 = "checked=\"checked\"";
		}	
		eval(charge_template($langue,$referencepage,"AjoutPro"));;
	}
				
	$libNavigSupp=$multilangue[ajt_compte_client];
	eval(charge_template($langue,$referencepage,"NavigSupp"));
	
	eval(charge_template($langue,$referencepage,"Ajout"));
}
// FIN AJOUT CLIENT

// DEBUT MODIF CLIENT (Enregistrement BDD)
if(isset($action) && $action == "doediter"){
	if($admin_droit[$scriptcourant][ecriture]){
		$existeUser = $DB_site->query_first("SELECT COUNT(userid) FROM utilisateur WHERE mail = '".addslashes($mail)."' AND userid != '$userid'");
		if ($existeUser[0]){ // Erreur un utilisateur existe déjà avec le mail saisi
			$texteErreur = "Un client existe déjà avec cette adresse mail : <strong>".$_POST['mail']."</strong>, merci de choisir une autre adresse mail.";
			eval(charge_template($langue, $referencepage, "Erreur"));
			
			// Erreur
		}else{
			$utilRecupPro=$DB_site->query_first("SELECT pro FROM utilisateur WHERE userid = '$userid'");
					
			$DB_site->query("UPDATE utilisateur 
								SET civilite='".securiserSql($_POST['civilite'])."', 
								nom='".securiserSql($_POST['nom'])."',
								prenom='".securiserSql($_POST['prenom'])."', 
								adresse='".securiserSql($_POST['adresse'])."', 
								adresse2='".securiserSql($_POST['adresse2'])."',
								mail='".securiserSql($_POST['mail'])."', 
								codepostal='".securiserSql($_POST['codepostal'])."', 
								ville='".securiserSql($_POST['ville'])."',
								telephone='".securiserSql($_POST['telephone'])."', 
								telephone2='".securiserSql($_POST['telephone2'])."', 
								raisonsociale='".securiserSql($_POST['raisonsociale'])."', 
								paysid='".securiserSql($_POST['paysid'])."',
								recevoir='".securiserSql($_POST['recevoir'])."', 
								pro='".securiserSql($_POST['pro'])."',
								siret='".securiserSql($_POST['siret'])."',
								tva='".securiserSql($_POST['tva'])."',
								fonction='".securiserSql($_POST['fonction'])."'
								WHERE userid='".securiserSql($_POST['userid'])."'");
			
			if(in_array(5817, $modules)|| $mode == "test_modules"){
				$DB_site->query("UPDATE utilisateur 
								SET recevoir_partenaire='".securiserSql($_POST['recevoir_partenaire'])."' 
								WHERE userid='".securiserSql($_POST['userid'])."'");
			}			
				
			if(in_array(5857, $modules)|| $mode == "test_modules"){ // Gestion des encours
				$DB_site->query("UPDATE utilisateur 
								SET encours_max='".securiserSql($_POST['encours_max'])."' 
								WHERE userid='".securiserSql($_POST['userid'])."'");
			} 

			if(in_array(5834, $modules)|| $mode == "test_modules"){ // Module fidélité
				$DB_site->query("UPDATE utilisateur
								SET nbpoints='".securiserSql($_POST['nbpoints'])."'
								WHERE userid='".securiserSql($_POST['userid'])."'");
			}
					
			if(isset($passField) && !empty($passField)){
				$DB_site->query("UPDATE utilisateur 
								SET password='".md5($passField)."' 
								WHERE userid='".securiserSql($_POST['userid'])."'");
				
				if(isset($_POST['envoieemailclient'])){
					switch($_POST['civilite']){
						case 0:
							$civilite="Monsieur";
							break;
						case 1:
							$civilite="Madame";
							break;					
					}
					$utilRecup=$DB_site->query_first("SELECT siteid FROM utilisateur 
														WHERE userid = '".securiserSql($_POST['userid'])."'");
						
					$email = $DB_site->query_first("SELECT * FROM mail_type_site 
													WHERE emailid = '254' 
													AND siteid='$utilRecup[siteid]'");
					$subject = $email[libelle];
					$htmlmess = $email[contenu];
					$htmlmess = str_replace("[boutique]", "<strong>".${$title}."</strong>", $htmlmess);//ok
					$htmlmess = str_replace("[loginpass]", "Login : $mail<br>Mot de passe : $passField", $htmlmess); //ok
					$htmlmess = str_replace("[infosclientsmall]", "$civilite $prenom $nf", $htmlmess);//ok
					email($DB_site, $mail, "${$title} : $multilangue[recuperation_mdp]", stripslashes($htmlmess), $params[mail_contact]);
					$erreur = "$multilangue[mdp_envoye_adresse] $mail." ;
				}
			}
			
			if (in_array(122, $modules)|| $mode == "test_modules"){
				if ($_POST['pro'] == 1 && isset($_POST['envoieemailclientPro']) && $utilRecupPro[pro] != $pro) {
					$utilRecup=$DB_site->query_first("SELECT siteid FROM utilisateur 
														WHERE userid = '".securiserSql($_POST['userid'])."'");
						
					$email = $DB_site->query_first("SELECT * FROM mail_type_site 
													WHERE emailid = '13' 
													AND siteid='$utilRecup[siteid]'");
					$subject = $email[libelle];
					$htmlmess = $email[contenu];
					email($DB_site, $mail, $subject, stripslashes($htmlmess), $params[mail_contact]);
				}
			}	
			header("location: clients.php?action=editer&user=".$_POST['userid']."&succes=3");
		}
	}else{
		header('location: clients.php?erreurdroits=1');	
	}
}
// FIN MODIF CLIENT (Enregistrement BDD)

// DEBUT AFFICHAGE DETAIL CLIENT
if ($action == "editer" && $user != ""){	
	// Debut Affichage infos client
	if($action2 == "showmodif"){
		$utilisateur=$_POST;
	}else{
		$utilisateur=$DB_site->query_first("SELECT * FROM utilisateur where userid = '$user'");
	}		
	
	$transmettreCivilite=retournerCivilite($utilisateur[civilite]);
	$paysUser = retournerLibellePays($DB_site, $utilisateur[paysid]);	
	$optionsPays=retournerListePays($DB_site, $utilisateur[paysid]);
	
	if($utilisateur[datedernieraction] != 0){
		$dateLastConnexion = date('d/m/Y H:i:s',$utilisateur[datedernieraction]);
	}else{
		$dateLastConnexion = "Jamais";
	}
	
	$lien_connexion = "http://$host/V2/client.htm?action=logging&cryptage=no&mail_logging=$utilisateur[mail]&pass_logging=$utilisateur[password]";
	
	$var_check_civ="checked_civ".$utilisateur[civilite];
	${$var_check_civ} = "checked=\"checked\"";
	
	$var_check_recevoir="checked_recevoir".$utilisateur[recevoir];
	${$var_check_recevoir} = "checked=\"checked\"";	
		
	switch ($utilisateur[recevoir]) {
		case '0':
			$utilisateur[news] = $multilangue[ne_recoit_pas_newsletter] ;
			break ;
		case '1':
			$utilisateur[news] = $multilangue[recoit_newsletter] ;
			break ;
	}
	eval(charge_template($langue,$referencepage,"InfosNewsletter"));
	
	
	if(in_array(5817, $modules)|| $mode == "test_modules"){ // Module newsletter partenaires
		$var_check_recevoir_part="checked_recevoir_part".$utilisateur[recevoir_partenaire];
		${$var_check_recevoir_part} = "checked=\"checked\"";

		switch ($utilisateur[recevoir_partenaire]) {
			case '0':
				$utilisateur[news_partenaire] = $multilangue[ne_recoit_pas_newsletter_partenaire];
				break ;
			case '1':
				$utilisateur[news_partenaire] = $multilangue[recoit_newsletter_partenaire];
				break ;
		}		
		eval(charge_template($langue,$referencepage,"ModifNewsletterPart"));
		eval(charge_template($langue,$referencepage,"InfosNewsletterPart"));		
	}
	
	if(in_array(122, $modules)|| $mode == "test_modules"){ // Module pro
		$var_check_pro="checked_pro".$utilisateur[pro];
		${$var_check_pro} = "checked=\"checked\"";
		
		switch ($utilisateur[pro]) {
			case '0':
				$utilisateur[est_pro] = $multilangue[non];
				break ;
			case '1':
				$utilisateur[est_pro] = $multilangue[oui];
				break ;
		}		
		eval(charge_template($langue,$referencepage,"ModifPro"));
		eval(charge_template($langue,$referencepage,"InfosPro"));
	}
	
	if(in_array(5857, $modules)|| $mode == "test_modules"){ // Module encours
		eval(charge_template($langue,$referencepage,"ModifEncours"));
		eval(charge_template($langue,$referencepage,"InfosEncours"));
	}
	
	if(in_array("5834",$modules)|| $mode == "test_modules"){ // Module fidélité			
		switch ($utilisateur[club]) {
			case '0':
				$utilisateur[est_fidele] = $multilangue[client_appartient_pas_club_fidelite];
				break ;
			case '1':
				$utilisateur[est_fidele] = $multilangue[client_appartient_club_fidelite]." (<b>$utilisateur[nbpoints]</b> $multilangue[points_fidelite])";
				eval(charge_template($langue,$referencepage,"ModifFidelite"));
				break ;
		}		
		eval(charge_template($langue,$referencepage,"InfosFidelite"));
	}
	
	if($action2 == "showmodif"){
		$display_infos="style=\"display:none;\"";
		$display_modif="style=\"display:block;\"";
	}else{
		$display_infos="style=\"display:block;\"";
		$display_modif="style=\"display:none;\"";
	}
	
	eval(charge_template($langue,$referencepage,"Infos"));	
	// Fin Affichage modif client		

	
	// Debut Affichage adresses livraison			
	$adressesLivraison=$DB_site->query("select * 
										FROM utilisateur_livraison 
										WHERE userid = '$utilisateur[userid]'");
											
	if ($DB_site->num_rows($adressesLivraison) > 0){			
		while($adresseLivraison=$DB_site->fetch_array($adressesLivraison)){
			$livCivilite = retournerCivilite($adresseLivraison[civilite]);
			$livPays = retournerLibellePays($DB_site, $adresseLivraison[paysid]); 
			
			eval(charge_template($langue,$referencepage,"AdresseLivraisonBit"));
		}			
		eval(charge_template($langue,$referencepage,"AdresseLivraison"));
	}
	//Fin Affichage adresses livraison

	//DEBUT AFFICHAGE RECAP COMMANDES
	$anneemois = date ('Y-m') ;
	$cetteannee= date ('Y');
	$anneemoissemaine1=date ('Y-m-d',(time()-1*24*60*60));//1 jours avant
	$anneemoissemaine2=date ('Y-m-d',(time()-2*24*60*60));//2 jours avant
	$anneemoissemaine3=date ('Y-m-d',(time()-3*24*60*60));//3 jours avant
	$anneemoissemaine4=date ('Y-m-d',(time()-4*24*60*60));//4 jours avant
	$anneemoissemaine5=date ('Y-m-d',(time()-5*24*60*60));//5 jours avant
	$anneemoissemaine6=date ('Y-m-d',(time()-6*24*60*60));//6 jours avant
	$anneemoissemaine7=date ('Y-m-d',(time()-0*24*60*60));//0 jours avant
		
		
	//****************************************************COMMANDES PASSEES*************************************************
	//commandes passees 
	$execTotalCmdPassees=$DB_site->query_first("SELECT COUNT(factureid) 
												FROM facture 
												WHERE userid = '$user'");//total commandes passées
	
	$nbTotalCmdPassees = $execTotalCmdPassees[0];	 //cherche avec ctrl F = rembourse pour trouver les autres query. Derien maggle.

	//commandes passées dans l'année
	$nb_cdespassees_cetteannee=$DB_site->query_first("SELECT COUNT(factureid) 
														FROM facture 
														WHERE userid = '$user' 
														AND ( datefacture LIKE '%$cetteannee-%')");
	$nbCmdPasseesAnnee = $nb_cdespassees_cetteannee[0];

	//commandes passées dans le mois
	$nb_cdespassees_mois=$DB_site->query_first("SELECT COUNT(factureid) 
												FROM facture 
												WHERE userid = '$user' 
												AND datefacture LIKE '%$anneemois-%'");
	$nbCmdPasseesMois = $nb_cdespassees_mois[0];

	//commandes passées dans la semaine
	$nb_cdespassees_semaine=$DB_site->query_first("SELECT COUNT(factureid) 
													FROM facture 
													WHERE userid = '$user' 
													AND ( datefacture LIKE '%$anneemoissemaine1%' 
													OR datefacture LIKE '%$anneemoissemaine2%' 
													OR datefacture LIKE '%$anneemoissemaine3%'   
													OR datefacture LIKE '%$anneemoissemaine4%'  
													OR datefacture LIKE '%$anneemoissemaine5%'  
													OR datefacture LIKE '%$anneemoissemaine6%'  
													OR datefacture LIKE '%$anneemoissemaine7%' )");
	$nbCmdPasseesSemaine = $nb_cdespassees_semaine[0];
	
	if($nbCmdPasseesSemaine || $nbCmdPasseesMois || $nbCmdPasseesAnnee || $nbTotalCmdPassees){
		eval(charge_template($langue,$referencepage,"RecapCommandePassees"));
	}

	//**************************************************COMMANDES REMBOURSEES*******************************************
	//total commandes remboursees
	$nb_cdesremboursees_tot=$DB_site->query_first("SELECT COUNT(factureid) FROM facture 
													WHERE userid = '$user' 
													AND etatid = '6'");
	$nbTotalCmdRemboursees  = $nb_cdesremboursees_tot[0];

	//commandes remboursées dans l'année
	$nb_cdesremboursees_cetteannee=$DB_site->query_first("SELECT COUNT(factureid) 
															FROM facture 
															WHERE userid = '$user' 
															AND etatid = '6' 
															AND ( datefacture LIKE '%$cetteannee-%')");
	$nbCmdRembourseesAnnee = $nb_cdesremboursees_cetteannee[0];

	//commandes remboursées dans le mois
	$nb_cdesremboursees_mois=$DB_site->query_first("SELECT COUNT(factureid) 
													FROM facture 
													WHERE userid = '$user' 
													AND etatid = '6' 
													AND datefacture LIKE '%$anneemois-%'");
	$nbCmdRembourseesMois = $nb_cdesremboursees_mois[0];

	//commandes remboursées dans la semaine
	$nb_cdesremboursees_semaine=$DB_site->query_first("SELECT COUNT(factureid) 
														FROM facture where userid = '$user' 
														AND etatid = '6' 
														AND ( datefacture LIKE '%$anneemoissemaine1%' 
														OR datefacture LIKE '%$anneemoissemaine2%' 
														OR datefacture LIKE '%$anneemoissemaine3%'   
														OR datefacture LIKE '%$anneemoissemaine4%'  
														OR datefacture LIKE '%$anneemoissemaine5%'  
														OR datefacture LIKE '%$anneemoissemaine6%'  
														OR datefacture LIKE '%$anneemoissemaine7%' )");
	$nbCmdRembourseesSemaine = $nb_cdesremboursees_semaine[0];

	if($nbCmdRembourseesSemaine || $nbCmdRembourseesMois || $nbCmdRembourseesAnnee || $nbTotalCmdRemboursees){
		eval(charge_template($langue,$referencepage,"RecapCommandeRemboursees"));
	}
	
	//***********************************************************COMMANDES EXPEDIEES******************************************
	//commandes expédiées total
	$nb_cdesexpediees_tot=$DB_site->query_first("SELECT COUNT(factureid) 
												FROM facture 
												WHERE userid = '$user' 
												AND etatid = '5'");
	$nbTotalCmdExpediees = $nb_cdesexpediees_tot[0];

	//commandes expédiées cette année
	$nb_cdesexpediees_cetteannee=$DB_site->query_first("SELECT COUNT(factureid) 
														FROM facture
														 WHERE userid = '$user' 
														AND etatid = '5' 
														AND ( datefacture LIKE '%$cetteannee-%')");
	$nbCmdExpedieesAnnee = $nb_cdesexpediees_cetteannee[0];

	//commandes expediées ce mois
	$nb_cdesexpediees_mois=$DB_site->query_first("SELECT COUNT(factureid) 
													FROM facture 
													WHERE userid = '$user' 
													AND etatid = '5' 
													AND datefacture LIKE '%$anneemois-%'");
	$nbCmdExpedieesMois = $nb_cdesexpediees_mois[0];

	//commandes expediées semaine
	$nb_cdesexpediees_semaine=$DB_site->query_first("SELECT COUNT(factureid) 
													FROM facture 
													WHERE userid = '$user' 
													AND etatid = '5' 
													AND ( datefacture LIKE '%$anneemoissemaine1%' 
													OR datefacture LIKE '%$anneemoissemaine2%' 
													OR datefacture LIKE '%$anneemoissemaine3%'   
													OR datefacture LIKE '%$anneemoissemaine4%'  
													OR datefacture LIKE '%$anneemoissemaine5%'  
													OR datefacture LIKE '%$anneemoissemaine6%'  
													OR datefacture LIKE '%$anneemoissemaine7%' )");
	$nbCmdExpedieesSemaine = $nb_cdesexpediees_semaine[0];
	
	if($nbCmdExpedieesSemaine || $nbCmdExpedieesMois || $nbCmdExpedieesAnnee || $nbTotalCmdExpediees){
		eval(charge_template($langue,$referencepage,"RecapCommandeExpediees"));
	}
			
	$totalCa = array();
	$totalCaHt = array();
	$j=0;
	for($i=0;$i<13;$i++){
		$j = 12-$i;
		$moisEnCours = date("m",mktime(0, 0, 0, date("m")-$i  , 01, date("Y")));
		$anneeEnCours = date("Y",mktime(0, 0, 0, date("m")-$i  , 01, date("Y")));
	
		$nbrJour = date("t",mktime(0, 0, 0, date("m")-$i  , 01, date("Y")));
		$periode1 = date("Y-m-d",mktime(0, 0, 0, date("m")-$i  , 01, date("Y"))); // premier jour du mois
		$periode2 = date("Y-m-d",mktime(0, 0, 0, date("m")-$i  , $nbrJour, date("Y"))); // dernier jour du mois
	
		$nbrCommandes = $DB_site->query("SELECT * FROM facture WHERE etatid IN (1,5) AND montanttotal_ttc > 0 AND datefacture >= ('".$periode1."') AND datefacture <= ('".$periode2."') AND userid='".$utilisateur['userid']."' ");
		$nombreProd = $DB_site->query_first("SELECT SUM(l.qte) as nbrprod FROM lignefacture l INNER JOIN facture f ON l.factureid=f.factureid AND f.etatid IN (1,5) AND f.datefacture >= ('".$periode1."') AND f.datefacture <= ('".$periode2."') AND f.montanttotal_ttc > 0 AND f.userid='".$utilisateur['userid']."'");
		$ca = $DB_site->query_first("SELECT SUM(montanttotal_ttc) AS montant_ttc, SUM(montanttotal_horsfraisport_ht) AS montant_ht FROM facture WHERE etatid IN (1,5) AND montanttotal_ttc > 0 AND datefacture >= ('".$periode1."') AND datefacture <= ('".$periode2."') AND userid='".$utilisateur['userid']."'");
	
		$recupNombresCommande  = $DB_site->num_rows($nbrCommandes);
		$recupNombreProd = $nombreProd['nbrprod'];
		$recupCa = $ca['montant_ttc'];
		$recupCaHt = $ca['montant_ht'];
			
		if(empty($recupCommandeMoyenne))
			$recupCommandeMoyenne = "0";
			
		if(empty($recupNombreProd))
			$recupNombreProd = "0";
			
		if(empty($recupCa))
			$recupCa = "0";
	
		//$moisEnCoursLib = retournerMoisFr($moisEnCours)." ".$anneeEnCours;
		$moisEnCoursLib = substr(retournerMoisFr2($moisEnCours),0,3);
		$graphMois[$j] = $moisEnCoursLib;
		$graphMoisDonne[$j] = $recupNombresCommande;
		$graphMoisDonneProd[$j] = $recupNombreProd;
		$totalCa[$j] = $recupCa;
		$totalCaHt[$j] = $recupCaHt;
		
	}

	$graph2="
			<div class=\"cont_graph1\">
				<div class=\"titre_graph\">$multilangue[nb_produits_commandes_12_mois]</div>	
				<div id=\"chart_1\" class=\"chart\" style=\"width:100%; height:300px;\"></div>
			</div>
		<script type=\"text/javascript\">	
			jQuery(document).ready(function(){ 	
			var nb_commandes = [";
			$contenu_stat = "";
			for($j=0;$j<13;$j++){
				$contenu_stat .= "['".$j."',".formaterPrix($graphMoisDonne[$j],2,'.','')."],";
			}
			$graph2.=substr($contenu_stat,0,-1);
			$graph2.="];
			
			var nb_produits = [";
			$contenu_stat = "";
			for($j=0;$j<13;$j++){
				$contenu_stat .= "['".$j."',".formaterPrix($graphMoisDonneProd[$j],2,'.','')."],";
			}
			$graph2.=substr($contenu_stat,0,-1);
			$graph2.="];
			
			var plot = $.plot($(\"#chart_1\"), [{
				data: nb_commandes,
				label: \"$multilangue[nb_commandes]\",
				lines: {
					lineWidth: 1,
				},
				shadowSize: 0
			
			}, {
				data: nb_produits,
				label: \"$multilangue[nb_produits_commandes]\",
				lines: {
					lineWidth: 1,
				},
				shadowSize: 0
			}], {
				series: {
					lines: {
						show: true,
						lineWidth: 2,
						fill: true,
						fillColor: {
							colors: [{
								opacity: 0.05
							}, {
								opacity: 0.01
							}
							]
						}
					},
					points: {
						show: true,
						radius: 3,
						lineWidth: 1
					},
					shadowSize: 2
				},
				grid: {
					hoverable: true,
					clickable: true,
					tickColor: \"#eee\",
					borderColor: \"#eee\",
					borderWidth: 1
				},
				colors: [\"#d12610\", \"#37b7f3\", \"#52e136\"],
				xaxis: {
					ticks: 13,
					tickFormatter: function (v) {	
									return retournerMois(v);
								},					
					tickColor: \"#eee\",
				},
				yaxis: {
					ticks: 11,
					tickDecimals: 0,
					tickColor: \"#eee\",
				}
			});
											
			  function showTooltip(x, y, contents) {
                    $('<div id=\"tooltip\">' + contents + '</div>').css({
                            position: 'absolute',
                            display: 'none',
                            top: y + 5,
                            left: x + 15,
                            border: '1px solid #333',
                            padding: '4px',
                            color: '#fff',
                            'border-radius': '3px',
                            'background-color': '#333',
                            opacity: 0.80
                        }).appendTo(\"body\").fadeIn(200);
                }
											
				function retournerMois(nummois) {
                    var tableauMois =new Array();";						
						for($j=0;$j<13;$j++){
							$graph2.= "tableauMois[$j] = \"$graphMois[$j]\";";
						}											
						$graph2.="
					return tableauMois[nummois];
                }							
																		
			var previousPoint = null;
                $(\"#chart_1\").bind(\"plothover\", function (event, pos, item) {
                    $(\"#x\").text(pos.x.toFixed(2));
                    $(\"#y\").text(pos.y.toFixed(2));

								
								
                    if (item) {
                        if (previousPoint != item.dataIndex) {
                            previousPoint = item.dataIndex;

                            $(\"#tooltip\").remove();
                            var x = item.datapoint[0],
                                y = item.datapoint[1];

                            showTooltip(item.pageX, item.pageY, \"<b>\" + retournerMois(x) + \"</b><br>\" +  item.series.label + \" : <b>\" + y + \"</b>\");
                        }
                    } else {
                        $(\"#tooltip\").remove();
                        previousPoint = null;
                    }
                });								
											
		});
		</script>";			
		
	$graph3="
		<style>	
			.cont_graph1 {
			    display:block;
			    float:left;
			    width:49.5%;
			 }
			 .cont_graph2 {
			    display:block;
			    float:right;
			    width:49.5%;
			 }	 	 
		 @media screen and (max-width: 640px) {
			  .cont_graph1 {
			    display:block;
			    float:left;
			    width:100%;
			  }
			  .cont_graph2 {
			    display:block;
			    float:left;
			    width:100%;
			  }
		 }
		</style>	
		<div class=\"cont_graph2\">
			<div class=\"titre_graph\">$multilangue[chiffre_affaires_genere_12_derniers_mois]</div>	
			<div id=\"chart_2\" class=\"chart\" style=\"width:100%; height:300px;\"></div>
		</div>
		<div class=\"clearfix\"></div>
		<script type=\"text/javascript\">	
			jQuery(document).ready(function(){"; 
		
			/*$graph3.= "var totalttc = [";
			$contenu_stat = "";
			for($j=0;$j<13;$j++){
				$contenu_stat .= "['".$j."',".formaterPrix($totalCa[$j],2,'.','')."],";
			}
			$graph3.=substr($contenu_stat,0,-1);
			$graph3.="];";*/
			
			$graph3.= "var totalht = [";
			$contenu_stat = "";
			for($j=0;$j<13;$j++){
				$contenu_stat .= "['".$j."',".formaterPrix($totalCaHt[$j],2,'.','')."],";
			}
			$graph3.=substr($contenu_stat,0,-1);
			$graph3.="];
			
			var plot = $.plot($(\"#chart_2\"), [{
				data: totalht,
				label: \"Montant total HT hors frais de port\",
				lines: {
					lineWidth: 1,
				},
				shadowSize: 0
			}], {
				series: {
					lines: {
						show: true,
						lineWidth: 2,
						fill: true,
						fillColor: {
							colors: [{
								opacity: 0.05
							}, {
								opacity: 0.01
							}
							]
						}
					},
					points: {
						show: true,
						radius: 3,
						lineWidth: 1
					},
					shadowSize: 2
				},
				grid: {
					hoverable: true,
					clickable: true,
					tickColor: \"#eee\",
					borderColor: \"#eee\",
					borderWidth: 1
				},
				colors: [\"#d12610\", \"#37b7f3\", \"#52e136\"],
				xaxis: {
					ticks: 13,
					tickFormatter: function (v) {	
									return retournerMois(v);
								},					
					tickColor: \"#eee\",
				},
				yaxis: {
					ticks: 11,
					tickDecimals: 0,
					tickColor: \"#eee\",
				}
			});
											
			  function showTooltip(x, y, contents) {
                    $('<div id=\"tooltip\">' + contents + '</div>').css({
                            position: 'absolute',
                            display: 'none',
                            top: y + 5,
                            left: x + 15,
                            border: '1px solid #333',
                            padding: '4px',
                            color: '#fff',
                            'border-radius': '3px',
                            'background-color': '#333',
                            opacity: 0.80
                        }).appendTo(\"body\").fadeIn(200);
                }
											
				function retournerMois(nummois) {
                    var tableauMois =new Array();";						
						for($j=0;$j<13;$j++){
							$graph3.= "tableauMois[$j] = \"$graphMois[$j]\";";
						}											
						$graph3.="
					return tableauMois[nummois];
                }							
																		
			var previousPoint = null;
                $(\"#chart_2\").bind(\"plothover\", function (event, pos, item) {
                    $(\"#x\").text(pos.x.toFixed(2));
                    $(\"#y\").text(pos.y.toFixed(2));

								
								
                    if (item) {
                        if (previousPoint != item.dataIndex) {
                            previousPoint = item.dataIndex;

                            $(\"#tooltip\").remove();
                            var x = item.datapoint[0],
                                y = item.datapoint[1].toFixed(2);

                            showTooltip(item.pageX, item.pageY, \"<b>\" + retournerMois(x) + \"</b><br>\" +  item.series.label + \" : <b>\" + y + \"€</b>\");
                        }
                    } else {
                        $(\"#tooltip\").remove();
                        previousPoint = null;
                    }
                });								
											
		});
		</script>";
	
	//************************************************** MOYENS PAIEMENT ************************************************

	$execMoyenPaiement = $DB_site->query("SELECT DISTINCT(moyenid) 
											FROM facture 
											WHERE moyenid > 0 
											AND deleted = '0'");

	while( $moyenPaiement = $DB_site->fetch_array($execMoyenPaiement)){
		$infosMoyen=$DB_site->query_first("SELECT * FROM moyenpaiement_site
											WHERE siteid = '1' 
											AND moyenid = '$moyenPaiement[moyenid]'");
		
		$nbTotalPaiement0=$DB_site->query_first("SELECT COUNT(factureid) 
												FROM facture 
												WHERE userid = '$user' 
												AND moyenid = '$moyenPaiement[moyenid]'");

		$nbPaiementCetteAnnee0=$DB_site->query_first("SELECT COUNT(factureid) 
														FROM facture 
														WHERE userid = '$user' 
														AND moyenid = '$moyenPaiement[moyenid]'
														AND( datefacture LIKE '%$cetteannee-%')");

		$nbPaiementCeMois0 = $DB_site->query_first("SELECT COUNT(factureid) 
													FROM facture 
													WHERE userid = '$user' 
													AND moyenid = 'moyenPaiement[moyenid]' 
													AND datefacture LIKE '%$anneemois-%'");

		$nbPaiementCetteSemaine0 = $DB_site->query_first("SELECT COUNT(factureid) 
															FROM facture WHERE userid = '$user' 
															AND moyenid = '$moyenPaiement[moyenid]' 
															AND ( datefacture LIKE '%$anneemoissemaine1%' 
															OR datefacture LIKE '%$anneemoissemaine2%' 
															OR datefacture LIKE '%$anneemoissemaine3%'   
															OR datefacture LIKE '%$anneemoissemaine4%'  
															OR datefacture LIKE '%$anneemoissemaine5%'  
															OR datefacture LIKE '%$anneemoissemaine6%'  
															OR datefacture LIKE '%$anneemoissemaine7%' )");

		$nbTotalPaiement = $nbTotalPaiement0[0];
		$nbPaiementCetteAnnee = $nbPaiementCetteAnnee0[0];
		$nbPaiementCeMois = $nbPaiementCeMois0[0];
		$nbPaiementCetteSemaine = $nbPaiementCetteSemaine0[0];
	
		$typePaiement = $infosMoyen[libelle];
		
		if($nbTotalPaiement){
			eval(charge_template($langue,$referencepage,"RecapCommandeMoyenBit"));
		}		
	}
	eval(charge_template($langue,$referencepage,"RecapCommande"));
		

	$execDetailCommande = $DB_site->query("SELECT * FROM facture 
											WHERE userid = '$utilisateur[userid]' 
											ORDER BY factureid DESC");
											
	while($detailCommande = $DB_site->fetch_array($execDetailCommande)){
		$modePaiement = $DB_site->query_first("SELECT libelle 
										FROM moyenpaiement_site m
										INNER JOIN facture f 
										ON m.moyenid = f.moyenid 
										WHERE userid = '$utilisateur[userid]' 
										AND m.siteid = 1
										AND m.moyenid = '$detailCommande[moyenid]'");
		$etatFacture = $DB_site->query_first("SELECT libelle, couleur 
										FROM etatfacture_langue el
										INNER JOIN etatfacture e
										ON e.etatid = el.etatid
										WHERE e.etatid = '$detailCommande[etatid]'");

		
		$detailCommande[datefacture]=convertirDateEnChaine($detailCommande[datefacture]);
		
		$deleted="";
		if($detailCommande[deleted] == 1){
			$deleted=" (Supprimée)";
		}
		if($detailCommande[numerofacture] == 0){
			$detailCommande[numerofacture] = "-";
		}
		
		$detailCommande[montanttotal_ttc]=formaterPrix($detailCommande[montanttotal_ttc]);

		eval(charge_template($langue, $referencepage, "DetailCommandeBit"));
	}
		
	$totalFactTtc = $DB_site->query_first("SELECT ROUND(SUM(montanttotal_ttc),2) as total 
												FROM facture 
												WHERE userid = '$utilisateur[userid]'
												AND etatid = 5 ");
		
	$totalCmdEnCours = $DB_site->query_first("SELECT ROUND(SUM(montanttotal_ttc),2) as Cmd_total
													FROM facture
													WHERE userid = '$utilisateur[userid]'
													AND etatid = 1 ");
													
	$cmdAttente = $DB_site->query_first("SELECT ROUND(SUM(montanttotal_ttc),2) as attente
												FROM facture
												WHERE userid = '$utilisateur[userid]'
												AND etatid = 0 ");
												
	$totalCmd = $DB_site->query_first("SELECT ROUND(SUM(montanttotal_ttc),2) as sum
											FROM facture
											WHERE userid = '$utilisateur[userid]'");
			
	$totalFactTtc[total]=formaterPrix($totalFactTtc[total]);
	$totalCmdEnCours[Cmd_total]=formaterPrix($totalCmdEnCours[Cmd_total]);
	$cmdAttente[attente]=formaterPrix($cmdAttente[attente]);
	$totalCmd[sum]=formaterPrix($totalCmd[sum]);
									
	eval(charge_template($langue, $referencepage, "TotauxCommandeBit"));
		
	eval(charge_template($langue, $referencepage, "DetailCommande"));

	/******************** RECAP ECHANGES **********************************/
	$tab_echanges=array();
	$iechange=0;
	//AFFICHAGE DETAIL MAIL
	$emailsSend = $DB_site->query("SELECT * FROM emails_envoyes WHERE destinataire = '$utilisateur[mail]' ORDER BY emailenvoyeid DESC");	
	if ($DB_site->num_rows($emailsSend) > 0){		
		while ($emailSend=$DB_site->fetch_array($emailsSend)){			
			$dateMail = date("d/m/Y H:i:s", $emailSend[dateline]);
			// $emailSend[contenu] = utf8_decode(gzuncompress($emailSend[contenu]));
			$emailSend[contenu] = utf8_decode($emailSend[contenu]);
			$emailSend[contenucoupe] = substr(strip_tags($emailSend[contenu]), 0, 100)."...";
			eval(charge_template($langue, $referencepage, "RecapMailBit"));
			
			$tab_echanges[$iechange][timestamp] = $emailSend[dateline];
			$tab_echanges[$iechange][date] = $dateMail;
			$tab_echanges[$iechange][type] = "<i class=\"fa fa-envelope-o\"></i> Email automatique";
			$tab_echanges[$iechange][contenu] = $emailSend[contenucoupe];
			$tab_echanges[$iechange][table] = "emails_envoyes";
			$tab_echanges[$iechange][identifiant] = $emailSend[emailenvoyeid];
			
			$tab_echanges[$iechange][details] ="
				<a href=\"#detail_echange$iechange\" data-toggle=\"modal\"><i>Details</i></a>
					<div aria-hidden=\"true\" aria-labelledby=\"detail_echange$iechange\" role=\"dialog\" tabindex=\"-1\" class=\"modal fade\" id=\"detail_echange$iechange\" style=\"display:none;\">
						<div class=\"modal-dialog\">
							<div class=\"modal-content\">
								<div class=\"modal-header\">
									<button aria-hidden=\"true\" data-dismiss=\"modal\" class=\"close\" type=\"button\"></button>
									<h4 class=\"modal-title\">Email envoyé à <b>\"$emailSend[destinataire]\"</b></h4>
								</div>
								<div class=\"modal-body\">
									<b>Date : </b>$dateMail<br>
									<b>Sujet : </b>$emailSend[sujet] <br>
									<b>Contenu : </b><br><br>
									$emailSend[contenu]									
									<div class=\"clearfix\"></div>
								</div>
							</div>
							<div class=\"clearfix\"></div>
						</div>
					</div>";
			
			$iechange++;
		}
		//eval(charge_template($langue, $referencepage, "RecapMail"));
	}
	
	//AFFICHAGE REPONSES FORMULAIRES
	$reponses = $DB_site->query("SELECT * FROM formulaire_reponse
									WHERE userid = '$utilisateur[userid]' ORDER BY date DESC, heure DESC");
	if ($DB_site->num_rows($reponses) > 0){
		while ($reponse = $DB_site->fetch_array($reponses)){
			$statuts = $DB_site->query("SELECT * FROM formulaire_reponse_statut ORDER BY statutid");
			$TemplateClientsRecapReponseStatuts = "";
			$TemplateClientsRecapReponseStatutsBit = "";
			while ($statut = $DB_site->fetch_array($statuts)){
				if ($reponse[statutid] == $statut[statutid])
					$selected = "selected";
				else
					$selected = "";
				eval(charge_template($langue, $referencepage, "RecapReponseStatutsBit"));
			}
			eval(charge_template($langue, $referencepage, "RecapReponseStatuts"));
			
			$TemplateClientsDetailsReponsesBit2="";
			$champs = $DB_site->query("SELECT * FROM formulaire_reponse_champ WHERE formulairereponseid = '$reponse[formulairereponseid]'");
			while ($champ = $DB_site->fetch_array($champs)){
				eval(charge_template($langue, $referencepage, "DetailsReponsesBit2"));
			}
			eval(charge_template($langue, $referencepage, "DetailsReponsesBit"));
			
			eval(charge_template($langue, $referencepage, "RecapReponseBit"));
											
			$split_date = explode("-",$reponse[date]);
			$annee = $split_date[0];
			$mois = $split_date[1];
			$jour = $split_date[2];
			
			$split_heure = explode(":",$reponse[heure]);
			$heure = $split_heure[0];
			$minute = $split_heure[1];
			$seconde = $split_heure[2];
			
			$reponse[timestamp] = mktime($heure,$minute,$seconde,$mois,$jour,$annee);
			
			$reponse[date_heure] = date("d/m/Y H:i:s", $reponse[timestamp]);
			
			$tab_echanges[$iechange][timestamp] = $reponse[timestamp];
			$tab_echanges[$iechange][date] = $reponse[date_heure];
			$tab_echanges[$iechange][type] = "<i class=\"fa fa-keyboard-o \"></i> Réponse formulaire";
			$tab_echanges[$iechange][contenu] = $reponse[page];
			$tab_echanges[$iechange][table] = "formulaire_reponse";
			$tab_echanges[$iechange][identifiant] = $reponse[formulairereponseid];
			
			$tab_echanges[$iechange][details] ="
				<a href=\"#detail_echange$iechange\" data-toggle=\"modal\"><i>Details</i></a>
				$TemplateClientsDetailsReponsesBit";
			
			$iechange++;
		}
		//eval(charge_template($langue, $referencepage, "RecapReponse"));
	}

	//AFFICHAGE DES COMMENTAIRES SUR LE CLIENT
	$commentaires = $DB_site->query("SELECT * FROM utilisateur_commentaire
										WHERE userid = '$utilisateur[userid]' AND deleted='0' ORDER BY date DESC");
	if ($DB_site->num_rows($commentaires) > 0){
		while ($commentaire = $DB_site->fetch_array($commentaires)){
			$TemplateClientsCommentairesBitSuppression="";
			
			
			$commentaire[date_eu] = date("d/m/Y H:i:s",$commentaire[date]);
			
			$utilisateur_admin_commentaire = $DB_site->query_first("SELECT * FROM admin_utilisateur WHERE userid = '$commentaire[adminid]'");
			if($user_info[userid] == $commentaire[adminid]){
				eval(charge_template($langue, $referencepage, "CommentairesBitSuppression"));
			}
			
			eval(charge_template($langue, $referencepage, "CommentairesBit"));
			
			$tab_echanges[$iechange][timestamp] = $commentaire[date];
			$tab_echanges[$iechange][date] = $commentaire[date_eu];
			$tab_echanges[$iechange][type] = "<i class=\"fa fa-comments-o\"></i> Commentaire interne";
			$tab_echanges[$iechange][contenu] = $commentaire[commentaire];
			$tab_echanges[$iechange][table] = "utilisateur_commentaire";
			$tab_echanges[$iechange][identifiant] = $commentaire[commentaireid];
			
			$tab_echanges[$iechange][details] ="
				<a href=\"#detail_echange$iechange\" data-toggle=\"modal\"><i>Details</i></a>
					<div aria-hidden=\"true\" aria-labelledby=\"detail_echange$iechange\" role=\"dialog\" tabindex=\"-1\" class=\"modal fade\" id=\"detail_echange$iechange\" style=\"display:none;\">
						<div class=\"modal-dialog\">
							<div class=\"modal-content\">
								<div class=\"modal-header\">
									<button aria-hidden=\"true\" data-dismiss=\"modal\" class=\"close\" type=\"button\"></button>
									<h4 class=\"modal-title\">Commentaire interne de <b>\"$utilisateur_admin_commentaire[username]\"</b></h4>
								</div>
								<div class=\"modal-body\">
									<b>Auteur : </b>$utilisateur_admin_commentaire[username]<br>
									<b>Date : </b>$commentaire[date_eu]<br>
									<br>
									<b>Commentaire : </b><br>								
									$commentaire[commentaire]
								</div>
							</div>
						</div>
					</div>";
			
			$iechange++;		
		}
		//eval(charge_template($langue, $referencepage, "Commentaires"));
	}
	
	
	
	//AFFICHAGE DES COMMENTAIRES SUR LES COMMANDES DU CLIENT
	$commentaires_fact = $DB_site->query("SELECT *, fc.commentaire AS commentaire_fact, fc.date AS commentaire_date   FROM facture_commentaires AS fc
											INNER JOIN facture AS f USING (factureid)
											WHERE f.userid = '$utilisateur[userid]' ORDER BY date DESC");
	
	if ($DB_site->num_rows($commentaires_fact) > 0){
		while ($commentaire_fact = $DB_site->fetch_array($commentaires_fact)){
			$utilisateur_admin_commentaire = $DB_site->query_first("SELECT * FROM admin_utilisateur WHERE userid = '$commentaire_fact[adminid]'");			
			$commentaire_fact[date_eu] = date("d/m/Y H:i:s",$commentaire_fact[commentaire_date]);	
			
			
			$split_timestamp = explode(" ",$commentaire_fact[timestamp]);
			$commentaire_fact[heure] = $split_timestamp[1];
			
			$split_heure = explode(":",$commentaire_fact[heure]);
			$heure = $split_heure[0];
			$minute = $split_heure[1];
			$seconde = $split_heure[2];
			
			$split_date = explode("-",$split_timestamp[0]);
			$annee = $split_date[0];
			$mois = $split_date[1];
			$jour = $split_date[2];
			
			$commentaire_fact[timestamp] = "$jour/$mois/$annee ".$multilangue[a]." $commentaire_fact[heure]";
			
			if($commentaire_fact[numerofacture] == 0){
				$commentaire_fact[numerofacture]="-";
			}			
			
			eval(charge_template($langue, $referencepage, "CommentairesCommandesBit"));
			
			$commentaire_fact[timestamp2] = mktime($heure,$minute,$seconde,$mois,$jour,$annee);
				
			$commentaire_fact[date_heure] = date("d/m/Y H:i:s", $commentaire_fact[commentaire_date]);
			
			$commentaire_fact[date_heure_commande] = date("d/m/Y H:i:s", $commentaire_fact[timestamp2]);
			
			$tab_echanges[$iechange][timestamp] = $commentaire_fact[timestamp2];
			$tab_echanges[$iechange][date] = $commentaire_fact[date_heure];
			$tab_echanges[$iechange][type] = "<i class=\"fa fa-comment\"></i> Commentaire commande";
			$tab_echanges[$iechange][contenu] = $commentaire_fact[commentaire_fact];
			$tab_echanges[$iechange][table] = " facture_commentaires";
			$tab_echanges[$iechange][identifiant] = $commentaire_fact[commentaireid];
			
			$tab_echanges[$iechange][details] ="
				<a href=\"#detail_echange$iechange\" data-toggle=\"modal\"><i>Details</i></a>
					<div aria-hidden=\"true\" aria-labelledby=\"detail_echange$iechange\" role=\"dialog\" tabindex=\"-1\" class=\"modal fade\" id=\"detail_echange$iechange\" style=\"display:none;\">
						<div class=\"modal-dialog\">
							<div class=\"modal-content\">
								<div class=\"modal-header\">
									<button aria-hidden=\"true\" data-dismiss=\"modal\" class=\"close\" type=\"button\"></button>
									<h4 class=\"modal-title\">Commentaire sur commande de <b>\"$utilisateur_admin_commentaire[username]\"</b></h4>
								</div>
								<div class=\"modal-body\">
									<b>Date commande : </b>$commentaire_fact[date_heure_commande]<br>
									<b>Montant commande : </b>$commentaire_fact[montanttotal_ttc] € TTC<br>
									<br>
									<b>Auteur commentaire : </b>$utilisateur_admin_commentaire[username]<br>
									<b>Date commentaire : </b>$commentaire_fact[date_heure]<br><br>
									<b>Commentaire : </b>
									<br>
									$commentaire_fact[commentaire_fact]
								</div>
							</div>
						</div>
					</div>";
			
			$iechange++;			
		}
		//eval(charge_template($langue, $referencepage, "CommentairesCommandes"));
	}
	
	// Ca marche !!!
	/*foreach ($tab_echanges as $key => $row) {
		$timestamp[$key] = $row['timestamp'];
	}	
	array_multisort($tab_echanges, SORT_DESC, $tab_echanges);	*/
	//print_r($tab_echanges);
	
	foreach ($tab_echanges as $key => $row) {
		$echangebit = $row;		
		eval(charge_template($langue, $referencepage, "EchangesBit"));
	}
	
	eval(charge_template($langue, $referencepage, "Echanges"));
	/******************** FIN RECAP ECHANGES **********************************/
	
	
	// DEBUT DETAILS DES PANIERS DE L'UTILISATEUR (les 30 derniers jours)	
	$panierscount=$DB_site->query_first("SELECT count(*) from panier p INNER JOIN utilisateur u ON (p.userid=u.userid) WHERE p.userid = '$user' ");
	
	//on refait tous les calculs puisque'on ne peut pas faire : $panier = new Panier($panierid); $montantTTC = $panier->getMontantTTC(); foreach($panier->getLignesPanier() as $lignepanier){ $qte = $lignepanier->getQte();} ...
	if($panierscount[0]){
		
		$orderby = "ORDER BY datepanier DESC, p.panierid DESC";
		
		$rq_panier = $DB_site->query("SELECT p.panierid, datepanier, u.mail, p.cadeauid
										FROM panier p
										INNER JOIN utilisateur u ON (p.userid = u.userid)
										WHERE p.userid = '$user'
										$orderby");

		if ($DB_site->num_rows($rq_panier) > 0){	
			$i_paniers = 1;
			while ($rs_panier=$DB_site->fetch_array($rq_panier)){
				$nb_articles_panier = articlesDansPanier($DB_site, $rs_panier['panierid']) ;
				if($nb_articles_panier>0){
					$totaux = calculerTotalPanier($DB_site, $rs_panier['panierid']);
					$sousTotalHTE = formaterPrix($totaux[sousTotalHT]) ;
					$sousTotalTTCE = formaterPrix($totaux[sousTotalTTC]) ;
					$montantPortHTE = formaterPrix($totaux[montantPortHT]) ;
					$montantPortTTCE = formaterPrix($totaux[montantPortTTC]) ;
					$montantTVAE = formaterPrix($totaux[montantTVA]) ;
					$totalHTE = formaterPrix($totaux[totalHT]) ;
					$totalTTCE = formaterPrix($totaux[totalTTC]) ;
					if($deviseid != 1)	{
						$sousTotalHTDE = formaterPrix($totaux[sousTotalHTD]) ;
						$sousTotalTTCDE = formaterPrix($totaux[sousTotalTTCD]) ;
						$montantPortHTDE = formaterPrix($totaux[montantPortHTD]) ;
						$montantPortTTCDE = formaterPrix($totaux[montantPortTTCD]) ;
						$montantTVADE = formaterPrix($totaux[montantTVAD]) ;
						$totalHTDE = formaterPrix($totaux[totalHTD]) ;
						$totalTTCDE = formaterPrix($totaux[totalTTCD]) ;
					}
					// Date de livraison globale
					afficherDateLivraisonGlobale($DB_site, $rs_panier['panierid'], "panier", $referencepage);
				}else{
					$sousTotalHTE = $sousTotalTTCE = $montantPortHTE = $montantPortTTCE = $montantTVAE = $totalHTE = $totalTTCE = formaterPrix(0) ;
					if($deviseid != 1){
						$sousTotalHTDE = $sousTotalTTCDE = $montantPortHTDE = $montantPortTTCDE= $montantTVADE = $totalHTDE = $totalTTCDE = formaterPrix(0) ;
					}
				}								
				$rowalt = "td_users" . getrowbg();
				list ($annee, $mois, $jour) = explode("-", $rs_panier[datepanier]);
				
				$rs_facture = $DB_site->query_first("SELECT f.factureid, efl.libelle, f.etatid, mps.libelle as moyenpaiement, f.numerofacture
														FROM facture f
														INNER JOIN etatfacture_langue efl ON (f.etatid = efl.etatid)
														INNER JOIN moyenpaiement_site mps ON (f.moyenid = mps.moyenid)
														WHERE f.panierid = '$rs_panier[panierid]'
														ORDER BY factureid DESC");

				if($rs_facture[numerofacture] == 0){
					$affNumFact="--";
				}else{
					$affNumFact=$rs_facture[numerofacture];
				}
				
				$affEtat=retournerLibelleEtatFacture($DB_site, $rs_facture[etatid], 1);
				
				$lignepaniertmps = $DB_site->query("SELECT lp.lignepanierid,lp.lp_artcode, asite.libelle AS libellearticle, lp.qte, lp.lp_prix, lp_prixperso
														FROM lignepanier AS lp
														INNER JOIN article AS a ON (a.artid = lp.artid)
														INNER JOIN article_site AS asite ON (a.artid = asite.artid)
														WHERE lp.panierid = '$rs_panier[panierid]' 
														AND asite.siteid='1'
														ORDER BY lp.lignepanierid");
				$TemplateClientsDetailPaniersBitLigneBit="";
				while ($lignepaniertmp = $DB_site->fetch_array($lignepaniertmps)){					
					if ($lignepaniertmp[qte] > 0){
						$caracteristiques = $DB_site->query("SELECT cvs.libelle AS libellecaractval,cs.libelle AS libelleCaract
															FROM  lignepaniercaracteristique AS lpc
															INNER JOIN caracteristiquevaleur AS cv ON lpc.caractvalid = cv.caractvalid
															INNER JOIN caracteristiquevaleur_site AS cvs ON cvs.caractvalid = cv.caractvalid
															INNER JOIN caracteristique AS c ON cv.caractid = c.caractid
															INNER JOIN caracteristique_site AS cs ON cs.caractid = c.caractid
															WHERE lpc.lignepanierid = '$lignepaniertmp[lignepanierid]'
															AND cs.siteid='1'
															AND cvs.siteid='1'
															ORDER BY c.position, cv.position");

						while($caracteristique = $DB_site->fetch_array($caracteristiques)){
							$listeCaracteristiques .= "[$caracteristique[libelleCaract] : $caracteristique[libellecaractval]]";
						}
						if(!empty($lignepaniertmp['lp_prixperso'])){
							$personnalisation  = ' + personnalisation : '.formaterPrix($lignepaniertmp['lp_prixperso'])." $symboleMonetaire";
						}						
						$prixLignePanier = ($lignepaniertmp['lp_prix']+$lignepaniertmp['lp_prixperso'])*$lignepaniertmp['qte'];
												
						$prixLignePanierE=formaterPrix($lignepaniertmp[lp_prix]);
						$prixLignePanierTotalE=formaterPrix($prixLignePanier);
						
						unset($listeCaracteristiques,$personnalisation);
					}					
					eval(charge_template($langue,$referencepage,"DetailPaniersBitLigneBit"));
				}						
				$i_paniers++;				
				eval(charge_template($langue,$referencepage,"DetailPaniersBit"));
			}
		}
		eval(charge_template($langue,$referencepage,"DetailPaniers"));
	}
	// FIN DETAILS DES PANIERS DE L'UTILISATEUR (les 30 derniers jours)

	$libNavigSupp="Détails du client N°$user";
	eval(charge_template($langue,$referencepage,"NavigSupp"));
}
// FIN AFFICHAGE DETAIL CLIENT


// DEBUT AFFICHER LES UTILISATEURS EN LIGNE
if (isset($action) && $action == "utilisateursactifs"){

	$listePays=retournerListePaysComplete($DB_site);

	$where .= " AND datedernieraction > '".(time()-1200)."'";

	//compte le nombre d'utilisateurs
	$utilisateurcount = $DB_site->query_first("SELECT count(*) FROM utilisateur WHERE userid>0 $where");

	$rq_client=$DB_site->query("SELECT * FROM utilisateur WHERE userid>0 $where");

	if ($DB_site->num_rows($rq_client) > 0) {
		$i = 0 ;
		$k=1;
		while ($rs_client=$DB_site->fetch_array($rq_client)) {
			$rowalt = "td_users".getrowbg();
			$i++;
			list($annee, $mois, $jour) = explode( "-", $rs_client[dateinscription]);
			
			$lien_connexion = "http://$host/V2/client.htm?action=logging&cryptage=no&mail_logging=$rs_client[mail]&pass_logging=$rs_client[password]";
				
			$pays_user = retournerLibellePays($DB_site, $rs_client[paysid]);

			$k++;
			eval(charge_template($langue,$referencepage,"ListeEnLigneBit"));
		}
	}

	eval(charge_template($langue,$referencepage,"ListeEnLigne"));
	$libNavigSupp=$multilangue[liste_clients_enligne];
	eval(charge_template($langue,$referencepage,"NavigSupp"));
}
// FIN AFFICHER LES UTILISATEURS EN LIGNE

// DEBUT AFFICHER LES UTILISATEURS
if (!isset($action) || $action == "" || $action == "rechercher"){
	
	$listePays=retournerListePaysComplete($DB_site);

	if (isset($nb_commandes) && $nb_commandes != 0) {		
		$where = " AND userid IN (SELECT  userid  FROM facture as f WHERE etatid IN ('1','5') GROUP BY userid HAVING count(f.factureid) = '$nb_commandes')";
	}
	
	if (isset ($lst_trietat) && $lst_trietat != "" && $lst_trietat != "-1") {
		$selected0 = "";
		$selected1 = "";
		$selected2 = "";		
		switch ($lst_trietat) {
			case 0:
				$selected0 = "selected";
				$orderby = "ORDER BY dateinscription DESC";
				break;
			case 1:
				$selected1 = "selected";
				$orderby = "ORDER BY nom, prenom";
				break;
			case 2:
				$selected2 = "selected";
				$jointure = "INNER JOIN pays AS p USING(paysid)";
				$orderby = "ORDER BY p.libelle";
				break;
			}
		}
	else
		$orderby = "ORDER BY userid DESC";
		
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
	if (isset($rs) and $rs != "") {
		$where .= " AND raisonsociale LIKE '%$rs%'" ;
	}		
	if (isset($useridrech) and $useridrech != "" && is_numeric ($useridrech)) {
		$where .= " AND userid = '$useridrech'" ;
	}	
	if (isset($mail) and $mail != ""){
		$where .= " AND mail LIKE '%$mail%'" ;
	}
	if (isset($date) and $date != "") {
		list($jour, $mois, $annee) = explode("/", $date);
		$date = "$annee-$mois-$jour" ;
		$where .= " AND dateinscription ='$date' ";
	}
	
	if($action == "utilisateursactifs"){
		$where .= " AND datedernieraction > '".(time()-1200)."'";
	}
	
	if (isset($pro) && $pro != ""){
		switch($pro){
			case 0:
				$where .= " AND pro ='0' ";
			break;
			case 1:
				$where .= " AND pro ='1' ";
			break;	
		}
	}
	//compte le nombre d'utilisateurs	
	$utilisateurcount = $DB_site->query_first("SELECT count(*) FROM utilisateur WHERE userid>0 $where");
	
	/*$rq_client=$DB_site->query("SELECT * FROM utilisateur $jointure WHERE userid>0 $where $orderby LIMIT $limitlower, $perpage");
	
	if ($DB_site->num_rows($rq_client) > 0) {	
		$i = 0 ;
		$k=1;
		while ($rs_client=$DB_site->fetch_array($rq_client)) {
			$rowalt = "td_users".getrowbg();
			$i++;
			list($annee, $mois, $jour) = explode( "-", $rs_client[dateinscription]);
						
			$lien_connexion = "http://$host/V2/client.htm?action=logging&cryptage=no&mail_logging=$rs_client[mail]&pass_logging=$rs_client[password]";
			
			$pays_user = retournerLibellePays($DB_site, $rs_client[paysid]);

			$k++;
			eval(charge_template($langue,$referencepage,"ListeBit"));
		}
	}*/
	
	eval(charge_template($langue,$referencepage,"Liste"));
	eval(charge_template($langue,$referencepage,"InitTable"));
	$libNavigSupp=$multilangue[liste_clients];
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
