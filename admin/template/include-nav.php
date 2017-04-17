<?php
/**
 * Navigation Include Template
 *
 * @package GetSimple
 */
 
$debugInfoUrl = 'http://get-simple.info/docs/debugging';

if (cookie_check()) { 
	echo '';
	if (isDebug()) {
		echo '';
	}
	echo '';
} 

//determine page type if plugin is being shown
if (get_filename_id() == 'load') {
	$plugin_class = $plugin_info[$plugin_id]['page_type'];
} else {
	$plugin_class = '';
}

?>
<div class="brand">
    <a href="<?php echo $SITEURL; ?>" target="_blank"><img src="template/assets/img/logo-dark.png" alt="Klorofil Logo" class="img-responsive logo">
    </a>
</div>
<div class="container-fluid">
    <div class="navbar-btn">
        <button type="button" class="btn-toggle-fullwidth"><i class="lnr lnr-arrow-left-circle"></i>
        </button>
    </div>
    <div class="navbar-btn navbar-btn-right">
        <a class="btn btn-success" href="" title="Publicar novo imóvel"><i class="lnr lnr-apartment
"></i> <span>Publicar novo imóvel</span></a>
    </div>
    <div id="navbar-menu">
        <ul class="nav navbar-nav navbar-right">
            <li class="dropdown">
                <a href="#" class="dropdown-toggle icon-menu" data-toggle="dropdown">
                    <i class="lnr lnr-alarm"></i>
                    <span class="badge bg-danger">5</span>
                </a>
                <ul class="dropdown-menu notifications">
                    <li><a href="#" class="notification-item"><span class="dot bg-warning"></span>System space is almost full</a>
                    </li>
                    <li><a href="#" class="notification-item"><span class="dot bg-danger"></span>You have 9 unfinished tasks</a>
                    </li>
                    <li><a href="#" class="notification-item"><span class="dot bg-success"></span>Monthly report is available</a>
                    </li>
                    <li><a href="#" class="notification-item"><span class="dot bg-warning"></span>Weekly meeting in 1 hour</a>
                    </li>
                    <li><a href="#" class="notification-item"><span class="dot bg-success"></span>Your request has been approved</a>
                    </li>
                    <li><a href="#" class="more">See all notifications</a>
                    </li>
                </ul>
            </li>
            <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="lnr lnr-question-circle"></i> <span>Ajuda</span></a>
                <ul class="dropdown-menu">
                    <li><a href="#">Basic Use</a>
                    </li>
                    <li><a href="#">Working With Data</a>
                    </li>
                    <li><a href="#">Security</a>
                    </li>
                    <li><a href="#">Troubleshooting</a>
                    </li>
                </ul>
            </li>
            <li>
                <a href="#" class="dropdown-toggle" data-toggle="dropdown"><img src="template/assets/img/user.png" class="img-circle" alt="Avatar"> <span><?php echo $USR; ?></span>
                </a>
                <ul class="dropdown-menu">
                    <li><a href="#"><i class="lnr lnr-user"></i> <span>My Profile</span></a>
                    </li>
                    <li><a href="#"><i class="lnr lnr-envelope"></i> <span>Message</span></a>
                    </li>
                    <li><a href="#"><i class="lnr lnr-cog"></i> <span>Settings</span></a>
                    </li>
                    <li><a href="logout.php"><i class="lnr lnr-exit"></i> <span>Logout</span></a>
                    </li>
                </ul>
            </li>
            <li>
                <li><a href="logout.php"><i class="lnr lnr-exit"></i> <span>Sair</span></a>
                </li>
            </li>
            <!-- <li>
							<a class="update-pro" href="https://www.themeineed.com/downloads/klorofil-pro-bootstrap-admin-dashboard-template/?utm_source=klorofil&utm_medium=template&utm_campaign=KlorofilPro" title="Upgrade to Pro" target="_blank"><i class="fa fa-rocket"></i> <span>UPGRADE TO PRO</span></a>
						</li> -->
        </ul>
    </div>
</div>
</nav>
<div id="sidebar-nav" class="sidebar">
    <div class="sidebar-scroll">
        <nav>
            <ul class="nav">
                <li><a class="" href="pages.php"><i class="lnr lnr-file-empty"></i> <span>Páginas</span></a>
                </li>
                <li><a href="upload.php"><i class="lnr lnr-cloud-upload"></i> <span>Arquivos</span></a>
                </li>
                <li><a href="theme.php"><i class="lnr lnr-code"></i> <span>Tema</span></a>
                </li>
                <li><a href="backups.php"><i class="lnr lnr-database"></i> <span>Backups</span></a>
                </li>
                <li><a href="plugins.php"><i class="lnr lnr-paperclip"></i> <span>Plugins</span></a>
                </li>
                <?php exec_action( 'nav-tab'); ?>
                <li><a href="settings.php"><i class="lnr lnr-cog"></i> <span>Configurações</span></a>
                </li>
                <li><a href="support.php"><i class="lnr lnr-question-circle"></i> <span>Suporte</span></a>
                </li>               
            </ul>
        </nav>
    </div>
</div>
<?php include( 'template/error_checking.php'); ?>