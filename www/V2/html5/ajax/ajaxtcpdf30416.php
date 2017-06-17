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
$dimensionplaques = json_decode($_POST["infoplaques"]);
//$correctionxplaque =json_decode($_POST["correctionxplaque"]);
//$correctionyplaque =json_decode($_POST["correctionyplaque"]);
// define style for border
$border_style = array('all' => array('width' => 0.2, 'cap' => 'square', 'join' => 'miter', 'dash' => 0, 'phase' => 0));
$pdf->SetFillColor(255, 255, 255);
$pdf->SetDrawColor(0, 0, 0);
$initialeleft = 15 + tomm($dimensionplaques->toplaque);
$initialetop = 15 + tomm($dimensionplaques->leftplaque);
//    $pdf->SetX($left, false);
//    $pdf->SetY($top, false, false);
$pdf->Rect($initialeleft, $initialetop, tomm($dimensionplaques->widthplaque), tomm($dimensionplaques->heightplaque), 'DF', $border_style);
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
       
    } else if ($anglecurrent > 0 && $anglecurrent <= 90)  {
        $correctionx = sin(deg2rad(todegree(trim($transform)))) * $pixelvalueheight[0];
        $left = $left + abs(tomm($correctionx));
        
    }
    else if ($anglecurrent > 90 && $anglecurrent <= 180)  {
        //$correctionx = sin(deg2rad(todegree(trim($transform)))) * $pixelvalueheight[0];
        $left = $left +tomm($pixelvalueweight[0]);
       
    }
     else if ($anglecurrent > 180 && $anglecurrent <= 270)  {
        $correctionx = sin(deg2rad(todegree(trim($transform)))) * $pixelvalueheight[0];
        $top = $top +tomm($pixelvalueweight[0]);
        $left = $left +tomm($pixelvalueweight[0]);
        
    }


    $pdf->SetX($left, false);
    $pdf->SetY($top, false, false);
    if ($transform != "") {

        $pdf->StartTransform();
        $pdf->Rotate(-todegree(trim($transform)));
    }
    $domain = strstr($src, 'montserrat.arobases.fr');
    $path_parts = pathinfo("http://montserrat.arobases.fr/V2/" . $src);
    if ($path_parts["extension"] == "svg") {
        $pdf->SetX($left, false);
        $pdf->SetY($top, false, false);
        $filenamesvg = "svg/" . time() . ".svg";
        $myfile = fopen($filenamesvg, "w") or die("Unable to open file!");
        chmod($filenamesvg, 0777);
        fclose($myfile);
        $imagesvg = countfill("http://montserrat.arobases.fr/V2/" . $src, $datacolorcode);
        file_put_contents($filenamesvg, $imagesvg);
        $pdf->ImageSVG($filenamesvg, $x = $left, $y = $top, $w = $width, $h = $height, $link = '', $align = '', $palign = '', $border = 0, $fitonpage = false);
    } else if ($path_parts["extension"] == "jpg" || $path_parts["extension"] == "jpeg") {

        if (!$domain)
            $pdf->Image("http://montserrat.arobases.fr/V2/" . $src, $left, $top, $w = $width, $h = $height, 'JPEG', '', '', false, 150);
        else {
            $pdf->Image($src, $left, $top, $w = $width, $h = $height, 'JPEG', '', '', false, 150);
        }
    } else if ($path_parts["extension"] == "png") {

        if (!$domain)
            $pdf->Image("http://montserrat.arobases.fr/V2/" . $src, $left, $top, $w = $width, $h = $height, 'PNG', '', '', false, 150);
        else {
            $pdf->Image($src, $left, $top, $w = $width, $h = $height, 'PNG', '', '', false, 150);
        }
    } else {
        if ($element->font != "0") {
            //echo $width." | ".$height."\n\r\t";
            if ($transform != "") {
                //$top=$top+ tomm($dimensionplaques->toplaque);
            }
            echo  $width."|".$height . "\n\r\t";
            $fontname = TCPDF_FONTS::addTTFfont('../fonts/' . $element->font, 'TrueTypeUnicode', '', 32);
            $rgb = hex2rgb($datacolorcode);
            $pdf->SetTextColor($rgb[0], $rgb[1], $rgb[2]);
            $pdf->SetFont($fontname, '', ($fontsize*2/3), '', 'default', true);
            //$pdf->MultiCell($width, $height, $src, $border = 0, $align = 'L', $fill = false, $ln = 1, $x = $left, $y = $top, $reseth = false, $stretch = 0, $ishtml = false, $autopadding = false, $maxh = 0, $valign = '', $fitcell = false
           // );
            $pdf->Text	($left,
                            $top,
                            $src,
                            $fstroke = false,
                            $fclip = false,
                            $ffill = true,
                            $border = 0,
                            $ln = 0,
                            $align = '',
                            $fill = false,
                            $link = '',
                            $stretch = 0,
                            $ignore_min_height = false,
                            $calign = 'T',
                            $valign = 'M',
                            $rtloff = false 
                    );
        }
    }

    if ($transform != "") {
        $pdf->StopTransform();
    }
}


// ---------------------------------------------------------
//Close and output PDF document
$pdf->Output(dirname(__DIR__) . '/ajax/' . time() . '.pdf', 'F');

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
    if ($nbfill > 1) {
//        $svgOutput = preg_replace(
//         '/#([0-9a-f]{6})/i',
//        '#'.$color,
//        file_get_contents($image)
//    );
        $svgOutput = RecolorImage($image, $color);
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
                $arraycolor=  array_push($arraycolor,strtoupper($VectorColor));
                $arraycolor=  array_push($arraycolor,strtoupper($StrokeColor));
            }
        }
        $arrayunique=array_unique($arraycolor);
        print_r($arrayunique);
        if($arrayunique==1){
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
