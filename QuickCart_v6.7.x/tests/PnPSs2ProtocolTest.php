<?php
$sTestDatabase = sys_get_temp_dir( ).'/quickcart-ss2-test-'.getmypid( ).'/';
mkdir( $sTestDatabase, 0700, true );
define( 'DIR_DATABASE', $sTestDatabase );

require_once dirname( __FILE__ ).'/../src/PlugnPaySs2/plugins/plugnpay_ss2/PnPSs2Protocol.php';
require_once dirname( __FILE__ ).'/../src/PlugnPaySs2/plugins/plugnpay_ss2/PnPSs2.php';

$iTests = 0;
$iFailures = 0;

function assertSameValue( $mExpected, $mActual, $sMessage ){
  global $iTests, $iFailures;
  $iTests++;
  if( $mExpected !== $mActual ){
    $iFailures++;
    fwrite( STDERR, "FAIL: ".$sMessage."\nExpected: ".var_export( $mExpected, true )."\nActual: ".var_export( $mActual, true )."\n" );
  }
}

function assertTrueValue( $bActual, $sMessage ){
  assertSameValue( true, $bActual, $sMessage );
}

$aFields = PnPSs2Protocol::buildHostedFields( Array(
  'gateway_account' => ' merchant ',
  'amount' => '12.5',
  'currency' => 'usd',
  'order_id' => 42,
  'token' => 'one-time-token',
  'success_url' => 'https://shop.example.test/order-details,18.html',
  'billing_name' => 'Test Customer'
) );

assertSameValue( PnPSs2Protocol::ENDPOINT, 'https://pay1.plugnpay.com/pay/', 'Uses the Smart Screens v2 endpoint' );
assertSameValue( 'merchant', $aFields['pt_gateway_account'], 'Trims the gateway account' );
assertSameValue( '12.50', $aFields['pt_transaction_amount'], 'Normalizes the amount' );
assertSameValue( 'USD', $aFields['pt_currency'], 'Normalizes the currency' );
assertSameValue( 'no', $aFields['pb_post_auth'], 'Requests authorization only' );
assertSameValue( 'QuickCart_SS2', $aFields['pt_client_identifier'], 'Identifies Quick.Cart' );
assertSameValue( '42', $aFields['pt_custom_value_2'], 'Includes the order ID' );
assertTrueValue( PnPSs2Protocol::amountsMatch( '10', '10.00' ), 'Equivalent amounts match' );
assertSameValue( false, PnPSs2Protocol::amountsMatch( '10.01', '10.00' ), 'Different amounts do not match' );

$aResponse = Array(
  'pi_response_status' => 'success',
  'pt_gateway_account' => 'merchant',
  'pt_transaction_amount' => '12.50',
  'pt_currency' => 'USD',
  'pt_order_id' => 'gateway-order-42',
  'pt_custom_name_1' => 'qctoken',
  'pt_custom_value_1' => 'one-time-token',
  'pt_custom_name_2' => 'qcorderid',
  'pt_custom_value_2' => '42'
);
assertSameValue( 'one-time-token', PnPSs2Protocol::extractCustomValue( $aResponse, 'qctoken' ), 'Extracts custom token' );
assertTrueValue( PnPSs2Protocol::isSuccessfulResponse( $aResponse ), 'Recognizes a successful response' );

$_SESSION = Array(
  'plugnpay_ss2' => Array(
    'order_id' => '42',
    'amount' => '12.50',
    'currency' => 'USD',
    'gateway_account' => 'merchant',
    'token' => 'one-time-token'
  )
);
$aResult = PnPSs2::validateResponse( $aResponse );
assertTrueValue( $aResult['valid'], 'Accepts a matching response' );
assertTrueValue( $aResult['success'], 'Returns the success state' );

$aTampered = $aResponse;
$aTampered['pt_transaction_amount'] = '99.00';
$aResult = PnPSs2::validateResponse( $aTampered );
assertSameValue( false, $aResult['valid'], 'Rejects a changed amount' );

$aTampered = $aResponse;
$aTampered['pt_custom_value_1'] = 'wrong-token';
$aResult = PnPSs2::validateResponse( $aTampered );
assertSameValue( false, $aResult['valid'], 'Rejects a changed token' );

$aTampered = $aResponse;
$aTampered['pt_currency'] = 'EUR';
$aResult = PnPSs2::validateResponse( $aTampered );
assertSameValue( false, $aResult['valid'], 'Rejects a changed currency' );

$aMixedCase = $aResponse;
$aMixedCase['pt_gateway_account'] = 'Merchant';
$aResult = PnPSs2::validateResponse( $aMixedCase );
assertTrueValue( $aResult['valid'], 'Accepts a gateway account that differs only in casing' );

$aDeclined = $aResponse;
$aDeclined['pi_response_status'] = 'badcard';
$aDeclined['pi_error_message'] = 'Card declined.';
$aResult = PnPSs2::validateResponse( $aDeclined );
assertTrueValue( $aResult['valid'], 'Accepts a decline response that matches the stored order' );
assertSameValue( false, $aResult['success'], 'Returns the decline state' );
assertSameValue( 'Card declined.', $aResult['message'], 'Returns the gateway error message' );

$GLOBALS['config']['plugnpay_ss2_gateway_account'] = 'merchant';
$GLOBALS['config']['plugnpay_ss2_currency'] = 'USD';
$sPersistentToken = PnPSs2::rememberOrder( 84, '20.00' );
$aPersistentResponse = Array(
  'pi_response_status' => 'success',
  'pt_gateway_account' => 'merchant',
  'pt_transaction_amount' => '20.00',
  'pt_currency_code' => 'USD',
  'pt_order_id' => 'gateway-order-84',
  'pt_custom_name_1' => 'qctoken',
  'pt_custom_value_1' => $sPersistentToken,
  'pt_custom_name_2' => 'qcorderid',
  'pt_custom_value_2' => '84'
);
$_SESSION['plugnpay_ss2'] = Array(
  'order_id' => '99',
  'amount' => '1.00',
  'currency' => 'USD',
  'gateway_account' => 'merchant',
  'token' => 'different-tab-token'
);
$aResult = PnPSs2::validateResponse( $aPersistentResponse );
assertTrueValue( $aResult['valid'], 'Uses order-specific persisted state when another tab overwrites the session' );
$aPersistentResponse['pt_authorization_code'] = 'ABC123';
assertTrueValue( PnPSs2::recordAuthorization( 84, $aPersistentResponse ), 'Records the authorization on the order' );
assertTrueValue( strpos( file_get_contents( $sTestDatabase.'plugnpay_ss2_transactions.php' ), 'gateway-order-84' ) !== false, 'Stores the gateway transaction ID' );
PnPSs2::clearExpectedResponse( );
unset( $_SESSION['plugnpay_ss2'] );
$aResult = PnPSs2::validateResponse( $aPersistentResponse );
assertSameValue( false, $aResult['valid'], 'Rejects replay after expected response is cleared' );

if( is_file( $sTestDatabase.'plugnpay_ss2.php' ) )
  unlink( $sTestDatabase.'plugnpay_ss2.php' );
if( is_file( $sTestDatabase.'plugnpay_ss2_transactions.php' ) )
  unlink( $sTestDatabase.'plugnpay_ss2_transactions.php' );
rmdir( $sTestDatabase );

if( $iFailures > 0 ){
  fwrite( STDERR, $iFailures.' of '.$iTests." tests failed.\n" );
  exit( 1 );
}

echo $iTests." tests passed.\n";
?>
