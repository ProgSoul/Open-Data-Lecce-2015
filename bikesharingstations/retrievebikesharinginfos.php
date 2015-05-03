<?php

$userGuid = "b4de99c8-bc3e-42ac-9e66-0bbf9019ff22";
$apiKey = "VX+ssJACaAbTXNbxWVf7bI0he+kkirPA0AHeUNxjk+eNDzDUCHo3axrfbz5rgUY1NpzdLZTS80l/Vq2i7JtR5A==";

// Issues a query request to import.io
function query($connectorGuid, $input, $userGuid, $apiKey) {
	$url = "https://query.import.io/store/connector/" . $connectorGuid . "/_query?_user=" . urlencode($userGuid) . "&_apikey=" . urlencode($apiKey);
	return json_decode(file_get_contents($url));
}

// Query for tile getBikeSharingStationsInfos
$queryResult = query("74ca0f38-5e47-4242-a52e-19d971952b15", array(
  "webpage/url" => "http://bicincitta.tobike.it/frmLeStazioni.aspx?ID=159",
), $userGuid, $apiKey, false)->results;

// Decode bike sharing coordinates from json
$bikeSharingCoordinates = json_decode(file_get_contents("bike_sharing_coordinates.json"),true);
//var_dump($bikeSharingCoordinates);

for($i = 0; $i < count($queryResult); $i++) {
	// cleanup values not well formed
	// create status class attribute and delete it from name attribute
	if (strpos($queryResult[$i]->name,' Non operativa') !== false) {
		$queryResult[$i]->name = str_replace(' Non operativa', '', $queryResult[$i]->name);
		$queryResult[$i]->is_operative = "false";
	} else {
		$queryResult[$i]->is_operative = "true";
	}
		
	// find numbers in value attribute and extract them
	preg_match_all('!\d+!', $queryResult[$i]->value, $matches);
	$queryResult[$i]->free_bikes = $matches[0][0];
	$queryResult[$i]->available_places = $matches[0][1];
	
	// once the numbers are extracted, value attribute will be deleted
	unset($queryResult[$i]->value);
	
	// get address and coordinates from json and set class attributes
	$queryResult[$i]->address = $bikeSharingCoordinates[$i]["address"];
	$queryResult[$i]->latitude = $bikeSharingCoordinates[$i]["latitude"];
	$queryResult[$i]->longitude = $bikeSharingCoordinates[$i]["longitude"];
}

echo json_encode($queryResult);
