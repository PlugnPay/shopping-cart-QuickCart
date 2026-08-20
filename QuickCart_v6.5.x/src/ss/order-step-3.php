<?php 
// More about design modifications - www.opensolution.org/Quick.Cart/docs/?id=en-design

// This modified template is for Quick.Cart v6.5, to support PlugnPay Technologies Inc.
// - Down-and-dirty implimentation for PlugnPay's Smart Screens payment method.
// - Supports both Credit Card & ACH/eCheck payment options [See readme file]
// Last Updated: 10/16/14

if( !defined( 'CUSTOMER_PAGE' ) )
  exit;

$bOrderPage = true;

require_once DIR_SKIN.'_header.php'; // include design of header
?>
<div id="page">
<?php
if( isset( $aData['sName'] ) ){ // displaying pages and subpages content
  echo '<h1>'.$aData['sName'].'</h1>'; // displaying page name

  // display order form
  if( $oOrder->checkEmptyBasket( ) === false && isset( $_POST['sOrderSend'] ) && $oOrder->checkFields( $_POST ) === true ){
    // save and print order
    $iOrder = $oOrder->addOrder( $_POST );

    if( !empty( $config['orders_email'] ) && checkEmail( $config['orders_email'] ) ){
      $oOrder->sendEmailWithOrderDetails( $iOrder );
    }

    $aOrder = $oOrder->throwOrder( $iOrder );
    $sOrderProducts = $oOrder->listProducts( $iOrder );
    ?>
    <div id="orderPrint">
      <?php
        if( isset( $aData['sDescriptionFull'] ) )
          echo '<div class="content" id="pageDescription">'.$aData['sDescriptionFull'].'</div>'; // full description

        if( isset( $aData['sPages'] ) )
          echo '<div class="pages">'.$lang['Pages'].': <ul>'.$aData['sPages'].'</ul></div>'; // full description pagination
      ?>
      <div class="legend"><?php echo $lang['Your_personal_data']; ?></div>
      <dl>
        <dt class="orderId">ID:</dt><dd class="orderId"><?php echo $aOrder['iOrder']; ?></dd>
        <dt class="firstAndLastName"><?php echo $lang['First_and_last_name']; ?>:</dt><dd class="firstAndLastName"><?php echo $aOrder['sFirstName'].' '.$aOrder['sLastName']; ?></dd>
        <dt class="company"><?php echo $lang['Company']; ?>:</dt><dd class="company"><?php if( isset( $aOrder['sCompanyName'] ) ) echo $aOrder['sCompanyName']; ?></dd>
        <dt class="street"><?php echo $lang['Street']; ?>:</dt><dd class="street"><?php echo $aOrder['sStreet']; ?></dd>
        <dt class="zipCode"><?php echo $lang['Zip_code']; ?>:</dt><dd class="zipCode"><?php echo $aOrder['sZipCode']; ?></dd>
        <dt class="city"><?php echo $lang['City']; ?>:</dt><dd class="city"><?php echo $aOrder['sCity']; ?></dd>
        <dt class="phone"><?php echo $lang['Telephone']; ?>:</dt><dd class="phone"><?php echo $aOrder['sPhone']; ?></dd>
        <dt class="email"><?php echo $lang['Email']; ?>:</dt><dd class="email"><?php echo $aOrder['sEmail']; ?></dd>
        <dt class="orderDate"><?php echo $lang['Date']; ?>:</dt><dd class="orderDate"><?php echo $aOrder['sDate']; ?></dd>
        <dt class="orderIP">IP:</dt><dd class="orderIP"><?php echo $aOrder['sIp']; ?></dd>
        <dt class="orderComment"><?php echo $lang['Comment']; ?>:</dt><dd class="orderComment"><?php if( isset( $aOrder['sComment'] ) ) echo str_replace( '|n|', '<br />', $aOrder['sComment'] ); ?></dd>
      </dl>
      <div class="legend"><?php echo $lang['Products']; ?></div>
      <div id="orderedProducts">
        <table cellspacing="0">
          <thead>
            <tr>
              <td class="name"><?php echo $lang['Name']; ?></td>
              <td class="price"><em><?php echo $lang['Price']; ?></em><span>[<?php echo $config['currency_symbol']; ?>]</span></td>
              <td class="quantity"><?php echo $lang['Quantity']; ?></td>
              <td class="summary"><em><?php echo $lang['Summary']; ?></em><span>[<?php echo $config['currency_symbol']; ?>]</span></td>
            </tr>
          </thead>
          <tfoot>
            <?php if( isset( $aOrder['iShipping'] ) ){ ?>
              <tr class="summaryProducts">
                <th colspan="3"><?php echo $lang['Summary']; ?></th>
                <td><?php echo $oOrder->aOrders[$iOrder]['sProductsSummary']; ?></td>
              </tr>
              <tr class="summaryShippingPayment">
                <th colspan="3"><?php echo $lang['Shipping_and_payment']; ?>: <strong><?php echo $aOrder['mShipping']; ?>, <?php echo $aOrder['mPayment']; ?></strong></th>
                <td id="shippingCost"><?php echo $oOrder->aOrders[$iOrder]['sPaymentShippingPrice']; ?></td>
              </tr>
            <?php } ?>
            <tr class="summaryOrder">
              <th colspan="3"><?php echo $lang['Summary_cost']; ?></th>
              <td id="orderSummary"><?php echo $oOrder->aOrders[$iOrder]['sOrderSummary']; ?></td>
            </tr>
          </tfoot>
          <tbody>
            <?php echo $sOrderProducts; // displaying products in basket ?>
          </tbody>
        </table>
      </div>

<!-- BEGIN PLUGNPAY -->
<div id="SmartScreens">
<form method="post" action="https://pay1.plugnpay.com/payment/pay.cgi">
<!-- start editting PnP settings here -->
<input type="hidden" name="publisher-name" value="[MERCHANT]">
<input type="hidden" name="card-allowed" value="visa,mastercard">
<!-- stop editing PnP setting here -->
<input type="hidden" name="checktype" value="WEB">
<input type="hidden" name="showcompany" value="yes">
<input type="hidden" name="shipinfo" value="0">
<input type="hidden" name="easycart" value="1">
<input type="hidden" name="order-id" value="<?php echo $aOrder['iOrder']; ?>">
<input type="hidden" name="card-name" value="<?php echo $aOrder['sFirstName'].' '.$aOrder['sLastName']; ?>">
<input type="hidden" name="card-company" value="<?php if( isset( $aOrder['sCompanyName'] ) ) echo $aOrder['sCompanyName']; ?>">
<input type="hidden" name="card-address1" value="<?php echo $aOrder['sStreet']; ?>">
<input type="hidden" name="card-city" value="<?php echo $aOrder['sCity']; ?>">
<input type="hidden" name="card-state" value="<?php echo $aOrder['sState']; ?>">
<input type="hidden" name="card-zip" value="<?php echo $aOrder['sZipCode']; ?>">
<input type="hidden" name="card-country" value="<?php echo $aOrder['sCountry']; ?>">
<input type="hidden" name="phone" value="<?php echo $aOrder['sPhone']; ?>">
<input type="hidden" name="email" value="<?php echo $aOrder['sEmail']; ?>">
<input type="hidden" name="currency" value="<?php echo $config['currency_symbol']; ?>">
<input type="hidden" name="subtotal" value="<?php echo displayPrice( $_SESSION['fOrderSummary'.LANGUAGE] ); ?>">
<input type="hidden" name="shipping" value="<?php echo $oOrder->aOrders[$iOrder]['sPaymentShippingPrice']; ?>">
<input type="hidden" name="card-amount" value="<?php echo $oOrder->aOrders[$iOrder]['sOrderSummary']; ?>">
<input type="hidden" name="receipt_type" value="itemized">
<input type="hidden" name="customname1" value="orderDate">
<input type="hidden" name="customvalue1" value="<?php echo $aOrder['sDate']; ?>">
<input type="hidden" name="customname2" value="orderIP">
<input type="hidden" name="customvalue2" value="<?php echo $aOrder['sIp']; ?>">
<input type="hidden" name="customname3" value="orderComment">
<input type="hidden" name="customvalue3" value="<?php if( isset( $aOrder['sComment'] ) ) echo str_replace( '|n|', '<br />', $aOrder['sComment'] ); ?>">

<?php
  // list the cart's contents
  $oOrder->generateProducts( $iOrder );
  $i = 1;
  foreach( $oOrder->aProducts as $aData ){
    $aData['sPrice'] = displayPrice( normalizePrice( $aData['fPrice'] ) );
    echo "<input type=\"hidden\" name=\"item".$i ."\" value=\"item".$i."\">\n";
    echo "<input type=\"hidden\" name=\"description".$i ."\" value=\"".$aData['sName']."\">\n";
    echo "<input type=\"hidden\" name=\"cost".$i ."\" value=\"".$aData['sPrice']."\">\n";
    echo "<input type=\"hidden\" name=\"quantity".$i ."\" value=\"".$aData['iQuantity']."\">\n";
    $i++;
  }
?>

<div class="legend">Complete Online Payment</div>
<div id="orderedProducts">
  <table cellspacing="0">
    <thead>
      <tr>
        <td class="name" colspan=4><span><b>A payment is required to finalize the order. &nbsp;Please use the below button to make the online payment.</b></span></td>
      </tr>
    </thead>
    <tfoot>
<!-- Uncomment this section to offer ACH/eCheck payment option [credit card payment is assumed otherwise]
      <tr class="summaryOrder">
        <th colspan="3" align="right" width="60%">Payment Method:</th>
        <td align="left" width="40%"><b><input type="radio" name="paymethod" value="credit" checked="checked"> Credit Card &nbsp; <input type="radio" value="onlinecheck"> ACH/eCheck</b></td>
      </tr>
-->
      <tr id="nextStep">
        <td colspan="4" class="nextStep"><input type="submit" value="<?php echo $lang['Send_order']; ?> &raquo;" class="submit" /></td>
      </tr>
    </tfoot>
  </table>
</div>

</form>
</div>
<!--END PLUGNPAY -->

      <script type="text/javascript">
      //AddOnload( delSavedUserData );
      </script>
    </div>
    <?php
  }
  else{
    echo '<div class="message" id="error"><h2>'.$lang['cf_no_word'].'<br /><a href="javascript:history.back();">&laquo; '.$lang['back'].'</a></h2></div>';
  }
}
else{
  echo '<div class="message" id="error"><h2>'.$lang['Data_not_found'].'</h2></div>'; // displaying 404 error
}
?>
</div>
<?php
require_once DIR_SKIN.'_footer.php'; // include design of footer
?>
