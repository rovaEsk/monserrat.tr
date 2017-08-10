<?
ini_set('memory_limit','128M');
ini_set('max_execution_time','0');

$prov = "comp" ;
if (!$rootpath)
	$rootpath = $_SERVER['DOCUMENT_ROOT'] ;
$nom_comparateur = "comparateur_bonlot";

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
		$cheminsanshref = " $separateur_navigation $categorie[$libelle] $cheminsanshref" ;
		if ($categorie[parentid] != 0)
			cheminsanshref($categorie[parentid], $DB) ;
		else
			$cheminsanshref = "$titleFR $cheminsanshref" ;
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
}*/

if (!function_exists("exporter_bonlot")) {
	function exporter_bonlot($siteid){	
		global $DB_site, $select, $i, $nom_comparateur, $libelle,$host,$provenance,$regleurlrewrite,$cheminsanshref,$modules, $tab, $ajd, $nom_site, $description_site;
		$title = "title".$siteid;
		global ${$title};
		$liste_artid = "SELECT artid FROM article_comparateur WHERE comparateurid = '15' AND siteid = '$siteid' ORDER BY artid";
		$nom_site = $DB_site->query_first("SELECT * FROM meta INNER JOIN meta_site USING(metaid) WHERE scriptname='/index.php'");
		$nom_site = $nom_site[t];
		$nom_site = str_replace("[boutiquetitre]", ${$title}, $nom_site);
		$description_site = $DB_site->query_first("SELECT * FROM meta INNER JOIN meta_site USING(metaid) WHERE scriptname='/index.php'");
		$description_site = $description_site[t];
		$description_site = str_replace("[boutiquetitre]", ${$title}, $description_site);
		$contenu="url produit; id produit; nom produit; catégorie texte; url image; descriptif court; prix ttc; descriptif long; stock; code ean; ecotaxe ttc; gamme marque; modele marque; marque; id2 produit; url image1; url image2; url image3; url image4; cout port; délais port; prix remisé ttc;\n";
		
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
			$nom_fic = $nom_comparateur."_selection".$siteid.".csv";
			$articles=$DB_site->query("SELECT * FROM article INNER JOIN article_site USING(artid) LEFT OUTER JOIN categorie USING(catid) WHERE prix > 0 AND siteid='$siteid' AND artid IN ($liste_artid) $where");	
		}else{
			$nom_fic = $nom_comparateur.$siteid.".csv";
			$articles=$DB_site->query("SELECT * FROM article INNER JOIN article_site USING(artid) LEFT OUTER JOIN categorie USING(catid) WHERE siteid='$siteid' AND prix > 0 $where ORDER BY artid");
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
				//Marque
				$article[marque] = "";
				$tab[separateur_marques] = ", ";
				$article_marques = $DB_site->query("SELECT * FROM marque INNER JOIN marque_site USING ( marqueid ) INNER JOIN article_marque USING ( marqueid ) WHERE artid = '$article[artid]' AND siteid = '$siteid' LIMIT 1");
				while ($article_marque=$DB_site->fetch_array($article_marques))
					$article[marque] .= $article_marque[libelle] . $tab[separateur_marques] ;
				$article[marque] = substr($article[marque], 0, -strlen($tab[separateur_marques])) ;
				$article[marque] = secure_chaine_csv($article[marque],0);
				
				$contenu.="\"http://$host/".$regleurlrewrite[$siteid][article]."-".url_rewrite($article[$libelle])."-".$article[artid].".htm\";";		
				$contenu.="\"".$article[artid]."\";";
				$article[libelle] = secure_chaine_csv($article[libelle],1);
				$contenu.= "\"".$article[libelle]."\";";	
				// Catégories
				$cheminsanshref = "";
				$separateur_navigation = " > ";
				cheminsanshref($article[catid], $DB_site);
				$cheminsanshref = secure_chaine_csv($cheminsanshref, 0);
				$contenu.="\"".$cheminsanshref."\";";
				
				
				$contenu.="\"http://$host/ori-".url_rewrite($article[$libelle])."-".$article[artid].".".$article[image]."\";";
					
				$article[description] = secure_chaine_csv($article[description],1);
				$desc = explode("<>",wordwrap($article[description],200, "<>", 1));
				$desc = $desc[0];
				$desc_long = explode("<>",wordwrap($article[description],1450, "<>", 1));
				$desc_long = $desc_long[0];

				$contenu.="\"".strtolower($desc)."\";";
				if (estEnPromo($DB_site, $article[artid],0)) {
					$prix_promo = round($article[prix] - ($article[prix]*$article[pctpromo]/100),2);
					$prix = $article[prix];
					$prixVente = $prix_promo;
				} else {
					if ($tab[exporter_prixpublic] == 1){
						$prix_promo = $article[prixpublic];
					}
					$prix = $article[prix];
					$prixVente = $prix;
				}
				$contenu.="\"".$prix."\";";
				
				$contenu.="\"".strtolower($desc_long)."\";";
				$contenu.="\"".$nbstock."\";";
				$contenu.= "\"".$article[code_EAN]."\";";
				$contenu.="\"".$article[ecotaxe]."\";";
				$contenu.="\"\";";
				$contenu.="\"\";";
				$contenu.="\"".$article[marque]."\";";
				$contenu.="\"\";";
				$nbphoto = 0;
				$photos=$DB_site->query("SELECT * FROM articlephoto WHERE artid='$article[artid]' ORDER BY position limit 4") ;
				while ($photo=$DB_site->fetch_array($photos)){
					$nbphoto++;
					$contenu.="\"http://$host/ori-".url_rewrite($article[$libelle])."-".$article[artid]."_".$photo[articlephotoid].".".$photo[image]."\";";
				}
				if ($nbphoto < 4){
					for ($iphoto=4;$iphoto>$nbphoto;$iphoto--){
						$contenu.="\"\";";
					}
				}
				
				$top = array("artid" => $article[artid], "prix" => $prixVente, "poids" => $article[poids]);
				$topFp = trouve_articleFraisPort($DB_site, $top, "", "", 0) ;
						
				$fraisport = number_format($topFp[fraisport], 2, '.', '') ;	
				$contenu .= "\"".$fraisport."\";";				
				$contenu.="\"".$article[delai]."\";";
				$contenu.="\"".$prix_promo."\";";
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
	
		$contenu=exporter_bonlot($site[siteid]);
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
				echo "$multilangue[adresse_fichier] : <a href=\"http://$host/configurations/$host/exports/$nom_fic\">http://$host/configurations/$host/exports/$nom_fic</a><br>";
			}
		}
		
		$articleselection = $DB_site->query("SELECT * FROM article_comparateur WHERE siteid=$site[siteid] AND comparateurid=$comparateurid[comparateurid]");
		if($DB_site->num_rows($articleselection)){
			$provenance = "selection";
			$contenu=exporter_bonlot($site[siteid]);
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
					echo "$multilangue[adresse_fichier] : <a href=\"http://$host/configurations/$host/exports/$nom_fic\">http://$host/configurations/$host/exports/$nom_fic</a><br>";
				}
			}
		}
	}
}
?>