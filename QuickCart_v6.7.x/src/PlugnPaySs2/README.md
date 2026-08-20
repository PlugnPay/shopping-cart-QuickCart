# PlugnPay Smart Screens v2 for Quick.Cart 6.7

Hosted, authorization-only checkout for Quick.Cart 6.7. Card data is collected by PlugnPay at `https://pay1.plugnpay.com/pay/`, not by the store.

## Install

1. Back up the Quick.Cart installation, especially the existing `templates/default/order-step-3.php`.
2. Copy the package's `plugins/` and `templates/` directories into the Quick.Cart root.
3. Edit `plugins/plugnpay_ss2/config.php`:
   - Set `plugnpay_ss2_gateway_account` to the PlugnPay gateway account.
   - Set `plugnpay_ss2_currency` to the three-letter account currency, such as `USD`.
   - Confirm `plugnpay_ss2_payment_id`. The stock English Quick.Cart database uses payment ID `3` for **On-line payment**.
   - Set `plugnpay_ss2_store_url` to the public HTTPS store URL. The module will not derive it from request headers.
4. In Quick.Cart admin, enable the matching payment method and associate it with the applicable shipping methods.
5. Place a test order using merchant-supplied test credentials.

See `../../INSTALL_SS2.txt` for the short installation guide.

## Behavior

- Quick.Cart saves the order before sending the customer to PlugnPay.
- Order email is delayed until PlugnPay returns a matching success response.
- `pb_post_auth=no` requests authorization only. The order remains **Pending** until it is settled in PlugnPay Merchant Admin.
- Declines display the gateway message and allow the customer to retry the hosted payment.
- The return checks gateway account, amount, currency, order ID, and a one-time token.
- Expected return values are also kept in `database/plugnpay_ss2.php` so validation still works when a browser withholds its session cookie on the cross-site return POST. Completed entries are removed and abandoned entries expire after seven days.
- Successful responses consume the token to prevent replay.
- The PlugnPay transaction ID and authorization code are written to `database/plugnpay_ss2_transactions.php` and included in the merchant's order email for reconciliation.

Because Quick.Cart 6.7 has no payment-plugin extension point, this package replaces `templates/default/order-step-3.php`. Reapply or review the overlay if that template has local customizations.

## Package layout

```text
plugins/plugnpay_ss2/
  config.php
  PnPSs2.php
  PnPSs2Protocol.php
templates/default/
  order-step-3.php
```

## Testing

From the repository's `QuickCart_v6.7.x/` directory:

```bash
php tests/PnPSs2ProtocolTest.php
```

The test suite has no external dependencies.

## Security notes

- Use HTTPS for both Quick.Cart and PlugnPay.
- Do not place card fields in Quick.Cart; the hosted page collects them.
- Do not log or store PAN, CVV, or other sensitive payment data.
- Keep `config.php` readable only by the web-server account where possible.
- Keep Quick.Cart's existing `database/.htaccess` protection in place. The module's return-state file contains no card data, but it must not be publicly downloadable.

Provided AS IS. Capture, void, and refund are performed in PlugnPay Merchant Admin.
