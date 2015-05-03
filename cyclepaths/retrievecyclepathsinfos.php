<?php

// converts CSV file to array
function convertCSVToArray($csvPath) {
	$result = array();
	if (($handle = fopen($csvPath, "r")) !== FALSE) {
		$column_headers = fgetcsv($handle); // read the row.
		foreach($column_headers as $header) {
			$header = str_replace(';', '', $header); //deletes ';' character from header
			$result[$header] = array();
		}

		while (($data = fgetcsv($handle)) !== FALSE) {
			$i = 0;
			foreach($result as &$column) {
					$column[] = $data[$i++];
			}
		}
		fclose($handle);
	}
	return $result;
}

// get all directories representing a cycle path
$directories = array_filter(glob('*'), 'is_dir');
// defines the result array that will contain every cycle path infos
$cyclePathsCompleteInfos = array();

for ($i = 0; $i < count($directories); $i++) {
	$cyclePathCoordinates = convertCSVToArray($directories[$i]."\cyclepathcoordinates.csv");
	$cyclePathInfos = json_decode(file_get_contents($directories[$i]."\cyclepathinfos.json"),true);
	// merge infos and coordinates and put the new array into the i-th position
	$cyclePathsCompleteInfos[$i] = array_merge($cyclePathCoordinates, $cyclePathInfos);
}

echo json_encode($cyclePathsCompleteInfos);