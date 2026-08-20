<?php
// Quick.Cart v6.7 order completion with PlugnPay Smart Screens v2.
if( !defined( 'CUSTOMER_PAGE' ) )
  exit;

require_once DIR_PLUGINS.'plugnpay_ss2/config.php';
require_once DIR_PLUGINS.'plugnpay_ss2/PnPSs2.php';

$config['this_is_order_page'] = true;
$bGatewayReturn = PnPSs2Protocol::isGatewayResponse( $_POST );
$aPaymentResult = null;
$aHostedFields = null;
$bUsePlugnPay = false;
$bAuthorizationRecorded = null;

if( $bGatewayReturn ){
  $aPaymentResult = PnPSs2::validateResponse( $_POST );
  if( !empty( $aPaymentResult['valid'] ) ){
    $iOrder = $aPaymentResult['order_id'];
    $aOrder = $oOrder->throwOrder( $iOrder );
    if( isset( $aOrder ) ){
      if( !isset( $aOrder['iPayment'] ) || (int) $aOrder['iPayment'] !== (int) $config['plugnpay_ss2_payment_id'] ){
        $aPaymentResult = Array( 'valid' => false, 'message' => 'The saved order does not use PlugnPay.' );
        $aOrder = null;
      }
    }
    if( isset( $aOrder ) ){
      $sOrderProducts = $oOrder->listProducts( $iOrder );
      $bUsePlugnPay = true;
      if( !empty( $aPaymentResult['success'] ) ){
        $sAuthorizationNote = PnPSs2::authorizationNote( $_POST );
        $oOrder->aOrders[$iOrder]['sComment'] = isset( $oOrder->aOrders[$iOrder]['sComment'] ) && $oOrder->aOrders[$iOrder]['sComment'] !== ''
          ? $oOrder->aOrders[$iOrder]['sComment'].'|n|'.$sAuthorizationNote
          : $sAuthorizationNote;
        $bAuthorizationRecorded = PnPSs2::recordAuthorization( $iOrder, $_POST );
        if( !empty( $config['orders_email'] ) && checkEmail( $config['orders_email'] ) )
          $oOrder->sendEmailWithOrderDetails( $iOrder );
        PnPSs2::clearExpectedResponse( );
      }
      else{
        $aHostedFields = PnPSs2::buildHostedFields( $iOrder, $aOrder );
      }
    }
    elseif( empty( $aPaymentResult['message'] ) ){
      $aPaymentResult = Array( 'valid' => false, 'message' => 'The saved order could not be found.' );
    }
  }
}
elseif( isset( $aData['sName'] ) && $oOrder->checkEmptyBasket( ) === false && isset( $_POST['sOrderSend'] ) && $oOrder->checkFields( $_POST ) === true ){
  $iOrder = $oOrder->addOrder( $_POST );
  $aOrder = $oOrder->throwOrder( $iOrder );
  $sOrderProducts = $oOrder->listProducts( $iOrder );
  $bUsePlugnPay = isset( $aOrder['iPayment'] )
    && (int) $aOrder['iPayment'] === (int) $config['plugnpay_ss2_payment_id'];

  if( $bUsePlugnPay ){
    if( PnPSs2::isConfigured( ) )
      $aHostedFields = PnPSs2::buildHostedFields( $iOrder, $aOrder );
  }
  elseif( !empty( $config['orders_email'] ) && checkEmail( $config['orders_email'] ) ){
    $oOrder->sendEmailWithOrderDetails( $iOrder );
  }
}

require_once DIR_SKIN.'_header.php';
?>
<div id="page">
<?php
if( isset( $aData['sName'] ) ){
  echo '<h1>'.$aData['sName'].'</h1>';

  if( isset( $aOrder ) ){
    if( isset( $aPaymentResult ) && empty( $aPaymentResult['valid'] ) ){
      echo '<div class="message" id="error"><h2>'.PnPSs2::escape( $aPaymentResult['message'] ).'</h2></div>';
    }
    elseif( isset( $aPaymentResult ) && !empty( $aPaymentResult['success'] ) ){
      echo '<div class="message"><h2>Payment authorized successfully.</h2><p>Your order is pending settlement by the merchant.</p></div>';
      if( $bAuthorizationRecorded === false )
        echo '<div class="message" id="error"><p>The authorization reference could not be saved locally. Keep order ID '.(int) $iOrder.' and contact the merchant if needed.</p></div>';
    }
    elseif( isset( $aPaymentResult ) ){
      $sDeclineMessage = !empty( $aPaymentResult['message'] )
        ? $aPaymentResult['message']
        : 'Your payment could not be authorized. Please try again or contact the store.';
      echo '<div class="message" id="error"><h2>'.PnPSs2::escape( $sDeclineMessage ).'</h2></div>';
    }

    if( isset( $aData['sDescriptionFull'] ) && ( !$bUsePlugnPay || ( isset( $aPaymentResult['success'] ) && $aPaymentResult['success'] ) ) )
      echo '<div class="content" id="pageDescription">'.$aData['sDescriptionFull'].'</div>';

    if( isset( $aData['sPages'] ) )
      echo '<div class="pages">'.$lang['Pages'].': <ul>'.$aData['sPages'].'</ul></div>';
    ?>
    <div id="orderPrint">
      <div class="legend"><?php echo $lang['Your_personal_data']; ?></div>
      <dl>
        <dt class="orderId">ID:</dt><dd class="orderId"><?php echo (int) $aOrder['iOrder']; ?></dd>
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
            <?php echo $sOrderProducts; ?>
          </tbody>
        </table>
      </div>

      <?php if( $bUsePlugnPay && !PnPSs2::isConfigured( ) ){ ?>
        <div class="message" id="error"><h2>PlugnPay Smart Screens v2 is not configured. Please contact the store.</h2></div>
      <?php }
      elseif( $bUsePlugnPay && is_array( $aHostedFields ) ){ ?>
        <div class="legend">Complete secure payment</div>
        <form method="post" action="<?php echo PnPSs2Protocol::ENDPOINT; ?>" id="plugnpaySs2Form">
          <?php foreach( $aHostedFields as $sName => $sValue ){ ?>
            <input type="hidden" name="<?php echo PnPSs2::escape( $sName ); ?>" value="<?php echo PnPSs2::escape( $sValue ); ?>" />
          <?php } ?>
          <p>Your order has been saved. Continue to PlugnPay's secure hosted checkout to authorize payment.</p>
          <p><input type="submit" value="Pay securely with PlugnPay &raquo;" class="submit" /></p>
        </form>
      <?php }
      elseif( $bUsePlugnPay ){ ?>
        <div class="message" id="error"><h2>Payment could not be initialized. Confirm that the database directory is writable, then try again.</h2></div>
      <?php } ?>

      <?php if( !$bUsePlugnPay || ( isset( $aPaymentResult['success'] ) && $aPaymentResult['success'] ) ){ ?>
        <script type="text/javascript">
        AddOnload( delSavedUserData );
        </script>
      <?php } ?>
    </div>
    <?php
  }
  else{
    $sError = isset( $aPaymentResult['message'] ) ? $aPaymentResult['message'] : $lang['cf_no_word'];
    echo '<div class="message" id="error"><h2>'.PnPSs2::escape( $sError ).'<br /><a href="javascript:history.back();">&laquo; '.$lang['back'].'</a></h2></div>';
  }
}
else{
  echo '<div class="message" id="error"><h2>'.$lang['Data_not_found'].'</h2></div>';
}
?>
</div>
<?php
require_once DIR_SKIN.'_footer.php';
?>
