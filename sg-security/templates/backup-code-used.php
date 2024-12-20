<style>#login { width:360px; padding:5% 0 0; }</style>

<form name="sgs2fa_form" id="loginform" action="<?php echo esc_url( $args['redirect_to'] ); ?>" method="post">
	<h1><?php echo esc_html_e( 'You are logging in with a one-time Backup Code', 'sg-security' ); ?></h1>
	<br />
	<p class="sg-2fa-title"><?php echo esc_html_e( 'We have noticed that you’re using a one-time backup code to log in. This code cannot be used anymore. In case you lost access to your authenticator app, please ask the website administrator to reset your 2FA setup.', 'sg-security' ); ?></p>
	
	<button id="saved_codes" href="<?php echo esc_url( $args['redirect_to'] ); ?>" class="button button-primary"><?php esc_html_e( 'Continue', 'sg-security' ); ?></button>
</form>
