<?php
  error_reporting(E_ALL);
  include("config.php");

  header("content-type: text/xml");
  echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
  echo "<Response>\n<Message>";
  $message = "None";
  $body = $_REQUEST["Body"];
  $from = hash("sha256", $_REQUEST["From"]);

  // Check if we have record of the user
  $user = getUser($from);
  if ($user)
  {
    // Check if the user has a ZIP code recorded
    if ($user->zip)
    {
      // Send them a suggestions
      $suggestion = getShowSuggestion($user->zip, null);
      $message = $suggestion ? $suggestion->Title : "Sorry, there's nothing on.";
    }
    else
    {
      // Check if they sent us a ZIP code
      if (preg_match("/\d{5}/", $body, $matches))
      {
        updateUser($from, $matches[0]);
        $message = "Your ZIP code was set to " . $matches[0] . ".";
      }
      else
      {
        $message = "I didn't understand your response. Reply with your ZIP code."
      }
    }
  }
  else
  {
    // This is a new user
    $message = "Welcome to RecommendTvTo.us!  Reply with your ZIP code to get started.";
    addUser($from, null);
  }

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

  function getShowSuggestion($zip, $category)
  {
    $zip = $_REQUEST["Body"] ? $_REQUEST["Body"] : 46385;

    $jsonurl = "http://api.rovicorp.com/TVlistings/v9/listings/services/postalcode/$zip/info?locale=en-US&countrycode=US&apikey=bnp966tdms7t9p5hze264wae&sig=sig";

    $json = file_get_contents($jsonurl);

    $resultObj = json_decode($json);

    $serviceId = $resultObj->ServicesResult->Services->Service[0]->ServiceId;

    $jsonurl = "http://api.rovicorp.com/TVlistings/v9/listings/linearschedule/$serviceId/info?locale=en-US&duration=30&inprogress=true&apikey=tq9qyz3r86vjhqn9w49vf4dt&sig=sig";
    $json = file_get_contents($jsonurl);
    $resultObj = json_decode($json);

    $airings = $resultObj->LinearScheduleResult->Schedule->Airings;

    if ($category)
    {
      $airings = filterByCategory($airings, $category);
    }

    $airing_count = count($airings);
    if ($airing_count > 0)
    {
      return $airings[rand(0, $airing_count - 1)]->Title;
    }

    return null;
  }

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

  function getUser($hashedPhone)
  {
    $query = $database->prepare("SELECT * FROM users WHERE phone = :phoneNumber");
    $query->execute(array(':phoneNumber' => $hashedPhone));
    $user = null;

    if ($query)
    {
      $data = $query->fetchAll();
      $num_rows = count($data);

      if ($num_rows > 0)
      {
        $user = $data[0];
      }
    }

    return $user;
  }

  function addUser($hashedPhone, $zip)
  {
    $database->beginTransaction();
    $statement = $database->prepare("INSERT INTO users (phone, zip) VALUES (:phoneNumber, :zipCode)");
    $statement->execute(array(':phoneNumber' => $hashedPhone, ':zipCode' => $zip));
    $database->commit();
  }

  function updateUser($hashedPhone, $zip)
  {
    $database->beginTransaction();
    $statement = $database->prepare("UPDATE users SET zip = :zipCode WHERE phone = :phoneNumber");
    $statement->execute(array(':phoneNumber' => $hashedPhone, ':zipCode' => $zip));
    $database->commit();
  }
?>

<?php echo $message ?></Message>
</Response>
