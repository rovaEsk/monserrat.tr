<?php

include "./includes/header.php";

$pagetitle = "Tableau de bord - $host - Admin Arobases";
$referencepage = "index";
if(!parse_template("index", $langue)){
    echo "erreur de chargement de template";
}

@ini_set('memory_limit','512M');
set_time_limit(0);

$langueLower = strtolower($langue);
$navigation = $multilangue[accueil] ;
//echo "<div class=\"navigation\">$navigation</div>";

$class_menu_index_active = "active";

if(isset($action) && $action == "supprimer"){
    if(isset($idsite) && $idsite != ""){
        $urlSite = "?idsite=$idsite";
    }else{
        $urlSite = "";
    }
    $DB_site->query("DELETE FROM widget WHERE widgetid = '$widgetid'");
    $DB_site->query("DELETE FROM widget_stat WHERE widgetid = '$widgetid'");
    header('location: index.php'.$urlSite);
}

$iSiteID = NULL;
if (isset($_GET['idsite']) && $_GET['idsite'] > 0) {   
    $iSiteID = $_GET['idsite'];
}

if(isset($action) && $action == "modifier"){
    if(isset($idsite) && $idsite != ""){
        $urlSite = "?idsite=$idsite";
    }else{
        $urlSite = "";
    }
    
    $dataOption = "";
    if( 
        isset($_POST['options']) && !empty($_POST['options']) 
        && in_array($_POST['donnee_modif'],array("17","12")) 
    ){
        $dataOption = HTools::jsonEncode($_POST['options']);
    }
    
    $ancienWidget = $DB_site->query_first("SELECT * FROM widget WHERE widgetid = '$widgetid'");
    $ancienX = $ancienWidget[posX];
    $ancienY = $ancienWidget[posY];
    $ancienWidth = $ancienWidget[cols];
    $ancienHeight = $ancienWidget[rows];
    
    $DB_site->query("DELETE FROM widget WHERE widgetid = '$widgetid'");
    $DB_site->query("DELETE FROM widget_stat WHERE widgetid = '$widgetid'");
    
    // Selection de la période pour le enum en BDD
    switch ($periode_modif){
        case "$multilangue[aujourdhui]":
            $type_periode = "jour";
            break;
        case "$multilangue[hier]":
            $type_periode = "hier";
            break;
        case "$multilangue[sept_derniers_jours]":
            $type_periode = "7jours";
            break;
        case "$multilangue[trente_derniers_jours]":
            $type_periode = "30jours";
            break;
        case "$multilangue[ce_mois]":
            $type_periode = "mois";
            break;
        case "$multilangue[mois_dernier]":
            $type_periode = "mois_dernier";
            break;
        case "$multilangue[cette_annee]":
            $type_periode = "annee";
            break;
        case "$multilangue[l_annee_derniere]":
            $type_periode = "annee_derniere";
            break;
        case "$multilangue[depuis_debut]":
            $type_periode = "debut";
            break;
        case "$multilangue[periode_precise]":
            $type_periode = "precise";
            break;
    }
    
    $DB_site->query("INSERT INTO widget (userid, cols, rows, posX, posY, type_representation, type_periode,periode_titre,options) VALUES ('$user_info[userid]','$ancienWidth','$ancienHeight','$ancienX','$ancienY','$valeur_representation_modif', '$type_periode','" . securiserSql($periode_modif) . "','$dataOption')");
    $widgetid = $DB_site->insert_id();
    
    // Chaque cas de représentation
    switch ($valeur_representation_modif) {
        case "courbe":
            $DB_site->query("UPDATE widget SET granularite = '$granularite_modif' WHERE widgetid = '$widgetid'");
            break;
        case "tableau":
            if(intval($donnee_modif) >= 16 && intval($donnee_modif) <= 20){
                $DB_site->query("UPDATE widget SET nb_resultats = '$nb_resultats_modif' WHERE widgetid = '$widgetid'");
                $DB_site->query("UPDATE widget SET tri = '$tri_modif' WHERE widgetid = '$widgetid'");
            }else{
                $DB_site->query("UPDATE widget SET granularite = '$granularite_modif' WHERE widgetid = '$widgetid'");
            }
            break;
    case "camembert":
        $DB_site->query("UPDATE widget SET nb_resultats = '$nb_resultats_modif' WHERE widgetid = '$widgetid'");
        if($periode_prec_modif != ""){
            $DB_site->query("UPDATE widget SET periode_precedente = '1' WHERE widgetid = '$widgetid'");
        }
        break;
    case "moyenne":
        $DB_site->query("UPDATE widget SET granularite = '$granularite_modif' WHERE widgetid = '$widgetid'");
        if($periode_prec_modif != ""){
            $DB_site->query("UPDATE widget SET periode_precedente = '1' WHERE widgetid = '$widgetid'");
        }
        break;
    case "total":
        if($periode_prec_modif != ""){
            $DB_site->query("UPDATE widget SET periode_precedente = '1' WHERE widgetid = '$widgetid'");
        }
        break;
    }
    // Premiere donnée
    $DB_site->query("INSERT INTO widget_stat (widgetid, type_statid) VALUES ('$widgetid','$donnee_modif')");
    $statid = $DB_site->insert_id();

    if($type_periode == "precise"){
        // date converti en d-m-Y sinon en d/m/Y strtotime lit m/d/Y
        $date_debut_modif = str_replace('/', '-', $date_debut_modif);
        $date_fin_modif = str_replace('/', '-', $date_fin_modif);
        $date_debut_modif = date('Y-m-d', strtotime($date_debut_modif));
        $date_fin_modif = date('Y-m-d', strtotime($date_fin_modif));
        $DB_site->query("UPDATE widget SET date_debut = '$date_debut_modif', date_fin = '$date_fin_modif' WHERE widgetid = '$widgetid'");
    }
    // Deuxieme donnée si elle est sélectionnée
    if($donnee2_modif != "0" || $donnee2_modif == ""){
        $DB_site->query("INSERT INTO widget_stat (widgetid, type_statid) VALUES ('$widgetid','$donnee2_modif')");
        $statid2 = $DB_site->insert_id();
    }

    // Troisième donnée si elle est sélectionnée
    if($donnee3_modif != "0" || $donnee3_modif == ""){
        $DB_site->query("INSERT INTO widget_stat (widgetid, type_statid) VALUES ('$widgetid','$donnee3_modif')");
        $statid3 = $DB_site->insert_id();
    }
    
    header('location: index.php'.$urlSite);
    exit();
}

if(isset($action) && $action == "ajouter"){
    
    if(isset($idsite) && $idsite != ""){
        $urlSite = "?idsite=$idsite";
    }else{
        $urlSite = "";
    }
    // Selection de la période pour le enum en BDD
    switch ($periode){
        case "$multilangue[aujourdhui]":
        $type_periode = "jour";
        break;
        case "$multilangue[hier]":
        $type_periode = "hier";
            break;
        case "$multilangue[sept_derniers_jours]":
        $type_periode = "7jours";
            break;
        case "$multilangue[trente_derniers_jours]":
        $type_periode = "30jours";
        break;
        case "$multilangue[ce_mois]":
        $type_periode = "mois";
        break;
        case "$multilangue[mois_dernier]":
            $type_periode = "mois_dernier";
            break;
        case "$multilangue[cette_annee]":
            $type_periode = "annee";
            break;
            case "$multilangue[l_annee_derniere]":
            $type_periode = "annee_derniere";
            break;
        case "$multilangue[depuis_debut]":
            $type_periode = "debut";
            break;
            case "$multilangue[periode_precise]":
            $type_periode = "precise";
            break;
    }

    $dataOption = "";
    if( 
        isset($_POST['options']) && !empty($_POST['options']) 
        && in_array($_POST['donnee'],array("17","12")) 
    ){
        $dataOption = HTools::jsonEncode($_POST['options']);
    }
    
    $colonne = $colonne * 2;
    $ligne = $ligne * 2;
    $DB_site->query("INSERT INTO widget (userid, cols, rows, posX, posY, type_representation, type_periode,periode_titre,options) VALUES ('$user_info[userid]','$colonne','$ligne','0','0','$valeur_representation', '$type_periode','" . securiserSql($periode) . "','$dataOption')");
    $widgetid = $DB_site->insert_id();

    // Chaque cas de représentation
    switch ($valeur_representation) {
        case "courbe":
            $DB_site->query("UPDATE widget SET granularite = '$granularite' WHERE widgetid = '$widgetid'");
            break;
        case "tableau":
            if(intval($donnee) >= 16 && intval($donnee) <= 20){
                $DB_site->query("UPDATE widget SET nb_resultats = '$nb_resultats' WHERE widgetid = '$widgetid'");
                $DB_site->query("UPDATE widget SET tri = '$tri' WHERE widgetid = '$widgetid'");
            }else{
                $DB_site->query("UPDATE widget SET granularite = '$granularite' WHERE widgetid = '$widgetid'");
            }
            break;
        case "camembert":
            $DB_site->query("UPDATE widget SET nb_resultats = '$nb_resultats' WHERE widgetid = '$widgetid'");
            if($periode_prec == "on"){
                $DB_site->query("UPDATE widget SET periode_precedente = '1' WHERE widgetid = '$widgetid'");
            }
            break;
        case "moyenne":
            $DB_site->query("UPDATE widget SET granularite = '$granularite' WHERE widgetid = '$widgetid'");
            if($periode_prec == "on"){
                $DB_site->query("UPDATE widget SET periode_precedente = '1' WHERE widgetid = '$widgetid'");
            }
            break;
        case "total":
            if($periode_prec == "on"){
                $DB_site->query("UPDATE widget SET periode_precedente = '1' WHERE widgetid = '$widgetid'");
            }
            break;
    }
    // Premiere donnée
    $DB_site->query("INSERT INTO widget_stat (widgetid, type_statid) VALUES ('$widgetid','$donnee')");
    $statid = $DB_site->insert_id();
    
    if($type_periode == "precise"){
        $DB_site->query("UPDATE widget SET date_debut = '$date_debut', date_fin = '$date_fin' WHERE widgetid = '$widgetid'");
    }
    
    // Deuxieme donnée si elle est sélectionnée
    if($donnee2 != "0" || $donnee2 == ""){
        $DB_site->query("INSERT INTO widget_stat (widgetid, type_statid) VALUES ('$widgetid','$donnee2')");
        $statid2 = $DB_site->insert_id();
        if($type_periode == "precise"){
            $DB_site->query("UPDATE widget SET date_debut = '$date_debut', date_fin = '$date_fin' WHERE widgetid = '$widgetid'");
        }
    }
    
    // Troisième donnée si elle est sélectionnée
    if($donnee3 != "0" || $donnee3 == ""){
        $DB_site->query("INSERT INTO widget_stat (widgetid, type_statid) VALUES ('$widgetid','$donnee3')");
        $statid3 = $DB_site->insert_id();
        if($type_periode == "precise"){
            $DB_site->query("UPDATE widget SET date_debut = '$date_debut', date_fin = '$date_fin' WHERE widgetid = '$widgetid'");
        }
    }
    header('location: index.php'.$urlSite);
    exit();
}

if(!isset($action) || $action == ""){
    $sites = $DB_site->query("SELECT * FROM site");

    if(isset($idsite) && ($idsite != '0' || $idsite != '')){
        $andSite = "AND siteid = '$idsite'";
    }else{
        $andSite = "";
    }
    
    while($site = $DB_site->fetch_array($sites)){
        if($idsite == $site[siteid]){
            $selectedSite = "selected='selected'";
            $selectedSiteTous = "";
        }else{
            $selectedSiteTous = "selected='selected'";
            $selectedSite = "";
        }
        eval(charge_template($langue,$referencepage,"SelectSiteOptionBit"));
    }
        
    $date_debut = $date_fin = date('d-m-Y', time());
    $type_stat_parents = $DB_site->getQueryResults("SELECT * FROM type_stat_parent WHERE langueid = '$admin_langueid'");
    $TemplateIndexOptionTypeDonnee1OptGroupBit = "" ;
    $htmlOptionDonnees = "" ;
    foreach( $type_stat_parents as $type_stat_parent ){
        $htmlOptionDonnees .= "<optgroup label=" . $type_stat_parent[libelle] . ">";
        $type_stats = $DB_site->getQueryResults("SELECT * FROM type_stat WHERE langueid = '$admin_langueid' AND typestatparentid = '$type_stat_parent[typestatparentid]'");
        foreach( $type_stats as $stat ){
            //eval(charge_template($langue,$referencepage,"OptionTypeDonnee1Bit"));
            $htmlOptionDonnees .= "<option value=". $stat[type_statid] . ">$stat[libelle]</option>" ;
            
            if(($stat[type_statid] < 15 || $stat[type_statid] > 20) && $stat[type_statid] != 5 && ($stat[type_statid] < 11 || $stat[type_statid] > 13)){
                eval(charge_template($langue,$referencepage,"OptionTypeDonnee2Bit"));
                eval(charge_template($langue,$referencepage,"OptionTypeDonnee3Bit"));
            }   
        }
        $htmlOptionDonnees .= '</optgroup>';
    }

    // @TODO Condition à enlever une fois le dév des stats terminé
    if(1/*$user_info[userid] == 1*/){
    // Template à enlever une fois le dév des stats terminé
    eval(charge_template($langue,$referencepage,"Ajt"));
    
    $data = "";
    $options = "";
    $placeholder = "";
    
    $htmlWidgetOptionTypeDonnee1 = "";
    // Affichage des widgets
    $widgets = $DB_site->getQueryResults("SELECT * FROM widget WHERE userid = '$user_info[userid]'");
    foreach( $widgets as $widget){
       /**
        * @XXX
        * @date 2015-10-09
        * Init template for convertion tunnel
        */
        $TemplateIndexWidgetBitTunelConversion = "";
        $TemplateIndexWidgetBitTunelConversionBit = "";
        
        $TemplateIndexWidgetBitCourbe = "";
        $TemplateIndexWidgetBitTableau = "";
        $TemplateIndexWidgetBitCamembert = "";
        $TemplateIndexWidgetBitTotal = "";
        $TemplateIndexWidgetBitTotalPrec = "";
        $TemplateIndexWidgetBitMoyenne = "";
        $TemplateIndexWidgetBitMoyennePrec = "";
        $TemplateIndexWidgetBitTableauColonneBit = "";
        $TemplateIndexWidgetBitTableauLigneBit = "";
        
        $TemplateIndexWidgetOptionTypeDonnee1Bit = "";
        $TemplateIndexWidgetOptgroupTypeDonnee1Bit = "";
        
        $TemplateIndexWidgetOptionTypeDonnee2Bit = "";
        $TemplateIndexWidgetOptgroupTypeDonnee2Bit = "";
        
        $TemplateIndexWidgetOptionTypeDonnee3Bit = "";
        
        // titre du widget
        $titre = "";
        // periode pour le titre
        $periode = "";
        
        $optionStat = "";
        $dataStat = "";
        
        // Récupération des dates pour l'affichage des données
        switch ($widget[type_periode]){
            case "jour";
                $date_debut_stat = date('Y-m-d', time());
                $date_fin_stat = date('Y-m-d', time());
                
                // Variable pour l'affichage modif widget
                $valeur_periode_modif = $multilangue[aujourdhui];
            break;
            case "hier";
                $date = strtotime('-1 day', time());
                $date_debut_stat = date('Y-m-d', $date);
                $date_fin_stat = date('Y-m-d', $date);
                
                // Variable pour l'affichage modif widget
                $valeur_periode_modif = $multilangue[hier];
            break;
            case "7jours";
                $date = strtotime('-6 days', time());
                $date_debut_stat = date('Y-m-d', $date);
                $date_fin_stat = date('Y-m-d', time());
                
                // Variable pour l'affichage modif widget
                $valeur_periode_modif = $multilangue[sept_derniers_jours];
            break;
            case "30jours";
                $date = strtotime('-29 days', time());
                $date_debut_stat = date('Y-m-d', $date);
                $date_fin_stat = date('Y-m-d', time());
                
                // Variable pour l'affichage modif widget
                $valeur_periode_modif = $multilangue[trente_derniers_jours];
            break;
            case "mois";
                $mois = date('Y-m', time());
                $date_debut_stat = $mois.'-01';
                $date_fin_stat = date('Y-m-d', time());
                
                // Variable pour l'affichage modif widget
                $valeur_periode_modif = $multilangue[ce_mois];
            break;
            case "mois_dernier";
                $annee = date('Y', time());
                $mois = date('m',  strtotime('last month', time()));
                $date_debut_stat = date('Y-m-d', strtotime('01-'.$mois.'-'.$annee));
                $date_fin_stat = date('Y-m-t', strtotime('01-'.$mois.'-'.$annee));
                
                // Variable pour l'affichage modif widget
                $valeur_periode_modif = $multilangue[mois_dernier];
            break;
            case "annee";
                $annee = date('Y', time());
                $date_debut_stat = date('Y-m-d', strtotime('01-01-'.$annee));
                $date_fin_stat = date('Y-m-d', time());
                
                // Variable pour l'affichage modif widget
                $valeur_periode_modif = $multilangue[cette_annee];
            break;
            case "annee_derniere";
                $annee = date('Y', strtotime('last year', time()));
                $date_debut_stat = date('Y-m-d', strtotime('01-01-'.$annee));
                $date_fin_stat = date('Y-m-t', strtotime('01-12-'.$annee));
                
                // Variable pour l'affichage modif widget
                $valeur_periode_modif = $multilangue[l_annee_derniere];
            break;
            case "debut";
                $date_debut_stat = date('Y-m-d', strtotime('01-01-2014'));
                $date_fin = date('Y-m-d', time());
                
                // Variable pour l'affichage modif widget
                $valeur_periode_modif = $multilangue[depuis_debut];
            break;
            case "precise";
                $date_debut_stat = date('Y-m-d', strtotime($widget[date_debut]));
                $date_fin_stat = date('Y-m-d', strtotime($widget[date_fin]));
                
                // Variable pour l'affichage modif widget
                $valeur_periode_modif = $multilangue[periode_precise];
            break;
        }
        
        // Affichage modification widget
        $date_debut_modif = date('d/m/Y', strtotime($date_debut_stat));
        $date_fin_modif = date('d/m/Y', strtotime($date_fin_stat));
        
        // Affichage titre widget
        $date_debut_titre = date('d/m/Y', strtotime($date_debut_stat));
        $date_fin_titre = date('d/m/Y', strtotime($date_fin_stat));
        
        
        if( $widget['periode_titre'] != "" ){
            switch( $widget['periode_titre'] ){
                case $multilangue[aujourdhui]:
                    $periode = strtolower($widget['periode_titre']) ;
                    break;
                case $multilangue[hier]:
                    $periode = strtolower($widget['periode_titre']) ;
                    break;
                case $multilangue[ce_mois]:
                    $periode = strtolower($widget['periode_titre']) ;
                    break;
                case $multilangue[cette_annee]:
                    $periode = $widget['periode_titre'] ;
                    break;
                case $multilangue[mois_dernier]:
                    $periode = $multilangue['le'] . " " . $widget['periode_titre'] ;
                    break;
                case $multilangue[depuis_debut]:
                    $periode = strtolower($widget['periode_titre']) ;
                    break;
                case $multilangue[l_annee_derniere]:
                    $periode = strtolower($widget['periode_titre']) ;
                    break;
                case $multilangue[periode_precise]:
                    $periode .= strtolower($multilangue[du]) . " " .  $date_debut_titre . " " .  strtolower($multilangue[au]) . " " .  $date_fin_titre;
                    break;
                default :
                    $periode = $multilangue['sur_les'] . " " . $widget['periode_titre'] ;
                break;
            }
        }
        else{
            $periode .= "$multilangue[du] $date_debut_titre $multilangue[au] $date_fin_titre";
        }
        
        

        
        $tabStat = array();
        $i = 0;
        $widget_stats = $DB_site->getQueryResults("SELECT * FROM widget_stat INNER JOIN type_stat USING (type_statid) WHERE widgetid = '$widget[widgetid]' AND langueid = '1'");
        // On construit un tableau qui contient toutes les stats pour un widget
        
        $tzTitre = array();
        $tzLegend = array();
        $tzColorList = array(
                '#66B7F2',
                '#E9B516',
                '#7FCC7F'
        );
        foreach($widget_stats as $widget_stat ){
          
                $tempTitre = $widget_stat['libelle'];
                
                if( in_array($widget_stat['type_statid'], array("17","12")) ){
                    $widgetTemp = $DB_site->getQueryRow("SELECT * FROM widget WHERE widgetid = $widget_stat[widgetid]");
                    $oJsonDataOption = null ;
                    if( isset($widgetTemp['options']) && $widgetTemp['options'] != "" ){
                        $oJsonDataOption = HTools::jsonDecode($widgetTemp['options']);
                        if( is_object($oJsonDataOption) && isset($oJsonDataOption->niveau) ){
                            
                            if( isset($oJsonDataOption->display_type) ){    
                                switch( $oJsonDataOption->display_type ){
                                    case "niveau":
                                        $tempTitre = $tempTitre . ' ' . $multilangue['de_niveau'] . ' <span class="label label-info">' .  $oJsonDataOption->niveau  . '</span>';
                                    break;
                                    case "tree":
                                        
                                    break;

                                }
                            }
                            //$toCategoriesSelected
                        }
                    }
                    
                }
                
                $tzLegend[] = array(
                    'color' =>  $tzColorList[$i],
                    'title' => $widget_stat['libelle']
                );
                
                $tzTitre[] = $tempTitre ; 
                
                $tabStat[$i] = $widget_stat;
                $i++;
        }
		
        //---------------------
        // Titre pour les courbe
        //---------------------
        if( $widget['type_representation'] == "courbe" ){
            if( count($tzTitre) > 1 ){
                $iCount = 0 ;
                foreach( $tzTitre as &$zTitreTemp ){ 
                    $zTitreTemp = '<span style="color:' . $tzColorList[$iCount]  .'">'.  $zTitreTemp . '</span>' ;
                    $iCount ++ ;
                }
            }
            else{
                $tzTitre[0] = '<span style="color:' . $tzColorList[0]  .'">'.  $tzTitre[0] . '</span>' ;
            }
        }
        
        if( count($tzTitre) > 1 ){
            $titre =  implode(' <span>/</span> ' , $tzTitre );
        }
        else{
            $titre = $tzTitre[0] ;
        }
        
        if( !in_array($widget['type_representation'],array('total' , 'moyenne') ) ){
            $titre .= ' <span class="not_upper_case">' . $periode . '</span>' ;
        }
        
        $oJsonWidgetDataOption = new stdClass();
        $toCategoriesSelected = HTools::jsonEncode(array());
        
        if( $widget['options'] != "" ){
            $oJsonWidgetDataOption = HTools::jsonDecode($widget['options']);
            if( 
                isset($oJsonWidgetDataOption->display_type) && $oJsonWidgetDataOption->display_type == "tree"
                &&
                isset($oJsonWidgetDataOption->categories)  
            ){
                $oJsonWidgetDataOption = $oJsonWidgetDataOption->categories;
                $toCategoriesSelected = HTools::jsonEncode($oJsonWidgetDataOption);
                
            }
            //p($oJsonWidgetDataOption,true);
        }
        
        $tempWidget = $widget ;
        $tempWidget['options'] = HTools::jsonDecode($tempWidget['options']); 
        
        $dataJsonWidget = HTools::jsonEncode(array(
            "widget" => $tempWidget,
            "widget_stat" => $tabStat
        ));
        
        // titre du widget
        //$titre = substr($titre, 0, -1);
        
        $htmlWidgetOptionTypeDonnee1 = "" ;
        // Affichage données modification widget
        foreach( $type_stat_parents as $type_stat_parent ){
            $type_stats = $DB_site->getQueryResults("SELECT * FROM type_stat WHERE langueid = '$admin_langueid' AND typestatparentid = '$type_stat_parent[typestatparentid]'");
            $htmlWidgetOptionTypeDonnee1 .= "<optgroup label=" . $type_stat_parent[libelle] . ">";
            
            foreach( $type_stats as $stat ){
                if($stat[type_statid] == $tabStat[0]['type_statid']){
                    $selectedDonnee = "selected='selected'";
                }else{
                    $selectedDonnee = "";
                }
                $htmlWidgetOptionTypeDonnee1 .= '<option value="' . $stat[type_statid] . '" ' . $selectedDonnee . '>' . $stat[libelle] . '</option>';
            }
            $htmlWidgetOptionTypeDonnee1 .= '<optgroup>';
        }
                                
        $stats = $DB_site->getQueryResults("SELECT * FROM type_stat WHERE langueid = '$admin_langueid'");
        $listOptionTypeDonneeId = array(2,3);
        
        foreach($listOptionTypeDonneeId as $donneeId){
            foreach( $stats as $stat ){
                if($stat[type_statid] == 15){
                    $displayTypeRepresentation = "style='display: none;'";
                }else{
                    $displayTypeRepresentation = "";
                    if(($stat[type_statid] < 15 || $stat[type_statid] > 20) && $stat[type_statid] != 5 && ($stat[type_statid] < 11 || $stat[type_statid] > 13)){
                        if($stat[type_statid] == $tabStat[1]['type_statid']){
                            ${'selectedDonnee' . $donneeId} = "selected='selected'";
                        }else{
                            ${'selectedDonnee' . $donneeId} = "";
                        }
                        eval(charge_template($langue,$referencepage,"WidgetOptionTypeDonnee" . $donneeId . "Bit"));
                    }
                }
            }
        }
		
        $displayNbResultats = "";
        $displayDonnee2 = "";
        $displayDonnee3 = "";
        $displayPeriodePrec = "";
        $displayGranularite = "";
        
        $selectedCourbe = "";
        $selectedTableau = "";
        $selectedCamembert = "";
        $selectedMoyenne = "";
        $selectedTotal = "";
        /** 
         * @date 2015-10-08
         * 
         * This is pasted here to manage sorting field displaying 
         */
        $displayTri = "style='display: none;'";
        
        $disabledRepresentation = "";
        switch($widget[type_representation]){
            case "courbe":
                if ($tabStat[0]['type_statid'] == '15') {
                    $selectedCourbe = "selected='selected'";
                    $displayDonnee2 = "style='display: none;'";
                    $displayDonnee3 = "style='display: none;'";
                    $displayPeriodePrec = "style='display: none;'";
                    $displayNbResultats = "style='display: none;'";
                    $displayGranularite = "style='display: none;'";
                    $disabledRepresentation = "disabled";
                } else {
                    $selectedCourbe = "selected='selected'";
                    $displayDonnee3 = "style='display: none;'";
                    $displayPeriodePrec = "style='display: none;'";
                    $displayNbResultats = "style='display: none;'";    
                }
                
                $valeur_representation_modif = "courbe";
                break;
            case "tableau":
                $selectedTableau = "selected='selected'";
                $displayPeriodePrec = "style='display: none;'";
                $displayNbResultats = "style='display: none;'";
                $valeur_representation_modif = "tableau";
                break;
            case "camembert":
                $disabledRepresentation = "disabled";
                $selectedCamembert = "selected='selected'";
                $displayPeriodePrec = "style='display: none;'";
                $displayDonnee2 = "style='display: none;'";
                $displayDonnee3 = "style='display: none;'";
                $displayGranularite = "style='display: none;'";
                $valeur_representation_modif = "camembert";
                break;
            case "moyenne":
                $selectedMoyenne = "selected='selected'";
                $displayDonnee2 = "style='display: none;'";
                $displayDonnee3 = "style='display: none;'";
                $displayNbResultats = "style='display: none;'";
                $valeur_representation_modif = "moyenne";
                break;
            case "total":
                $selectedTotal = "selected='selected'";
                $displayDonnee2 = "style='display: none;'";
                $displayDonnee3 = "style='display: none;'";
                $displayNbResultats = "style='display: none;'";
                $displayGranularite = "style='display: none;'";
                $valeur_representation_modif = "total";
                break;
        }
        
        /** 
         * @date 2015-10-08
         * 
         * remove else statement because it cause needed fields to be hidden 
         */
        if($tabStat[0]['type_statid'] >= '16'){
            $displayTri = "";
            $displayNbResultats = "";
            $displayDonnee2 = "style='display: none;'";
            $displayDonnee3 = "style='display: none;'";
            $displayGranularite = "style='display: none;'";
        }
        
        if($tabStat[0]['type_statid'] == 5 || $tabStat[0]['type_statid'] == 10 || $tabStat[0]['type_statid'] == 11 || $tabStat[0]['type_statid'] == 12 ){
            $displayRepresentation = "";
        }else{
            $displayRepresentation = "style='display: none;'";
        }
        
        $selectedJour = "";
        $selectedMois = "";
        $selectedAnnee = "";
        switch($widget[granularite]){
            case "jour":
                $selectedJour = "selected='selected'";
                break;
            case "mois":
                $selectedMois = "selected='selected'";
                break;
            case "année":
                $selectedAnnee = "selected='selected'";
                break;
        }

        if($widget[periode_precedente] == 1){
            $checkedPeriodePrec = "checked";
        }else{
            $checkedPeriodePrec = "";
        }
        // Fin affichage donnees modification widget
        
        // Si il y a strictement plus d'1 stat dans le tableau $tabStat => forcement tableau ou courbe
        //=> traitement spécial pour savoir si les stats sont les mêmes ou non
        if(sizeof($tabStat) > 1){
            
            // Cas ou la représentation est une courbe
            if($widget[type_representation] == "courbe"){
                
                $placeholder .= "$.plot($('#placeholder$widget[widgetid]'), data$widget[widgetid], options$widget[widgetid]);";
                $dataStat = "var data$widget[widgetid] = [";
                $optionStat = "var options$widget[widgetid] = {
                                    series: {
                                        lines: { show: true },
                                        points: { show: true }
                                    },
                                    legend: {
                                        show: true
                                    },
                                    grid: {
                                        hoverable: true,
                                        clickable: true,
                                        borderWidth: 1,
                                        mouseActiveRadius: 5
                                    },
                                    xaxis: {
                                        ticks: [";
                $meme_stat = false;
                
                /**  antohny.webdatis [Couleur des courbes] **/
                $toAllDataStatCourbe = array();
                $tmDataStatCourbeJson = array();
                $tzDateStartEnd = array();
                
                // Boucle sur les stats du tableau $tabStat
                for($i=0;$i<sizeof($tabStat);$i++){
                    
                    if($tabStat[$i]['type_statid'] == 4){
                        $unite_courbe = "(%)";
                    }elseif($tabStat[$i]['type_statid'] == 7 || $tabStat[$i]['type_statid'] == 8 || $tabStat[$i]['type_statid'] == 9){
                        if($andSite == ""){
                            $unite_courbe = "(€)";
                        }else{
                            $unite_courbe = "(".$tabsites[$idsite][devise_complete].")";
                        }
                    }else{
                        $unite_courbe = "";
                    }
                    if($i != 0){
                        $index_prec = $i-1;
                        // Vérification si la stat est la même que la stat précédente
                        if($tabStat[$i]['type_statid'] == $tabStat[$index_prec]['type_statid'] && $widget[type_periode] != "debut"){
                            $meme_stat = true;
                            //Periode précedente en fonction du type de période
                            switch ($widget[type_periode]){
                                case "jour";
                                    $date = strtotime('-1 day', strtotime($date_debut_stat));
                                    $date_debut_stat = date('Y-m-d', $date);
                                    $date_fin_stat = $date_debut_stat;
                                break;
                                case "hier";
                                    $date_fin_stat = $date_debut_stat;
                                    $date = strtotime('-1 day', strtotime($date_debut_stat));
                                    $date_debut_stat = date('Y-m-d', $date);
                                    $date_fin_stat = $date_debut_stat;
                                break;
                                case "7jours";
                                    $date_fin_stat =  date('Y-m-d', strtotime('-1 day', strtotime($date_debut_stat)));
                                    $date = strtotime('-7 days', strtotime($date_debut_stat));
                                    $date_debut_stat = date('Y-m-d', $date);
                                    break;
                                case "30jours";
                                    $date_fin_stat =  date('Y-m-d', strtotime('-1 day', strtotime($date_debut_stat)));
                                    $date = strtotime('-30 days', strtotime($date_debut_stat));
                                    $date_debut_stat = date('Y-m-d', $date);
                                    break;
                                case "mois";
                                    $annee = date('Y', time());
                                    $mois = date('m',  strtotime('last month', strtotime($date_debut_stat)));
                                    $date_debut_stat = date('Y-m-d', strtotime('01-'.$mois.'-'.$annee));
                                    $date_fin_stat = date('Y-m-t', strtotime('01-'.$mois.'-'.$annee));
                                break;
                                case "mois_dernier";
                                    $annee = date('Y', time());
                                    $mois = date('m',  strtotime('last month', strtotime($date_debut_stat)));
                                    $date_debut_stat = date('Y-m-d', strtotime('01-'.$mois.'-'.$annee));
                                    $date_fin_stat = date('Y-m-t', strtotime('01-'.$mois.'-'.$annee));
                                break;
                                case "annee";
                                    $annee = date('Y', strtotime('last year', strtotime($date_debut_stat)));
                                    $date_debut_stat = date('Y-m-d', strtotime('01-01-'.$annee));
                                    $date_fin_stat = date('Y-m-t', strtotime('01-12-'.$annee));;
                                break;
                                case "annee_derniere";
                                    $annee = date('Y', strtotime('last year', strtotime($date_debut_stat)));
                                    $date_debut_stat = date('Y-m-d', strtotime('01-01-'.$annee));
                                    $date_fin_stat = date('Y-m-t', strtotime('01-12-'.$annee));;
                                break;
                                case "precise";
                                    $diffJour = ((strtotime($date_fin_stat)-strtotime($date_debut_stat))/86400);
                                    $date_fin_stat = $date_debut_stat;
                                    $date = strtotime('-'.$diffJour.' days', strtotime($date_debut_stat));
                                    $date_debut_stat = date('Y-m-d', $date);
                                break;
                            }
                            $dataStat .= '{label:"'.$tabStat[$i]['libelle'].' ('.$multilangue[periode_precedente].') '.$unite_courbe.'",';
                        }else{
                            $dataStat .= '{label:"'.$tabStat[$i]['libelle'].' '.$unite_courbe.'",';
                        }
                    }else{
                        $dataStat .= '{label:"'.$tabStat[$i]['libelle'].' '.$unite_courbe.'",';
                    }
                    // Requêtes de récupération des stats
                    switch ($tabStat[$i]['type_statid']){
                        case "1": // Nombre de sessions
                            $donnees = $DB_site->query("SELECT SUM(nb_sessions) AS stat, date FROM stat_audience WHERE date >= '$date_debut_stat' AND date <= '$date_fin_stat' $andSite GROUP BY date");
                            break;
                        case "2": // Nombre d'utilisateurs
                            $donnees = $DB_site->query("SELECT SUM(nb_utilisateurs) AS stat, date FROM stat_audience WHERE date >= '$date_debut_stat' AND date <= '$date_fin_stat' $andSite GROUP BY date");
                            break;
                        case "3": // Nombre de pages vues
                            $donnees = $DB_site->query("SELECT SUM(nb_pages_vues) AS stat, date FROM stat_audience WHERE date >= '$date_debut_stat' AND date <= '$date_fin_stat' $andSite GROUP BY date");
                            break;
                        case "4": // Taux de rebond
                            $donnees = $DB_site->query("SELECT ROUND(AVG(taux_rebond), 2) AS stat, date FROM stat_audience WHERE date >= '$date_debut_stat' AND date <= '$date_fin_stat' $andSite GROUP BY date");
                            break;
                        case "5": // Catégorie d'appareil
                            $donnees = $DB_site->query("SELECT SUM(sessions_desktop) AS ordinateur, SUM(sessions_mobile) AS mobile, SUM(sessions_tablette) AS tablette FROM stat_audience WHERE date >= '$date_debut_stat' AND date <= '$date_fin_stat' $andSite");
                                // rien car camembert
                            break;
                        case "6": // Nombre de commandes
                            $donnees = $DB_site->query("SELECT SUM(nb_commandes) AS stat, date FROM stat_facture WHERE date >= '$date_debut_stat' AND date <= '$date_fin_stat' $andSite GROUP BY date");
                            break;
                        case "7": // CA HT HFP
                            if($andSite == ""){
                                //$donnees = $DB_site->query("SELECT ROUND(SUM(ca_ttc / tauxdevise), 2) AS stat, date FROM stat_facture WHERE date >= '$date_debut_stat' AND date <= '$date_fin_stat' GROUP BY date");
                                $donnees = $DB_site->query("SELECT ROUND(SUM(ca_ht_hfp / tauxdevise), 2) AS stat, date FROM stat_facture WHERE date >= '$date_debut_stat' AND date <= '$date_fin_stat' GROUP BY date");
                            }else{
                                //$donnees = $DB_site->query("SELECT ROUND(SUM(ca_ttc), 2) AS stat, date FROM stat_facture WHERE date >= '$date_debut_stat' AND date <= '$date_fin_stat' $andSite GROUP BY date");
                                $donnees = $DB_site->query("SELECT ROUND(SUM(ca_ht_hfp), 2) AS stat, date FROM stat_facture WHERE date >= '$date_debut_stat' AND date <= '$date_fin_stat' $andSite GROUP BY date");
                            }                                                   
                            break;
                        case "8": // Panier moyen
                            if($andSite == ""){
                                $donnees = $DB_site->query("SELECT ROUND(AVG(panier_moyen / tauxdevise), 2) AS stat, date FROM stat_facture WHERE date >= '$date_debut_stat' AND date <= '$date_fin_stat' AND panier_moyen != '0' GROUP BY date");
                            }else{
                                $donnees = $DB_site->query("SELECT ROUND(AVG(panier_moyen), 2) AS stat, date FROM stat_facture WHERE date >= '$date_debut_stat' AND date <= '$date_fin_stat' AND panier_moyen != '0' $andSite GROUP BY date");
                            }
                            break;
                        case "9": // Frais de port HT
                            if($andSite == ""){
                                $donnees = $DB_site->query("SELECT ROUND(SUM(fp_ht / tauxdevise), 2) AS stat, date FROM stat_facture WHERE date >= '$date_debut_stat' AND date <= '$date_fin_stat'  GROUP BY date");
                            }else{
                                $donnees = $DB_site->query("SELECT ROUND(SUM(fp_ht), 2) AS stat, date FROM stat_facture WHERE date >= '$date_debut_stat' AND date <= '$date_fin_stat' $andSite GROUP BY date");
                            }
                            break;
                        case "10": // Nombre de comptes créés
                        
                            break;
                        case "11": // CA HT par moyen de paiement
                        
                            break;
                        case "12": // CA HT par catégorie
                        
                            break;
                        case "13": // CA HT par fournisseur
                        
                            break;
                        case "14": // Taux de conversion
                            $donnees = MStat::getTauxConversion($_GET['idsite'],$date_debut_stat,$date_fin_stat,false);
                            break;
                        case "15": // Tunnel de conversion
                        
                            break;
                        case "16": // Top articles
                        
                            break;
                        case "17": // Top catégories
                        
                            break;
                        case "18": // Top clients
                        
                            break;
                        case "19": // Top fournisseurs
                        
                            break;
                        case "20": // Top marques
                        
                            break;
                    }   
                    
                    $tzDateStartEnd[] = array(
                        'start' => $date_debut_stat,
                        'end' => $date_fin_stat
                    );
                    //$widget[granularite] = "mois" ;
                    switch ($widget[granularite]){
                        
                        case "jour":
                            $tabDate = array();
                            $dataStat .= "data:[ ";
                            $j = 0;
                            while($donnee = $DB_site->fetch_array($donnees)){
                                $dataStat .= "[$j, $donnee[stat]],";
                                
                                $date_stat = date('d/m', strtotime($donnee[date]));
                                
                                $tabDate[] = date('Y-m-d', strtotime($donnee[date]));
                                
                                if($meme_stat){
                                    $optionStat .= "[$j, '<br>$date_stat'],";
                                }else{
                                    $optionStat .= "[$j, '$date_stat'],";
                                }
                                //added by anthony 
                                $tmDataStatCourbeJson['data_' . $i][] = $donnee ;
                                
                                $j++;
                            }
                            $dataStat = substr($dataStat, 0, -1);
                            $dataStat .= "]},";
                            break;
                        case "mois":
                            $tabDate = array();
                            $tabNbElements = array();
                            while($donnee = $DB_site->fetch_array($donnees)){
                                $date_stat = date('m/Y', strtotime($donnee[date]));
                                $existe = false;
                                foreach($tabDate as $indexDate => $somme_donnee){
                                    if($indexDate == $date_stat){
                                        $ancienne_stat = $tabDate[$indexDate];
                                        $nouvelle_stat = $ancienne_stat + $donnee[stat];
                                        $tabDate[$indexDate] = $nouvelle_stat;
                                        $tabNbElements[$indexDate] = $tabNbElements[$indexDate] + 1;
                                        $existe = true;
                                        break;
                                    }
                                }
                                if(!$existe){
                                    $tabDate[$date_stat] = $donnee[stat];
                                    $tabNbElements[$date_stat] = 1;
                                }
                            }
                            $j = 0;
                            $dataStat .= "data:[ ";
                            foreach($tabDate as $indexDate => $somme_donnee){
                                 $somme_donneeNotFormated = $somme_donnee ;
                                if($tabNbElements[$indexDate] != 0){
                                    $somme_donnee = $somme_donnee / $tabNbElements[$indexDate];
                                    $somme_donneeNotFormated = $somme_donnee ;
                                    $somme_donnee = formaterPrix($somme_donnee,"2", ".", " ");
                                }
                                $dataStat .= "[$j, '$somme_donnee'],";
                                if($meme_stat){
                                    $optionStat .= "[$j, '<br>" . str_replace("-","/",$indexDate). "'],";
                                }else{
                                    $optionStat .= "[$j, '" . str_replace("-","/",$indexDate). "'],";
                                }
                                $j++;
                                
                                $tmDataStatCourbeJson['data_' . $i][] = array(
                                    'date' => $indexDate,
                                    'stat' => round($somme_donneeNotFormated,0)
                                ) ;
                            }
                            $dataStat = substr($dataStat, 0, -1);
                            $dataStat .= "]},";
                            
                            
                            if( $widget['widgetid'] == "539" ){
                                // p($tabDate);
                                // p($tmDataStatCourbeJson);
                                // d($dataStat);
                            }
                            break;
                        case "année":
                            $tabDate = array();
                            $tabNbElements = array();
                            while($donnee = $DB_site->fetch_array($donnees)){
                                $date_stat = date('Y', strtotime($donnee[date]));
                                $existe = false;
                                foreach($tabDate as $indexDate => $somme_donnee){
                                    if($indexDate == $date_stat){
                                        $ancienne_stat = $tabDate[$indexDate];
                                        $nouvelle_stat = $ancienne_stat + $donnee[stat];
                                        $tabDate[$indexDate] = $nouvelle_stat;
                                        $tabNbElements[$indexDate] = $tabNbElements[$indexDate] + 1;
                                        $existe = true;
                                        break;
                                    }
                                }
                                if(!$existe){
                                    $tabDate[$date_stat] = $donnee[stat];
                                    $tabNbElements[$date_stat] = 1;
                                }
                            }
                            $j = 0;
                            $dataStat .= "data:[ ";
                            foreach($tabDate as $indexDate => $somme_donnee){
                                $somme_donneeNotFormated = $somme_donnee ; 
                                if($tabNbElements[$indexDate] != 0){
                                    $somme_donnee = $somme_donnee / $tabNbElements[$indexDate];
                                    $somme_donneeNotFormated = $somme_donnee ;
                                    $somme_donnee = formaterPrix($somme_donnee,"2", ".", " ");
                                     
                                }
                                $dataStat .= "[$j, '$somme_donnee'],";
                                if($meme_stat){
                                    $optionStat .= "[$j, '<br>" . str_replace("-","/",$indexDate). "'],";
                                }else{
                                    $optionStat .= "[$j, '$indexDate'],";
                                    $optionStat .= "[$j, '" . str_replace("-","/",$indexDate). "'],";
                                }
                                $j++;
                                
                                $tmDataStatCourbeJson['data_' . $i][] = array(
                                    'date' => $indexDate,
                                    'stat' => round($somme_donneeNotFormated,0)
                                ) ;
                            }
                            $dataStat = substr($dataStat, 0, -1);
                            $dataStat .= "]},";
                            break;
                        default:
                            break;
                    }
                } // Fin for $tabStat
                
                $dataStat = substr($dataStat, 0, -1);
                
                $dataStat .= "];\n";
                $data .= $dataStat;
                
                $optionStat = substr($optionStat, 0, -1);
                $optionStat .= "]}};";
                $options .= $optionStat;
                
                $tmDataNew = array();
                $toAmchartGraphs = array();
                
                if( !empty($tmDataStatCourbeJson) ){
                    $tzDateTemp = array();
                    $iCount = 0 ;
                    $toAmchartValueAxis = array();
                    
                    
                    // stat xy 
                    if( $meme_stat ){
                        $tmAmchartValueAxis[] = array(
                            "id"        => "v1",
                            "axisAlpha" => 0
                        );
                    } else { //stat serial
                        $tmAmchartValueAxis[] = array(
                            "autoGridCount" => true,
                            "axisAlpha" => 0
                        );
                    }
                    $iCountTemp = 1 ;
                    for ( $i = 0 ; $i < sizeof($tabStat) ; $i++ ){
                        
                        $iCountTemp ++ ;
                        
                        $tmDataTemp = $tmDataStatCourbeJson['data_' . $i ] ;
                        
                        //donnees differentes
                        if( !$meme_stat ){
                    if(is_array($tmDataTemp)){
                        foreach( $tmDataTemp as $mDataTemp ){
                            if( !in_array($mDataTemp['date'],$tzDateTemp) ){
                                $tzDateTemp[] = $mDataTemp['date'] ;
                                $mDataTemp['dataStat_' .$i ] = $mDataTemp['stat'] ;
                                
                                $tmDataNew[] = $mDataTemp ;
                            }
                            else{ 
                                foreach($tmDataNew as &$mDataNew){
                                    if( $mDataNew['date'] == $mDataTemp['date'] ){
                                        $mDataNew['dataStat_' . $i] = $mDataTemp['stat'] ;
                                        break ; 
                                    }
                                }
                            }
                        }
                    }
                            
                            $toAmchartGraphs[] = array(
                                "id" => "graph_" . $i,
                                "bullet" => "square",
                                "bulletBorderAlpha" => 1,
                                "bulletBorderThickness" => 1,
                                "fillAlphas" => 0.3,
                                "fillColorsField" => "lineColor_$i",
                                "legendValueText" => "[[value]]",
                                "lineColorField" => "lineColor_$i",
                                "title" => $tabStat[$i]['libelle'],
                                "dateFormat" => "DD-MM-YYYY",
                                "valueField" => "dataStat_" . $i,
                                "balloonText" => "<div style='margin:5px;'><b>[[title]]</b><br>Date : <b>[[category]]</b><br>Stat : <b>[[value]]</b></div>",
                                'balloonFunctionx' => '[balloonFunction]'
                            );
                        } else { //meme stat
                            foreach( $tmDataTemp as $mDataTemp ){
                                $mDataTemp['dataStat_' .$i ] = $mDataTemp['stat'] ;
                                $mDataTemp['lineColor_' . $i] = $tzColorList[$i] ;
                                $tmDataNew[] = $mDataTemp ;
                            }
                            
                            $toAmchartGraphs[] = array(
                                "bullet"    => "square",
                                "lineAlpha" => 1,
                                "bulletBorderAlpha" => 1,
                                "bulletBorderThickness" => 1,
                                "fillAlphas" => 0.3,
                                "fillColorsField" => "lineColor_$i",
                                "legendValueText" => "[[value]]",
                                "lineColorField" => "lineColor_$i",
                                "title"     => $tabStat[$i]['libelle'],
                                "xField"    => "date",
                                "yField"    => "dataStat_" . $i,
                                "xAxis"     => "v$iCountTemp" ,
                                "dateFormat" => "DD MMM",
                                "balloonText" => "<div style='margin:5px;'><b>[[title]]</b><br>Date : <b>[[x]]</b><br>Stat : <b>[[y]]</b></div>",
                                "fillToAxis" => "v2",
                                "balloonFunctionx" => "[balloonFunction]"
                            );
                            
                            $tmAmchartValueAxis[] = array(
                                "id"        => "v" . $iCountTemp,
                                "axisAlpha" => 0,
                                "type"      => "date",
                                "minimumDate" => $tzDateStartEnd[$i]['start'],
                                "maximumDate" => $tzDateStartEnd[$i]['end'],
                                "dateFormats" => array(
                                        array('period' => "DD","format" => "DD MMM"),
                                        array('period' => "WW","format" => "DD MMM"),
                                        array('period' => "DD","format" => "DD MMM"),
                                        array('period' => "YYYY","format" => "YYYY")
                                )
                            );
                        }
                    }
                }
                
                
                $zBalloonDateFormat = "DD MMM YYYY";
                $zCourbeDateFormat = "";
                switch( $widget['granularite'] ){
                    case "jour":
                        $zCourbeDateFormat = "YYYY-MM-DD" ;
                        $zBalloonDateFormat = "DD MMM YYYY";
                    break;
                    case "mois":
                        $zCourbeDateFormat = "MM-YYYY" ;
                        $zBalloonDateFormat = "MMM YYYY";
                    break;
                    case "année":
                        $zCourbeDateFormat = "YYYY" ;
                        $zBalloonDateFormat = "YYYY";
                    break;
                }
                
                $zDataChartCategory = "" ;
                
                $zChartDataType = "serial" ;
                
                if( !$meme_stat ){
                    foreach( $tmDataNew as &$mDataNew ){
                        foreach( $mDataNew as $keyTemp=>$valTemp ){
                            if( is_int($keyTemp) ){
                                unset($mDataNew[$keyTemp]);
                            }
                        }
                        for( $i = 0 ; $i < sizeof($tabStat) ; $i++ ){
                            $mDataNew['lineColor_' . $i ] = $tzColorList[$i];
                        }
                    }
                    
                    $zDataChartCategory = '
                        "categoryField": "date",
                        "categoryAxis": {
                                "dateFormats": [
                                    {"period": "DD","format": "DD MMM"}, 
                                    {"period": "WW","format": "DD MMM"},
                                    {"period": "MM","format": "DD MMM"},
                                    {"period": "YYYY","format": "YYYY"}
                                ],
                                "parseDates": true,
                                "autoGridCount": false,
                                "axisColor": "#555555",
                                "gridAlpha": 0,
                                "gridCount": 50
                        }';
                }
                else{
                    $zChartDataType = "xy" ;
                    $tmAmchartValueAxis[1]['position'] = 'bottom' ;
                    $tmAmchartValueAxis[2]['position'] = 'top' ;
                }
                
                $zDataValueAxis = HTools::jsonEncode($tmAmchartValueAxis);
                $zDataChartsGraphs = HTools::jsonEncode($toAmchartGraphs);
                
                /**
                 * @todo ballonFunction pour calculer le taux
                 * 
                $zDataChartsGraphs = str_replace(
                        '"[balloonFunction]"',
                        "function( graphDataItem, graph ){
                            var zDate = moment(graphDataItem.category).format('dddd DD MMM. YYYY');
                            var fValue = graphDataItem.values.value ;
                            var zBgColor = graphDataItem.fillColors ;
                            console.log(zBgColor);
                            
                            var zHtml = '<div style=\"backgroundx:#333333\">' +
                                '<span style=\"color:#333333\">' + zDate + ' : </span> <span>' + fValue + '</span> <span style=\"padding:4px;border:1px solid #fff;background:\"' + zBgColor + ';\"></span><br/> ' +
                                '<span style=\"color:#333333\">' + zDate + ' : </span> <span>' + fValue + '</span><br/> ' +
                            '</div>';
                            
                            console.log(graphDataItem);
                            //console.log(graph);
                            return zHtml ;
                        }",$zDataChartsGraphs);
                */
                
                $tmChartData = array(
                    'type'              => $zChartDataType,
                    'graphs'            => $zDataChartsGraphs,
                    'dataProvider'      => HTools::jsonEncode($tmDataNew),
                    'legend'            => HTools::jsonEncode($tzLegend),
                    //"numberFormatter"   => 
                    'dataDateFormat'    => $zCourbeDateFormat ,
                    'valueAxes'         => $zDataValueAxis,
                    'categoriesData'    => $zDataChartCategory ,
                );
                if($widget['widgetid'] == 555){
                    //p( $tzDateStartEnd );
                    //p($tmAmchartValueAxis);
                    //d($tmDataNew);
                    //p($widget);
                    //d($tmDataNew);
                }
                eval(charge_template($langue,$referencepage,"WidgetBitCourbe"));
                
            // Type de représentation = tableau
            }else{
                $tabTableau = array();
                $tabDate = array();
                $tabNbElements = array();
                $cpt_tabDate = 0;
                // Boucle sur les stats du tableau $tabStat
                for($i=0;$i<sizeof($tabStat);$i++){
                    if($i != 0){
                        if($i == 1){
                            // Vérification si la stat est la même que la 1ere stat
                            if($tabStat[$i]['type_statid'] == $tabStat[0]['type_statid']){
                                //Periode précedente
                                switch ($widget[type_periode]){
                                    case "jour";
                                        $date = strtotime('-1 day', strtotime($date_debut_stat));
                                        $date_debut_stat = date('Y-m-d', $date);
                                        $date_fin_stat = $date_debut_stat;
                                        break;
                                    case "hier";
                                        $date_fin_stat = $date_debut_stat;
                                        $date = strtotime('-1 day', strtotime($date_debut_stat));
                                        $date_debut_stat = date('Y-m-d', $date);
                                        $date_fin_stat = $date_debut_stat;
                                        break;
                                    case "7jours";
                                        $date_fin_stat =  date('Y-m-d', strtotime('-1 day', strtotime($date_debut_stat)));
                                        $date = strtotime('-7 days', strtotime($date_debut_stat));
                                        $date_debut_stat = date('Y-m-d', $date);
                                        break;
                                    case "30jours";
                                        $date_fin_stat =  date('Y-m-d', strtotime('-1 day', strtotime($date_debut_stat)));
                                        $date = strtotime('-30 days', strtotime($date_debut_stat));
                                        $date_debut_stat = date('Y-m-d', $date);
                                        break;
                                    case "mois";
                                        $annee = date('Y', time());
                                        $mois = date('m',  strtotime('last month', strtotime($date_debut_stat)));
                                        $date_debut_stat = date('Y-m-d', strtotime('01-'.$mois.'-'.$annee));
                                        $date_fin_stat = date('Y-m-t', strtotime('01-'.$mois.'-'.$annee));
                                        break;
                                    case "mois_dernier";
                                        $annee = date('Y', time());
                                        $mois = date('m',  strtotime('last month', strtotime($date_debut_stat)));
                                        $date_debut_stat = date('Y-m-d', strtotime('01-'.$mois.'-'.$annee));
                                        $date_fin_stat = date('Y-m-t', strtotime('01-'.$mois.'-'.$annee));
                                        break;
                                    case "annee";
                                        $annee = date('Y', strtotime('last year', strtotime($date_debut_stat)));
                                        $date_debut_stat = date('Y-m-d', strtotime('01-01-'.$annee));
                                        $date_fin_stat = date('Y-m-t', strtotime('01-12-'.$annee));;
                                        break;
                                    case "annee_derniere";
                                        $annee = date('Y', strtotime('last year', strtotime($date_debut_stat)));
                                        $date_debut_stat = date('Y-m-d', strtotime('01-01-'.$annee));
                                        $date_fin_stat = date('Y-m-t', strtotime('01-12-'.$annee));;
                                        break;
                                    case "precise";
                                        $diffJour = ((strtotime($date_fin_stat)-strtotime($date_debut_stat))/86400);
                                        $date_fin_stat = $date_debut_stat;
                                        $date = strtotime('-'.$diffJour.' days', strtotime($date_debut_stat));
                                        $date_debut_stat = date('Y-m-d', $date);
                                        break;
                                }
                                // Colonne pour la date
                                $colonne = "$multilangue[date]";
                                eval(charge_template($langue,$referencepage,"WidgetBitTableauColonneBit"));
                                    
                                // Colonne avec le nom de la donnee
                                $colonne = $tabStat[1]['libelle']." ($multilangue[periode_precedente])";
                                eval(charge_template($langue,$referencepage,"WidgetBitTableauColonneBit"));
                            }else{
                                // Colonne avec le nom de la donnee
                                $colonne = $tabStat[1]['libelle'];
                                eval(charge_template($langue,$referencepage,"WidgetBitTableauColonneBit"));
                            }
                        }elseif($i == 2){
                            // Vérification si la stat est la même que les stats précédentes
                            // Les 3 stats sont différentes, on ne change pas la période
                            if($tabStat[2]['type_statid'] != $tabStat[0]['type_statid'] && $tabStat[2]['type_statid'] != $tabStat[1]['type_statid']){
                                if($tabStat[0]['type_statid'] == $tabStat[1]['type_statid']){
                                    // Colonne pour la date
                                    $colonne = "$multilangue[date]";
                                    eval(charge_template($langue,$referencepage,"WidgetBitTableauColonneBit"));
                                        
                                    // Colonne avec le nom de la donnee
                                    $colonne = $tabStat[2]['libelle'];
                                    eval(charge_template($langue,$referencepage,"WidgetBitTableauColonneBit"));
                                }else{
                                    // Colonne avec le nom de la donnee
                                    $colonne = $tabStat[2]['libelle'];
                                    eval(charge_template($langue,$referencepage,"WidgetBitTableauColonneBit"));
                                }
                                $date_debut_stat = $date_debut_donnee1;
                                $date_fin_stat = $date_fin_donnee1;
                            }else{
                                switch ($widget[type_periode]){
                                    case "jour";
                                        $date = strtotime('-1 day', strtotime($date_debut_stat));
                                        $date_debut_stat = date('Y-m-d', $date);
                                        $date_fin_stat = $date_debut_stat;
                                        break;
                                    case "hier";
                                        $date_fin_stat = $date_debut_stat;
                                        $date = strtotime('-1 day', strtotime($date_debut_stat));
                                        $date_debut_stat = date('Y-m-d', $date);
                                        $date_fin_stat = $date_debut_stat;
                                        break;
                                    case "7jours";
                                        $date_fin_stat =  date('Y-m-d', strtotime('-1 day', strtotime($date_debut_stat)));
                                        $date = strtotime('-7 days', strtotime($date_debut_stat));
                                        $date_debut_stat = date('Y-m-d', $date);
                                        break;
                                    case "30jours";
                                        $date_fin_stat =  date('Y-m-d', strtotime('-1 day', strtotime($date_debut_stat)));
                                        $date = strtotime('-30 days', strtotime($date_debut_stat));
                                        $date_debut_stat = date('Y-m-d', $date);
                                        break;
                                    case "mois";
                                        $annee = date('Y', time());
                                        $mois = date('m',  strtotime('last month', strtotime($date_debut_stat)));
                                        $date_debut_stat = date('Y-m-d', strtotime('01-'.$mois.'-'.$annee));
                                        $date_fin_stat = date('Y-m-t', strtotime('01-'.$mois.'-'.$annee));
                                        break;
                                    case "mois_dernier";
                                        $annee = date('Y', time());
                                        $mois = date('m',  strtotime('last month', strtotime($date_debut_stat)));
                                        $date_debut_stat = date('Y-m-d', strtotime('01-'.$mois.'-'.$annee));
                                        $date_fin_stat = date('Y-m-t', strtotime('01-'.$mois.'-'.$annee));
                                        break;
                                    case "annee";
                                        $annee = date('Y', strtotime('last year', strtotime($date_debut_stat)));
                                        $date_debut_stat = date('Y-m-d', strtotime('01-01-'.$annee));
                                        $date_fin_stat = date('Y-m-t', strtotime('01-12-'.$annee));;
                                        break;
                                    case "annee_derniere";
                                        $annee = date('Y', strtotime('last year', strtotime($date_debut_stat)));
                                        $date_debut_stat = date('Y-m-d', strtotime('01-01-'.$annee));
                                        $date_fin_stat = date('Y-m-t', strtotime('01-12-'.$annee));;
                                        break;
                                    case "precise";
                                        $diffJour = ((strtotime($date_fin_stat)-strtotime($date_debut_stat))/86400);
                                        $date_fin_stat = $date_debut_stat;
                                        $date = strtotime('-'.$diffJour.' days', strtotime($date_debut_stat));
                                        $date_debut_stat = date('Y-m-d', $date);
                                        break;
                                }
                                
                                // Colonne pour la date
                                $colonne = "$multilangue[date]";
                                eval(charge_template($langue,$referencepage,"WidgetBitTableauColonneBit"));
                                    
                                // Colonne avec le nom de la donnee
                                $colonne = $tabStat[2]['libelle']." ($multilangue[periode_precedente])";
                                eval(charge_template($langue,$referencepage,"WidgetBitTableauColonneBit"));
                            }
                        }
                    }else{
                        // Colonne pour la date
                        $colonne = "$multilangue[date]";
                        eval(charge_template($langue,$referencepage,"WidgetBitTableauColonneBit"));
                            
                        // Colonne avec le nom de la donnee
                        $colonne = $tabStat[0]['libelle'];
                        eval(charge_template($langue,$referencepage,"WidgetBitTableauColonneBit"));
                        
                        $date_debut_donnee1 = $date_debut_stat;
                        $date_fin_donnee1 = $date_fin_stat;
                    }

                    // Requêtes de récupération des stats
                    switch ($tabStat[$i]['type_statid']){
                        case "1":
                            $donnees = $DB_site->query("SELECT SUM(nb_sessions) AS stat, date FROM stat_audience WHERE date >= '$date_debut_stat' AND date <= '$date_fin_stat' $andSite GROUP BY date");
                            break;
                        case "2":
                            $donnees = $DB_site->query("SELECT SUM(nb_utilisateurs) AS stat, date FROM stat_audience WHERE date >= '$date_debut_stat' AND date <= '$date_fin_stat' $andSite GROUP BY date");
                            break;
                        case "3":
                            $donnees = $DB_site->query("SELECT SUM(nb_pages_vues) AS stat, date FROM stat_audience WHERE date >= '$date_debut_stat' AND date <= '$date_fin_stat' $andSite GROUP BY date");
                            break;
                        case "4":
                            $donnees = $DB_site->query("SELECT ROUND( AVG(taux_rebond), 2) AS stat, date FROM stat_audience WHERE date >= '$date_debut_stat' AND date <= '$date_fin_stat' $andSite GROUP BY date");
                            break;
                        case "5":
                            break;
                        case "6":
                            $donnees = $DB_site->query("SELECT SUM(nb_commandes) AS stat, date FROM stat_facture WHERE date >= '$date_debut_stat' AND date <= '$date_fin_stat' $andSite GROUP BY date");
                            break;
                        case "7":
                            if($andSite == ""){
                                //$donnees = $DB_site->query("SELECT ROUND(SUM(ca_ttc / tauxdevise), 2) AS stat, date FROM stat_facture WHERE date >= '$date_debut_stat' AND date <= '$date_fin_stat' GROUP BY date");
                                $donnees = $DB_site->query("SELECT ROUND(SUM(ca_ht_hfp / tauxdevise), 2) AS stat, date FROM stat_facture WHERE date >= '$date_debut_stat' AND date <= '$date_fin_stat' GROUP BY date");
                            }else{
                                //$donnees = $DB_site->query("SELECT ROUND(SUM(ca_ttc), 2) AS stat, date FROM stat_facture WHERE date >= '$date_debut_stat' AND date <= '$date_fin_stat' $andSite GROUP BY date");
                                $donnees = $DB_site->query("SELECT ROUND(SUM(ca_ht_hfp), 2) AS stat, date FROM stat_facture WHERE date >= '$date_debut_stat' AND date <= '$date_fin_stat' $andSite GROUP BY date");
                            }
                            
                            break;
                        case "8":
                            if($andSite == ""){
                                $donnees = $DB_site->query("SELECT ROUND(AVG(panier_moyen / tauxdevise), 2) AS stat, date FROM stat_facture WHERE date >= '$date_debut_stat' AND date <= '$date_fin_stat' AND panier_moyen != '0' GROUP BY date");
                            }else{
                                $donnees = $DB_site->query("SELECT ROUND(AVG(panier_moyen), 2) AS stat, date FROM stat_facture WHERE date >= '$date_debut_stat' AND date <= '$date_fin_stat' AND panier_moyen != '0' $andSite GROUP BY date");
                            }
                            break;
                        case "9":
                            if($andSite == ""){
                                $donnees = $DB_site->query("SELECT ROUND(SUM(fp_ht / tauxdevise), 2) AS stat, date FROM stat_facture WHERE date >= '$date_debut_stat' AND date <= '$date_fin_stat'  GROUP BY date");
                            }else{
                                $donnees = $DB_site->query("SELECT ROUND(SUM(fp_ht), 2) AS stat, date FROM stat_facture WHERE date >= '$date_debut_stat' AND date <= '$date_fin_stat' $andSite GROUP BY date");
                            }
                            break;
                        case "10":
                            break;
                        case "11":
                            break;
                        case "12":
                            break;
                        case "13":
                            break;
                        case "14":
                            $donnees = MStat::getTauxConversion($_GET['idsite'],$date_debut_stat,$date_fin_stat,false);
                            break;
                        case "15":
                            break;
                        case "16":
                            break;
                        case "17":
                            break;
                        case "18":
                            break;
                        case "19":
                            break;
                        case "20":
                            break;
                    }
                    switch ($widget[granularite]){
                        case "jour":
                            $date_stat = $date_debut_stat;
                            while(strtotime($date_stat) <= strtotime($date_fin_stat)){
                                $index = date('d/m', strtotime($date_stat));
                                $tabDate[$cpt_tabDate][$index] = 0;
                                if($tabStat[$i]['type_statid'] == '4' || $tabStat[$i]['type_statid'] == '8' || $tabStat[$i]['type_statid'] == '14'){
                                    $tabNbElements[$cpt_tabDate][$index] = 0;
                                }
                                $date = strtotime('+1 day', strtotime($date_stat));
                                $date_stat = date('Y-m-d', $date);
                            }
                            while($donnee = $DB_site->fetch_array($donnees)){
                                $passe = false;
                                $date_stat = date('d/m', strtotime($donnee[date]));
                                foreach($tabDate as $key){
                                    foreach($key as $indexDate => $somme_donnee){
                                        if($indexDate == $date_stat){
                                            if(!$passe){
                                                if($tabStat[$i]['type_statid'] == '4' || $tabStat[$i]['type_statid'] == '8' || $tabStat[$i]['type_statid'] == '14'){
                                                    $tabNbElements[$cpt_tabDate][$indexDate] = $tabNbElements[$cpt_tabDate][$indexDate] + 1;
                                                    $passe = true;
                                                }
                                            }
                                            $nouvelle_stat = $somme_donnee + $donnee[stat];
                                            $tabDate[$cpt_tabDate][$indexDate] = $nouvelle_stat;
                                        }
                                    }
                                }
                            }
                            $cpt_tabDate++;
                            break;
                        case "mois":
                            $date_stat = $date_debut_stat;
                            while(strtotime($date_stat) <= strtotime($date_fin_stat)){
                                $index = date('m/Y', strtotime($date_stat));
                                $tabDate[$cpt_tabDate][$index] = 0;
                                if($tabStat[$i]['type_statid'] == '4' || $tabStat[$i]['type_statid'] == '8' || $tabStat[$i]['type_statid'] == '14'){
                                    $tabNbElements[$cpt_tabDate][$index] = 0;
                                }
                                $date = strtotime('+1 month', strtotime($date_stat));
                                $date_stat = date('Y-m-d', $date);
                            }
                            while($donnee = $DB_site->fetch_array($donnees)){
                                $passe = false;
                                $date_stat = date('m/Y', strtotime($donnee[date]));
                                foreach($tabDate as $key){
                                    foreach($key as $indexDate => $somme_donnee){
                                        if($indexDate == $date_stat){
                                            if(!$passe){
                                                if($tabStat[$i]['type_statid'] == '4' || $tabStat[$i]['type_statid'] == '8' || $tabStat[$i]['type_statid'] == '14'){
                                                    $tabNbElements[$cpt_tabDate][$indexDate] = $tabNbElements[$cpt_tabDate][$indexDate] + 1;
                                                    $passe = true;
                                                }
                                            }
                                            $nouvelle_stat = $somme_donnee + $donnee[stat];
                                            $tabDate[$cpt_tabDate][$indexDate] = $nouvelle_stat;
                                        }
                                    }
                                }
                            }
                            $cpt_tabDate++;
                            break;
                        case "année":
                            $date_stat = $date_debut_stat;
                            while(strtotime($date_stat) <= strtotime($date_fin_stat)){
                                $index = date('Y', strtotime($date_stat));
                                $tabDate[$cpt_tabDate][$index] = 0;
                                if($tabStat[$i]['type_statid'] == '4' || $tabStat[$i]['type_statid'] == '8' || $tabStat[$i]['type_statid'] == '14'){
                                    $tabNbElements[$cpt_tabDate][$index] = 0;
                                }
                                $date = strtotime('+1 year', strtotime($date_stat));
                                $date_stat = date('Y-m-d', $date);
                            }
                            while($donnee = $DB_site->fetch_array($donnees)){
                                $passe = false;
                                $date_stat = date('Y', strtotime($donnee[date]));
                                foreach($tabDate as $key){
                                    foreach($key as $indexDate => $somme_donnee){
                                        if($indexDate == $date_stat){
                                            if(!passe){
                                                if($tabStat[$i]['type_statid'] == '4' || $tabStat[$i]['type_statid'] == '8' || $tabStat[$i]['type_statid'] == '14'){
                                                    $tabNbElements[$cpt_tabDate][$indexDate] = $tabNbElements[$cpt_tabDate][$indexDate] + 1;
                                                    $passe = true;
                                                }
                                            }
                                            $nouvelle_stat = $somme_donnee + $donnee[stat];
                                            $tabDate[$cpt_tabDate][$indexDate] = $nouvelle_stat;
                                        }
                                    }
                                }
                            }
                            $cpt_tabDate++;
                            break;
                        default:
                            break;
                    }
                }
                $j = 0;
                foreach($tabDate as $key){
                    foreach($key as $indexDate => $somme_donnee){
                        if(sizeof($tabNbElements[$j]) != 0){
                            if($somme_donnee != 0 ){
                                $tabDate[$j][$indexDate] = $tabDate[$j][$indexDate] / $tabNbElements[$j][$indexDate];
                                $tabDate[$j][$indexDate] = formaterPrix($tabDate[$j][$indexDate]);
                            }
                        }
                        
                        if($tabStat[$j]['type_statid'] == 4){
                            $tabDate[$j][$indexDate] = $tabDate[$j][$indexDate].' %';
                        }elseif($tabStat[$j]['type_statid'] == 7 || $tabStat[$j]['type_statid'] == 8 || $tabStat[$j]['type_statid'] == 9){
                            if($andSite == ""){
                                $tabDate[$j][$indexDate] = formaterPrix($tabDate[$j][$indexDate]).' €';
                            }else{
                                $tabDate[$j][$indexDate] = formaterPrix($tabDate[$j][$indexDate])." ".$tabsites[$idsite][devise_complete];
                            }
                        }
                    }
                    $j++;
                }
                // Insertion des lignes vides dans tabDate
                $nb_lignes = 0;
                $index_nb_lignes_max = 0;
                for($indexTabDate=0;$indexTabDate<sizeof($tabDate);$indexTabDate++){
                    if(sizeof($tabDate[$indexTabDate])>$nb_lignes){
                        $nb_lignes = sizeof($tabDate[$indexTabDate]);
                        $index_nb_lignes_max = $indexTabDate;
                    }
                }
                
                for($indexTabDate=0;$indexTabDate<sizeof($tabDate);$indexTabDate++){
                    if(sizeof($tabDate[$indexTabDate])<$nb_lignes){
                        reset($tabDate[$indexTabDate]);
                        $first_index = key($tabDate[$indexTabDate]);
                        $first_index = explode('-',$first_index);
                        reset($tabDate[$index_nb_lignes_max]);
                        $first_index_prec = key($tabDate[$index_nb_lignes_max]);
                        $first_index_prec = explode('-',$first_index_prec);
                        if($first_index[0] > $first_index_prec[0]){
                            $insertion = "avant";
                        }else{
                            $insertion = "apres";   
                        }
                        $nb_new_ligne = sizeof($tabDate[$index_nb_lignes_max]) - sizeof($tabDate[$indexTabDate]);
                        for($j=0;$j<$nb_new_ligne;$j++){
                            if($insertion == "avant"){
                                array_unshift($tabDate[$indexTabDate] ," ");
                            }else{
                                $tabDate[$indexTabDate][] = " ";
                            }
                        }
                    }
                }
                
                foreach($tabDate as $tab){
                    $j = 0;
                    foreach($tab as $indexDate => $somme_donnee){
                        if(is_numeric($indexDate)){
                            $indexDate = " ";
                        }
                        
                        if($tabStat[2]['type_statid'] != ""){
                            // 3Stats
                            if($tabStat[2]['type_statid'] != $tabStat[0]['type_statid'] && $tabStat[2]['type_statid'] != $tabStat[1]['type_statid']){
                                if($tabStat[0]['type_statid'] != $tabStat[1]['type_statid']){
                                    if($tabTableau[$j][0] != ""){
                                        $tabTableau[$j][] = $somme_donnee;
                                    }else{
                                        $tabTableau[$j][] = $indexDate;
                                        $tabTableau[$j][] = $somme_donnee;
                                    }
                                }else{
                                    $tabTableau[$j][] = $indexDate;
                                    $tabTableau[$j][] = $somme_donnee;
                                }
                            }elseif($tabStat[0]['type_statid'] != $tabStat[1]['type_statid'] && $tabStat[2]['type_statid'] != $tabStat[1]['type_statid']){
                                if(sizeof($tabTableau[$j]) == 0 || sizeof($tabTableau[$j]) == 3){
                                    $tabTableau[$j][] = $indexDate;
                                    $tabTableau[$j][] = $somme_donnee;
                                }else{
                                    $tabTableau[$j][] = $somme_donnee;
                                }
                            }elseif($tabStat[0]['type_statid'] != $tabStat[1]['type_statid'] && $tabStat[2]['type_statid'] == $tabStat[1]['type_statid']){
                                if(sizeof($tabTableau[$j]) == 0 || sizeof($tabTableau[$j]) == 3){
                                    $tabTableau[$j][] = $indexDate;
                                    $tabTableau[$j][] = $somme_donnee;
                                }else{
                                    $tabTableau[$j][] = $somme_donnee;
                                }
                            }elseif($tabStat[0]['type_statid'] == $tabStat[1]['type_statid'] && $tabStat[0]['type_statid'] == $tabStat[2]['type_statid'] && $tabStat[2]['type_statid'] == $tabStat[1]['type_statid']){
                                $tabTableau[$j][] = $indexDate;
                                $tabTableau[$j][] = $somme_donnee;
                            }
                        }else{
                            // 2Stats 
                            if($tabStat[1]['type_statid'] == $tabStat[0]['type_statid']){
                                $tabTableau[$j][] = $indexDate;
                                $tabTableau[$j][] = $somme_donnee;
                            }else{
                                if($tabTableau[$j][0] != ""){
                                    $tabTableau[$j][] = $somme_donnee;
                                }else{
                                    $tabTableau[$j][] = $indexDate;
                                    $tabTableau[$j][] = $somme_donnee;
                                }
                            }
                        }
                        $j++;
                    }
                }
                // Construction des lignes du tableau
                for($k=0;$k<sizeof($tabTableau);$k++){
                    $ligne = "";
                    for($j=0;$j<sizeof($tabTableau[$k]);$j++){
                        $ligne .= "<td>".$tabTableau[$k][$j]."</td>";
                    }
                    eval(charge_template($langue,$referencepage,"WidgetBitTableauLigneBit"));
                }
                eval(charge_template($langue,$referencepage,"WidgetBitTableau"));
            }
            
        // Sinon forcément qu'une seule stat
        }else{
          
            //-------------------------------
            // Limit / Order parameters
            //-------------------------------
            $zOrderBy = "ca";
            $zOrderWay = "DESC";
            if( $widget['tri'] != "ca" && $widget['tri'] != "" ){
                $zOrderBy = $widget['tri'];
            }
            
            $iLimit = NULL ;
            if( (int)$widget['nb_resultats'] != 0 ){
                $iLimit = (int)$widget['nb_resultats'] ;
            }
            //-------------------------------
            
            $dataJsonPieChart = HTools::jsonEncode(array());

            switch ($tabStat[0]['type_statid']){
                case "1":
                    $donnees = $DB_site->query("SELECT SUM(nb_sessions) AS stat, date FROM stat_audience WHERE date >= '$date_debut_stat' AND date <= '$date_fin_stat' $andSite GROUP BY date");
                    break;  
                case "2":
                    $donnees = $DB_site->query("SELECT SUM(nb_utilisateurs) AS stat, date FROM stat_audience WHERE date >= '$date_debut_stat' AND date <= '$date_fin_stat' $andSite GROUP BY date");
                    break;
                case "3":
                    $donnees = $DB_site->query("SELECT SUM(nb_pages_vues) AS stat, date FROM stat_audience WHERE date >= '$date_debut_stat' AND date <= '$date_fin_stat' $andSite GROUP BY date");
                    break;
                case "4":
                    $donnees = $DB_site->query("SELECT ROUND( AVG(taux_rebond), 2) AS stat, date FROM stat_audience WHERE date >= '$date_debut_stat' AND date <= '$date_fin_stat' $andSite GROUP BY date");
                    break;
                case "5":
                    $donnees = $DB_site->query("SELECT SUM(sessions_desktop) AS ordinateur, SUM(sessions_mobile) AS mobile, SUM(sessions_tablette) AS tablette FROM stat_audience WHERE date >= '$date_debut_stat' AND date <= '$date_fin_stat' $andSite");
                    $dataJsonPieChart = "";
                    $titleField = "label";
                    $valueField = "data";
                    $labelText = "[[".$titleField."]] : [[".$valueField."]] sessions";
                    break;
                case "6":
                    $donnees = $DB_site->query("SELECT SUM(nb_commandes) AS stat, date FROM stat_facture WHERE date >= '$date_debut_stat' AND date <= '$date_fin_stat' $andSite GROUP BY date");
                    break;
                case "7":
                    if($andSite == ""){
                        //$donnees = $DB_site->query("SELECT ROUND(SUM(ca_ttc / tauxdevise), 2) AS stat, date FROM stat_facture WHERE date >= '$date_debut_stat' AND date <= '$date_fin_stat' GROUP BY date");
                        $donnees = $DB_site->query("SELECT ROUND(SUM(ca_ht_hfp / tauxdevise), 2) AS stat, date FROM stat_facture WHERE date >= '$date_debut_stat' AND date <= '$date_fin_stat' GROUP BY date");
                    }else{
                        //$donnees = $DB_site->query("SELECT ROUND(SUM(ca_ttc), 2) AS stat, date FROM stat_facture WHERE date >= '$date_debut_stat' AND date <= '$date_fin_stat' $andSite GROUP BY date");
                        $donnees = $DB_site->query("SELECT ROUND(SUM(ca_ht_hfp), 2) AS stat, date FROM stat_facture WHERE date >= '$date_debut_stat' AND date <= '$date_fin_stat' $andSite GROUP BY date");
                    }
                    
                    break;
                case "8":
                    if($andSite == ""){
                        $donnees = $DB_site->query("SELECT ROUND(AVG(panier_moyen / tauxdevise), 2) AS stat, date FROM stat_facture WHERE date >= '$date_debut_stat' AND date <= '$date_fin_stat' AND panier_moyen != '0' GROUP BY date");
                    }else{
                        $donnees = $DB_site->query("SELECT ROUND(AVG(panier_moyen), 2) AS stat, date FROM stat_facture WHERE date >= '$date_debut_stat' AND date <= '$date_fin_stat' AND panier_moyen != '0' $andSite GROUP BY date");
                    }
                    break;
                case "9":
                    if($andSite == ""){
                        $donnees = $DB_site->query("SELECT ROUND(SUM(fp_ht / tauxdevise), 2) AS stat, date FROM stat_facture WHERE date >= '$date_debut_stat' AND date <= '$date_fin_stat'  GROUP BY date");
                    }else{
                        $donnees = $DB_site->query("SELECT ROUND(SUM(fp_ht), 2) AS stat, date FROM stat_facture WHERE date >= '$date_debut_stat' AND date <= '$date_fin_stat' $andSite GROUP BY date");
                    }
                    break;
                case "10": // Nombre de comptes créés
                    $donnees = MStat::getNbrComptesCrees($_GET['idsite'],$date_debut_stat,$date_fin_stat,false);
                    break;
                case "11": // CA par Moyen de paiement
                    $CaParMoyenDePaiements = MStat::getCaMoyenPaiement($_GET['idsite'],$date_debut_stat,$date_fin_stat);
                    $total = 0 ;
                    if( !empty($CaParMoyenDePaiements) ){
                        foreach($CaParMoyenDePaiements as &$CaParMoyenDePaiement){
                            $total += $CaParMoyenDePaiement['ca']; 
                        }
                    }
                    $dataJsonPieChart = HTools::jsonEncode($CaParMoyenDePaiements);
                    $titleField = "libelle";
                    $valueField = "ca";
                    $labelText = "[[".$titleField."]] : [[".$valueField."]] €";
                    break;
                case "12": // CA par catégorie
                    //--------------------------------------
                    // <anthony> 
                    //--------------------------------------
                    $iOptionNiveau = MWidget::getOption($widget['widgetid'],"niveau");
                    
                    $tzAliasWhere = array();
                    if( (int)$iOptionNiveau != 0 ){
                        $tmCategoriesNiveau = MCategory::getCategoryLevelIs($iOptionNiveau,$idsite);
                        
                        if( !empty($tmCategoriesNiveau) ){
                            $tiCategoryIDTemp = array();
                            foreach( $tmCategoriesNiveau as $mCategorie ){
                                $tiCategoryIDTemp[] = $mCategorie['catid'] ;
                            }
                            $tzAliasWhere[] = " AND cs.catid IN (" . implode(",",$tiCategoryIDTemp) . ")";
                        }
                    }
                if( !empty($toCategoriesSelected) ){
                    $tiCategoryIDTemp = array();
                    foreach( json_decode($toCategoriesSelected) as $key => $mCatid ){
                        if( (int)$mCatid != 0 )
                            $tiCategoryIDTemp[] = $mCatid ;
                    }
                    if(count($tiCategoryIDTemp)>0)
                        $tzAliasWhere[] = " AND cs.catid IN (" . implode(",",$tiCategoryIDTemp) . ")";
                }

                $CaParCategories = MStat::getCaCategorie($_GET['idsite'],$date_debut_stat,$date_fin_stat,$tzAliasWhere) ;

                    $total = 0 ;
                    if( !empty($CaParCategories) ){
                        foreach($CaParCategories as $CaParCategorie){
                            $total += $CaParCategorie['ca']; 
                        }
                    }
                    $dataJsonPieChart = HTools::jsonEncode($CaParCategories);
                    
                    $titleField = "libelle";
                    $valueField = "ca";
                    $labelText = "[[".$titleField."]] : [[".$valueField."]] € ([[nb_ventes]] ventes)";
                    
                    break;
                case "13": // CA par fournisseur
                    $CaParFournisseur = MStat::getCaFourniseur($_GET['idsite'],$date_debut_stat,$date_fin_stat) ;
                    
                    $total = 0 ;
                    if( !empty($CaParFournisseurs) ){
                        foreach($CaParFournisseurs as $CaParFournisseur){
                            $total += $CaParFournisseur['ca']; 
                        }
                    }
                    $dataJsonPieChart = HTools::jsonEncode($CaParFournisseurs);
                    
                    $titleField = "libelle";
                    $valueField = "ca";
                    $labelText = "[[".$titleField."]] : [[".$valueField."]] €";
                    
                    break;
                case "14":
                    $donnees = MStat::getTauxConversion($_GET['idsite'],$date_debut_stat,$date_fin_stat,false);
                    break;
                case "15":
                    /**
                     * @date 2015-10-09 
                    */
                    $tConvertionTunnel = MStat::getConvertionTunnel($date_debut_stat, $date_fin_stat, $iSiteID);

                    p(array_keys($tConvertionTunnel),true);
                    break;
                case "16":
                    /**
                     * @date 2015-10-05 
                     * Top Articles
                    */
                    $iSiteID = NULL;
                    if (isset($_GET['idsite']) && $_GET['idsite'] > 0) {   
                        $iSiteID = $_GET['idsite'];
                    }
                    $tmStatTopArticle = MStat::getCATopArticles($date_debut_stat, $date_fin_stat, $iSiteID, $widget['nb_resultats'], $widget['tri']);
                    foreach ($tmStatTopArticle as &$tTop) {
                        $zLibelleTemp = str_replace("\"", "''", html_entity_decode(strip_tags($tTop['product_name']))) ;
                        $zUrl = $regleurlrewrite[1]["article"] . "-" . url_rewrite($zLibelleTemp) . "-" . $tTop['product_reference'] . ".htm";
                        //$tTop['product_url'] = '<a href="http://' . $host . '/' . $plateforme . '/' . $zUrl . '">' . $zUrl . '</a>';
                        $tTop['product_url'] = '<a data-original-title="Fiche article" data-placement="top" class="tooltips" href="produits.php?action=modifier&amp;artid=' . $tTop['product_reference'] . '"><i class="glyphicon glyphicon-eye-open"></i></a>';
                    }
                    break;
                case "17":
                    //-------------------------------------------
                    // top category <anthony>
                    //-------------------------------------------
                    $oWidgetOptions = HTools::jsonDecode($widget['options']);
                    
                    $tCategoryIDSiteTemp = array();
                    $tmStatTopCategories = array();
                    
                    if( is_object($oWidgetOptions) ){
                        //----------------------
                        // option du widget
                        //----------------------
                        if( isset($oWidgetOptions->display_type) ){
                            switch( $oWidgetOptions->display_type ){
                                case "niveau":
                                    if( isset($oWidgetOptions->niveau) && (int)$oWidgetOptions->niveau != 0 ){
                                        $tmCategories = MCategory::getCategoryLevelIs($oWidgetOptions->niveau,$idsite);
                                        
                                        if( !empty($tmCategories) ){
                                            foreach($tmCategories as $mCategory){
                                                $tCategoryIDSiteTemp[] = array(
                                                    'catid' => $mCategory["catid"],
                                                    'siteid' => $mCategory['siteid']
                                                );
                                            }
                                        }
                                    }
                                break;
                                case "tree":
                                    if( isset($oWidgetOptions->categories) ){
                                        $tmAllSite = array();
                                        $tmAllSite = MSite::getSites();
                                        $tCategoryID = $oWidgetOptions->categories ;
                                        
                                        if( (int)HTools::getValue('idsite') == 0 ){
                                            foreach( $tmAllSite as $mSite ){
                                                foreach( $tCategoryID as $iCatID ){
                                                    $tCategoryIDSiteTemp[] = array(
                                                        'catid' => $iCatID,
                                                        'siteid' => $mSite['siteid']
                                                    );
                                                }
                                            }
                                        }
                                        else{
                                            foreach( $tCategoryID as $iCatID ){
                                                $tCategoryIDSiteTemp[] = array(
                                                    'catid' => $iCatID,
                                                    'siteid' => (int)HTools::getValue('idsite')
                                                );
                                            }
                                        }
                                    }
                                break;
                            }
                            
                            if( !empty($tCategoryIDSiteTemp) ){
                                $tmStatTopCategories = array();
                                
                                foreach( $tCategoryIDSiteTemp as $tiCategoryIDSiteTemp ){
                                    
                                    $mCa = MStat::getCategoryCA( $tiCategoryIDSiteTemp['catid'],$tiCategoryIDSiteTemp['siteid'],$date_debut_stat,$date_fin_stat );
                                    
                                    /*
                                    if( $widget['widgetid'] == "567" && $tiCategoryIDSiteTemp['catid'] == 194  ){
                                        d(Db::getInstance()->lastQueryString);
                                        d($mCa);
                                    }
                                    */
                                    $tmCategoryAllChildren = MCategory::getAllChildren($tiCategoryIDSiteTemp['catid'],$tiCategoryIDSiteTemp['siteid'],false);
                                    
                                    $tiCategoryChildrenID = array();
                                    $tiCategoryChildrenIDDetph = array() ;
                                    
                                    $tmStatChildren = array();
                                    if( !empty($mCa) ){
                                        $mCa = $mCa[0];
                                    }else{
                                        $mCategory = MCategory::get($tiCategoryIDSiteTemp['catid'],$tiCategoryIDSiteTemp['siteid']);
                                        $mCa = array(
                                            'libelle'   => $mCategory['libelle'] ,
                                            'levelStr'  => str_repeat("&nbsp;&nbsp;&nbsp;&nbsp;",(int)$mCategory['niveau']),
                                            'catid'     => $tiCategoryIDSiteTemp['catid'],
                                            'siteid'    => $tiCategoryIDSiteTemp['siteid'] ,
                                            'nb_ventes' => 0,
                                            'ca'        => 0,
                                            'marge_brute' => 0,
                                            'countPrixAchatZero' => 1,
                                            'depth'     => $mCategory['depth']
                                        );
                                    }
                                    
                                    if( !empty($tmCategoryAllChildren) ){
                                        foreach( $tmCategoryAllChildren as $mCategoryChildren ){
                                            $tiCategoryChildrenID[] = $mCategoryChildren['catid'];
                                            $tiCategoryChildrenIDDetph[] = array(
                                                'catid' => $mCategoryChildren['catid'],
                                                'depth' => $mCategoryChildren['depth']
                                            );
                                        }
                                        $tmStatChildren = MStat::getCategoryCA($tiCategoryChildrenID,$iSiteID,$date_debut_stat,$date_fin_stat );
                                        
                                        if( !empty($tmStatChildren) ){
                                            foreach( $tmStatChildren as $mStatTemp ){
                                                $mCa['nb_ventes'] += $mStatTemp['nb_ventes'];
                                                $mCa['ca'] += $mStatTemp['ca'];
                                                $mCa['marge_brute'] += $mStatTemp['marge_brute'];
                                                $mCa['countPrixAchatZero'] += $mStatTemp['countPrixAchatZero'];
                                                //$mCa['detph'] = $mStatTemp['countPrixAchatZero'];
                                            }
                                        }            
                                    } 
                                    
                                    if( $mCa['ca'] != 0 && $mCa['libelle'] != '' ){
                                        $tmStatTopCategories[] = $mCa;
                                    } 
                                }

                                foreach( $tmStatTopCategories as &$mStatTopCategories ){
                                    $tzAllParentLabel = MCategory::getAllParentLibelle($mStatTopCategories['catid'] , $iSiteID,$tmp = array());
                                    
                                    if( !empty($tzAllParentLabel) ){
                                        $mStatTopCategories['libelle'] = implode('<span class="glyphicon glyphicon-chevron-right"></span>',$tzAllParentLabel) . '<span class="glyphicon glyphicon-chevron-right"></span>' . $mStatTopCategories['libelle'] ;
                                    }
                                    else{
                                        $mStatTopCategories['libelle'] = $mStatTopCategories['libelle'] ;
                                    }
                                }
                                 
                                if( !empty($tmStatTopCategories) ){
                                    switch( $widget['tri'] ){
                                        case "nb_ventes":
                                            usort($tmStatTopCategories, function($a, $b) {
                                                if ($a['nb_ventes'] == $b['nb_ventes']) {
                                                    return 0;
                                                }
                                                return ($a['nb_ventes'] > $b['nb_ventes']) ? -1 : 1;
                                            });
                                        break;
                                        case "ca":
                                            usort($tmStatTopCategories, function($a, $b) {
                                                if ($a['ca'] == $b['ca']) {
                                                    return 0;
                                                }
                                                return ($a['ca'] > $b['ca']) ? -1 : 1;
                                            });
                                        break;
                                    }
                                }
                            }
                        }
                    }
                    break;
                case "18":
                    //-------------------------------------------
                    // top client <anthony>
                    //-------------------------------------------
                    $tmStatTopClient = MStat::getTopClient($idsite,$date_debut_stat,$date_fin_stat,array(),$zOrderBy,$zOrderWay,$iLimit);
                    break;
                case "19":
                    $tmStatTopFournisseur = MStat::getCaTopFournisseur($idsite,$date_debut_stat,$date_fin_stat,array(),$zOrderBy,$zOrderWay,$iLimit);
                    break;
                case "20": 
                    //-------------------------------------------
                    // top marques <anthony>
                    //-------------------------------------------
                    $tmStatTopMarques = MStat::getTopMarque($idsite,$date_debut_stat,$date_fin_stat,array() , $zOrderBy, $zOrderWay,$iLimit);
                     
                    break;
            }
            
            /**
             * @date 2015-10-08
             * 
             * Take into account $nb_resultats value on widget type is camembert
             */
             if (isset($dataJsonPieChart) && $widget['nb_resultats']){
                $tDataJsonPieChart = HTools::jsonDecode($dataJsonPieChart, true);
                if (count($tDataJsonPieChart) > $widget['nb_resultats']) {
                    $i = 1;
                    $tNewDataJsonPieChart = array();
                    $tOhters = array(
                        "libelle" => $multilangue['autres'],
                        //"moyenid" => $widget['nb_resultats'] + 1,
                        "ca" => 0
                    );
                    foreach ($tDataJsonPieChart as $tRow) {
                        if ($i <= $widget['nb_resultats']) {
                            $tNewDataJsonPieChart[] = $tRow;
                        } else {
                            $tOhters['ca'] += $tRow['ca']; 
                            $tOhters['nb_ventes'] += $tRow['nb_ventes']; 
                        }
                        $i++;
                    }
                    $tNewDataJsonPieChart[] = $tOhters;
                    $dataJsonPieChart = HTools::jsonEncode($tNewDataJsonPieChart);
                }
            }
            
            if($widget[periode_precedente] == 1){
                switch ($widget[type_periode]){
                    case "jour";
                        $date = strtotime('-1 day', strtotime($date_debut_stat));
                        $date_debut_stat = date('Y-m-d', $date);
                        $date_fin_stat = $date_debut_stat;
                        
                        break;
                    case "hier";
                        $date_fin_stat = $date_debut_stat;
                        $date = strtotime('-1 day', strtotime($date_debut_stat));
                        $date_debut_stat = date('Y-m-d', $date);
                        $date_fin_stat = $date_debut_stat;
                        break;
                    case "7jours";
                        $date_fin_stat =  date('Y-m-d', strtotime('-1 day', strtotime($date_debut_stat)));
                        $date = strtotime('-7 days', strtotime($date_debut_stat));
                        $date_debut_stat = date('Y-m-d', $date);
                        break;
                    case "30jours";
                        $date_fin_stat =  date('Y-m-d', strtotime('-1 day', strtotime($date_debut_stat)));
                        $date = strtotime('-30 days', strtotime($date_debut_stat));
                        $date_debut_stat = date('Y-m-d', $date);
                        break;
                    case "mois";
                        $annee = date('Y', time());
                        $mois = date('m',  strtotime('last month', strtotime($date_debut_stat)));
                        $date_debut_stat = date('Y-m-d', strtotime('01-'.$mois.'-'.$annee));
                        $date_fin_stat = date('Y-m-t', strtotime('01-'.$mois.'-'.$annee));
                        break;
                    case "mois_dernier";
                        $annee = date('Y', time());
                        $mois = date('m',  strtotime('last month', strtotime($date_debut_stat)));
                        $date_debut_stat = date('Y-m-d', strtotime('01-'.$mois.'-'.$annee));
                        $date_fin_stat = date('Y-m-t', strtotime('01-'.$mois.'-'.$annee));
                        break;
                    case "annee";
                        $annee = date('Y', strtotime('last year', strtotime($date_debut_stat)));
                        $date_debut_stat = date('Y-m-d', strtotime('01-01-'.$annee));
                        $date_fin_stat = date('Y-m-t', strtotime('01-12-'.$annee));;
                        break;
                    case "annee_derniere";
                        $annee = date('Y', strtotime('last year', strtotime($date_debut_stat)));
                        $date_debut_stat = date('Y-m-d', strtotime('01-01-'.$annee));
                        $date_fin_stat = date('Y-m-t', strtotime('01-12-'.$annee));;
                        break;
                    case "precise";
                        $diffJour = ((strtotime($date_fin_stat)-strtotime($date_debut_stat))/86400);
                        $date_fin_stat = $date_debut_stat;
                        $date = strtotime('-'.$diffJour.' days', strtotime($date_debut_stat));
                        $date_debut_stat = date('Y-m-d', $date);
                        break;
                }
                
                 
                switch ($tabStat[0]['type_statid']){
                    case "1":
                        $donnees_prec = $DB_site->query("SELECT SUM(nb_sessions) AS stat, date FROM stat_audience WHERE date >= '$date_debut_stat' AND date <= '$date_fin_stat' $andSite GROUP BY date");
                        break;
                    case "2":
                        $donnees_prec = $DB_site->query("SELECT SUM(nb_utilisateurs) AS stat, date FROM stat_audience WHERE date >= '$date_debut_stat' AND date <= '$date_fin_stat' $andSite GROUP BY date");
                        break;
                    case "3":
                        $donnees_prec = $DB_site->query("SELECT SUM(nb_pages_vues) AS stat, date FROM stat_audience WHERE date >= '$date_debut_stat' AND date <= '$date_fin_stat' $andSite GROUP BY date");
                        break;
                    case "4":
                        $donnees_prec = $DB_site->query("SELECT ROUND( AVG(taux_rebond), 2) AS stat, date FROM stat_audience WHERE date >= '$date_debut_stat' AND date <= '$date_fin_stat' $andSite GROUP BY date");
                        break;
                    case "5": // Catégorie d'appareil
                        $donnees_prec = $DB_site->query("SELECT SUM(sessions_desktop) AS ordinateur, SUM(sessions_mobile) AS mobile, SUM(sessions_tablette) AS tablette FROM stat_audience WHERE date >= '$date_debut_stat' AND date <= '$date_fin_stat' $andSite");
                        break;
                    case "6":
                        $donnees_prec = $DB_site->query("SELECT SUM(nb_commandes) AS stat, date FROM stat_facture WHERE date >= '$date_debut_stat' AND date <= '$date_fin_stat' $andSite GROUP BY date");
                        break;
                    case "7":
                        if($andSite == ""){
                            //$donnees_prec = $DB_site->query("SELECT ROUND(SUM(ca_ttc / tauxdevise), 2) AS stat, date FROM stat_facture WHERE date >= '$date_debut_stat' AND date <= '$date_fin_stat' GROUP BY date");
                            $donnees_prec = $DB_site->query("SELECT ROUND(SUM(ca_ht_hfp / tauxdevise), 2) AS stat, date FROM stat_facture WHERE date >= '$date_debut_stat' AND date <= '$date_fin_stat' GROUP BY date");
                        }else{
                            //$donnees_prec = $DB_site->query("SELECT ROUND(SUM(ca_ttc), 2) AS stat, date FROM stat_facture WHERE date >= '$date_debut_stat' AND date <= '$date_fin_stat' $andSite GROUP BY date");
                            $donnees_prec = $DB_site->query("SELECT ROUND(SUM(ca_ht_hfp), 2) AS stat, date FROM stat_facture WHERE date >= '$date_debut_stat' AND date <= '$date_fin_stat' $andSite GROUP BY date");
                        }
                        
                        break;
                    case "8":
                        if($andSite == ""){
                            $donnees_prec = $DB_site->query("SELECT ROUND(AVG(panier_moyen / tauxdevise), 2) AS stat, date FROM stat_facture WHERE date >= '$date_debut_stat' AND date <= '$date_fin_stat' AND panier_moyen != '0' GROUP BY date");
                        }else{
                            $donnees_prec = $DB_site->query("SELECT ROUND(AVG(panier_moyen), 2) AS stat, date FROM stat_facture WHERE date >= '$date_debut_stat' AND date <= '$date_fin_stat' AND panier_moyen != '0' $andSite GROUP BY date");
                        }
                        break;
                    case "9":
                        if($andSite == ""){
                            $donnees_prec = $DB_site->query("SELECT ROUND(SUM(fp_ht / tauxdevise), 2) AS stat, date FROM stat_facture WHERE date >= '$date_debut_stat' AND date <= '$date_fin_stat'  GROUP BY date");
                        }else{
                            $donnees_prec = $DB_site->query("SELECT ROUND(SUM(fp_ht), 2) AS stat, date FROM stat_facture WHERE date >= '$date_debut_stat' AND date <= '$date_fin_stat' $andSite GROUP BY date");
                        }
                        break;
                    case "10": // Nombre de comptes créés
                        $donnees_prec = MStat::getNbrComptesCrees($_GET['idsite'],$date_debut_stat,$date_fin_stat,false);
                        break;
                    case "11": // CA HT par moyen de paiement
                        break;
                    case "12": // CA HT par Catégorie  
                        break;
                    case "13": // CA HT par Fournisseur
                        break;
                    case "14": // Taux de conversion
                        $donnees_prec = MStat::getTauxConversion($_GET['idsite'],$date_debut_stat,$date_fin_stat,false);
                        break;
                    case "15": // Entonnoir de conversion
                        break;
                    case "16": // Top articles
                        break;
                    case "17": // Top catégories
                        break;
                    case "18": // Top clients
                        break;
                    case "19": // Top fournisseurs
                        break;
                    case "20": // Top marques
                        break;
                }
            
            }
            
            if( in_array( $tabStat[0]['type_statid'], array(4,14) ) ){
                $unite_courbe = "(%)";
                $unite = '%';
            }elseif( in_array( $tabStat[0]['type_statid'], array(7,8,9) ) ){
                if($andSite == ""){
                    $unite = '€';
                    $unite_courbe = "(€)";
                }else{
                    $unite = $tabsites[$idsite][devise_complete];
                    $unite_courbe = "(".$tabsites[$idsite][devise_complete].")";
                }
            }else{
                $unite_courbe = "";
                $unite = "";    
            }
            
            switch ($widget[type_representation]){
                case "courbe":
                    /**
                     * @date 2015-10-09
                     * Add More ID if required 
                    */
                    
                    $tmDataStatCourbeJson = array();
                    
                    if ($tabStat[0]['type_statid'] === '15') {
                        
                        $tzColorsData = array(
                            '#FF0101',
                            '#FF5300',
                            '#FEA200',
                            '#FFDB00',
                            '#ACF40C',
                            '#53D118'
                        );
                        $i = 0;
                        foreach ($tConvertionTunnel as $zKey => $mValue) {
                            $iMax = 100;
                            // if (preg_match ("/^\d+$/", $mValue)){
                            if ($mValue > $iMax){
                                $iMax = $mValue;  
                            }
                            $zTooltip = $multilangue['tooltip_rate_' . $zKey];
                            $zTitreTunel = $multilangue['tunel_' . $zKey];
                            $zDataColor = $tzColorsData[$i];
                            
                            $TemplateIndexWidgetBitTunelConversionBitArrow = "";
                            if($i < count($tConvertionTunnel) - 1) {
                                eval(charge_template($langue, $referencepage, "WidgetBitTunelConversionBitArrow"));
                            }
                            
                            $i++;
                            eval(charge_template($langue, $referencepage, "WidgetBitTunelConversionBit"));
                        }
                        eval(charge_template($langue, $referencepage, "WidgetBitTunelConversion"));
                        
                    } else {
                        $placeholder .= "$.plot($('#placeholder$widget[widgetid]'), data$widget[widgetid], options$widget[widgetid]);";
                        $dataStat = 'var data'.$widget[widgetid].' = [{label: "'.$tabStat[0]['libelle'].' '.$unite_courbe.'", data: [';
                        $optionStat = "var options$widget[widgetid] = { 
                                            series: {
                                                lines: { show: true },
                                                points: { show: true }
                                            },
                                            legend: {
                                                show: true
                                            },
                                            grid: {
                                                hoverable: true,
                                                clickable: true,
                                                borderWidth: 1,
                                                mouseActiveRadius: 5
                                            },
                                            xaxis: { 
                                                ticks: [";
                        switch ($widget[granularite]){
                            case "jour":
                                $j = 0;
                                while($donnee = $DB_site->fetch_array($donnees)){
                                    $dataStat .= "[$j, '$donnee[stat]'],";
                                    $donnee['fillColor'] = '#F1F7FD';
                                    $donnee['lineColor'] = '#9ACAE6';
                                    $tmDataStatCourbeJson[] = $donnee ; 
                                    
                                    $date_stat = date('d/m', strtotime($donnee[date]));
                                    $optionStat .= "[$j, '$date_stat'],";
                                    $j++;
                                }
                                $dataStat = substr($dataStat, 0, -1);
                                $dataStat .= "]}];";
                                
                                $optionStat = substr($optionStat, 0, -1);
                                $optionStat .= "]}};";
                                break;
                            case "mois":
                                $tabDate = array();
                                while($donnee = $DB_site->fetch_array($donnees)){
                                    $date_stat = date('m/Y', strtotime($donnee[date]));
                                    $existe = false;
                                    foreach($tabDate as $indexDate => $somme_donnee){
                                        if($indexDate == $date_stat){
                                            $ancienne_stat = $tabDate[$indexDate];
                                            $nouvelle_stat = $ancienne_stat + $donnee[stat];
                                            $tabDate[$indexDate] = $nouvelle_stat;
                                            $existe = true;
                                            break;
                                        }
                                    }
                                    if(!$existe){
                                        $tabDate[$date_stat] = $donnee[stat];
                                    }
                                }
                                $j = 0;
                                 
                                foreach($tabDate as $indexDate => $somme_donnee){
                                    
                                    $dataStat .= "[$j, '$somme_donnee'],";
                                    $optionStat .= "[$j, '" . str_replace("-","/",$indexDate). "'],";
                                    
                                    $tmDataStatCourbeJson[] = array(
                                        'date' => $indexDate,
                                        'stat' => $somme_donnee
                                    );
                                    
                                    
                                    $j++;
                                }
                                $dataStat = substr($dataStat, 0, -1);
                                $dataStat .= "]}];";
                                    
                                $optionStat = substr($optionStat, 0, -1);
                                $optionStat .= "]}};";
                                break;
                            case "année":
                                $tabDate = array();
                                while($donnee = $DB_site->fetch_array($donnees)){
                                    $date_stat = date('Y', strtotime($donnee[date]));
                                    $existe = false;
                                    foreach($tabDate as $indexDate => $somme_donnee){
                                        if($indexDate == $date_stat){
                                            $ancienne_stat = $tabDate[$indexDate];
                                            $nouvelle_stat = $ancienne_stat + $donnee[stat];
                                            $tabDate[$indexDate] = $nouvelle_stat;
                                            
                                            $existe = true;
                                            break;
                                        }
                                    }
                                    if(!$existe){
                                        $tabDate[$date_stat] = $donnee[stat];
                                    }
                                }
                                $j = 0;
                                
                                
                                foreach($tabDate as $indexDate => $somme_donnee){
                                    $dataStat .= "[$j, '$somme_donnee'],";
                                    $optionStat .= "[$j, '" . str_replace("-","/",$indexDate). "'],";
                                    
                                    $tmDataStatCourbeJson[] = array(
                                        'date' => $indexDate,
                                        'stat' => $somme_donnee
                                    );
                                    
                                    $j++;
                                }
                                $dataStat = substr($dataStat, 0, -1);
                                $dataStat .= "]}];";
                                
                                $optionStat = substr($optionStat, 0, -1);
                                $optionStat .= "]}};";
                                break;
                            default:
                                break;
                                    
                        }
                        
                        $toAmchartGraphs = array();
                        
                        $toAmchartGraphs[] = array(
                                "id" => "graph1",
                                "bullet" => "square",
                                "bulletBorderAlpha" => 1,
                                "bulletBorderThickness" => 1,
                                "fillAlphas" => 0.3,
                                "fillColorsField" => "fillColor",
                                //"legendValueText" => "[[value]]",
                                "lineColorField" => "lineColor",
                                "dateFormat" => "DD MMM",
                                "balloonText" => "<div style='margin:5px;'><b>[[title]]</b><br>Date : <b>[[category]]</b><br>Stat : <b>[[value]] $unite</b></div>",
                                "title" => $tabStat[0]['libelle'],
                                "valueField" => "stat"
                        );
                        
                        
                        $zDataChartCategory = '
                                "categoryField": "date",
                                "categoryAxis": {
                                        "dateFormats": [
                                            {"period": "DD","format": "DD MMM"}, 
                                            {"period": "WW","format": "DD MMM"},
                                            {"period": "MM","format": "DD MMM"},
                                            {"period": "YYYY","format": "YYYY"}
                                        ],
                                        "parseDates": true,
                                        "autoGridCount": false,
                                        "axisColor": "#555555",
                                        "gridAlpha": 0,
                                        "gridCount": 50
                                }';
                        $zBalloonDateFormat = "DD MMM YYYY";
                        $zCourbeDateFormat = "";
                        switch( $widget['granularite'] ){
                            case "jour":
                                $zCourbeDateFormat = "YYYY-MM-DD" ;
                                $zBalloonDateFormat = "DD MMM YYYY";
                            break;
                            case "mois":
                                $zCourbeDateFormat = "MM-YYYY" ;
                                $zBalloonDateFormat = "MMM YYYY";
                            break;
                            case "année":
                                $zCourbeDateFormat = "YYYY" ;
                                $zBalloonDateFormat = "YYYY";
                            break;
                        }
                        
                        $tmChartData = array(
                            'type'              => "serial",
                            'graphs'            => HTools::jsonEncode($toAmchartGraphs),
                            'dataProvider'      => HTools::jsonEncode($tmDataStatCourbeJson),
                            'legend'            => HTools::jsonEncode($tzLegend),
                            'valueAxes'         => HTools::jsonEncode(
                                                    array(
                                                        array(
                                                            "autoGridCount" => "true",
                                                            "axisAlpha" => 0
                                                        )
                                                    )),
                            'dataDateFormat'    => $zCourbeDateFormat,
                            'categoriesData'    => $zDataChartCategory ,
                        );
                        eval(charge_template($langue,$referencepage,"WidgetBitCourbe"));    
                    }
                    
                    break;
                case "tableau":
                    if( in_array($tabStat[0]['type_statid'], array("16", "17", "18", "20","19")) ){ 
                        // Top vente par categorie <anthony>
                        $tzColonnes = array(
                            "17" => array(
                                $multilangue['nom'] ,
                                $multilangue['nb_ventes'],
                                $multilangue['ca'] . " " . $multilangue['ht'] ,
                                $multilangue['marge_brute']
                            ),
                            "18" => array(
                                $multilangue['client'],
                                $multilangue['email'],
                                $multilangue['nb_achats'],
                                $multilangue['ca'] . " " . $multilangue['ht'],
                                $multilangue['action']
                            ),
                             "19" => array(
                                $multilangue['nom'] ,
                                $multilangue['nb_produits_commandes'],
                                $multilangue['ca'] . " " . $multilangue['ht'] ,
                                $multilangue['marge_brute']
                            ),
                            "20" => array(
                                $multilangue['nom'],
                                $multilangue['nombre_de_produit_recommande'],
                                $multilangue['ca'] . " " . $multilangue['ht'] ,
                                $multilangue['marge_brute']
                            )
                        );
                        $tzLigne = array();
                        switch($tabStat[0]['type_statid']){
                            case "16":
                                /**
                                * @date 2015-10-06 - update 2015-10-08
                                * Add More ID if required 
                                */
                                $tFields = array();
                                if( !empty($tmStatTopArticle) ){
                                    foreach ($tmStatTopArticle as $tTop) {
                                        if (count($tFields) === 0) {
                                            foreach ($tTop as $zKey => $mValue) {
                                                $tFields[] = $multilangue[$zKey];
                                            }
                                        }
                                        $ligne = "";
                                              
                                        $ligne = "";      
                                        foreach ($tTop as $zKey => $mValue) { 
                                            
                                            if (preg_match("/\d+(\.|,)\d+/", $mValue)) {
                                                $mValue = '<span class="align-right">' . formaterPrix($mValue, "2", ",", " ") . ' ' . $symboleMonetaire . '</span>'; 
                                            } else if (preg_match("/^\d+$/", $mValue)) {
                                                $mValue = '<span class="align-right">' . $mValue . '</span>';
                                            } else if ($zKey == "marge_brute" && $mValue == "N/D") {
                                                $mValue = '<span class="align-right"><span class="label label-info" data-toggle="tooltip" data-placement="left" title="' . $multilangue['non_calculable_car_vous_navezpas'] . '">' .  $multilangue['nd'] . '</span></span>' ;
                                            } else if (!preg_match("/^\<a.+\<\/a\>$/", $mValue) && preg_match("/^[ -~]+[^\d+(\.|,)\d+$]/", $mValue)) {
                                                $mValue = '<span class="align-left">' . $mValue . '</span>';
                                            }
                                            $ligne .= "<td>" . $mValue . "</td>";
                                        }
                                        eval(charge_template($langue, $referencepage, "WidgetBitTableauLigneBit"));
                                    }
                                }
                                foreach($tFields as $colonne) {
                                    eval(charge_template($langue, $referencepage, "WidgetBitTableauColonneBit"));
                                }
                            break;
                            case "17":
                                
                                if( !empty($tmStatTopCategories) ){
                                    //------------------------------------------
                                    // <anthony>
                                    // Limiter le nombre de resultat
                                    //------------------------------------------
                                    if( (int)$widget['nb_resultats'] > 0 && count($tmStatTopCategories) > (int)$widget['nb_resultats'] ){
                                        $tmStatTopCategoriesFirst = array_slice( $tmStatTopCategories, 0 , (int)$widget['nb_resultats'] );
                                        $tmStatTopCategoriesLast = array_slice( $tmStatTopCategories, (int)$widget['nb_resultats'] );
                                        
                                        $mStatTopCategoryAutres = array_shift($tmStatTopCategoriesLast);
                                        $tmStatTopCategoriesLast = array_slice( $tmStatTopCategories, 1);
                                        
                                        $mStatTopCategoryAutres['libelle_parent'] = $multilangue[''];
                                        $mStatTopCategoryAutres['libelle'] = $multilangue['autres'];
                                        $mStatTopCategoryAutres['catid'] = "";
                                        
                                        foreach( $tmStatTopCategoriesLast as $mStatTemp ){
                                            $mStatTopCategoryAutres['ca'] += $mStatTemp['ca'];
                                            $mStatTopCategoryAutres['marge_brute'] += $mStatTemp['marge_brute'];
                                            $mStatTopCategoryAutres['countPrixAchatZero'] += $mStatTemp['countPrixAchatZero'];
                                            $mStatTopCategoryAutres['nb_ventes'] += $mStatTemp['nb_ventes']; 
                                        }
                                        
                                        //$tmStatTopCategoriesLast
                                        array_push($tmStatTopCategoriesFirst,$mStatTopCategoryAutres);
                                        
                                        $tmStatTopCategories = $tmStatTopCategoriesFirst ;
                                    }
                                    //------------------------------------------
                                    
                                    foreach($tmStatTopCategories as $mStatTopCategory){
                                        $tzLigne[] = 
                                            //'<td align="left">' . ( $mStatTopCategory['levelStr'] != "" ? $mStatTopCategory['levelStr'] . " > " : "" ).  $mStatTopCategory['libelle'] . '</td>' .
                                            '<td align="left">' . $mStatTopCategory['libelle'] . '</td>' .
                                            '<td align="right">' . formaterPrix($mStatTopCategory['nb_ventes'],0,"", " ") . '</td>' .
                                            '<td align="right">' . formaterPrix($mStatTopCategory['ca']) . " $symboleMonetaire" . '</td>' .
                                            '<td align="right">' . 
                                                ( (int)$mStatTopCategory['countPrixAchatZero'] > 0 ? '<span class="label label-info" data-toggle="tooltip" data-placement="left" title="' . $multilangue['non_calculable_car_vous_navezpas'] . '">' .  $multilangue['nd'] . '</span>' : formaterPrix($mStatTopCategory['marge_brute']) . " $symboleMonetaire" )  . 
                                            '</td>' ;    
                                    }
                                }
                                else{
                                    $tzLigne[] = '<td colspan="' . count($tzColonnes[$tabStat[0]['type_statid']]) . '">' . $multilangue['vide'] . '</td>' ;
                                }
                            break;
                            case "18":
                                if( !empty($tmStatTopClient) ){
                                    foreach( $tmStatTopClient as $mStatTopClient ){
                                        $tzLigne[] = 
                                            '<td align="left">' . ucfirst($mStatTopClient['prenom']) . " " . ucfirst($mStatTopClient['nom']) . '</td>' .
                                            '<td align="left">' . $mStatTopClient['mail'] . '</td>' .
                                            '<td align="right">' . formaterPrix($mStatTopClient['nb_ventes'],0,"", " ") . '</td>' .
                                            '<td align="right">' . formaterPrix($mStatTopClient['ca']) . " $symboleMonetaire" . '</td>' .
                                            '<td align="right">' . '<a href="clients.php?action=editer&user=' . $mStatTopClient['userid'] . '" class="tooltips"  data-placement="top" data-original-title="Fiche client"><i class="glyphicon glyphicon-eye-open"></i></a>'  . '</td>' ; 
                                    }
                                }
                                else{
                                    $tzLigne[] = '<td colspan="' . count($tzColonnes[$tabStat[0]['type_statid']]) . '">' . $multilangue['vide'] . '</td>' ;
                                }
                            break;
                            case "19":
                                if( !empty($tmStatTopFournisseur) ){
                                    foreach( $tmStatTopFournisseur as $mStatTopFournisseur ){
                                        $tzLigne[] = 
                                            '<td align="left">' . ucfirst($mStatTopFournisseur['libelle']) . '</td>' .
                                            '<td align="right">' . formaterPrix($mStatTopFournisseur['nb_ventes'],0,"", " ") . '</td>' .
                                            '<td align="right">' . formaterPrix($mStatTopFournisseur['ca']) . " $symboleMonetaire" . '</td>' .
                                            '<td align="right">' . 
                                                ( (int)$mStatTopFournisseur['countPrixAchatZero'] > 0 ? '<span class="label label-info" data-toggle="tooltip" data-placement="left" title="' . $multilangue['non_calculable_car_vous_navezpas'] . '">' .  $multilangue['nd'] . '</span>' : formaterPrix($mStatTopFournisseur['marge_brute']) ) . " $symboleMonetaire"  . 
                                            '</td>' ; 
                                    }
                                }
                                else{
                                    $tzLigne[] = '<td colspan="' . count($tzColonnes[$tabStat[0]['type_statid']]) . '">' . $multilangue['vide'] . '</td>' ;
                                }
                            break;
                            case "20":
                                if( !empty($tmStatTopMarques) ){
                                    foreach($tmStatTopMarques as $mStatTopCategory){
                                        $tzLigne[] = 
                                            '<td align="left">' . $mStatTopCategory['libelle'] . '</td>' .
                                            '<td align="right">' . $mStatTopCategory['nb_ventes'] . '</td>' .
                                            '<td align="right">' . formaterPrix($mStatTopCategory['ca']) . " $symboleMonetaire" . '</td>' .
                                            '<td>' . 
                                                ( (int)$mStatTopCategory['countPrixAchatZero'] > 0 ? '<span class="label label-info" data-toggle="tooltip" data-placement="left" title="' . $multilangue['non_calculable_car_vous_navezpas'] . '">' .  $multilangue['nd'] . '</span>' : formaterPrix($mStatTopCategory['marge_brute']) )  . 
                                            '</td>' ;    
                                    }
                                }
                                else{
                                    $tzLigne[] = '<td colspan="' . count($tzColonnes[$tabStat[0]['type_statid']]) . '">(' . $multilangue['vide'] . ')</td>' ;
                                }
                            break;
                        }
                        //----------------------
                        //display table
                        //----------------------
                        if( !empty($tzColonnes[$tabStat[0]['type_statid']]) ){
                             foreach($tzColonnes[$tabStat[0]['type_statid']] as $colonne){
                                eval(charge_template($langue,$referencepage,"WidgetBitTableauColonneBit"));
                            }
                        }
                        if( !empty($tzLigne) ){
                            foreach($tzLigne as $ligne){
                                eval(charge_template($langue,$referencepage,"WidgetBitTableauLigneBit"));
                            }
                        }
                    }
                    else{
                        // Colonne pour la date
                        $colonne = "";
                        eval(charge_template($langue,$referencepage,"WidgetBitTableauColonneBit"));
                        
                        // Colonne avec le nom de la donnee
                        $colonne = $tabStat[0]['libelle'];
                        eval(charge_template($langue,$referencepage,"WidgetBitTableauColonneBit"));
                        
                        switch ($widget[granularite]){
                            case "jour":
                                while($donnee = $DB_site->fetch_array($donnees)){
                                    $date_stat = date('d/m', strtotime($donnee[date]));
                                    $donneStat = $donnee[stat] ;
                                    
                                    if( $unite == "€" ){
                                        $donneStat = formaterPrix($donneStat);
                                    } else {
                                        $donneStat = formaterPrix($donneStat,0,"", " ");
                                    }
                                    
                                    $ligne = "
                                        <td>$date_stat</td>
                                        <td align=\"right\">$donneStat $unite</td>";
                                    eval(charge_template($langue,$referencepage,"WidgetBitTableauLigneBit"));
                                }
                                break;
                            case "mois":
                                $tabDate = array();
                                while($donnee = $DB_site->fetch_array($donnees)){
                                    $date_stat = date('m/Y', strtotime($donnee[date]));
                                    $existe = false;
                                    foreach($tabDate as $indexDate => $somme_donnee){
                                        if($indexDate == $date_stat){
                                            $ancienne_stat = $tabDate[$indexDate];
                                            $nouvelle_stat = $ancienne_stat + $donnee[stat];
                                            $tabDate[$indexDate] = $nouvelle_stat;
                                            $existe = true;
                                            break;
                                        }
                                    }
                                    if(!$existe){
                                        $tabDate[$date_stat] = $donnee[stat];
                                    }
                                }
                                
                                foreach($tabDate as $indexDate => $somme_donnee){
                                    $donneStat = $somme_donnee ;
                                    
                                    if( $unite == "€" ){
                                        $donneStat = formaterPrix($donneStat);
                                    } else {
                                        $donneStat = formaterPrix($donneStat,0,"", " ");
                                    }
                                    
                                    $ligne = "<td>". str_replace("-","/",$indexDate) . "</td><td align=\"right\">$donneStat $unite</td>";
                                    eval(charge_template($langue,$referencepage,"WidgetBitTableauLigneBit"));
                                }
                                break;
                            case "année":
                                $tabDate = array();
                                while($donnee = $DB_site->fetch_array($donnees)){
                                    $date_stat = date('Y', strtotime($donnee[date]));
                                    $existe = false;
                                    foreach($tabDate as $indexDate => $somme_donnee){
                                        if($indexDate == $date_stat){
                                            $ancienne_stat = $tabDate[$indexDate];
                                            $nouvelle_stat = $ancienne_stat + $donnee[stat];
                                            $tabDate[$indexDate] = $nouvelle_stat;
                                            $existe = true;
                                            break;
                                        }
                                    }
                                    if(!$existe){
                                        $tabDate[$date_stat] = $donnee[stat];
                                    }
                                }
                                $j = 0;
                                
                                foreach($tabDate as $indexDate => $somme_donnee){
                                    $donneStat = $somme_donnee ;
                                    
                                    if( $unite == "€" ){
                                        $donneStat = formaterPrix($donneStat);
                                    } else {
                                        $donneStat = formaterPrix($donneStat,0,"", " ");
                                    }
                                    $ligne = "<td>". str_replace("-","/",$indexDate) . "</td><td align=\"right\">$donneStat $unite</td>";
                                    eval(charge_template($langue,$referencepage,"WidgetBitTableauLigneBit"));
                                }
                                break;
                            default:
                                break;
                        }   
                    }
                    
                    eval(charge_template($langue,$referencepage,"WidgetBitTableau"));
                    break;
                case "camembert":
                    
                    $placeholder .= "$.plot($('#placeholder$widget[widgetid]'), data$widget[widgetid], options$widget[widgetid]);";
                    $optionStat = "var options$widget[widgetid] = {
                                        series: {
                                            pie: {
                                                show: true,
                                                radius: 1,
                                                label: {
                                                    show: true,
                                                    radius: 3/4,
                                                    formatter: function(label, series){
                                                        return '<div style=\"font-size:9px;text-align:center;padding:2px;color:white;\">'+label+'<br/>'+Math.round(series.percent)+'%</div>';
                                                    },
                                                    background: {
                                                        opacity: 0.5,
                                                        color: '#000'
                                                    }
                                                }
                                            }
                                        },
                                    legend: {
                                        show: false
                                    }};";
                    if ($dataJsonPieChart) {
                        $dataStat = "var data$widget[widgetid] = $dataJsonPieChart";
                    } else {
                        $dataStat = "var data$widget[widgetid] = [ ";
                        while($donnee = $DB_site->fetch_array($donnees)){
                            foreach($donnee as $key => $value)
                                if(!is_numeric($key)){
                                    $dataStat .= "{\"label\": \"".ucfirst($key)." $unite_courbe\", \"data\": \"$value\"},";
                                }
                        }
                        
                        $dataStat = substr($dataStat, 0, -1);
                        $dataStat .= "]";
                    }
                    
                    eval(charge_template($langue,$referencepage,"WidgetBitCamembert"));
                    break;
                case "moyenne":
                    $total = 0;
                    
                    $classComparaison = "white_color";
                    $tauxAccroissement = "" ;
                    
                    switch ($widget[granularite]){
                        case "jour":
                            $granularite = strtolower($multilangue[jour]);
                            $nb_jours = 0;
                            $totalNonFormate = 0 ;
                            if($DB_site->num_rows($donnees) > 0){
                                while($donnee = $DB_site->fetch_array($donnees)){
                                    $total += $donnee[stat];
                                    $nb_jours++;
                                }
                            
                                $total = $total/$nb_jours;
                                $totalNonFormate = $total;
                                
                                $total = HTools::formatPrix($tabStat[0]['format'],$total );
                                /*
                                if( $tabStat[0]['format'] == 'monnaie' ){
                                    $total = formaterPrix($total);
                                }                                
                                if( $tabStat[0]['format'] == 'nombre' ){
                                    $total = formaterPrix($total,0,"", " ");
                                }
                                */
                            }else{
                                $total = 0;
                            }
                            
                            if($widget[periode_precedente] == 1){
                                $total_prec = 0;
                                $nb_jours_prec = 0;
                                while($donnee_prec = $DB_site->fetch_array($donnees_prec)){
                                    $total_prec += $donnee_prec[stat];
                                    $nb_jours_prec++;
                                }
                                $total_prec = $total_prec/$nb_jours_prec;
                                
                                //----------------------------------
                                // accroissement
                                //----------------------------------
                                if( $totalNonFormate > $total_prec ){
                                    $classComparaison = "green_color";
                                }
                                else{
                                    $classComparaison = "red_color";
                                }
                                $tauxAccroissement = calculTauxAccroissement($totalNonFormate,$total_prec); 
                                $tauxAccroissement = '<span class="' . $classComparaison . '">' . $tauxAccroissement . '%</span>';
                                
                                $total_prec = HTools::formatPrix($tabStat[0]['format'],$total_prec );
                                /*
                                if( $tabStat[0]['format'] == 'monnaie' ){
                                    $total_prec = formaterPrix($total_prec);
                                }
                                if( $tabStat[0]['format'] == 'nombre' ){
                                    //$total_prec = round($total_prec,0);
                                    $total_prec = formaterPrix($total_prec,0,"", " ");
                                }
                                */
                                
                                eval(charge_template($langue,$referencepage,"WidgetBitMoyennePrec"));
                            }
                            break;
                        case "mois":
                            $granularite = strtolower($multilangue[mois]);
                            $total = 0;
                            $totalNonFormate = 0 ;
                            $tabDate = array();
                            if($DB_site->num_rows($donnees) > 0){
                                while($donnee = $DB_site->fetch_array($donnees)){
                                    $date_stat = date('m/Y', strtotime($donnee[date]));
                                    $existe = false;
                                    foreach($tabDate as $indexDate => $somme_donnee){
                                        if($indexDate == $date_stat){
                                            $ancienne_stat = $tabDate[$indexDate];
                                            $nouvelle_stat = $ancienne_stat + $donnee[stat];
                                            $tabDate[$indexDate] = $nouvelle_stat;
                                            $existe = true;
                                            break;
                                        }
                                    }
                                    if(!$existe){
                                        $tabDate[$date_stat] = $donnee[stat];
                                    }
                                }
                                $nb_mois = 0;
                                foreach($tabDate as $indexDate => $somme_donnee){
                                    $total += $somme_donnee;
                                    $nb_mois++;
                                }
                                $total = $total/$nb_mois;
                                $totalNonFormate = $total ;
                                
                                $total = HTools::formatPrix($tabStat[0]['format'],$total );
                                /*
                                if( $tabStat[0]['format'] == 'monnaie' ){
                                    $total = formaterPrix($total);
                                }
                                if( $tabStat[0]['format'] == 'nombre' ){
                                    $total = formaterPrix($total,0,"", " ");
                                }
                                */
                            }else{
                                $total = 0; 
                            }
                            if($widget[periode_precedente] == 1){
                                $total_prec = 0;
                                $tabDatePrec = array();
                                while($donnee_prec = $DB_site->fetch_array($donnees_prec)){
                                    $date_stat = date('m/Y', strtotime($donnee_prec[date]));
                                    $existe = false;
                                    foreach($tabDatePrec as $indexDate => $somme_donnee){
                                        if($indexDate == $date_stat){
                                            $ancienne_stat = $tabDatePrec[$indexDate];
                                            $nouvelle_stat = $ancienne_stat + $donnee_prec[stat];
                                            $tabDatePrec[$indexDate] = $nouvelle_stat;
                                            $existe = true;
                                            break;
                                        }
                                    }
                                    if(!$existe){
                                        $tabDatePrec[$date_stat] = $donnee_prec[stat];
                                    }
                                }
                                $nb_mois = 0;
                                foreach($tabDatePrec as $indexDate => $somme_donnee){
                                    $total_prec += $somme_donnee;
                                    $nb_mois++;
                                }
                                $total_prec = $total_prec/$nb_mois;
                                //----------------------------------
                                // accroissement
                                //----------------------------------
                                if( $totalNonFormate > $total_prec ){
                                    $classComparaison = "green_color";
                                }
                                else{
                                    $classComparaison = "red_color";
                                }
                                $tauxAccroissement = calculTauxAccroissement($totalNonFormate,$total_prec); 
                                $tauxAccroissement = '<span class="' . $classComparaison . '">' . $tauxAccroissement . '%</span>';
                                
                                
                                $total_prec = HTools::formatPrix($tabStat[0]['format'],$total_prec );
                                /*
                                if( $tabStat[0]['format'] == 'monnaie' ){
                                    $total_prec = formaterPrix($total_prec);
                                }
                                if( $tabStat[0]['format'] == 'nombre' ){
                                    //$total_prec = round($total_prec,0);
                                    $total_prec = formaterPrix($total_prec,0,"", " ");
                                }
                                */
                                eval(charge_template($langue,$referencepage,"WidgetBitMoyennePrec"));
                            }
                            break;
                        case "année":
                            $granularite = strtolower($multilangue[an]);
                            $total = 0;
                            $totalNonFormate = 0;
                            $tabDate = array();
                            if($DB_site->num_rows($donnees) > 0){
                                while($donnee = $DB_site->fetch_array($donnees)){
                                    $date_stat = date('Y', strtotime($donnee[date]));
                                    $existe = false;
                                    foreach($tabDate as $indexDate => $somme_donnee){
                                        if($indexDate == $date_stat){
                                            $ancienne_stat = $tabDate[$indexDate];
                                            $nouvelle_stat = $ancienne_stat + $donnee[stat];
                                            $tabDate[$indexDate] = $nouvelle_stat;
                                            $existe = true;
                                            break;
                                        }
                                    }
                                    if(!$existe){
                                        $tabDate[$date_stat] = $donnee[stat];
                                    }
                                }
                                $nb_mois = 0;
                                foreach($tabDate as $indexDate => $somme_donnee){
                                    $total += $somme_donnee;
                                    $nb_mois++;
                                }
                                $total = $total/$nb_mois;
                                $totalNonFormate = $total;
                                
                                $total = HTools::formatPrix($tabStat[0]['format'],$total );
                                /*
                                if( $tabStat[0]['format'] == 'monnaie' ){
                                    $total = formaterPrix($total);
                                }
                                if( $tabStat[0]['format'] == 'nombre' ){
                                    $total = formaterPrix($total,0,"", " ");
                                    $total = round($total,0);
                                }
                                */
                            }else{
                                $total = 0; 
                            }
                            if($widget[periode_precedente] == 1){
                                $total_prec = 0;
                                $tabDatePrec = array();
                                
                                while($donnee_prec = $DB_site->fetch_array($donnees_prec)){
                                    $date_stat = date('Y', strtotime($donnee_prec[date]));
                                    $existe = false;
                                    foreach($tabDatePrec as $indexDate => $somme_donnee){
                                        if($indexDate == $date_stat){
                                            $ancienne_stat = $tabDatePrec[$indexDate];
                                            $nouvelle_stat = $ancienne_stat + $donnee_prec[stat];
                                            $tabDatePrec[$indexDate] = $nouvelle_stat;
                                            $existe = true;
                                            break;
                                        }
                                    }
                                    if(!$existe){
                                        $tabDatePrec[$date_stat] = $donnee_prec[stat];
                                    }
                                }
                                $nb_mois = 0;
                                foreach($tabDatePrec as $indexDate => $somme_donnee){
                                    $total_prec += $somme_donnee;
                                    $nb_mois++;
                                }
                                $total_prec = $total_prec/$nb_mois;
                                //----------------------------------
                                // accroissement
                                //----------------------------------
                                if( $totalNonFormate > $total_prec ){
                                    $classComparaison = "green_color";
                                }
                                else{
                                    $classComparaison = "red_color";
                                }
                                $tauxAccroissement = calculTauxAccroissement($totalNonFormate,$total_prec); 
                                $tauxAccroissement = '<span class="' . $classComparaison . '">' . $tauxAccroissement . '%</span>';
                                
                                
                                $total_prec = HTools::formatPrix($tabStat[0]['format'],$total_prec );
                                /*
                                if( $tabStat[0]['format'] == 'monnaie' ){
                                    $total_prec = formaterPrix($total_prec);
                                }
                                if( $tabStat[0]['format'] == 'nombre' ){
                                    $total_prec = formaterPrix($total_prec,0,"", " ");
                                    //$total_prec = round($total_prec,0);
                                }
                                */
                                eval(charge_template($langue,$referencepage,"WidgetBitMoyennePrec"));
                            }
                            break;
                        default:
                            break;
                    }
                    eval(charge_template($langue,$referencepage,"WidgetBitMoyenne"));
                    break;
                case "total":
                    
                    $total = 0;
                    while($donnee = $DB_site->fetch_array($donnees)){
                        $total += $donnee[stat];
                    }
                    $totalNonFormate = $total;
                    
                    $total = HTools::formatPrix($tabStat[0]['format'],$total );
                    /*
                    if( $tabStat[0]['format'] == 'monnaie' ){
                        $total = formaterPrix($total);
                    }
                    if( $tabStat[0]['format'] == 'nombre' ){
                        $total = formaterPrix($total,0,"", " ");
                    }
                    */
                    $classComparaison = "white_color";
                    $tauxAccroissement = "" ;
                                   
                    if($widget[periode_precedente] == 1){
                        $total_prec = 0;
                        $tauxAccroissement = 0 ;
                        while($donnee_prec = $DB_site->fetch_array($donnees_prec)){
                            $total_prec += $donnee_prec[stat];
                        }
                        //----------------------------------
                        // accroissement
                        //----------------------------------
                        if( $totalNonFormate > $total_prec ){
                            $classComparaison = "green_color";
                        }
                        else{
                            $classComparaison = "red_color";
                        }
                        $tauxAccroissement = calculTauxAccroissement($totalNonFormate,$total_prec);
                        $tauxAccroissement = '<span class="' . $classComparaison . '">' . $tauxAccroissement . '%</span>';
                        
                        $total_prec = HTools::formatPrix($tabStat[0]['format'],$total_prec );
                        /*
                        if( $tabStat[0]['format'] == 'monnaie' ){
                            $total_prec = formaterPrix($total_prec);
                        }
                        if( $tabStat[0]['format'] == 'nombre' ){
                            //$total_prec = round($total_prec,0);
                            $total_prec = formaterPrix($total_prec,0,"", " ");
                        }
                        */
                        eval(charge_template($langue,$referencepage,"WidgetBitTotalPrec"));
                    }
                    eval(charge_template($langue,$referencepage,"WidgetBitTotal"));
                    break;
            }
        }
        
        $data .= $dataStat;
        $options .= $optionStat;
        
        $classTitreBloc = "";
        
        $classWidgetBlue = "";
        
        $TemplateIndexWidgetBitVisualTotal = "";
        $TemplateIndexWidgetBitVisualIconChart = "";
        
        $boxMinHeight = 2;
        $zClassNotOverflow = "overflow-y-only";
        $zClassContentTableau = "overflow-y-heigt90" ;
        
        //total et moyenne
        if( $widget[type_representation] == "total" || $widget[type_representation] == "moyenne" ){
            $zClassContentTableau = "";
            $classWidgetBlue = "dashboard-stat blue-madison";
            $classTitreBloc = "titre_bloc" ;
            $zClassNotOverflow = "total_content";
            if( $widget[periode_precedente] == 1 ){
                $boxMinHeight = 3 ;
            }
            
            eval(charge_template($langue,$referencepage,"WidgetBitVisualIconChart"));
            eval(charge_template($langue,$referencepage,"WidgetBitVisualTotal"));
        }
        
        eval(charge_template($langue,$referencepage,"WidgetBit"));
    }
    eval(charge_template($langue,$referencepage,"Widget"));
    }
}

global $TemplateIncludeJs;
$TemplateIncludeJs = "";
eval(charge_template($langue, "include", "js"));

//include "./includes/footer.php";
eval(charge_template($langue,"commun","header"));
eval(charge_template($langue,"commun","footer"));
eval(charge_template($langue,"index","index"));

echo $TemplateIndexIndex;
$DB_site->close();
flush();
?>
