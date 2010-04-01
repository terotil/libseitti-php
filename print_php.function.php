<?php
/****************************************************************************
 * Copyright 2005, Tero Tilus <tero@tilus.net>
 *
 * This php function is distributed under the terms of the GNU General
 * Public License <http://www.gnu.org/licenses/gpl.html> and WITHOUT
 * ANY WARRANTY.
 **/

function __format($string, $format, $type) {
  switch ( $type ) {
  case 'text/plain':
  default:
    return $string;
  case 'text/html': 
    static $colors = array('num' => 'blue', 'str' => 'green', 
			   'punct' => 'black', 'bool' => 'magenta',
			   'special' => 'cyan', 'comment' => 'silver');
    return '<span style="color:'.$colors[$format].'">'.$string.'</span>';
  }
}

function __punct($str, $type) {
  return __format($str, 'punct', $type);
}

// Return php variable declaration as pretty printed php-code.
function print_php($variable, $indent=0, $type='text/html') {
  $indent_str = str_repeat('  ', $indent);
  switch (gettype($variable)) {
  case 'boolean':
    return __format($variable ? 'true' : 'false', 'bool', $type);
  case 'integer':
  case 'double':
    return __format("$variable", 'num', $type);
  case 'string':
    return __format("'" . addcslashes($variable, "'\\") . "'",
			    'str', $type);
  case 'array':
    $output = array();
    foreach ( $variable as $key => $element ) {
      $output[] = print_php($key, $indent+1, $type) . __punct(' => ', $type) . 
	print_php($element, $indent+1, $type);
    }
    return "array" . __punct('(', $type) . "\n$indent_str" . 
      join( __punct(',', $type)."\n$indent_str", $output) . 
      "\n$indent_str" . __punct(')', $type);
  case 'object':
    $serialized = serialize($variable);
    return 'unserialize' . __punct('(', $type) .
      print_php($serialized, $indent, $type) . 
      __punct(')', $type).' '.__format('/* Object */', 'comment', $type);
  case 'resource':
    $resourcetype = get_resource_type($variable);
    return __format('NULL', 'special', $type) . ' ' .
      __format("/* Resource: $resourcetype */", 'comment', $type);
  case 'NULL':
    return __format('NULL', 'special', $type);
  default:
  }
  return __format('NULL', 'special', $type) . ' ' .
    __format("/* Unknown */", 'comment', $type);
}

?>