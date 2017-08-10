<?
ini_set('memory_limit','128M');
ini_set('max_execution_time','0');

$prov = "comp" ;
if (!$rootpath)
	$rootpath = $_SERVER['DOCUMENT_ROOT'] ;
$nom_comparateur = "comparateur_shopzilla";


if($onestdansuncron=="" or $onestdansuncron==0){
	require_once $rootpath."admin/includes/admin_global.php";
} else {
//	require_once $rootpath."admin/includes/fonctions.php";
}

if (!function_exists("cheminsanshref")) {
	function cheminsanshref($categorie, $DB) {
		global $libelle, $separateur_navigation, $titleFR, $host, $cheminsanshref ;
		$ss=$DB->query_first("SELECT COUNT(catid) FROM categorie WHERE parentid = '$categorie'");
		$categorie=$DB->query_first("SELECT * FROM categorie where catid = '$categorie'");
		$cheminsanshref = " $separateur_navigation $categorie[libelle] $cheminsanshref" ;
		if ($categorie[parentid] != 0)
			cheminsanshref($categorie[parentid], $DB) ;
		else
			$cheminsanshref = "$titleFR $cheminsanshref" ;
		return $cheminsanshref ;
		}
	}

if (!function_exists("exporter_shopzilla")) {
	function exporter_shopzilla($siteid){	
		global $DB_site, $libelle,$host,$provenance,$regleurlrewrite,$cheminsanshref,$modules,$datedujour, $tab;
		
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
		
		$liste_artid = "SELECT artid from article_comparateur where comparateurid = '17' AND siteid = '$siteid' ORDER BY artid";
		$contenu="libelle;marque;ean;url;url image;prix TTC;frais port TTC;disponibilite;description;categorie;sku;id;devise\n";
		if ($provenance == "selection"){
			$nom_fic = "export_icomparateur_selection".$siteid.".csv";
			$articles = $DB_site->query("SELECT * FROM article INNER JOIN article_site USING(artid) WHERE prix > 0 AND siteid='$siteid' AND artid IN ($liste_artid) $where");	
		}else{
			$nom_fic = "export_icomparatuer".$siteid.".csv";
			$articles = $DB_site->query("SELECT * FROM article INNER JOIN article_site USING(artid) WHERE siteid='$siteid' AND prix > 0 $where ORDER BY artid");
		}
		while ($article=$DB_site->fetch_array($articles)) {
			$export = 1;
			if ($aff_inactive == 0){
				$categs = $DB_site->query("SELECT catid FROM position WHERE artid = '$article[artid]'");
				while($categ=$DB_site->fetch_array($categs)){
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
				$article[description] = secure_chaine_csv($article[description], 1);
				$article[libelle] = secure_chaine_csv($article[libelle], 1);		
	
				// libelle
				$contenu .= $article[libelle].";" ;
				// marque
				$article[marque] = "";
				$tab[separateur_marques] = ", ";
				$article_marques = $DB_site->query("SELECT * FROM marque INNER JOIN marque_site USING ( marqueid ) INNER JOIN article_marque USING ( marqueid ) WHERE artid = '$article[artid]' AND siteid = '$siteid' LIMIT 1");
				while ($article_marque=$DB_site->fetch_array($article_marques))
					$article[marque] .= $article_marque[libelle] . $tab[separateur_marques] ;
				$article[marque] = substr($article[marque], 0, -strlen($tab[separateur_marques])) ;
				$article[marque] = secure_chaine_csv($article[marque],0);
				$contenu.=$article[marque].";";	
				// ean
				$contenu.=$article[code_EAN].";";
				// url
				$contenu.="http://$host/".$regleurlrewrite[$siteid][article]."-".url_rewrite($article[libelle])."-".$article[artid].".htm".";";			
				// url image
				if ($article[image] != "")			
					$contenu.="http://$host/ar-".url_rewrite($article[libelle])."-".$article[artid].".".$article[image].";";					
				else
					$contenu.=";";
				// prix TTC
				$prixbarre = 0;
				if (estEnPromo($DB_site, $article[artid],0)) {	
					$prix = $article[prix] * (1 - ($article[pctpromo] / 100));
					$prixVente = $prix;
					$prix = formaterPrix($prix);
					$prixbarre = $article[prix];
				}else{
					if ($tab[exporter_prixpublic] == 1){
						$prixbarre = $article[prixpublic];
					}
					$prix = formaterPrix($article[prix]) ;
					$prixVente = $article[prix];
				}
				$contenu .= $prix.";";
				// frais port TTC
				$top = array("artid" => $article[artid], "prix" => $prixVente, "poids" => $article[poids]);
				$topFp = trouve_articleFraisPort($DB_site, $top, "", "", 0) ;
				$article[prixport] = formaterPrix($topFp[fraisport]);
				
				$contenu.="$article[prixport];";
				// dispo
				if (!in_array(4, $modules) || (retournerStockArticle($DB_site, $article[artid]) > 0)) {
					$contenu .= "In stock;";
				} else {
					$contenu .= ";";
				}
				// description (250 car)

				$desc = explode("<>",wordwrap($article[description],250, "<>", 1));
				$desc = $desc[0];

				$contenu.=$desc.";";
				// categorie
				$cheminsanshref = "";
				$separateur_navigation = " > ";
				cheminsanshref($article[catid],$DB_site);
				$cheminsanshref = secure_chaine_csv($cheminsanshref, 1);		
				$contenu.=$cheminsanshref.";";
				// sku
				$contenu .= ";" ;
				// id
				$contenu .= $article[artid].";" ;
				// currency
				$devise = $DB_site->query_first("SELECT devise FROM devise INNER JOIN site USING(deviseid) WHERE siteid='$siteid'");
				$contenu.="$devise[devise];";
			
				$contenu .= "\n" ;
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
		$contenu=exporter_shopzilla($site[siteid]);
		if (isset($provenance) and $provenance == "selection"){
			$nom_fic = "export_".$nom_comparateur."_selection".$site[siteid].".csv";
		}else{
			$nom_fic = "export_".$nom_comparateur.$site[siteid].".csv";
		}
		if (!is_dir($rootpath."configurations/$host/exports")) {
			mkdir($rootpath."configurations/$host/exports",0777);
		}
		$filename = $rootpath."configurations/$host/exports/".$nom_fic;
		if (!$handle = fopen($filename, 'w')) {
			echo "$multilangue[erreur_ouverture_fichier] ($filename)";
			exit;
		} else {
			fputs($handle, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF)));
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
			$contenu=exporter_shopzilla($site[siteid]);
			if (isset($provenance) and $provenance == "selection"){
				$nom_fic = "export_".$nom_comparateur."_selection".$site[siteid].".csv";
			}else{
				$nom_fic = "export_".$nom_comparateur.$site[siteid].".csv";
			}
			if (!is_dir($rootpath."configurations/$host/exports")) {
				mkdir($rootpath."configurations/$host/exports",0777);
			}
			$filename = $rootpath."configurations/$host/exports/".$nom_fic;
			if (!$handle = fopen($filename, 'w')) {
				echo "$multilangue[erreur_ouverture_fichier] ($filename)";
				exit;
			} else {
				fputs($handle, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF)));
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