<?php
  header("content-type: text/xml");
  echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";

  $jsonurl = "http://api.rovicorp.com/TVlistings/v9/listings/linearschedule/360861/info?locale=en-US&duration=30&inprogress=true&apikey=tq9qyz3r86vjhqn9w49vf4dt&sig=sig";
  $json = file_get_contents($jsonurl);
  $resultObj = json_decode($json);
  filterByCategory($resultObj.LinearScheduleResult.Schedule.Airings, "Other");

  foreach ($resultObj as $item)
  {
    
  }
?>
<Response>
    <Message></Message>
</Response>
