<?php
/*
Plugin Name: I18N Special Pages extras
Description: This plugin allows you to control which special page fields are required, validate fields by regex, it allows you to add additional info text next to field label, add special field types and set some page options like: title label, content label, hide keywords and meta descriptions fields etc.
Version: 1.22
Author: Michał Gańko
Author URI: http://flexphperia.net
*/

# get correct id for plugin
$thisfile=basename(__FILE__, ".php");

# register plugin
register_plugin(
	$thisfile, 
	'I18N Special Pages extras', 	
	'1.24', 		
	'Michał Gańko',
	'http://flexphperia.net', 
	'This plugin allows you to control which special page fields are required, validate fields by regex, it allows you to add additional info text next to field label, add special field types and set some page options like: title label, content label, hide keywords and meta descriptions fields etc.',
    'plugins'
);


//replace deafult edit-extras hook 
if(strtolower(get_filename_id()) == 'edit'){
	global $plugins;
	
	//remove old hook that is added by special pages plugin
	foreach ($plugins as $key => $hook)	{
		if ($hook['function'] == 'i18n_specialpages_edit'){
			
			$hook['function'] = 'spe_i18n_specialpages_edit';
			$hook['file'] = basename(__FILE__);
			$plugins[$key] = $hook;
			break;
		}
	}
	
	//from original backend.class.php,
	# hack to ensure that i18n special pages action is called last:
	for ($i=0; $i<count($plugins); $i++) {
		if ($plugins[$i]['function'] == 'spe_i18n_specialpages_edit') {
			$item = $plugins[$i];
			$plugins[$i] = array('hook' => 'dummy', 'function' => 'dummy'); //do not unsed, set dummy values to prevent undefined errors on backend.class.php and plugin_functions.php
			$plugins[] = $item;
			break;
		}
	}
}

//modified on page extras
function spe_i18n_specialpages_edit(){
	require_once(GSPLUGINPATH.'i18n_specialpages/specialpages.class.php');
	require_once(GSPLUGINPATH.'i18n_specialpages/backend.class.php');
	include(GSPLUGINPATH.'SpecialPagesExtras/views/pageEdit.php');
}

//modified special pages function
function spe_i18n_specialpages_main() {
    require_once(GSPLUGINPATH.'i18n_specialpages/specialpages.class.php');
    if (isset($_GET['create'])) {
    include(GSPLUGINPATH.'i18n_specialpages/create.php');
    } else if (isset($_GET['pages'])) {
    include(GSPLUGINPATH.'i18n_specialpages/pages.php');
    } else if (isset($_GET['config']) && isset($_GET['edit'])) {
    include(GSPLUGINPATH.'i18n_specialpages/conf_edit.php');
    } else if (isset($_GET['config']) && isset($_GET['settings'])) {
    include(GSPLUGINPATH.'i18n_specialpages/conf_settings.php');
    } else if (isset($_GET['config'])) {
    include(GSPLUGINPATH.'i18n_specialpages/conf_overview.php');
    }
    
    if ( isset($_GET['config']) ){
        require_once('SpecialPagesExtras/SPEClass.php');
        $spe = SPEClass::getInstance();
    
        //was undo and success ($success is from i18n_specialpages/pages.php file)
        if (isset($_GET['edit']) && isset($_GET['undo']) && !isset($_POST['save']) && @$success ){
            $spe->confUndo();
        }
        else if (isset($_GET['edit']) && isset($_POST['save']) && @$success ) {  //was save and success
            $spe->confSave();
        }   
        else if (isset($_GET['delete']) && @$success ) {  //was save and success
            $spe->confDelete();
        }  
    }
}

if (!is_frontend()){

    global $LANG;
    i18n_merge('SpecialPagesExtras', substr($LANG,0,2)) || i18n_merge('SpecialPagesExtras','en');

    add_action('header', 'spe_on_header'); 
    add_action('footer', 'spe_on_footer'); 
    add_action('common', 'spe_on_common'); 
    
    add_action('edit-extras', 'spe_on_edit_extras'); 
    
    //if saving page and its special page, security check functions
    if (strtolower(get_filename_id()) == 'changedata' && @$_POST['post-special']) {
        require_once('SpecialPagesExtras/SPEClass.php');
        require_once('SpecialPagesExtras/SPEValidator.php');
        $spe = SPEClass::getInstance();
        
        $pSettings = SPESettings::load(@$_POST['post-special']);
        
        $spe->onPageEditSave($pSettings);

        $res = SPEValidator::editValidateData($pSettings);
        
		if ($res['validation-failed'])
			die('SpecialPagesExtras: validation security check failed!');
    }   
    else if (strtolower(get_filename_id()) == 'deletefile') {
        if ( isset($_GET['file']) ){
            require_once('SpecialPagesExtras/SPEClass.php');
            $spe = SPEClass::getInstance();
            $spe->onFileDelete();
        }
        else if ( isset($_GET['folder']) ){ //removing empty folder
            require_once('SpecialPagesExtras/SPEClass.php');
            $spe = SPEClass::getInstance();
            $spe->onFolderDelete();
        }
    }   
    else if (strtolower(get_filename_id()) == 'load' && $_GET['id'] == 'i18n_navigation') { //navigation edition
         require_once('SpecialPagesExtras/SPEClass.php');
        $spe = SPEClass::getInstance();
        
        $spe->onNavigationEditSave();        
    } 

}

// function spe_admin_tab(){
    // require_once('SpecialPagesExtras/SPEClass.php');
    // $spe = SPEClass::getInstance();
    
    // if (@$_POST['save'])
        // $settings = $spe->saveGlobalSettings();
    
    // $settings = $spe->loadGlobalSettings();

    // require_once('SpecialPagesExtras/globalConf.html');
// }

function spe_on_common(){
	//hooked common
	//override i18n_specialpages start function
	if(strtolower(get_filename_id()) == 'load' && $_GET['id'] == 'i18n_specialpages'){
		global $plugin_info;  
		$plugin_info['i18n_specialpages']['load_data'] = 'spe_i18n_specialpages_main';
	}
}

function spe_on_header(){
    
    if ( strtolower(get_filename_id()) == 'edit'  ){
        require_once('SpecialPagesExtras/SPEClass.php');
        $spe = SPEClass::getInstance();
        $spe->editHeader();
    }   
    else if ( strtolower(get_filename_id()) == 'load' && @$_GET['id'] == 'i18n_specialpages' && isset($_GET['config']) && isset($_GET['edit']) ){
        require_once('SpecialPagesExtras/SPEClass.php');
        $spe = SPEClass::getInstance();
        $spe->confHeader();
    }
}

function spe_on_footer(){
    require_once('SpecialPagesExtras/SPEClass.php');

    //if in edition special page
    if ( strtolower(get_filename_id()) == 'load' && @$_GET['id'] == 'i18n_specialpages' && isset($_GET['config']) && isset($_GET['edit']) ){
        $spe = SPEClass::getInstance();
        $spe->confFooter();
    }
    else if (strtolower(get_filename_id()) == 'load' && $_GET['id'] == 'i18n_navigation') { //navigation edition
        $spe = SPEClass::getInstance();
        
        $spe->onNavigationEditShow();        
    } 
 
}

function spe_on_edit_extras(){
    global $data_edit;
    $spname = null;
    
    //copied and little modified form 18n special pages
    if (isset($_GET['special'])) {
        // create a special page or change the type of page
        $spname = $_GET['special'];
    } else if (isset($data_edit) && isset($data_edit->special) && (string) $data_edit->special) {
        // edit a special page
        $spname = (string) $data_edit->special;
    } else if (isset($_GET['newid']) && strpos($_GET['newid'],'_') > 0) {
        // this language page should be the same as the default language page
        $id_base = substr($_GET['newid'], 0, strrpos($_GET['newid'],'_'));
        $data_base = getXML(GSDATAPAGESPATH . $id_base . '.xml');
        if (isset($data_base) && isset($data_base->special) && (string) $data_base->special) {
          $spname = (string) $data_base->special;
        }
    }
        
    if ( $spname ){
        require_once('SpecialPagesExtras/SPEClass.php');
        $spe = SPEClass::getInstance();
        $spe->editExtras($spname); 
    }

}