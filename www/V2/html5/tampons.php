<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
if (isset($_SESSION['rapidpub']))
session_unset($_SESSION['rapidpub']);

/** --- includ global  ---*/   
if( !isset($DB_site) ){
    require_once("../global.php");
} 
/** --- includ global  ---*/  

require_once ('includes/functions.php');
function random_color_part() {
    
    return str_pad(dechex(mt_rand(0, 255)), 2, '0', STR_PAD_LEFT);
}
function random_color() {
    
    return random_color_part() . random_color_part() . random_color_part();
}
$arrayColors = array();
for ($i = 0; $i < 70; $i++) {
    
    $arrayColors[] = '#' . random_color();
}
$productID = md5(uniqid(rand(), true));

//get TVA
$idPays = 57;
$pays = $DB_site->query_first("SELECT * FROM pays WHERE paysid = ".$idPays);
$taxeTVANormal = $pays[TVAtauxnormal];

if (strstr($_SERVER['PHP_SELF'], "V2")) {
	$racine = "/V2";
    $racinePdf = "V2";
} else {
	$racine = "/";
    $racinePdf = "";
}

$strDirRoot = $_SERVER['DOCUMENT_ROOT'];

$strUrlModuleHTML5 = "$racine/html5/";

if ($racine == "/V2") {
	$strDirModuleHTML5 = $strDirRoot . "V2/html5/";
} else {
	$strDirModuleHTML5 = $strDirRoot . "html5/";
}

/** ----- get module data feature ----- **/
$articlemoduleid = 9;
$moduleFeature = $DB_site->query_first("SELECT * FROM articlemodule WHERE articlemoduleid=$articlemoduleid");
$idmodule = $moduleFeature[articlemoduleid];
$textemodule = $moduleFeature[textemodule];
$libelle= $moduleFeature[libelle];
$imagemoduleExtension= $moduleFeature[imagemodule];
$imageModule  = "http://".$host."/admin/assets/img/modulehtml5/". $idmodule .".".$imagemoduleExtension;
$imageArticleModule = "";
if($imagemoduleExtension !== null){
	$imageArticleModule =  '<img src="'.$imageModule.'" alt="Créer '.$libelle.' en ligne" title="Créer $libelle en ligne" />';
}else{
	$imageArticleModule = '<img src="http://placehold.it/130x130" alt="Créer test banderole en ligne" title="Produit préféré de nos clients" />';       
}
/** ----- get module data feature----- **/

error_reporting(0);
?>
<link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/themes/blitzer/jquery-ui.css" />
<link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="<?= $strUrlModuleHTML5 ?>css/ddlist.jquery.css" type="text/css" media="all" />
    <link rel="stylesheet" href="<?= $strUrlModuleHTML5 ?>css/tampons.css?<?php
        echo mt_rand(); ?>" type="text/css" media="all" />
        <link rel="stylesheet" href="<?= $strUrlModuleHTML5 ?>css/jquery.mCustomScrollbar.min.css" type="text/css" media="all" />
		<link rel="stylesheet" href="<?= $strUrlModuleHTML5 ?>css/lobibox.css" type="text/css" media="all" />
        <link rel="stylesheet" href="<?= $strUrlModuleHTML5 ?>css/jquery.qtip.min.css" type="text/css" media="all" />
        <link rel="stylesheet" href="<?= $strUrlModuleHTML5 ?>css/spectrum.css" type="text/css" media="all" />
        <link rel="stylesheet" href="<?= $strUrlModuleHTML5 ?>css/hopscotch.min.css" type="text/css" media="all" />
        <link rel="stylesheet" href="<?= $strUrlModuleHTML5 ?>css/sweet-alert.css" type="text/css" media="all" />
        <link rel="stylesheet" href="<?= $strUrlModuleHTML5 ?>fancybox/jquery.fancybox.css?v=2.1.5" type="text/css" media="screen" />
        <div id="tampons_config">
            <div class="container-left">
                <div id="block_left">
                    <div id="text_content">
                        <div class="img">
                           <?php echo $imageArticleModule ?>
                        </div>
                        <div class="text">
                           <h1><?php echo $libelle ?></h1>
                           <p class="mCustomScrollbar" data-mcs-theme="dark">
                              <?php echo $textemodule ?>
                           </p>
                        </div>
                     </div>
                    <div id="cfg_content">
                        <div style="width:325px;height:auto;margin:0 auto;float:left">Longueur : <input title="Cliquez sur un élément de votre création et modifiez ses dimensions ici" class="elemSize" type="text" id="elem_width" disabled / > - Hauteur : <input title="Cliquez sur un élément de votre création et modifiez ses dimensions ici" class="elemSize" type="text" id="elem_height" disabled / ></div><div style="float:left"><img src="<?= $strUrlModuleHTML5 ?>img/valid.png" id="validSize" style="width:15px;height:auto" alt="" title="Valider les modifications"></div>
                    </div>
                    <div id="apercu_content">
                        <div id="tampon_bg">
                            <div id="tampon_content">
                                <div id="margin">
                                    
                                </div>
                            </div>
                            <!--<div id="preview_container">
                                <span id="title">Exemple taille réelle</span>
                                <div id="tampon_preview">
                                
                                </div>
                            </div>-->
                            <div id="disclaimer">
                                <span id="text"><strong>IMPORTANT</strong>: la qualité de l'aperçu n'est pas la qualité finale. Le rendu final sera net et précis.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="container-right">
                <div id="block_right">
                    <div id="config_right">
                        <div id="tampons">
                            <p class="tampon-select">Choisissez votre modèle de tampon</p>
                            <button id="change-modele">Changer de modèle</button>
                            <div class="tamponcontainer">
                                <div id="tampon-printy" class="tampon-fade">
                                    <img src="<?= $strUrlModuleHTML5 ?>img/badge2_tampon_899.png" alt="" id="printy-pricetag">
                                    <a href="#"><img src="<?= $strUrlModuleHTML5 ?>img/small-tampon-printy.jpg" alt="Créer son tampon à encre Printy sur mesure"></a>
                                    <span class="descriptif-tampon">Tampon Printy</span>
                                </div>
                                <div id="tampon-metalline" class="tampon-fade">
                                    <img src="<?= $strUrlModuleHTML5 ?>img/badge2_tampon_1099.png" alt="" id="metalline-pricetag">
                                    <a href="#"><img src="<?= $strUrlModuleHTML5 ?>img/small-tampon-metalline.jpg" alt="Créer son tampon à encre Metal Line sur mesure"></a>
                                    <span class="descriptif-tampon">Tampon Metal Line</span>
                                </div>
                            </div>
                            <div id="tampon-metallline-content" class="tampon-content">
                                <ul id="ulmetalline">
                                    <li class="select-tampon" title="Trodat 5200" data-tampon-ws="500x293" data-tampon-prev="155x90" data-tampon-prevcm="4.1x5.2">
                                        <div class="tampon-img">
                                            <img src="<?= $strUrlModuleHTML5 ?>img/tampons/5200-img.jpg" data-zoom-image="<?= $strUrlModuleHTML5 ?>img/tampons/5200-img-big.jpg" alt="">
                                        </div>
                                        <div class="tampon-ex">
                                            <img src="<?= $strUrlModuleHTML5 ?>img/tampons/5200-ex.jpg" alt="">
                                        </div>
                                        <div class="tampon-colors">
                                            <span>Couleurs disponibles</span>
                                            <img data-colorselect="select-black" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/19171C.jpg" alt="">
                                            <img data-colorselect="select-purple" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/554D96.jpg" alt="">
                                            <img data-colorselect="select-green" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/59AC26.jpg" alt="">
                                            <img data-colorselect="select-pink" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/B8027B.jpg" alt="">
                                            <img data-colorselect="select-red" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/E30117.jpg" alt="">
                                        </div>
                                        <span class="tampon-prix"><span>Trodat 5200</span> - Taille de gravure max 4,10 x 5,20 cm</span>
                                    </li>
                                    <li class="select-tampon" title="Trodat 5203" data-tampon-ws="500x284" data-tampon-prev="185x106" data-tampon-prevcm="4.9x2.8">
                                        <div class="tampon-img">
                                            <img src="<?= $strUrlModuleHTML5 ?>img/tampons/5203-img.jpg" data-zoom-image="<?= $strUrlModuleHTML5 ?>img/tampons/5203-img-big.jpg" alt="">
                                        </div>
                                        <div class="tampon-ex">
                                            <img src="<?= $strUrlModuleHTML5 ?>img/tampons/5203-ex.jpg" alt="">
                                        </div>
                                        <div class="tampon-colors">
                                            <span>Couleurs disponibles</span>
                                            <img data-colorselect="select-black" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/19171C.jpg" alt="">
                                            <img data-colorselect="select-purple" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/554D96.jpg" alt="">
                                            <img data-colorselect="select-green" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/59AC26.jpg" alt="">
                                            <img data-colorselect="select-pink" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/B8027B.jpg" alt="">
                                            <img data-colorselect="select-red" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/E30117.jpg" alt="">
                                        </div>
                                        <span class="tampon-prix"><span>Trodat 5203</span> - Taille de gravure max 4,90 x 2,80 cm</span>
                                    </li>
                                    <li class="select-tampon" title="Trodat 5204" data-tampon-ws="500x233" data-tampon-prev="212x98" data-tampon-prevcm="5.6x2.6">
                                        <div class="tampon-img">
                                            <img src="<?= $strUrlModuleHTML5 ?>img/tampons/5204-img.jpg" data-zoom-image="<?= $strUrlModuleHTML5 ?>img/tampons/5204-img-big.jpg" alt="">
                                        </div>
                                        <div class="tampon-ex">
                                            <img src="<?= $strUrlModuleHTML5 ?>img/tampons/5204-ex.jpg" alt="" style="width:256px">
                                        </div>
                                        <div class="tampon-colors">
                                            <span>Couleurs disponibles</span>
                                            <img data-colorselect="select-black" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/19171C.jpg" alt="">
                                            <img data-colorselect="select-purple" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/554D96.jpg" alt="">
                                            <img data-colorselect="select-green" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/59AC26.jpg" alt="">
                                            <img data-colorselect="select-pink" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/B8027B.jpg" alt="">
                                            <img data-colorselect="select-red" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/E30117.jpg" alt="">
                                        </div>
                                        <span class="tampon-prix"><span>Trodat 5204</span> - Taille de gravure max 5,60 x 2,60 cm</span>
                                    </li>
                                    <li class="select-tampon" title="Trodat 5205" data-tampon-ws="500x175" data-tampon-prev="264x94" data-tampon-prevcm="7x2.5">
                                        <div class="tampon-img">
                                            <img src="<?= $strUrlModuleHTML5 ?>img/tampons/5205-img.jpg" data-zoom-image="<?= $strUrlModuleHTML5 ?>img/tampons/5205-img-big.jpg" alt="">
                                        </div>
                                        <div class="tampon-ex">
                                            <img src="<?= $strUrlModuleHTML5 ?>img/tampons/5205-ex.jpg" alt="" style="width:255px">
                                        </div>
                                        <div class="tampon-colors">
                                            <span>Couleurs disponibles</span>
                                            <img data-colorselect="select-black" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/19171C.jpg" alt="">
                                            <img data-colorselect="select-purple" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/554D96.jpg" alt="">
                                            <img data-colorselect="select-green" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/59AC26.jpg" alt="">
                                            <img data-colorselect="select-pink" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/B8027B.jpg" alt="">
                                            <img data-colorselect="select-red" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/E30117.jpg" alt="">
                                        </div>
                                        <span class="tampon-prix"><span>Trodat 5205</span> - Taille de gravure max 7 x 2,50 cm</span>
                                    </li>
                                    <li class="select-tampon" title="Trodat 5206" data-tampon-ws="500x295" data-tampon-prev="212x125" data-tampon-prevcm="5.6x3.3">
                                        <div class="tampon-img">
                                            <img src="<?= $strUrlModuleHTML5 ?>img/tampons/5206-img.jpg" data-zoom-image="<?= $strUrlModuleHTML5 ?>img/tampons/5206-img-big.jpg" alt="">
                                        </div>
                                        <div class="tampon-ex">
                                            <img src="<?= $strUrlModuleHTML5 ?>img/tampons/5206-ex.jpg" alt="">
                                        </div>
                                        <div class="tampon-colors">
                                            <span>Couleurs disponibles</span>
                                            <img data-colorselect="select-black" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/19171C.jpg" alt="">
                                            <img data-colorselect="select-purple" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/554D96.jpg" alt="">
                                            <img data-colorselect="select-green" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/59AC26.jpg" alt="">
                                            <img data-colorselect="select-pink" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/B8027B.jpg" alt="">
                                            <img data-colorselect="select-red" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/E30117.jpg" alt="">
                                        </div>
                                        <span class="tampon-prix"><span>Trodat 5206</span> - Taille de gravure max 5,60 x 3,30 cm</span>
                                    </li>
                                    <li class="select-tampon" title="Trodat 5207" data-tampon-ws="500x332" data-tampon-prev="227x151" data-tampon-prevcm="6x4">
                                        <div class="tampon-img">
                                            <img src="<?= $strUrlModuleHTML5 ?>img/tampons/5207-img.jpg" data-zoom-image="<?= $strUrlModuleHTML5 ?>img/tampons/5207-img-big.jpg" alt="">
                                        </div>
                                        <div class="tampon-ex">
                                            <img src="<?= $strUrlModuleHTML5 ?>img/tampons/5207-ex.jpg" alt="">
                                        </div>
                                        <div class="tampon-colors">
                                            <span>Couleurs disponibles</span>
                                            <img data-colorselect="select-black" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/19171C.jpg" alt="">
                                            <img data-colorselect="select-purple" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/554D96.jpg" alt="">
                                            <img data-colorselect="select-green" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/59AC26.jpg" alt="">
                                            <img data-colorselect="select-pink" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/B8027B.jpg" alt="">
                                            <img data-colorselect="select-red" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/E30117.jpg" alt="">
                                        </div>
                                        <span class="tampon-prix"><span>Trodat 5207</span> - Taille de gravure max 6 x 4 cm</span>
                                    </li>
                                    <li class="select-tampon" title="Trodat 5208" data-tampon-ws="500x344" data-tampon-prev="257x178" data-tampon-prevcm="6.8x4.7">
                                        <div class="tampon-img">
                                            <img src="<?= $strUrlModuleHTML5 ?>img/tampons/5208-img.jpg" data-zoom-image="<?= $strUrlModuleHTML5 ?>img/tampons/5208-img-big.jpg" alt="">
                                        </div>
                                        <div class="tampon-ex">
                                            <img src="<?= $strUrlModuleHTML5 ?>img/tampons/5208-ex.jpg" alt="">
                                        </div>
                                        <div class="tampon-colors">
                                            <span>Couleurs disponibles</span>
                                            <img data-colorselect="select-black" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/19171C.jpg" alt="">
                                            <img data-colorselect="select-purple" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/554D96.jpg" alt="">
                                            <img data-colorselect="select-green" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/59AC26.jpg" alt="">
                                            <img data-colorselect="select-pink" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/B8027B.jpg" alt="">
                                            <img data-colorselect="select-red" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/E30117.jpg" alt="">
                                        </div>
                                        <span class="tampon-prix"><span>Trodat 5208</span> - Taille de gravure max 6,80 x 4,70 cm</span>
                                    </li>
                                    <li class="select-tampon" title="Trodat 5211" data-tampon-ws="500x323" data-tampon-prev="321x208" data-tampon-prevcm="8.5x5.5">
                                        <div class="tampon-img">
                                            <img src="<?= $strUrlModuleHTML5 ?>img/tampons/5211-img.jpg" data-zoom-image="<?= $strUrlModuleHTML5 ?>img/tampons/5211-img-big.jpg" alt="" style="height:85px;">
                                        </div>
                                        <div class="tampon-ex">
                                            <img src="<?= $strUrlModuleHTML5 ?>img/tampons/5211-ex.jpg" alt="">
                                        </div>
                                        <div class="tampon-colors">
                                            <span>Couleurs disponibles</span>
                                            <img data-colorselect="select-black" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/19171C.jpg" alt="">
                                            <img data-colorselect="select-purple" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/554D96.jpg" alt="">
                                            <img data-colorselect="select-green" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/59AC26.jpg" alt="">
                                            <img data-colorselect="select-pink" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/B8027B.jpg" alt="">
                                            <img data-colorselect="select-red" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/E30117.jpg" alt="">
                                        </div>
                                        <span class="tampon-prix"><span>Trodat 5211</span> - Taille de gravure max 8,50 x 5,50 cm</span>
                                    </li>
                                </ul>
                            </div>
                            <div id="tampon-printy-content" class="tampon-content">
                                <ul id="ulprinty">
                                    <li class="select-tampon" title="Trodat 4910" data-tampon-ws="500x176" data-tampon-prev="98x34" data-tampon-prevcm="3.8x1.4">
                                        <div class="tampon-img">
                                            <img src="<?= $strUrlModuleHTML5 ?>img/tampons/4910-img.jpg" data-zoom-image="<?= $strUrlModuleHTML5 ?>img/tampons/4911-img-big.jpg" alt="" style="height:85px;">
                                        </div>
                                        <div class="tampon-ex">
                                            <img src="<?= $strUrlModuleHTML5 ?>img/tampons/4910-ex.jpg" alt="">
                                        </div>
                                        <div class="tampon-colors">
                                            <span>Couleurs disponibles</span>
                                            <img data-colorselect="select-black" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/19171C.jpg" alt="">
                                            <img data-colorselect="select-purple" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/554D96.jpg" alt="">
                                            <img data-colorselect="select-green" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/59AC26.jpg" alt="">
                                            <img data-colorselect="select-pink" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/B8027B.jpg" alt="">
                                            <img data-colorselect="select-red" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/E30117.jpg" alt="">
                                        </div>
                                        <span class="tampon-prix"><span>Trodat 4910</span> - Taille de gravure max 3,80 x 1,40 cm</span>
                                    </li>
                                     <li class="select-tampon" title="Trodat 4911" data-tampon-ws="500x185" data-tampon-prev="144x53" data-tampon-prevcm="3.8x1.4">
                                        <div class="tampon-img">
                                            <img src="<?= $strUrlModuleHTML5 ?>img/tampons/4911-img.jpg" data-zoom-image="<?= $strUrlModuleHTML5 ?>img/tampons/4911-img-big.jpg" alt="" style="height:85px;">
                                        </div>
                                        <div class="tampon-ex">
                                            <img src="<?= $strUrlModuleHTML5 ?>img/tampons/4911-ex.jpg" alt="">
                                        </div>
                                        <div class="tampon-colors">
                                            <span>Couleurs disponibles</span>
                                            <img data-colorselect="select-black" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/19171C.jpg" alt="">
                                            <img data-colorselect="select-purple" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/554D96.jpg" alt="">
                                            <img data-colorselect="select-green" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/59AC26.jpg" alt="">
                                            <img data-colorselect="select-pink" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/B8027B.jpg" alt="">
                                            <img data-colorselect="select-red" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/E30117.jpg" alt="">
                                        </div>
                                        <span class="tampon-prix"><span>Trodat 4911</span> - Taille de gravure max 3,80 x 1,40 cm</span>
                                    </li>
                                    <li class="select-tampon" title="Trodat 4912" data-tampon-ws="500x192" data-tampon-prev="178x68" data-tampon-prevcm="4.7x1.8">
                                        <div class="tampon-img">
                                            <img src="<?= $strUrlModuleHTML5 ?>img/tampons/4912-img.jpg" data-zoom-image="<?= $strUrlModuleHTML5 ?>img/tampons/4912-img-big.jpg" alt="">
                                        </div>
                                        <div class="tampon-ex">
                                            <img src="<?= $strUrlModuleHTML5 ?>img/tampons/4912-ex.jpg" alt="">
                                        </div>
                                        <div class="tampon-colors">
                                            <span>Couleurs disponibles</span>
                                            <img data-colorselect="select-black" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/19171C.jpg" alt="">
                                            <img data-colorselect="select-purple" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/554D96.jpg" alt="">
                                            <img data-colorselect="select-green" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/59AC26.jpg" alt="">
                                            <img data-colorselect="select-pink" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/B8027B.jpg" alt="">
                                            <img data-colorselect="select-red" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/E30117.jpg" alt="">
                                        </div>
                                        <span class="tampon-prix"><span>Trodat 4912</span> - Taille de gravure max 4,70 x 1,80 cm</span>
                                    </li>
                                    <li class="select-tampon" title="Trodat 4913" data-tampon-ws="500x189" data-tampon-prev="219x83" data-tampon-prevcm="5.8x2.2">
                                        <div class="tampon-img">
                                            <img src="<?= $strUrlModuleHTML5 ?>img/tampons/4913-img.jpg" data-zoom-image="<?= $strUrlModuleHTML5 ?>img/tampons/4913-img-big.jpg" alt="">
                                        </div>
                                        <div class="tampon-ex">
                                            <img src="<?= $strUrlModuleHTML5 ?>img/tampons/4913-ex.jpg" alt="">
                                        </div>
                                        <div class="tampon-colors">
                                            <span>Couleurs disponibles</span>
                                            <img data-colorselect="select-black" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/19171C.jpg" alt="">
                                            <img data-colorselect="select-purple" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/554D96.jpg" alt="">
                                            <img data-colorselect="select-green" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/59AC26.jpg" alt="">
                                            <img data-colorselect="select-pink" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/B8027B.jpg" alt="">
                                            <img data-colorselect="select-red" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/E30117.jpg" alt="">
                                        </div>
                                        <span class="tampon-prix"><span>Trodat 4913</span> - Taille de gravure max 5,80 x 2,20 cm</span>
                                    </li>
                                    <li class="select-tampon" title="Trodat 4914" data-tampon-ws="500x204" data-tampon-prev="242x98" data-tampon-prevcm="6.4x2.6">
                                        <div class="tampon-img">
                                            <img src="<?= $strUrlModuleHTML5 ?>img/tampons/4914-img.jpg" data-zoom-image="<?= $strUrlModuleHTML5 ?>img/tampons/4914-img-big.jpg" alt="">
                                        </div>
                                        <div class="tampon-ex">
                                            <img src="<?= $strUrlModuleHTML5 ?>img/tampons/4914-ex.jpg" alt="">
                                        </div>
                                        <div class="tampon-colors">
                                            <span>Couleurs disponibles</span>
                                            <img data-colorselect="select-black" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/19171C.jpg" alt="">
                                            <img data-colorselect="select-purple" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/554D96.jpg" alt="">
                                            <img data-colorselect="select-green" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/59AC26.jpg" alt="">
                                            <img data-colorselect="select-pink" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/B8027B.jpg" alt="">
                                            <img data-colorselect="select-red" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/E30117.jpg" alt="">
                                        </div>
                                        <span class="tampon-prix"><span>Trodat 4914</span> - Taille de gravure max 6,40 x 2,60 cm</span>
                                    </li>
                                    <li class="select-tampon" title="Trodat 4915" data-tampon-ws="500x179" data-tampon-prev="264x94" data-tampon-prevcm="7x2.5">
                                        <div class="tampon-img">
                                            <img src="<?= $strUrlModuleHTML5 ?>img/tampons/4915-img.jpg" data-zoom-image="<?= $strUrlModuleHTML5 ?>img/tampons/4915-img-big.jpg" alt="">
                                        </div>
                                        <div class="tampon-ex">
                                            <img src="<?= $strUrlModuleHTML5 ?>img/tampons/4915-ex.jpg" alt="">
                                        </div>
                                        <div class="tampon-colors">
                                            <span>Couleurs disponibles</span>
                                            <img data-colorselect="select-black" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/19171C.jpg" alt="">
                                            <img data-colorselect="select-purple" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/554D96.jpg" alt="">
                                            <img data-colorselect="select-green" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/59AC26.jpg" alt="">
                                            <img data-colorselect="select-pink" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/B8027B.jpg" alt="">
                                            <img data-colorselect="select-red" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/E30117.jpg" alt="">
                                        </div>
                                        <span class="tampon-prix"><span>Trodat 4915</span>- Taille de gravure max 7 x 2,50 cm</span>
                                    </li>
                                    <li class="select-tampon" title="Trodat 4916" data-tampon-ws="500x71" data-tampon-prev="264x38" data-tampon-prevcm="7x1">
                                        <div class="tampon-img">
                                            <img src="<?= $strUrlModuleHTML5 ?>img/tampons/4916-img.jpg" data-zoom-image="<?= $strUrlModuleHTML5 ?>img/tampons/4916-img-big.jpg" alt="" style="width:85px">
                                        </div>
                                        <div class="tampon-ex">
                                            <img src="<?= $strUrlModuleHTML5 ?>img/tampons/4916-ex.jpg" alt="">
                                        </div>
                                        <div class="tampon-colors">
                                            <span>Couleurs disponibles</span>
                                            <img data-colorselect="select-black" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/19171C.jpg" alt="">
                                            <img data-colorselect="select-purple" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/554D96.jpg" alt="">
                                            <img data-colorselect="select-green" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/59AC26.jpg" alt="">
                                            <img data-colorselect="select-pink" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/B8027B.jpg" alt="">
                                            <img data-colorselect="select-red" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/E30117.jpg" alt="">
                                        </div>
                                        <span class="tampon-prix"><span>Trodat 4916</span> - Taille de gravure max 7 x 1 cm</span>
                                    </li>
                                    <li class="select-tampon" title="Trodat 4917" data-tampon-ws="500x99" data-tampon-prev="189x38" data-tampon-prevcm="5x1">
                                        <div class="tampon-img">
                                            <img src="<?= $strUrlModuleHTML5 ?>img/tampons/4917-img.jpg" data-zoom-image="<?= $strUrlModuleHTML5 ?>img/tampons/4917-img-big.jpg" alt="">
                                        </div>
                                        <div class="tampon-ex">
                                            <img src="<?= $strUrlModuleHTML5 ?>img/tampons/4917-ex.jpg" alt="">
                                        </div>
                                        <div class="tampon-colors">
                                            <span>Couleurs disponibles</span>
                                            <img data-colorselect="select-black" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/19171C.jpg" alt="">
                                            <img data-colorselect="select-purple" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/554D96.jpg" alt="">
                                            <img data-colorselect="select-green" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/59AC26.jpg" alt="">
                                            <img data-colorselect="select-pink" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/B8027B.jpg" alt="">
                                            <img data-colorselect="select-red" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/E30117.jpg" alt="">
                                        </div>
                                        <span class="tampon-prix"><span>Trodat 4917</span> - Taille de gravure max 5 x 1 cm</span>
                                    </li>
                                    <li class="select-tampon" title="Trodat 4918" data-tampon-ws="500x162" data-tampon-prev="283x57" data-tampon-prevcm="7.5x1.5">
                                        <div class="tampon-img">
                                            <img src="<?= $strUrlModuleHTML5 ?>img/tampons/4918-img.jpg" data-zoom-image="<?= $strUrlModuleHTML5 ?>img/tampons/4918-img-big.jpg" alt="" style="width:85px">
                                        </div>
                                        <div class="tampon-ex">
                                            <img src="<?= $strUrlModuleHTML5 ?>img/tampons/4918-ex.jpg" alt="">
                                        </div>
                                        <div class="tampon-colors">
                                            <span>Couleurs disponibles</span>
                                            <img data-colorselect="select-black" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/19171C.jpg" alt="">
                                            <img data-colorselect="select-purple" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/554D96.jpg" alt="">
                                            <img data-colorselect="select-green" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/59AC26.jpg" alt="">
                                            <img data-colorselect="select-pink" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/B8027B.jpg" alt="">
                                            <img data-colorselect="select-red" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/E30117.jpg" alt="">
                                        </div>
                                        <span class="tampon-prix"><span>Trodat 4918</span> - Taille de gravure max 7,5 x 1,5 cm</span>
                                    </li>
                                    <li class="select-tampon" title="Trodat 4921" data-tampon-ws="300x300" data-tampon-prev="45x45" data-tampon-prevcm="1.2x1.2">
                                        <div class="tampon-img">
                                            <img src="<?= $strUrlModuleHTML5 ?>img/tampons/4921-img.jpg" data-zoom-image="<?= $strUrlModuleHTML5 ?>img/tampons/4921-img-big.jpg" alt="">
                                        </div>
                                        <div class="tampon-ex">
                                            <img src="<?= $strUrlModuleHTML5 ?>img/tampons/4921-ex.jpg" alt="">
                                        </div>
                                        <div class="tampon-colors">
                                            <span>Couleurs disponibles</span>
                                            <img data-colorselect="select-black" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/19171C.jpg" alt="">
                                            <img data-colorselect="select-purple" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/554D96.jpg" alt="">
                                            <img data-colorselect="select-green" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/59AC26.jpg" alt="">
                                            <img data-colorselect="select-pink" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/B8027B.jpg" alt="">
                                            <img data-colorselect="select-red" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/E30117.jpg" alt="">
                                        </div>
                                        <span class="tampon-prix"><span>Trodat 4921</span> - Taille de gravure max 1,2 x 1,2 cm</span>
                                    </li>
                                    <li class="select-tampon" title="Trodat 4922" data-tampon-ws="300x300" data-tampon-prev="75x75">
                                        <div class="tampon-img">
                                            <img src="<?= $strUrlModuleHTML5 ?>img/tampons/4922-img.jpg" data-zoom-image="<?= $strUrlModuleHTML5 ?>img/tampons/4922-img-big.jpg" alt="">
                                        </div>
                                        <div class="tampon-ex">
                                            <img src="<?= $strUrlModuleHTML5 ?>img/tampons/4922-ex.jpg" alt="">
                                        </div>
                                        <div class="tampon-colors">
                                            <span>Couleurs disponibles</span>
                                            <img data-colorselect="select-black" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/19171C.jpg" alt="">
                                            <img data-colorselect="select-purple" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/554D96.jpg" alt="">
                                            <img data-colorselect="select-green" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/59AC26.jpg" alt="">
                                            <img data-colorselect="select-pink" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/B8027B.jpg" alt="">
                                            <img data-colorselect="select-red" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/E30117.jpg" alt="">
                                        </div>
                                        <span class="tampon-prix"><span>Trodat 4922</span> - Taille de gravure max 2 x 2 cm</span>
                                    </li>
                                    <li class="select-tampon" title="Trodat 4923" data-tampon-ws="300x300" data-tampon-prev="113x113" data-tampon-prevcm="3x3">
                                        <div class="tampon-img">
                                            <img src="<?= $strUrlModuleHTML5 ?>img/tampons/4923-img.jpg" data-zoom-image="<?= $strUrlModuleHTML5 ?>img/tampons/4923-img-big.jpg" alt="">
                                        </div>
                                        <div class="tampon-ex">
                                            <img src="<?= $strUrlModuleHTML5 ?>img/tampons/4923-ex.jpg" alt="">
                                        </div>
                                        <div class="tampon-colors">
                                            <span>Couleurs disponibles</span>
                                            <img data-colorselect="select-black" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/19171C.jpg" alt="">
                                            <img data-colorselect="select-purple" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/554D96.jpg" alt="">
                                            <img data-colorselect="select-green" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/59AC26.jpg" alt="">
                                            <img data-colorselect="select-pink" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/B8027B.jpg" alt="">
                                            <img data-colorselect="select-red" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/E30117.jpg" alt="">
                                        </div>
                                        <span class="tampon-prix"><span>Trodat 4923</span> - Taille de gravure max 3 x 3 cm</span>
                                    </li>
                                    <li class="select-tampon" title="Trodat 4924" data-tampon-ws="300x300" data-tampon-prev="151x151" data-tampon-prevcm="4x4">
                                        <div class="tampon-img">
                                            <img src="<?= $strUrlModuleHTML5 ?>img/tampons/4924-img.jpg" data-zoom-image="<?= $strUrlModuleHTML5 ?>img/tampons/4924-img-big.jpg" alt="">
                                        </div>
                                        <div class="tampon-ex">
                                            <img src="<?= $strUrlModuleHTML5 ?>img/tampons/4924-ex.jpg" alt="">
                                        </div>
                                        <div class="tampon-colors">
                                            <span>Couleurs disponibles</span>
                                            <img data-colorselect="select-black" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/19171C.jpg" alt="">
                                            <img data-colorselect="select-purple" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/554D96.jpg" alt="">
                                            <img data-colorselect="select-green" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/59AC26.jpg" alt="">
                                            <img data-colorselect="select-pink" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/B8027B.jpg" alt="">
                                            <img data-colorselect="select-red" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/E30117.jpg" alt="">
                                        </div>
                                        <span class="tampon-prix"><span>Trodat 4924</span> - Taille de gravure max 4 x 4 cm</span>
                                    </li>
                                    <li class="select-tampon" title="Trodat 4925" data-tampon-ws="500x153" data-tampon-prev="310x94" data-tampon-prevcm="8.2x2.5">
                                        <div class="tampon-img">
                                            <img src="<?= $strUrlModuleHTML5 ?>img/tampons/4925-img.jpg" data-zoom-image="<?= $strUrlModuleHTML5 ?>img/tampons/4925-img-big.jpg" alt="" style="width:85px">
                                        </div>
                                        <div class="tampon-ex">
                                            <img src="<?= $strUrlModuleHTML5 ?>img/tampons/4925-ex.jpg" alt="">
                                        </div>
                                        <div class="tampon-colors">
                                            <span>Couleurs disponibles</span>
                                            <img data-colorselect="select-black" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/19171C.jpg" alt="">
                                            <img data-colorselect="select-purple" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/554D96.jpg" alt="">
                                            <img data-colorselect="select-green" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/59AC26.jpg" alt="">
                                            <img data-colorselect="select-pink" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/B8027B.jpg" alt="">
                                            <img data-colorselect="select-red" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/E30117.jpg" alt="">
                                        </div>
                                        <span class="tampon-prix"><span>Trodat 4925</span> - Taille de gravure max 8,2 x 2,5 cm</span>
                                    </li>
                                    <li class="select-tampon" title="Trodat 4926" data-tampon-ws="500x254" data-tampon-prev="283x144">
                                        <div class="tampon-img">
                                            <img src="<?= $strUrlModuleHTML5 ?>img/tampons/4926-img.jpg" data-zoom-image="<?= $strUrlModuleHTML5 ?>img/tampons/4926-img-big.jpg" alt="" style="width:85px">
                                        </div>
                                        <div class="tampon-ex">
                                            <img src="<?= $strUrlModuleHTML5 ?>img/tampons/4926-ex.jpg" alt="">
                                        </div>
                                        <div class="tampon-colors">
                                            <span>Couleurs disponibles</span>
                                            <img data-colorselect="select-black" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/19171C.jpg" alt="">
                                            <img data-colorselect="select-purple" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/554D96.jpg" alt="">
                                            <img data-colorselect="select-green" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/59AC26.jpg" alt="">
                                            <img data-colorselect="select-pink" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/B8027B.jpg" alt="">
                                            <img data-colorselect="select-red" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/E30117.jpg" alt="">
                                        </div>
                                        <span class="tampon-prix"><span>Trodat 4926</span> - Taille de gravure max 7,5 x 3,8 cm</span>
                                    </li>
                                    <li class="select-tampon" title="Trodat 4927" data-tampon-ws="500x332" data-tampon-prev="227x151" data-tampon-prevcm="6x4">
                                        <div class="tampon-img">
                                            <img src="<?= $strUrlModuleHTML5 ?>img/tampons/4927-img.jpg" data-zoom-image="<?= $strUrlModuleHTML5 ?>img/tampons/4927-img-big.jpg" alt="">
                                        </div>
                                        <div class="tampon-ex">
                                            <img src="<?= $strUrlModuleHTML5 ?>img/tampons/4927-ex.jpg" alt="">
                                        </div>
                                        <div class="tampon-colors">
                                            <span>Couleurs disponibles</span>
                                            <img data-colorselect="select-black" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/19171C.jpg" alt="">
                                            <img data-colorselect="select-purple" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/554D96.jpg" alt="">
                                            <img data-colorselect="select-green" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/59AC26.jpg" alt="">
                                            <img data-colorselect="select-pink" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/B8027B.jpg" alt="">
                                            <img data-colorselect="select-red" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/E30117.jpg" alt="">
                                        </div>
                                        <span class="tampon-prix"><span>Trodat 4927</span> - Taille de gravure max 6 x 4 cm</span>
                                    </li>
                                    <li class="select-tampon" title="Trodat 4928" data-tampon-ws="500x276" data-tampon-prev="227x125" data-tampon-prevcm="6x3.3">
                                        <div class="tampon-img">
                                            <img src="<?= $strUrlModuleHTML5 ?>img/tampons/4928-img.jpg" data-zoom-image="<?= $strUrlModuleHTML5 ?>img/tampons/4928-img-big.jpg" alt="">
                                        </div>
                                        <div class="tampon-ex">
                                            <img src="<?= $strUrlModuleHTML5 ?>img/tampons/4928-ex.jpg" alt="">
                                        </div>
                                        <div class="tampon-colors">
                                            <span>Couleurs disponibles</span>
                                            <img data-colorselect="select-black" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/19171C.jpg" alt="">
                                            <img data-colorselect="select-purple" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/554D96.jpg" alt="">
                                            <img data-colorselect="select-green" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/59AC26.jpg" alt="">
                                            <img data-colorselect="select-pink" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/B8027B.jpg" alt="">
                                            <img data-colorselect="select-red" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/E30117.jpg" alt="">
                                        </div>
                                        <span class="tampon-prix"><span>Trodat 4928</span> - Taille de gravure max 6 x 3,3 cm</span>
                                    </li>
                                    <li class="select-tampon" title="Trodat 4929" data-tampon-ws="500x300" data-tampon-prev="189x113" data-tampon-prevcm="5x3">
                                        <div class="tampon-img">
                                            <img src="<?= $strUrlModuleHTML5 ?>img/tampons/4929-img.jpg" data-zoom-image="<?= $strUrlModuleHTML5 ?>img/tampons/4929-img-big.jpg" alt="">
                                        </div>
                                        <div class="tampon-ex">
                                            <img src="<?= $strUrlModuleHTML5 ?>img/tampons/4929-ex.jpg" alt="">
                                        </div>
                                        <div class="tampon-colors">
                                            <span>Couleurs disponibles</span>
                                            <img data-colorselect="select-black" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/19171C.jpg" alt="">
                                            <img data-colorselect="select-purple" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/554D96.jpg" alt="">
                                            <img data-colorselect="select-green" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/59AC26.jpg" alt="">
                                            <img data-colorselect="select-pink" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/B8027B.jpg" alt="">
                                            <img data-colorselect="select-red" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/E30117.jpg" alt="">
                                        </div>
                                        <span class="tampon-prix"><span>Trodat 4929</span> - Taille de gravure max 5 x 3 cm</span>
                                    </li>
                                    <li class="select-tampon" title="Trodat 4931" data-tampon-ws="500x214" data-tampon-prev="264x113" data-tampon-prevcm="7x3">
                                        <div class="tampon-img">
                                            <img src="<?= $strUrlModuleHTML5 ?>img/tampons/4931-img.jpg" data-zoom-image="<?= $strUrlModuleHTML5 ?>img/tampons/4931-img-big.jpg" alt="">
                                        </div>
                                        <div class="tampon-ex">
                                            <img src="<?= $strUrlModuleHTML5 ?>img/tampons/4931-ex.jpg" alt="">
                                        </div>
                                        <div class="tampon-colors">
                                            <span>Couleurs disponibles</span>
                                            <img data-colorselect="select-black" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/19171C.jpg" alt="">
                                            <img data-colorselect="select-purple" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/554D96.jpg" alt="">
                                            <img data-colorselect="select-green" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/59AC26.jpg" alt="">
                                            <img data-colorselect="select-pink" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/B8027B.jpg" alt="">
                                            <img data-colorselect="select-red" class="colorselectc" src="<?= $strUrlModuleHTML5 ?>img/tampons/E30117.jpg" alt="">
                                        </div>
                                        <span class="tampon-prix"><span>Trodat 4931</span> - Taille de gravure max 7 x 3 cm</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="block_right2">
                    <div id="text_right">
                            <!--               sosso             ajout svg-->
				<div id="text_right">
				<div class="text_left"></div><input type="text" placeholder="Insérer un texte" value="" id="textecfg" name="textecfg">
                                <label style="float: left; font-size: 12px;line-height: 30px;">Selectionnez la taille</label>
                                <input type="number" placeholder="" value="12" id="textecfgsize" name="textecfgsize" max="1000" min="1">
                                <button title="Ajouter le texte saisi" id="add_text">ok</button>
			</div>
			</div>
                    <div id="policecontainer">
                        <select id="police" name="police">
                            <?php
                                $polices = array();
                                if ($handle = opendir('./fonts')) {
                                    while (false !== ($entry = readdir($handle))) {
                                        if ($entry != "." && $entry != "..") {
                                            $polices[] = $entry;
                                        }
                                    }                                    
                                    closedir($handle);
                                    sort($polices);                                    
                                    foreach ($polices as $police) {
                                        
                                        $fontname = explode('.', $police);
                                        $im = imagecreatetruecolor(100, 30);
                                        $white = imagecolorallocate($im, 255, 255, 255);
                                        $grey = imagecolorallocate($im, 128, 128, 128);
                                        $black = imagecolorallocate($im, 0, 0, 0);
                                        imagefilledrectangle($im, 0, 0, 99, 29, $white);
                                        imagettftext($im, 10, 0, 11, 21, $grey, 'fonts/' . $police, str_replace('_', ' ', $fontname[0]));
                                        imagettftext($im, 10, 0, 10, 20, $black, 'fonts/' . $police, str_replace('_', ' ', $fontname[0]));
                                        ob_start();
                                        imagepng($im);
                                        $img = ob_get_clean();
                                        echo '<option value="' . $police . '">' . str_replace('_', ' ', $fontname[0]) . '</option>';
                                        imagedestroy($im);
                                    }
                                }
                            ?>
                        </select>
                    </div>
                    <div class="step_container" style="width:145px"><span class="step">1</span><span class="step_title">Choisir une police</span></div>
                </div>
                <div id="block_right3" style="background-color:#F4F4F4">
                    <div style="margin-bottom:10px;padding-top:5px">
 				<div class="step_container" style="width:165px;margin-right: 45px;"><span class="step">3</span><span class="step_title">Insérer un pictogramme</span></div>
 				<select id="themes" name="themes">
 					<option value="divers" selected="selected">Divers</option>
 					<option value="vehicules">Véhicules</option>
 					<option value="personnages">Personnages</option>
 					<option value="signes">Signes</option>
 					<option value="outils">Outils</option>
                                        <option value="svg">svg</option>
 				</select>
 			</div>
 			<div id="motifs_contain">
 				<div id="divers_container" class="motifs_container">
 					<?php
 						$files = glob( $strDirModuleHTML5.'img/motifs/divers/*.{png}', GLOB_BRACE );
 						foreach( $files AS $file ) {
 							echo '<div title="Ajouter un motif sur votre plaque" class="motif" id="' . basename( $file ) . '" style="background-size:100%;background-image: url(' . str_replace($strDirRoot, '/', $file) . ');"></div>';
 						}
 					?>
 				</div>
 				<div id="vehicules_container" class="motifs_container" style="display:none;">
 					<?php
 						$files = glob( $strDirModuleHTML5.'img/motifs/vehicules/*.{png}', GLOB_BRACE );
 						foreach( $files AS $file ) {
 							echo '<div title="Ajouter un motif sur votre plaque" class="motif" id="' . basename( $file ) . '" style="background-size:100%;background-image: url(' . str_replace($strDirRoot, '/', $file) . ');"></div>';
 						}
 					?>
 				</div>
 				<div id="personnages_container" class="motifs_container" style="display:none;">
 					<?php
 						$files = glob( $strDirModuleHTML5.'img/motifs/personnages/*.{png}', GLOB_BRACE );
 						foreach( $files AS $file ) {
 							echo '<div title="Ajouter un motif sur votre plaque" class="motif" id="' . basename( $file ) . '" style="background-size:100%;background-image: url(' . str_replace($strDirRoot, '/', $file) . ');"></div>';
 						}
 					?>
 				</div>
 				<div id="signes_container" class="motifs_container" style="display:none;">
 					<?php
 						$files = glob( $strDirModuleHTML5.'img/motifs/signes/*.{png}', GLOB_BRACE );
 						foreach( $files AS $file ) {
 							echo '<div title="Ajouter un motif sur votre plaque" class="motif" id="' . basename( $file ) . '" style="background-size:100%;background-image: url(' . str_replace($strDirRoot, '/', $file) . ');"></div>';
 						}
 					?>
 				</div>
 				<div id="outils_container" class="motifs_container" style="display:none;">
 					<?php
 						$files = glob( $strDirModuleHTML5.'img/motifs/outils/*.{png}', GLOB_BRACE );
 						foreach( $files AS $file ) {
 							echo '<div title="Ajouter un motif sur votre plaque" class="motif" id="' . basename( $file ) . '" style="background-size:100%;background-image: url(' . str_replace($strDirRoot, '/', $file) . ');"></div>';
 						}
 					?>
 				</div>
                            <div id="svg_container" class="motifs_container" style="display:none;">
 					<?php
 						$files = glob( $strDirModuleHTML5.'img/motifs/svg/*.{svg}', GLOB_BRACE );
 						foreach( $files AS $file ) {
 							echo '<div title="Ajouter un motif sur votre plaque" class="motif" id="' . basename( $file ) . '" style="background-size:100%;background-image: url(' . str_replace($strDirRoot, '/', $file) . ');"></div>';
 						}
 					?>
 				</div>
 			</div>
 				<!-- 
                    <div id="motif_custom">
                        <button style="margin-right: 10px;" id="uploadFile" title="Ajouter une image">Télécharger une image</button>
                                <label style="font-size: 12px;">Quliquer sur shift pour redimensionner le pictogramme</label>
                    </div>
                    <div id="uploadFileContent">
                        <div>
                            <h1>Télécharger une image</h1>
                            <p>Choisissez une image au format <strong>.gif</strong> ou <strong>.png</strong> sur fond transparent et sans bordure de préférence.<br>Si votre image dispose d'un fond (blanc ou de couleur) et d'une bordure, ceux-ci seront imprimés*.</p>
                            <p>
                            <div style="float:left;width:auto;margin-left:40px;">
                                <span style="font-size:12px;font-weight:bold">Image sur fond transparent</span><br>
                                <img style="border: 1px solid #ccc;" src="<?= $strUrlModuleHTML5 ?>img/fond_transparent_small.jpg" alt="">
                            </div>
                            <div style="float:left;width:auto;margin-left:40px;">
                                <span style="font-size:12px;font-weight:bold">Image sur fond plein</span><br>
                                <img style="border: 1px solid #ccc;" src="<?= $strUrlModuleHTML5 ?>img/fond_couleur_small.jpg" alt="">
                            </div>
                            <div style="clear:both"></div>
                            <div id="progressbar" style="display:none" max="100" value="0"></div>
                            <button class="dlbutton download" style="float:none;margin-top:10px">Télécharger</button>
                            </p>
                            <p><em style="font-size:10px">*Sous réserve de faisabilité</em></p>
                            <div class="hiddenfile">
                                <input name="imageupload" type="file" id="fileinput"/>
                            </div>
                        </div>
                    </div>
                     -->
                </div>
                <div id="block_right4">
                    <div id="conftabs" style="overflow: hidden;">
                        <p style="font-weight:bold;color:#c70077 !important;font-size:12px">Couleur de l'encrier</p>
                        <div style="overflow:hidden;padding:0 0 10px 110px">
                            <div style="background:url('<?= $strUrlModuleHTML5 ?>img/tampons/19171C.png');" class="colorset select-black" data-colorset="#19171C"><div class="check"></div></div>
                            <div style="background:url('<?= $strUrlModuleHTML5 ?>img/tampons/554D96.png');" class="colorset select-purple" data-colorset="#554D96"><div class="check"></div></div>
                            <div style="background:url('<?= $strUrlModuleHTML5 ?>img/tampons/5AAB28.png');" class="colorset select-green" data-colorset="#5AAB28"><div class="check"></div></div>
                            <div style="background:url('<?= $strUrlModuleHTML5 ?>img/tampons/B80078.png');" class="colorset select-pink" data-colorset="#B80078"><div class="check"></div></div>
                            <div style="background:url('<?= $strUrlModuleHTML5 ?>img/tampons/E30115.png');" class="colorset select-red" data-colorset="#E30115"><div class="check"></div></div>
                        </div>
                    </div>
                </div>
                <div id="block_right4_2" style="position:relative;background-color:#F4F4F4">
                    <div class="step_container" style="width:190px"><span class="step">3</span><span class="step_title">Dupliquer / Aligner / Inverser</span></div>
                    <div id="align_container">
                        <div id="duplicate" class="alignmove" title="Dupliquer l'élément"></div>
                        <div id="center_h" class="alignmove" title="Centrer horizontalement"></div>
                        <div id="center_v" class="alignmove" title="Centrer verticalement"></div>
                        <div id="center" class="alignmove" title="Centrer horizontalement et verticalement"></div>
                        <div id="move_left" class="alignmove" title="Déplacer à gauche"></div>
                        <div id="move_right" class="alignmove" title="Déplacer à droite"></div>
                        <div id="move_top" class="alignmove" title="Déplacer vers le haut"></div>
                        <div id="move_bottom" class="alignmove" title="Déplacer vers le bas"></div>
                        <div id="flip_vertical" class="alignmove" title="Rotation verticale"></div>
                        <div id="flip_horizontal" class="alignmove" title="Rotation horizontale"></div>
                    </div>
                    <!--<div class="sizes" style="position: absolute;right: 0;top: 13px;">
                                                                <label for="sizes" style="font-size:12px;cursor:pointer">Afficher les dimensions</label><input id="sizes" type="checkbox" checked style="cursor:pointer">
                    </div>-->
                </div>
                <div id="block_right6">
                    <div style="width:150px;position:relative;z-index:10;height:40px;margin:5px auto 0 auto;background:#70c007"><span style="display:block;padding-top:8px;color:#fff;font-size:18px">NOTRE PRIX</span></div>
                    <div id="prix">
                        <div id="prix_container">
                            <span id="prix_ht"><span>0.00</span> € HT</span>
                            <span id="prix_ttc">Soit <span>0.00</span> € TTC</span>
                        </div>
                    </div>
                    <span style="text-transform:uppercase;font-size:14px;color:#70ad24;font-weight:bold">Expédition prévue le <?php
                    echo date('d/m/Y', strtotime("+2 days")); ?></span>
                </div>
		
				<div id="block_right7">
		 			<!-- <a href="#" id="addpanier_button">Ajouter au panier</a> -->
		 			<a href="#" id="pdf_button" style="">pdf</a>
		 			<a href="#" id="addpanier_button">
		 				<img src="template-add_to_cart.png" alt="Ajouter au panier" />
		 			</a>
		 			<div id="div_export"> </div>
		 			
		 			<form id="formu_add_panier_gravograph" method="post" action="cde1.php">
		 				<input type="hidden" name="action" id="action" value="addpanier">
		 				<input type="hidden" name="artid" id="add_artid" value="">
		 				<input type="hidden" name="articlemoduleid" id="add_articlemoduleid" value="9">
		 				<input type="hidden" name="prix_ht" id="add_prix_ht" value="">
		 				<input type="hidden" name="prix_ttc" id="add_prix_ttc" value="">
		 				<input type="hidden" name="longueur" id="add_longueur" value="">
		 				<input type="hidden" name="hauteur" id="add_hauteur" value="">
		 				<input type="hidden" name="surface" id="add_surface" value="">
		 				<input type="hidden" name="id" id="add_id" value="">
		 				<input type="hidden" name="typetest" id="add_typetest" value="">
		 				<input type="hidden" name="productID" id="add_productID" value="">
		 				<input type="hidden" name="url_image" id="add_url_image" value="">
	 					<input type="hidden" name="url_pdf" id="add_url_pdf" value="">
		 				<input type="hidden" name="qte" id="qte" value="1">
		 			</form>
		 			
		 		</div>
            </div>
        </div>
        
        <script type="text/javascript">
        var productID = "<?php echo $productID; $_SESSION['prodID'] = $productID; ?>";
        var strUrlModuleHTML5 = "<?= $strUrlModuleHTML5 ?>";
    	var tvaNormal = "<?php echo $taxeTVANormal ?>";
    	var host  = "<?php echo $host."/".$racinePdf ?>";
        </script>
        
        <!-- <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script> -->
        <script src="<?= $strUrlModuleHTML5 ?>js/jquery.min.js"></script>
		<script src="<?= $strUrlModuleHTML5 ?>js/jquery-ui.min.js"></script>
        <script src="<?= $strUrlModuleHTML5 ?>js/spectrum.js"></script>
        <script src="<?= $strUrlModuleHTML5 ?>js/jquery.ui.rotatable.min.js"></script>
        <script src="<?= $strUrlModuleHTML5 ?>js/jquery.mCustomScrollbar.concat.min.js"></script>
        <script src="<?= $strUrlModuleHTML5 ?>js/jquery.qtip.min.js"></script>
        <script src="<?= $strUrlModuleHTML5 ?>js/rasterizeHTML.allinone.js"></script>
        <!-- <script src="<?= $strUrlModuleHTML5 ?>js/functionstampons.js?<?php echo mt_rand(); ?>"></script> -->
        <script src="<?= $strUrlModuleHTML5 ?>js/ddlist.jquery.min.js"></script>
        <script src="<?= $strUrlModuleHTML5 ?>js/hopscotch.min.js"></script>
 		<script src="<?= $strUrlModuleHTML5 ?>js/ajaxq.js"></script>
        <script type="text/javascript" src="<?= $strUrlModuleHTML5 ?>fancybox/jquery.fancybox.pack.js?v=2.1.5"></script>
        <script type="text/javascript" src="<?= $strUrlModuleHTML5 ?>fancybox/jquery.mousewheel-3.0.6.pack.js"></script>
        <script src="<?= $strUrlModuleHTML5 ?>js/sweet-alert.min.js"></script>
        <script src="<?= $strUrlModuleHTML5 ?>js/jquery-collision.min.js"></script>
		<script src="<?= $strUrlModuleHTML5 ?>js/html2canvas.js"></script>
        <script src="<?= $strUrlModuleHTML5 ?>js/jquery.elevatezoom.js"></script>
		<script src="<?= $strUrlModuleHTML5 ?>js/lobibox.js"></script>
        <script src="<?= $strUrlModuleHTML5 ?>js/tampons.js?<?php echo mt_rand(); ?>"></script>
