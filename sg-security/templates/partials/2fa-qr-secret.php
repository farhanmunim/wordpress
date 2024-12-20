<div class="qr-section" style="text-align: center">
	<br />
	<img src="<?php echo esc_url( $args['qr'] ); ?>">
	<input type="hidden" name="sgs-2fa-setup" value="1" />
</div>
<div class="qr-section" style="text-align: center">
	<br />
	<label for="sgc2facode"><?php esc_html_e( 'Secret Key:', 'sg-security' ); ?></label>
	<br />
	<text><b><?php echo esc_html( $args['secret'] ); ?></b><text>
	</div>
	<div class="qr-section">
	<br />
	<p><?php esc_html_e( 'You can use the secret key to add the token to the Authenticator app.', 'sg-security' ); ?></p>
</div>