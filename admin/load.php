<?php 
/**
 * Load Plugin
 *
 * Displays the plugin file passed to it 
 *
 * @package GetSimple
 * @subpackage Plugins
 */


# Setup inclusions
$load['plugin'] = true;
include('inc/common.php');
login_cookie_check();

global $plugin_info;

# verify a plugin was passed to this page
if (empty($_GET['id']) || !isset($plugin_info[$_GET['id']])) {
	redirect('plugins.php');
}

# include the plugin
$plugin_id = $_GET['id'];

get_template('header', cl($SITENAME).' &raquo; '. $plugin_info[$plugin_id]['name']); 

?>
	
<?php include('template/include-nav.php'); ?>
<!--incluir-->
<div class="main">
			<!-- MAIN CONTENT -->
			<div class="main-content">
				<div class="container-fluid">
					<!-- OVERVIEW -->
					<div class="panel panel-headline">						
						<div class="panel-body">							
							<div class="row">
								<div class="col-md-9" id="maincontent">
									<?php call_user_func_array($plugin_info[$plugin_id]['load_data'],array()); ?>	
								</div>
								<div class="col-md-3">
									<?php $res = (@include('template/sidebar-'.$plugin_info[$plugin_id]['page_type'].'.php')); if (!$res) { ?>							      
							        <?php exec_action($plugin_info[$plugin_id]['page_type']."-sidebar"); ?>
								    <?php
								      }
								    ?>
								</div>
							</div>
						</div>
					</div>					
				</div>
			</div>			
		</div>
<!--fim-->
<?php get_template('footer'); ?>