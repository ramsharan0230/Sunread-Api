<table class="order-details"
       style="border-collapse: collapse; border-spacing: 0; color: #000; padding: 0; text-align: left; vertical-align: top; width: 100%;">
    <tbody style="color: #000;">
    <?php foreach($order_items as $item) { ?>
    <tr style="color: #000; padding: 0; text-align: left; vertical-align: top;">
        <td style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0; border-collapse: collapse !important; color: #000; font-family: Helvetica, Arial, sans-serif; font-size: 14px; font-weight: normal; hyphens: auto; line-height: 1.3; margin: 0; padding: 10px 5px; padding-left: 20px; text-align: left; vertical-align: top; word-wrap: break-word;">
            <img src="{{$item->image_url}}"
                 style="-ms-interpolation-mode: bicubic; clear: both; color: #000; display: block; max-width: 100%; min-width: 60px; outline: none; text-decoration: none; width: auto;">
        </td>
        <td style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0; border-collapse: collapse !important; color: #000; font-family: Helvetica, Arial, sans-serif; font-size: 14px; font-weight: normal; hyphens: auto; line-height: 1.3; margin: 0; padding: 10px 5px; text-align: left; vertical-align: top; word-wrap: break-word;">
            <table class="product-info"
                   style="border-collapse: collapse; border-spacing: 0; color: #000; padding: 0; text-align: left; vertical-align: top; width: 100%;">
                <tbody style="color: #000;">
                <tr style="color: #000; padding: 0; text-align: left; vertical-align: top;">
                    <td class="product-name"
                        style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0; border-collapse: collapse !important; color: #000; font-family: Helvetica, Arial, sans-serif; font-size: 14px; font-weight: bold; hyphens: auto; line-height: 1.3; margin: 0; padding: 10px 5px; padding-left: 5px; padding-top: 40px; text-align: left; vertical-align: top; word-wrap: break-word;">
                        <span style="color: #000;">{{$item->name}}</span>
                    </td>
                    <td class="product-attributes-wrapper"
                        style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0; border-collapse: collapse !important; color: #000; font-family: Helvetica, Arial, sans-serif; font-size: 14px; font-weight: normal; hyphens: auto; line-height: 1.3; margin: 0; padding: 10px 5px; padding-right: 0; padding-top: 40px; text-align: left; vertical-align: top; word-wrap: break-word;">
                        <table class="product-attributes"
                               style="border-collapse: collapse; border-spacing: 0; color: #000; padding: 0; text-align: left; vertical-align: top; width: 100%;">
                            <tbody style="color: #000;">

                                <tr style="color: #000; padding: 0; text-align: left; vertical-align: top;">
                                    <td style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0; border-collapse: collapse !important; color: #AEAEAE; font-family: Helvetica, Arial, sans-serif; font-size: 12px; font-weight: normal; hyphens: auto; line-height: 1.3; margin: 0; padding: 2px 5px; padding-left: 5px; text-align: left; vertical-align: middle; word-wrap: break-word;">
                                        Color
                                    </td>
                                    <td style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0; border-collapse: collapse !important; color: #000; font-family: Helvetica, Arial, sans-serif; font-size: 14px; font-weight: bold; hyphens: auto; line-height: 1.3; margin: 0; padding: 2px 20px 2px 5px; padding-right: 0; text-align: right; vertical-align: middle; white-space: nowrap; word-wrap: break-word;">
                                        {{$item->color}}
                                    </td>
                                </tr>
                                <tr style="color: #000; padding: 0; text-align: left; vertical-align: top;">
                                    <td style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0; border-collapse: collapse !important; color: #AEAEAE; font-family: Helvetica, Arial, sans-serif; font-size: 12px; font-weight: normal; hyphens: auto; line-height: 1.3; margin: 0; padding: 2px 5px; padding-left: 5px; text-align: left; vertical-align: middle; word-wrap: break-word;">
                                        Size
                                    </td>
                                    <td style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0; border-collapse: collapse !important; color: #000; font-family: Helvetica, Arial, sans-serif; font-size: 14px; font-weight: bold; hyphens: auto; line-height: 1.3; margin: 0; padding: 2px 20px 2px 5px; padding-right: 0; text-align: right; vertical-align: middle; white-space: nowrap; word-wrap: break-word;">
                                        {{$item->size}}
                                    </td>
                                </tr>

                            <tr class="price"
                                style="color: #000; padding: 0; text-align: left; vertical-align: top;">
                                <td style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0; border-collapse: collapse !important; color: #AEAEAE; font-family: Helvetica, Arial, sans-serif; font-size: 12px; font-weight: normal; hyphens: auto; line-height: 1.3; margin: 0; padding: 2px 5px; padding-left: 5px; padding-top: 30px; text-align: left; vertical-align: middle; word-wrap: break-word;">
                                    Price
                                </td>
                                <td style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0; border-collapse: collapse !important; color: #000; font-family: Helvetica, Arial, sans-serif; font-size: 14px; font-weight: normal; hyphens: auto; line-height: 1.3; margin: 0; padding: 2px 20px 2px 5px; padding-right: 0; padding-top: 30px; text-align: right; vertical-align: middle; white-space: nowrap; word-wrap: break-word;">
                                    {{$item->row_total}} {{$order->currency_code}}
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>
    <?php } ?>
    </tbody>
</table>
