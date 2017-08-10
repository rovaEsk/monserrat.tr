<?
ini_set('memory_limit','128M');
ini_set('max_execution_time','0');

$prov = "comp" ;
if (!$rootpath)
	$rootpath = $_SERVER['DOCUMENT_ROOT'] ;
$nom_comparateur = "comparateur_aujardin";

if(!$onestdansuncron){
	require_once $rootpath."admin/includes/admin_global.php";
} else {
	//require_once $rootpath."admin/includes/fonctions.php";
}

if (!function_exists("cheminsanshref")) {
	function cheminsanshref($categorie, $DB_site) {
		global $libelle, $separateur_navigation, $titleFR, $host, $cheminsanshref ;
		$ss=$DB_site->query_first("SELECT COUNT(catid) FROM categorie WHERE parentid = '$categorie'");
		$categorie=$DB_site->query_first("SELECT * FROM categorie INNER JOIN categorie_site USING(catid) WHERE catid = '$categorie'");
		$cheminsanshref = " $separateur_navigation $categorie[$libelle] $cheminsanshref" ;
		if ($categorie[parentid] != 0)
			cheminsanshref($categorie[parentid], $DB_site) ;
		else
			$cheminsanshref = "$titleFR $cheminsanshref" ;
		return $cheminsanshref ;
		}
	}

if (!function_exists("exporter_aujardin")) {
	function exporter_aujardin($siteid){	
		global $DB_site, $libelle, $host, $provenance, $tab, $regleurlrewrite, $cheminsanshref, $datedujour, $modules;
		$liste_artid = "SELECT artid from article_comparateur where comparateurid = '10' AND siteid = '$siteid' ORDER BY artid";
		$contenu="Identifiant|Catégorie|Titre|Description|Prix TTC|URL produit|URL photo";
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
		if (isset($provenance) and $provenance == "selection") {
			$nom_fic = "export_aujardin_selection".$siteid.".txt";
			$articles=$DB_site->query("SELECT * FROM article INNER JOIN article_site USING(artid) LEFT OUTER JOIN categorie USING(catid) WHERE prix > 0 AND siteid='$siteid' AND artid IN ($liste_artid) $where");	
		} else {
			$nom_fic = "export_aujardin".$siteid.".txt";
			$articles=$DB_site->query("SELECT * FROM article INNER JOIN article_site USING(artid) LEFT OUTER JOIN categorie USING(catid) WHERE prix > 0 AND siteid='$siteid' $where ORDER BY artid");
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
						if ($categ2[visible_treeviewV1] == 0){
							$export = 0;	
						}
						$catid = $categ2[parentid];
					}
				}
			}
			if ($tab[exporter_stock_epuise] == 0 && in_array(4, $modules) && retournerStockArticle($DB_site, $article[artid]) <= 0 && $article[stock_illimite] != 1){
				$export = 0;
			}
			if ($export == 1){
				$article[description] = secure_chaine_csv($article[description], 1);
				$article[libelle] = secure_chaine_csv($article[libelle], 1);
			
				$article[urlimage] = "" ;
				if ($article[image] != "")
					$article[urlimage].="http://$host/ar-".url_rewrite($article[$libelle])."-".$article[artid].".".$article[image];
				$contenu.="\n";	
				$contenu.=$article[artid];
				$contenu.="|";
				$cheminsanshref = "";
				$separateur_navigation = " > ";
				cheminsanshref($article[catid],$DB_site);
				$cheminsanshref = secure_chaine_csv($cheminsanshref);
				$contenu.=$cheminsanshref;
				$contenu.="|";
				$contenu.=$article[libelle];
				$contenu.="|";
				$contenu.=$article[description];
				$contenu.="|";
				if (estEnPromo($DB_site, $article[artid],0)) {
					$article[prix] = formaterPrix($article[prix] * (1 - ($article[pctpromo] / 100)), 2, ",", "") ;
				}
				$contenu.=$article[prix];
				$contenu.="|";
				$contenu.="http://$host/".$regleurlrewrite[$siteid][article]."-".url_rewrite($article[$libelle])."-".$article[artid].".htm";
				$contenu.="|";
				$contenu.="$article[urlimage]";
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
		$contenu = exporter_aujardin($site[siteid]);
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
			$contenu = exporter_aujardin($site[siteid]);
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
				if (fwrite($handle,$contenu) === FALSE) {
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