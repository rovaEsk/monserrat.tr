<?php

//============================================================+
// File name   : example_058.php
// Begin       : 2010-04-22
// Last Update : 2013-05-14
//
// Description : Example 058 for TCPDF class
//               SVG Image
//
// Author: Nicola Asuni
//
// (c) Copyright:
//               Nicola Asuni
//               Tecnick.com LTD
//               www.tecnick.com
//               info@tecnick.com
//============================================================+

/**
 * Creates an example PDF TEST document using TCPDF
 * @package com.tecnick.tcpdf
 * @abstract TCPDF - Example: SVG Image
 * @author Nicola Asuni
 * @since 2010-05-02
 */
// Include the main TCPDF library (search for installation path).
require_once('tcpdf_include.php');

$arobasesUrl = "http://montserrat.arobases.fr/V2/";
// create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, "mm", PDF_PAGE_FORMAT, true, 'UTF-8', false);

//// set document information
//$pdf->SetCreator(PDF_CREATOR);
//$pdf->SetAuthor('Nicola Asuni');
//$pdf->SetTitle('TCPDF Example 058');
//$pdf->SetSubject('TCPDF Tutorial');
//$pdf->SetKeywords('TCPDF, PDF, example, test, guide');
//
//// set default header data
//$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 058', PDF_HEADER_STRING);
// set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);


// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set image scale factor
//$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
// set some language-dependent strings (optional)
if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
    require_once(dirname(__FILE__) . '/lang/eng.php');
    $pdf->setLanguageArray($l);
}

// ---------------------------------------------------------
// set font
$pdf->SetFont('helvetica', '', 10);

// add a page
$pdf->AddPage();
// NOTE: Uncomment the following line to rasterize SVG image using the ImageMagick library.
//$pdf->setRasterizeVectorImages(true);

$elements = json_decode($_POST["info"]);
$texteonly1 = json_decode($_POST["texteonly1"]);
$dimensionplaques = json_decode($_POST["infoplaques"]);
//$correctionxplaque =json_decode($_POST["correctionxplaque"]);
//$correctionyplaque =json_decode($_POST["correctionyplaque"]);
// define style for border
$border_style = array('all' => array('width' => 0.2, 'cap' => 'square', 'join' => 'miter', 'dash' => 0, 'phase' => 0));
$pdf->SetFillColor(255, 255, 255);
$pdf->SetDrawColor(0, 0, 0);

//$initialeleft = 15 + tomm($dimensionplaques->toplaque);
//$initialetop = 15 + tomm($dimensionplaques->leftplaque);

$ratioDimension = 1;
$dimensionWidth = $ratioDimension * tomm($dimensionplaques->widthplaque);
$dimensionHeight = $ratioDimension * tomm($dimensionplaques->heightplaque);
$initialeleft =  15 + tomm($dimensionplaques->toplaque);
$initialetop = 15 + tomm($dimensionplaques->leftplaque);

$newFileNameTime = time();
//    $pdf->SetX($left, false);
//    $pdf->SetY($top, false, false);
if ($texteonly1 != "1") {
    $pdf->Rect($initialeleft, $initialetop, $dimensionWidth, $dimensionHeight, 'DF', $border_style);
    //$pdf->Rect(145, 10, 40, 20, 'D', array('all' => $style3));
    foreach ($elements as $element) {
        $src = $element->src;
        $style = $element->style;
        $srctxt = $element->srctxt;
        $widthorigine = $element->width;
        $heightorigine = $element->height;
        $datacolorcode = $element->datacolorcode;
        $fontsize = $element->fontsize;
        $correctionxplaque = $element->correctionxplaque;
        $correctionyplaque = $element->correctionyplaque;
        $anglecurrent = $element->anglecurrent;
		
        $arraystyle = explode(";", $style);

        $transform = 0;
        foreach ($arraystyle as $elemstyle) {
            $propstyle = explode(":", $elemstyle);

            switch (trim($propstyle[0])) {
                case "left":
                    // echo "Left" . ":" . $correctionxplaque . "\n\r\t";
                    $left = $initialeleft + tomm($correctionxplaque);
                    break;
                case "top":
                    // echo "Top" . ":" . $correctionyplaque . "\n\r\t";
                    $top = $initialetop + tomm($correctionyplaque);
                    break;
                case "width":
                    $width = tomm($propstyle[1]);
                    $widthpx = $propstyle[1];
                    break;
                case "right":
                    $right = $propstyle[1];
                    break;
                case "height":
                    $height = tomm($propstyle[1]);
                    $heightpx = $propstyle[1];
                    break;
                case "bottom":
                    $bottom = $propstyle[1];
                    break;
                case "transform":
                    $transform = $propstyle[1];
                    break;
            }
        }
        if ($width <= 0) {
            $width = tomm($widthorigine);
            $widthpx = $widthorigine . "px";
        }

        if ($height <= 0) {
            $height = tomm($heightorigine);
            $heightpx = $heightorigine . "px";
        }


        if (!strstr($transform, 'matrix')) {
            $r = array("rotate", "(", ")");
            $transform = str_replace($r, "", $transform);
        } else {
            $r = array("matrix", "(", ")");
            $transform = str_replace($r, "", $transform);
            $transform = explode(",", $transform);
            $a = $transform[0];
            $b = $transform[1];
            $c = $transform[2];
            $d = $transform[3];

            $scale = sqrt($a * $a + $b * $b);

            $sin = $b / $scale;
            $transform = deg2rad(round(atan2($b, $a) * (180 / pi())));
        }

        $pixelvalueheight = explode("px", $heightpx);
        $pixelvalueweight = explode("px", $widthpx);


        if ($anglecurrent > 270 && $anglecurrent <= 360) {
            $carrecorrectiony = sin(-deg2rad(todegree(trim($transform)))) * $pixelvalueweight[0];
            $correctuiony = carre($pixelvalueheight[0]) - carre($carrecorrectiony);
            $correctuiony = sqrt($carrecorrectiony);
            $top = $top + tomm(abs($carrecorrectiony));
        } else if ($anglecurrent > 0 && $anglecurrent <= 90) {
            $correctionx = sin(deg2rad(todegree(trim($transform)))) * $pixelvalueheight[0];
            $left = $left + abs(tomm($correctionx));
        } else if ($anglecurrent > 90 && $anglecurrent <= 180) {
            //$correctionx = sin(deg2rad(todegree(trim($transform)))) * $pixelvalueheight[0];
            $left = $left + tomm($pixelvalueweight[0]);
        } else if ($anglecurrent > 180 && $anglecurrent <= 270) {
            $correctionx = sin(deg2rad(todegree(trim($transform)))) * $pixelvalueheight[0];
            $top = $top + tomm($pixelvalueweight[0]);
            $left = $left + tomm($pixelvalueweight[0]);
        }


        $pdf->SetX($left, false);
        $pdf->SetY($top, false, false);
        if ($transform != "") {

            $pdf->StartTransform();
            $pdf->Rotate(-todegree(trim($transform)));
        }
        $domain = strstr($src, 'montserrat.arobases.fr');
        $path_parts = pathinfo($arobasesUrl . $src);
        
        if ($path_parts["extension"] == "svg") {
            $pdf->SetX($left, false);
            $pdf->SetY($top, false, false);
            $filenamesvg = "svg/" . time() . ".svg";
            $myfile = fopen($filenamesvg, "w") or die("Unable to open file!");
            chmod($filenamesvg, 0777);
            fclose($myfile);
            $imagesvg = countfill($arobasesUrl . $src, $datacolorcode);
            file_put_contents($filenamesvg, $imagesvg);
            $pdf->ImageSVG($filenamesvg, $x = $left, $y = $top, $w = $width, $h = $height, $link = '', $align = '', $palign = '', $border = 0, $fitonpage = false);
        } else if ($path_parts["extension"] == "jpg" || $path_parts["extension"] == "jpeg") {

            if (!$domain)
                $pdf->Image($arobasesUrl . $src, $left, $top, $w = $width, $h = $height, 'JPEG', '', '', false, 150);
            else {
                $pdf->Image($src, $left, $top, $w = $width, $h = $height, 'JPEG', '', '', false, 150);
            }
        } else if ($path_parts["extension"] == "png") {

            if (!$domain)
                $pdf->Image($arobasesUrl . $src, $left, $top, $w = $width, $h = $height, 'PNG', '', '', false, 150);
            else {
                $pdf->Image($src, $left, $top, $w = $width, $h = $height, 'PNG', '', '', false, 150);
            }
        } else {
            if ($element->font != "0") {

                if ($transform != "") {
                    
                }

                $fontname = TCPDF_FONTS::addTTFfont('../fonts/' . $element->font, '', '', 32);
                $rgb = hex2rgb($datacolorcode);
                $pdf->SetTextColor($rgb[0], $rgb[1], $rgb[2]);
                $pdf->SetFont($fontname, '', ($fontsize * 0.75), '', 'default', true);

                $strsvg = "<?xml version=\"1.0\" encoding=\"utf-8\"?>
				<!-- Generator: Adobe Illustrator 16.0.0, SVG Export Plug-In . SVG Version: 6.00 Build 0)  -->
				<!DOCTYPE svg PUBLIC \"-//W3C//DTD SVG 1.1//EN\" \"http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd\">
				
				<svg height=\"" . $heightorigine . "px\"
				     width=\"" . $widthorigine . "px\"
				     version=\"1.1\"
				     id=\"Calque_1\" 
				     xmlns=\"http://www.w3.org/2000/svg\"
				     xmlns:xlink=\"http://www.w3.org/1999/xlink\" 
				     x=\"0px\" 
				     y=\"0px\"
				     viewBox=\"\" enable-background=\"\"
				     xml:space=\"preserve\">
				<defs>
				  <style type=\"text/css\">
				    @font-face {
				      font-family: 'fontcustom' ;
				      src: url('" . $arobasesUrl . "/html5/fonts/" . $element->font . "');
				          outline: none;
				    }
				    #texte {
				     margin: none;
				    border: dotted 1px red;
				    padding: 0;
				    height: ".$heightorigine."px;
				    font-size:".$fontsize."px;
				    line-height:".$heightorigine."px;
				    display:inline-block;
				}
				  </style>
				  
				</defs>
				  <text id=\"texte\"
				 x=\"0px\" y=\"" . $heightorigine . "px\" font-size= \"" . $fontsize . "px\" fill=\"$datacolorcode\" font-family=\"fontcustom\" transform=\"\">" . $src . "</text>
				</svg>";
                
                $filenamesvg = "svg/txt/" . $newFileNameTime . ".svg";
                $myfile = fopen($filenamesvg, "w") or die("Unable to open file!");
                chmod($filenamesvg, 0777);
                fclose($myfile);
                //$imagesvg = countfill($arobasesUrl . $src, $datacolorcode);
                file_put_contents($filenamesvg, $strsvg);
                $pdf->ImageSVG($filenamesvg, $x = $left, $y = $top, $w = $width, $h = $height, $link = '', $align = '', $palign = '', $border = 0, $fitonpage = false);
            }
        }
        if ($transform != "") {
            $pdf->StopTransform();
        }
    }
} else {
	$sourcesvg = $_POST["srcsvg"];
	$widthsvg =tomm($_POST["widthsvg"]);
	$heightsvg =tomm($_POST["heightsvg"]);


	$strsvg = "<?xml version=\"1.0\" encoding=\"utf-8\"?>
	<!-- Generator: Adobe Illustrator 16.0.0, SVG Export Plug-In . SVG Version: 6.00 Build 0)  -->
	<!DOCTYPE svg PUBLIC \"-//W3C//DTD SVG 1.1//EN\" \"http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd\">
	<svg height=\"" . $heightsvg . "px\"
	     width=\"" . $widthsvg . "px\"
	     version=\"1.1\"
	     id=\"Calque_1\" 
	     xmlns=\"http://www.w3.org/2000/svg\"
	     xmlns:xlink=\"http://www.w3.org/1999/xlink\" 
	     x=\"0px\" 
	     y=\"0px\"
	     viewBox=\"\" enable-background=\"\"
	     xml:space=\"preserve\">
	<image width=\"".$widthsvg."\" height=\"".$heightsvg."\" xlink:href=\"".utf8_decode(urldecode ($sourcesvg))."\"/>
	</svg>";

   //echo urldecode ($strsvg);
    $filenamesvg = "svg/texttype/" . $newFileNameTime . ".svg";
    $myfile = fopen($filenamesvg, "w") or die("Unable to open file!");
    chmod($filenamesvg, 0777);
    fclose($myfile);
    //$imagesvg = countfill($arobasesUrl . $src, $datacolorcode);
    file_put_contents($filenamesvg, $strsvg);
    $pdf->ImageSVG($filenamesvg, $x = $initialeleft, $y = $initialetop, $w = $width, $h = $height, $link = '', $align = '', $palign = '', $border = 0, $fitonpage = false);
    
    
    /****************************************************************************************/
    
    if ($_POST["font"] != "0") {
    	$fontsize=20;
    	$fontname = TCPDF_FONTS::addTTFfont('../fonts/' . $_POST["font"], '', '', 32);
    	$rgb = hex2rgb($datacolorcode);
    	$pdf->SetTextColor($rgb[0], $rgb[1], $rgb[2]);
    	$pdf->SetFont($fontname, '', ($fontsize * 0.75), '', 'default', true);

    	$strsvg1 = "<?xml version=\"1.0\" encoding=\"utf-8\"?>
		<!-- Generator: Adobe Illustrator 16.0.0, SVG Export Plug-In . SVG Version: 6.00 Build 0)  -->
		<!DOCTYPE svg PUBLIC \"-//W3C//DTD SVG 1.1//EN\" \"http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd\">
		
		<svg height=\"" . $heightsvg . "px\"
		     width=\"" . $widthsvg . "px\"
		     version=\"1.1\"
		     id=\"Calque_1\" 
		     xmlns=\"http://www.w3.org/2000/svg\"
		     xmlns:xlink=\"http://www.w3.org/1999/xlink\" 
		     x=\"0px\" 
		     y=\"0px\"
		     viewBox=\"\" enable-background=\"\"
		     xml:space=\"preserve\">
		<defs>
		  <style type=\"text/css\">
		    @font-face {
		      font-family: 'fontcustom' ;
		      src: url('" . $arobasesUrl . "/fonts/" .  $_POST["font"] . "');
		    }
		  </style>
		</defs>
		  <text x=\"0px\" y=\"".$heightsvg."px\" font-size= \"" . $fontsize . "px\" fill=\"#000000\" font-family=\"fontcustom\" transform=\"\">" . $_POST["text"] . "</text>
		</svg>";
    	$filenamesvg = "svg/txt/" . $newFileNameTime . ".svg";
    	$myfile = fopen($filenamesvg, "w") or die("Unable to open file!");
    	chmod($filenamesvg, 0777);
    	fclose($myfile);
    	//$imagesvg = countfill($arobasesUrl . $src, $datacolorcode);
    	file_put_contents($filenamesvg, $strsvg1);
    	$pdf->ImageSVG($filenamesvg, $x = $initialeleft, $y = $initialetop*4, $w = $widthsvg, $h = $heightsvg, $link = '', $align = '', $palign = '', $border = 0, $fitonpage = false);
    }
        
}
// ---------------------------------------------------------
//Close and output PDF document
//$pdf->Output(dirname(__DIR__) . '/ajax/' . time() . '.pdf', 'I');
$time = time();
$pdf->Output(dirname(__DIR__) . '/ajax/' . $time . '.pdf', 'F');

/** set json data callback for pdf **/
$pdfStatFile = array();
if(file_exists(dirname(__DIR__) . '/ajax/' . $time . '.pdf')){
    $pdfStatFile = array(
                        'file_stat' => 1,
                        'pdf_path' =>  $time . '.pdf'
                    );    
} else {
    $pdfStatFile = array(
                        'file_stat' => 0
                    );    
}
echo json_encode($pdfStatFile);
/** set json data callback for pdf **/

function todegree($rad) {
    $radvalue = explode("rad", $rad);
    $degree = $radvalue[0] * 180 / pi();
    return $degree;
}

function tomm($pixel) {
    if ($pixel != "auto") {
        $pixelvalue = explode("px", $pixel);

        // $mm = ($pixelvalue[0] * 25.4) / 150;
        $mm = ($pixelvalue[0] / 3.77);
    } else {
        $mm = "";
    }
    return $mm;
}

if (!function_exists('getimagesizefromstring')) {

    function getimagesizefromstring($data, &$imageinfo = array()) {
        $uri = 'data://application/octet-stream;base64,' . base64_encode($data);
        return getimagesize($uri, $imageinfo);
    }

}

function hex2rgb($hex) {
    $hex = str_replace("#", "", $hex);

    if (strlen($hex) == 3) {
        $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
        $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
        $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
    } else {
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
    }
    $rgb = array($r, $g, $b);
    //return implode(",", $rgb); // returns the rgb values separated by commas
    return $rgb; // returns an array with the rgb values
}

function carre($nb) {
    return $nb * $nb;
}

function countfill($image, $color) {
    $nbfill = substr_count(file_get_contents($image), "fill");
    $svgOutput = RecolorImage($image, $color);


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
    $arraycolor = array();
    foreach ($arrayelement as $value) {
        $AllTags = $doc->getElementsByTagName($value);
        foreach ($AllTags as $ATag) {
            $VectorColor = $ATag->getAttribute('fill');
            $StrokeColor = $ATag->getAttribute('stroke');
            if (strtoupper($VectorColor) != '#FFFFFF' && strtoupper($VectorColor) != 'none' &&
                    strtoupper($StrokeColor) != '#FFFFFF' && strtoupper($StrokeColor) != 'none') {
                //This vector is not white, so change it's color.

                array_push($arraycolor, strtoupper($VectorColor));
                array_push($arraycolor, strtoupper($StrokeColor));
            }
        }
        $arrayunique = array_unique($arraycolor);
        //print_r($arrayunique);
        if (count($arrayunique) == 1) {
            /*             * ******************* */
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
