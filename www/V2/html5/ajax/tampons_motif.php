<?php
header('Content-Type: text/html; charset=utf-8');
session_start();

if ( 'POST' == $_SERVER['REQUEST_METHOD'] ) {
	if ( isset( $_SERVER['HTTP_ORIGIN'] ) ) {
		$address = 'http://' . $_SERVER['SERVER_NAME'];
		if ( strpos( $address, $_SERVER['HTTP_ORIGIN'] ) !== 0 ) {
			exit( 'CSRF protection in POST request: detected invalid Origin header: ' . $_SERVER['HTTP_ORIGIN'] );
		}
	}
}

error_reporting(E_ALL);
ini_set('display_errors', '1');

$session = md5( session_id()+time() );
require_once( '../includes/connexion.php' );
require_once( '../includes/functions.php' );

if ( isset( $_SESSION['prodID'] ) ) $productID = $_SESSION['prodID']; else die();

if ( isset( $_POST['motif'] ) AND $_POST['motif'] != '' AND isset( $_POST['action'] ) AND $_POST['action'] == 'update'  AND file_exists( '../img/motifs/' . $_POST['folder'] . '/' . $_POST['motif'] ) ) {

	$motifUrl = $_POST['motif']; // motif
	$motifFolder = $_POST['folder']; // répertoire motifs
	$color = $_POST['couleur']; //couleur du texte
	$url = '';
	$id = md5( uniqid( rand(), true ) );
if(strstr($_POST['motif'], ".svg")){
    
        if($color!="#000000" && $color!="#000"){
            $image=countfill('../img/motifs/svg/' . $motifUrl,$color);
            $imageData = base64_encode($image);
        $src = 'data:image/svg+xml;base64,'.$imageData;
        $svg="1";
//         $data = array(
//		'imgMotif'		=> $src,
//                'contentsvg'            => $image,
//                'svg'			=> $svg,
//	);
        $data = array(
		'id'			=> $id,
		'motif'			=> 'html5/img/motifs/svg/' . $motifUrl,
		'imgMotif'		=> $src,
		'code'			=> $motifUrl,
		'color'			=> $color,
		'expDate'		=> date( 'Yd/m/Y', strtotime( "+2 days" ) ),
            
                           'contentsvg'            => file_get_contents('../img/motifs/svg/' . $motifUrl),
                'svg'			=> $svg,
	);
        }else{
          $imageData = base64_encode(file_get_contents('../img/motifs/svg/' . $motifUrl));
        $src = 'data:image/svg+xml;base64,'.$imageData;  
        $svg="1";
//         $data = array(
//		'imgMotif'		=> $src,
//                'contentsvg'            => file_get_contents($image),
//                'svg'			=> $svg,
//	);
        $data = array(
		'id'			=> $id,
		'motif'			=> 'html5/img/motifs/svg/' . $motifUrl,
		'imgMotif'		=> $src,
		'code'			=> $motifUrl,
		'color'			=> $color,
		'expDate'		=> date( 'Yd/m/Y', strtotime( "+2 days" ) ),
                           'contentsvg'            => file_get_contents('../img/motifs/svg/' . $motifUrl),
                'svg'			=> $svg,
	);
        }
        
}else{
	$im = new Imagick('../img/motifs/' . $motifFolder . '/' . $motifUrl);
	//$im->evaluateImage(Imagick::EVALUATE_MULTIPLY, 0.85, Imagick::CHANNEL_ALPHA);
	$im->setImageAlphaChannel( Imagick::ALPHACHANNEL_EXTRACT );
	$im->setImageBackgroundColor( $color);
	$im->setImageAlphaChannel( Imagick::ALPHACHANNEL_SHAPE );
	$im->trimImage( 0 );

	$data = array(
		'id'			=> $id,
		'motif'			=> 'html5/img/motifs/' . $motifFolder . '/' . $motifUrl,
		'imgMotif'		=> base64_encode( $im ),
		'code'			=> $motifUrl,
		'color'			=> $color,
		'expDate'		=> date( 'Yd/m/Y', strtotime( "+2 days" ) )
	);

	
}
        $_SESSION['rapidpub'][$productID]['motif'][$id] = $data;
	echo json_encode( $data );

}
function countfill($image,$color){
   $nbfill= substr_count(file_get_contents($image),"fill");
   $svgOutput=RecolorImage($image, $color);
   if($nbfill>1){

       $svgOutput=RecolorImage($image, $color);
   }
  
   return $svgOutput;
}
function RecolorImage($ImageSvgFile, $ImageColor) {
    $FileContents = file_get_contents($ImageSvgFile);
    $doc = new DOMDocument();
    $dom->preserveWhiteSpace = False;
    $doc->loadXML($FileContents) or die('Failed to load SVG file ' . $ImageSvgFile . ' as XML.  It probably contains malformed data.');
    $SvgTags = $doc->getElementsByTagName("svg");
//    if (preg_match('/^([0-9a-f]{1,2}){3}$/i', $ImageColor) == false)
//        {
//        die('Invalid color: ' . $ImageColor);
//        }
    //Look at each element in the XML and add or replace it's Fill attribute to change the color.
    $arrayelement = array("rect", "circle", "path");
    $arraycolor=array();
    foreach ($arrayelement as $value) {
        $AllTags = $doc->getElementsByTagName($value);
         foreach ($AllTags as $ATag) {
            $VectorColor = $ATag->getAttribute('fill');
            $StrokeColor = $ATag->getAttribute('stroke');
            if (strtoupper($VectorColor) != '#FFFFFF' && strtoupper($VectorColor) != 'none' &&
                    strtoupper($StrokeColor) != '#FFFFFF' && strtoupper($StrokeColor) != 'none') {
                //This vector is not white, so change it's color.
                 array_push($arraycolor,strtoupper($VectorColor));
                 array_push($arraycolor,strtoupper($StrokeColor));
            }
        }
        $arrayunique=array_unique($arraycolor);
        //print_r($arrayunique);
        if(count($arrayunique)==1){
        /**********************/
        foreach ($AllTags as $ATag) {
            $VectorColor = $ATag->getAttribute('fill');
            $StrokeColor = $ATag->getAttribute('stroke');
            if (strtoupper($VectorColor) != '#FFFFFF' && strtoupper($VectorColor) != 'none' &&
                    strtoupper($StrokeColor) != '#FFFFFF' && strtoupper($StrokeColor) != 'none') {
                //This vector is not white, so change it's color.
                $ATag->setAttribute('fill', '' . $ImageColor);
                $ATag->setAttribute('stroke', '' . $ImageColor);
                $FileContents = $doc->saveXML($doc);
            }
        }
    }
    }

    Return $FileContents;
}
?>
