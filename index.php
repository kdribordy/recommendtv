<?php
  error_reporting(E_ALL);
  include("config.php");

  header("content-type: text/xml");
  $dom = new DOMDocument('1.0', 'utf-8');
  $responseEl = $dom->createElement('Response'); 
  $messageEl = $dom->createElement('Message');
  $message = "None";

  //testing

  $body = $_REQUEST["Body"];
  $from = hash("sha256", $_REQUEST["From"]);

  // Check if we have record of the user
  $user = getUser($from);
  if ($user)
  {
    // Check if the user has a ZIP code recorded
    if ($user["zip_code"])
    {
      // Send them a suggestions
      $suggestion = getShowSuggestion($user["zip_code"], null);
      
      if ($suggestion)
      {
        $airtime = date ('g:i A T',strtotime($suggestion->AiringTime));
        $message = "How about $suggestion->Title? It started at $airtime on channel $suggestion->Channel and runs for $suggestion->Duration minutes.";
      }
      else {
        $message = "Sorry, there is literally nothing on.";
      }
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
        $message = "I didn't understand your response. Reply with your ZIP code.";
      }
    }
  }
  else
  {
    // This is a new user
    $message = "Welcome to RecommendTvTo.us!  Reply with your ZIP code to get started.";
    addUser($from, null);
  }

//  $query = $database->prepare("SELECT * FROM recommendtv.users WHERE phone = :reqPhone");
//  $query->execute(array('phone' => $_REQUEST['from']));
//  if ($query)
//  {
//    $data = $query->fetchAll();
//    $num_rows = count($data);
//  } else { db_print_error(); }
  
  function getShowSuggestion($zip, $category)
  {

    $jsonurl = "http://api.rovicorp.com/TVlistings/v9/listings/services/postalcode/$zip/info?locale=en-US&countrycode=US&apikey=bnp966tdms7t9p5hze264wae&sig=sig";

    $json = file_get_contents($jsonurl);

    $resultObj = json_decode($json);

    $serviceId = $resultObj->ServicesResult->Services->Service[0]->ServiceId;

    $jsonurl = "http://api.rovicorp.com/TVlistings/v9/listings/linearschedule/$serviceId/info?locale=en-US&duration=30&inprogress=true&apikey=tq9qyz3r86vjhqn9w49vf4dt&sig=sig&inprogress=true";
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
      return $airings[rand(0, $airing_count - 1)];
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
    global $database;
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
    global $database;
    $database->beginTransaction();
    $statement = $database->prepare("INSERT INTO users (phone, zip_code) VALUES (:phoneNumber, :zipCode)");
    $statement->execute(array(':phoneNumber' => $hashedPhone, ':zipCode' => $zip));
    $database->commit();
  }

  function updateUser($hashedPhone, $zip)
  {
    global $database;
    $database->beginTransaction();
    $statement = $database->prepare("UPDATE users SET zip_code = :zipCode WHERE phone = :phoneNumber");
    $statement->execute(array(':phoneNumber' => $hashedPhone, ':zipCode' => $zip));
    $database->commit();
  }

//print XML Response
$messageEl->appendChild($dom->createTextNode($message));
$responseEl->appendChild($messageEl); 
$dom->appendChild($responseEl);
echo $dom->saveXml();
?>
