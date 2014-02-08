<?php
  header("content-type: text/xml");
  echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";

  $zip = $_REQUEST["Body"] ? $_REQUEST["Body"] : 46385;

  $jsonurl = "http://api.rovicorp.com/TVlistings/v9/listings/services/postalcode/$zip/info?locale=en-US&countrycode=US&apikey=bnp966tdms7t9p5hze264wae&sig=sig";

  $json = file_get_contents($jsonurl);

  $resultObj = json_decode($json);

  $serviceId = $resultObj->ServicesResult->Services->Service[0]->ServiceId;

  $jsonurl = "http://api.rovicorp.com/TVlistings/v9/listings/linearschedule/$serviceId/info?locale=en-US&duration=30&inprogress=true&apikey=tq9qyz3r86vjhqn9w49vf4dt&sig=sig";
  $json = file_get_contents($jsonurl);
  $resultObj = json_decode($json);

  $airings = $resultObj->LinearScheduleResult->Schedule->Airings;

  foreach ($resultObj as $item)
  {
    
  }
?>
<Response>
  <Message></Message>
</Response>
