<?php
# CartoDB Info
$cartodb_username = 'cynrm';
$cartodb_api_key = 'ff723434c587cd2386dd78d07df09039bc3c5b3c';
$table = 'sea_turtle_monitor_nest_1';

# Fulcrum Info
$form_id = '42b78b8c-30eb-41c6-87dc-19d533e5e15f';
$fulcrum_api_key = '01b49c9f022e02216d2af99321386060ede9af48759f5d92d26bdef73f76db3f';

//$input = file_get_contents('payload.json'); # local file for testing
$input = file_get_contents('php://input'); # POST data from webhook
$data = json_decode($input, true);

# Build key/data_name lookup arrays for non Section fields
function lookup($array) {
    global $data_name, $label, $id;
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            if (isset($value['key']) && $value['type'] !== 'Section') {
                $data_name[$value['key']] = $value['data_name'];
                $label[$value['key']] = $value['label'];
                $id[$value['data_name']] = $value['key'];
            } else {
                lookup($value);
            }
        }
    }
}

# Fetch form definitions to build key/data_name lookup arrays
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
curl_setopt($ch, CURLOPT_URL, 'https://api.fulcrumapp.com/api/v2/forms');
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'X-ApiToken: ' . $fulcrum_api_key
));
$forms_json = curl_exec($ch);
$forms = json_decode($forms_json, TRUE);
foreach ($forms as $key => $value) {
    if ($key === 'forms') {
        foreach ($value as $formKey => $formValue) {
            foreach ($formValue as $elementKey => $elementValue) {
                if ($elementKey === 'elements') {
                    # Loop through nested elements arrays for non Section types and build key/data_name lookup array
                    lookup($elementValue);
                }
            }
        }
    }
}

# Make sure it's the form we want
if ($data['data']['form_id'] == $form_id) {
	
	$formArray = array(); # Array to hold form fields
	$addressArray = array(); # Array to hold address fields
	
	# Loop through form values and format values
	foreach ($data['data']['form_values'] as $key => $value) {
	   /*
	    # Get address objects in logical order
	    if (isset($value['sub_thoroughfare'])) {    
		    array_push($addressArray, $value['sub_thoroughfare']);
		}
		if (isset($value['thoroughfare'])) {    
		    array_push($addressArray, $value['thoroughfare']);
		}
		if (isset($value['suite'])) {    
		    array_push($addressArray, $value['suite']); 
		}
		if (isset($value['locality'])) {    
		    array_push($addressArray, $value['locality']); 
		}
		if (isset($value['admin_area'])) {    
		    array_push($addressArray, $value['admin_area']); 
		}
		if (isset($value['sub_admin_area'])) {    
		    array_push($addressArray, $value['sub_admin_area']); 
		}
		if (isset($value['postal_code'])) {    
		    array_push($addressArray, $value['postal_code']); 
		}
		if (isset($value['country'])) {    
		    array_push($addressArray, $value['country']); 
		}
		if (isset($value['sub_thoroughfare']) || isset($value['thoroughfare']) || isset($value['suite']) || isset($value['locality']) || isset($value['admin_area']) || isset($value['sub_admin_area']) || isset($value['postal_code']) || isset($value['country'])) {
			$value = implode(' ', array_filter($addressArray));
		}
	    */
	    # Join choice values & other values and convert to string
	    if (isset($value['choice_values']) && is_array($value['choice_values'])) {
	        if (isset($value['other_values'])) {
	            $value = array_merge($value['choice_values'], $value['other_values']);
	        }
	        $value = implode(', ', $value);
	    }
	    
	    # If it's an array of objects (photos or repeatables), just give us a string of the id's
	    if (is_array($value)) {
	        $objectArray = array();
            $objectArray1 = array();
	        foreach ($value as $objectKey => $objectValue) {
	            if (isset($objectValue['photo_id'])) {
	            	$object['id'] = $objectValue['photo_id'];
	            }
	            if (isset($objectValue['id'])) {
	            	$object['id'] = $objectValue['id'];
	            }
                if (isset($objectValue['caption'])) {
	            	$object['id1'] = $objectValue['caption'];
	            }
	            array_push($objectArray, $object['id']);
	           array_push($objectArray1, $object['id1']);
            }
	        $value = implode(', ', $objectArray);
	    }
	    $formArray[$key] = $value;
	    # Inspect local payload.json for testing
	    //echo $data_name[$key] . ': ' . $formArray[$key] . '<br>';
	}

	# Standard Fulcrum fields
	$fulcrum_id = $data['data']['id'];
	$status = $data['data']['status'];
	$version = $data['data']['version'];
	$form_id = $data['data']['form_id'];
	$form_version = $data['data']['form_version'];
	$project_id = $data['data']['project_id'];
	$created_at = $data['data']['created_at'];
	$updated_at = $data['data']['updated_at'];
	$client_created_at = $data['data']['client_created_at'];
	$client_updated_at = $data['data']['client_updated_at'];
	$created_by = $data['data']['created_by'];
	$created_by_id = $data['data']['created_by_id'];
	$updated_by = $data['data']['updated_by'];
	$updated_by_id = $data['data']['updated_by_id'];
	$assigned_to = $data['data']['assigned_to'];
	$assigned_to_id = $data['data']['assigned_to_id'];
	$latitude = $data['data']['latitude'];
	$longitude = $data['data']['longitude'];
	$altitude = $data['data']['altitude'];
	$speed = $data['data']['speed'];
	$course = $data['data']['course'];
	$horizontal_accuracy = $data['data']['horizontal_accuracy'];
	$vertical_accuracy = $data['data']['vertical_accuracy'];

	# Custom form fields- modify with Data Name value from the Fulcrum form builder
	# Check if field is in payload and return an empty string if not
	function field($data_name) {
	    global $formArray, $id;
	 	return (isset($formArray[$id[$data_name]]) ? $formArray[$id[$data_name]] : '');   
	}
    if ($project_id == '14ade468-b26a-46dd-8493-6c7c5457233b')
    {
        $project = 'SCYC';
    }
    elseif($project_id == '3fa60cf6-5645-4ad3-83c0-047ad7f2c24e' )
    {
        $project = 'Coastal Rangers';
    }
    else
    {
        $project = '- No Project -';
    }
	$nest_id_number = field('nest_id_number');
	$ranger_collecting = field('ranger_collecting');
    $nest_condition = field('nest_condition');
	$marker_removed = field('marker_removed');
	$count_shells = (field('count_shells') ? "'" . field('count_shells') . "'" : "null");
	$actions_taken = field('actions_taken');
	$eggs_remaining = field('eggs_remaining');
	$protection_provided = field('protection_provided');
    $comments = field('comments');
    
	$picture = field('picture');
    $caption = $objectArray1[0];
    
    $predation_source = field('predation_source');
    $predation_extent = field('predation_extent');
    $damage_source =  field('damage_source');
    

	# Create new CartoDB record (use PostgreSQL dollar quoting)
	# FYI- CartoDB seems to convert all fields on imported CSV files to string
	if ($data['type'] == 'record.create') {
		$sql = "INSERT INTO $table (the_geom, fulcrum_id, version, nest_id_number, created_by, updated_by, assigned_to, project, ranger_collecting, nest_condition, marker_removed, count_shells, actions_taken, eggs_remaining, protection_provided, picture, status, protection_provided_other, predation_source, predation_extent, damage_source, picture_caption) VALUES (ST_SetSRID(ST_MakePoint($longitude,$latitude),4326), '$fulcrum_id', '$version', '$nest_id_number', '$created_by', '$updated_by', '$assigned_to', '$project', '$ranger_collecting', '$nest_condition', '$marker_removed', $count_shells, '$actions_taken', '$eggs_remaining', '$protection_provided', '$picture', '$status', '$comments', '$predation_source', '$predation_extent', '$damage_source', '$caption')";
	   //$sql = "INSERT INTO $table (the_geom, fulcrum_id, nest_id_number) VALUES (ST_SetSRID(ST_MakePoint($longitude,$latitude),4326), '$fulcrum_id', '$nest_id_number')";
    }
	# Update existing CartoDB record (use PostgreSQL dollar quoting)
	if ($data['type'] == 'record.update') {
		$sql = "UPDATE $table SET (the_geom, version, nest_id_number, created_by, updated_by, assigned_to, project, ranger_collecting, nest_condition, marker_removed, count_shells, actions_taken, eggs_remaining, protection_provided, picture, status, protection_provided_other, predation_source, predation_extent, damage_source, picture_caption) = (ST_SetSRID(ST_MakePoint($longitude,$latitude),4326), '$version', '$nest_id_number', '$created_by', '$updated_by', '$assigned_to', '$project', '$ranger_collecting', '$nest_condition', '$marker_removed', $count_shells, '$actions_taken', '$eggs_remaining', '$protection_provided', '$picture', '$status', '$comments', '$predation_source', '$predation_extent', '$damage_source', '$caption') WHERE fulcrum_id = $$$fulcrum_id$$;";
	}
	# Delete existing CartoDB record
	if ($data['type'] == 'record.delete') {
		$sql = "DELETE FROM $table WHERE fulcrum_id = $$$fulcrum_id$$;";
	}

	# Wire up cURL to POST SQL to CartoDB 
	$ch = curl_init('https://'.$cartodb_username.'.cartodb.com/api/v2/sql');
	$query = http_build_query(array('q'=>$sql,'api_key'=>$cartodb_api_key));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
	curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	$result = curl_exec($ch);
	curl_close($ch);

	# Write SQL out to file for inspection
	$text = fopen('cartodb.sql', 'w+');
	fwrite($text, $sql);
	fclose($text);

	# Write payload out to file for inspection
	$json = json_encode($input);
	$data = json_decode($json, true);
	$payload = fopen('payload.json', 'w+');
	fwrite($payload, $data);
	fclose($payload);
    
    # write form values
    $text1 = fopen('muz_test.sql', 'w+');
	fwrite($text1, print_r($formArray, TRUE));
	fclose($text1);
    
    $text1 = fopen('array_test.sql', 'w+');
	fwrite($text1, print_r($objectArray1, TRUE));
	fclose($text1);
}
?>