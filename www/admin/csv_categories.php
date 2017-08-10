<?php
/**
* Export personnalisé par sélection du catalogue
*/
	
ini_set('memory_limit','512M');
ini_set('max_execution_time','0');
set_time_limit(0);

require "includes/admin_global.php";

//construction des titres des colonnes
$contenu="";
/*print_r($_POST);
echo "*$categorie_catid / $exporterTitre*";
exit;*/


$nb_sites_select=0;
foreach ($critereSite as $siteid => $value){	
	$tab_siteid[$siteid]=$siteid;
	$nb_sites_select++;	
}
/*
if($critereSite != ""){
	$sites = $DB_site->query("SELECT * FROM site WHERE siteid='$critereSite' ORDER BY siteid");
	while($site = $DB_site->fetch_array($sites)){
		$tab_siteid[$site[siteid]]=$site[siteid];
	}
}else{
	$sites = $DB_site->query("SELECT * FROM site ORDER BY siteid");
	while($site = $DB_site->fetch_array($sites)){
		$tab_siteid[$site[siteid]]=$site[siteid];
	}
}
*/
if ($exporterTitre == 1){

	//Identifiant Catégorie
	if ($categorie_catid != "") $contenu.="$multilangue[identifiant_categorie];";
		
	//Libellés
	foreach ($tab_siteid as $siteid){
		if (${'categorie_libelle'.$siteid} != "") $contenu.=${'categorie_libelle'.$siteid}.";";
	}
	
	//Descriptions
	foreach ($tab_siteid as $siteid){
		if (${'categorie_description'.$siteid} != "") $contenu.=${'categorie_description'.$siteid}.";";
	}
	
	//Balises title
	foreach ($tab_siteid as $siteid){
		if (${'categorie_ref_title'.$siteid} != ""){
			$contenu.=${'categorie_ref_title'.$siteid}.";";
		}
	}
	
	//Balises meta desc
	foreach ($tab_siteid as $siteid){
		if (${'categorie_ref_description'.$siteid} != "") {
			$contenu.=${'categorie_ref_description'.$siteid}.";";
		}
	}
	
	//Balises meta key
	foreach ($tab_siteid as $siteid){
		if (${'categorie_ref_keywords'.$siteid} != ""){
			$contenu.=${'categorie_ref_keywords'.$siteid}.";";
		}
	}
	
	//module couleur de rayon
	if ($categorie_color != "") $contenu.="$multilangue[couleur];";
	
	//module couleur de fond du rayon
	if ($categorie_color_back != "") $contenu.="$multilangue[couleur_fond];";
	
	//module couleur de survol du rayon
	if ($categorie_color_survol != "") $contenu.="$multilangue[couleur_survol];";
	
	//URL's
	foreach ($tab_siteid as $siteid){
		if (${'categorie_url'.$siteid} != "") $contenu.=${'categorie_url'.$siteid}.";";
	}
	
	//Arborescences
	foreach ($tab_siteid as $siteid){
		if (${'categorie_arborescence'.$siteid} != "") $contenu.=${'categorie_arborescence'.$siteid}.";";
	}

	//Images
	foreach ($tab_siteid as $siteid){
		if (${'categorie_image'.$siteid} != ""){ 
			$contenu.=${'categorie_image'.$siteid}.";";
		}
	}
	
	//module deuxième image de categorie
	foreach ($tab_siteid as $siteid){
		if (${'categorie_image2'.$siteid} != ""){
			 $contenu.=${'categorie_image2'.$siteid}.";";
		}
	}
	
	//module troisième image de categorie
	foreach ($tab_siteid as $siteid){
		if (${'categorie_image3'.$siteid} != ""){
			 $contenu.=${'categorie_image3'.$siteid}.";";
		}
	}
	
	//module background categorie
	if ($categorie_background != "") $contenu.="$multilangue[background];";
		
	
	//Nombre d'articles au total
	if ($categorie_nb_articles != "") $contenu.="$multilangue[nb_articles_total];";
	
	//Nombre d'articles actifs
	foreach ($tab_siteid as $siteid){
		if (${'categorie_articles_actifs'.$siteid} != ""){
			$contenu.=${'categorie_articles_actifs'.$siteid}.";";
		}
	}
		
	//Nombre d'articles commandables
	foreach ($tab_siteid as $siteid){
		if (${'categorie_articles_commandables'.$siteid} != ""){
			$contenu.=${'categorie_articles_commandables'.$siteid}.";";
		}
	}
	
	//Nombre d'articles en stock
	if ($categorie_articles_stock != "") $contenu.="$multilangue[nb_articles_stock];";
	
	//Nombre d'affichages multiples
	if ($categorie_affichage_multiple != "") $contenu.="$multilangue[nb_affichages_multiples];";
	
	$contenu.="\n";
}

$nom_fichier_export = 'categories.csv';

$filename = './export/csv/'.$nom_fichier_export;
//if (is_writable($filename)) {
	if (!$handle = fopen($filename, 'w+')) {
		echo "$multilangue[erreur_ouverture_fichier] ($filename)";
		exit;
	}else{
		// UTF8 pour csv
		fputs($handle, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF)));
		if (fwrite($handle, $contenu) === FALSE) {
			echo "$multilangue[erreur_ecriture_fichier] ($filename)";
			exit;
		}
	}
	
/*}else {
	echo "$multilangue[erreur_accessibilite_ecriture_fichier] ($filename).";
	exit();
}*/

//$handle = fopen($filename, 'a');



//requete en fonction des critères sélectionnés
$where="";
$or="";

if ($critereVisible != ""){	
	$where.=" AND cs.visible_treeviewV1 = '$critereVisible' AND siteid='1'";		
}

if ($critereContientArticle != ""){	
	if($critereContientArticle == 1){
		$where.=" AND cs.articles_actifsV1 >= '$critereContientArticle'";
	}else{
		$where.=" AND cs.articles_actifsV1 = '$critereContientArticle'";
	}	
}

/*if($critereSite){
	$where.=" AND cs.siteid = '$critereSite'";
}*/

if($nb_sites_select == 1){
	foreach ($tab_siteid as $siteid){
		$and_siteid =" AND siteid = '$siteid'";
	}
}

if ($critereOnglet != ""){
	$where.=" AND cs.onglet = '$critereOnglet'";
}

$sql="SELECT cs.*,c.* FROM categorie AS c LEFT JOIN categorie_site AS cs ON c.catid=cs.catid $and_siteid WHERE 1=1 $where GROUP BY c.catid ORDER BY c.catid";
/*echo $sql;
exit;*/
$categories=$DB_site->query($sql);


while($categorie=$DB_site->fetch_array($categories)) {

	foreach ($tab_siteid as $siteid){
		$sql="SELECT * FROM categorie AS c LEFT JOIN categorie_site AS cs ON c.catid=cs.catid AND siteid='$siteid' WHERE c.catid='$categorie[catid]'";
		$categorie_site[$siteid]=$DB_site->query_first($sql);
	}

	$contenu="";

	//Identifiant Catégorie
	if ($categorie_catid != ""){
		$contenu.=$categorie[catid].";";
	}	
	
	//Libellés
	foreach ($tab_siteid as $siteid){
		if (${'categorie_libelle'.$siteid} != ""){
			$contenu.=secure_chaine_csv($categorie_site[$siteid]['libelle']).";";
		}
	}
	
	//Descriptions
	foreach ($tab_siteid as $siteid){
		if (${'categorie_description'.$siteid} != ""){
			$contenu.=secure_chaine_csv($categorie_site[$siteid]['description']).";";
		}
	}
	
	//Balises meta title
	foreach ($tab_siteid as $siteid){
		if (${'categorie_ref_title'.$siteid} != ""){
			$contenu.=secure_chaine_csv($categorie_site[$siteid]['ref_title']).";";
		}
	}
	
	//Balises meta desc
	foreach ($tab_siteid as $siteid){
		if (${'categorie_ref_description'.$siteid} != ""){
			$contenu.=secure_chaine_csv($categorie_site[$siteid]['ref_description']).";";
		}
	}
	
	//Balises meta key
	foreach ($tab_siteid as $siteid){
		if (${'categorie_ref_keywords'.$siteid} != ""){
			$contenu.=secure_chaine_csv($categorie_site[$siteid]['ref_keywords']).";";
		}
	}
	
	//module couleur de rayon
	if ($categorie_color != ""){
		$contenu.=$categorie[color].";";
	}
	
	//Couleur de fond du rayon
	if ($categorie_color_back != ""){
		$contenu.=$categorie[color_back].";";
	}
	
	//Couleur de survol du rayon
	if ($categorie_color_survol != ""){
		$contenu.=$categorie[color_survol].";";
	}
	
	//URLs
	foreach ($tab_siteid as $siteid){		
		if (${'categorie_url'.$siteid} != ""){
			$contenu.="http://$host/".$regleurlrewrite[$siteid][categorie]."-".url_rewrite($categorie_site[$siteid]['libelle'])."-$categorie[catid].htm;";
		}
	}
	
	//Arborescence
	foreach ($tab_siteid as $siteid){
		$chemin_sans_href_site="";
		if (${'categorie_arborescence'.$siteid} != ""){
			$chemin_sans_href_site = chemin_sans_href_site($categorie[catid], $siteid, $DB_site );
			$contenu.=secure_chaine_csv($chemin_sans_href_site).";";
		}
	}

	//Images
	foreach ($tab_siteid as $siteid){
		if (${'categorie_image'.$siteid} != ""){
			if ($categorie_site[$siteid]['image'] != ""){
				$lienImage = "http://$host/cat-".url_rewrite($categorie_site[$siteid]['libelle'])."-$categorie[catid].".$categorie_site[$siteid]['image'];
			}else{
				$lienImage="";
			}
			$contenu.=$lienImage.";";
		}
	}
	
	//module deuxième image de categorie
	foreach ($tab_siteid as $siteid){
		if (${'categorie_image2'.$siteid} != ""){
			if ($categorie_site[$siteid]['image2'] != ""){
				$lienImage = "http://$host/cat-".url_rewrite($categorie_site[$siteid]['libelle'])."-$categorie[catid]_2.".$categorie_site[$siteid]['image2'];
			}else{
				$lienImage="";
			}
			$contenu.=$lienImage.";";
		}
	}
	
	//module troisième image de categorie
	foreach ($tab_siteid as $siteid){
		if (${'categorie_image3'.$siteid} != ""){
			if ($categorie_site[$siteid]['image3'] != ""){
				$lienImage = "http://$host/cat-".url_rewrite($categorie_site[$siteid]['libelle'])."-$categorie[catid]_3.".$categorie_site[$siteid]['image3'];
			}else{
				$lienImage="";
			}
			$contenu.=$lienImage.";";
		}
	}
	
	//module background categorie
	if ($categorie_background != ""){
		if ($categorie[background] != ""){
			$lienBackground="http://$host/configurations/$host/images/categories/background/$categorie[catid].$categorie[background]";
		}else{
			$lienBackground="";
		}
		$contenu.=$lienBackground.";";
	}

	//Nombre d'articles au total
	if ($categorie_nb_articles != ""){
		$contenu.=$categorie[nb_articles].";";
	}
	
	//Nombre d'articles actifs
	foreach ($tab_siteid as $siteid){
		if (${'categorie_articles_actifs'.$siteid} != ""){
			$contenu.=$categorie_site[$siteid]['articles_actifsV1'].";";
		}
	}
	
	//Nombre d'articles commandables (ne s'affiche que s'il n'a pas le module de stock, en lieu et place de categorie_articles_stock)
	foreach ($tab_siteid as $siteid){
		if (${'categorie_articles_commandables'.$siteid} != ""){		
			$sql="SELECT COUNT(*) FROM article_site AS asite INNER JOIN position p ON (asite.artid=p.artid) WHERE p.catid='$categorie[catid]' AND asite.commandable='1'";
			$res=$DB_site->query_first($sql);
			$contenu.=$res[0].";";
		}
	}
	
	//Nombre d'articles en stock (ne s'affiche que s'il a le module de stock, en lieu et place de categorie_articles_commandables)
	if ($categorie_articles_stock != ""){
		$sql="SELECT COUNT(*) FROM article a INNER JOIN position p ON (a.artid=p.artid) INNER JOIN stock s ON (a.artid=s.artid) WHERE p.catid='$categorie[catid]' AND (a.stock_illimite='1' OR s.nombre>0)";
		$res=$DB_site->query_first($sql);
		$contenu.=$res[0].";";
	}
	
	//Nombre d'affichages multiples
	if ($categorie_affichage_multiple != ""){
		$sql="SELECT COUNT(*) FROM position WHERE catid='$categorie[catid]' AND artid NOT IN (SELECT artid FROM article WHERE catid='$categorie[catid]')";
		$res=$DB_site->query_first($sql);
		$contenu.=$res[0].";";
	}
	
	$contenu.="\n";
	fwrite($handle, $contenu);
}

fclose($handle);
$file = realpath(".")."/export/csv/".$nom_fichier_export;
header('Content-Description: File Transfer'); 
header('Content-Type: application/force-download'); 
header('Content-Length: ' . filesize($file)); 
header('Content-Disposition: attachment; filename=' . basename($file)); 
readfile($file); 
exit;

?>
