<?php

include "includes/header.php";

$referencepage="plaques_professionnelles";
$pagetitle = "Gestion du module plaques professionnelles - $host - Admin Arobases";

$titrepage=$multilangue[gestion_plaques_professionnelles];
$lienpagebase="plaques-professionnelles.php";
$niveaunavigsup="";

$class_menu_gestion_modules_plaques_professionnelles_active = "active";

if(!parse_template($referencepage, $langue)){
	echo "erreur de chargement de template";
}

if($_GET[alert] == 1){
	$texteSuccess=$multilangue[les_coordonnees_ont_ete_mises_a_jour];
	eval(charge_template($langue,$referencepage,"Success"));
}

if(isset($erreurdroits) and $erreurdroits == 1){
	$texteErreur = $multilangue[action_page_refuse];
	eval(charge_template($langue, $referencepage, "Erreur"));
}

$articleModuleId =  7 ; 
$widthbg = "300px";
$heightbg = "200px";
/** init form module **/
if (!isset($action) or $action == ""){
    if($admin_droit[$scriptcourant][suppression]){
        $sitesParams = $DB_site->query("SELECT * FROM site");
    	while($sitesParam = $DB_site->fetch_array($sitesParams)){
			$devise_site_actuel = $tabsites[$sitesParam[siteid]][devise_complete];
		}
    	$site = $DB_site->query_first("SELECT * FROM articlemodule WHERE articlemoduleid=$articleModuleId");  
        $pays = $DB_site->query_first("SELECT * FROM pays WHERE paysid = '57'");
    	$image_module =  "http://$host/admin/assets/img/modulehtml5/default-image.png";
            $class_wysiwyg="editeur";
            $articleModuleId = var2html($site[articlemoduleid]);
        	$titre_module=var2html($site[libelle]);
        	$texte_module=var2html($site[textemodule]);
            $image_module_extension=var2html($site[imagemodule]);
            if($image_module_extension != null){
                $image_module= "http://$host/admin/assets/img/modulehtml5/".$articleModuleId.".".$image_module_extension;
            }
    
            $prixRegles = $DB_site->query("SELECT * FROM tarifs_plaques"); 
            if ($DB_site->num_rows($prixRegles) > 0){
                while ($prixReglesItem = $DB_site->fetch_array($prixRegles)){
                    $tvatauxnormale = $pays[TVAtauxnormal];
                    $prixmoduleTTC =  number_format((float)( $prixReglesItem[prix_constate] + ( $prixReglesItem[prix_constate] * $tvatauxnormale ) / 100), 2, '.', '');
                    $prixmoduleHTC = number_format((float)($prixReglesItem[prix_constate]), 2, '.', ''); 
                    $categoriRegelePrixId = $prixReglesItem[id_matiere];
                    $categoriDimensionId = $prixReglesItem[dimensions];
                    $idPlaqueProID =  $prixReglesItem[id];
                    $sitesDevise = $devise_site_actuel;
                    /** get all categorie regle produit  **/
                    $categorieRegleProduits = $DB_site->query("SELECT * FROM matieres"); 
                    if ($DB_site->num_rows($categorieRegleProduits) > 0){
                        $TemplatePlaques_professionnellesModuleCategoriePrixListBit = "";
                        $categoriePrixMatiere = "Aucun";
						while ($categorieRegleProduit = $DB_site->fetch_array($categorieRegleProduits)){
                            $categorieRegleProduitId = $categorieRegleProduit[id];
                            $categorieregleprix = $categorieRegleProduit[nom];
                            $selectedOption = "";
                            if($categoriRegelePrixId == $categorieRegleProduitId){
                                $selectedOption ="selected";
								$categorieregleprixnom = $categorieregleprix;
                            }
                            //eval(charge_template($langue,$referencepage,"moduleCategoriePrixListBit"));            
                        }
						$categoriePrixMatiere = $categorieregleprixnom;
						eval(charge_template($langue,$referencepage,"moduleCategoriePrixListBit"));            
                    }
                	/** get all categorie regle produit  **/
                    /** get all categorie dimension produit  **/
                    $categorieDimensionProduits = $DB_site->query("SELECT * FROM categorie_dimension_module"); 
                    if ($DB_site->num_rows($categorieDimensionProduits) > 0){
                        $TemplatePlaques_professionnellesModuleCategorieDimensionListBit = "";
                        $dimensionLongHaut = "Aucun";
						while ($categorieDimensionProduit = $DB_site->fetch_array($categorieDimensionProduits)){
                            $categorieDimensionProduitId = $categorieDimensionProduit[categoriedimensionid];
                            $categorieDimensionprix = $categorieDimensionProduit[dimension_prix];
                            $selectedOption = "";
                            if($categoriDimensionId == $categorieDimensionProduitId){
                                $selectedOption ="selected";
								$nomDimension = $categorieDimensionprix;
                            }
                            //eval(charge_template($langue,$referencepage,"moduleCategorieDimensionListBit"));            
                        }
						$dimensionLongHaut = $nomDimension;
						eval(charge_template($langue,$referencepage,"moduleCategorieDimensionListBit"));
                    }
                	/** get all categorie dimension produit  **/
                    $categorieMatiere = $DB_site->query_first("SELECT nom FROM matieres WHERE id = '$categoriRegelePrixId'"); 
                    $categorieDimension = $DB_site->query_first("SELECT dimension_prix FROM categorie_dimension_module WHERE categoriedimensionid = '$categoriDimensionId'"); 
                    $nomCategorieMatiere = $categorieMatiere[nom];
                    $nomCategorieDimension = $categorieDimension[dimension_prix];
                    eval(charge_template($langue,$referencepage,"modulePrixBit"));            
                }
            }
  
            /** get all categorie dimension produit  **/
            $categorieDimensionProduits = $DB_site->query("SELECT * FROM categorie_dimension_module"); 
            if ($DB_site->num_rows($categorieDimensionProduits) > 0){
                while ($categorieDimensionProduit = $DB_site->fetch_array($categorieDimensionProduits)){
                    $categorieDimensionProduitId = $categorieDimensionProduit[categoriedimensionid];
                    $categorieDimensionPrix = $categorieDimensionProduit[dimension_prix];
                    eval(charge_template($langue,$referencepage,"moduleCategorieDimensionBit"));            
                }
            }
        	/** get all categorie dimension produit  **/
            /** get all categorie regle produit  **/
            $categorieRegleProduits = $DB_site->query("SELECT * FROM matieres"); 
            if ($DB_site->num_rows($categorieRegleProduits) > 0){
                while ($categorieRegleProduit = $DB_site->fetch_array($categorieRegleProduits)){
                    $categorieRegleProduitId = $categorieRegleProduit[id];
                    $categorieregleprix = $categorieRegleProduit[nom];
                    eval(charge_template($langue,$referencepage,"moduleCategoriePrixBit"));            
                }
            }
        	/** get all categorie regle produit  **/
            eval(charge_template($langue,$referencepage,"moduleBit"));

    }else{
       header('location: plaques_professionnelles.php?erreurdroits=1'); 
    }	
}
/** edit form module **/
if(isset($action) && $action == "modifmodule"){
    
    if($admin_droit[$scriptcourant][ecriture]){
        $articlemoduleid   = securiserSql($_POST['moduleid']);
        $titremodule    = securiserSql($_POST['titremodule']);
        $textmodule     = securiserSql($_POST['textemodule']);
        
        $DB_site->query("UPDATE articlemodule SET libelle = '$titremodule', textemodule = '$textmodule' WHERE articlemoduleid = '$articlemoduleid'");
        
        $prixModules = $DB_site->query("SELECT * FROM tarifs_plaques");
        while($prixModule = $DB_site->fetch_array($prixModules)){
            $idprixmodule = $prixModule[id];
            /** update data to plaque pro table **/
            $newprixmodulehtc = $_POST['prixmodulehtc_'.$idprixmodule];
            $categorieDimesion = $_POST['categorieDimesion_'.$idprixmodule];
            $categorieregleprix = $_POST['categorieregleprix_'.$idprixmodule];
            #insert prix dans  tarifs_plaques >>  prix /dimension : long & haut / id_matiere
            $dimensionQuery = $DB_site->query_first("SELECT * FROM categorie_dimension_module WHERE categoriedimensionid='$categorieDimesion'"); 
            $dimension = $dimensionQuery[dimension_prix];
            $dimensionId = $dimensionQuery[categoriedimensionid];
            $arrayDimension = explode("x", $dimension);
            $longueur = $arrayDimension[0];
            $hauteur = $arrayDimension[1];
            $DB_site->query("UPDATE tarifs_plaques SET prix_constate = '$newprixmodulehtc', longueur= '$longueur', hauteur= '$hauteur', dimensions='$dimensionId', id_matiere='$categorieregleprix' WHERE id = '$idprixmodule'");
            /** update data to plaque pro table **/  
        } 
   
        if(!empty($_FILES['imageModule']['name'])){
    			$listeTypesAutorise = array("image/pjpeg", "image/jpeg", "image/gif", "image/png");
    			erreurUpload("imageModule", $listeTypesAutorise, 5048576);
    			if ($erreur == "" && !empty($_FILES['imageModule']['name'])){
    				$type_fichier = define_extention($_FILES['imageModule']['name']);
                   // p($type_fichier , true);
    				$DB_site->query("UPDATE articlemodule SET imagemodule = '$type_fichier' WHERE articlemoduleid = '$articlemoduleid'");
    				$nom_fichier = $rootpath."admin/assets/img/modulehtml5/".$articlemoduleid.".".$type_fichier;
    				copier_image($nom_fichier, 'imageModule');
    			}
    	}
        header('location: plaques_professionnelles.php');   
    }else{
        header('location: plaques_professionnelles.php?erreurdroits=1');	
    }
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
