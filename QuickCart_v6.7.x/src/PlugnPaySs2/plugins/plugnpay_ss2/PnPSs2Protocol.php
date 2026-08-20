<?php
/**
 * PlugnPay Smart Screens v2 protocol helpers for Quick.Cart 6.7.
 *
 * This file intentionally uses PHP 5.2-compatible syntax because Quick.Cart
 * 6.7 supports PHP 5.2 and later.
 */
class PnPSs2Protocol
{
  const ENDPOINT = 'https://pay1.plugnpay.com/pay/';
  const CLIENT_IDENTIFIER = 'QuickCart_SS2';
  const CUSTOM_TOKEN = 'qctoken';
  const CUSTOM_ORDER_ID = 'qcorderid';

  /**
   * Builds the fields posted to Smart Screens v2.
   *
   * @param array $aInput
   * @return array
   */
  public static function buildHostedFields( $aInput ){
    $sOrderId = (string) $aInput['order_id'];
    $sCurrency = strtoupper( trim( (string) $aInput['currency'] ) );

    return Array(
      'pt_gateway_account' => trim( (string) $aInput['gateway_account'] ),
      'pt_transaction_amount' => self::normalizeAmount( $aInput['amount'] ),
      'pt_currency' => $sCurrency,
      'pt_currency_code' => $sCurrency,
      'pb_post_auth' => 'no',
      'pt_account_code_1' => $sOrderId,
      'pt_billing_company' => isset( $aInput['billing_company'] ) ? (string) $aInput['billing_company'] : '',
      'pt_payment_name' => isset( $aInput['billing_name'] ) ? (string) $aInput['billing_name'] : '',
      'pt_billing_address_1' => isset( $aInput['billing_address_1'] ) ? (string) $aInput['billing_address_1'] : '',
      'pt_billing_city' => isset( $aInput['billing_city'] ) ? (string) $aInput['billing_city'] : '',
      'pt_billing_postal_code' => isset( $aInput['billing_postal_code'] ) ? (string) $aInput['billing_postal_code'] : '',
      'pt_billing_phone_number' => isset( $aInput['billing_phone'] ) ? (string) $aInput['billing_phone'] : '',
      'pt_billing_email_address' => isset( $aInput['billing_email'] ) ? (string) $aInput['billing_email'] : '',
      'pt_client_identifier' => self::CLIENT_IDENTIFIER,
      'pt_ip_address' => isset( $aInput['ip_address'] ) ? (string) $aInput['ip_address'] : '',
      'pb_transition_type' => 'post',
      'pb_success_url' => (string) $aInput['success_url'],
      'pd_collect_shipping_information' => 'no',
      'pd_display_items' => 'no',
      'pt_custom_name_1' => self::CUSTOM_TOKEN,
      'pt_custom_value_1' => (string) $aInput['token'],
      'pt_custom_name_2' => self::CUSTOM_ORDER_ID,
      'pt_custom_value_2' => $sOrderId
    );
  }

  /**
   * @param array $aResponse
   * @param string $sName
   * @return string
   */
  public static function extractCustomValue( $aResponse, $sName ){
    $sName = strtolower( trim( (string) $sName ) );
    for( $i = 1; $i <= 10; $i++ ){
      $sNameKey = 'pt_custom_name_'.$i;
      $sValueKey = 'pt_custom_value_'.$i;
      if( isset( $aResponse[$sNameKey] ) && strtolower( trim( (string) $aResponse[$sNameKey] ) ) === $sName ){
        return isset( $aResponse[$sValueKey] ) ? trim( (string) $aResponse[$sValueKey] ) : '';
      }
    }

    return isset( $aResponse[$sName] ) ? trim( (string) $aResponse[$sName] ) : '';
  }

  /**
   * @param mixed $mAmount
   * @return string
   */
  public static function normalizeAmount( $mAmount ){
    return number_format( (float) str_replace( ',', '.', (string) $mAmount ), 2, '.', '' );
  }

  /**
   * @param mixed $mReturned
   * @param mixed $mExpected
   * @return bool
   */
  public static function amountsMatch( $mReturned, $mExpected ){
    return self::normalizeAmount( $mReturned ) === self::normalizeAmount( $mExpected );
  }

  /**
   * @param array $aResponse
   * @return bool
   */
  public static function isSuccessfulResponse( $aResponse ){
    return strtolower( trim( isset( $aResponse['pi_response_status'] ) ? (string) $aResponse['pi_response_status'] : '' ) ) === 'success';
  }

  /**
   * @param array $aResponse
   * @return bool
   */
  public static function isGatewayResponse( $aResponse ){
    return isset( $aResponse['pi_response_status'] );
  }
}
?>
