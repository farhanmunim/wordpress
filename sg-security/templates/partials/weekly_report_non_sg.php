<tr>
                    <td class="body-text"
                        style="color: #363636; font-weight: 700; font-family: 'Roboto', Arial, Helvetica, sans-serif; font-size: 20px; line-height: 30px; padding: 0 0 20px 0"><?php echo esc_html( $args['non_sg']['promo_heading'] ); ?>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 0 0 30px 0;">
                        <table class="infobox" border="0" cellpadding="0" cellspacing="0" width="100%"
                               bgcolor="#eadfed">
                            <tr>
                                <td class="body-text"
                                    style="color: #363636; font-weight: 700; font-family: 'Roboto', Arial, Helvetica, sans-serif; font-size: 20px; line-height: 30px; padding: 30px 30px 0 30px;">
                                    <?php echo esc_html( $args['non_sg']['promo_title'] ); ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="body-text"
                                    style="color: #444444; font-weight: 400; font-family: 'Open Sans', Arial, Helvetica, sans-serif; font-size: 16px; line-height: 26px; padding: 0px 30px 0 30px;">
                                    <?php echo esc_html( $args['non_sg']['promo_subtitle'] ); ?>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 30px 30px 0 30px;;">
                                    <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                        <tr>
                                            <td width="330" valign="top" class="dblock">
                                                <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                                <?php
                                                    foreach ( $args['non_sg']['extras'] as $extra ):
                                                ?>
                                                    <tr>
                                                        <td class="body-text"
                                                            style="color: #444444; font-weight: 400; font-family: 'Open Sans', Arial, Helvetica, sans-serif; font-size: 16px; line-height: 26px;">
                                                            <span style="color: #cdaa72; display: inline-block; padding: 0 5px 0 0;">&#8226;</span>
                                                            <?php echo esc_html( $extra ); ?>
                                                        </td>
                                                    </tr>
                                                <?php
                                                    endforeach;
                                                ?>
                                                    <tr>
                                                        <td class="body-text"
                                                            style="color: #444444; font-weight: 400; font-family: 'Open Sans', Arial, Helvetica, sans-serif; font-size: 16px; line-height: 26px; padding: 0 0 0 15px;">
                                                            <a href="<?php echo esc_url( $args['non_sg']['learn_more_link'] ); ?>" target="_blank" rel="noreferrer" style="color: #22b8d1; text-decoration: none;"><strong><?php echo esc_html( $args['non_sg']['learn_more_text'] ); ?></strong></a>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="body-text"
                                                            style="padding: 30px 0 40px 0">
                                                            <a href="<?php echo esc_url( $args['non_sg']['promo_link'] ); ?>" class="body-text"
                                                               target="_blank" rel="noreferrer" style="font-family: 'Open Sans', Arial, Helvetica, sans-serif; font-size: 14px; font-weight: 600; line-height: 20px; text-transform: uppercase; text-decoration: none; display: inline-block; border-top: 10px solid #f47b44; border-bottom: 10px solid #f47b44; border-left: 20px solid #f47b44; border-right: 20px solid #f47b44;  color: #ffffff; background: #f47b44; border-radius: 2px;"><?php echo esc_html( $args['non_sg']['promo_button'] ); ?></a>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                            <td width="30" style="font-size: 0; line-height: 0;" class="dblock">&nbsp;</td>
                                            <td width="180" valign="top" class="dblock">
                                                <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                                    <tr>
                                                        <td class="body-text" style=";text-align: left; padding: 0 0 40px 0;'">
                                                            <img src="<?php echo esc_url( $args['non_sg']['promo_banner'] ); ?>" width="177" alt="<?php echo esc_html( $args['non_sg']['promo_banner_alt'] ); ?>">
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>

                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>