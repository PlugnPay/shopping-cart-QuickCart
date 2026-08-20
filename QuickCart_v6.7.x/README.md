# Quick.Cart v6.7.x — PlugnPay Smart Screens v2

Payment module for Quick.Cart Shopping Cart **v6.7.x**. Hosted, authorization-only checkout at `https://pay1.plugnpay.com/pay/`. Quick.Cart does **not** collect sensitive payment data.

## Module

| | Smart Screens v2 |
|---|---|
| Folder | [`src/PlugnPaySs2/`](./src/PlugnPaySs2/) |
| Download | [quickcart_6.7_ss2_module.zip](./quickcart_6.7_ss2_module.zip) |
| Checkout | Redirect → `https://pay1.plugnpay.com/pay/` |
| Card data on your server | No |
| PCI scope | Lower |
| Authorization | Authorization-only (`pb_post_auth=no`) |
| Admin Capture / Void / Refund | No (use PlugnPay Admin) |
| Status | Available for Quick.Cart 6.7 |

## Smart Screens v2 (hosted)

Redirects customers to PlugnPay hosted Smart Screens. Successful auths leave the order pending until settled in PlugnPay Admin.

- Source: [src/PlugnPaySs2/](./src/PlugnPaySs2/)
- Download: [quickcart_6.7_ss2_module.zip](./quickcart_6.7_ss2_module.zip)
- Quick install: [INSTALL_SS2.txt](./INSTALL_SS2.txt)
- Full docs: [src/PlugnPaySs2/README.md](./src/PlugnPaySs2/README.md)

The legacy 6.5 module uses Smart Screens v1 (`pay.cgi`) as a template overlay. Do not mix that file with a 6.7 package.

### Requirements

- Quick.Cart **6.7.x**
- PHP **5.2+**
- Storefront HTTPS (strongly recommended)

## Development layout

```
QuickCart_v6.7.x/
  README.md
  INSTALL_SS2.txt
  quickcart_6.7_ss2_module.zip
  src/
    PlugnPaySs2/
      plugins/plugnpay_ss2/
      templates/default/
  tests/
```

For the existing 6.5 Smart Screens overlay, see [../QuickCart_v6.5.x/](../QuickCart_v6.5.x/).
