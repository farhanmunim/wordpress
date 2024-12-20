<br />
<h1><?php esc_html_e( 'New Backup Codes', 'sg-security' ); ?></h1>
<br />
<p class="sg-2fa-title"><?php esc_html_e( 'As you have used all backup codes available for your user, we have generated new ones for you.', 'sg-security' ); ?></p>
<br />
<p><b><?php esc_html_e( 'Save the codes to make sure that you don’t end up locked out of this website.', 'sg-security' ); ?></b> <?php esc_html_e( 'Each code can only be used once.', 'sg-security' ); ?></p>

<?php if ( ! empty( $args['backup_codes'] ) ) : ?>
	<div class="qr-section" style="text-align: center">
		<pre style="margin: 20px;"><code style="background: #2b2b2b; color: #f8f8f2;"><?php echo implode( "\n", esc_html( $args['backup_codes'] ) ); ?></code></pre>
	</div>
<?php endif ?>
<div class="backup_codes_written" style="text-align: left">
	<input name="backup_codes_checkbox" type="checkbox" id="backup_codes_checkbox" onchange="document.getElementById('saved_codes').disabled = !this.checked;" />
	<label for="backup_codes_checkbox"><?php esc_html_e( 'I have saved my backup codes.', 'sg-security' ); ?></label>
</div>
<br />
