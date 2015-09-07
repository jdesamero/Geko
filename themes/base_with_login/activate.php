<?php

//
class Gloc_Layout_Activate extends Gloc_Layout
{
	
	
	//
	public function start() {
		
		parent::start();
		
		if ( !defined( 'WP_INSTALLING' ) ) {
			define( 'WP_INSTALLING', TRUE );
		}
		
		
		// include GF User Registration functionality
		require_once( sprintf( '%s/includes/signups.php', GFUser::get_base_path() ) );
		
		GFUserSignups::prep_signups_functionality();
		
		do_action( 'activate_header' );
		
		add_action( 'wp_head', function() {
			do_action( 'activate_wp_head' );	
		} );
		
	}
	
	
	
	//
	public function echoHeadLate() {
	
		?>
		<style type="text/css">
			form { margin-top: 2em; }
			#submit, #key { width: 90%; font-size: 24px; }
			#language { margin-top: .5em; }
			.error { background: #f66; }
			span.h3 { padding: 0 8px; font-size: 1.3em; font-family: "Lucida Grande", Verdana, Arial, "Bitstream Vera Sans", sans-serif; font-weight: bold; color: #333; }
		</style>
		<?php
		
	}
	
	//
	public function echoContent() {		
		
		$oPage = $this->newPage();
		
		?>
		
		<div id="content" class="widecolumn">
			<?php if ( empty($_GET['key']) && empty($_POST['key']) ) { ?>
		
				<h2><?php _e('Activation Key Required') ?></h2>
				<form name="activateform" id="activateform" method="post" action="<?php echo network_site_url('?page=gf_activation'); ?>">
					<p>
						<label for="key"><?php _e('Activation Key:') ?></label>
						<br /><input type="text" name="key" id="key" value="" size="50" />
					</p>
					<p class="submit">
						<input id="submit" type="submit" name="Submit" class="submit" value="<?php esc_attr_e('Activate') ?>" />
					</p>
				</form>
		
			<?php } else {
		
				$key = !empty($_GET['key']) ? $_GET['key'] : $_POST['key'];
				$result = GFUserSignups::activate_signup($key);
				if ( is_wp_error($result) ) {
					if ( 'already_active' == $result->get_error_code() || 'blog_taken' == $result->get_error_code() ) {
						$signup = $result->get_error_data();
						?>
						<h2><?php _e('Your account is now active!'); ?></h2>
						<?php
						echo '<p class="lead-in">';
						if ( $signup->domain . $signup->path == '' ) {
							printf( __('Your account has been activated. You may now <a href="%1$s">log in</a> to the site using your chosen username of &#8220;%2$s&#8221;. Please check your email inbox at %3$s for your password and login instructions. If you do not receive an email, please check your junk or spam folder. If you still do not receive an email within an hour, you can <a href="%4$s">reset your password</a>.'), network_site_url( 'wp-login.php', 'login' ), $signup->user_login, $signup->user_email, network_site_url( 'wp-login.php?action=lostpassword', 'login' ) );
						} else {
							printf( __('Your site at <a href="%1$s">%2$s</a> is active. You may now log in to your site using your chosen username of &#8220;%3$s&#8221;. Please check your email inbox at %4$s for your password and login instructions. If you do not receive an email, please check your junk or spam folder. If you still do not receive an email within an hour, you can <a href="%5$s">reset your password</a>.'), 'http://' . $signup->domain, $signup->domain, $signup->user_login, $signup->user_email, network_site_url( 'wp-login.php?action=lostpassword' ) );
						}
						echo '</p>';
					} else {
						?>
						<h2><?php _e('An error occurred during the activation'); ?></h2>
						<?php
						echo '<p>'.$result->get_error_message().'</p>';
					}
				} else {
					extract($result);
					$url = is_multisite() ? get_blogaddress_by_id( (int) $blog_id) : home_url('', 'http');
					$user = new WP_User( (int) $user_id);
					?>
					<h2><?php _e('Your account is now active!'); ?></h2>
		
					<div id="signup-welcome">
						<p><span class="h3"><?php _e('Username:'); ?></span> <?php echo $user->user_login ?></p>
						<p><span class="h3"><?php _e('Password:'); ?></span> <?php echo $password; ?></p>
					</div>
					
					<?php if ( $url != network_home_url('', 'http') ) : ?>
						<p class="view"><?php printf( __('Your account is now activated. <a href="%1$s">View your site</a> or <a href="%2$s">Log in</a>'), $url, $url . 'wp-login.php' ); ?></p>
					<?php else: ?>
						<p class="view"><?php printf( __('Your account is now activated. <a href="%1$s">Log in</a> or go back to the <a href="%2$s">homepage</a>.' ), network_site_url('wp-login.php', 'login'), network_home_url() ); ?></p>
					<?php endif;
				}
			}
			?>
		</div>
		<script type="text/javascript">
			var key_input = document.getElementById('key');
			key_input && key_input.focus();
		</script>
		
		<?php
		
		// Add a key+value of "comments" to enable comments on this page
		if ( $oPage->getMeta( 'comments' ) ) $this->doCommentsTemplate();
		
	}
}



