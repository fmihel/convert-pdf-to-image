<?php

require_once __DIR__.'/../source/ConvertPdfToImage.php';
use fmihel\ConvertPdfToImage\ConvertPdfToImage;

$pdf = './_data/Primer_pdf_.pdf';
$out = './_result/local.jpg';


$time_start = microtime(true);
echo '<html><head><style>body{color:gray}</style></head>';

$cpi = new ConvertPdfToImage($pdf); 
$cpi->save($out,['page'=>'all']);


$time_end = microtime(true);
$time = $time_end - $time_start;


echo 'dT: '.$time.' [s]<br>';

?>