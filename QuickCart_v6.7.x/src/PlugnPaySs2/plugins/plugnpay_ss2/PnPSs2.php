<?php
require_once dirname( __FILE__ ).'/PnPSs2Protocol.php';

/**
 * Quick.Cart-specific Smart Screens v2 integration.
 */
class PnPSs2
{
  /**
   * @return bool
   */
  public static function isConfigured( ){
    return !empty( $GLOBALS['config']['plugnpay_ss2_gateway_account'] )
      && $GLOBALS['config']['plugnpay_ss2_gateway_account'] !== 'YOUR_GATEWAY_ACCOUNT'
      && !empty( $GLOBALS['config']['plugnpay_ss2_currency'] )
      && !empty( $GLOBALS['config']['plugnpay_ss2_store_url'] )
      && $GLOBALS['config']['plugnpay_ss2_store_url'] !== 'https://shop.example.com/';
  }

  /**
   * Creates a one-time return token and remembers the submitted order values.
   *
   * @param int $iOrder
   * @param string $sAmount
   * @return string
   */
  public static function rememberOrder( $iOrder, $sAmount ){
    $sToken = hash( 'sha256', uniqid( mt_rand( ), true ).session_id( ).microtime( true ) );
    $aExpected = Array(
      'order_id' => (string) $iOrder,
      'amount' => PnPSs2Protocol::normalizeAmount( $sAmount ),
      'currency' => strtoupper( trim( (string) $GLOBALS['config']['plugnpay_ss2_currency'] ) ),
      'gateway_account' => trim( (string) $GLOBALS['config']['plugnpay_ss2_gateway_account'] ),
      'token' => $sToken,
      'created_at' => time( )
    );
    $_SESSION['plugnpay_ss2'] = $aExpected;
    if( !self::writeExpectedResponse( (string) $iOrder, $aExpected ) ){
      unset( $_SESSION['plugnpay_ss2'] );
      return false;
    }
    return $sToken;
  }

  /**
   * @param int $iOrder
   * @param array $aOrder
   * @return array
   */
  public static function buildHostedFields( $iOrder, $aOrder ){
    $sAmount = isset( $GLOBALS['oOrder']->aOrders[$iOrder]['fOrderSummary'] )
      ? $GLOBALS['oOrder']->aOrders[$iOrder]['fOrderSummary']
      : $GLOBALS['oOrder']->aOrders[$iOrder]['sOrderSummary'];
    $sToken = self::rememberOrder( $iOrder, $sAmount );
    if( $sToken === false )
      return false;

    return PnPSs2Protocol::buildHostedFields( Array(
      'gateway_account' => $GLOBALS['config']['plugnpay_ss2_gateway_account'],
      'amount' => $sAmount,
      'currency' => $GLOBALS['config']['plugnpay_ss2_currency'],
      'order_id' => $iOrder,
      'token' => $sToken,
      'success_url' => self::buildReturnUrl( ),
      'billing_company' => isset( $aOrder['sCompanyName'] ) ? $aOrder['sCompanyName'] : '',
      'billing_name' => trim( $aOrder['sFirstName'].' '.$aOrder['sLastName'] ),
      'billing_address_1' => $aOrder['sStreet'],
      'billing_city' => $aOrder['sCity'],
      'billing_postal_code' => $aOrder['sZipCode'],
      'billing_phone' => $aOrder['sPhone'],
      'billing_email' => $aOrder['sEmail'],
      'ip_address' => isset( $aOrder['sIp'] ) ? $aOrder['sIp'] : ''
    ) );
  }

  /**
   * @return string
   */
  public static function buildReturnUrl( ){
    $sStoreUrl = rtrim( $GLOBALS['config']['plugnpay_ss2_store_url'], '/' ).'/';

    $sOrderPage = isset( $GLOBALS['oPage']->aPages[$GLOBALS['config']['order_print']]['sLinkName'] )
      ? $GLOBALS['oPage']->aPages[$GLOBALS['config']['order_print']]['sLinkName']
      : 'index.php';
    return $sStoreUrl.ltrim( html_entity_decode( $sOrderPage, ENT_QUOTES, 'UTF-8' ), '/' );
  }

  /**
   * Validates a Smart Screens return against values stored before redirect.
   *
   * @param array $aResponse
   * @return array
   */
  public static function validateResponse( $aResponse ){
    $sStatus = strtolower( trim( isset( $aResponse['pi_response_status'] ) ? (string) $aResponse['pi_response_status'] : '' ) );
    if( $sStatus === '' ){
      return Array( 'valid' => false, 'message' => 'The gateway response did not include a status.' );
    }

    $sOrderId = PnPSs2Protocol::extractCustomValue( $aResponse, PnPSs2Protocol::CUSTOM_ORDER_ID );
    $sToken = PnPSs2Protocol::extractCustomValue( $aResponse, PnPSs2Protocol::CUSTOM_TOKEN );
    $aExpected = self::readExpectedResponse( $sOrderId );
    if( !is_array( $aExpected )
      && isset( $_SESSION['plugnpay_ss2'] )
      && is_array( $_SESSION['plugnpay_ss2'] )
      && isset( $_SESSION['plugnpay_ss2']['order_id'] )
      && (string) $_SESSION['plugnpay_ss2']['order_id'] === $sOrderId
    ){
      $aExpected = $_SESSION['plugnpay_ss2'];
    }
    if( !is_array( $aExpected ) ){
      return Array( 'valid' => false, 'message' => 'The payment session could not be verified.' );
    }
    $_SESSION['plugnpay_ss2'] = $aExpected;

    if( $sOrderId === '' || !self::safeEquals( (string) $aExpected['order_id'], $sOrderId ) ){
      return Array( 'valid' => false, 'message' => 'The returned order did not match.' );
    }
    if( $sToken === '' || !self::safeEquals( (string) $aExpected['token'], $sToken ) ){
      return Array( 'valid' => false, 'message' => 'The payment token did not match.' );
    }

    $sReturnedAccount = trim( isset( $aResponse['pt_gateway_account'] ) ? (string) $aResponse['pt_gateway_account'] : '' );
    if( $sReturnedAccount === '' || strcasecmp( $sReturnedAccount, $aExpected['gateway_account'] ) !== 0 ){
      return Array( 'valid' => false, 'message' => 'The gateway account did not match.' );
    }

    if( !isset( $aResponse['pt_transaction_amount'] )
      || !PnPSs2Protocol::amountsMatch( $aResponse['pt_transaction_amount'], $aExpected['amount'] ) ){
      return Array( 'valid' => false, 'message' => 'The payment amount did not match.' );
    }

    $sReturnedCurrency = isset( $aResponse['pt_currency_code'] )
      ? strtoupper( trim( (string) $aResponse['pt_currency_code'] ) )
      : ( isset( $aResponse['pt_currency'] ) ? strtoupper( trim( (string) $aResponse['pt_currency'] ) ) : '' );
    if( $sReturnedCurrency === '' || $sReturnedCurrency !== $aExpected['currency'] ){
      return Array( 'valid' => false, 'message' => 'The payment currency did not match.' );
    }

    return Array(
      'valid' => true,
      'success' => PnPSs2Protocol::isSuccessfulResponse( $aResponse ),
      'order_id' => (int) $aExpected['order_id'],
      'message' => isset( $aResponse['pi_error_message'] ) ? trim( (string) $aResponse['pi_error_message'] ) : ''
    );
  }

  /**
   * Constant-time string comparison compatible with PHP 5.2.
   *
   * @param string $sExpected
   * @param string $sActual
   * @return bool
   */
  private static function safeEquals( $sExpected, $sActual ){
    $sExpected = (string) $sExpected;
    $sActual = (string) $sActual;
    if( strlen( $sExpected ) !== strlen( $sActual ) )
      return false;

    $iResult = 0;
    for( $i = 0; $i < strlen( $sExpected ); $i++ )
      $iResult |= ord( $sExpected[$i] ) ^ ord( $sActual[$i] );

    return $iResult === 0;
  }

  /**
   * Prevents a successful gateway response from being replayed.
   */
  public static function clearExpectedResponse( ){
    if( isset( $_SESSION['plugnpay_ss2']['order_id'] ) )
      self::deleteExpectedResponse( (string) $_SESSION['plugnpay_ss2']['order_id'] );
    unset( $_SESSION['plugnpay_ss2'] );
  }

  /**
   * Returns a safe merchant-facing authorization note.
   *
   * @param array $aResponse
   * @return string
   */
  public static function authorizationNote( $aResponse ){
    $sTransactionId = isset( $aResponse['pt_order_id'] )
      ? preg_replace( '/[^a-zA-Z0-9_.-]/', '', (string) $aResponse['pt_order_id'] )
      : '';
    $sAuthorizationCode = isset( $aResponse['pt_authorization_code'] )
      ? preg_replace( '/[^a-zA-Z0-9_.-]/', '', (string) $aResponse['pt_authorization_code'] )
      : '';
    $sNote = 'PlugnPay SS2 authorized; transaction '.$sTransactionId;
    if( $sAuthorizationCode !== '' )
      $sNote .= '; auth '.$sAuthorizationCode;
    return $sNote.'; settle in PlugnPay Admin';
  }

  /**
   * Appends the authorization to a dedicated audit file without
   * rewriting Quick.Cart's order database.
   *
   * @param int $iOrder
   * @param array $aResponse
   * @return bool
   */
  public static function recordAuthorization( $iOrder, $aResponse ){
    if( !defined( 'DIR_DATABASE' ) )
      return false;

    $sFile = DIR_DATABASE.'plugnpay_ss2_transactions.php';
    $bNewFile = !is_file( $sFile );
    $rFile = fopen( $sFile, 'a+' );
    if( $rFile === false || !flock( $rFile, LOCK_EX ) ){
      if( $rFile !== false )
        fclose( $rFile );
      return false;
    }
    if( $bNewFile ){
      @chmod( $sFile, 0600 );
      if( fwrite( $rFile, "<?php exit; ?>\n" ) === false ){
        flock( $rFile, LOCK_UN );
        fclose( $rFile );
        return false;
      }
    }

    $aRecord = Array(
      'iOrder' => (int) $iOrder,
      'iTime' => time( ),
      'sStatus' => 'authorized',
      'sTransactionId' => isset( $aResponse['pt_order_id'] )
        ? preg_replace( '/[^a-zA-Z0-9_.-]/', '', (string) $aResponse['pt_order_id'] )
        : '',
      'sAuthorizationCode' => isset( $aResponse['pt_authorization_code'] )
        ? preg_replace( '/[^a-zA-Z0-9_.-]/', '', (string) $aResponse['pt_authorization_code'] )
        : '',
      'sAmount' => isset( $aResponse['pt_transaction_amount'] )
        ? PnPSs2Protocol::normalizeAmount( $aResponse['pt_transaction_amount'] )
        : '',
      'sCurrency' => isset( $aResponse['pt_currency_code'] )
        ? strtoupper( trim( (string) $aResponse['pt_currency_code'] ) )
        : strtoupper( trim( isset( $aResponse['pt_currency'] ) ? (string) $aResponse['pt_currency'] : '' ) )
    );
    $bSaved = fwrite( $rFile, serialize( $aRecord )."\n" ) !== false;
    fflush( $rFile );
    flock( $rFile, LOCK_UN );
    fclose( $rFile );
    return $bSaved;
  }

  /**
   * Saves expected return values outside the browser session. Modern browsers
   * may withhold the session cookie on Smart Screens' cross-site POST.
   *
   * @param string $sOrderId
   * @param array $aExpected
   * @return bool
   */
  private static function writeExpectedResponse( $sOrderId, $aExpected ){
    return self::updateExpectedResponses( $sOrderId, $aExpected );
  }

  /**
   * @param string $sOrderId
   * @return array|null
   */
  private static function readExpectedResponse( $sOrderId ){
    if( $sOrderId === '' || !defined( 'DIR_DATABASE' ) )
      return null;

    $sFile = DIR_DATABASE.'plugnpay_ss2.php';
    if( !is_file( $sFile ) )
      return null;

    $rFile = fopen( $sFile, 'r' );
    if( $rFile === false )
      return null;

    flock( $rFile, LOCK_SH );
    $sContent = stream_get_contents( $rFile );
    flock( $rFile, LOCK_UN );
    fclose( $rFile );
    $aExpected = self::decodeExpectedResponses( $sContent );

    return isset( $aExpected[$sOrderId] ) ? $aExpected[$sOrderId] : null;
  }

  /**
   * @param string $sOrderId
   * @return bool
   */
  private static function deleteExpectedResponse( $sOrderId ){
    return self::updateExpectedResponses( $sOrderId, null );
  }

  /**
   * @param string $sOrderId
   * @param array|null $aValue
   * @return bool
   */
  private static function updateExpectedResponses( $sOrderId, $aValue ){
    if( !defined( 'DIR_DATABASE' ) || $sOrderId === '' )
      return false;

    $sFile = DIR_DATABASE.'plugnpay_ss2.php';
    $bNewFile = !is_file( $sFile );
    $rFile = fopen( $sFile, 'a+' );
    if( $rFile === false )
      return false;
    if( $bNewFile )
      @chmod( $sFile, 0600 );

    if( !flock( $rFile, LOCK_EX ) ){
      fclose( $rFile );
      return false;
    }

    rewind( $rFile );
    $aExpected = self::decodeExpectedResponses( stream_get_contents( $rFile ) );
    foreach( $aExpected as $sSavedOrderId => $aSaved ){
      if( isset( $aSaved['created_at'] ) && (int) $aSaved['created_at'] < time( ) - 604800 )
        unset( $aExpected[$sSavedOrderId] );
    }
    if( isset( $aValue ) )
      $aExpected[$sOrderId] = $aValue;
    elseif( isset( $aExpected[$sOrderId] ) )
      unset( $aExpected[$sOrderId] );

    ftruncate( $rFile, 0 );
    rewind( $rFile );
    $bSaved = fwrite( $rFile, "<?php exit; ?>\n".serialize( $aExpected ) ) !== false;
    fflush( $rFile );
    flock( $rFile, LOCK_UN );
    fclose( $rFile );
    return $bSaved;
  }

  /**
   * @param string $sContent
   * @return array
   */
  private static function decodeExpectedResponses( $sContent ){
    $iLineEnd = strpos( $sContent, "\n" );
    if( $iLineEnd !== false )
      $sContent = substr( $sContent, $iLineEnd + 1 );
    $aExpected = !empty( $sContent ) ? @unserialize( trim( $sContent ) ) : Array( );
    return is_array( $aExpected ) ? $aExpected : Array( );
  }

  /**
   * @param string $sValue
   * @return string
   */
  public static function escape( $sValue ){
    return htmlspecialchars( (string) $sValue, ENT_QUOTES, 'UTF-8' );
  }
}
?>
