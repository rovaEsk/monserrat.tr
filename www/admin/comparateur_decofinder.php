<?
ini_set('memory_limit','128M');
ini_set('max_execution_time','0');


$prov = "comp" ;
if (!$rootpath)
	$rootpath = $_SERVER['DOCUMENT_ROOT'] ;
$nom_comparateur = "comparateur_decofinder";

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
		$categorie=$DB->query_first("SELECT parentid, libelle, attributid FROM googleshopping_attribut WHERE attributid = '$categorie'");
		$cheminsanshref = $categorie[libelle].$cheminsanshref;
		if ($categorie[parentid] != 0){
			$cheminsanshref = $separateur_navigation.$cheminsanshref ;
			cheminsanshref2($categorie[parentid], $DB) ;
		}
		return $cheminsanshref ;
		}
	}
/*	
if (!function_exists("supprcarspe")){
	function supprcarspe($chaine){	
		$chaine_finale = "";
		for ($i=0;$i<strlen($chaine);$i++){
			if (ord($chaine[i]) != 38 && ord($chaine[i]) != 60 && ord($chaine[i]) != 62 && ord($chaine[i]) != 146 && ord($chaine[i]) < 250 && ord($chaine[i]) > 31 && ord($chaine[i]) != 128){
				$chaine_finale .= $chaine[i];
			}elseif (ord($chaine[i]) == 128){
				$chaine_finale .= "euro";
			}
		}
		return $chaine_finale;
	}
}*/

if (!function_exists("exporter_decofinder")) {
	function exporter_decofinder($siteid){	
		global $DB_site, $libelle,$host,$provenance,$regleurlrewrite,$cheminsanshref,$modules, $tab, $ajd, $nom_site, $description_site;
		$title = "title".$siteid;
		global ${$title};
		$liste_artid = "SELECT artid FROM article_comparateur WHERE comparateurid = '23' AND siteid = '$siteid' ORDER BY artid";
		$nom_site = $DB_site->query_first("SELECT * FROM meta INNER JOIN meta_site USING(metaid) WHERE scriptname='/index.php'");
		$nom_site = $nom_site[t];
		$nom_site = str_replace("[boutiquetitre]", ${$title}, $nom_site);
		$contenu="<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
		
		$where = "";		
		if ($tab[exporter_non_visibles] == 0){
			$where .= "AND activeV1 = '1' ";
		}
		if ($tab[exporter_non_commandables] == 0){
			$where .= "AND commandable = '1' ";
		}
		$aff_inactive=$tab[exporter_cats_non_visibles];
		
		$contenu.="<Produits>\n";

		if (isset($provenance) and $provenance == "selection"){
			$articles = $DB_site->query("SELECT * FROM article INNER JOIN article_site USING(artid) WHERE siteid='$siteid' AND prix > 0 AND artid IN ($liste_artid) $where");	
		}else{
			$articles = $DB_site->query("SELECT * FROM article INNER JOIN article_site USING(artid) WHERE siteid='$siteid' AND prix > 0 $where ORDER BY artid");
		}
		
		while($article = $DB_site->fetch_array($articles)){
			
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
	
		//	if ($export && (!in_array(4, $modules) || (retournerStockArticle($DB_site, $article[artid]) > 0))){
			if ($export){
			
				//Marque
				$article[marque] = "";
				$tab[separateur_marques] = ", ";
				$article_marques = $DB_site->query("SELECT * FROM marque INNER JOIN marque_site USING ( marqueid ) INNER JOIN article_marque USING ( marqueid ) WHERE artid = '$article[artid]' AND siteid = '$siteid' LIMIT 1") ;
				while ($article_marque=$DB_site->fetch_array($article_marques))
					$article[marque] .= $article_marque[libelle] . $tab[separateur_marques] ;
				$article[marque] = substr($article[marque], 0, -strlen($tab[separateur_marques])) ;
				$article[marque] = secure_chaine_csv($article[marque],0);
				$article[description] = secure_chaine_csv($article[description],0);
							
				$fournisseur = $DB_site->query_first("SELECT libelle FROM fournisseur WHERE fournisseurid = '$article[fournisseurid]'");
				$fournisseur[libelle] = secure_chaine_csv($fournisseur[libelle], 1);
				$contenu.="<Produit>\n
					<Reference>$article[artcode]</Reference>\n
					<Marque>$article[marque]</Marque>\n
					<Image>http://$host/ori-".url_rewrite($article[libelle])."-".$article[artid].".".$article[image]."</Image>\n";
				$i=2;
				$images = $DB_site->query("SELECT * FROM articlephoto WHERE artid = '$article{artid]' ORDER BY articlephotoid LIMIT 3 ");
				while($image = $DB_site->fetch_array($images)){
					$contenu.="<Image>http://$host/ori-".url_rewrite($article[libelle])."-".$article[artid]."_$i.".$article[image]."</Image>\n";
					$i++;
				}				
				$categorie = $DB_site->query_first("SELECT * FROM categorie WHERE catid='$article[catid]'");
				$categorie[libelle] = secure_chaine_csv($categorie[libelle],1);
				$article[libelle] = secure_chaine_csv($article[libelle],1);
				$article[libelle] = explode("<>",wordwrap($article[libelle]));
				$article[libelle] = $article[libelle][0];
                $contenu.="<TypeProduit>$categorie[libelle]</TypeProduit>\n
                <Modele>$article[libelle]</Modele>\n
                <Collection />\n
                <Date_Nouveaute/>\n
                <Designer>$fournisseur[libelle]</Designer>\n";
                if ($tab['comparateurDecofinderCaractidMatiere']>0){
					$materiau = $DB_site->query("SELECT * FROM caracteristiquevaleur cv INNER JOIN article_caractval acv ON cv.caractvalid = acv.caractvalid WHERE acv.artid = '$article[artid]' AND cv.caractid = '$tab[comparateurDecofinderCaractidMatiere]' LIMIT 3");
					while ($matiere = $DB_site->fetch_array($materiau)){
						$matiere[libelle] = secure_chaine_csv($matiere[libelle],1);
						$contenu .= "<Materiau>$matiere[libelle]</Materiau>\n";					
					}
				}
				if ($tab['comparateurDecofinderCaractidCouleur']>0){
					$couleur = $DB_site->query_first("SELECT * FROM caracteristiquevaleur cv INNER JOIN article_caractval acv ON cv.caractvalid = acv.caractvalid WHERE acv.artid = '$article[artid]' AND cv.caractid = '$tab[comparateurDecofinderCaractidCouleur]'");
					$couleur[libelle] = secure_chaine_csv($couleur[libelle],1);
					$contenu .= "<Couleur>$couleur[libelle]</Couleur>\n";					
				}
				if ($tab['comparateurDecofinderCaractidStyle']>0){
					$style = $DB_site->query_first("SELECT * FROM caracteristiquevaleur cv INNER JOIN article_caractval acv ON cv.caractvalid = acv.caractvalid WHERE acv.artid = '$article[artid]' AND cv.caractid = '$tab[comparateurDecofinderCaractidStyle]'");
					$style[libelle] = secure_chaine_csv($style[libelle],1);
					$contenu .= "<Style>$style[libelle]</Style>\n";					
				}
				if ($tab['comparateurDecofinderCaractidMotif']>0){
					$motif = $DB_site->query_first("SELECT * FROM caracteristiquevaleur cv INNER JOIN article_caractval acv ON cv.caractvalid = acv.caractvalid WHERE acv.artid = '$article[artid]' AND cv.caractid = '$tab[comparateurDecofinderCaractidMotif]'");
					$motif[libelle] = secure_chaine_csv($motif[libelle],1);
					$contenu .= "<Motif>$motif[libelle]</Motif>\n";					
				}
				if ($tab['comparateurDecofinderCaractidOrigine']>0){
					$pays = $DB_site->query_first("SELECT * FROM caracteristiquevaleur cv INNER JOIN article_caractval acv ON cv.caractvalid = acv.caractvalid WHERE acv.artid = '$article[artid]' AND cv.caractid = '$tab[comparateurDecofinderCaractidOrigine]' ");
					$pays[libelle] = secure_chaine_csv($pays[libelle],1);
					$contenu .= "<Pays>$pays[libelle]</Pays>\n";					
				}
				
                $contenu.="<Prix>".str_replace('.',',',$article[prix])."</Prix>\n";
				if(strtotime($article[datedebut])>=time() && strtotime($article[datefin])>time() ){
					$contenu .= "<Date_Promotion>".date('d/m/Y',strtotime($article[datefin]))."</Date_Promotion>\n
					<Prix_Promotion>".round($article[prix] - ($article[prix]*$article[pctpromo]/100),2)."</Prix_Promotion>\n";
				}else{
					$contenu .= "<Date_Promotion />\n<Prix_Promotion />\n";				
				}
				$contenu.="<Url_Page_Produit>http://$host/".$regleurlrewrite[$siteid][article]."-".url_rewrite($article[libelle])."-".$article[artid].".htm</Url_Page_Produit>\n";
				$article[description] = secure_chaine_csv($article[description],1);
				$contenu.="<Description>$article[description]</Description>\n";

				$contenu.="</Produit>\n";
			}
		}
		$contenu.="</Produits>\n";
		return $contenu;
	}
}
	
if(!$onestdansuncron){
/*$allEntities = get_html_translation_table(HTML_ENTITIES, ENT_NOQUOTES);
$specialEntities = get_html_translation_table(HTML_SPECIALCHARS, ENT_NOQUOTES); 
print_r($allEntities);
print_r($specialEntities);*/
	$sites = $DB_site->query("SELECT * FROM site");
	$comparateurid = $DB_site->query_first("SELECT comparateurid FROM comparateur WHERE fichier='".$nom_comparateur.".php'");
	
	while($site = $DB_site->fetch_array($sites)){
		$provenance = "";
	
		$contenu=exporter_decofinder($site[siteid]);
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
			$contenu=exporter_decofinder($site[siteid]);
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
		}
		
	}
}



?>