<?
ini_set('memory_limit','128M');
ini_set('max_execution_time','0');

$prov = "comp" ;
if (!$rootpath)
	$rootpath = $_SERVER['DOCUMENT_ROOT'] ;
$nom_comparateur = "comparateur_clubic";


if(!$onestdansuncron){
	require_once $rootpath."admin/includes/admin_global.php";
} else {
	//require_once $rootpath."admin/includes/fonctions.php";
}

if (!function_exists("exporter_clubic")) {
	function exporter_clubic($siteid){	
		global $DB_site, $libelle,$host,$provenance,$regleurlrewrite,$tab,$datedujour,$modules,$multilangue;
		
		$where = "";
		if ($tab[exporter_non_visibles] == 0){
			$where .= "AND activeV1 = '1'";
		}
		if ($tab[exporter_non_commandables] == 0){
			$where .= "AND commandable = '1'";
		}
		if ($tab[exporter_cats_non_visibles] == 0){
			$aff_inactive = 0;
		}else{
			$aff_inactive = 1;	
		}
			
		$liste_artid = "SELECT artid FROM article_comparateur WHERE comparateurid = '4' AND siteid = '$siteid' ORDER BY artid";	
		$contenu="Numéro unique|nom du produit|prix|frais de port|délai de livraison|type de produit|adresse de la page d'achat du produit|En stock|image produit";
		if (isset($provenance) and $provenance == "selection"){
			$articles = $DB_site->query("SELECT * FROM article INNER JOIN article_site USING(artid) WHERE prix > 0 AND siteid='$siteid' AND artid IN ($liste_artid) $where");	
		}else{
			$articles = $DB_site->query("SELECT * FROM article INNER JOIN article_site USING(artid) WHERE prix > 0 AND siteid='$siteid' $where ORDER BY artid");
		}
			
		while ($article = $DB_site->fetch_array($articles)) {
			$export = 1;
			if ($aff_inactive == 0){
				$categs = $DB_site->query("SELECT catid FROM position WHERE artid = '$article[artid]'");
				while($categ=$DB_site->fetch_array($categs)){
					$catid = $categ[catid];
					$export = 1;
					while ($catid != 0 && $export == 1){
						$categ2=$DB_site->query_first("SELECT * FROM categorie WHERE catid = '$catid'");
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
				$article[libelle] = secure_chaine_csv($article[libelle], 1);
				
				$categorie=$DB_site->query_first("SELECT * FROM categorie INNER JOIN categorie_site USING(catid) WHERE siteid='$siteid' AND catid = '$article[catid]'");
				$categorie[libelle] = secure_chaine_csv($categorie[libelle], 1);
			
				$contenu.="\n";	
				$contenu.=$article[artid]; //identifiant article
				$contenu.="|";
				$contenu.=$article[libelle]; // libelle article
				$contenu.="|";
				
				$datedujour = date("Y-m-d");
				if (estEnPromo($DB_site, $article[artid],0)) {	
					$prix = $article[prix] * (1 - ($article[pctpromo] / 100)) ;
					$prixVente = $prix;
					$prix = formaterPrix($prix);
				}else{
					$prix = formaterPrix($article[prix]) ;
					$prixVente = $article[prix];
				}
				
				$contenu.= $prix; // prix article
				$contenu.="|";
				
				// frais de port
				
				$top = array("artid" => $article[artid], "prix" => $prixVente, "poids" => $article[poids]);
				$topFp = trouve_articleFraisPort($DB_site, $top, "", "", 0) ;
				$contenu.=round($topFp[fraisport]).";";
				
				$paysid=$tab[paysid]; //pays de domiciliation de l'entreprise
				$sql_mode = "SELECT modelivraisonid, activeV1 AS active FROM mode_livraison INNER JOIN mode_livraison_site USING(modelivraisonid) WHERE (activeV1='1' OR activeV1M='1') AND modelivraisonid IN (SELECT distinct(modelivraisonid) FROM fraisport WHERE paysid = '$paysid' AND prix > 0) ORDER BY position LIMIT 1";
				$mode=$DB_site->query_first($sql_mode);
				
				if ($tab[type_tranches_port] == 0) // tranches de poids
					$valeuratester = $article[poids] ;
				else // tranches de prix
					$valeuratester = $article[prix] ;	
				$tranche=$DB_site->query_first("SELECT MIN(prix) as prix FROM fraisport WHERE modelivraisonid = '$mode[modelivraisonid]' AND paysid = '57' AND debut <= '$valeuratester' AND fin >= '$valeuratester'");
				if ($tranche[prix] && $tranche[prix] != NULL)
					$article[prixport] = $tranche[prix] ;
				else{
					$tranche=$DB_site->query_first("SELECT MIN(prix) as prix FROM fraisport WHERE modelivraisonid = '$mode[modelivraisonid]' AND  paysid = '57' AND debut = '0' AND fin = '0'");
					if ($tranche[prix] != NULL)
						$article[prixport] = $tranche[prix] ;
				}
				$contenu.=round($article[prixport]); 
				
				$contenu.="|";
				if ($article[delai])
					$contenu.= $article[delai]." ".$multilangue[jour]."(s)"; // delais de livraison	
				else
					$contenu.="2 jours"; // delais de livraison	
				$contenu.="|";
				$contenu.=$categorie[libelle]; // type de produit
				$contenu.="|";
				$contenu.="http://$host/".$regleurlrewrite[$site[siteid]][article]."-".url_rewrite($article[libelle])."-".$article[artid].".htm";	
				$contenu.="|";
				$contenu.="1"; // en stock
				$contenu.="|";
				if($article[image] != "")
					$contenu.="http://$host/ar-".url_rewrite($article[libelle])."-".$article[artid].".".$article[image];			
				else
					$contenu.=""; // img produit	
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
		$contenu=exporter_clubic($site[siteid]);
		$nom_fic = "export_".$nom_comparateur.$site[siteid].".txt";
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
				echo "$multilangue[adresse_fichier] : <a href=\"http://$host/configurations/$host/exports/$nom_fic\">http://$host/configurations/$host/exports/$nom_fic</a><br>";
			}
		}
		
		$articleselection = $DB_site->query("SELECT * FROM article_comparateur WHERE siteid=$site[siteid] AND comparateurid=$comparateurid[comparateurid]");
		if($DB_site->num_rows($articleselection)){
			$provenance = "selection";
			$contenu=exporter_clubic($site[siteid]);
			$nom_fic = "export_" . $nom_comparateur . "_selection$site[siteid].txt";
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
					echo "$multilangue[adresse_fichier] : <a href=\"http://$host/configurations/$host/exports/$nom_fic\">http://$host/configurations/$host/exports/$nom_fic</a><br>";
				}
			}
		}
	}
}

?>