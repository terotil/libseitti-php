<?php
/*****************************************************************************
 * Returns size ($size in bytes) in human-readable form
 * 2004-07-30 Tero Tilus
 *
 * $Id: hrsize.function.php,v 1.1 2004/07/31 13:42:55 mediaseitti Exp $
 */
function hrsize($size, $units=array('t', 'kt', 'Mt', 'Gt')) {
  $factor = 1024;
  $unit = array_shift($units);
  while ( $size > $factor && count($units) > 0 ) {
    $size = $size / $factor;
    $unit = array_shift($units);
  }
  return number_format($size, ($size<10?2:1), ',', ' ') . ' ' . $unit;
}
?>