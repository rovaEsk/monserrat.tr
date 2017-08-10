<?php
include "./includes/header.php";

if (!$rootpath)
	$rootpath = $_SERVER['DOCUMENT_ROOT'] ;

$referencepage="traduction";
$pagetitle = "Traduction des expressions utilisées côté site - $host - Admin Arobases";

if(!parse_template($referencepage, $langue)){
	echo "erreur de chargement de template";
}


// AFFICHAGE ALERTES SUCCES ET ERREUR
if(isset($erreurdroits) and $erreurdroits == 1){
	$texteErreur = $multilangue[action_page_refuse];
	eval(charge_template($langue, $referencepage, "Erreur"));
}

if(isset($action) && $action == "ajouter"){
	if($admin_droit[$scriptcourant][ecriture]){
		$expression=nl2br($expression);
		$expression_existe = $DB_site->query_first("SELECT * FROM multilangue WHERE expression = '$expression'");
		if($expression_existe[multilangueid] == ""){
			$DB_site->query("INSERT INTO multilangue (expression) VALUES ('".securiserSql($expression,"html")."')");
			$multilangueid = $DB_site->insert_id();
			
			$sites = $DB_site->query("SELECT * FROM site");
			while($site = $DB_site->fetch_array($sites)){
				if(${"libelle".$site[siteid]}){
					$libelle = nl2br(${"libelle".$site[siteid]});
					$DB_site->query("INSERT INTO multilangue_txt (multilangueid, siteid, text) VALUES ('$multilangueid', '$site[siteid]','".securiserSql($libelle,"html")."')");
				}else{
					$DB_site->query("INSERT INTO multilangue_txt (multilangueid, siteid, text) VALUES ('$multilangueid', '$site[siteid]','')");
				}
			}
			
			header("location: traduction.php");
		}else{
			$action = "ajoutVariable";
			$message_erreur = "<div class='alert alert-danger alert-dismissable ta-center'>
						<button aria-hidden='true' data-dismiss='alert' class='close' type='button'></button>
						Expression déjà existante.
					</div>";	
		}
	}else{
		header('location: traduction.php?erreurdroits=1');	
	}
}

if(isset($action) &&  $action == "ajoutVariable"){
	$libNavigSupp = $multilangue[ajouter_variable];
	eval(charge_template($langue,$referencepage,"NavigSupp"));
	$sites = $DB_site->query("SELECT * FROM site");
	while($site = $DB_site->fetch_array($sites)){
		$valeur_saisie = ${"libelle".$site[siteid]};
		eval(charge_template($langue,$referencepage,"AjoutVariableLibelleSiteBit"));
	}
	eval(charge_template($langue,$referencepage,"AjoutVariable"));
}

//GESTION EXPORT
if(isset($action) and $action == "export"){
	$contenu = "multilangueid";
	$listeSites = $DB_site->query("SELECT * FROM site");
	
	while($listeSite = $DB_site->fetch_array($listeSites)){		
		if(isset(${"site".$listeSite[siteid]})){
			$contenu .= ";$listeSite[libelle]";
		}
	}
	$contenu .= "\n";
	
	$texte_multilangue = $DB_site->query("SELECT DISTINCT multilangueid FROM multilangue_txt ORDER BY multilangueid");
	while($multilangueid = $DB_site->fetch_array($texte_multilangue)){
		$contenu .= "$multilangueid[multilangueid]";
		foreach($_POST as $key => $value){
			$texte = $DB_site->query_first("SELECT text FROM multilangue_txt WHERE multilangueid='$multilangueid[multilangueid]' AND siteid='$value'");
			$contenu .= ";$texte[text]";
		}
		$contenu .= "\n";
	}
	
	$nom_fic = "export_traduction.csv";
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
	
	// Force le téléchargement du fichier après la création
	if (file_exists($filename)) { 
		if(!is_dir($filename)){ 
			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename='.basename($filename));
			header('Content-Transfer-Encoding: binary');
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
			header('Content-Length: ' . filesize($filename));
			ob_clean(); 
			flush(); 
			readfile($filename);
			return "Downloading $filename";
			exit; 
		}
	}
}

//GESTION IMPORT
if(isset($action) && $action == "import"){
	if($admin_droit[$scriptcourant][ecriture]){
		global $tab_siteid;
		$erreur = "";
		if(isset($_FILES['fichier_import']) && $_FILES[fichier_import][name] != ""){
			$listeTypesAutorise = array("text/csv");
			erreurUpload("fichier_import", $listeTypesAutorise, 1048576);
			if(!$erreur){
				$maj_francais=false;
				if($maj=="1")
					$maj_francais=true;
				
				$first=true;
				
				$fileData=fopen($_FILES['fichier_import']['tmp_name'],'r');
				if (($handle = $fileData) !== FALSE) {
					// Chaque ligne du fichier
					while (($data = fgetcsv($handle, 10000, ";")) !== FALSE) {
						if($maj_francais){
							$i=1;
						}else{
							$i=2;
						}
						// 1ère ligne
						if($first){
							// Récupération des ID des sites à mettre à jour
							for($i;$i<sizeof($data);$i++){							
								$siteid = $DB_site->query_first("SELECT siteid FROM site WHERE libelle='$data[$i]'");							
								$tab_siteid[$siteid[siteid]] = $i;					
							}
							$first=false;	
						}else{						
							if(sizeof($tab_siteid)){
								foreach($tab_siteid AS $siteid => $colonne){
									$existe = $DB_site->query("SELECT * FROM multilangue_txt WHERE multilangueid='$data[0]' AND siteid='$siteid'");
									if($DB_site->num_rows($existe)){
										$DB_site->query('UPDATE multilangue_txt SET text = "'.securiserSql($data[$colonne],"html").'" WHERE multilangueid="'.$data[0].'" AND siteid="'.$siteid.'"');	
										//echo 'UPDATE multilangue_txt SET text = "'.securiserSql($data[$colonne],"html").'" WHERE multilangueid="'.$data[0].'" AND siteid="'.$siteid.'"<br>';
									}else{
										$DB_site->query('INSERT INTO multilangue_txt (multilangueid, siteid, text) VALUES ("'.$data[0].'","'.$siteid.'","'.securiserSql($data[$colonne],"html").'")');
										//echo 'INSERT INTO multilangue_txt (multilangueid, siteid, text) VALUES ("'.$data[0].'","'.$siteid.'","'.securiserSql($data[$colonne],"html").'")<br>';
									}
								}
							}
						}
					}
					fclose($handle);			
				}
				//exit;
				header("location: traduction.php");					
			} else {
				header("location: traduction.php?erreur=1");
			}
		} else {
			header("location: traduction.php?erreur=2");	
		}
	}
}


//GESTION TRADUCTION
if ($action == "translate") {	
	if($admin_droit[$scriptcourant][ecriture]){
		//print_r($traductionTxt);	
		
		foreach( $traductionTxt as $multilangueid => $trad ){
			
			if($trad != ""){
				$testExiste = $DB_site->query_first("SELECT * FROM multilangue_txt
														WHERE multilangueid = '$multilangueid'
														AND siteid = '$idSite'");
				
				$trad=nl2br($trad);
				
				if($testExiste[multilangueid] != ""){
					$DB_site->query("UPDATE multilangue_txt
										SET text='".securiserSql($trad,"html")."'
										WHERE multilangueid = '$multilangueid'
										AND siteid = '$idSite'");
				}else{
					$DB_site->query("INSERT INTO multilangue_txt(multilangueid, siteid, text) VALUES('$multilangueid', '$idSite', '".securiserSql($trad,"html")."')");
				}
			}		
			
		}
		$action = "traduire";
		header("location: traduction.php?action=traduire&idSite=$idSite&alertSuccess=success");
	}
}

//GESTION AFFICHAGE LISTE TRADUCTION
if ($action == "traduire") {
	
	$reqListeTrad = $DB_site->query("SELECT *
									FROM multilangue_txt
									WHERE siteid = 1");	
	$reqListeInput = $DB_site->query("SELECT *
										FROM multilangue_txt
										WHERE siteid = '$idSite'");
	
	$infosite = $DB_site->query_first("SELECT * FROM site WHERE siteid = '$idSite'");
	
	
	while ($listeTraduction = $DB_site->fetch_array($reqListeTrad)){
		$inputTraduction = $DB_site->query_first("SELECT *
													FROM multilangue_txt
													WHERE siteid = '$idSite'
													AND multilangueid = $listeTraduction[multilangueid]");
													
		$listeTraduction[text] = str_replace('<br/>', "\n", $listeTraduction[text]);
		$listeTraduction[text] = str_replace('<br>', "\n", $listeTraduction[text]);
		$listeTraduction[text] = str_replace('<br/ >', "\n", $listeTraduction[text]);
		$listeTraduction[text] = str_replace('<br />', "\n", $listeTraduction[text]);
		$listeTraduction[text] = str_replace('<bR>', "\n", $listeTraduction[text]);
		$listeTraduction[text] = str_replace('<Br>', "\n", $listeTraduction[text]);
		$listeTraduction[text] = str_replace('<BR>', "\n", $listeTraduction[text]);
		$listeTraduction[text] = str_replace('<BR />', "\n", $listeTraduction[text]);
		$listeTraduction[text] = str_replace('<BR/>', "\n", $listeTraduction[text]);
		$listeTraduction[text] = str_replace('<BR/ >', "\n", $listeTraduction[text]);
		
		
		$inputTraduction[text] = str_replace('<br/>', "\n", $inputTraduction[text]);
		$inputTraduction[text] = str_replace('<br>', "\n", $inputTraduction[text]);
		$inputTraduction[text] = str_replace('<br/ >', "\n", $inputTraduction[text]);
		$inputTraduction[text] = str_replace('<br />', "\n", $inputTraduction[text]);
		$inputTraduction[text] = str_replace('<bR>', "\n", $inputTraduction[text]);
		$inputTraduction[text] = str_replace('<Br>', "\n", $inputTraduction[text]);
		$inputTraduction[text] = str_replace('<BR>', "\n", $inputTraduction[text]);
		$inputTraduction[text] = str_replace('<BR />', "\n", $inputTraduction[text]);
		$inputTraduction[text] = str_replace('<BR/>', "\n", $inputTraduction[text]);
		$inputTraduction[text] = str_replace('<BR/ >', "\n", $inputTraduction[text]);
		
		eval(charge_template($langue,$referencepage,"ListeBit"));
		
	}
	eval(charge_template($langue,$referencepage,"Liste"));
	$libNavigSupp="Modification des traductions du <i><b>\"$infosite[libelle]\"</b></i>";
	if ($alertSuccess == "success"){
		$infosSuccess = $DB_site->query_first("SELECT * FROM mode_livraison_site WHERE modelivraisonid = '$modelivraisonid'");
		$texteSuccess = $multilangue[le_tableau_de_traduction_du]." \"$infosite[libelle]\"". $mutilangue[a_bien_ete_edite];
		eval(charge_template($langue,$referencepage,"Success"));
	}
	eval(charge_template($langue,$referencepage,"NavigSupp"));
}



//GESTION AFFICHAGE LISTE SITES
if (!isset($action) or ($action == "")) {
	if(isset($erreur) && ($erreur == "1")){
		$texteErreur = $multilangue[fichier_doit_etre_csv];
		eval(charge_template($langue,$referencepage,"Erreur"));
	}
	if(isset($erreur) && ($erreur == "2")){
		$texteErreur = $multilangue[fichier_obligatoire];
		eval(charge_template($langue,$referencepage,"Erreur"));
	}
	$listeSites = $DB_site->query("SELECT * FROM site");
	$site_principal = $DB_site->query_first("SELECT libelle FROM site WHERE siteid='1'");
	while ($listeSite = $DB_site->fetch_array($listeSites)){
		$langueSite = $DB_site->query_first("SELECT libelle
				FROM langue l
				WHERE l.langueid = $listeSite[langueid]");
		$siteDevise = $DB_site->query_first("SELECT contenu
				FROM devise d
				WHERE d.deviseid = $listeSite[deviseid]");

		$value = "value='$listeSite[siteid]'";
		if($listeSite[siteid] != "1"){
			eval(charge_template($langue,$referencepage,"ExportSiteBit"));
		}
		eval(charge_template($langue,$referencepage,"ListeSiteBit"));
	}
	eval(charge_template($langue,$referencepage,"ListeSite"));
	eval(charge_template($langue,$referencepage,"ImportInfo"));
	eval(charge_template($langue,$referencepage,"Export"));
	eval(charge_template($langue,$referencepage,"Import"));	
}



$TemplateIncludejavascript = eval(charge_template($langue, $referencepage,"Includejavascript"));
eval(charge_template($langue,"commun","header"));
eval(charge_template($langue,"commun","footer"));
eval(charge_template($langue,$referencepage,"index"));

$nomtemplateindex="Template".ucfirst($referencepage)."Index";
echo ${$nomtemplateindex};

$DB_site->close();
flush();

?>