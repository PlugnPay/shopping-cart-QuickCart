# PlugnPay Smart Screens v2 for Quick.Cart 6.7

Hosted, authorization-only checkout for Quick.Cart 6.7. Card data is collected by PlugnPay at `https://pay1.plugnpay.com/pay/`, not by the store.

## Install

1. Back up the Quick.Cart installation, especially `templates/default/order-step-3.php`.
2. Copy the package's `plugins/` and `templates/` directories into the Quick.Cart root.
3. Edit `plugins/plugnpay_ss2/config.php`:
   - Set the PlugnPay gateway account and three-letter currency.
   - Confirm the Quick.Cart payment ID; the stock **On-line payment** method is ID `3`.
   - Set the public HTTPS store URL.
4. Enable and associate that payment method with the applicable shipping methods in Quick.Cart admin.
5. Place a test order with merchant-supplied test credentials.

## Behavior and security

- Orders are saved before hosted payment, but merchant order email is delayed until a matching success response.
- Transactions are authorization-only (`pb_post_auth=no`) and remain Pending until settlement in PlugnPay Merchant Admin.
- The return checks gateway account, amount, currency, order ID, and a one-time token.
- Return state is stored in `database/plugnpay_ss2.php`; authorization records are appended to `database/plugnpay_ss2_transactions.php`.
- Successful responses consume the token to prevent replay.
- No PAN or CVV is collected or stored by Quick.Cart.

This package replaces `templates/default/order-step-3.php`. Merge local template customizations before installation and keep Quick.Cart's `database/.htaccess` protection in place.

Provided AS IS. Capture, void, and refund are performed in PlugnPay Merchant Admin.
