<?php
function &get_translations($lang, $file='trans.txt') {
  $tekstit = array();
  // K��nn�kset ovat ./trans.txt -tiedostossa t�h�n tapaan sana/rivi.
  //    hae|Hae|Search|S�k
  $rivit = file($file);
  // Kielet l�ytyv�t ensimm�iselt� rivilt� (otsikkorivi).  Shiftataan
  // kerran, koska ensimm�isess� sarakkeessa on kunkin k��nn�ksen avain,
  // eik� k��nn�st� itse��n.
  $kielet = explode('|', array_shift($rivit)); array_shift($kielet);
  // Haetaan monennessako sarakkeessa k�ytetty kieli taulukossa on
  $lang_col = 0;
  foreach ( $kielet as $nro => $kieli ) {
    if ( $lang == $kieli ) { $lang_col = $nro; break; }
  }
  // Tuodaan tekstit kyseisest� sarakkeesta taulukkoon.  Indeksoidaan
  // ensimm�isess� sarakkeessa olevalla avaimella.
  $tekstit = array();
  foreach ( $rivit as $kaannokset ) {
    $kaannokset = rtrim($kaannokset);
    $kaannokset = explode('|', $kaannokset);
    $key = array_shift($kaannokset);
    $tekstit[$key] = $kaannokset[$lang_col];
  }
  return $tekstit;
}
?>
