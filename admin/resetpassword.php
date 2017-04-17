<?php 
/**
 * Reset Password
 *
 * Resets the password for GetSimple control panel access
 *
 * @package GetSimple
 * @subpackage Login
 */

# setup inclusions
$load['plugin'] = true;
include('inc/common.php');

if(isset($_POST['submitted'])){
	
	// check for csrf
	if (!defined('GSNOCSRF') || (GSNOCSRF == FALSE) ) {
		$nonce = $_POST['nonce'];
		if(!check_nonce($nonce, "reset_password")) {
			die("CSRF detected!");
		}
	}
	
	$randSleep = rand(250000,2000000); // random sleep for .25 to 2 seconds

	if(isset($_POST['username']) and !empty($_POST['username']))	{

		# user filename
		$file = _id($_POST['username']).'.xml';
		
		# get user information from existing XML file
		
		if (filepath_is_safe(GSUSERSPATH . $file,GSUSERSPATH)) {
			$data = simplexml_load_file(GSUSERSPATH . $file);
			$USR = strtolower($data->USR);
			$EMAIL = $data->EMAIL;
			
			if(strtolower($_POST['username']) == $USR) {
				# create new random password
				$random = createRandomPassword();
				// $random = '1234';
				
				# create backup
				createBak($file, GSUSERSPATH, GSBACKUSERSPATH);
				
				# create password change trigger file
				$flagfile = GSUSERSPATH . _id($USR).".xml.reset";
				copy(GSUSERSPATH . $file, $flagfile);
				
				# change password and resave xml file
				$data->PWD = passhash($random); 
				$status = XMLsave($data, GSUSERSPATH . $file);
				
				# send the email with the new password
				$subject = $site_full_name .' '. i18n_r('RESET_PASSWORD') .' '. i18n_r('ATTEMPT');
				$message = "<p>". cl($SITENAME) ." ". i18n_r('RESET_PASSWORD') ." ". i18n_r('ATTEMPT').'</p>';
				$message .= "<p>". i18n_r('LABEL_USERNAME').": <strong>". $USR."</strong>";
				$message .= "<br>". i18n_r('NEW_PASSWORD').": <strong>". $random."</strong>";
				$message .= '<br>'. i18n_r('EMAIL_LOGIN') .': <a href="'.$SITEURL . $GSADMIN.'/">'.$SITEURL . $GSADMIN.'/</a></p>';
				exec_action('resetpw-success');
				$status = sendmail($EMAIL,$subject,$message);
				# show the result of the reset attempt
				usleep($randSleep); 
				$status = 'success';
				redirect("resetpassword.php?upd=pwd-".$status);
			} else{
				# username doesnt match listed xml username
				exec_action('resetpw-error');
				usleep($randSleep);
				redirect("resetpassword.php?upd=pwd-success");
			} 
		} else {
			# no user exists for this username, but do not show this to the submitter
			usleep($randSleep);
			redirect("resetpassword.php?upd=pwd-success");
		}
	} else {
		
		# no username was submitted
		redirect("resetpassword.php?upd=pwd-error");
	}
} 

get_template('header', cl($SITENAME).' &raquo; '.i18n_r('RESET_PASSWORD')); 

?>
<div id="wrapper">
		<div class="vertical-align-wrap">
			<div class="vertical-align-middle">
				<div class="auth-box ">
					<div class="left">
						<div class="content">
							<div class="header">								
								<p class="lead"><?php i18n('RESET_PASSWORD'); ?></p>								
							</div>
							<div class="logo text-center"><img src="template/assets/img/logo-dark.png" alt="<?php echo cl($SITENAME); ?>"></div>							
							<?php exec_action('index-login'); ?>
							<form class="form-auth-small" action="<?php myself(); ?>" method="post">
								<div class="form-group">
									<input name="nonce" id="nonce" type="hidden" value="<?php echo get_nonce("reset_password");?>"/>
									<input class="form-control" name="username" type="text" value="" placeholder="<?php i18n('LABEL_USERNAME'); ?>" />									
								</div>
								<p><input class="submit btn btn-primary btn-lg btn-block" type="submit" name="submitted" value="<?php echo i18n('SEND_NEW_PWD'); ?>" /></p>
								
							</form>
							<p class="cta" ><b>&laquo;</b> <a href="<?php echo $SITEURL; ?>"><?php i18n('BACK_TO_WEBSITE'); ?></a> &nbsp; | &nbsp; <a href="index.php"><?php i18n('CONTROL_PANEL'); ?></a> &raquo;</p>
							<p class="desc"><?php i18n('MSG_PLEASE_EMAIL'); ?></p>
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