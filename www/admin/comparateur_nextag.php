<?

$prov = "comp" ;
if (!$rootpath)
	$rootpath = $_SERVER['DOCUMENT_ROOT'] ;
$nom_comparateur = "comparateur_nextag";


if($onestdansuncron=="" or $onestdansuncron==0){
	require_once $rootpath."admin/includes/admin_global.php";
} else {
	//require_once $rootpath."include/fonction_catalogue.php";
}

if (!function_exists("exporter_nextag")) {
	function exporter_nextag($siteid){	
		global $DB_site, $libelle,$host,$provenance,$regleurlrewrite,$tab,$datedujour,$modules,$multilangue;
		
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
			
		$liste_artid = "SELECT artid FROM article_comparateur WHERE comparateurid = '20' AND siteid = '$siteid' ORDER BY artid";	
		$contenu="Fabricant|Nom du produit|Description du produit|Lien site marchand|Prix|Ancien prix|Catégorie : Autre format|Catégorie : Identifiant numérique Nextag|URL image|Livraison|Disponibilité|Etat du produit|Poids|EAN|Identifiant du distributeur";
		if (isset($provenance) and $provenance == "selection"){
			$articles = $DB_site->query("SELECT * FROM article INNER JOIN article_site USING(artid) WHERE siteid='$siteid' AND prix > 0 AND artid IN ($liste_artid) $where");	
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
				$article[libelle] = secure_chaine_csv($article[libelle], 1);
				
				$contenu.="\n";
				
				//Fabricant
				$article[marque] = "";
				$tab[separateur_marques] = ", ";
				$article_marques = $DB_site->query("SELECT * FROM marque INNER JOIN marque_site USING ( marqueid ) INNER JOIN article_marque USING ( marqueid ) WHERE artid = '$article[artid]' AND siteid = '$siteid' LIMIT 1") ;
								while ($article_marque=$DB_site->fetch_array($article_marques))
					$article[marque] .= $article_marque[libelle] . $tab[separateur_marques] ;
				$article[marque] = substr($article[marque], 0, -strlen($tab[separateur_marques])) ;
				$article[marque] = secure_chaine_csv($article[marque],0);
				
				$contenu.=$article[marque];
				$contenu.="|";
				
				//Nom du produit
				$contenu.=$article[libelle]; // libelle article
				$contenu.="|";
				
				//Description du produit
				$article[description] = secure_chaine_csv($article[description], 1);
				$contenu.=$article[description]; 
				$contenu.="|";
				
				//Lien site marchand
				$contenu.="http://$host/".$regleurlrewrite[$siteid][article]."-".url_rewrite($article[libelle])."-".$article[artid].".htm";	
				$contenu.="|";
				
				//Prix
				$datedujour = date("Y-m-d");
				if (estEnPromo($DB_site, $article[artid],0)) {					
					if ($tab[exporter_prixpublic] == 1){
						$ancienprix  = formaterPrix($article[prixpublic]);
					}else{
						$ancienprix = formaterPrix($article[prix]) ;
					}
					$prix = $article[prix] * (1 - ($article[pctpromo] / 100)) ;
					$prixVente = $prix;
					$prix = formaterPrix($prix);
				}else{
					if ($tab[exporter_prixpublic] == 1){
						$ancienprix = formaterPrix($article[prixpublic]);
					}else{
						$ancienprix = 0 ;
					}
					$prix = formaterPrix($article[prix]) ;
					$prixVente = $article[prix];
				}			
				$contenu.= "€ ".$prix; // prix article
				$contenu.="|";
				
				//Ancien prix
				$contenu.= "€ ".$ancienprix; // prix barre article
				$contenu.="|";
							
				//Cat�gorie : Autre format
				$categorie=$DB_site->query_first("SELECT * FROM categorie INNER JOIN categorie_site WHERE categorie.catid = '$article[catid]'");
				$categorie[libelle] = secure_chaine_csv($categorie[libelle]);
				$contenu.=$categorie[libelle]; // libelle categ
				$contenu.="|";
				
				//Cat�gorie : Identifiant num�rique Nextag
				$nextagid = $DB_site->query_first("SELECT * FROM nextag INNER JOIN nextag_attribut ON attributid = catnextagid WHERE artid = '$article[artid]'");
				if($nextagid[categorieid]){
					$contenu.=$nextagid[categorieid];			
				}
				$contenu.="|";	
				
				//URL image
				if($article[image] != ""){
					$contenu.="http://$host/ar-".url_rewrite($article[libelle])."-".$article[artid].".".$article[image];
				}				
				$contenu.="|";
				
				//Livraison
				$top = array("artid" => $article[artid], "prix" => $prixVente, "poids" => $article[poids]);
				$topFp = trouve_articleFraisPort($DB_site, $top, "", "", 0) ;
				$contenu.="€".round($topFp[fraisport]);
				$contenu.="|";		
				
				//Disponibilit�
				if($nbstock > 0){
					$contenu.= "Oui"; 				
				}else{
					$contenu.= "Non"; 
				}
				$contenu.="|";
				
				//Etat du produit
				$contenu.="Neuf|";
				
				//Poids
				if ($article[poids]){
					$contenu.=$article[poids];
				}
				$contenu.="|";
				
				//EAN
				$contenu.=$article[code_EAN]; 
				$contenu.="|";
				
				//Identifiant du distributeur
				$contenu.=$article[artid]; 
				$contenu.="|";
				
				// frais de port
				
				/*$top = array("artid" => $article[artid], "prix" => $prixVente, "poids" => $article[poids]);
				$topFp = trouve_articleFraisPort($DB_site, $top, "", "", 0) ;
				$contenu.=round($topFp[fraisport]).";";
				
				$paysid=$tab[paysid]; //pays de domiciliation de l'entreprise
				$sql_mode = "SELECT modelivraisonid, activeV1 AS active FROM mode_livraison WHERE activeV1='1' AND modelivraisonid IN (SELECT distinct(modelivraisonid) FROM fraisport WHERE paysid = '$paysid' AND prix > 0) ORDER BY position LIMIT 1";
				$mode=$DB_site->query_first($sql_mode);
				
				if ($tab[type_tranches_port] == 0) // tranches de poids
					$valeuratester = $article[poids] ;
				else // tranches de prix
					$valeuratester = $article[prix] ;	
				$tranche=$DB_site->query_first("select MIN(prix) as prix from fraisport where modelivraisonid = '$mode[modelivraisonid]' AND paysid = '57' and debut <= '$valeuratester' and fin >= '$valeuratester'");
				if ($tranche[prix] && $tranche[prix] != NULL)
					$article[prixport] = $tranche[prix] ;
				else{
					$tranche=$DB_site->query_first("select MIN(prix) as prix from fraisport where modelivraisonid = '$mode[modelivraisonid]' AND  paysid = '57' and debut = '0' and fin = '0'");
					if ($tranche[prix] != NULL)
						$article[prixport] = $tranche[prix] ;
				}
				$contenu.=round($article[prixport]); */
				
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
		
		$contenu=exporter_nextag($site[siteid]);
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
			$contenu=exporter_nextag($site[siteid]);
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