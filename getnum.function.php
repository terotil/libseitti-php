<?php
/****************************************************************************
 * Copyright 2002, Tero Tilus <tero@tilus.net>
 *
 * This php function is distributed under the terms of the GNU General
 * Public License <http://www.gnu.org/licenses/gpl.html> and WITHOUT
 * ANY WARRANTY.
 **/

// last , or . found from string is considered a decimal separator
// everything else but digits [0-9] is removed
function getnum($str) {
  // already number?
  if ( is_float($str) || is_int($str) ) return $str;
  // non-string or string with no digits?
  if ( !is_string($str) || !ereg('[0-9]', $str) ) return false;
  // now we know $str is string having one or more digits
  $last_comma = strrchr($str, ",");
  $last_dot = strrchr($str, ".");
  // no decimal part?  return integer
  if ( $last_comma===false && $last_dot===false )
    return intval(ereg_replace('[^0-9]', '', $str));
  // decimal part exists, which one to choose?
  if ( ((strlen($last_comma) > strlen($last_dot)) ||
	($last_comma === false)) &&
       $last_dot !== false ) {
    // dots exist and last one is closer to the end than last comma
    $decpart = $last_dot;
  } else {
    // commas exist and last one is closer to the end than last dot
    $decpart = $last_comma;
  }
  $intpart = substr($str, 0, strlen($str)-strlen($decpart));
  return floatval(ereg_replace('[^0-9]', '', $intpart)) 
    + floatval('0.'.ereg_replace('[^0-9]', '', $decpart));
}

/* Testi

require_once('simpletest/unit_tester.php');
require_once('simpletest/reporter.php');
require_once('libseitti-php/print_php.function.php');
class TestGetnum extends UnitTestCase {
  function testGetnum() {
    $this->numstrcases = 
      array(
	    '.1234' => 0.1234,
	    '0.1234' => 0.1234,
	    '0,1234' => 0.1234,
	    '0,.1234' => 0.1234,
	    '0,0.1234' => 0.1234,
	    '0,0,1234' => 0.1234,
	    'asdf.1234' => 0.1234,
	    'asdf0.1234' => 0.1234,
	    'asdf0,1234' => 0.1234,
	    '.1234asdf' => 0.1234,
	    '0.1234aasdf' => 0.1234,
	    '0,1234asdf' => 0.1234,
	    '10.1234' => 10.1234,
	    '10.1234' => 10.1234,
	    '10,1234' => 10.1234,
	    '1,0.1234' => 10.1234,
	    '1,0.1234' => 10.1234,
	    '1.0,1234' => 10.1234,
	    '1' => 1,
	    '0' => 0,
	    );
    $this->nonnumcases =
      array(
	    'asdf' => false,
	    ',' => false,
	    '.' => false
	    );
  }
    
  function __iterate($cases) {
    foreach ( $cases as $in => $test ) {
      $out = getnum($in);
      if ( $out !== $test ) {
	echo print_php($in), ' ', print_php($out), ' ', print_php($test), ' ';
      }
      $this->assertTrue($out === $test);
    }
  }

  function testStrNumbers() {
    $this->__iterate($this->numstrcases);
  }

  function testNonNumbers() {
    $this->__iterate($this->nonnumcases);
  }

  function testNumbers() {
    $this->assertTrue(getnum(false) === false);
    $this->assertTrue(getnum(0) === 0);
    $this->assertTrue(getnum(1) === 1);
    $this->assertTrue(getnum(-1) === -1);
    $this->assertTrue(getnum(0.0) === 0.0);
    $this->assertTrue(getnum(0.1) === 0.1);
    $this->assertTrue(getnum(-0.1) === -0.1);
  }

}

$test = &new TestGetnum();
$test->run(new HtmlReporter());

/**/

?>