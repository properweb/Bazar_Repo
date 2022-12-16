<!doctype html>

<html lang="en-US">
    <head>
        <meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
        <title>Order Invoice</title>
        <meta name="description" content="Order Invoice">
    </head>

    <body marginheight="0" topmargin="0" marginwidth="0" style="margin: 0px; background-color: #f2f3f8;" leftmargin="0">
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
                                       style="max-width:670px;background:#fff; border-radius:3px; text-align:center;-webkit-box-shadow:0 6px 18px 0 rgba(0,0,0,.06);-moz-box-shadow:0 6px 18px 0 rgba(0,0,0,.06);box-shadow:0 6px 18px 0 rgba(0,0,0,.06);padding:15px;">
                                    <tr>
                                        <td align="center" valign="middle" style="padding: 0;"><table width="100%" border="0" cellspacing="0" cellpadding="0">
                                                <tr>
<!--                                                    <td style="color: rgba(0, 0, 0, 0.5); font-family: Arial, Helvetica, sans-serif; font-size: 13px; font-weight: normal; padding: 0 10px 8px 0;">Billing Address</td>-->
                                                    <td style="color: rgba(0, 0, 0, 0.5); font-family: Arial, Helvetica, sans-serif; font-size: 13px; font-weight: normal; padding: 0 10px 8px 10px;">Shipping Address</td>
                                                    <td style="color: rgba(0, 0, 0, 0.5); font-family: Arial, Helvetica, sans-serif; font-size: 13px; font-weight: normal; padding: 0 0 8px 10px;">Brand details</td>
                                                    <td style="color: rgba(0, 0, 0, 0.5); font-family: Arial, Helvetica, sans-serif; font-size: 13px; font-weight: normal; padding: 0 0 8px 10px;">Order details</td>
                                                </tr>
                                                <tr>
<!--                                                    <td style="color: #000; font-family: Arial, Helvetica, sans-serif; font-size: 14px; font-weight: normal; line-height: 28px; padding: 0 10px 20px 0;" valign="top"><strong>Little Ivy Rose &amp; Co</strong><br />
                                                        Natasha Eills<br />
                                                        219 Melton High Street<br />
                                                        Rotherham, S63 RQ<br />
                                                        the U.K.<br />
                                                        Tel: +44 7949 712393</td>-->
                                                    <td style="color: #000; font-family: Arial, Helvetica, sans-serif; font-size: 14px; font-weight: normal; line-height: 28px; padding: 0 10px 20px 10px;" valign="top"><strong>{{$sstore_name}}</strong><br />
                                                        {{$sname}}<br />
                                                        {{$saddress1}}<br />
                                                        {{$saddress2}}<br />
                                                        {{ $stown }}, {{ $spost_code }}<br />
                                                        {{$sstate}},{{ $scountry }}.<br />
                                                        Tel: +{{$sphone_code}} {{$sphone}}</td>
                                                    <td style="color: #000; font-family: Arial, Helvetica, sans-serif; font-size: 14px; font-weight: normal; line-height: 28px; padding: 0 0 20px 10px;" valign="top"><strong>{{$bname}}</strong><br />
                                                        {{$bemail}}<br />
                                                         Tel: +{{$bphone_code}} {{$bphone}}<br />
                                                    </td>
                                                    <td style="color: #000; font-family: Arial, Helvetica, sans-serif; font-size: 14px; font-weight: normal; line-height: 28px; padding: 0 0 20px 10px;" valign="top"><strong>#{{$order_no}}</strong><br />
                                                        <span style="color: rgba(0, 0, 0, 0.5); font-size: 13px;"><strong>Order Date</strong></span><br />
                                                        <span style="font-size: 13px;">{{$ordered_date}}</span><br />
                                                        <span style="color: rgba(0, 0, 0, 0.5); font-size: 13px;"><strong>Ship Date</strong></span><br />
                                                        <span style="font-size: 13px;">{{$shipping_date}}</span><br /></td>
                                                </tr>
                                            </table></td>
                                    </tr>
                                    <tr>
                                        <td valign="middle" style="padding: 0;"><table width="100%" border="0" cellspacing="0" cellpadding="0">
                                                <tr>
                                                    <th style="border-bottom: 1px solid rgba(0, 0, 0, 0.25); color: #000; font-family: Arial, Helvetica, sans-serif; font-size: 13px; font-weight: normal; padding: 0 10px 8px 0; text-align: left;">SKU</th>
                                                    <th style="border-bottom: 1px solid rgba(0, 0, 0, 0.25); color: #000; font-family: Arial, Helvetica, sans-serif; font-size: 13px; font-weight: normal; padding: 0 10px 8px 10px; text-align: left;">Item</th>
                                                    <th style="border-bottom: 1px solid rgba(0, 0, 0, 0.25); color: #000; font-family: Arial, Helvetica, sans-serif; font-size: 13px; font-weight: normal; padding: 0 10px 8px 10px; text-align: left;">Qty.</th>
                                                    <th style="border-bottom: 1px solid rgba(0, 0, 0, 0.25); color: #000; font-family: Arial, Helvetica, sans-serif; font-size: 13px; font-weight: normal; padding: 0 10px 8px 10px; text-align: left;">WSP</th>
                                                    <th style="border-bottom: 1px solid rgba(0, 0, 0, 0.25); color: #000; font-family: Arial, Helvetica, sans-serif; font-size: 13px; font-weight: normal; padding: 0 0 8px 10px;" align="right">Subtotal</th>
                                                </tr>
                                                @foreach($products as $prd)
                                                <tr>
                                                    <td style="border-bottom: 1px solid rgba(0, 0, 0, 0.25); color: #000; font-family: Arial, Helvetica, sans-serif; font-size: 13px; font-weight: normal; padding: 8px 10px 8px 0;" valign="top">{{$prd['sku']}}</td>
                                                    <td style="border-bottom: 1px solid rgba(0, 0, 0, 0.25); color: #000; font-family: Arial, Helvetica, sans-serif; font-size: 13px; font-weight: normal; padding: 8px 10px 8px 10px;" valign="top">{{$prd['name']}}<br />
                                                        <span style="color: rgba(0, 0, 0, 0.5); display: block; font-size: 12px; font-weight: bold;">{{$prd['desc']}}</span></td>
                                                    <td style="border-bottom: 1px solid rgba(0, 0, 0, 0.25); color: #000; font-family: Arial, Helvetica, sans-serif; font-size: 13px; font-weight: normal; padding: 8px 10px 8px 10px;" valign="top">{{$prd['qty']}}</td>
                                                    <td style="border-bottom: 1px solid rgba(0, 0, 0, 0.25); color: #000; font-family: Arial, Helvetica, sans-serif; font-size: 13px; font-weight: normal; padding: 8px 10px 8px 10px; white-space: nowrap;" valign="top">{{$prd['price']}}</td>
                                                    <td style="border-bottom: 1px solid rgba(0, 0, 0, 0.25); color: #000; font-family: Arial, Helvetica, sans-serif; font-size: 13px; font-weight: normal; padding: 8px 0 8px 10px;" align="right" valign="top">{{$prd['subtotal']}}</td>
                                                </tr>
                                                @endforeach
                                                <tr>
                                                    <td colspan="2" valign="top" style="border-bottom: 1px solid rgba(0, 0, 0, 0.25); padding: 10px 10px 10px 0;"><h4 style="color: #000; font-family: Arial, Helvetica, sans-serif; font-size: 13px; font-weight: bold; margin: 0 0 0 0;">Item Subtotals</h4>
                                                        <p style="color: rgba(0, 0, 0, 0.5); font-family: Arial, Helvetica, sans-serif; font-size: 12px; font-weight: bold; margin: 0 0 0 0;">Pricing and subtotals do not reflect promotions, credits, or taxes</p></td>
                                                    <td style="border-bottom: 1px solid rgba(0, 0, 0, 0.25); color: #000; font-family: Arial, Helvetica, sans-serif; font-size: 13px; font-weight: normal; padding: 10px 10px 10px 10px;" valign="top"><strong>{{$total_qty}}</strong></td>
                                                    <td style="border-bottom: 1px solid rgba(0, 0, 0, 0.25); color: #000; font-family: Arial, Helvetica, sans-serif; font-size: 13px; font-weight: normal; padding: 10px 10px 10px 10px;" valign="top">&nbsp;</td>
                                                    <td style="border-bottom: 1px solid rgba(0, 0, 0, 0.25); color: #000; font-family: Arial, Helvetica, sans-serif; font-size: 13px; font-weight: normal; padding: 10px 0 10px 10px;" align="right" valign="top"><strong>{{$total_price}}</strong></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2" valign="top" style="padding: 10px 10px 0 0;"><h4 style="color: #000; font-family: Arial, Helvetica, sans-serif; font-size: 13px; font-weight: bold; margin: 0 0 0 0;">PAYMENT METHOD</h4></td>
                                                    <td colspan="3" style="color: #000; font-family: Arial, Helvetica, sans-serif; font-size: 13px; font-weight: normal; padding: 10px 0 0 10px;" align="right" valign="top">{{$payment_method}}</td>
                                                </tr>
                                            </table></td>
                                    </tr>
                                    <!--<tr>
                                          <td style="padding: 45px 0 0 0;">
                                          <p style="color: #000; font-family: Arial, Helvetica, sans-serif; font-size: 13px; font-weight: normal; margin: 0 0 30px 0;"><strong>Damage or missing Items:</strong> Damage or missing Items must be reported within 14 daysof receiving your shipment. To report damage or missing Items. go to your order on bazar.com and select Report Damage/Missing Items.</p>
                                          <p style="color: #000; font-family: Arial, Helvetica, sans-serif; font-size: 13px; font-weight: normal; margin: 0 0 30px 0;"><strong>Returns:</strong> To be eligible for returns. each Item must be (1) unopened &amp; unused (2) in origle packaging (3) free of any price tags or stickers (4) In your first order with the brand. To initiate a return, go to your on bazar.com and select Return Item. Some exclusions apply to our return policy, Please refer to our Help Center for more Information about returns or reporting missing or damaged Items at www.zar.com/support</p>
                                      </td>
                                    </tr> -->
                                </table>
                            </td>
                        <tr>
                            <td style="height:20px;">&nbsp;</td>
                        </tr>
                        <tr>
                            <td style="text-align:center;">
                                <p style="font-size:14px; color:#393939; line-height:18px; margin:0 0 0;">©2021 <strong>BAZAR</strong>, Inc.</p>
                            </td>
                        </tr>
                        <tr>
                            <td style="height:80px;">&nbsp;</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

    </body>
</html>