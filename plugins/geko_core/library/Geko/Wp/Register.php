<?php

//
class Geko_Wp_Register {
	
	// properties
	private static $sLoginFormSrc;
	private static $sFormUrl;
	private static $sProfileUrl;
	private static $sAction;
	private static $bUseClassRegistrationForm = TRUE;
	
	//// methods
	
	// call this method before calling output()
	public static function init() {
		
		global $user_ID;
		
		$sFormUrl = parse_url( $_SERVER[ 'REQUEST_URI' ], PHP_URL_PATH );
		$sAction = $_REQUEST[ 'action' ];
		$aMailLinkActions = array( 'retrievepassword', 'lostpassword', 'resetpass', 'rp' );
		
		// do overrides to $_REQUEST before loading wp-login.php
		if ( 'logout' == $sAction ) {
			$sFormUrl .= '?loggedout=true';
		} elseif ( TRUE == in_array( $sAction, $aMailLinkActions ) ) {
			$_REQUEST[ 'mail_link' ] = Geko_Wp::getUrl() . $sFormUrl;
		}
		
		$_REQUEST[ 'redirect_to' ] = $sFormUrl;
		
		ob_start();
		require_once( './wp-login.php' );		// loads wp-load.php and various functions
		
		// user is not logged in
				
		// utilize what's in wp-login.php
		$sRawLoginFormSource = ob_get_clean();
		
		// extract stuff inside <body>
		$aRegs = array();
		preg_match(
			'/<body[^>]*>(.*)<\/body>/is',
			$sRawLoginFormSource,
			$aRegs
		);
		
		$sLoginFormSrc = $aRegs[1];
		
		// do replacements
		$sLoginFormSrc = str_replace(
			ABS_URL_PREFIX . '/wp-login.php',
			$sFormUrl,
			$sLoginFormSrc
		);
		
		// strip <h1> login tags
		$sLoginFormSrc = preg_replace(
			'/<div id="login"><h1>.*?<\/h1>/is',
			'<div id="login">',
			$sLoginFormSrc
		);
		
		// strip back to blog link
		$sLoginFormSrc = preg_replace(
			'/<p id="backtoblog">.*?<\/p>/is',
			'',
			$sLoginFormSrc
		);
		
		// change redirection page
		$sLoginFormSrc = preg_replace(
			'/<input type="hidden" name="redirect_to" value="[^"]*" \/>/is',
			'<input type="hidden" name="redirect_to" value="' . $sFormUrl . '" />',
			$sLoginFormSrc
		);
		
		self::$sLoginFormSrc = $sLoginFormSrc;
					
		// track action
		self::$sAction = $sAction;
	}
	
	//// accessors
	
	// form URL
	public static function setFormUrl($sFormUrl) {
		self::$sFormUrl = $sFormUrl;
	}
	
	public static function getFormUrl() {
		return self::$sFormUrl;
	}
	
	// profile URL
	public static function setProfileUrl($sProfileUrl) {
		self::$sProfileUrl = $sProfileUrl;
	}
	
	public static function getProfileUrl() {
		if ('' == self::$sProfileUrl) self::$sProfileUrl = ABS_URL_PREFIX . '/wp-admin/profile.php';
		return self::$sProfileUrl;
	}
	
	// use built-in class registration form, instead of wp-login.php
	public static function setUseClassRegistrationForm($bUseClassRegistrationForm) {
		self::$bUseClassRegistrationForm = $bUseClassRegistrationForm;
	}
	
	public static function getUseClassRegistrationForm($bUseClassRegistrationForm) {
		return self::$bUseClassRegistrationForm;
	}	
	
	//
	public static function getIdentity()
	{
		global $user_ID;
		
		if ($user_ID){
			$user_info = get_userdata($user_ID);
			if ('' == $user_info->first_name && '' == $user_info->last_name) {
				return $user_info->user_nicename;
			} else {
				return $user_info->first_name . ' ' . $user_info->last_name;
			}
		}
	}
	
	
	// display form
	public static function output()
	{
		global $user_ID;
		
		if (
			(self::$bUseClassRegistrationForm) &&
			(('register' == self::$sAction) || $user_ID)
		):
			// overrride
			global $errors;
						
			if ($user_ID) {
				$user_info = get_userdata($user_ID);
				$user_login = $user_info->user_login;
				$first_name = $user_info->first_name;
				$last_name = $user_info->last_name;
				$user_email = $user_info->user_email;
			}

			if ('POST' == $_SERVER['REQUEST_METHOD']) {
				$user_login = ($_POST['user_login']) ? $_POST['user_login'] : $user_login;
				$first_name = $_POST['first_name'];
				$last_name = $_POST['last_name'];
				$user_email = $_POST['user_email'];
			} 
			
			//var_dump($user_info);
			
			?>
			<div id="login">
				<p class="message register"><?php if ($user_ID): ?>Update Profile<?php else: ?>Register For This Site<?php endif; ?></p>
			<?php if (is_wp_error($errors)): ?>
				<div id="login_error">
					<?php foreach ($errors->errors as $error):
						echo implode(' ', $error) . '<br />';
					endforeach; ?>
				</div>
			<?php endif; ?>
			<form name="registerform" id="registerform" action="<?php echo $sFormUrl; ?>?action=register" method="post">
				<p>
					<label><?php _e('Username') ?><br />
					<input type="text" name="user_login" id="user_login" class="input" value="<?php echo attribute_escape(stripslashes($user_login)); ?>" size="20" tabindex="10" <?php echo ($user_ID) ? 'disabled="disabled"' : ''; ?>  /></label>
				</p>
				<p>
					<label><?php _e('First Name') ?><br />
					<input type="text" name="first_name" id="first_name" class="input" value="<?php echo attribute_escape(stripslashes($first_name)); ?>" size="20" tabindex="11" /></label>
				</p>
				<p>
					<label><?php _e('Last Name') ?><br />
					<input type="text" name="last_name" id="last_name" class="input" value="<?php echo attribute_escape(stripslashes($last_name)); ?>" size="20" tabindex="12" /></label>
				</p>
				<p>
					<label><?php _e('E-mail') ?><br />
					<input type="text" name="user_email" id="user_email" class="input" value="<?php echo attribute_escape(stripslashes($user_email)); ?>" size="25" tabindex="13" /></label>
				</p>
				<?php if ($user_ID): ?>
					<input name="userid" id="userid" type="hidden" value="<?php echo $user_ID; ?>" />
					<p>
						<label><?php _e('Current Password') ?><br />
						<input type="password" name="currpass" id="currpass" class="input" value="" size="20" tabindex="14" /></label>
					</p>				
					<p>
						<label><?php _e('New Password') ?><br />
						<input type="password" name="pass" id="pass" class="input" value="" size="20" tabindex="14" /></label>
					</p>				
				<?php else: ?>
					<p>
						<label><?php _e('Password') ?><br />
						<input type="password" name="pass" id="pass" class="input" value="" size="20" tabindex="14" /></label>
					</p>				
				<?php endif; ?>
				<p>
					<label><?php _e('Confirm Password') ?><br />
					<input type="password" name="confirmpass" id="confirmpass" class="input" value="" size="20" tabindex="15" /></label>
				</p>
				<?php do_action('register_form'); ?>
				<p class="submit"><input type="submit" name="wp-submit" id="wp-submit" value="<?php ($user_ID) ? _e('Update Profile') : _e('Register'); ?>" tabindex="100" /></p>
							
			</form>
			
			<?php if (!$user_ID): ?><p id="nav">
				<a href="<?php echo site_url('wp-login.php', 'login') ?>"><?php _e('Log in') ?></a> | 
				<a href="<?php echo site_url('wp-login.php?action=lostpassword', 'login') ?>" title="<?php _e('Password Lost and Found') ?>"><?php _e('Lost your password?') ?></a>
			</p><?php endif; ?>
			
			</div>
			
			<script type="text/javascript">
				try{document.getElementById('user_login').focus();}catch(e){}
			</script>			
			<?php
		else:
			// default
			echo self::$sLoginFormSrc;
		endif;
	}
	
}


