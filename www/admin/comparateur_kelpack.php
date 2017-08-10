<?
ini_set('memory_limit','128M');
ini_set('max_execution_time','0');
set_time_limit(0);

$prov = "comp" ;
if (!$rootpath)
	$rootpath = $_SERVER['DOCUMENT_ROOT'] ;
$nom_comparateur = "comparateur_kelpack";

if($onestdansuncron=="" or $onestdansuncron==0){
	require_once $rootpath."admin/includes/admin_global.php";
} else {
//	require_once $rootpath."admin/includes/fonctions.php";
}

if (!function_exists("cheminsanshref")) {
	function cheminsanshref($categorie, $DB) {
		global $libelle, $separateur_navigation, $titleFR, $host, $cheminsanshref ;
		$ss=$DB->query_first("SELECT COUNT(catid) FROM categorie WHERE parentid = '$categorie'");
		$categorie=$DB->query_first("SELECT * FROM categorie INNER JOIN categorie_site USING(catid) WHERE catid = '$categorie'");
		$cheminsanshref = " $separateur_navigation $categorie[libelle] $cheminsanshref" ;
		if ($categorie[parentid] != 0)
			cheminsanshref($categorie[parentid], $DB) ;
		else
			$cheminsanshref = "$titleFR $cheminsanshref" ;
		return $cheminsanshref ;
	}
}

/*if (!function_exists("supprcarspe_kelpack")){
	function supprcarspe_kelpack($chaine){	
		$chaine_finale = "";
		for ($i=0;$i<strlen($chaine);$i++){
			if (ord($chaine[i]) != 38 && ord($chaine[i]) != 60 && ord($chaine[i]) != 62 && ord($chaine[i]) != 146 && ord($chaine[i]) < 250 && ord($chaine[i]) > 31 && ord($chaine[i]) != 128 && ord($chaine[i]) != 156 && ord($chaine[i]) != 140 && ord($chaine[i]) != 148 && ord($chaine[i]) != 147 && ord($chaine[i]) != 150){
				$chaine_finale .= $chaine[i];
			}elseif (ord($chaine[i]) == 128){
				$chaine_finale .= "euro";
			}elseif (ord($chaine[i]) == 156){
				$chaine_finale .= "oe";
			}elseif (ord($chaine[i]) == 140){
				$chaine_finale .= "Oe";
			}elseif (ord($chaine[i]) == 38){
				$chaine_finale .= "et";
			}elseif (ord($chaine[i]) == 150){
				$chaine_finale .= "-";
			}
		}
		//$chaine_finale = str_replace('&amp;','et',$chaine_finale);
		//$chaine_finale = str_replace('& ','et ',$chaine_finale);
		return $chaine_finale;
	}
}*/


if (!function_exists("exporter_kelpack")) {
	function exporter_kelpack($siteid){
		global $DB_site,$libelle,$host,$provenance,$regleurlrewrite,$cheminsanshref,$modules,$tab,$ajd;
	
		
		$contenu = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
		$contenu .="<products>\n";
		
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
		
		$liste_artid = "SELECT artid FROM article_comparateur where comparateurid = '14' AND siteid = '$siteid[siteid]' ORDER BY artid";
		
		if (isset($provenance) and $provenance == "selection") {
			$nom_fic = "export_kelpack_selection".$siteid.".xml";
			$articles = $DB_site->query("SELECT * FROM article INNER JOIN article_site USING(artid) WHERE siteid='$siteid' AND prix > 0 AND artid IN ($liste_artid) $where");
		} else {
			$nom_fic = "export_kelpack".$siteid.".xml";
			$articles = $DB_site->query("SELECT * FROM article INNER JOIN article_site USING(artid) WHERE siteid='$siteid' AND prix > 0 $where ORDER BY artid");
		}
		while ($article = $DB_site->fetch_array($articles)) {
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
				//Marque
				$article[marque] = "";
				$tab[separateur_marques] = ", ";
				$article_marques = $DB_site->query("SELECT * FROM marque INNER JOIN marque_site USING ( marqueid ) INNER JOIN article_marque USING ( marqueid ) WHERE artid = '$article[artid]' AND siteid = '$siteid' LIMIT 1") ;
				while ($article_marque=$DB_site->fetch_array($article_marques))
					$article[marque] .= $article_marque[libelle] . $tab[separateur_marques] ;
				$article[marque] = substr($article[marque], 0, -strlen($tab[separateur_marques])) ;
				$marque = secure_chaine_csv($article[marque],0);
				if ($marque == ''){$marque = 'Noname';}
								
				$article[libelle] = explode("<>",wordwrap($article[libelle],80, "<>", 1));
				$article[libelle] = $article[libelle][0];
				$article[libelle] = secure_chaine_csv($article[libelle],1);
				
				$cheminsanshref = "";
				$separateur_navigation = " > ";
				cheminsanshref($article[catid],$DB_site);
				$cheminsanshref = secure_chaine_csv($cheminsanshref);
				
				$contenu .= "<product>\n";
				// title
				$contenu .= "<title>".$article[libelle]."</title>\n";
				// product-url
				$contenu .= "<product-url>http://$host/".$regleurlrewrite[$siteid][article]."-".url_rewrite($article[libelle])."-".$article[artid].".htm</product-url>\n";
				// price
				if (estEnPromo($DB_site, $article[artid],0)) {
					$prix = $article[prix];
					$prixpromo = round($article[prix] - ($article[prix]*$article[pctpromo]/100),2);
					$prixVente = $prixpromo;
				} else {
					if ($tab[exporter_prixpublic] == 1 && $article[prixpublic]){
						$prix = $article[prixpublic];
						$prixpromo = $article[prix];
						$prixVente = $prixpromo;
					}else{
						$prix = $article[prix];
						$prixVente = $article[prix];
					}
					
				}
				$contenu .= "<price>".$prixVente."</price>\n";
				// brand
				$contenu .= "<brand>".$marque."</brand>\n";
				// description
				$article[description] = secure_chaine_csv($article[description], 1);
				$contenu .= "<description>".$article[description]."</description>\n";
				// image-url
				if ($article[image] != ""){
					$contenu .= "<image-url>http://$host/ori-".url_rewrite($article[libelle])."-".$article[artid].".".$article[image]."</image-url>\n";						
				} else {
					$contenu .= "<image-url></image-url>\n";
				}
				// ean
				$contenu .= "<ean>".$article[code_EAN]."</ean>\n";
				// merchant-category
				$contenu .= "<merchant-category>".$cheminsanshref."</merchant-category>\n";
				// availability
				$contenu .= "<availability>1</availability>\n";
				// delivery-cost
				$top = array("artid" => $article[artid], "prix" => $prixVente, "poids" => $article[poids]);
				$topFp = trouve_articleFraisPort($DB_site, $top, "", "", 0) ;
				$fraisport = formaterPrix($topFp[fraisport], 2, '.', '') ;
				$contenu .= "<delivery-cost>".$fraisport."</delivery-cost>\n";
				// delivery-time
				$contenu .= "<delivery-time>Sous $article[delai] jours</delivery-time>\n";
				// condition
				$contenu .= "<condition>0</condition>\n";
				// ecotax
				$contenu .= "<ecotax>0</ecotax>\n";
				// warranty
				$contenu .= "<warranty></warranty>\n";
				
				$contenu .= "</product>\n";
			}
			
		}
		$contenu .= "</products>\n";
		return $contenu;
	}
}


if(!$onestdansuncron){

	$sites = $DB_site->query("SELECT * FROM site");
	$comparateurid = $DB_site->query_first("SELECT comparateurid FROM comparateur WHERE fichier='".$nom_comparateur.".php'");
	
	while($site = $DB_site->fetch_array($sites)){
		$provenance = "";
		$contenu=exporter_kelpack($site[siteid]);
		if (isset($provenance) and $provenance == "selection"){
			$nom_fic = "export_".$nom_comparateur."_selection".$site[siteid].".xml";
		}else{
			$nom_fic = "export_".$nom_comparateur.$site[siteid].".xml";
		}
		if (!is_dir($rootpath."configurations/$host/exports")) {
			mkdir($rootpath."configurations/$host/exports",0777);
		}
		$filename = $rootpath."configurations/$host/exports/".$nom_fic;
		if (!$handle = fopen($filename, 'w')) {
			echo "$multilangue[erreur_ouverture_fichier] ($filename)";
			exit;
		} else {
			if (fwrite($handle,$contenu) === FALSE) {
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
			$contenu=exporter_kelpack($site[siteid]);
			if (isset($provenance) and $provenance == "selection"){
				$nom_fic = "export_".$nom_comparateur."_selection".$site[siteid].".xml";
			}else{
				$nom_fic = "export_".$nom_comparateur.$site[siteid].".xml";
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