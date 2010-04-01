<?php
/*****************************************************************************
 * Create LaTeX-hyperlink
 * 29.7.2004 Tero Tilus
 *
 * $Id: href_latex.function.php,v 1.1.1.1 2004/07/29 19:27:07 mediaseitti Exp $
 */
function href_latex($url, $lnktxt=false, $color=false) {
  if ( ! $lnktxt ) $lnktxt = $url;
  $color = ( $color ? ('\color{'.$color.'}') : '' );
  return '\href{'.latex2url($url).'}{'.$color.$lnktxt.'}'; 
}

// Unquote ampersand, underscore and tilde
function latex2url($txt) {
  $repl = array('\&' => '&',
		'\_' => '_',
		'\~{}' => '~');
  foreach ( $repl as $s => $r ) {
    $txt = str_replace($s, $r, $txt);
  }
  return $txt;
}
?>
