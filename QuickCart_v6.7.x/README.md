# Quick.Cart v6.7.x — PlugnPay Payment Modules

Payment modules for Quick.Cart Shopping Cart **v6.7.x**. Smart Screens v2 is available; Remote API remains planned.

## Choose a module

| | Remote API | Smart Screens v2 |
|---|---|---|
| Folder | `src/PlugnPayApi/` | [`src/PlugnPaySs2/`](./src/PlugnPaySs2/) |
| Download | *(to be added)* | [quickcart_6.7_ss2_module.zip](./quickcart_6.7_ss2_module.zip) |
| Checkout | Onsite card fields → `pnpremote.cgi` | Redirect → `https://pay1.plugnpay.com/pay/` |
| Card data on your server | Yes | No |
| PCI scope | Higher | Lower |
| Authorization | `authonly` or `authpostauth` | Authorization-only (`pb_post_auth=no`) |
| Admin Capture / Void / Refund | No (use PlugnPay Admin) | No (use PlugnPay Admin) |
| Status | Not packaged yet | Available for Quick.Cart 6.7 |

You may install both when they exist; enable only the method(s) you need.

## Remote API (onsite) — planned

Collects card data on the storefront and posts from the server to PlugnPay Remote API. Capture / void / refund are done in PlugnPay Merchant Admin.

- Source: `src/PlugnPayApi/` *(to be added)*
- Quick install: `INSTALL.txt` *(to be added)*

## Smart Screens v2 (hosted)

Redirects customers to PlugnPay hosted Smart Screens. The cart does **not** collect sensitive payment data. Successful auths should leave the order pending until settled in PlugnPay Admin.

- Source: [src/PlugnPaySs2/](./src/PlugnPaySs2/)
- Download: [quickcart_6.7_ss2_module.zip](./quickcart_6.7_ss2_module.zip)
- Quick install: [INSTALL_SS2.txt](./INSTALL_SS2.txt)
- Full docs: [src/PlugnPaySs2/README.md](./src/PlugnPaySs2/README.md)

The legacy 6.5 module uses Smart Screens v1 (`pay.cgi`) as a template overlay. Do not mix that file with a 6.7 package.

### Requirements

- Quick.Cart **6.7.x**
- PHP **5.2+** for Smart Screens v2
- Storefront HTTPS (strongly recommended for SS2)
- PlugnPay outbound Response Verification Hash enabled and configured

## Development layout

```
QuickCart_v6.7.x/
  README.md
  INSTALL.txt                 # Remote API (to be added)
  INSTALL_SS2.txt             # Smart Screens v2
  quickcart_6.7_ss2_module.zip
  src/
    PlugnPayApi/              # to be added
    PlugnPaySs2/
      plugins/plugnpay_ss2/
      templates/default/
  tests/
```

For the existing 6.5 Smart Screens overlay, see [../QuickCart_v6.5.x/](../QuickCart_v6.5.x/).
