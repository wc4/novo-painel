<?php
class SPEClass{

    private static $instance;
    private function __construct() { } 
    private function __clone(){} 
    
 
    public static function getInstance (){
        if (self::$instance === null) {
            require_once('SPESettings.php');
            self::$instance = new SPEClass();
        }
        return self::$instance;
 
        return (self::$instance === null) ? self::$instance = new SPEClass() : self::$instance;
    }
    
    public function editHeader(){
        //extrabrowser is loaded no matter its used or not
        ?>
            <link rel="stylesheet" href="../plugins/SpecialPagesExtras/css/extraBrowser.css" />
            <link rel="stylesheet" href="../plugins/SpecialPagesExtras/css/edit.css" />
            <script type="text/javascript" src="../plugins/SpecialPagesExtras/js/jquery-ui.sort.min.js"></script>
            <script type="text/javascript" src="../plugins/SpecialPagesExtras/js/jquery.extraBrowser.js"></script>
        <?php
    }
    
    public function editExtras($specialName){
        $settings = SPESettings::load($specialName);

        if (empty($settings))
            return;
            
        $pageData = @$settings['pageData'];
        $fieldsData = @$settings['fieldsData'];
        
        $imageFieldNames = array(); //standard image fields
        $hasImageWithThumbsFields = false;
        
        require_once(GSPLUGINPATH.'i18n_specialpages/specialpages.class.php');
        $specialSettings = I18nSpecialPages::getSettings($specialName);
        
        //if extra browser is on, then find all fields that has type of image and pass it to JS
        if (@$pageData['extra-browser']){ //silent error, for old versions that do not have tihs settings
            
			if (isset($specialSettings['fields'])){ //if any special fields exists
				for ($i = 0; $i < count($specialSettings['fields']); $i++) {
					$field = $specialSettings['fields'][$i];
					if ($field['type'] == 'image')
						$imageFieldNames[] = array('name' => strtolower($field['name']), 'index' => $i + 1); //lower is must have, +1 is to match specialepages edit.php
				}
			}
        }
        
        if ($fieldsData){
            foreach ($fieldsData as $name => $data) {
                if ($data['new-type'] == 'imagewiththumb' || $data['new-type'] == 'multiimage')
                    $hasImageWithThumbsFields = true;
            }
        }

        include('views/editScripts.php'); //include all js 
        
        if (@$pageData['extra-browser'] && (count($imageFieldNames) || $hasImageWithThumbsFields))
            include('views/extraBrowser.html'); //include extraBrowseer
    }  


    public function confHeader(){
        ?>
            <link rel="stylesheet" href="../plugins/SpecialPagesExtras/css/configure.css" />
        <?php
    }


    //used when i18n navigation is displayed
    public function onNavigationEditShow(){     
        global $pagesArray;
        $settings = SPESettings::load(null); //load all settings
    
        if (empty($settings))
            return;
            
        $script = '<script>$(document).ready(function() {  ';
        
        
        foreach($pagesArray as $page){
            if (isset($page['special'])){ //if its special
                if (isset($settings[$page['special']])){ //has settings
                    if ($settings[$page['special']]['pageData']['menu-hide']){
                        $script .= '$("#tr-'. $page['url'] .' .modifyable").off();  $("#tr-'. $page['url'] .' .toggleMenu").remove(); '; //disable clicking and hide remove toggler
                    }
                }
            }
        }
        
        $script .= '});</script>';
        
        echo $script;
    }    

    //used when i18n navigation is saving
    public function onNavigationEditSave(){     
        $settings = SPESettings::load(null); //load all settings
    
        if (empty($settings))
            return;
            
            
        getPagesXmlValues(true); //prepare pageArraay
            
        global $pagesArray;
            
        for ($i=0; isset($_POST['page_'.$i.'_url']); $i++) {
            $url = $_POST['page_'.$i.'_url'];
            
            if (isset($pagesArray[$url]['special'])){
				
                if (@$settings[$pagesArray[$url]['special']]['pageData']['menu-hide']){
                    $_POST['page_'.$i.'_menu'] = ''; //reset menu
                }
            }
             
             //obtain special type,
             
             //check that special type extras exists
             
        }
        // $script = '<script>$(document).ready(function() {  ';
        
        
        // foreach($pagesArray as $page){
            // if (isset($page['special'])){ //if its special
                // if (isset($settings[$page['special']])){ //has settings
                    // if ($settings[$page['special']]['pageData']['menu-hide']){
                        // $script .= '$("#tr-'. $page['url'] .' .modifyable").off();  $("#tr-'. $page['url'] .' .toggleMenu").remove(); '; //disable clicking and hide remove toggler
                    // }
                // }
            // }
        // }
        
        // $script .= '});</script>';
        
        // echo $script;
    }   

    //used when page content editions is saving
    public function onPageEditSave($settings){         
        if ($settings['pageData']['menu-hide']) //reset value to ''
            $_POST['post-menu'] = '';

    }
    
    //used when GS deletes file from uploads, delete cached thumb
    public function onFileDelete(){    
        $path = (isset($_GET['path'])) ? $_GET['path'] : '';
        $id = $_GET['file'];            
        $filepath = GSDATAUPLOADPATH . $path;
        $file =  $filepath . $id;
        
        $uploadsDirPart = str_replace(GSDATAPATH, '', GSDATAUPLOADPATH); //find name of uploads subdir
        $thumbsDirPart = str_replace(GSDATAPATH, '', GSTHUMBNAILPATH); //find name of uploads subdir

        //check that path is safe, etc.
        if(path_is_safe($filepath,GSDATAUPLOADPATH) && filepath_is_safe($file,$filepath)){
            //delete from uploads image,  thumb
            @unlink(GSDATAOTHERPATH . 'SpecialPagesExtras/thumbs_cache/extra-browser-thumbs/'.$uploadsDirPart.$path . $id);
            @unlink(GSDATAOTHERPATH . 'SpecialPagesExtras/thumbs_cache/image-field-thumbs/'.$uploadsDirPart.$path . $id);
            @unlink(GSDATAOTHERPATH . 'SpecialPagesExtras/thumbs_cache/multi-image-thumbs/'.$uploadsDirPart.$path . $id);
      
            //delete thumb for thumbnail 
            @unlink(GSDATAOTHERPATH . 'SpecialPagesExtras/thumbs_cache/extra-browser-thumbs/'.$thumbsDirPart.$path . 'thumbnail.'.$id);
            @unlink(GSDATAOTHERPATH . 'SpecialPagesExtras/thumbs_cache/image-field-thumbs/'.$thumbsDirPart.$path . 'thumbnail.'.$id);
            @unlink(GSDATAOTHERPATH . 'SpecialPagesExtras/thumbs_cache/multi-image-thumbs/'.$thumbsDirPart.$path . 'thumbnail.'.$id);
        }	
    }
    
    //used when GS deletes empty folder from uploads
    public function onFolderDelete(){
        $path = (isset($_GET['path'])) ? $_GET['path'] : '';
        $folder = $_GET['folder'];
        $target = GSDATAUPLOADPATH . $path . $folder;
        
        $uploadsDirPart = str_replace(GSDATAPATH, '', GSDATAUPLOADPATH); //find name of uploads subdir
        
        //check that path is safe for uploads
        if (path_is_safe($target,GSDATAUPLOADPATH)) {
       
            //removes empty directory, so we believe that all thumbs from that dir was deleted when user deleted files by admin panel
            @rmdir(GSDATAOTHERPATH . 'SpecialPagesExtras/thumbs_cache/extra-browser-thumbs/'.$uploadsDirPart.$path . $folder);
            @rmdir(GSDATAOTHERPATH . 'SpecialPagesExtras/thumbs_cache/image-field-thumbs/'.$uploadsDirPart.$path . $folder);
            @rmdir(GSDATAOTHERPATH . 'SpecialPagesExtras/thumbs_cache/multi-image-thumbs/'.$uploadsDirPart.$path . $folder);
        }
    }
   
    //work after all scripts
    public function confFooter(){

        if (!isset($_GET['copy'])) //copying old, load copy source settings
            $pageName = isset($_GET['edit']) && $_GET['edit'] ? $_GET['edit'] : @$_POST['post-name']; //might be new one or eiditng old one
        else
            $pageName = @$_GET['copy'];
	
		if ($pageName) //creating new page, screen pageName not exists do not load
			$settings = SPESettings::load($pageName); 

        
        $pageData = @$settings['pageData'];
        $fieldsData = @$settings['fieldsData'];
        
        require('views/confScripts.php'); //include all js 
        require('views/confDialogs.html'); //include dialogs html  
    }
    
    //save settings
    public function confSave(){    
        SPESettings::save(); 
    }
    
    public function confUndo(){
        $name = preg_match('/^[A-Za-z0-9-]+$/', @$_GET['edit']) ? $_GET['edit'] : null;
        $newname = @$_GET['new'];
    
        //supress errors, no extra data
        @unlink(GSDATAOTHERPATH.'i18n_special-' . $newname . '-extras.xml'); //delete old
        @copy(GSBACKUPSPATH.'other/i18n_special-' . $name . '-extras.xml', GSDATAOTHERPATH.'i18n_special-' . $name . '-extras.xml');
    }
    
    
    public function confDelete(){
        $name = $_GET['delete']; 
         //supress errors, no extra data
        @copy(GSDATAOTHERPATH.'i18n_special-' . $name . '-extras.xml', GSBACKUPSPATH.'other/i18n_special-' . $name . '-extras.xml');
        @unlink(GSDATAOTHERPATH . 'i18n_special-' . $name . '-extras.xml');
    }


    
    

}