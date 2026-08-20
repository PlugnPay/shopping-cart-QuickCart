# Quick.Cart v6.5.x — PlugnPay Payment Module

Package for Quick.Cart Shopping Cart **v6.5** (free edition, OpenSolution). Legacy **Smart Screens** hosted checkout: a modified `order-step-3.php` template that posts the saved order to `pay.cgi`.

Install by editing the publisher settings in the template, then uploading it into `templates/default/`.

## Choose a module

| | Smart Screens |
|---|---|
| Download | [quickcart_6.5_ss_module.zip](./quickcart_6.5_ss_module.zip) |
| Source | [src/ss/](./src/ss/) |
| Checkout | Order saved in Quick.Cart → redirect / post → `pay.cgi` hosted form |
| Card data on your server | No |
| PCI scope | Lower |
| Storefront SSL | Not required (card data is collected on PlugnPay) |
| Status | Legacy for Quick.Cart 6.5 |

## Smart Screens (legacy)

- Source: [src/ss/](./src/ss/)
- Download: [quickcart_6.5_ss_module.zip](./quickcart_6.5_ss_module.zip)
- Quick install: [INSTALL.txt](./INSTALL.txt)
- Vendor notes: [src/ss/readme_install.txt](./src/ss/readme_install.txt)

Built from the **2014-09-16** release of Quick.Cart v6.5 (free edition). Not tested on v6.4 or below.

The default checkout still runs in Quick.Cart. After the order is stored (and the optional order email is sent), the customer is offered hosted payment on PlugnPay. PlugnPay shows an itemized receipt. Capture / void / refund are done in PlugnPay Merchant Admin.

### Requirements

- Quick.Cart **v6.5** (free edition)
- An active PlugnPay merchant account for each card type (and ACH, if enabled) you list in the template

### Install steps

1. Download [quickcart_6.5_ss_module.zip](./quickcart_6.5_ss_module.zip) (or use `src/ss/`).
2. Open `order-step-3.php` and set `publisher-name` and `card-allowed`. Optionally uncomment the ACH/eCheck radios.
3. Upload the file into `templates/default/` on the store (overwrite the existing template).
4. Confirm `.php` permissions (often `chmod 755`).

## Development layout

```
QuickCart_v6.5.x/
  README.md
  INSTALL.txt
  quickcart_6.5_ss_module.zip
  src/
    ss/                       # → templates/default/order-step-3.php
      order-step-3.php
      readme_install.txt
```
