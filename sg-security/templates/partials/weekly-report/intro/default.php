<tr>
    <td class="body-text"
        style="color: #444444; font-weight: 400; font-family: 'Open Sans', Arial, Helvetica, sans-serif; font-size: 16px; line-height: 26px; padding: 0px 0 40px 0">
        <?php echo esc_html( $args['email_body']['summary'] ); ?><a href="<?php echo esc_url( $args['domain'] ); ?>" target="_blank" rel="noreferrer" style="color: #22b8d1; text-decoration: none;"><?php echo esc_html( $args['domain'] ); ?></a><?php echo esc_html( $args['email_body']['traffic'] ); ?><a href="<?php echo esc_url( $args['activity_log_link'] ); ?>" target="_blank" rel="noreferrer" style="color: #22b8d1; text-decoration: none;"><?php echo esc_html( $args['email_body']['activity_log_dashboard'] ); ?></a><?php echo esc_html( $args['email_body']['details'] ); ?>
    </td>
</tr>