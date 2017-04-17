<?php
require_once('../../../gsconfig.php');
$admin = defined('GSADMIN') ? GSADMIN : 'admin';

require_once("../../../${admin}/inc/common.php");
$loggedin = cookie_check();

if (!$loggedin) 
	die('Not logged in!');
	
if(!defined('IN_GS')){ 
	die('you cannot load this page directly.'); 
}

require_once('../EGImage.php');
require_once('../SPEValidator.php');

$sizes = array(
    'image-field-thumb' => array( 'width' => 300, 'height' => 200, 'cacheDir' => 'image-field-thumbs/' ),
    'multi-image-thumb' => array( 'width' => 148, 'height' => 111, 'cacheDir' => 'multi-image-thumbs/' ),
    'extra-browser-thumb' => array( 'width' => 180, 'height' => 120, 'cacheDir' => 'extra-browser-thumbs/' )
);

$mode = @$_GET['mode'];

$requestedPath =  @$_GET['img']; //requested path

try {
    if ( !in_array($mode, array('image-field-thumb', 'multi-image-thumb', 'extra-browser-thumb')) )
        throw new Exception('Unknown mode');

    if (empty($requestedPath)){
        throw new Exception('Image not found'); //empty image
    }
    
    $cacheDir = GSDATAOTHERPATH . 'SpecialPagesExtras/thumbs_cache/';
    $cacheDir .= $sizes[$mode]['cacheDir'];
    
    if ($mode == 'extra-browser-thumb'){
        $requestedPath = '/'.str_replace(GSROOTPATH, '', GSDATAUPLOADPATH). $requestedPath;  
    }
    
    $paths = SPEValidator::findImagePath($requestedPath);
    
    if ($paths === false)
        throw new Exception('Wrong path'); 
    	
    if (!filepath_is_safe($paths['full'], GSDATAPATH))
        throw new Exception('Not valid path'); //may be not safe
    
    //file exists and its modification date is older than source
    $cachedExists = file_exists($cacheDir.$paths['dir'].$paths['filename']) && @filemtime($paths['full']) <= filemtime($cacheDir.$paths['dir'].$paths['filename']);
    
    $t = new EGImage($cachedExists ? $cacheDir.$paths['dir'].$paths['filename'] : $paths['full']);


    if (!$cachedExists){
        $t->resize($sizes[$mode]['width'], $sizes[$mode]['height'], 'fit', true);
        
        if ( !file_exists(dirname($cacheDir.$paths['dir'].$paths['filename'])) ){ //directory not exists, prepare one, requsivly
            mkdir(dirname($cacheDir.$paths['dir'].$paths['filename']), 0755, true);
        }
        
        $t->save($cacheDir.$paths['dir'].$paths['filename'], 92);
    }
    
    $t->render(172800); //two days
} catch (Exception $e) {
	if ($mode == 'extra-browser-thumb'){
		EGImage::renderError($e->getMessage(), @$sizes[$mode]['width'], @$sizes[$mode]['height']);
		die;
	}
	header("HTTP/1.0 404 Not Found"); //retiunr 404, jquery will fail loading image
	echo $e->getMessage();

}
