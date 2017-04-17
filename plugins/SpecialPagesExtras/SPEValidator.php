<?php
class SPEValidator{

    public static function editValidateData($settings){

		$valResult = array('validation-failed' => false, 'fields' => array());
        
        if (empty($settings)){
            return $valResult;
		}
			
		//title is alwyas required by GS
		if ( self::_isEmpty($_POST['post-title']) ){
			$valResult['fields']['post-title'] = false; 
			$valResult['validation-failed'] = true;
		}
		else
			$valResult['fields']['post-title'] = true;

		//title regex validation if exists
		if ( @$settings['pageData']['title-regex'] ){      
			if ( !preg_match ( $settings['pageData']['title-regex'], $_POST['post-title'])){ //apply regex check
				$valResult['fields']['post-title'] = false; 
				$valResult['validation-failed'] = true;
			}		
			else
				$valResult['fields']['post-title'] = true;
        }
		
		
        if ( $settings['pageData']['content-required'] ){            
            if ( self::_isEmpty(@$_POST['post-content']) ){
                $valResult['fields']['post-content'] = false; 
				$valResult['validation-failed'] = true;
			}
			else
				$valResult['fields']['post-content'] = true;
        }      

        
        if (@empty($settings['fieldsData']))
            return $valResult;

			
		foreach ($settings['fieldsData'] as $fData) {
			$name = 'post-sp-'.$fData['name'];
			$value = @$_POST['post-sp-'.$fData['name']];
			
			
			$valResult['fields'][$name] = true; //as default we think that validation is passed so make it true
			
			if ($fData['required']){ //not required

				if ($fData['new-type'] == 'multitext'){ //dropdown with multi				
				
					$valResult['fields'][$name] = array();  //in array will be stored indexes of invalid fields

					$arr = explode('||', $value); //find records and iterate over it
					for ($b = 0; $b < count($arr); $b++) {
						if ( self::_isEmpty($arr[$b]) ){ //if its empty add to array its index
							$valResult['fields'][$name][] = $b; 
                            $valResult['validation-failed'] = true;
                        }
					}
					if (empty($valResult['fields'][$name]))
						$valResult['fields'][$name] = true;
						
                    //do not stop here, other subfields  indexes may fail regex or image  validation
				}	
				else{
                    if ( self::_isEmpty($value) ){ //no matter what if empty - fail
                        $valResult['fields'][$name] = false;
                        $valResult['validation-failed'] = true;
						continue; //break loop do not check regex etc.
                    }
				}
			}     
            
            if ( $fData['new-type'] == 'multiimage' ){
                //if we are here, field is not empty
                $valResult['fields'][$name] = array();
                
                if (!self::_isEmpty($value)){ //if has any value
                    $arr = explode('||', $value); //find records and iterate over it
                    for ($b = 0; $b < count($arr); $b++) {
                        if ( !self::_validateImage($arr[$b], $fData)){ //apply  check that all are images
                            $valResult['fields'][$name][] = $b;
                            $valResult['validation-failed'] = true;
                        }
                    }
                }
                if ( empty($valResult['fields'][$name]) )
                    $valResult['fields'][$name] = true;

                continue;
            }
            else if ( $fData['new-type'] == 'imagewiththumb' && !self::_isEmpty($value) ){ //imagewiththumb and not empty,validate as image
                if ( !self::_validateImage($value, $fData) ){ //check whole aspect ratios and size of image too
                    $valResult['fields'][$name] = false;
                    $valResult['validation-failed'] = true;
                }
                continue;
            }
           
			//regex check
			if (@$fData['regex']){ 
				if ($fData['new-type'] == 'multitext'){ 
					if ( !is_array($valResult['fields'][$name]) ){ //there might be array created on required check
						$valResult['fields'][$name] = array();
					}
					
					$arr = explode('||', $value); //find records and iterate over it
					for ($b = 0; $b < count($arr); $b++) {
						if ($fData['required'] || ( !$fData['required'] && !self::_isEmpty($arr[$b]) ) ) { //is required is not empty
							if ( !preg_match ( $fData['regex'], $arr[$b])){ //apply regex check
								$valResult['fields'][$name][] = $b;
								$valResult['validation-failed'] = true;
							}
						}
					}
					if (empty($valResult['fields'][$name]))
						$valResult['fields'][$name] = true;
				}
				else{
					if (!preg_match ( $fData['regex'], $value)){
						$valResult['fields'][$name] = false;
						$valResult['validation-failed'] = true;
					}
				}
			}
		}

		return $valResult;
    }
    
    
    private static function _isEmpty($value){
        $pattern = '/\s|<br\s*?\/>/mi'; //trim pattern for wysiwyg empty
        $testVal = trim($value);
        $testVal = preg_replace($pattern, '', $testVal); //pattern 
        
        return $testVal == '';
    }
    
    
    /* 
     * Used to validate image
    */
    private static function _validateImage($url, $fieldData){
    
        $imgPath = self::findImagePath($url);


        if (!$imgPath || !filepath_is_safe($imgPath['full'], GSDATAPATH)) //wrong image path or file not exists
            return false;

        list($width, $height) = @getimagesize($imgPath['full']);
        
        if (!$width || !$height) //not image or not readable
            return false;
        
        if ( $fieldData['aspect-ratio-width'] && $fieldData['aspect-ratio-height'] ){
            $reqAspect = floor($fieldData['aspect-ratio-width'] / $fieldData['aspect-ratio-height'] * 100) / 100; //cut float after two places, do not round
            $imAspect = floor($width / $height * 100) / 100; //cut float after two places, do not round
            
            if ($reqAspect != $imAspect)
               return false;
        }       
        
        if ( $fieldData['width-comparator'] && $fieldData['width'] ){ //check width
            if (!self::_validateSize($fieldData['width-comparator'],  $fieldData['width'], $width))
                return false;
        }       

        if ( $fieldData['height-comparator'] && $fieldData['height'] ){ //check height if specified
            if (!self::_validateSize($fieldData['height-comparator'],  $fieldData['height'], $height))
                return false;
        }
        return true;
    }
    
    
    /* 
     * Used to validate image width or height against comparator (lte,gte,=)
    */
    private static function _validateSize($comparator, $targetValue, $value){
        switch($comparator){
            case 'lte':{
                if ($value > $targetValue)
                    return false;
                break;
            }
            case 'gte':{
                if ($value < $targetValue) 
                    return false;
                break;
            }
            default:{ //=
                if ($value != $targetValue)
                   return false;
                break;
            }
        }
        
        return true;
    }
    
    
    /* 
     * Finds image file path from image url (full or root relative)
     * Returns path to its folder from relative to data folder and full path
    */
    public static function findImagePath($url){
        global $GSADMIN;
    
        $dataDirPart = str_replace(GSROOTPATH, '', GSDATAPATH); // "data/"
        $pluginsDirPart = str_replace(GSROOTPATH, '', GSPLUGINPATH); // "plugins/"
        $pluginsDirPart = str_replace(GSROOTPATH, '', GSPLUGINPATH); // "plugins/"

        $host = $_SERVER['SERVER_NAME'] . ($_SERVER['SERVER_PORT'] != '80' ? ':'.$_SERVER['SERVER_PORT'] : '');
        $pathParts = pathinfo($_SERVER['PHP_SELF']);
        
        if ($pathParts['filename'] == 'changedata') //while saving page or while validating by ajax
            $subDir = substr($pathParts['dirname'], 0, strrpos($pathParts['dirname'], $GSADMIN)); //find page subdir
        else
            $subDir = substr($pathParts['dirname'], 0, strpos($pathParts['dirname'], $pluginsDirPart.'SpecialPagesExtras')); //find page subdir
        
        $port = ($p = $_SERVER['SERVER_PORT'])!='80' && $p !='443' ? ':'.$p : '';
        $siteHost = http_protocol()."://". $host . $port;
        
        //delete site host and subdir and data from image url
        if ( substr($url, 0, strlen($siteHost.$subDir.$dataDirPart) ) == $siteHost.$subDir.$dataDirPart) {
            $path = substr($url, strlen($siteHost.$subDir.$dataDirPart));
        }
        else if (substr($url, 0, strlen($subDir.$dataDirPart) ) == $subDir.$dataDirPart){ //if its root relative (ex. /data/uploads/image.jpg)
            $path = substr($url, strlen($subDir.$dataDirPart));
        }
        else{ //wrong path, not full and root relative
            return false;
        }
            
        $file = basename($path);
        $subDir = tsl(dirname($path)); 
        
        
        return array('dir' => $subDir, 'filename' => $file, 'full' => GSDATAPATH.$path);
    }
    
    

}