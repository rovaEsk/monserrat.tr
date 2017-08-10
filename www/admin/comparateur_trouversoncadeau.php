<?
ini_set('memory_limit','128M');
ini_set('max_execution_time','0');

$prov = "comp" ;
if (!$rootpath)
	$rootpath = $_SERVER['DOCUMENT_ROOT'] ;
$nom_comparateur = "comparateur_trouversoncadeau";

if(!$onestdansuncron){
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
	
if (!function_exists("cheminsanshref2")) {
	function cheminsanshref2($categorie, $DB) {
		global $libelle, $separateur_navigation, $titleFR, $host, $cheminsanshref ;
		$ss=$DB->query_first("SELECT COUNT(attributid) FROM googleshopping_attribut WHERE parentid = '$categorie'");
		$categorie=$DB->query_first("SELECT parentid, libelle, attributid FROM googleshopping_attribut where attributid = '$categorie'");
		$cheminsanshref = $categorie[libelle].$cheminsanshref;
		if ($categorie[parentid] != 0){
			$cheminsanshref = $separateur_navigation.$cheminsanshref ;
			cheminsanshref2($categorie[parentid], $DB) ;
		}
		return $cheminsanshref ;
		}
	}
	
/*if (!function_exists("supprcarspe")){
	function supprcarspe($chaine){	
		$chaine_finale = "";
		for ($i=0;$i<strlen($chaine);$i++){
			if (ord($chaine[$i]) != 38 && ord($chaine[$i]) != 60 && ord($chaine[$i]) != 62 && ord($chaine[$i]) != 146 && ord($chaine[$i]) < 250 && ord($chaine[$i]) > 31 && ord($chaine[$i]) != 128){
				$chaine_finale .= $chaine[$i];
			}elseif (ord($chaine[$i]) == 128){
				$chaine_finale .= "euro";
			}
		}
		return $chaine_finale;
	}
}
*/
if (!function_exists("exporter_trouversoncadeau")) {
	function exporter_trouversoncadeau($siteid){	
		global $DB_site, $libelle,$host,$provenance,$regleurlrewrite,$cheminsanshref,$modules, $tab, $ajd, $nom_site, $description_site;
		$title = "title".$siteid;
		global ${$title};
		$liste_artid = "SELECT artid from article_comparateur where comparateurid = '5' AND siteid = '$siteid' ORDER BY artid";
		$nom_site = $DB_site->query_first("SELECT * FROM meta INNER JOIN meta_site USING(metaid) WHERE scriptname='/index.php'");
		$nom_site = $nom_site[t];
		$nom_site = str_replace("[boutiquetitre]", ${$title}, $nom_site);
		$description_site = $DB_site->query_first("SELECT * FROM meta INNER JOIN meta_site USING(metaid) WHERE scriptname='/index.php'");
		$description_site = $description_site[t];
		$description_site = str_replace("[boutiquetitre]", ${$title}, $description_site);
		$contenu="<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<produits>\n";
		
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
			$nom_fic = "export_google_selection".$siteid.".xml";
			$articles = $DB_site->query("SELECT * FROM article INNER JOIN article_site USING(artid) WHERE siteid='$siteid' AND prix > 0 AND artid IN ($liste_artid) $where");	
		}else{
			$nom_fic = "export_google".$siteid.".xml";
			$articles = $DB_site->query("SELECT * FROM article INNER JOIN article_site USING(artid) WHERE siteid='$siteid' AND prix > 0 $where ORDER BY artid");
		}
		while($article=$DB_site->fetch_array($articles)){
			
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
				$contenu.="<offre>\n";
				$marque = secure_chaine_csv($article[marque],1);
				$article[description] = secure_chaine_csv($article[description],1);
				$article[libelle] = secure_chaine_csv($article[libelle],1);
				
				
				$article[libelle] = explode("<>",wordwrap($article[libelle],70, "<>", 1));
				$article[libelle] = $article[libelle][0];
				$contenu.= "<nom>".$article[libelle]."</nom>\n";	
				if ($article[description] == ""){
					$article[description] = "Pas de description pour cet article";
				}
				
				$contenu.="<description>".strtolower($article[description])."</description>\n";
				if (estEnPromo($DB_site, $article[artid],0)) {
					$prix = $article[prix];
					$prixpromo = round($article[prix] - ($article[prix]*$article[pctpromo]/100),2);
					$prixVente = $prixpromo;
					$contenu.="<prix>".$prixpromo."</prix>\n";
			
				} else {
					if ($tab[exporter_prixpublic] == 1){
						$prix = $article[prixpublic];
						$prixpromo = $article[prix];
						$prixVente = $prixpromo;
						$contenu.="<prix>".$prixpromo."</prix>\n";
					}else{
						$prix = $article[prix];
						$prixVente = $article[prix];			
						$contenu.="<prix>".$prix."</prix>\n";
					}
					
				}
				
				$categories_trouversoncadeau = $DB_site->query("SELECT * FROM trouversoncadeau_categories INNER JOIN trouversoncadeau_categories_site USING(trouversoncadeau_catid) INNER JOIN trouversoncadeau_articles USING(trouversoncadeau_catid) WHERE artid = '$article[artid]' ORDER BY niveau");
				while($categorie_trouversoncadeau=$DB_site->fetch_array($categories_trouversoncadeau)){			
					$categorie_trouversoncadeau[libelle] = secure_chaine_csv($categorie_trouversoncadeau[libelle],1);
					$contenu .= "<cat$categorie_trouversoncadeau[niveau]>".strtolower($categorie_trouversoncadeau[libelle])."</cat$categorie_trouversoncadeau[niveau]>\n";
				}
				
				if ($article[image] != ""){
					$contenu.="<image>http://$host/ori-".url_rewrite($article[libelle])."-".$article[artid].".".$article[image]."</image>\n";						
				}else{
					$contenu.="<image></image>\n";
				}
				
				$contenu.="<lien>http://$host/".$regleurlrewrite[$siteid][article]."-".url_rewrite($article[libelle])."-".$article[artid].".htm</lien>\n";	
							
				$contenu .= "</offre>\n";
			}
		}
		$contenu.="</produits>\n";
		return $contenu;
	}
}
	
if(!$onestdansuncron){
	$sites = $DB_site->query("SELECT * FROM site");
	$comparateurid = $DB_site->query_first("SELECT comparateurid FROM comparateur WHERE fichier='".$nom_comparateur.".php'");
	
	while($site = $DB_site->fetch_array($sites)){
		$provenance = "";
		$contenu=exporter_trouversoncadeau($site[siteid]);
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
		
		$articleselection = $DB_site->query("SELECT * FROM article_comparateur WHERE siteid=$site[siteid] AND comparateurid=$comparateurid[comparateurid]");
		if($DB_site->num_rows($articleselection)){
			$provenance = "selection";
			$contenu=exporter_trouversoncadeau($site[siteid]);
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