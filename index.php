<?php
  error_reporting(E_ALL);
  include("config.php");

  header("content-type: text/xml");
  echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
  echo "<Response>\n<Message>";

  $zip = $_REQUEST["Body"] ? $_REQUEST["Body"] : 46385;

  $jsonurl = "http://api.rovicorp.com/TVlistings/v9/listings/services/postalcode/$zip/info?locale=en-US&countrycode=US&apikey=bnp966tdms7t9p5hze264wae&sig=sig";

  $json = file_get_contents($jsonurl);

  $resultObj = json_decode($json);

  $serviceId = $resultObj->ServicesResult->Services->Service[0]->ServiceId;

  $jsonurl = "http://api.rovicorp.com/TVlistings/v9/listings/linearschedule/$serviceId/info?locale=en-US&duration=30&inprogress=true&apikey=tq9qyz3r86vjhqn9w49vf4dt&sig=sig";
  $json = file_get_contents($jsonurl);
  $resultObj = json_decode($json);

//  filterByCategory($resultObj->LinearScheduleResult->Schedule->Airings, "Other");

/*
Database Example Usage:
        Basic PDO Query (Pre-Sanitized or No Data Sanitation Required):
        $query = $database->query("SELECT * FROM ... WHERE ...");
        if ($query) {
                $data = $query->fetchAll();
                $num_rows = count($data);
        } else { db_print_error(); }

        Prepared PDO Query (Protection from SQL Injections Built-in):
        $query = $database->prepare("SELECT * FROM ... WHERE thing = :whatsit AND person = :whosit");
        $query->execute(array('whatsit' => 'burger', 'whosit' => 'John'));
        if ($query) {
                $data = $query->fetchAll();
                $num_rows = count($data);
        } else { db_print_error(); }

        For Transactions:
        $database->beginTransaction();
        ...statement code...
        $database->commit();

        Detecting Last Insert ID:
        $database->lastInsertId();

        Detecting Rows Deleted:
        $query->rowCount();

        Explanation of Querying Errors:
        $db_error = $query->errorInfo();
        print $db_error[2];
*/

//  $query = $database->prepare("SELECT * FROM recommendtv.users WHERE phone = :reqPhone");
//  $query->execute(array('phone' => $_REQUEST['from']));
//  if ($query)
//  {
//    $data = $query->fetchAll();
//    $num_rows = count($data);
//  } else { db_print_error(); }

  $airings = $resultObj->LinearScheduleResult->Schedule->Airings;
  $filteredAirings = filterByCategory($airings, "comedy");

  function filterByCategory($airings, $category)
  {
     $category = strtolower($category);
     $result = array();

     foreach ($airings as $program)
     {
       if (strconts(strtolower($program->Category), $category) || strconts(strtolower($program->Subcategory), $category))
       {
         array_push($result, $program);
       }
     }

     return $result;
  }

  function strconts($string, $search)
  {
    return strpos($string, $search) !== false;
  }
?>

</Message>
</Response>

