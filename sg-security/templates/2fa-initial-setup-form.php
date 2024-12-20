<style>#login { width:360px; padding:5% 0 0; }</style>

<?php if ( ! empty( $args['error'] ) ) : ?>
	<div id="login_error"><strong><?php echo esc_html( $args['error'] ); ?></strong><br /></div>
<?php endif ?>

<form name="sgs2fa_form" id="loginform" action="<?php echo esc_url( $args['action'] ); ?>" method="post">
	<h1><?php esc_html_e( '2-factor Authentication', 'sg-security' ); ?></h1>
	<br />
	<p class="sg-2fa-title"><?php esc_html_e( 'The administrator of this site has asked that you enable 2-factor authentication. To do that, install the Google Authenticator app and scan the QR code below to add a token for this website.', 'sg-security' ); ?></p>

	<?php include_once SG_Security\DIR . '/templates/partials/2fa-qr-secret.php'; ?>

	<p>
		<br />
		<label for="sgc2facode"><?php esc_html_e( 'Authentication Code:', 'sg-security' ); ?></label>
		<input name="sgc2facode" id="sgc2facode" class="input" value="" size="20" pattern="[0-9]*" autofocus />
	</p>

	<?php if ( $args['interim_login'] ) : ?>
		<input type="hidden" name="interim-login" value="1" />
	<?php else : ?>
		<input type="hidden" name="redirect_to" value="<?php echo esc_url( $args['redirect_to'] ); ?>" />
	<?php endif; ?>
	<input type="hidden" name="rememberme" id="rememberme" value="<?php echo esc_html( $args['rememberme'] ); ?>" />
	<input name="do_not_challenge" type="checkbox" id="do_not_challenge" />
	<label for="do_not_challenge"><?php esc_html_e( 'Do not challenge me for the next 30 days.', 'sg-security' ); ?></label>
	<p>
		<br />
		<?php submit_button( __( 'Authenticate', 'sg-security' ) ); ?>
	</p>
</form>
