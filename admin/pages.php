<?php
/**
 * All Pages
 *
 * Displays all pages 
 *
 * @package GetSimple
 * @subpackage Page-Edit
 */

// Setup inclusions
$load['plugin'] = true;

// Include common.php
include('inc/common.php');

// Variable settings
login_cookie_check();
$id      =  isset($_GET['id']) ? $_GET['id'] : null;
$ptype   = isset($_GET['type']) ? $_GET['type'] : null; 
$path    = GSDATAPAGESPATH;
$counter = '0';
$table   = '';

# clone attempt happening
if ( isset($_GET['action']) && isset($_GET['id']) && $_GET['action'] == 'clone') {
	
	// check for csrf
	if (!defined('GSNOCSRF') || (GSNOCSRF == FALSE) ) {
		$nonce = $_GET['nonce'];
		if(!check_nonce($nonce, "clone", "pages.php")) {
			die("CSRF detected!");	
		}
	}

	# check to not overwrite
	$count = 1;
	$newfile = GSDATAPAGESPATH . $_GET['id'] ."-".$count.".xml";
	if (file_exists($newfile)) {
		while ( file_exists($newfile) ) {
			$count++;
			$newfile = GSDATAPAGESPATH . $_GET['id'] ."-".$count.".xml";
		}
	}
	$newurl = $_GET['id'] .'-'. $count;
	
	# do the copy
	$status = copy($path.$_GET['id'].'.xml', $path.$newurl.'.xml');
	if ($status) {
		$newxml = getXML($path.$newurl.'.xml');
		$newxml->url = $newurl;
		$newxml->title = $newxml->title.' ['.i18n_r('COPY').']';
		$newxml->pubDate = date('r');
		$status = XMLsave($newxml, $path.$newurl.'.xml');
		if ($status) {
			create_pagesxml('true');
			header('Location: pages.php?upd=clone-success&id='.$newurl);
		} else {
			$error = sprintf(i18n_r('CLONE_ERROR'), $_GET['id']);
			header('Location: pages.php?error='.$error);
		}
	} else {
		$error = sprintf(i18n_r('CLONE_ERROR'), $_GET['id']);
		header('Location: pages.php?error='.$error);
	}
}


getPagesXmlValues(true);

$count = 0;
foreach ($pagesArray as $page) {
	if ($page['parent'] != '') { 
		$parentTitle = returnPageField($page['parent'], "title");
		$sort = $parentTitle .' '. $page['title'];		
		$sort = $parentTitle .' '. $page['title'];
	} else {
		$sort = $page['title'];
	}
	$page = array_merge($page, array('sort' => $sort));
	$pagesArray_tmp[$count] = $page;
	$count++;
}
// $pagesArray = $pagesArray_tmp;
$pagesSorted = subval_sort($pagesArray_tmp,'sort');
$table = get_pages_menu('','',0);

get_template('header', cl($SITENAME).' &raquo; '.i18n_r('PAGE_MANAGEMENT')); 

?>
<?php include('template/include-nav.php'); ?>
<!--incluir-->
<div class="main">
			<!-- MAIN CONTENT -->
			<div class="main-content">
				<div class="container-fluid">
					<!-- OVERVIEW -->
					<div class="panel panel-headline">
						<div class="panel-heading">
							<h3 class="panel-title"><?php i18n('PAGE_MANAGEMENT'); ?></h3>							
						</div>
						<div class="panel-body">
							<div class="row">
								<div class="col-md-3">
									<div class="metric">
										<span class="icon"><i class="fa fa-home"></i></span>
										<p>
											<span class="number">45</span>
											<span class="title">Imóveis</span>
										</p>
									</div>
								</div>
								<div class="col-md-3">
									<div class="metric">
										<span class="icon"><i class="fa fa-shopping-bag"></i></span>
										<p>
											<span class="number">203</span>
											<span class="title">Sales</span>
										</p>
									</div>
								</div>
								<div class="col-md-3">
									<div class="metric">
										<span class="icon"><i class="fa fa-eye"></i></span>
										<p>
											<span class="number">274,678</span>
											<span class="title">Visits</span>
										</p>
									</div>
								</div>
								<div class="col-md-3">
									<div class="metric">
										<span class="icon"><i class="fa fa-bar-chart"></i></span>
										<p>
											<span class="number">35%</span>
											<span class="title">Conversions</span>
										</p>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-9" id="maincontent">									
									<?php exec_action('pages-main'); ?>										
											<div id="filter-search">
												<form><input type="text" autocomplete="off" class="form-control" id="q" placeholder="Filtrar, exemplo: Quem Somos, Apartamento, entre outros..." /> &nbsp; <a href="pages.php" class="cancel">Esconder campo de busca</a></form>
											</div>
											
											<table id="editpages" class="table table-striped">
												<tr><th class="col-md-8"><?php i18n('PAGE_TITLE'); ?></th><th style="text-align:right;" class="col-md-3"><?php i18n('DATE'); ?></th><th></th><th class="col-md-1"></th></tr>
												<?php echo $table; ?>
											</table>
											<p><em><b><span id="pg_counter"><?php echo $count; ?></span></b> <?php i18n('TOTAL_PAGES'); ?></em></p>
								</div>
								<div class="col-md-3">
									<?php include('template/sidebar-pages.php'); ?>
								</div>
							</div>
						</div>
					</div>					
				</div>
			</div>			
		</div>
<!--fim-->
<?php get_template('footer'); ?>
