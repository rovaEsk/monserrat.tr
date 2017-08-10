<?
$prov = "comp" ;
require "includes/admin_global.php";
if (!$rootpath)
	$rootpath = $_SERVER['DOCUMENT_ROOT'] ;

$contenu="categorie;identifiant_unique;titre;prix;url_produit;url_image;description;frais_de_livraison;D3E;disponibilite;marque;ean;delai_livraison;garantie;prix_barre;reference_modele;occasion;devise;promotion";
if(in_array(5932, $modules)){$contenu.=";mobile_url";}
$contenu.="\n";

if ($tab[exporter_non_visibles] == 0){
	$where .= "activeV1 = '1' ";
}
if ($tab[exporter_non_commandables] == 0){
	$where .= "AND commandable = '1' ";
}
if ($tab[exporter_cats_non_visibles] == 0){
	$aff_inactive = 0;
}else{
	$aff_inactive = 1;	
}

$articles = $DB_site->query("SELECT * FROM article INNER JOIN article_site USING(artid) WHERE prix > 0 $where");
while ($article = $DB_site->fetch_array($articles)) {
	$export = 1;
	if ($aff_inactive == 0){
		$categs = $DB_site->query("SELECT catid FROM position WHERE artid = '$article[artid]'");
		while($categ=$DB_site->fetch_array($categs)){
			$catid = $categ[catid];
			$export = 1;
			while ($catid != 0 && $export == 1){
				$categ2 = $DB_site->query_first("SELECT * FROM categorie INNER JOIN categorie_site USING(catid) WHERE catid = '$catid' AND siteid = '$site[siteid]'");
				if ($categ2[visible_treeviewV1] == 0){
					$export = 0;	
				}
				$catid = $categ2[parentid];
			}
		}
	}
	$nbstock = retournerStockArticle($DB_site, $article[artid]);
	if ($tab[exporter_stock_epuise] == 0 && in_array(4, $modules) && $nbstock <= 0  && $article[stock_illimite] != 1){
		$export = 0;
	}
	if ($export){
		$article[description] = html_entity_decode(strip_tags($article[description]));
		$article[description] = str_replace("\n"," ",$article[description]);
		$article[description] = str_replace("\r"," ",$article[description]);
		$article[description] = str_replace("\t"," ",$article[description]);
		$article[description] = str_replace("&#39","'",$article[description]);
		$article[description] = str_replace("&rsquo","'",$article[description]);
		$article[description] = str_replace("&oelig","oeil",$article[description]);
		$article[libelle] = html_entity_decode(strip_tags($article[libelle]));
		$article[libelle] = str_replace("\n"," ",$article[libelle]);
		$article[libelle] = str_replace("\r"," ",$article[libelle]);
		$article[libelle] = str_replace("\t"," ",$article[libelle]);
		// categorie
		$cheminsanshref = "";
		$separateur_navigation = " > ";
		cheminsanshref($article[catid],$DB_site);
		$contenu .= $cheminsanshref.";" ; 
		// identifiant_unique
		$contenu .= $article[artid].";" ;
		// titre
		$contenu .= $article[libelle].";" ;
		// prix
		$prixbarre = 0;
		if (estEnPromo($DB_site, $article[artid],0)) {	
			$prix = formaterPrix($article[prix] * (1 - ($article[pctpromo] / 100)), 2, '.', '') ;
			$prixbarre = $article[prix];
			$solde = 1 ;
		}elseif ($tab[exporter_prixpublic] == 1){
			$prixbarre = $article[prixpublic];
			$prix = $article[prix];
		}else{
			$prix = $article[prix] ;	
			$solde = 0;
		}
		$contenu .= formaterPrix($prix, 2, ",", "").";";
		// url_produit
		$contenu.="http://$host/".$regleurlrewrite[$site[siteid]][article]."-".url_rewrite($article[libelle])."-".$article[artid].".htm".";";
		// url_image
		if ($article[image] != "")
			$contenu.="http://$host/ar-".url_rewrite($article[libelle])."-".$article[artid].".".$article[image].";";	
		else
			$contenu.=";";
		// description
		$desc = substr($article[description], 0, 250) ;
		if ($desc == "") 
			$desc="Pas de description pour cet article";
		$contenu .= "$desc".";";
		
		// frais_de_livraison
		$top = array("artid" => $article[artid], "prix" => $prix, "poids" => $article[poids]);
		$topFp = trouve_articleFraisPort($DB_site, $top, "", "", 0) ;
		$article[prixport] = number_format($topFp[fraisport], 2, '.', '') ;
		
		$contenu.="$article[prixport];";
		// D3E
		$contenu.=";";
		// disponibilite
		if (in_array("4",$modules)) {
			if ($nbstock <= 0)
				$contenu.="0;";
			elseif ($nbstock > 0)
				$contenu.="0;";
			else
				$contenu.="0;";
		}
		else	
			$contenu.="0;";
		// marque
		$article[marque] = "";
		$tab[separateur_marques] = ", ";
		$article_marques = $DB_site->query("SELECT * FROM marque m, article_marque am WHERE m.marqueid = am.marqueid AND am.artid = '$article[artid]' LIMIT 1") ;
		while ($article_marque=$DB_site->fetch_array($article_marques)) {
			$article[marque] .= $article_marque[libelle] . $tab[separateur_marques] ; // ici : rajouter les liens et autres de marque ...
			}
		$article[marque] = substr($article[marque], 0, -strlen($tab[separateur_marques])) ;
		$contenu.="$article[marque];";
		// ean
		$contenu.="$article[code_EAN];";
		// D�lai de livraison
		$contenu.="$article[delai] semaines;";
		// garantie
		$contenu.="$article[commentaire];";
		// prix_barre
		if ($prixbarre != 0){	// Article en promo -> on affiche le prix promo
			$prixbarre = formaterPrix($prixbarre, 2, ",", "");
			$contenu.="$prixbarre;";
		} 
		else
			$contenu.=";";
		// reference_modele
		$contenu .= $article[artcode].";" ;
		// occasion
		$contenu.="0;";
		// devise	
		$contenu.="EUR;";
		if ($solde == 1)
			$contenu.="1;";
		else
			$contenu.=";";
		
		if(in_array(5932, $modules)){
			// url_produit mobile
			$contenu.="http://$mobilehost/".$regleurlrewrite[$site[siteid]][article]."-".url_rewrite($article[libelle])."-".$article[artid].".htm".";";	
		}
		$contenu .= "\n" ;
	}
}

if (!is_dir($rootpath."configurations/$host/exports")) {
	mkdir($rootpath."configurations/$host/exports",0777);
}
$filename = $rootpath."configurations/$host/exports/export_leguide_soldes".$site[siteid].".csv";
if (!$handle = fopen($filename, 'w')) {
	echo "$multilangue[erreur_ouverture_fichier] ($filename)";
	exit;
} else {
	if (fwrite($handle, stripslashes(html_entity_decode($contenu))) === FALSE) {
		echo "$multilangue[erreur_ecriture_fichier] ($filename)";
		fclose($handle);
		exit;
	} else {
		fclose($handle);
	}
}

?>