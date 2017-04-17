<?php
class SPESettings {

	//stores settings, key is special type 
    private static $_storage = array();
    
    //flag telling that all settings for all special types was loaded
    private static $_allLoaded = false;

	
	/* 
	 * This function will load only once settings if it was loaded before, if null it will load all
	*/
    public static function load($specialType = null){
	
		if (self::$_allLoaded || ($specialType && isset(self::$_storage[$specialType]))) {
		// nothing to do - already loaded
		} else if (!self::$_allLoaded) { //not all loaded
			if ($dh = @opendir(GSDATAOTHERPATH)) {
				while ($filename = readdir($dh)) {
					if (substr($filename,-11) == '-extras.xml') { //is extras seettings file
						$n = substr($filename,13,-11); //find name from file name
						if (!isset(self::$_storage[$n]) && (!$specialType || $n === $specialType)) { 
							// load 
                            $ret = array();
                            $xml = getXML(GSDATAOTHERPATH.'i18n_special-' . $n . '-extras.xml');      
    
                            //parse data from settings
                            foreach ($xml->page->children() as $pageSetting)
                            {
                                $name = $pageSetting->getName();
                                $ret['pageData'][$name] = (string)$pageSetting;
                            }     
                            foreach ($xml->fields->children() as $field)
                            {      
                                $data['fields'][] = array();
                            
                                foreach ($field->children() as $fieldSetting)
                                {               
                                    $fieldName = $fieldSetting->getName();
                                    
                                    if(//not empty and of int type cast to int for json
                                        (string)$fieldSetting && 
                                        in_array( $fieldName, array('aspect-ratio-width', 'aspect-ratio-height', 'width', 'height', 'cell-width') )
                                    ){
                                        $ret['fieldsData'][(string)$field->name][$fieldName] = (int)(string)$fieldSetting;
                                    }
                                    else
                                        $ret['fieldsData'][(string)$field->name][$fieldName] = (string)$fieldSetting;
                                }
                            }
                            self::$_storage[$n] = $ret;
						}
					}
				}
				closedir($dh);
			}
		}
		
		///return all or one
		if (!$specialType){
			self::$_allLoaded = true;
			return self::$_storage;
		} else{
			return isset(self::$_storage[$specialType]) ? self::$_storage[$specialType] : null; //no settings when requested that not eexists or settings
		}
    }  

    public static function save(){
        $xml = @new SimpleXMLExtended('<?xml version="1.0" encoding="UTF-8"?><extras></extras>');
        
        $parsedData = self::_parse();

        $page = $xml->addChild('page');
        
        //iterate over params and add to xml
        foreach ($parsedData['page'] as $key => $value){
            $page->addChild($key)->addCData($value);
        }
        
        $fields = $xml->addChild('fields');
		
		// var_dump($_GET['edit']);
        
        for ($i = 0; $i < count($parsedData['fields']); $i++){
            $f = $fields->addChild('field');
            
            //iterate over params and add to xml
            foreach ($parsedData['fields'][$i] as $key => $value){
                $f->addChild($key)->addCData($value);
            }
        }
        
        //store backup
        $oldName = @$_GET['edit'];
        if ($oldName && file_exists(GSDATAOTHERPATH . 'i18n_special-' . $oldName . '-extras.xml')) {
            @copy(GSDATAOTHERPATH . 'i18n_special-' . $oldName . '-extras.xml', GSBACKUPSPATH . 'other/i18n_special-' . $oldName . '-extras.xml'); //create copy
            @unlink(GSDATAOTHERPATH . 'i18n_special-' . $oldName . '-extras.xml'); //delete old
        }
       

        //name must be with minus char not underscore to not be identified as special page type file
        XMLsave($xml, GSDATAOTHERPATH . 'i18n_special-' . $_POST['post-name'] . '-extras.xml');
    }
    
    
    //parse data from POST
    private static function _parse(){
        $data = array('fields' => array(), 'page' => array());
    
   
        $data['page']['type-show'] = isset($_POST['spe_page-type-show']) ? 1 : '';
        $data['page']['options-show'] =  isset($_POST['spe_page-options-show']) ? 1 : '';
        
        $data['page']['title-label-show'] = isset($_POST['spe_page-title-label-show']) ? 1 : '';
        $data['page']['title-label'] = $_POST['spe_page-title-label'];
		
		//title regex
		$titleRegex = $_POST['spe_page-title-regex'];
		if (@preg_match($titleRegex, "Lorem ipsum") === false)
			$titleRegex = ''; //reset to empty
        $data['page']['title-regex'] = $titleRegex;
		
        $data['page']['content-hide'] = isset($_POST['spe_page-content-hide']) ? 1 : '';
        $data['page']['content-label-show'] = isset($_POST['spe_page-content-label-show']) ? 1 : '';
        $data['page']['content-label'] = $_POST['spe_page-content-label'];
        $data['page']['content-required'] = isset($_POST['spe_page-content-required']) ? 1 : '';
        $data['page']['menu-hide'] = isset($_POST['spe_page-menu-hide']) ? 1 : '';
        $data['page']['tags-hide'] = isset($_POST['spe_page-tags-hide']) ? 1 : '';
        $data['page']['meta-description-hide'] = isset($_POST['spe_page-meta-description-hide']) ? 1 : '';
        $data['page']['extra-browser'] = isset($_POST['spe_page-extra-browser']) ? 1 : '';
        
        //fields data;
        for ($i=0; isset($_POST['cf_'.$i.'_name']); $i++) {
            $name = strtolower($_POST['cf_'.$i.'_name']); //all names are used in lowercase but sotred in i18special pages as not touched, weird 
        
            if ($name == ''){ //empty names are removes by special pages core and page settings is saved, also removes last line hidden
                continue;
            }
        
            $a = array(
                'name' => $name,
				
                //supress errors, no javascript = empty vars
                'required' => @$_POST['spe_field-'.$i.'-required'] ? 1 : '',
                'description' => @$_POST['spe_field-'.$i.'-description'],
                'new-type' => $_POST['spe_field-'.$i.'-new-type'],
				//if not specified save default value
                'cell-width-unit' => $_POST['spe_field-'.$i.'-cell-width-unit'] == 'px' ? 'px' : 'percent',			
                'cell-width' => (int)$_POST['spe_field-'.$i.'-cell-width'] > 0 ? (int)$_POST['spe_field-'.$i.'-cell-width'] : ''
            );
			

			$type = $a['new-type'] ? $a['new-type'] : $_POST['cf_'.$i.'_type'];
            //store extra params for special types

            switch ($type) { //type or new type
				case 'multiselect':{
					$a['options'] = implode('||', preg_split("/\r?\n/", rtrim($_POST['cf_'.$i.'_options']))); //store options
				}
   				case 'text':
				case 'textfull':
				case 'textarea':
				case 'multiselect':
				case 'multitext':{
                    $a['regex'] = @$_POST['spe_field-'.$i.'-regex'];
					
					//here is validation of php regullar expression passed from html form
					if (@preg_match($a['regex'], "Lorem ipsum") === false)
						$a['regex'] = ''; //reset to empty
						
                    break;
                }	
				case 'multiimage':
				case 'imagewiththumb':{
					$aw = (int)$_POST['spe_field-'.$i.'-aspect-ratio-width'];
					$ah = (int)$_POST['spe_field-'.$i.'-aspect-ratio-height'];
					
					if ($aw <= 0 || $ah <= 0){ //reset if invalid
						$aw = $ah = '';
					}
					
					$a['aspect-ratio-width'] = $aw; 
					$a['aspect-ratio-height'] = $ah; 
					$a['width-comparator'] = $_POST['spe_field-'.$i.'-width-comparator'];
					$a['width'] = (int)$_POST['spe_field-'.$i.'-width'] > 0 ? (int)$_POST['spe_field-'.$i.'-width']: '';	
					$a['height-comparator'] = $_POST['spe_field-'.$i.'-height-comparator'];
					$a['height'] = (int)$_POST['spe_field-'.$i.'-height'] > 0 ? (int)$_POST['spe_field-'.$i.'-height']: '';
                    break;
                }
            }
            
            $data['fields'][] = $a; //add to array
        }
        
        return $data;
    }
    
 

    

}