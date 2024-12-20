<style>#login { width:360px; padding:5% 0 0; }</style>

<?php if ( ! empty( $args['error'] ) ) : ?>
	<div id="login_error"><strong><?php echo esc_html( $args['error'] ); ?></strong><br /></div>
<?php endif ?>

<form name="sgs2fa_form" id="loginform" action="<?php echo esc_url( $args['action'] ); ?>" method="post">
	<h1><?php esc_html_e( '2-factor Authentication', 'sg-security' ); ?></h1>
	<br />
	<p class="sg-2fa-title"><?php esc_html_e( 'In order to log in, please enter one of the backup codes you have received on your first login:', 'sg-security' ); ?></p>

	<input type="hidden" name="backup-code-used" value="1" />
	<p>
		<br />
		<label for="sgc2fabackupcode"><?php esc_html_e( 'Backup Code:', 'sg-security' ); ?></label>
		<input name="sgc2fabackupcode" id="sgc2fabackupcode" class="input" value="" size="20" pattern="[0-9]*" autofocus />
	</p>

	<?php if ( $args['interim_login'] ) : ?>
		<input type="hidden" name="interim-login" value="1" />
	<?php else : ?>
		<input type="hidden" name="redirect_to" value="<?php echo esc_url( $args['redirect_to'] ); ?>" />
	<?php endif; ?>

	<input type="hidden" name="rememberme" id="rememberme" value="<?php echo esc_html( $args['rememberme'] ); ?>" />
	<p>
		<br />
		<?php submit_button( __( 'Authenticate', 'sg-security' ) ); ?>
	</p>
</form>

