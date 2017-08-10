<?php
include "includes/header.php";

$referencepage="recherche_globale";
$pagetitle = "Résultats recherche globale - $host - Admin Arobases";

//$mode = "test_modules";

if(!parse_template($referencepage, $langue)){
	echo "erreur de chargement de template";
}

ini_set("memory_limit",'512M');

if ($action == "recherche_globale") {
	$searched = $_POST["global_search"];
	/************************************* REQUETES ******************************************************/
	$req_arts = $DB_site->query("SELECT artid, libelle, legende, titre2, artcode, reference_fabricant, typearticle, siteid FROM article INNER JOIN article_site USING(artid) 
												WHERE	 artcode LIKE'%$searched%' || reference_fabricant LIKE'%$searched%' 
												|| typearticle LIKE '%$searched%' || libelle LIKE '%$searched%' || 
												legende LIKE '%$searched%' || titre2='%$searched%'");
												
	$req_cats = $DB_site->query("SELECT catid, siteid, libelle, description
												FROM categorie_site
												WHERE libelle LIKE '%$searched%'");
											
	$req_instits= $DB_site->query("SELECT institutionnelid, siteid, libelle, contenu
												FROM institutionnel_site
												WHERE libelle LIKE '%$searched%' || contenu LIKE '%$searched%'");
												
	$req_marques = $DB_site->query("SELECT marqueid, libelle, description, siteid FROM marque INNER JOIN marque_site USING(marqueid)
												WHERE libelle LIKE '%$searched%'");
	
	$req_fournisseurs = $DB_site->query("SELECT f.fournisseurid, f.libelle, f.adresse, f.codepostal, f.ville, f.mail, fc.nom, fc.prenom
											FROM fournisseur as f INNER JOIN fournisseurcontact as fc USING(fournisseurid) 
												WHERE f.libelle LIKE '%$searched%' || 
												f.adresse LIKE '%$searched%' || f.codepostal LIKE '%$searched%' || f.ville LIKE '%$searched%' || 
												f.mail LIKE '%$searched%'|| fc.nom LIKE '%$searched%' || fc.prenom LIKE '%$searched%'");
	
	$req_caracteristiques = $DB_site->query("SELECT caractid, libelle, siteid 
												FROM caracteristique_site 
												WHERE libelle LIKE '%$searched%'");
	
	$req_clients = $DB_site->query("SELECT userid, mail, nom, prenom, adresse, codepostal, ville, raisonsociale, password 
												FROM utilisateur 
												WHERE mail LIKE '%$searched%' || nom LIKE '%$searched%' || prenom LIKE '%$searched%' || 
												adresse LIKE '%$searched%' || codepostal LIKE '%$searched%' || ville LIKE '%$searched%' ||
												raisonsociale LIKE '%$searched%'");
	
	$req_adminUsers = $DB_site->query("SELECT userid, username FROM admin_utilisateur WHERE username LIKE '%$searched%'");
	
	$req_codesReduction = $DB_site->query("SELECT cadeauid, c.valeurcadeau, c.code, c.userid, nom, prenom
												FROM cadeau as c LEFT JOIN utilisateur USING(userid)
												WHERE valeurcadeau LIKE '%$searched%' || code LIKE '%$searched%' || nom LIKE '%$searched%' 
												|| prenom LIKE '%$searched%'");
	
	
	
	$nb_answer_articles = $DB_site->num_rows($req_arts);

	$nb_answer_categories = $DB_site->num_rows($req_cats);
	
	$nb_answer_instits = $DB_site->num_rows($req_instits);
	
	$nb_answer_marques = $DB_site->num_rows($req_marques);
	
	$nb_answer_fournisseurs = $DB_site->num_rows($req_fournisseurs);
	
	$nb_answer_caracteristiques = $DB_site->num_rows($req_caracteristiques);
	
	$nb_answer_clients = $DB_site->num_rows($req_clients);
	
	$nb_answer_adminUsers = $DB_site->num_rows($req_adminUsers);
	
	$nb_answer_codesReduction = $DB_site->num_rows($req_codesReduction);

	/********************************************** recherche ******************************************************/
	if($DB_site->num_rows($req_arts) || $DB_site->num_rows($req_cats) || $DB_site->num_rows($req_instits) 
	|| $DB_site->num_rows($req_marques) || $DB_site->num_rows($req_fournisseurs) || $DB_site->num_rows($req_caracteristiques) || 
	$DB_site->num_rows($req_clients) || $DB_site->num_rows($req_adminUsers) || $DB_site->num_rows($req_codesReduction)){
		
		$TemplateRecherche_globaleArticleBit="";
		$TemplateRecherche_globaleCategorieBit="";
		if($DB_site->num_rows($req_arts)){
			while($req_art = $DB_site->fetch_array($req_arts)){
				$artcode = highlight($searched, $req_art[artcode]);
				$reference_fabricant = highlight($searched, $req_art[reference_fabricant]);
				$typearticle = highlight($searched, $req_art[typearticle]);
				$libelle = highlight($searched, $req_art[libelle]);
				$legende = highlight($searched, $req_art[legende]);
				$titre2 = highlight($searched, $req_art[titre2]);
				$siteid = $DB_site->query_first("SELECT libelle FROM site WHERE siteid = '$req_art[siteid]'");
				eval(charge_template($langue,$referencepage,"ArticleBit"));
			}
		eval(charge_template($langue,$referencepage,"Article"));
		}
		
		if($DB_site->num_rows($req_cats)){
			while($req_cat = $DB_site->fetch_array($req_cats)){
				$libelle = highlight($searched, $req_cat[libelle]);
				$siteid = $DB_site->query_first("SELECT libelle FROM site WHERE siteid = '$req_cat[siteid]'");
				eval(charge_template($langue,$referencepage,"CategorieBit"));
			}
			eval(charge_template($langue,$referencepage,"Categorie"));
		}
		if($DB_site->num_rows($req_instits)){
			$urlPage="http://$host/V2/".$regleurlrewrite[$langue][institutionnel]."-".url_rewrite($infosPage[libelle])."-$infosPage[institutionnelid].htm";
			while($req_instit = $DB_site->fetch_array($req_instits)){
				$libelle = highlight($searched, $req_instit[libelle]);
				$contenu = highlight($searched, $req_instit[contenu]);
				$siteid = $DB_site->query_first("SELECT libelle FROM site WHERE siteid = '$req_instit[siteid]'");
				eval(charge_template($langue,$referencepage,"InstitBit"));
			}
			eval(charge_template($langue,$referencepage,"Instit"));
		}
		if($DB_site->num_rows($req_marques)){
			while($req_marque = $DB_site->fetch_array($req_marques)){
				$libelle = highlight($searched, $req_marque[libelle]);
				$siteid = $DB_site->query_first("SELECT libelle FROM site WHERE siteid = '$req_marque[siteid]'");
				eval(charge_template($langue,$referencepage,"MarqueBit"));
			}
			eval(charge_template($langue,$referencepage,"Marque"));
		}
		if($DB_site->num_rows($req_fournisseurs)){
			while($req_fournisseur = $DB_site->fetch_array($req_fournisseurs)){
				$libelle = highlight($searched, $req_fournisseur[libelle]);
				$nom = highlight($searched, $req_fournisseur[nom]);
				$prenom = highlight($searched, $req_fournisseur[prenom]);
				$mail = highlight($searched, $req_fournisseur[mail]);
				$adresse = highlight($searched, $req_fournisseur[adresse]);
				$codepostal = highlight($searched, $req_fournisseur[codepostal]);
				$ville = highlight($searched, $req_fournisseur[ville]);
				$siteid = $DB_site->query_first("SELECT libelle FROM site WHERE siteid = '$req_fournisseur[siteid]'");
				eval(charge_template($langue,$referencepage,"FournisseurBit"));
			}
			eval(charge_template($langue,$referencepage,"Fournisseur"));
		}
		if($DB_site->num_rows($req_caracteristiques)){
		
			while($req_caracteristique = $DB_site->fetch_array($req_caracteristiques)){
				$libelle = highlight($searched, $req_caracteristique[libelle]);
				$siteid = $DB_site->query_first("SELECT libelle FROM site WHERE siteid = '$req_caracteristique[siteid]'");
				eval(charge_template($langue,$referencepage,"CaracteristiqueBit"));
			}
			eval(charge_template($langue,$referencepage,"Caracteristique"));
		}
		if($DB_site->num_rows($req_clients)){
			while($req_client = $DB_site->fetch_array($req_clients)){
				$lien_connexion = "http://$host/V2/client.htm?action=logging&cryptage=no&mail_logging=$req_client[mail]&pass_logging=$req_client[password]";
				$name = highlight($searched, $req_client[nom]);
				$mail = highlight($searched, $req_client[mail]);
				$prenom = highlight($searched, $req_client[prenom]);
				$adresse = highlight($searched, $req_client[adresse]);
				$codepostal = highlight($searched, $req_client[codepostal]);
				$ville = highlight($searched, $req_client[ville]);
				$raisonsociale = highlight($searched, $req_client[raisonsociale]);
				$siteid = $DB_site->query_first("SELECT libelle FROM site WHERE siteid = '$req_client[siteid]'");
				eval(charge_template($langue,$referencepage,"ClientBit"));
			}
			eval(charge_template($langue,$referencepage,"Client"));
		}
		if($DB_site->num_rows($req_adminUsers)){
			while($req_adminUser = $DB_site->fetch_array($req_adminUsers)){
				$username = highlight($searched, $req_adminUser[username]);
				$siteid = $DB_site->query_first("SELECT libelle FROM site WHERE siteid = '$req_adminUser[siteid]'");
				eval(charge_template($langue,$referencepage,"AdminUtilisateurBit"));
			}
			eval(charge_template($langue,$referencepage,"AdminUtilisateur"));
		}
		if($DB_site->num_rows($req_codesReduction)){
			while($req_codeReduction = $DB_site->fetch_array($req_codesReduction)){
				$valeurcadeau = highlight($searched, $req_codeReduction[valeurcadeau]);
				$code = highlight($searched, $req_codeReduction[code]);
				$nom = highlight($searched, $req_codeReduction[nom]);
				$prenom = highlight($searched, $req_codeReduction[prenom]);
				eval(charge_template($langue,$referencepage,"Code_reductionBit"));
			}
			eval(charge_template($langue,$referencepage,"Code_reduction"));
		}
	}else{
		$texteErreur = "La recherche n'a retourné aucun résultat";
		eval(charge_template($langue,$referencepage,"Erreur"));
	}
	
}



function highlight ($toReplace, $where){
	$newString = "<strong>".$toReplace."</strong>";
	$changedString = str_ireplace($toReplace, $newString, $where);
	return $changedString;
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
