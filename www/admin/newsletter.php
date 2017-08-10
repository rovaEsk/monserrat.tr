<?php
include "./includes/header.php";

$referencepage="newsletter";
$pagetitle = "Gestion des newsletter - $host - Admin Arobases";

if(!parse_template($referencepage, $langue)){
	echo "erreur de chargement de template";
}


$display_none_ecriture = $display_none_suppression = "";
if(!$admin_droit[$scriptcourant][ecriture]){
	$display_none_ecriture = "style=\"display:none;\"";
}
if(!$admin_droit[$scriptcourant][suppression]){
	$display_none_suppression = "style=\"display:none;\"";
}

// AFFICHAGE ALERTES SUCCES ET ERREUR
if(isset($erreurdroits) and $erreurdroits == 1){
	$texteErreur = $multilangue[action_page_refuse];
	eval(charge_template($langue, $referencepage, "Erreur"));
}

// AJOUT RAPIDE D'UNE ADRESSE
if(isset($action) and $action == "ajout_rapide"){
	if($admin_droit[$scriptcourant][ecriture]){
		if (isset($ad_mail) && $ad_mail != ""){
			$msg_erreur_saisi = "" ;
			if (!preg_match('/^[a-z0-9&\'\.\-_\+]+@[a-z0-9\-]+\.([a-z0-9\-]+\.)*+[a-z]{2}/is', $ad_mail)){
				$texteErreur = $multilangue[email_incorrect];
			}
			if ($texteErreur == ""){
				$ad_existe_newsletters=$DB_site->query_first("SELECT adresse_mail FROM mails_newsletter WHERE adresse_mail = '$ad_mail'");
				$ad_existe_users=$DB_site->query_first("SELECT mail as adresse_mail FROM utilisateur WHERE mail = '$ad_mail'");
				$ad_existe_gl = 0 ;
				if ($ad_existe_newsletters[adresse_mail] != ""){
					$msg_saisi = "$multilangue[email_existe_deja] $multilangue[email_ajoute]" ;
					$modif_ad=$DB_site->query("update mails_newsletter set allow_email = '1' WHERE  adresse_mail = '$ad_mail'");
					$ad_existe_gl = 1 ;
				}
				if ($ad_existe_users[adresse_mail] != ""){
					$msg_saisi = "$multilangue[email_existe_deja] $multilangue[email_ajoute]" ;
					$modif_ad=$DB_site->query("update utilisateur set recevoir = '1' WHERE mail = '$ad_mail'");
					$ad_existe_gl = 1 ;
				}
				if ($ad_existe_gl == '0'){
					$msg_saisi = "$multilangue[email_ajoute]" ;
					$ajt_ad=$DB_site->query("INSERT INTO mails_newsletter (adresse_mail, allow_email) VALUES ('$ad_mail', '1')");
				}
			}else{
				eval(charge_template($langue, $referencepage, "Erreur"));
			}
		}elseif(empty($_FILES['fichier_mails']['name'])){
			$texteErreur = $multilangue[email_obligatoire];
			eval(charge_template($langue, $referencepage, "Erreur"));
		}
		$action="";
	}else{
		$texteErreur = "$multilangue[action_page_refuse]." ;
		eval(charge_template($langue, $referencepage, "Erreur"));
		$action="";
	}
}



// AJOUTER DES ADRESSES VIA IMPORT CSV
if ($action == "importCsv"){
	if($admin_droit[$scriptcourant][ecriture]){
		$msg_erreur_importcsv = "" ;
		$msg_importcsv = "" ;
		if(!empty($selectimportnewss) && $selectimportnewss!=-1){
			$lib_importcsv=" yes ";
		}
		if($lib_importcsv=="" && isset($lib_importcsv)) {
			//$msg_erreur_importcsv = "$multilangue[libelle_obligatoire]<br>" ;
			$lib_importcsv = "Import ".date("d/m/Y H:i:s");
		}
		if($msg_erreur_importcsv == "") {
			$dossier_ajouter=$rootpath."configurations/$host/importcsv";
			if (!file_exists($dossier_ajouter))
				mkdir($dossier_ajouter,0777);
			$nb_mail = 0;
			$nb_mail2 = 0;
			$nb_mail3=0;
			if ($_FILES['fichier']['name']!=""){
				if(define_extention($_FILES['fichier']['name']) == "csv"){
					if(!empty($selectimportnewss) && $selectimportnewss!=-1){
						$import_newsletterid=$selectimportnewss;
						$lib_importcsv="";
					}else{
						$ajt_import_table=$DB_site->query("INSERT INTO import_newsletter (import_newsletter_date,import_newsletter_lib) VALUES ('".time()."','$lib_importcsv')");
						$import_newsletterid=$DB_site->insert_id();
					}
					$nom_fic=$rootpath."configurations/$host/importcsv/import_mail".date("d-m-Y.H.i.s").".csv" ;
					copier_image($nom_fic,"fichier") ;
					$rh = fopen($nom_fic, 'rb');
					if ($rh) {
						$row = 1 ;
						while ($data = fgetcsv ($rh, 10000, ";")) {
							$num = count ($data);
							for ($c = 0 ; $c < $num ; $c++) 
								$montableau[$row][$c] = $data[$c] ;
							$row++;
						}
					}
					fclose($rh);				
					if ((isset($montableau)) and (count($montableau) > 0)){
						foreach ($montableau as $key => $value) {
						if (!filter_var($montableau[$key][0], FILTER_VALIDATE_EMAIL) === false){
								$mail = addslashes($montableau[$key][0]) ;
								$test=$DB_site->query("SELECT * FROM mails_newsletter WHERE adresse_mail='$mail' ") ;
								$test=$DB_site->num_rows($test);
								$test2=$DB_site->query("SELECT * FROM utilisateur WHERE mail='$mail' ") ;
								$test2=$DB_site->num_rows($test2);
								if($test>0 || $test2>0){
									if($test>0){
										if($test[allow_email] == 0){
											$DB_site->query("UPDATE mails_newsletter SET allow_email='1', import_newsletterid = '$import_newsletterid' WHERE adresse_mail='$mail'") ;
											$nb_mail3++;
										}else{
											$nb_mail2++;
										}
										if($test2[recevoir] == 0){
											$DB_site->query("UPDATE utilisateur SET recevoir='1' WHERE mail='$mail'");
											$nb_mail3++;
										}else{
											$nb_mail2++;
										}
									}								
								}else{
									$DB_site->query("INSERT INTO mails_newsletter SET adresse_mail='$mail' , allow_email='1', siteid='$selectimportsite' import_newsletterid = '$import_newsletterid' ") ;
									$nb_mail++;
								}
							}
						}
						$texteSuccess= $nb_mail." $multilangue[email_s_insere_s] $multilangue[et] ".$nb_mail2." $multilangue[email_s_deja_existant_s] $multilangue[et] ".$nb_mail3." $multilangue[email_s_deja_existant_s_modifies] <br>" ;
						eval(charge_template($langue, $referencepage, "Success"));
					}
				}else{
					$texteErreur = $multilangue[fichier_doit_etre_csv] ;
					eval(charge_template($langue, $referencepage, "Erreur"));
				}
			}else{
				$texteErreur = $multilangue[chemin_obligatoire] ;
				eval(charge_template($langue, $referencepage, "Erreur"));
			}
		}
		$action="";
	}else{
		$texteErreur = "$multilangue[action_page_refuse]." ;
		eval(charge_template($langue, $referencepage, "Erreur"));
		$action="";
	}
}

// SUPPRIMER DES ADRESSES VIA IMPORT CSV
if($action=="supprimer_CSV"){
	if($admin_droit[$scriptcourant][suppression]){
		$dossier_ajouter=$rootpath."configurations/$host/importcsv";
		if (!file_exists($dossier_ajouter))
			mkdir($dossier_ajouter,0777);
		$nb_mail = 0;
		$nb_mail2 = 0;
		if ($_FILES['fichier']['name']!="")	{
			$nom_fic=$rootpath."configurations/$host/importcsv/import_mail_to_delete".date("d-m-Y.H.i.s").".csv" ;
			copier_image($nom_fic,"fichier") ;
			$rh = fopen($nom_fic, 'rb');
			if ($rh){
				$row = 1 ;
				while ($data = fgetcsv ($rh, 10000, ";")){
					$num = count ($data);
					for ($c = 0 ; $c < $num ; $c++){
						$montableau[$row][$c] = $data[$c] ;
					}
					$row++;
				}
			}
			fclose($rh);
			if ((isset($montableau)) and (count($montableau) > 0)){
				foreach ($montableau as $key => $value){
					$mail = addslashes($montableau[$key][0]) ;
					$test=$DB_site->query("SELECT * FROM mails_newsletter WHERE adresse_mail='$mail' ") ;
					$test=$DB_site->num_rows($test);
					$test2=$DB_site->query("SELECT * FROM utilisateur WHERE mail='$mail' ") ;
					$test2=$DB_site->num_rows($test2);
					if($test>0){
						$DB_site->query("UPDATE mails_newsletter SET allow_email = '0' WHERE adresse_mail = '$mail'");
						$nb_mail++;
					}
					if($test2>0){
						$DB_site->query("UPDATE utilisateur SET recevoir = '0' WHERE mail = '$mail'");
						$nb_mail++;
					}
				}
				$texteSuccess = $nb_mail." $multilangue[email_s_desinscrit_s].<br>";
				eval(charge_template($langue, $referencepage, "Success"));
			}
		}else{
			$texteErreur = "$multilangue[chemin_obligatoire] !" ;
			eval(charge_template($langue, $referencepage, "Erreur"));
		}
		$action="";
	}else{
		$texteErreur = "$multilangue[action_page_refuse]." ;
		eval(charge_template($langue, $referencepage, "Erreur"));
		$action="";
	}
}



// SUPPRESSION D'UN IMPORT
if($action=="supprimer_Import"){
	if($admin_droit[$scriptcourant][suppression]){
		if(!empty($selectimportnewsstodelete) && $selectimportnewsstodelete!=-1){
			$infos_import=$DB_site->query_first("SELECT * FROM import_newsletter WHERE import_newsletterid = '$selectimportnewsstodelete'");
			$DB_site->query("UPDATE mails_newsletter SET allow_email ='0' WHERE import_newsletterid = '$selectimportnewsstodelete'");
			$DB_site->query("DELETE FROM import_newsletter WHERE import_newsletterid = '$selectimportnewsstodelete'");
	
			$texteSuccess = "$multilangue[l_import] : <b>$infos_import[import_newsletter_lib]</b> $multilangue[a_bien_ete_supprime].";
			eval(charge_template($langue, $referencepage, "Success"));
			
			$action="";
		}else{	
			$texteErreur = "$multilangue[selection_import_obligatoire] !" ;
			eval(charge_template($langue, $referencepage, "Erreur"));
			$action="";
		}
	}else{		
		$texteErreur = "$multilangue[action_page_refuse]." ;
		eval(charge_template($langue, $referencepage, "Erreur"));
		$action="";
	}
}


// SUPPRESSION RAPIDE D'UNE ADRESSE
if(isset($action) and $action == "suppression_rapide"){
	if($admin_droit[$scriptcourant][suppression]){
		$ad_existe_newsletters=$DB_site->query_first("SELECT adresse_mail FROM mails_newsletter WHERE adresse_mail = '$mailtodelete'");
		$ad_existe_users=$DB_site->query_first("SELECT mail as adresse_mail FROM utilisateur WHERE mail = '$mailtodelete'");
		$ad_existe_gl = 0 ;
		$msg_suppr = "" ;
		if ($ad_existe_newsletters[0] != ""){
			$msg_suppr .= "$multilangue[email_supprime_dans] $multilangue[de_la_liste_des_inscrits_newsletter]." ;
			$ajt_ad=$DB_site->query("update mails_newsletter set allow_email = '0' WHERE adresse_mail = '$mailtodelete'");
			$ad_existe_gl = 1 ;
		}
		if ($ad_existe_users[0] != ""){
			$msg_suppr .= "<br>$multilangue[email_supprime_dans] $multilangue[de_la_liste_des_clients_inscrits_newsletter]." ;
			$ajt_ad=$DB_site->query("update utilisateur set recevoir = '0' WHERE mail = '$mailtodelete'");
			$ad_existe_gl = 1 ;
		}
		if ($ad_existe_gl == '0'){
			$texteErreur = "$multilangue[email_innexistant]  !" ;
			eval(charge_template($langue, $referencepage, "Erreur"));
		}
		$action="";
	}else{		
		$texteErreur = "$multilangue[action_page_refuse]." ;
		eval(charge_template($langue, $referencepage, "Erreur"));
		$action="";
	}
}



// SUPPRIMER UNE ADRESSE NEWSLETTER
if(isset($action) and $action == "supprimerMN"){
	if($admin_droit[$scriptcourant][suppression]){
		$DB_site->query("UPDATE mails_newsletter SET allow_email='0' WHERE adresse_mail = '$emailsuppr'");
		
		$texteSuccess = "$multilangue[l_email] : <b>$emailsuppr</b> $multilangue[a_bien_ete_supprimee] $multilangue[de_la_liste_des_inscrits_newsletter].";
		eval(charge_template($langue, $referencepage, "Success"));
		$action="";
	}else{
		$texteErreur = "$multilangue[action_page_refuse]." ;
		eval(charge_template($langue, $referencepage, "Erreur"));
		$action="";
	}
}

// SUPPRIMER UNE CLIENT DE LA NEWSLETTER
if(isset($action) and $action == "supprimerMC"){
	if($admin_droit[$scriptcourant][suppression]){	
		$user_temp = $DB_site->query_first("SELECT * FROM utilisateur WHERE userid = '$useridsuppr'");
		$DB_site->query("UPDATE utilisateur SET recevoir='0' WHERE userid = '$useridsuppr'");
	
		
		$texteSuccess = "$multilangue[le_client] : <b>$user_temp[nom] $user_temp[prenom] ($user_temp[mail])</b> $multilangue[a_bien_ete_supprime] $multilangue[de_la_liste_des_inscrits_newsletter].";
		eval(charge_template($langue, $referencepage, "Success"));
		$action="";
	}else{
		$texteErreur = "$multilangue[action_page_refuse]." ;
		eval(charge_template($langue, $referencepage, "Erreur"));
		$action="";
	}
}

// SUPPRIMER UNE NEWSLETTER
if(isset($action) and $action == "supprimerN"){
	if($admin_droit[$scriptcourant][suppression]){
		$news_temp = $DB_site->query_first("SELECT * FROM newsletter WHERE newsletterid = '$newsletteridsuppr'");
		
		$DB_site->query("DELETE FROM newsletter WHERE newsletterid = '$newsletteridsuppr'");
		$DB_site->query("DELETE FROM newsletter_site WHERE newsletterid = '$newsletteridsuppr'");
	
		$texteSuccess = "$multilangue[la_newsletter] : <b>$news_temp[libelleadmin]</b> $multilangue[a_bien_ete_supprimee].";
		eval(charge_template($langue, $referencepage, "Success"));
		$action="";
	}else{
		$texteErreur = "$multilangue[action_page_refuse]." ;
		eval(charge_template($langue, $referencepage, "Erreur"));
		$action="";
	}
}

// SUPPRIMER UN MAILING
if(isset($action) and $action == "supprimerM"){
	if($admin_droit[$scriptcourant][suppression]){
		$mailing_temp = $DB_site->query_first("SELECT * FROM mailing WHERE newsletterid = '$mailingidsuppr'");
		$mailing_N_temp = $DB_site->query_first("SELECT * FROM newsletter WHERE newsletterid = '$mailing_temp[newsletterid]'");
	
		$DB_site->query("DELETE FROM mailing WHERE mailingid = '$mailingidsuppr'");
		$DB_site->query("DELETE FROM mailing_envoi WHERE mailingid = '$mailingidsuppr'");
		$DB_site->query("DELETE FROM mailing_liste WHERE mailingid = '$mailingidsuppr'");
		$DB_site->query("DELETE FROM mailing_site WHERE mailingid = '$mailingidsuppr'");
	
		$texteSuccess = "$multilangue[le_mailing] : <b>$mailing_N_temp[libelleadmin]</b> $multilangue[a_bien_ete_supprimee].";
		eval(charge_template($langue, $referencepage, "Success"));
		$action="";
	}else{
		$texteErreur = "$multilangue[action_page_refuse]." ;
		eval(charge_template($langue, $referencepage, "Erreur"));
		$action="";
	}
}

// ENVOYER UNE NEWSLETTER TEST (action=envoyertest2)
if ($action == "envoyertest2"){
	if (isset($monmail) and !filter_var($monmail, FILTER_VALIDATE_EMAIL) === false){
		$texteSuccess="";
		$rs_newsletter=$DB_site->query_first("SELECT * FROM newsletter WHERE newsletterid = '$newsletterid'") ;
		
		$sites=$DB_site->query("SELECT * FROM site ORDER BY siteid");
		while($site=$DB_site->fetch_array($sites)){
			$rs_newsletter_site=$DB_site->query_first("SELECT * FROM newsletter_site 
														WHERE newsletterid = '$newsletterid'
														AND siteid='$site[siteid]'") ;
			
			
			$texteSuccess .= "$multilangue[la_newsletter] : <b>$rs_newsletter_site[libelle]</b> $multilangue[a_bien_ete_envoee] $multilangue[a] $monmail.<br>";
			
			email($DB_site, $monmail, stripslashes($rs_newsletter_site[libelle]), $rs_newsletter_site[contenu], $params[$site[siteid]][mail_contact]);			
		}			
		eval(charge_template($langue, $referencepage, "Success"));
		$action="";
	}else{	
		$texteErreur = "$multilangue[email_incorrect]" ;
		eval(charge_template($langue, $referencepage, "Erreur"));
		$action="";
	}
}

// VOIR UNE NEWSLETTER (action=voir)
if (substr($action,0,4) == "voir"){	
	$siteid=str_replace("voir","",$action);	
	$rs_newsletter = $DB_site->query_first("SELECT * FROM newsletter_site 
												WHERE newsletterid = '$newsletterid'
												AND siteid='$siteid'");

	echo "<html><head><title>$rs_newsletter[libelle]</title></head><body $newsletter_couleur_fond>$rs_newsletter[contenu]</body></html>";
	exit;
}


if($action=="generer2"){
	if($admin_droit[$scriptcourant][ecriture]){
		// On récupere le type de newsletter générer. Suivant ce type, on va charger le template correspondant pour afficher la bonne newsletter.
		$newsletter_type = $_POST['newsletter_type'];
		if($newsletter_type == 1) $newsletter_type = "";	
		$referencenewsletter = "admin_newsletter".$newsletter_type;
		$templatespath = "../configurations/$host/templatesadmin";
		if(!parse_template($referencenewsletter)){
			echo $multilangue[erreur_chargement_template].' -> '.$referencenewsletter;
			exit;
		}
		
		$datedujour = date('Y-m-d');
		
		$DB_site->query("INSERT INTO newsletter (libelleadmin) VALUES ('')");
		$newsletterid = $DB_site->insert_id();
		
		
		$sites = $DB_site->query("SELECT * FROM site ORDER BY siteid");
		while ($site = $DB_site->fetch_array($sites)){		
			$TemplateAdmin_newsletterArticle="";
			if (!parse_template($referencenewsletter, "FR")) {
				echo "$multilangue[erreur_chargement_template] FR";
			}
			
			$activeOperation = "activeV1";
					
			// En premier on prends les valeurs par défaut (siteid à 1)
			$rq_multilangue=$DB_site->query("SELECT * FROM multilangue
										INNER JOIN multilangue_txt USING(multilangueid)
										WHERE siteid='1'");
			$multilangue_news=array();
			while($rs_multilangue=$DB_site->fetch_array($rq_multilangue)){
				$multilangue_news[$rs_multilangue[expression]]=$rs_multilangue[text];
			}		
			// Ensuite on écrase avec la langue en cours
			$rq_multilangue=$DB_site->query("SELECT * FROM multilangue
					INNER JOIN multilangue_txt USING(multilangueid)
					WHERE siteid='$site[siteid]'");
			while($rs_multilangue=$DB_site->fetch_array($rq_multilangue)){
				$multilangue_news[$rs_multilangue[expression]]=$rs_multilangue[text];
			}
			
			$regleurlrewriteTemp = $regleurlrewrite[$site[siteid]];
		
			$textes = $DB_site->query("SELECT * FROM textepersonnel");
			while ($texte=$DB_site->fetch_array($textes)) {
				$variable = "TextePerso$texte[textepersonnelid]";
				${$variable} = $texte[$contenu];
				eval(charge_template($langue, "admin_newsletter", "TextePerso".$texte[textepersonnelid]));
			}
	
			$i=0;
			if($params[articlebit1_dans_newsletter] == 0) $i = 1;
			$j=0;
	
			$TemplateAdmin_newsletterArticleBit1="";
			$TemplateAdmin_newsletterArticleBit2="";
	
			$articles=$DB_site->query("SELECT * FROM newsletter_article AS na 
										INNER JOIN article a USING(artid) 
										INNER JOIN article_site as asite ON asite.artid = a.artid AND asite.siteid='$site[siteid]'
										ORDER BY na.position");
			while ($article=$DB_site->fetch_array($articles)) {
				$TemplateAdmin_newsletterArticleSeparateur1="";
				$TemplateAdmin_newsletterArticleSeparateur2="";
				$i++;
				if ($i == 1 || ($params[articlebit1_dans_newsletter] && $i <= $params[articlebit1_dans_newsletter])) {
					$top = trouve_article($DB_site, $article, "newsletter1", "admin_newsletter", "ArticleBit1", $lglib_newsletter1, $lgdesc_newsletter1);
					if ($params[articlebit1_dans_newsletter] && $i < $params[articlebit1_dans_newsletter]) {
						eval(charge_template($curentlangue, "admin_newsletter", "ArticleSeparateur1"));
					}
					eval(charge_template($curentlangue, "admin_newsletter", "ArticleBit1"));
				}else{
					if ($j == $params[newsletter_nbarticles_par_ligne]){
						$TemplateAdmin_newsletterArticleBit2 .= "</tr><tr>";
						$j = 0;
					}elseif($j < ($params[newsletter_nbarticles_par_ligne]-1)){
						eval(charge_template($curentlangue, "admin_newsletter", "ArticleSeparateur2"));
					}
					$top = trouve_article($DB_site, $article, "newsletter2", "admin_newsletter", "ArticleBit2", $lglib_newsletter2, $lgdesc_newsletter2);
					eval(charge_template($curentlangue, "admin_newsletter", "ArticleBit2"));
					$j++;
				}
				unset($top);
			}
			eval(charge_template($curentlangue, "admin_newsletter", "Article"));
			$contenuheader="contenuheader".$site[siteid];
			$contenufooter="contenufooter".$site[siteid];	
			
			$contenuheader = ${$contenuheader};
			$contenufooter = ${$contenufooter};
				
			$contenuheader=str_replace("src=\"/userfiles/", "src=\"http://$host/userfiles/",$contenuheader);
			$contenufooter=str_replace("src=\"/userfiles/", "src=\"http://$host/userfiles/",$contenufooter);
			
			$TemplateAdmin_newsletterIndex="";
			$TemplateAdmin_newsletterHeader="";
			$TemplateAdmin_newsletterFooter="";
	
			eval(charge_template($curentlangue, "admin_newsletter", "Header"));
			eval(charge_template($curentlangue, "admin_newsletter", "Footer"));
			eval(charge_template($curentlangue, "admin_newsletter", "Index"));
	
			$contenu = addslashes($TemplateAdmin_newsletterIndex);
			
			$DB_site->query("INSERT INTO newsletter_site (newsletterid, siteid, libelle, contenu)
					VALUES ('$newsletterid', '$site[siteid]', '".securiserSql($_POST["titre$site[siteid]"])."', '".$contenu."')");
			
			if($site[siteid] == 1){
				$DB_site->query("UPDATE newsletter SET libelleadmin='".securiserSql($_POST["titre$site[siteid]"])."' WHERE newsletterid='$newsletterid'");
			}		
			
		}
		$texteSuccess = "$multilangue[newsletter_type_generee]";
		eval(charge_template($langue, $referencepage, "Success"));
		$action="";
	}else{
		$texteErreur = "$multilangue[email_incorrect]" ;
		eval(charge_template($langue, $referencepage, "Erreur"));
		$action="";
	}
}


// GENERER UNE NEWSLETTER TYPE (action=generer)
if($action == "generer"){	
	if($admin_droit[$scriptcourant][ecriture]){
		$first = true;
		$arts_newsletter = $DB_site->query("SELECT * FROM newsletter_article AS na
											 	INNER JOIN article_site AS asite ON asite.artid = na.artid
												INNER JOIN article AS a ON asite.artid = a.artid
												WHERE asite.siteid = '1' 
												ORDER BY na.position");
		while($art_newsletter = $DB_site->fetch_array($arts_newsletter)){
			if($first){
				$ordreArticles .= "$art_newsletter[artid]";
				$first = false;
			}else{
				$ordreArticles .= "|$art_newsletter[artid]";
			}
				
			if(strlen($art_newsletter[libelle])>30){
				$texte_article = substr($art_newsletter[libelle], 0, 30);
			}else{
				$texte_article = $art_newsletter[libelle];
			}
			$texte_coup_coeur .= " ($art_newsletter[artcode])";
		
			if($art_newsletter[image] != ""){
				$image_article="<img src='http://$host/br-a-$art_newsletter[artid].$art_newsletter[image]' style='max-width:140px;max-height:100px;'>";
			}else{
				$image_article="";
			}
		
			eval(charge_template($langue,$referencepage,"Articles"));
			eval(charge_template($langue,$referencepage,"ArticlesBoitesBit"));
		}
		
		if($params[generer_newsletter_type]!=0) {		
			for($i=1;$i<=$params[generer_newsletter_type];$i++){
				eval(charge_template($langue,$referencepage,"GenererTypeBit"));
			}	
			eval(charge_template($langue,$referencepage,"GenererType"));
		}
		
		// Une newsletter par site
		$sites = $DB_site->query("SELECT * FROM site ORDER BY siteid");
		while ($site = $DB_site->fetch_array($sites)){
			eval(charge_template($langue,$referencepage,"GenererNewsletterTypeSiteBit"));
		}
		$libNavigSupp = "$multilangue[generer_newsletter_type]";
		eval(charge_template($langue, $referencepage, "NavigSupp"));
	
		eval(charge_template($langue, $referencepage, "GenererNewsletterType"));
	}else{
		$texteErreur = "$multilangue[email_incorrect]" ;
		eval(charge_template($langue, $referencepage, "Erreur"));
		$action="";
	}
}

// AJOUTER BDD NEWSLETTER (action=ajouter2)
if($action == "ajouter2"){
	if($admin_droit[$scriptcourant][ecriture]){
		if($newsletterid == ""){			
			$DB_site->query("INSERT INTO newsletter (libelleadmin) VALUES ('')");
			$newsletterid = $DB_site->insert_id();
			$nouvellenewsletter = 1;
		}		
	
		$sites = $DB_site->query("SELECT * FROM site");
		while($site = $DB_site->fetch_array($sites)){
			
			if($nouvellenewsletter){		
				$DB_site->query("INSERT INTO newsletter_site (newsletterid,siteid) VALUES ('$newsletterid','$site[siteid]')");
			}
			$DB_site->query("UPDATE newsletter_site SET libelle = '".securiserSql($_POST["titre$site[siteid]"])."', contenu = '".securiserSql($_POST["contenu$site[siteid]"], "html")."' WHERE newsletterid = '$newsletterid' AND siteid = '$site[siteid]'");
			if($site[siteid] == 1){
				$DB_site->query("UPDATE newsletter SET libelleadmin='".securiserSql($_POST["titre$site[siteid]"])."' WHERE newsletterid='$newsletterid'");
			}
		}
		
		if($nouvellenewsletter){
			$texteSuccess = "$multilangue[newsletter] : <b>$_POST[titre1]</b> $multilangue[a_bien_ete_cree].";
		}else{
			$texteSuccess = "$multilangue[newsletter] : <b>$_POST[titre1]</b> $multilangue[a_bien_ete_modifiee].";
		}
		
		eval(charge_template($langue, $referencepage, "Success"));
		$action="";
	}else{
		$texteErreur = "$multilangue[email_incorrect]" ;
		eval(charge_template($langue, $referencepage, "Erreur"));
		$action="";
	}
}

// MODIFIER UNE NEWSLETTER (action=modifier)
if($action == "modifier"){	
	if($admin_droit[$scriptcourant][ecriture]){
		// Une newsletter par site
		$sites = $DB_site->query("SELECT * FROM site ORDER BY siteid");
		while ($site = $DB_site->fetch_array($sites)){
			$infosNewsletterEdit = $DB_site->query_first("SELECT * FROM newsletter
														INNER JOIN newsletter_site ON newsletter.newsletterid=newsletter_site.newsletterid
														 WHERE newsletter.newsletterid = '$newsletterid'  AND newsletter_site.siteid = '$site[siteid]'");		
			eval(charge_template($langue,$referencepage,"AjoutNewsletterTypeSiteBit"));
		}
		$libNavigSupp = "$multilangue[modif_newsletter]";
		eval(charge_template($langue, $referencepage, "NavigSupp"));
		
		eval(charge_template($langue, $referencepage, "AjoutNewsletterType"));
	}else{
		header('location: newsletter.php?erreurdroits=1');	
		exit();
	}	
}



// AJOUTER UNE NEWSLETTER (action=ajouter)
if($action == "ajouter"){	
	if($admin_droit[$scriptcourant][ecriture]){
		// Une newsletter par site
		$sites = $DB_site->query("SELECT * FROM site ORDER BY siteid");
		while ($site = $DB_site->fetch_array($sites)){		
			eval(charge_template($langue,$referencepage,"AjoutNewsletterTypeSiteBit"));
		}
		$libNavigSupp = "$multilangue[ajt_newsletter_type]";
		eval(charge_template($langue, $referencepage, "NavigSupp"));
		
		eval(charge_template($langue, $referencepage, "AjoutNewsletterType"));
	}else{
		$texteErreur = "$multilangue[email_incorrect]" ;
		eval(charge_template($langue, $referencepage, "Erreur"));
		$action="";
	}	
}


// ENVOYER UNE NEWSLETTER (action=envoyer)
if ($action == "envoyer"){		
	if($admin_droit[$scriptcourant][ecriture]){
		if ($actionSpe == 'validEtape1'){		
			// init des variables
			$incluretablenewsletter = 0;
			$erreur = "";
			$table_email = array();
			$sql_com_condi = "";
			$secu_com = "";				
			
			if ($particulier == '1' && $pro != '1') {
				$sql_uti_condi .= " AND u.pro = '0'";
			}
			
			if ($pro == '1' && $particulier != '1') {
				$sql_uti_condi .= " AND u.pro = '1'";
			}
			
			// select multi pays
			if (!empty($_POST['paysid']) && $_POST['paysid'][0] != 0) {
				$sql_uti_condi .= " AND (";			
				foreach($_POST['paysid'] as $key => $value){
					if ($key == 0){
						$sql_uti_condi .= " u.paysid = '" . $value . "'";
					}else{
						$sql_uti_condi .= " OR u.paysid = '" . $value . "'";
					}
				}
				$sql_uti_condi .= ")";
			}
			
			if ($newsUnique == '1') {
				
			}
			
			
			switch($destinataires){
				case 0 : // Tous les inscrits à la newsletter
					$sql_import_condi = " AND allow_email = '1'";
					$sql_uti_condi .= " AND u.recevoir='1'";
					$incluretablenewsletter = 1;
					break;
				case 1 : // Seulement les comptes clients souhaitant recevoir la newsletter				
					$sql_uti_condi .= " AND u.recevoir='1'";
					break;
				case 2 : // Seulement les inscrits à la newsletter
					$incluretablenewsletter = 1;
					$sql_import_condi = " AND allow_email = '1'";
					break;
				case 3 : // Tous les comptes clients, même ceux qui ne souhaitent pas recevoir de newsletter.
					
					break;
			}
			
			
			if (in_array ( 5817, $modules ) && $news_partenaireUnique == '1') {
				$sql_uti_condi .= " AND u.recevoir_partenaire='1'";
			}
			
			if ($incluretablenewsletter == '1') {
				if ($selectImport == '1') {
					$rq_select_import = $DB_site->query ( "SELECT adresse_mail, siteid FROM mails_newsletter WHERE import_newsletterid='" . $import_newsletterid . "'" . $sql_import_condi );
					// remplissage des email de l'import dans un tableau
					while ( $arr_select_import = $DB_site->fetch_array ( $rq_select_import ) ) {
						$arr_select_import [mail] = $arr_select_import [adresse_mail];
						array_push ( $table_email, $arr_select_import );
					}
				}else{
					if($news_partenaireUnique != '1'){
						$rq_select_import = $DB_site->query ( "SELECT adresse_mail, siteid FROM mails_newsletter WHERE 1" . $sql_import_condi );
						// remplissage des email de l'import dans un tableau
						while($arr_select_import = $DB_site->fetch_array($rq_select_import)){
							$arr_select_import [mail] = $arr_select_import [adresse_mail];
							array_push($table_email, $arr_select_import);
						}
					}
				}
			}
			
			/* fourchette de la date d'inscription (client ou prospect) */
			if (!empty($inscripDebut)) {			
				// reconversion de la date au format ricain
				$inscripDebutReq = convertirChaineEnDate($inscripDebut);
				$sql_uti_condi .= " AND u.dateinscription >= '" . $inscripDebutReq . "'";
			}
			
			if (!empty($inscripFin)){			
				// reconversion de la date au format ricain
				$inscripFinReq = convertirChaineEnDate($inscripFin);			
				$sql_uti_condi .= " AND u.dateinscription <= '" . $inscripFinReq . "'";
			}
		
			if($acommande == 1 && !empty($acommande)){
				
				/* fourchette de la date de commande */
				if (!empty($lastCommandeDebut)) {				
					// reconversion de la date au format ricain
					$lastCommandeDebutReq = convertirChaineEnDate ( $lastCommandeDebut );				
					$sql_com_condi .= " AND f.datefacture >= '" . $lastCommandeDebutReq . "'";
				}
				
				if (!empty($lastCommandeFin)) {				
					// reconversion de la date au format ricain
					$lastCommandeFinReq = convertirChaineEnDate ( $lastCommandeFin );				
					$sql_com_condi .= " AND f.datefacture <= '" . $lastCommandeFinReq . "'";
				}
				
				// select multi categorie
				if (!empty($_POST['catid']) && $_POST['catid'][0] != 0) {
					$sql_com_condi .= " AND (";
					foreach ($_POST['catid'] as $key => $value ) {
						$listcat = substr (lister_catid($DB_site, $value), 0, - 1 );
						if($key == 0){
							$sql_com_condi .= " lf.catid  IN ($listcat)";
						}else{
							$sql_com_condi .= " OR lf.catid IN ($listcat)";
						}
					}
					$sql_com_condi .= ")";
				}
		
				if (!empty($_POST['articles']) && $_POST['articles'][0] != 0) {
					$sql_com_condi .= " AND (";
					foreach ($_POST['catid'] as $key => $value ){					
						if($key == 0){
							$sql_com_condi .= " lf.artid = '$value'";
						}else{
							$sql_com_condi .= " OR lf.artid = '$value'";
						}
					}
					$sql_com_condi .= ")";
				}
	
				$secu_com = " AND (SELECT COUNT(facsecu.factureid) FROM facture facsecu WHERE facsecu.userid=f.userid AND (facsecu.etatid=1 OR facsecu.etatid=5) ) > 0";
				
			}elseif($acommande == 2 && !empty($acommande)){
				$secu_com = " AND (SELECT COUNT(facsecu.factureid) FROM facture facsecu WHERE facsecu.userid=f.userid AND (facsecu.etatid=1 OR facsecu.etatid=5)) = 0";
			}elseif($acommande == 0 && !empty($acommande)){ // Tous
				$secu_com = "";
			}
				
			// aucun utilisateur
			if ($destinataires == '2') {
				$secu_com = " AND u.mail='vide'";
			}
			
			
			$sql_final = "SELECT u.mail, u.siteid FROM utilisateur u 
							LEFT JOIN facture f ON (u.userid=f.userid) 
							LEFT JOIN lignefacture lf ON (lf.factureid=f.factureid) 
							WHERE 1" . $sql_com_condi . " " . $sql_uti_condi . " " . $secu_com . " 
							GROUP BY u.userid";
			// echo $sql_final;
			$nbmail = count($table_email);
			$rq_recup_user = $DB_site->query ( $sql_final );
			// remplissage des email de l'import dans un tableau
			while ( $arr_recup_user = $DB_site->fetch_array ( $rq_recup_user ) ) {
				$add=true;
				// vérification si l'email n'est pas déjà select
				
				 foreach($table_email as $key => $value){
				 	if($value['mail']==$arr_recup_user[mail]) {
				  		$add=false;
				 	}
				 }
				
				//if (! in_array ( $arr_recup_user [mail], $table_email )) {
				if($add==true) {
					array_push ( $table_email, $arr_recup_user );
					$nbmail ++;
					// echo count($table_email)."<br>";
				}
				// if($nbmail==1000){
				// break;
				// }
			}
			
			/*
			 * -----------------------------interface de test-----------------------
			 * echo $sql_final;
			 * echo "<br>/////////<br>";
			 * foreach($table_email as $key => $value){
			 * echo $key."=>".$value['mail']."<br>";
			 * }
			 * echo "<br>/////////<br>";
			 */
			/* ----------------------------fin interface de test----------------------- */
			
			// sécu si aucun email
			if(count($table_email) <= 0){
				$erreur .= "$multilangue[aucun_email_select_elargir_selection]<br>";
			}
			
			if($erreur == ""){
				if (!empty($export) && $export == 1){ // export csv
					
					$contenu = "e-mail;site";
					foreach($table_email as $key => $value){
						$contenu .= "\n";
						$contenu .= $value['mail'] . ";";
						$contenu .= $tabsites[$value['siteid']][libelle];
					}
					$filename = './csv/email.csv';
					if(is_writable($filename)) {
						if(!$handle = fopen($filename,'w')){
							echo "$multilangue[erreur_ouverture_fichier] ($filename)";
							exit ();
						}
						if(fwrite($handle, stripslashes(html_entity_decode($contenu))) === FALSE){
							echo "$multilangue[erreur_ecriture_fichier] ($filename)";
							exit ();
						}
						fclose ($handle);
						
						$file = $rootpath . "admin/" . $filename;
						header ( 'Content-Description: File Transfer' );
						header ( 'Content-Type: application/force-download' );
						header ( 'Content-Length: ' . filesize ( $file ) );
						header ( 'Content-Disposition: attachment; filename=' . basename ( $file ) );
						readfile ( $file );
						exit ();
					}else{
						$erreur = "$multilangue[erreur_accessibilite_ecriture_fichier] ($filename).<br>";
					}				
					// export donc on reste sur le default qui est la premiere etape
					$actionSpe = "default";
				}else{ // enregistrement				
					if (!isset($_SESSION['mailingid']) || $_SESSION['mailingid'] == "" || $_SESSION ['mailingid'] == 0) {
						// ajout du mailing
						$DB_site->query ("INSERT INTO mailing (date_creation) VALUES ('".time()."')" );
						$mailingid = $DB_site->insert_id ();
						
						$_SESSION ['mailingid'] = $mailingid;					
					}else{					
						// recupération de l'auto_increment pour eviter de monter trop vite dans les id
						$auto_increment = $DB_site->query_first ("SELECT mailing_listeid FROM mailing_liste WHERE mailingid='".$_SESSION['mailingid']."' ORDER BY mailing_listeid LIMIT 1");
						$DB_site->query ( "DELETE FROM mailing_liste WHERE mailingid='".$_SESSION['mailingid']."'" );
						
						if($auto_increment[mailing_listeid] != ""){
							// set auto increment
							$DB_site->query ( "ALTER TABLE mailing_liste AUTO_INCREMENT = " . $auto_increment [mailing_listeid] );
						}
										
						$modif_ad = $DB_site->query("UPDATE mailing SET date_creation = '".time()."' WHERE mailingid='".$_SESSION['mailingid']."'");
					}
					// enregistrement des mail selectionné dans la table mailing
					foreach ( $table_email as $key => $value ) {
						$DB_site->query("INSERT INTO mailing_liste (siteid,mailingid,mailing_liste_email) VALUES ('".addslashes($value['siteid'])."','".$_SESSION ['mailingid']."','".addslashes($value['mail'])."')");
					}				
					// passage etape 2
					$actionSpe = "etape2";
				}
			}else{
				// erreur donc on reste sur le default qui est la premiere etape
				$actionSpe = "default";
			}
		}
		
		if ($actionSpe == 'validEtape2') {
			// traitement des données et enregistrement des crénaux et mailing recurent ou autre fonctionnalité
			$erreur = "";
			
			// nb mail
			$sql_nb_mail_secu = "SELECT mailing_listeid FROM mailing_liste WHERE mailingid='".$_SESSION['mailingid']."'";
			$rq_nb_mail_secu = $DB_site->query($sql_nb_mail_secu);
			$nb_mail_secu = $DB_site->num_rows($rq_nb_mail_secu);
			
			// Validation final du mailing
			if ($newsletterid == 0) {
				$erreur .= "$multilangue[mail_type_obligatoire]<br>";
			}
		
			if ($erreur == "" && $annul != 1) {
				// enregistrement			
				
				// Format : dd/mm/YYYY HH:ii
				$date_debut_split = explode (" ",$date_debut);
				$date_debut_date = $date_debut_split[0];
				$date_debut_heure = $date_debut_split[1];
				
				$split_date = explode ("/",$date_debut_date);
				$jour = $split_date[0];
				$mois = $split_date[1];
				$annee = $split_date[2];
				$split_heure = explode (":",$date_debut_heure);
				$heure = $split_heure[0];
				$minute = $split_heure[1];
				
				$date_debut_TM = mktime($heure, $minute, 0, $mois, $jour, $annee);					
				
				$nb_minutes_envoi = $nb_mail_secu / $newsletter_per_min;			
				$date_fin_estimee_TM = $date_debut_TM + ($nb_minutes_envoi * 60);		
			
				
				$DB_site->query("UPDATE mailing SET newsletterid='$newsletterid', date_creation='".time()."', date_debut='$date_debut_TM', 
								date_fin_estimee='$date_fin_estimee_TM'
								WHERE mailingid = '".$_SESSION['mailingid']."'");
				
				$sites_gen_news=$DB_site->query("SELECT * FROM site ORDER BY siteid");
				while($site_gen_news=$DB_site->fetch_array($sites_gen_news)){
					$newsletteracopier = $DB_site->query_first ("SELECT * FROM newsletter_site
																	WHERE newsletterid='$newsletterid'
																	AND siteid='$site_gen_news[siteid]'");	
	
					$DB_site->query("INSERT INTO mailing_site(mailingid, siteid, libelle, contenu) 
										VALUES('".$_SESSION['mailingid']."', '$site_gen_news[siteid]',
										 '".addslashes($newsletteracopier[libelle])."', '".addslashes($newsletteracopier[contenu])."')");					
				}
							
				// On copie les mails de mailing_liste dans mailing_envoi avec envoye à 0 (changement du système d'envoi pour simplifier).
				$mails_to_send =$DB_site->query("SELECT * FROM mailing_liste WHERE mailingid = '".$_SESSION['mailingid']."'");
				while($mail_to_send=$DB_site->fetch_array($mails_to_send)){
					$DB_site->query("INSERT INTO mailing_envoi(mailingid, siteid, email, envoye)
										VALUES('".$_SESSION['mailingid']."', '$mail_to_send[siteid]',
										'$mail_to_send[mailing_liste_email]', '0')");
				}			
				
				// retour page d'accueil des newsletter
				// On vide le session mailing
				$_SESSION['mailingid'] = "";
				$actionSpe = "";
				$action = "";
				echo "<script language=\"javascript\">location.href=\"newsletter.php\";</script>";
				exit ();
			}else{
				if (!empty($annul) && $annul == 1) {
					// retour arrière
					$erreur = "";
					$actionSpe = "etape1";
				}else{
					// sinon retour etape 3
					$actionSpe = "etape2";
				}
			}
		}
			
		switch ($actionSpe){
			// etape 2
			case "etape2" :
					
				if (!empty($erreur)) {
					$contenu_onglet .= "<tr><td align=\"center\"><br><b class=\"erreur\">$erreur</b><br></td></tr>";
				}
				
				$sql_etape2_m = "SELECT * FROM mailing WHERE mailingid='".$_SESSION['mailingid']."'";
				$sql_etape2_ml = "SELECT mailing_listeid FROM mailing_liste WHERE mailingid='" . $_SESSION ['mailingid'] . "'";
				$rq_etape2_m = $DB_site->query_first($sql_etape2_m);
				$rq_etape2_ml = $DB_site->query($sql_etape2_ml);
				
				// on compte les email
				$nb_mail = $DB_site->num_rows($rq_etape2_ml);
						
				if($rq_etape2_m[date_debut] != 0 && $rq_etape2_m[date_debut] != "") {
					$date_debut = date("d/m/y H:i", $rq_etape2_m[date_debut]);
				}else{
					$date_debut_TM = mktime(date("H")+1, date("i"), date("s"), date("m"), date("d"), date ("Y"));
					$date_debut = date ( "d/m/Y H:i", $date_debut_TM);
				}
				
				if($rq_etape2_m [date_fin] != "0000-00-00" && $rq_etape2_m [date_fin] != "") {
					$date_fin_estimee = date("d/m/y H:i", $rq_etape2_m[date_fin_estimee]);
				}else{
					$nb_minutes_envoi = ceil($nb_mail / $newsletter_per_min);			
					$date_fin_estimee_TM = $date_debut_TM + ($nb_minutes_envoi * 60);
					$date_fin_estimee = date("d/m/y H:i", $date_fin_estimee_TM);				
				}
				
				$max_mails_jour = $newsletter_per_min * 60 * 24;
							
				$rq_newsletter = $DB_site->query("SELECT * FROM newsletter ORDER BY newsletterid DESC");
				while ($arr_newsletter = $DB_site->fetch_array($rq_newsletter)) {			
					if ($newsletterid == $arr_newsletter[newsletterid]) {
						$selectedNewsLetter = " selected";
					} else {
						$selectedNewsLetter = "";
					}		
					eval(charge_template($langue, $referencepage, "EnvoyerEtape2NewsletterBit"));
				}			
				
				eval(charge_template($langue, $referencepage, "EnvoyerEtape2"));
					
				$libNavigSupp = "$multilangue[envoyer_newsletter] > $multilangue[etape] 2 : $multilangue[planification]";
				eval(charge_template($langue, $referencepage, "NavigSupp"));			
				
			break;
	
			// etape 1
			default :		
				if (!empty($erreur)){
					$contenu_onglet .= "<tr><td colspan=\"2\" align=\"center\"><br><b class=\"erreur\">$erreur</b><br></td></tr>";
					
					// traitement des valeur poster pour le reaffichage du formulaire
					if ($homme == 1)
						$selectH = " checked=\"checked\"";
					if ($femme == 1)
						$selectF = " checked=\"checked\"";
					
					if ($particulier == 1)
						$selectPa = " checked=\"checked\"";
					if ($pro == 1)
						$selectPr = " checked=\"checked\"";
					
					
					$checked_dest_0 = $checked_dest_1 = $checked_dest_2 = $checked_dest_3 = "";
					switch($destinataires){
						case 0 : // Tous les inscrits à la newsletter
							$checked_dest_0 = " checked=\"checked\"";
							break;
						case 1 : // Seulement les comptes clients souhaitant recevoir la newsletter
							$checked_dest_1 = " checked=\"checked\"";
							break;
						case 2 : // Seulement les inscrits à la newsletter
							$checked_dest_2 = " checked=\"checked\"";
							break;
						case 3 : // Tous les comptes clients, même ceux qui ne souhaitent pas recevoir de newsletter. 
							$checked_dest_3 = " checked=\"checked\"";
							break;
					}
				
					if (in_array ( 5817, $modules )) {
						if ($news_partenaireUnique == 1)
							$selectNuPart = " checked=\"checked\"";
					}
					
					if ($selectImport == 1)
						$selectI = " checked=\"checked\"";
					if ($incluretablenewsletter == 1)
						$selectTN = " checked=\"checked\"";
				}
				
				// selection par default quand on arrive sur la page
				if(empty($actionSpe) || $annul == 1){
					$selectH = " checked=\"checked\"";
					$selectF = " checked=\"checked\"";
					$selectPa = " checked=\"checked\"";
					$selectPr = " checked=\"checked\"";			
				}
				
				if (in_array ( 122, $modules )) {
					eval(charge_template($langue, $referencepage, "EnvoyerEtape1Module122"));
				}
										
				if ($_POST['paysid'][0] == 0 || empty($_POST['paysid'])) {
					$selectTousPays = " selected";
				}else{
					$selectTousPays = "";
				}					
				$payss = $DB_site->query("SELECT * FROM pays ORDER BY libelle");
				while($pays = $DB_site->fetch_array($payss)){
					if (!empty($_POST['paysid'])){
						foreach($_POST['paysid'] as $key => $value){
							if ($value == $pays [paysid]) {
								$selectPays = "selected=\"selected\"";
								break;
							}else{
								$selectPays = "";
							}
						}
					}
					eval(charge_template($langue, $referencepage, "EnvoyerEtape1PaysBit"));
				}					
				/*if (in_array ( 5817, $modules )) {
					$contenu_onglet .= "<input type=\"checkbox\" value=\"1\" name=\"news_partenaireUnique\" $selectNuPart/>&nbsp;$multilangue[inscrit_newsletter_partenaire_uniquement]";
				}*/
							
				$req_import = $DB_site->query ( "SELECT * FROM import_newsletter ORDER BY import_newsletter_date" );			
				while ( $arr_import = $DB_site->fetch_array ( $req_import ) ) {				
					if ($import_newsletterid == $arr_import [import_newsletterid]) {
						$selectedImport = " selected=\"selected\"";
					} else {
						$selectedImport = "";
					}				
					$dateImport = date("d/m/Y $multilangue[a] H:i:s",$arr_import[import_newsletter_date]);
					eval(charge_template($langue, $referencepage, "EnvoyerEtape1ImportBit"));
				}
			
				
				if ($_POST ['catid'] [0] == 0 || empty ( $_POST ['catid'] )) {
					$selectTousCatid = " selected=\"selected\"";
				} else {
					$selectTousCatid = "";
				}
				
				$options_categ = arborescenceMultiple ( $DB_site, 0 );		
							
				$sites = $DB_site->query("SELECT * FROM site ORDER BY siteid");
				while ($site = $DB_site->fetch_array($sites)){
					eval(charge_template($langue, $referencepage, "EnvoyerEtape1SiteBit"));
				}
				
				
	
				eval(charge_template($langue, $referencepage, "EnvoyerEtape1"));
				
				$libNavigSupp = "$multilangue[envoyer_newsletter] > $multilangue[etape] 1 : $multilangue[selection_destinataires]";
				eval(charge_template($langue, $referencepage, "NavigSupp"));
				
				break;
		}
	}else{
		$texteErreur = "$multilangue[email_incorrect]" ;
		eval(charge_template($langue, $referencepage, "Erreur"));
		$action="";
	}
}

if (!isset($action) or $action == ""){
	$_SESSION['mailingid'] = "";
	
	$DB_site->query("DELETE FROM mailing WHERE date_debut='0'");
	
	// Liste d'imports
	$importnews=$DB_site->query("SELECT * FROM import_newsletter ORDER BY import_newsletter_date DESC");
	while($importnew=$DB_site->fetch_array($importnews)){
		eval(charge_template($langue, $referencepage, "ListeImportBit"));
	}
	
	// Liste ddes sites
	$sites_import_news=$DB_site->query("SELECT * FROM site ORDER BY siteid");
	while($site_import_news=$DB_site->fetch_array($sites_import_news)){
		eval(charge_template($langue, $referencepage, "ListeSitesBit"));
	}	
		
	eval(charge_template($langue, $referencepage, "ListeMailings"));		
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