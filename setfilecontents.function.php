<?php
/****************************************************************************
 * Write data to file
 * 2004-08-15 Tero Tilus <tero@tilus.net>
 *
 * $Id: setfilecontents.function.php,v 1.1 2004/08/15 10:58:58 mediaseitti Exp $
 *
 * This php function is distributed under the terms of the GNU General
 * Public License <http://www.gnu.org/licenses/gpl.html> and WITHOUT
 * ANY WARRANTY.
 **/

function setfilecontents($file, $contents, $overwrite=true) {
  if ( !$overwrite && file_exists($file) ) {
    // if we would overwrite file and we're told not to, nothing is
    // written
    return 0;
  }
  if ( $handle = fopen($file, 'w') ) {
    // file succesfully opened
    $written = fwrite($handle, $contents);
    fclose($handle);
    return $written;
  }
  // fopen failed
  return false;
}

?>
