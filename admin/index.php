<?php
/**
 * Login
 *
 * Allows access to the GetSimple control panel
 *
 * @package GetSimple
 * @subpackage Login
 */

# Setup inclusions
$load['login'] = true;
$load['plugin'] = true;

// wrap all include and header output in output buffering to prevent sending before headers.
ob_start();
	include('inc/common.php');
	get_template('header', cl($SITENAME).' &raquo; '.i18n_r('LOGIN')); 
ob_end_flush();

?>
<div id="wrapper">
		<div class="vertical-align-wrap">
			<div class="vertical-align-middle">
				<div class="auth-box ">
					<div class="left">
						<div class="content">
							<div class="header">								
								<p class="lead">Acesse sua conta</p>
							</div>
							<div class="logo text-center"><img src="template/assets/img/logo-dark.png" alt="<?php echo cl($SITENAME); ?>"></div>							
							<?php exec_action('index-login'); ?>
							<form class="form-auth-small" action="<?php echo myself(false).'?'. htmlentities($_SERVER['QUERY_STRING'], ENT_QUOTES); ?>" method="post">
								<div class="form-group">
									<input type="text" class="form-control" id="userid" name="userid" placeholder="<?php i18n('USERNAME'); ?>" />
								</div>
								<div class="form-group">
									<input type="password" class="form-control" id="pwd" name="pwd" placeholder="<?php i18n('PASSWORD'); ?>" />
								</div>
								<p><input type="submit" name="submitted" class="submit btn btn-primary btn-lg btn-block" value="<?php i18n('LOGIN'); ?>" /></p>
							</form>
							<p class="cta" ><b>&laquo;</b> <a href="<?php echo $SITEURL; ?>"><?php i18n('BACK_TO_WEBSITE'); ?></a> &nbsp; | &nbsp; <a href="resetpassword.php"><?php i18n('FORGOT_PWD'); ?></a> &raquo;</p>
							<div class="reqs" ><?php exec_action('login-reqs'); ?></div>
						</div>
					</div>
					<div class="right">
						<div class="overlay"></div>
						<div class="content text">
							<h1 class="heading">Painel administrativo</h1>
							<p>by The Develovers</p>
							<?php include('template/error_checking.php'); ?>
						</div>
					</div>
					<div class="clearfix"></div>
				</div>
			</div>
		</div>
	</div>
<?php get_template('footer'); ?>