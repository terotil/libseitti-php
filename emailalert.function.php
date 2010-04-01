<?php
/*****************************************************************************
 * Send mail-alert
 * 15.8.2004 Tero Tilus
 *
 * $Id: emailalert.function.php,v 1.3 2006/10/05 13:44:08 mediaseitti Exp $
 */
function emailalert($to, $errmsg) {
  if ( defined('DISABLE_EMAILALERT') ) return true;
  if ( ereg('Googlebot|Yahoo! Slurp|msnbot|Gigabot|www.fi crawler', 
	    $_SERVER['HTTP_USER_AGENT']) ) {
    $bot = '(bot)';
  }
  $subject = "[{$_SERVER['SERVER_NAME']}] Alert $bot";
  $server = prettyprint_array($_SERVER);
  $request = prettyprint_array($_REQUEST);
  $mail = "ERROR\n'$errmsg'\n\nREQUEST\n$request\n\nSERVER\n$server\n\n-- \n";
  return mail($to, $subject, $mail, 
	      "Content-type: text/plain; charset=iso-8859-1\r\n".
	      "Content-transfer-encoding: 8bit");
}

function prettyprint_array(&$array, $cut=200) {
  foreach ( $array as $key => $val ) {
    if ( strlen($val) > $cut ) {
      $val = substr($val, 0, $cut);
      $postfix = " cut at $cut chars";
    } else {
      $postfix = '';
    }
    $strval .= "'$key'\n   '$val'$postfix\n";
  }
  return $strval;
}