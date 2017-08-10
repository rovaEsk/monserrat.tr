<?
ini_set('memory_limit','128M');
ini_set('max_execution_time','0');

$prov = "comp" ;
if (!$rootpath)
	$rootpath = $_SERVER['DOCUMENT_ROOT'] ;
$nom_comparateur = "comparateur_shopping";


if(!$onestdansuncron){
	require_once $rootpath."admin/includes/admin_global.php";
} else {
	//require_once $rootpath."admin/includes/fonctions.php";
}

if (!function_exists("cheminsanshref")) {
	function cheminsanshref($categorie, $DB) {
		global $separateur_navigation, $host, $cheminsanshref, $libelle, $title ;
		$ss=$DB->query_first("SELECT COUNT(catid) FROM categorie WHERE parentid = '$categorie'");
		$categorie=$DB->query_first("SELECT * FROM categorie INNER JOIN categorie_site USING(catid) WHERE catid = '$categorie'");
		$categorie[$libelle] = html_entity_decode(strip_tags($categorie[$libelle]));
		$categorie[$libelle] = str_replace("\n"," ",$categorie[$libelle]);
		$categorie[$libelle] = str_replace("\r"," ",$categorie[$libelle]);
		$categorie[$libelle] = trim(str_replace("\t"," ",$categorie[$libelle]));
		$cheminsanshref = "$separateur_navigation $categorie[$libelle] $cheminsanshref" ;
		if ($categorie[parentid] != 0)
			cheminsanshref($categorie[parentid], $DB) ;
		else
			$cheminsanshref = $title." ".$cheminsanshref;
		//return $cheminsanshref ;
	}
}

if (!function_exists("exporter_shopping")) {
	function exporter_shopping($siteid){	
		global $DB_site, $host, $provenance, $regleurlrewrite, $cheminsanshref, $tab, $datedujour, $modules, $title, $libelle, ${$title};
		$infos_site = $DB_site->query_first("SELECT * FROM site WHERE siteid = '$siteid'");
		$regleurlrewriteTemp = $regleurlrewrite[$siteid];
		$where = "";
			if ($tab[exporter_non_visibles] == 0){
				$where .= "AND activeV1 = '1' ";
			}
			if ($tab[exporter_non_commandables] == 0){
				$where .= "AND commandable = '1' ";
			}
			if ($tab[exporter_cats_non_visibles] == 0){
				$aff_inactive = 0;
			}else{
				$aff_inactive = 1;	
			}
			
		$liste_artid = "SELECT artid FROM article_comparateur WHERE comparateurid = '12' AND siteid = '$siteid' ORDER BY artid";	
		$contenu = "Designation|price|regular_price|product_url|description|image_url|category|merchant_id|in_stock|shipping_cost|délai de livraison\n";
		if (isset($provenance) and $provenance == "selection"){
			$articles = $DB_site->query("SELECT * FROM article INNER JOIN article_site USING(artid) WHERE prix > 0 AND siteid='$siteid' AND artid IN ($liste_artid) $where");	
		}else{
			$articles = $DB_site->query("SELECT * FROM article INNER JOIN article_site USING(artid) WHERE siteid='$siteid' AND prix > 0 $where ORDER BY artid");
		}
			
		while ($article = $DB_site->fetch_array($articles)) {
			$export = 1;
				if ($aff_inactive == 0){
					$categs = $DB_site->query("SELECT catid FROM position WHERE artid = '$article[artid]'");
					while($categ = $DB_site->fetch_array($categs)){
						$catid = $categ[catid];
						$export = 1;
						while ($catid != 0 && $export == 1){
							$categ2 = $DB_site->query_first("SELECT * FROM categorie INNER JOIN categorie_site USING(catid) WHERE catid = '$catid' AND siteid = '$siteid'");
							if ($categ2[visible_treeviewV1] == 0)
								$export = 0;
							$catid = $categ2[parentid];
						}
					}
				}
				$nbstock = retournerStockArticle($DB_site, $article[artid]);
				if ($tab[exporter_stock_epuise] == 0 && in_array(4, $modules) && $nbstock <= 0  && $article[stock_illimite] != 1){
					$export = 0;
				}
			
			if ($export){
				$categorie = $DB_site->query_first("SELECT * FROM categorie INNER JOIN categorie_site USING(catid) WHERE catid = '$article[catid]' AND siteid = '$siteid'");
				$categorie[libelle] = secure_chaine_csv($categorie[libelle], 1);
				$article[description] = secure_chaine_csv($article[description], 1);
				$article[libelle] = secure_chaine_csv($article[libelle], 1);
				
				// MPN
				//$contenu .= $article[reference_fabricant]."|";
				// EAN / UPC
				//$contenu .= $article[code_EAN]."|";
				// Fabricant ou Marque
				/*$article[marque] = "";
				$tab[separateur_marques] = ", ";
				$article_marques = $DB_site->query("SELECT * FROM marque m, article_marque am WHERE m.marqueid = am.marqueid AND am.artid = '$article[artid]' LIMIT 1");
				while ($article_marque=$DB_site->fetch_array($article_marques)) {
					$article[marque] .= $article_marque[$libelle] . $tab[separateur_marques] ; // ici : rajouter les liens et autres de marque ...
				}
				$article[marque] = substr($article[marque], 0, -strlen($tab[separateur_marques])) ;
				if (!$article[marque]) {
					$article[marque] = ${$title};
				}
				$article[marque] = secure_chaine_csv($article[marque], 1);
				$contenu .= $article[marque]."|";
				// Reference Interne
				$contenu .= $article[artcode]."|";*/
				// Nom du produit
				$contenu .= $article[libelle]."|"; 
				// Prix
				if (estEnPromoSite($DB_site, $article[artid],$siteid)) {
					// Prix promo
					$article[prix2] = formaterPrix($article[prix],2,'.','') ;
					$article_promo = $DB_site->query_first("SELECT * FROM article_promo_site WHERE artid = '$article[artid]' AND siteid = '$siteid'");
					$article[prix] = formaterPrix($article[prix] * (1 - ($article_promo[pctpromo] / 100)), 2, '.', '') ;
				} else {
					/*$prixVente = $article[prix];
					if ($tab[exporter_prixpublic] == 1){
						$contenu .= $article[prixpublic]."|";
					}else{
						$contenu .= $article[prix]."|";
					}*/
					$article[prix2] = 0 ;
				}
				$contenu .= $article[prix]."|";
				$contenu .= $article[prix2]."|";
				// Expedition standard
				
				/*
				// Disponibilite
				if (!in_array(4, $modules) || (retournerStockArticle($DB_site, $article[artid]) > 0)) {
					$contenu .= "oui|";
				} else {
					$contenu .= "non|";
				}
				*/
				// URL produit
				
				$contenu .= "http://$infos_site[site_url]/".$regleurlrewriteTemp[article]."-".url_rewrite($article[libelle])."-".$article[artid].".htm|";	
				// Description du produit
				$contenu .= $article[description]."|";
				// URL image
				if ($article[image] != "") {
					$contenu .= "http://$infos_site[site_url]/ar-".url_rewrite($article[libelle])."-".$article[artid].".".$article[image]."|";			
				} else {
					$contenu .= "|";
				} 
				// Categorie
				$separateur_navigation = " / ";
				$cheminsanshref="";
				cheminsanshref($article[catid],$DB_site);
				$cheminsanshref = secure_chaine_csv($cheminsanshref);
				
				$contenu .= $cheminsanshref."|";	
				//ID
				$contenu .= $article[artid]."|";
				
				//En stock
				$contenu.="O|";
				
				//Frais port
				$top = array("artid" => $article[artid], "prix" => $prixVente, "poids" => $article[poids]);
				$topFp = trouve_articleFraisPort($DB_site, $top, "", "", 0) ;
				$article[prixport] = formaterPrix($topFp[fraisport], 2, '.', '') ;
				$contenu .= round($article[prixport])."|";
				
				// Description de la disponibilite + garantie
				$contenu .= "Livraison sous ".$article[delai]." jours|";
				
				
				// URL image secondaire
				/*$articlephoto = $DB_site->query_first("SELECT * FROM articlephoto WHERE artid = '$article[artid]' ORDER BY position LIMIT 1");
				if ($articlephoto[image] != "") {
					$contenu .= "http://$host/ar-".url_rewrite($article[$libelle])."-".$article[artid]."_".$articlephoto[articlephotoid].".".$articlephoto[image]."|";			
				} else {
					$contenu .= "|";
				}
				// Prix promo
				$contenu .= $prixVente."|";*/
				$contenu.="\n";
			}
		}
		return $contenu;
	}
}

if(!$onestdansuncron){
	$sites = $DB_site->query("SELECT * FROM site");
	$comparateurid = $DB_site->query_first("SELECT comparateurid FROM comparateur WHERE fichier='".$nom_comparateur.".php'");
	
	while($site = $DB_site->fetch_array($sites)){
		$provenance = "";
	
		$contenu=exporter_shopping($site[siteid]);
		if (isset($provenance) and $provenance == "selection"){
			$nom_fic = "export_".$nom_comparateur."_selection".$site[siteid].".txt";
		}else{
			$nom_fic = "export_".$nom_comparateur.$site[siteid].".txt";
		}
		if (!is_dir($rootpath."configurations/$host/exports")) {
			mkdir($rootpath."configurations/$host/exports",0777);
		}
		$filename = $rootpath."configurations/$host/exports/".$nom_fic;
		if (!$handle = fopen($filename, 'w')) {
			echo "$multilangue[erreur_ouverture_fichier] ($filename)";
			exit;
		} else {
			if (fwrite($handle, $contenu) === FALSE) {
				echo "$multilangue[erreur_ecriture_fichier] ($filename)";
				fclose($handle);
				exit;
			} else {
				fclose($handle);
			}
		}
		
		$articleselection = $DB_site->query("SELECT * FROM article_comparateur WHERE siteid=$site[siteid] AND comparateurid=$comparateurid[comparateurid]");
		if($DB_site->num_rows($articleselection)){
			$provenance = "selection";
			$contenu=exporter_shopping($site[siteid]);
			if (isset($provenance) and $provenance == "selection"){
				$nom_fic = "export_".$nom_comparateur."_selection".$site[siteid].".txt";
			}else{
				$nom_fic = "export_".$nom_comparateur.$site[siteid].".txt";
			}
			if (!is_dir($rootpath."configurations/$host/exports")) {
				mkdir($rootpath."configurations/$host/exports",0777);
			}
			$filename = $rootpath."configurations/$host/exports/".$nom_fic;
			if (!$handle = fopen($filename, 'w')) {
				echo "$multilangue[erreur_ouverture_fichier] ($filename)";
				exit;
			} else {
				if (fwrite($handle, $contenu) === FALSE) {
					echo "$multilangue[erreur_ecriture_fichier] ($filename)";
					fclose($handle);
					exit;
				} else {
					fclose($handle);
				}
			}
		}
	}
}

?>