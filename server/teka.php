<?php
$url = 'http://maps.googleapis.com/maps/api/geocode/json?latlng=40.714224,-73.961452&sensor=false';
$data = file_get_contents($url);
$jsondata = json_decode($data,true);
if(is_array($jsondata )&& $jsondata ['status']=='OK')
{
      $addr = $jsondata['results'][0]['formatted_address'];
}
?>
