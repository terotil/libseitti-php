<?php
/******************************************************************************
 ** Transform two dimensional array (array of arrays) to table
 **  -formatted strign.
 **
 ** @param $data Array of arrays to be transformed to table
 ** @param $fs Field separator.  Occurances in data are quoted (quopri)
 ** @param $rs Record separator.  Occurances in data are quoted (quopri)
 ** @param $headers Only values corresponding to keys found from this
 **   array are printed.  An array in $data not having a key found from
 **   $headers produces empty column in resulting table.  
 */
function array2table($data, $fs=";", $rs="\r\n", $headers=false, $fsr=', ', $rsr=', ') {
  // Default to including all the fields found in first record
  if ( ! $headers ) {
    $headers = array_keys(current($data));
  }

  // Format data as table
  $table = array();
  foreach ( $data as $record ) {
    $row = array();
    foreach ( $headers as $colkey ) {
      // Append to list of fields.  Set nonexisting fields empty.
      $row[] = a2t_quote(a2t_quote((isset($record[$colkey])) ? 
				   ($record[$colkey]) : (''), 
				   $fs, $fsr), 
			  $rs, $rsr);
    }
    // Assemble a record using $fs as separator for fields and append
    // it to list of records.
    $table[] = join($fs, $row);
  }

  // Assemble a table using $rs as separator for records
  return join($rs, $table);
}

function a2t_quote($str, $separator, $replacement) {
  if ( $separator == "\r\n" || 
       $separator == "\n" ||
       $separator == "\r" ) {
    return ereg_replace("[\r\n]+", $replacement, $str);
  }
  return str_replace($separator, $replacement, $str);
}
?>