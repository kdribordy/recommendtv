<?php
  error_reporting(E_ALL);
  include("config.php");

  header("content-type: text/xml");
  $dom = new DOMDocument('1.0', 'utf-8');
  $responseEl = $dom->createElement('Response'); 
  $messageEl = $dom->createElement('Message');
  $message = "None";

  $body = $_REQUEST["Body"];
  $from = hash("sha256", $_REQUEST["From"]);

  // Check if we have record of the user
  $user = getUser($from);
  if ($user)
  {
    // Check if the user has a ZIP code recorded
    if ($user["zip_code"])
    {
      if ($user["service_id"])
      {
        // Send them a suggestion
        // Try to infer a category from the message
        $category = tryExtractCategory($body);

        $suggestion = getShowSuggestion($user["service_id"], $category);
        
        if ($suggestion)
        {
          $airtime = date ('g:i A T',strtotime($suggestion->AiringTime));
          $message = "How about $suggestion->Title? It started at $airtime on channel $suggestion->Channel and runs for $suggestion->Duration minutes.";
        }
        else
        {
          $message = "Sorry, there is literally nothing on.";
        }
      }
      else
      {
        if (preg_match("/\d+/", $body, $matches))
        {
          updateUserServiceId($from, $matches[0]);
          $message = "Service provider set. Want a recommendation?";
        }
        else
        {
          $message = "Didn't quite understand that. Try again!";
        }  
      }
    }
    else
    {
      // Check if they sent us a ZIP code
      if (preg_match("/\d{5}/", $body, $matches))
      {
        updateUserZip($from, $matches[0]);

        // Try to get a service id for the zip
        $serviceProviders = getProvidersForZip($matches[0]);
        $message = "Zip code set to " . $matches[0] . ". ";

        // Now see if they sent us a service provider
        if (preg_match("/[A-z]+/", $body, $providerMatches))
        {
          // They at least tried to give us a provider
          $partialMatches = array();
          foreach($serviceProviders as $provider)
          {
            if(strconts($provider->Name, $providerMatches[0]))
            {
              $partialMatches[] = $provider;
            }
          }

          $numberOfMatches = count($partialMatches);
          if ($numberOfMatches == 1)
          {
            updateUserServiceId($from, $partialMatches[0]->ServiceId);
            $providerName = $partialMatches[0]->Name;
            $message .= "Service provider set to $providerName! Would you like a recommendation?";
          }
          elseif ($numberOfMatches > 1)
          {
            $message = "There were a few matches. Select your provider: ";
            foreach ($partialMatches as $potentialMatch)
            {
              $message .= "$potentialMatch->ServiceId for $potentialMatch->Name\n";
            }
          }
          else 
          {
            $message = "We couldn't find a match :/ Select your provider: ";
            foreach ($serviceProviders as $provider)
            {
              $message .= "$provider->ServiceId - $provider->SystemName, ";
            }
          }
        }
        else
        {
          $message .= "Text back ";
          foreach ($serviceProviders as $provider)
          {
            $message .= "$provider->ServiceId - $provider->SystemName, ";
          }
        }
      }
      else
      {
        $message = "I didn't understand your response. Reply with your zip code.";
      }
    }
  }
  else
  {
    // This is a new user
    $message = "Welcome to RecommendTvTo.us!  Reply with your ZIP code and TV provider (if you know it) to get started.";
    addUser($from, null);
  }
  
  function getShowSuggestion($serviceId, $category)
  {
    $jsonurl = "http://api.rovicorp.com/TVlistings/v9/listings/linearschedule/$serviceId/info?locale=en-US&duration=5&inprogress=true&apikey=tq9qyz3r86vjhqn9w49vf4dt&sig=sig";
    $json = file_get_contents($jsonurl);
    $resultObj = json_decode($json);

    $airings = $resultObj->LinearScheduleResult->Schedule->Airings;

    if ($category)
    {
      $airings = filterByCategory($airings, $category);
    }

    $airings = filterToLowChannels($airings);

    $airing_count = count($airings);
    if ($airing_count > 0)
    {
      return $airings[rand(0, $airing_count - 1)];
    }

    return null;
  }

  function tryExtractCategory($text)
  {
    $knownCategories = array("education", "sport", "golf", "hockey", "baseball", "basketball", "shop", "comedy", "fantasy", "drama", "tennis", "soccer", "variety", "documentary", "adult", "western", "horror", "religion", "music", "talk", "magazine", "news", "food", "reality", "travel", "animate", "football", "entertainment", "paranormal", "performance", "outdoors", "boxing", "biography", "musical", "politics", "nature", "movie", "children", "lifestyle", "music", "other");

    foreach ($knownCategories as $category)
    {
      if (strconts($text, $category))
      {
        return $category;
      }
    }

    return null;
  }

  function filterByCategory($airings, $category)
  {
     $result = array();

     foreach ($airings as $program)
     {
       if (strconts($program->Category, $category) || ($program->Subcategory && strconts($program->Subcategory, $category)))
       {
         array_push($result, $program);
       }
     }

     return $result;
  }

  function filterToLowChannels($airings)
  {
     $result = array();

     foreach ($airings as $program)
     {
       if (preg_match("/^\d{1,2}$/", $program->Channel))
       {
         array_push($result, $program);
       }
     }

     return $result;
  }

  function strconts($string, $search)
  {
    return strpos(strtolower($string), strtolower($search)) !== false;
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

  function updateUserZip($hashedPhone, $zip)
  {
    global $database;
    $database->beginTransaction();
    $statement = $database->prepare("UPDATE users SET zip_code = :zipCode WHERE phone = :phoneNumber");
    $statement->execute(array(':phoneNumber' => $hashedPhone, ':zipCode' => $zip));
    $database->commit();
  }

  function updateUserServiceId($hashedPhone, $serviceId)
  {
    global $database;
    $database->beginTransaction();
    $statement = $database->prepare("UPDATE users SET service_id = :serviceId WHERE phone = :phoneNumber");
    $statement->execute(array(':phoneNumber' => $hashedPhone, ':serviceId' => $serviceId));
    $database->commit();
  }

  function getProvidersForZip($zip)
  {
    $jsonurl = "http://api.rovicorp.com/TVlistings/v9/listings/services/postalcode/47906/info?locale=en-US&countrycode=US&format=json&apikey=bnp966tdms7t9p5hze264wae";
    $json = file_get_contents($jsonurl);
    $resultObj = json_decode($json);
    $providers = $resultObj->ServicesResult->Services->Service;
    return $providers;
  }

// Print XML Response
$messageEl->appendChild($dom->createTextNode($message));
$responseEl->appendChild($messageEl); 
$dom->appendChild($responseEl);
echo $dom->saveXml();
?>
