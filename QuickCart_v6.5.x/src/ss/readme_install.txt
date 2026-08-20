=================================================
PlugnPay Smart Screens Method
Payment Module for Quick.Cart v6.5
=================================================

***** IMPORTANT NOTES *****
This module is being provided "AS IS".  Limited technical support assistance will
be given to help diagnose/address problems with this module.  The amount of support
provided is up to PlugnPay's staff.

If you experience a problem with this module, first seek assistance through this
module's readme file, then check with the community at 'www.opensolution.org'.
If you are still unable to resolve the issue, contact us via PlugnPay's Online Helpdesk.

This module is used without having your own server's SSL abilities. We will ask the
customer for all of their credit card info on our SSL secured billing page on our
secured servers.  The authorization will be done via PlugnPay's Smart Screens payment
method.  The module is intended to itemize the order in the PlugnPay system using
available information from the cart.

If you want to change the behavior of this module, please feel free to make changes
to the files yourself.  However, customized payment modules will not be provided
support assistance.
***************************

This is a very down-and-dirty payment solution designed for Quick.Cart v6.5 (free edition)
It has not been tested with QuickCart v6.4 or below versions, so use there with caution.

This process allows the customer to go through the default shopping cart checkout process.
However instead of just storing the contact info & displaying a basic thank you response;
this modification offers the customer the ability to pay their order online via our
Smart Screens payment method.  At the end of the order, PlugnPay will display an
online receipt for the customer.


Installation:

1. Open the 'order-step-3.php' file in a text editor, such as in Windows Notepad.

2. At line # 92, replace [MERCHANT] with your PlugnPay username.

  i.e. <input type="hidden" name="publisher-name" value="pnpdemo2">

3. At line # 93, add any additional card types you're allowed to accept.
   (Refer to PlugnPay's Integration Specifications document for details)

  i.e. <input type="hidden" name="card-allowed" value="visa,mastercard,amex,discover">

4. If you're allowed to accept ACH/eCheck payments, uncomment the HTML code between
   line #s 144 & 149, to offer the ACH/eCheck payment option to your customers.

* IMPORTANT NOTE: Do not include/activate any payment types you do not have an account for.
If you accept orders for a payment type you don't have an account with, you will not be able
to settle the transaction. Be sure you can accept the payment type before adding it to your
list of allowed option.  If unsure, contact your merchant account provider.

5. Save the changes to the 'order-step-3.php' file.  No other adjustments should be necessary.

6. Upload the modified 'order-step-3.php' file into the 'templates/default' folder of your shopping cart.

7. You're done...


Troubleshooting:

Check to be sure you actually uploaded the files into the correct folders;
ensuring you overwrite the existing file, if one exists.

Check the uploaded file's permissions:
-- .php files may need to be chmod 755
   (read/write/execute by owner, read/execute by all others)

Make sure you have an active merchant account for a given card type, prior to accepting
online payments for it.  This is very important for Amex & Discover cards; as well as
for ACH/eCheck acceptance. 

-----------------------------------------------------------------------------
History:

11/22/14
- created basic PlugnPay Smart Screens payment module.
- built from the "2014-09-16" release of Quick.Cart v6.5 (free eduition)
- added support for basic credit cards & ACH/eCheck payments

