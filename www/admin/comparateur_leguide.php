<?
ini_set('memory_limit','128M');
ini_set('max_execution_time','0');

$prov = "comp" ;
if (!$rootpath)
	$rootpath = $_SERVER['DOCUMENT_ROOT'] ;
$nom_comparateur = "comparateur_leguide";

if(!$onestdansuncron){
	require_once $rootpath."admin/includes/admin_global.php";
}

if (!function_exists("cheminsanshref")) {
	function cheminsanshref($categorie, $DB) {
		global $libelle, $separateur_navigation, $titleFR, $host, $cheminsanshref ;
		$ss=$DB->query_first("SELECT COUNT(catid) FROM categorie WHERE parentid = '$categorie'");
		$categorie=$DB->query_first("SELECT * FROM categorie INNER JOIN categorie_site USING(catid) WHERE catid = '$categorie'");
		$categorie[libelle] = str_replace(";"," ",$categorie[libelle]);
		$cheminsanshref = " $separateur_navigation $categorie[libelle] $cheminsanshref" ;
		if ($categorie[parentid] != 0)
			cheminsanshref($categorie[parentid], $DB) ;
		else
			$cheminsanshref = "$titleFR $cheminsanshref" ;
		
		return $cheminsanshref ;
	}
}

if (!function_exists("exporter_leguide")) {
	function exporter_leguide($siteid){	
		global $DB_site, $libelle,$host,$mobilehost,$provenance,$regleurlrewrite,$cheminsanshref,$modules,$tab, $datedujour;
		$liste_artid = "SELECT artid from article_comparateur where comparateurid = '5' AND siteid = '$siteid' ORDER BY artid";

		$contenu="categorie;identifiant_unique;titre;prix;url_produit;url_image;description;frais_de_port;D3E;disponibilite;marque;ean;delais_de_livraison;garantie;prix_barre;reference_modele;occasion;devise";
			if(in_array(5932, $modules)){$contenu.=";mobile_url";}
		$contenu.="\n";
		
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
		
		if (isset($provenance) and $provenance == "selection"){
			$nom_fic = "export_leguide_selection".$siteid.".csv";
			$articles = $DB_site->query("SELECT * FROM article INNER JOIN article_site USING(artid) WHERE prix > 0 AND siteid='$siteid' AND artid IN ($liste_artid) $where");	
		}else{
			$nom_fic = "export_leguide".$siteid.".csv";
			$articles = $DB_site->query("SELECT * FROM article INNER JOIN article_site USING(artid) WHERE prix > 0 AND siteid='$siteid' $where ORDER BY artid");
		}
		while ($article = $DB_site->fetch_array($articles)){
			$export = 1;
			if ($aff_inactive == 0){
				$categs = $DB_site->query("SELECT catid FROM position WHERE artid = '$article[artid]'");
				while($categ=$DB_site->fetch_array($categs)){
					$catid = $categ[catid];
					$export = 1;
					while ($catid != 0 && $export == 1){
						$categ2 = $DB_site->query_first("SELECT * FROM categorie INNER JOIN categorie_site USING(catid) WHERE catid = '$catid' AND siteid = '$siteid'");
						if ($categ2["visible_treeviewV1".$siteid] == 0){
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
				$article[description] = secure_chaine_csv($article[description], 1);
				$article[libelle] = secure_chaine_csv($article[libelle], 1);
				
				// categorie
				$cheminsanshref = "";
				$separateur_navigation = " > ";
				cheminsanshref($article[catid],$DB_site);
				$cheminsanshref = secure_chaine_csv($cheminsanshref, 0);
				$contenu .= $cheminsanshref.";";
				// identifiant_unique
				$contenu .= $article[artid].";";
				// titre
				$contenu .= $article[libelle].";";
				// prix
				$prixbarre = 0;
				if (estEnPromo($DB_site, $article[artid],0)){
					$prix = $article[prix] * (1 - ($article[pctpromo] / 100));
					$prixbarre = $article[prix];
				}elseif ($tab[exporter_prixpublic] == 1){
					$prixbarre = $article[prixpublic];
					$prix = $article[prix];
				}else{
					$prix = $article[prix];
				}
				$contenu .= formaterPrix($prix, 2, ",", "").";";
				// url_produit
				$contenu.="http://$host/".$regleurlrewrite[langueExport][article]."-".url_rewrite($article[libelle])."-".$article[artid].".htm".";";	
				// url_image
				if ($article[image] != "")
					$contenu.="http://$host/ar-".url_rewrite($article[libelle])."-".$article[artid].".".$article[image].";";					
				else
					$contenu.=";";
				// description
				$desc = explode("<>",wordwrap($article[description],250, "<>", 1));
				$desc = $desc[0];
				
				if ($desc == "") 
					$desc="Pas de description pour cet article";
				
				$contenu .= $desc.";";
				
				// frais_de_livraison
				$top = array("artid" => $article[artid], "prix" => $prix, "poids" => $article[poids]);
				$topFp = trouve_articleFraisPort($DB_site, $top, "", "", 0) ;
				$article[prixport] = number_format($topFp[fraisport], 2, '.', '') ;
				$contenu.="$article[prixport];";
				
				// D3E
				$contenu.=";";
				// disponibilite
				if (in_array("4",$modules)){
					if ($nbstock <= 0)
						$contenu.=";";
					elseif ($nbstock > 0)
						$contenu.="0;";
					else
						$contenu.=";";
				}else
					$contenu.="0;";
				// marque
				$article[marque] = "";
				$tab[separateur_marques] = ", ";
				$article_marques = $DB_site->query("SELECT * FROM marque INNER JOIN marque_site USING ( marqueid ) INNER JOIN article_marque USING ( marqueid ) WHERE artid = '$article[artid]' AND siteid = '$siteid' LIMIT 1") ;
				while ($article_marque=$DB_site->fetch_array($article_marques)) {
					$article[marque] .= $article_marque[libelle] . $tab[separateur_marques] ; // ici : rajouter les liens et autres de marque ...
				}
				$article[marque] = substr($article[marque], 0, -strlen($tab[separateur_marques])) ;
				$article[marque] = secure_chaine_csv($article[marque],0);
				$contenu.="$article[marque];";
				//ean
				$article[code_EAN] = secure_chaine_csv($article[code_EAN],0);
				$contenu.="$article[code_EAN];";
				//delai_de_livraison
				$contenu.="$article[delai];";
				// garantie
				$contenu.="0;";
				// prix_barre
				if ($prixbarre != 0){ // Article en promo -> on affiche le prix promo
					$prixbarre = formaterPrix($prixbarre, 2, ",", "");
					$contenu.="$prixbarre;";
				} 
				else
					$contenu.=";";
				// reference_modele
				$contenu .= $article[artcode].";";
				// occasion
				$contenu.="0;";
				// devise	
				$devise = $DB_site->query_first("SELECT devise FROM devise INNER JOIN site USING(deviseid) WHERE siteid='$siteid'");
				$contenu.="$devise[devise];";
				if(in_array(5932, $modules)){
					// url_produit mobile
					$contenu.="http://".$mobilehost."/".$regleurlrewrite[langueExport][article]."-".url_rewrite($article[libelle])."-".$article[artid].".htm".";";	
				}
				$contenu .= "\n";
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
	
		$contenu=exporter_leguide($site[siteid]);
		if (isset($provenance) and $provenance == "selection"){
			$nom_fic = "export_".$nom_comparateur."_selection".$site[siteid].".csv";
		}else{
			$nom_fic = "export_".$nom_comparateur.$site[siteid].".csv";
		}
		if (!is_dir($rootpath."configurations/$host/exports")) {
			mkdir($rootpath."configurations/$host/exports",0777);
		}
		$filename = $rootpath."configurations/$host/exports/".$nom_fic;
		if (!$handle = fopen($filename, 'w+')) {
			echo "$multilangue[erreur_ouverture_fichier] ($filename)";
			exit;
		}else{
			// UTF8 pour csv
			fputs($handle, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF)));
			if (fwrite($handle, $contenu) === FALSE){
				echo "$multilangue[erreur_ecriture_fichier] ($filename)";
				fclose($handle);
				exit;
			}else{
				fclose($handle);
			}
		}
		
		$articleselection = $DB_site->query("SELECT * FROM article_comparateur WHERE siteid=$site[siteid] AND comparateurid=$comparateurid[comparateurid]");
		if($DB_site->num_rows($articleselection)){
			$provenance = "selection";
			$contenu=exporter_leguide($site[siteid]);
			if (isset($provenance) and $provenance == "selection"){
				$nom_fic = "export_".$nom_comparateur."_selection".$site[siteid].".csv";
			}else{
				$nom_fic = "export_".$nom_comparateur.$site[siteid].".csv";
			}
			if (!is_dir($rootpath."configurations/$host/exports")) {
				mkdir($rootpath."configurations/$host/exports",0777);
			}
			$filename = $rootpath."configurations/$host/exports/".$nom_fic;
			if (!$handle = fopen($filename, 'w+')) {
				echo "$multilangue[erreur_ouverture_fichier] ($filename)";
				exit;
			}else{
				// UTF8 pour csv
				fputs($handle, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF)));
				if (fwrite($handle, $contenu) === FALSE){
					echo "$multilangue[erreur_ecriture_fichier] ($filename)";
					fclose($handle);
					exit;
				}else{
					fclose($handle);
				}
			}
		}
	}
}
?>