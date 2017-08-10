<?php
require("includes/admin_global.php");


if( HTools::getValue("controller") ){      
    $tzTempFiles = scandir( dirname(__FILE__). "/controllers");
    $tzControllerFiles = array();
    foreach( $tzTempFiles as $zFile ){
        if( $zFile != "." && $zFile != ".."  && HTools::get_file_extension($zFile) == "php"){
            $temp = explode(".",$zFile);
            $tzControllerFiles[] = $temp[0] ;
        }
    }

    $iCountFile = count($tzControllerFiles) ;
    if( $iCountFile > 0 ){
        $bFoundClass = false ;
        $iCountTempFile = 0; 
        while( $iCountFile > 0 && !$bFoundClass ) {
            $zControllerClass = $tzControllerFiles[$iCountTempFile] ;
            
            $iCountTempFile ++  ; 
            
            spl_autoload_register(function ($zControllerClass) {
                include dirname(__FILE__) . '/controllers/' . $zControllerClass . '.php';
            });
            $oController = new $zControllerClass();
            
            if( is_object($oController) && isset($oController->_name) && $oController->_name == HTools::getValue("controller") ){
                if( !HTools::getValue("action") ){
                    $oController->index();
                } else {
                    $tzClassMethods = array();
                    $tzClassMethods = get_class_methods($oController);
                    $iCount = count($tzClassMethods);
                    
                    $iCountTemp = 0 ;
                    if( $iCount > 0 ){
                        $iCountTemp = 0 ; 
                        $zActionName = "" ;
                        
                        while($iCount > 0 && $zActionName == "" ){
                            if( HTools::getValue("action") == $tzClassMethods[$iCountTemp] ){
                                $zActionName = $tzClassMethods[$iCountTemp];
                            }
                            $iCountTemp ++ ;
                            $iCount --;
                        }
                        if( $zActionName != "" ){
                            $oController->{$zActionName}();
                        }
                    } 
                }
                $bFoundClass = true ;
            }
        }
    }
}