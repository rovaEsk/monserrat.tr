<?php
include "./includes/header.php";

$referencepage="articles_offerts";
$pagetitle = "Gestion des articles offerts - $host - Admin Arobases";

if(!parse_template($referencepage, $langue)){
	echo "erreur de chargement de template";
}

if(isset($erreurdroits) and $erreurdroits == 1){
	$texteErreur = $multilangue[action_page_refuse];
	eval(charge_template($langue, $referencepage, "Erreur"));
}

if(isset($action) && $action=="supprimer"){
	if($admin_droit[$scriptcourant][suppression]){
		$DB_site->query("DELETE FROM articleoffert WHERE articleoffertid = '$idOffre'");
		$DB_site->query("DELETE FROM articleoffert_categorie WHERE articleoffertid = '$idOffre'");
		$DB_site->query("DELETE FROM articleoffert_article WHERE articleoffertid = '$idOffre'");
		$DB_site->query("DELETE FROM articleoffert_cadeau WHERE articleoffertid = '$idOffre'");
		header("location: articles_offerts.php");
	}else{
		header('location: articles_offerts.php?erreurdroits=1');	
	}
}

if(isset($action) and $action == "ajout2"){
	if($admin_droit[$scriptcourant][ecriture]){
		if(isset($articleTab)){
			foreach ($articleTab as $key => $value)
				$artid = $value;
		}
		$date_debut = "0";
		$date_fin = "0";
		if ($_POST[datedebut] != "") {
			list($jour, $mois, $annee) = explode('/', $_POST[datedebut]);
			$date_debut = mktime(0, 0, 0, $mois, $jour, $annee);
		}
		if ($_POST[datefin] != "") {
			list($jour, $mois, $annee) = explode('/', $_POST[datefin]);
			$date_fin = mktime(0, 0, 0, $mois, $jour, $annee);
		}
		
		// Mise à jour d'une offre
		if(isset($offreid) && $offreid != ""){
			$DB_site->query("UPDATE articleoffert SET siteid='$siteSelect', libelle='".securiserSql($libelleOffre,"html")."', description='".securiserSql($descriptionOffre,"html")."', montantminimum='$montantminimum', datedebut='$date_debut', datefin='$date_fin' WHERE articleoffertid='$offreid'");
			
			$DB_site->query("DELETE FROM articleoffert_categorie WHERE articleoffertid='$offreid'");
			$DB_site->query("DELETE FROM articleoffert_article WHERE articleoffertid='$offreid'");
			$DB_site->query("DELETE FROM articleoffert_cadeau WHERE articleoffertid='$offreid'");
			
			if(sizeof($_POST[articleTabOffert]) > 0){
				foreach($_POST[articleTabOffert] as $value)
					$DB_site->query("INSERT INTO articleoffert_cadeau (articleoffertid, artid) VALUES ('$offreid','$value')");
			}

			switch($application){
				case 1:
					$catidOffres = explode(",", $_POST[catidOffre]);
					foreach($catidOffres as $key => $value) {
						if ($value != "0")
							$DB_site->query("INSERT INTO articleoffert_categorie (articleoffertid, catid) VALUES ('$offreid', '" . securiserSql($value) . "')");
					}
					break;
				case 2:
					$DB_site->query("INSERT INTO articleoffert_article (articleoffertid, artid) VALUES ('$offreid', '" . securiserSql($artid) . "')");
					break;
				case 3:
					$articles = explode(",", $_POST[articles]);
					foreach($articles as $value) {
						$value = explode("t", $value);
						if($value[0] == "ar"){
							$DB_site->query("INSERT INTO articleoffert_article (articleoffertid, artid) VALUES ('$offreid', '$value[1]')");
						}
					}
					break;
			}
		// Ajout d'une nouvelle offre
		} else {
			$DB_site->query("INSERT INTO articleoffert (siteid, libelle, description, montantminimum, datedebut, datefin, active)
								VALUES ('$siteSelect','".securiserSql($libelleOffre,"html")."','".securiserSql($descriptionOffre,"html")."','$montantminimum','$date_debut','$date_fin','1')");
			$articleoffertid = $DB_site->insert_id();
			if(sizeof($_POST[articleTabOffert]) > 0){
				foreach($_POST[articleTabOffert] as $key => $value)
					$DB_site->query("INSERT INTO articleoffert_cadeau  (articleoffertid, artid) VALUES ('$articleoffertid','$value')");
			}
			switch($application){
				case 1:
					$catidOffres = explode(",", $_POST[catidOffre]);
					foreach($catidOffres as $key => $value) {
						if ($value != "0")
						$DB_site->query("INSERT INTO articleoffert_categorie (articleoffertid, catid) VALUES ('$articleoffertid', '" . securiserSql($value) . "')");
					}
					break;
				case 2:
						$DB_site->query("INSERT INTO articleoffert_article (articleoffertid, artid) VALUES ('$articleoffertid', '" . securiserSql($artid) . "')");
					break;
				case 3:
					$articles = explode(",", $_POST[articles]);
					foreach($articles as $value) {
						$value = explode("t", $value);
						if($value[0] == "ar"){
								$DB_site->query("INSERT INTO articleoffert_article (articleoffertid, artid) VALUES ('$articleoffertid', '$value[1]')");
						}
					}
					break;
			}
		}
		header("location: articles_offerts.php");
	}else{
		header('location: articles_offerts.php?erreurdroits=1');	
	}
}

if(isset($action) and $action == "ajout"){
	if($admin_droit[$scriptcourant][ecriture]){
		$libelle_offre = "";
		$desc_offre = "";
		$montant_offre = "";
		$datedebut_offre = "";
		$datefin_offre = "";
		$selectedappli0 = "selected=\"selected\"";
		$selectedappli1 = "";
		$selectedappli2 = "";
		$selectedappli3 = "";
		
		// Modification d'une offre
		if(isset($idOffre)){
			$offre = $DB_site->query_first("SELECT * FROM articleoffert WHERE articleoffertid='$idOffre'");
			$libNavigSupp = "$multilangue[modifier] : <b>$offre[libelle]</b>";
			$sites = $DB_site->query("SELECT * FROM site ORDER BY siteid");
			// Selection du site
			while($site = $DB_site->fetch_array($sites)){
				$devise_site_actuel = $tabsites[$site[siteid]][devise_complete];
				if($site[siteid] == "$offre[siteid]"){
					$selected = "selected=\"selected\"";
					$display = "";
				} else {
					$selected = "";
					$display = "style=\"display:none;\"";
				}
				eval(charge_template($langue,$referencepage,"ListeSiteBit"));
				eval(charge_template($langue,$referencepage,"DeviseSymboleBit"));
					
			}
			
			$tabArticle = "";
			$articles_offre = $DB_site->query("SELECT * FROM articleoffert_article WHERE articleoffertid = '$offre[articleoffertid]'");
			// Offre sur plusieurs articles
			if($DB_site->num_rows($articles_offre) > "1"){
				$selectedappli3 = "selected=\"selected\"";
				while($article_offre = $DB_site->fetch_array($articles_offre)){
					$tabArticle .= "art$article_offre[artid],";
				}
			// Offre sur un article
			} elseif ($DB_site->num_rows($articles_offre) == "1"){
				$selectedappli2 = "selected=\"selected\"";
				$article_offre = $DB_site->fetch_array($articles_offre);
				$article = $DB_site->query_first("SELECT asite.libelle, asite.artid, a.artcode FROM article_site AS asite 
															INNER JOIN article AS a USING(artid)
															WHERE asite.artid='$article_offre[artid]' AND asite.siteid='$offre[siteid]'"); 
				
				eval(charge_template($langue,$referencepage,"ModificationArticle"));
			}
			
			$tabCategorie = "";
			// Offre sur les catégories
			$categories_offre = $DB_site->query("SELECT * FROM articleoffert_categorie WHERE articleoffertid = '$offre[articleoffertid]'");
			if($DB_site->num_rows($categories_offre) != "0"){
				$selectedappli1 = "selected=\"selected\"";
				while($categorie_offre = $DB_site->fetch_array($categories_offre)){
					$tabCategorie .= "$categorie_offre[catid],";
				}
			}
			
			// Cadeau
			$cadeaux_offre = $DB_site->query("SELECT * FROM articleoffert_cadeau WHERE articleoffertid = '$offre[articleoffertid]'");
			while($cadeau_offre = $DB_site->fetch_array($cadeaux_offre)){
				$articleoffert = $DB_site->query_first("SELECT asite.libelle, asite.artid, a.artcode FROM article_site AS asite 
															INNER JOIN article AS a USING(artid)
															WHERE asite.artid='$cadeau_offre[artid]' AND asite.siteid='$offre[siteid]'");
				eval(charge_template($langue,$referencepage,"ModificationArticleOffert"));
			}
			
			$libelle_offre = $offre[libelle];
			$desc_offre = $offre[description];
			$montant_offre = $offre[montantminimum];
			
			if($offre[datedebut] == "0"){
				$datedebut_offre = "";
			} else {
				$datedebut_offre = $offre[datedebut] = date("d/m/Y", $offre[datedebut]);
			}
			
			if( $offre[datefin] == "0"){
				$datefin_offre = "";
			} else {
				$datefin_offre = $offre[datefin] = date("d/m/Y", $offre[datefin]);
			}
			
		} else {
			$libNavigSupp = "$multilangue[ajt_offre]"; 
			$sites = $DB_site->query("SELECT * FROM site ORDER BY siteid");
			while($site = $DB_site->fetch_array($sites)){
				$devise_site_actuel = $tabsites[$site[siteid]][devise_complete];
				if($site[siteid] == "1"){
					$selected = "selected=\"selected\"";
					$display = "";
				} else {
					$selected = "";
					$display = "style=\"display:none;\"";
				}
				eval(charge_template($langue,$referencepage,"ListeSiteBit"));
				eval(charge_template($langue,$referencepage,"DeviseSymboleBit"));
				
			}
		}
		eval(charge_template($langue, $referencepage, "NavigSupp"));
		eval(charge_template($langue, $referencepage, "Ajout"));
	}else{
		header('location: articles_offerts.php?erreurdroits=1');	
	}
}

if(!isset($action) or $action == ""){
	// Affichage des paramètres
	$decrementer = $DB_site->query_first("SELECT valeur FROM parametre WHERE parametre = 'stock_articles_gratuits'");
	$valable = $DB_site->query_first("SELECT valeur FROM parametre WHERE parametre = 'affiche_all_articles_gratuits'");
	
	$decrementer_check = "";
	if($decrementer[valeur]){
		$decrementer_check = "checked=\"checked\"";
	}
	$valable_check = "";
	if($valable[valeur]){
		$valable_check = "checked=\"checked\"";
	}
	
	// Affichage du tableau des offres
	$offres = $DB_site->query("SELECT * FROM articleoffert");
	while($offre = $DB_site->fetch_array($offres)){
		// Colonne site
		$site = $DB_site->query_first("SELECT siteid, libelle FROM site WHERE siteid = '$offre[siteid]'");
		$devise_site_actuel = $tabsites[$site[siteid]][devise_complete];
		// Colonne période
		if($offre[datedebut] == "0" && $offre[datefin] == "0"){
			$periode = $multilangue[illimite];	
		} elseif($offre[datedebut] == "0" && $offre[datefin] != "0") {
			$offre[datefin] = date("d/m/Y", $offre[datefin]);
			$periode = "$multilangue[jusqu_au] $offre[datefin]";
		} elseif($offre[datefin] == "0" && $offre[datedebut] != "0"){
			$offre[datedebut] = date("d/m/Y", $offre[datedebut]);
			$periode = "$multilangue[a_partir_du] $offre[datedebut]";
		} else {
			$offre[datefin] = date("d/m/Y", $offre[datefin]);
			$offre[datedebut] = date("d/m/Y", $offre[datedebut]);
			$periode = "$multilangue[du] $offre[datedebut] $multilangue[au] $offre[datefin]";
		}
		
		// Colonne rayon(s)/article(s)
		$display_lien_liste = "style=\"display:none\"";
		$rayons_articles = "-";
		$liste_categories_articles = "";
		$offres_articles = $DB_site->query("SELECT * FROM articleoffert_article WHERE articleoffertid = '$offre[articleoffertid]'");
		// Offre sur des articles
		if($DB_site->num_rows($offres_articles) < 10 && $DB_site->num_rows($offres_articles) > 0){
			$rayons_articles = "";
			while ($offre_article = $DB_site->fetch_array($offres_articles)){
				$articleoffert_articles = $DB_site->query_first("SELECT libelle FROM article_site INNER JOIN articleoffert_article USING (artid) WHERE artid = '$offre_article[artid]' AND siteid = '$offre[siteid]' ");
				$rayons_articles .= "$articleoffert_articles[libelle]<br>";
			}
		} elseif($DB_site->num_rows($offres_articles) > 10) {
			$rayons_articles = $DB_site->num_rows($offres_articles)." $multilangue[articles]<br>";
			$liste = $multilangue[liste_articles];
			$display_lien_liste = "";
			while ($offre_article = $DB_site->fetch_array($offres_articles)){
				$articleoffert_articles = $DB_site->query_first("SELECT libelle FROM article_site INNER JOIN articleoffert_article USING (artid) WHERE artid = '$offre_article[artid]' AND siteid = '$offre[siteid]' ");
				$liste_categories_articles .= "<div style=\"width:25%;float:left;height:auto;white-space: normal;text-align:left;\">$articleoffert_articles[libelle]</div>";
			}
		} 
		
		$offres_categs = $DB_site->query("SELECT * FROM articleoffert_categorie WHERE articleoffertid = '$offre[articleoffertid]'");
		// Offre sur des catégories
		if($DB_site->num_rows($offres_categs) < 10 && $DB_site->num_rows($offres_categs) > 0){
			$rayons_articles = "";
			while($offre_categ = $DB_site->fetch_array($offres_categs)){
				$articleoffert_categ = $DB_site->query_first("SELECT libelle FROM categorie_site INNER JOIN articleoffert_categorie USING (catid) WHERE catid = '$offre_categ[catid]' AND siteid = '$offre[siteid]' ");
				$rayons_articles .= "$articleoffert_categ[libelle]<br>";
			}
		} elseif($DB_site->num_rows($offres_categs) > 10) {
			$rayons_articles = $DB_site->num_rows($offres_categs)." $multilangue[categories]<br>";
			$liste = $multilangue[liste_categories];
			$display_lien_liste = "";
			while($offre_categ = $DB_site->fetch_array($offres_categs)){
				$articleoffert_categ = $DB_site->query_first("SELECT libelle FROM categorie_site INNER JOIN articleoffert_categorie USING (catid) WHERE catid = '$offre_categ[catid]' AND siteid = '$offre[siteid]' ");
				$liste_categories_articles .= "<div style=\"width:25%;float:left;height:auto;white-space: normal;text-align:left;\">$articleoffert_categ[libelle]</div>";
			}
		}
		
		// Colonne article cadeau
		$articles_cadeaux = $DB_site->query("SELECT * FROM articleoffert_cadeau WHERE articleoffertid = '$offre[articleoffertid]'");
		$cadeau = "";
		while($article_cadeau = $DB_site->fetch_array($articles_cadeaux)){
			$libelle_cadeau = $DB_site->query_first("SELECT * FROM article_site INNER JOIN articleoffert_cadeau USING (artid) WHERE artid = '$article_cadeau[artid]' AND siteid = '$site[siteid]'");
			if($libelle_cadeau[libelle] == "")
				$libelle_cadeau[libelle] = "Pas de libelle";
			$cadeau .= "$libelle_cadeau[libelle]<br>";
		}
		
		// Colonne active
		if ($offre[active] == 1){
			$color_aff = "vert";
			$color2_aff = "green";
			$ico_aff = "fa-check-square-o";
			$tooltip_visible = $multilangue[desactiver];
		}else{
			$color_aff = "rouge";
			$color2_aff = "red";
			$ico_aff = "fa-square-o";
			$tooltip_visible = $multilangue[activer];
		}
		// Bouton supprimer offre
		$facturesCount = $DB_site->query_first("SELECT COUNT(*) FROM lignefactureoffert WHERE articleoffertid = '$offre[articleoffertid]'");
		$PaniersCount = $DB_site->query_first("SELECT COUNT(*) FROM lignepanieroffert WHERE articleoffertid = '$offre[articleoffertid]'");
		$display_btn_supprimer = "";
		if ($facturesCount[0] > 0 || $PaniersCount[0] > 0)
			$display_btn_supprimer = "style=\"display:none;\"";
		
		eval(charge_template($langue, $referencepage, "ListeBit"));
		
	}
			
	eval(charge_template($langue, $referencepage, "Parametres"));
	eval(charge_template($langue, $referencepage, "Info"));
	eval(charge_template($langue, $referencepage, "Liste"));

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