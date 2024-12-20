<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en"
      xmlns:v="urn:schemas-microsoft-com:vml"
      xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <!--[if !mso]><!-->
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <!--<![endif]-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="color-scheme" content="light dark"/>
    <meta name="supported-color-schemes" content="light dark"/>
    <meta name="description" content="SiteGround Newsletter"/>
    <title>Security Optimizer by SiteGround</title>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,600|Roboto:400,700" rel="stylesheet"/>
    <style type="text/css">
        :root {
            color-scheme: light dark;
            supported-color-schemes: light dark;
        }
        body { margin: 0; padding: 0; width: 100% !important; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        img { max-width: 100%;outline: none; text-decoration: none; -ms-interpolation-mode: bicubic; display: block !important; border: none;}
        #backgroundTable { margin: 0; padding: 10px 10px 10px 10px; width: 100% !important; line-height: 100%;}
        @media screen and (max-width: 480px), screen and (max-device-width: 480px) {
            .flex, [class=flex] { width: 94% !important; }
            .dblock, [class=dblock] { display: block !important; width: 100% !important; max-width: 100%; padding: 0 !important; max-height: none !important; }

            #backgroundTable { padding: 10px 0 10px 0px;}
            a { word-break: break-word;}
        }
        @media (prefers-color-scheme: dark) {
            .dark-img {display:block !important; width: auto !important; overflow: visible !important; float: none !important; max-height:inherit !important; max-width:inherit !important; line-height: auto !important; margin-top:0px !important; visibility:inherit !important; }
            .light-img { display:none !important; }
            #backgroundTable, body { background: #363636 !important;}
            .body-text, h1, h2, p, span, strong, em, b { color: #f2f2f2 !important; }
            .infobox { background: #666666 !important;}
            .datatable td, .datatable th{background: #363636 !important;}
            a{ color: #3adcf7 !important; }
            [data-ogsc] .dark-img { display:block !important; width: auto !important; overflow: visible !important; float: none !important; max-height:inherit !important; max-width:inherit !important; line-height: auto !important; margin-top:0px !important; visibility:inherit !important; }
            [data-ogsc] .light-img { display:none !important; }
            [data-ogsb] #backgroundTable, body { background: #363636 !important;}
            [data-ogsc] .body-text, h1, h2, p, span, strong, em, b { color: #f2f2f2 !important; }
            [data-ogsb] .infobox { background: #666666 !important;}
            [data-ogsb] .datatable td, .datatable th{background: #363636 !important;}
            [data-ogsc] a{ color: #3adcf7 !important; }
        }
    </style>
    <!--Fallback For Outlook -->
    <!--[if mso]>
    <style type=”text/css”>
        .body-text {
            font-family: Arial, sans-serif !important;
        }
    </style>
    <![endif]-->
    <!--MS Outlook 120 DPI fix-->
    <!--[if gte mso 9]>
    <xml>
        <o:OfficeDocumentSettings>
            <o:AllowPNG/>
            <o:PixelsPerInch>96</o:PixelsPerInch>
        </o:OfficeDocumentSettings>
    </xml>
    <![endif]-->
</head>
<body style="margin: 0; padding: 0;">
<table border="0" cellpadding="0" cellspacing="0" width="100%" id="backgroundTable" style="background: #ffffff;">
    <tr>
        <td>
            <!-- Main Container -->
            <table class="flex" align="center" border="0" cellpadding="0" cellspacing="0" width="600"
                   style="border-collapse: collapse; font-family: 'Open Sans', Arial, Helvetica, sans-serif;">


                <tr>
                    <td style="padding: 30px 0 30px 0;">
                        <img
                                src="<?php echo esc_url( $args['email_image'] ); ?>"
                                width="600" alt="SiteGround Security Optimizer weekly report"
                                style="max-height: 300px;"/>
                    </td>
                </tr>
                <tr>
                    <td class="body-text"
                        style="color: #363636; font-weight: 700; font-family: 'Roboto', Arial, Helvetica, sans-serif; font-size: 26px; line-height: 38px; padding: 0 0 25px 0"><?php esc_html_e( 'Hey there,', 'sg-security' ); ?>
                    </td>
                </tr>
                <?php
                    include_once( $args['intro_path'] );
                ?>
                <tr>
                    <td class="body-text"
                        style="color: #363636; font-weight: 700; font-family: 'Roboto', Arial, Helvetica, sans-serif; font-size: 20px; line-height: 30px; padding: 0 0 20px 0"><?php esc_html_e( 'Traffic summary for ', 'sg-security' ); echo esc_html( $args['start_time'] ); ?> - <?php echo esc_html( $args['end_time'] ); ?>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 0 0 30px 0;">
                        <table class="infobox" border="0" cellpadding="0" cellspacing="0" width="100%"
                               bgcolor="#ececec">
                            <tr>
                                <td style="padding: 30px 30px 30px 30px;">
                                    <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                        <tr>
                                            <td valign="top" align="center" class="body-text" style="width:50%; padding: 0 0 30px 0;">
                                                <div class="body-text" style="color: #363636; font-weight: 700; font-family: 'Roboto', Arial, Helvetica, sans-serif; font-size: 26px; line-height: 38px; padding: 0 15px; text-align: center"><?php echo esc_html( $args['total_human'] ); ?></div>
                                                <div class="body-text" style="color: #444444; font-weight: 400; font-family: 'Open Sans', Arial, Helvetica, sans-serif; font-size: 16px; line-height: 26px; padding: 0 15px; text-align: center; display: block">
                                                <?php esc_html_e( 'Human traffic', 'sg-security' ); ?>
                                                 </div>
                                            </td>
                                            <td valign="top" align="center" class="body-text" style="width:50%; padding: 0 0 30px 0;">
                                                <div class="body-text" style="color: #363636; font-weight: 700; font-family: 'Roboto', Arial, Helvetica, sans-serif; font-size: 26px; line-height: 38px; padding: 0 15px; text-align: center"><?php echo esc_html( $args['total_bots'] ); ?></div>
                                                <div class="body-text" style="color: #444444; font-weight: 400; font-family: 'Open Sans', Arial, Helvetica, sans-serif; font-size: 16px; line-height: 26px; padding: 0 15px; text-align: center; display: block">
                                                <?php esc_html_e( 'Bot traffic', 'sg-security' ); ?>
                                                 </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td valign="top" align="center" class="body-text" style="width:50%;">
                                                <div class="body-text" style="color: #363636; font-weight: 700; font-family: 'Roboto', Arial, Helvetica, sans-serif; font-size: 26px; line-height: 38px; padding: 0 15px; text-align: center"><?php echo esc_html( $args['total_blocked_login'] ); ?></div>
                                                <div class="body-text" style="color: #444444; font-weight: 400; font-family: 'Open Sans', Arial, Helvetica, sans-serif; font-size: 16px; line-height: 26px; padding: 0 15px; text-align: center; display: block">
                                                 <?php esc_html_e( 'Blocked login attempts', 'sg-security' ); ?>
                                                 </div>
                                            </td>
                                            <td valign="top" align="center" class="body-text" style="width:50%;">
                                                <div class="body-text" style="color: #363636; font-weight: 700; font-family: 'Roboto', Arial, Helvetica, sans-serif; font-size: 26px; line-height: 38px; padding: 0 15px; text-align: center"><?php echo esc_html( $args['total_blocked_visits'] ); ?></div>
                                                <div class="body-text" style="color: #444444; font-weight: 400; font-family: 'Open Sans', Arial, Helvetica, sans-serif; font-size: 16px; line-height: 26px; padding: 0 15px; text-align: center; display: block">
                                                <?php esc_html_e( 'Blocked visit attempts', 'sg-security' ); ?>
                                                 </div>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <?php
                    include_once( $args['learn_more_path'] );
                ?>
                <?php if (
                    0 === $args['is_siteground'] &&
                    1 === $args['agreed_email_consent']
                ) {
                    include_once( \SG_Security\DIR . '/templates/partials/weekly_report_non_sg.php' );
                } ?>
                <tr>
                    <td class="body-text"
                        style="color: #a4a4a4; font-weight: 400; font-family: 'Open Sans', Arial, Helvetica, sans-serif; font-size: 13px; line-height: 20px; padding: 0px 0 25px 0">
                        <?php echo esc_html( $args['unsubscribe']['text'] ); ?><a href="<?php echo esc_url( $args['activity_log_link'] ); ?>" target="_blank" rel="noreferrer" style="color: #22b8d1; text-decoration: none;"><?php echo esc_html( $args['unsubscribe']['button'] ); ?></a>.
                    </td>
                </tr>

            </table>

            <!-- End Main Container -->
        </td>
    </tr>
</table>

</body>
</html>
