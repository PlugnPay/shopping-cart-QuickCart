# Shopping Cart - Quick.Cart Payment Modules

Easy to install payment modules for the [Quick.Cart](https://opensolution.org/Quick.Cart/) shopping cart (OpenSolution).
**Quick.Cart 6.5** keeps the legacy Smart Screens (`pay.cgi`) template overlay. **Quick.Cart 6.7** includes Smart Screens v2 hosted checkout.

## Downloads by Quick.Cart version

### Quick.Cart v6.7.x (current)

* **Smart Screens v2** — gateway hosted, authorization-only checkout
  - [Download](./QuickCart_v6.7.x/quickcart_6.7_ss2_module.zip)
  - Source: [./QuickCart_v6.7.x/src/PlugnPaySs2/](./QuickCart_v6.7.x/src/PlugnPaySs2/)
  - Docs: [package README](./QuickCart_v6.7.x/README.md) · [INSTALL_SS2.txt](./QuickCart_v6.7.x/INSTALL_SS2.txt) · [module README](./QuickCart_v6.7.x/src/PlugnPaySs2/README.md)

Package overview: [./QuickCart_v6.7.x/README.md](./QuickCart_v6.7.x/README.md)

### Quick.Cart v6.5.x (legacy Smart Screens)

* **Smart Screens** — hosted checkout via `pay.cgi` (template overlay)
  - [Download](./QuickCart_v6.5.x/quickcart_6.5_ss_module.zip)
  - Source: [./QuickCart_v6.5.x/src/ss/](./QuickCart_v6.5.x/src/ss/)
  - Docs: [package README](./QuickCart_v6.5.x/README.md) · [INSTALL.txt](./QuickCart_v6.5.x/INSTALL.txt) · [vendor notes](./QuickCart_v6.5.x/src/ss/readme_install.txt)

Package overview: [./QuickCart_v6.5.x/README.md](./QuickCart_v6.5.x/README.md)

## Installation

For complete instructions, open the README inside the zip (or the linked docs above).

### Quick.Cart 6.7.x

1. Download [quickcart_6.7_ss2_module.zip](./QuickCart_v6.7.x/quickcart_6.7_ss2_module.zip).
2. Back up `templates/default/order-step-3.php`.
3. Extract the package into the Quick.Cart root.
4. Configure `plugins/plugnpay_ss2/config.php`.

- Quick install: [QuickCart_v6.7.x/INSTALL_SS2.txt](./QuickCart_v6.7.x/INSTALL_SS2.txt)

### Quick.Cart 6.5.x — Smart Screens

1. Download [quickcart_6.5_ss_module.zip](./QuickCart_v6.5.x/quickcart_6.5_ss_module.zip).
2. Edit `order-step-3.php` (publisher name and allowed card types).
3. Upload it into `templates/default/` on the store, overwriting the existing template.

- Quick install: [QuickCart_v6.5.x/INSTALL.txt](./QuickCart_v6.5.x/INSTALL.txt)

## Usage

### Smart Screens (Quick.Cart 6.5.x legacy)

* Hosted billing at `https://pay1.plugnpay.com/payment/pay.cgi`.
* Quick.Cart does **not** collect sensitive payment data; the customer pays on PlugnPay after the order is saved.
* Built from the 2014-09-16 Quick.Cart v6.5 (free edition) template; not tested on 6.4 or earlier.
* Capture / void / refund are done in PlugnPay Merchant Admin.

### Smart Screens v2 (Quick.Cart 6.7.x)

* Hosted checkout at `https://pay1.plugnpay.com/pay/`.
* Quick.Cart does **not** collect sensitive payment data.
* Authorization-only (`pb_post_auth=no`); successful orders remain **Pending** for settlement in PlugnPay Merchant Admin.
* Return checks cover PlugnPay's response signature, amount, currency, gateway account, order ID, and a one-time token.

## Repository layout

```
shopping-cart-QuickCart/
  README.md
  .gitignore
  QuickCart_v6.7.x/           # current (6.7.x) — Smart Screens v2
    README.md
    INSTALL_SS2.txt
    quickcart_6.7_ss2_module.zip
    src/PlugnPaySs2/
    tests/
  QuickCart_v6.5.x/           # legacy (6.5.x) — Smart Screens template overlay
    README.md
    INSTALL.txt
    quickcart_6.5_ss_module.zip
    src/ss/
```

## Support

Provided AS IS. See [PlugnPay docs](https://docs.plugnpay.com/) and the module README for integration details.
