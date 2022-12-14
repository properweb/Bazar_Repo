<!doctype html>
<html lang="en-US">

    <head>
        <meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
        <title>Activate Shop Email</title>
        <meta name="description" content="Activate Shop Email Template.">
        <style type="text/css">
            .btn {
                color: #ffffff !important;
                text-decoration: none;
                background: #393939;
                border: none;
                height: 43px;
                font-family: Futura!important;
                font-size: 18px;
                font-weight: 200;
                line-height: 43px;
                padding: 0 20px;
                width: 190px;
                margin:auto;
                border-radius: 0.25rem;
                display: inline-block;
                cursor: pointer;
            }
            .btn:hover {
                background: #000;
            }
        </style>
    </head>

    <body marginheight="0" topmargin="0" marginwidth="0" style="margin: 0px; background-color: #f2f3f8;" leftmargin="0">
        <!--100% body table-->
        <table cellspacing="0" border="0" cellpadding="0" width="100%" bgcolor="#f2f3f8"
               style="@import url(https://fonts.googleapis.com/css?family=Futura:300,400,500,700|Open+Sans:300,400,600,700); font-family: 'Open Sans', sans-serif;">
            <tr>
                <td>
                    <table style="background-color: #f2f3f8; max-width:670px;  margin:0 auto;" width="100%" border="0"
                           align="center" cellpadding="0" cellspacing="0">
                        <tr>
                            <td style="height:80px;">&nbsp;</td>
                        </tr>
                        <tr>
                            <td style="text-align:center;">
                                <a href="{{ $site_url }}" target="_blank">
                                    <img align="center" alt="{{ $site_name }}" border="0" class="center fixedwidth" src="{{ $site_url }}admin/public/admin/dist/img/logo.png" style="-ms-interpolation-mode: bicubic; height: auto; width: 105px;" title="{{ $site_name }}"/>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td style="height:20px;">&nbsp;</td>
                        </tr>
                        <tr>
                            <td>
                                <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0"
                                       style="max-width:670px;background:#fff; border-radius:3px; text-align:center;-webkit-box-shadow:0 6px 18px 0 rgba(0,0,0,.06);-moz-box-shadow:0 6px 18px 0 rgba(0,0,0,.06);box-shadow:0 6px 18px 0 rgba(0,0,0,.06);">
                                    <tr>
                                        <td style="height:40px;">&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td style="padding:0 35px;">
                                            <h1 style="color:#1e1e2d; font-weight:500; margin:0;font-size:32px;font-family:'Futura',sans-serif;">Hi, {{ $name }}</h1>
                                            <span style="display:inline-block; vertical-align:middle; margin:29px 0 26px; border-bottom:1px solid #cecece; width:100px;"></span>
                                            <p style="color:#455056; font-size:15px;line-height:24px; margin:0;">
                                                {{ $name }} want to activate shop and go live, please verify and activate {{ $name }}'s shop from admin panel.
                                            </p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="height:40px;">&nbsp;</td>
                                    </tr>
                                </table>
                            </td>
                        <tr>
                            <td style="height:20px;">&nbsp;</td>
                        </tr>
                        <tr>
                            <td style="text-align:center;">
                                <p style="font-size:14px; color:#393939; line-height:18px; margin:0 0 0;">?2021 <strong>BAZAR</strong>, Inc.</p>
                            </td>
                        </tr>
                        <tr>
                            <td style="height:80px;">&nbsp;</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <!--/100% body table-->
    </body>

</html>