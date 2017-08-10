<?
ini_set('memory_limit','128M');
ini_set('max_execution_time','0');

$prov = "comp" ;
if (!$rootpath)
	$rootpath = $_SERVER['DOCUMENT_ROOT'] ;
$nom_comparateur = "comparateur_kelkoo";
echo "pass";

if(!$onestdansuncron){
	require_once $rootpath."admin/includes/admin_global.php";
} else {
//	require_once $rootpath."admin/includes/fonctions.php";
}

if (!function_exists("cheminsanshref")) {
	function cheminsanshref($categorie, $DB) {
		global $libelle, $separateur_navigation, $titleFR, $host, $cheminsanshref ;
		$ss=$DB->query_first("SELECT COUNT(catid) FROM categorie where parentid = '$categorie'");
		$categorie=$DB->query_first("SELECT * FROM categorie INNER JOIN categorie_site USING(catid) WHERE catid = '$categorie'");
		$cheminsanshref = " $separateur_navigation $categorie[libelle] $cheminsanshref" ;
		if ($categorie[parentid] != 0)
			cheminsanshref($categorie[parentid], $DB) ;
		else
			$cheminsanshref = "$titleFR $cheminsanshref" ;
		return $cheminsanshref ;
		}
	}

if (!function_exists("exporter_kelkoo")) {
	function exporter_kelkoo($siteid){
		global $DB_site, $libelle,$host,$provenance,$regleurlrewrite,$cheminsanshref,$modules,$tab,$datedujour;
	
		$contenu="#country=fr\n#type=basic\n#currency=EUR\ncategory\turl\ttitle\tdescription\tprice\tofferid\timage\tavailability\tdeliverycost\tecopart\n";
		
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
		
		$liste_artid = "SELECT artid FROM article_comparateur WHERE comparateurid = '2' AND siteid = '$siteid' ORDER BY artid";
		
		if (isset($provenance) and $provenance == "selection"){
			$nom_fic = "export_kelkoo_selection$siteid.txt";
			$articles = $DB_site->query("SELECT * FROM article INNER JOIN article_site USING(artid) WHERE prix > 0 AND siteid='$siteid' AND artid IN ($liste_artid) $where");	
		}else{
			$nom_fic = "export_kelkoo$siteid.txt";
			$articles = $DB_site->query("SELECT * FROM article INNER JOIN article_site USING(artid) WHERE prix > 0 AND siteid='$siteid' $where ORDER BY artid");
		}
		while($article=$DB_site->fetch_array($articles)) {
			$export = 1;
			if ($aff_inactive == 0){
				$categs = $DB_site->query("SELECT catid FROM position WHERE artid = '$article[artid]'");
				while($categ=$DB_site->fetch_array($categs)){
					$catid = $categ[catid];
					$export = 1;
					while ($catid != 0 && $export == 1){
						$categ2 = $DB_site->query_first("SELECT * FROM categorie INNER JOIN categorie_site USING(catid) WHERE catid = '$catid' AND siteid = '$siteid'");
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
				$cheminsanshref = "";
				$separateur_navigation = " > ";
				cheminsanshref($article[catid],$DB_site);
				$cheminsanshref = secure_chaine_csv($cheminsanshref, 0);
				$contenu .= $cheminsanshref."\t" ; 
				
				$article[libelle] = secure_chaine_csv($article[libelle], 1);
				$article[description] = secure_chaine_csv($article[description], 1);
				
				$contenu.="http://$host/".$regleurlrewrite[$site[siteid]][article]."-".url_rewrite($article[libelle])."-".$article[artid].".htm\t";
				
				$lib = explode("<>",wordwrap($article[libelle],80, "<>", 1));
				$lib = $lib[0];
				$contenu.= $lib."\t";
				
				$desc = explode("<>",wordwrap($article[description],160, "<>", 1));
				$desc = $desc[0];
				if ($desc=="")
					$desc="Pas de description pour cet article";
				$contenu.=$desc."\t";
				if (estEnPromo($DB_site, $article[artid],0)) {
					$temp = number_format($article[prix] * (1 - ($article[pctpromo] / 100)), 2, '.', '') ;
				} else {
					$temp = number_format($article[prix],2,'.','') ;
				}
				$contenu.=$temp."\t";
				$contenu.=$article[artid]."\t";
				if ($article[image] != "")
					$contenu.="http://$host/ar-".url_rewrite($article[libelle])."-".$article[artid].".".$article[image]."\t";
				else
					$contenu.="\t";
					
				$contenu.="001\t";
	
				$top = array("artid" => $article[artid], "prix" => $temp, "poids" => $article[poids]);
				$topFp = trouve_articleFraisPort($DB_site, $top, "", "", 0) ;
							
				$fraisport = formaterPrix($topFp[fraisport] , 2, '.', '') ;	
				$contenu.=$fraisport." €\t";
				$contenu.=$article[ecotaxe];
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
		
		$contenu = exporter_kelkoo($site[siteid]);
		$nom_fic = "export_".$nom_comparateur.$site[siteid].".txt";
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
			$contenu = exporter_kelkoo($site[siteid]);
			$nom_fic = "export_".$nom_comparateur."_selection".$site[siteid].".txt";
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