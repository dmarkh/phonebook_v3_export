<?php


function my_invenio_approve_request( $url ) {
    $cnf =& ServiceConfig::Instance();
    $api_token = $cnf->Get('settings', 'invenio-token');

    $headers = [
        "Accept: application/json",
        "Content-Type: application/json",
        "Authorization: Bearer ".$api_token
    ];

    $data_json = '{"payload":{"content":"accepted","format":"html"}}';

    $curl = curl_init( $url );
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data_json);

    $json_response = curl_exec($curl);
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    if ( $status != 200 && $status != 201 ) {
        return false; // bad request
    }

    $response = json_decode( $json_response, true );
    return $response;
}

function my_invenio_add_community_to_record( $record_id, $community_id ) {
    $cnf =& ServiceConfig::Instance();
    $url = $cnf->Get('settings','invenio-url');
    $api_token = $cnf->Get('settings', 'invenio-token');
    $headers = [
        "Accept: application/json",
        "Content-Type: application/json",
        "Authorization: Bearer ".$api_token
    ];
    $api_url = $url.'/api/records/'.$record_id.'/communities';
    $data = [
        'communities' => [
            [ 'id' => $community_id ]
        ]
    ];

    $data_json = json_encode($data);

    $curl = curl_init($api_url);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data_json);

    $json_response = curl_exec($curl);
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    if ( $status != 200 && $status != 201 ) {
        echo '[add community] return status != 200, it is: '.$status."\n";
        return false; // bad request
    }

    $response = json_decode( $json_response, true );
    if ( $response['processed'] ) {
        $approve_url = $response['processed'][0]['request']['links']['actions']['accept'];
        if ( !empty($approve_url) ) {
            $rc = my_invenio_approve_request( $approve_url );
			if ( !$rc) { return false; }
        }
    } else {
        return false;
    }

    return true;
}

function my_invenio_create_community( $community_name, $community_identifier ) {
    $cnf =& ServiceConfig::Instance();
    $url = $cnf->Get('settings','invenio-url');
    $api_token = $cnf->Get('settings', 'invenio-token');

    $headers = [
        "Accept: application/json",
        "Content-Type: application/json",
        "Authorization: Bearer ".$api_token
    ];

    $api_url = $url.'/api/communities';

    $data = [
        'access' => [
            'visibility' => 'restricted',
            'member_policy' => 'open',
            'record_policy' => 'open'
        ],
        'slug' => $community_identifier,
        'metadata' => [
            'title' => $community_name,
            'description' => 'Community Description',
            'type' => [
                'id' => 'event'
            ],
            'curation_policy' => 'This is the kind of records we accept.',
            'page' => 'Information for my community.',
            'website' => $url,
            'organizations' => [
                [
                    'name' => 'BNL'
                ]
            ]
        ]
    ];
    $data_json = json_encode($data);

    $curl = curl_init($api_url);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data_json);

    $json_response = curl_exec($curl);
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    if ( $status != 200 && $status != 201 ) {
        return false; // bad request
    }

    $response = json_decode( $json_response, true );
    if ( $response && $response['links'] ) {
        return $response['links']['self_html'];
    }
    return ''; // community exists
}

function my_invenio_search_community( $community_identifier ) {
    $cnf =& ServiceConfig::Instance();
    $url = $cnf->Get('settings','invenio-url');
    $api_token = $cnf->Get('settings', 'invenio-token');

    $api_url = $url.'/api/communities?q='.urlencode($community_identifier).'&sort=bestmatch';
    $headers = [
        "Accept: application/json",
        "Content-Type: application/json",
        "Authorization: Bearer ".$api_token
    ];

    $curl = curl_init($api_url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

    $json_response = curl_exec($curl);
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    if ( $status != 200 && $status != 201 ) {
        return false; // bad request
    }

    $response = json_decode($json_response, true);

    if ( $response && $response['hits'] && $response['hits']['hits'] ) {
        foreach( $response['hits']['hits'] as $k => $v ) {
            if ( $v['slug'] === $community_identifier) {
                return $v['links']['self_html'];
                break;
            }
        }
        return ''; // there were results, but community not found
    }
    return ''; // no results, community not found
}

function my_invenio_make_curl_request($method, $url, $headers, $data = null) {
    $ch = curl_init($url);
    
    // Set headers
    $formattedHeaders = [];
    foreach ($headers as $key => $value) {
        $formattedHeaders[] = "$key: $value";
    }

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $formattedHeaders);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    
    // Equivalent to verify=False in Python
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    if ($data) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    $response = curl_exec($ch);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if ( curl_errno($ch) ) {
		die('Curl error: ' . curl_error($ch));
    }
    
    curl_close($ch);

    return [
        'status_code' => $statusCode,
        'body' => json_decode($response, true)
    ];
}

function my_invenio_get_record($api_url, $header, $record_id) {
    $url = "{$api_url}/records/{$record_id}/draft";
    $response = my_invenio_make_curl_request('POST', $url, $header);
    if ( $response['status_code'] !== 201 ) {
        // die("Failed to update record (code: {$response['status_code']})\n");
		return false;
    }
    return $response['body'];
}

function my_invenio_update_record_draft($api_url, $header, $record_id, $record_data) {
    $url = "{$api_url}/records/{$record_id}/draft";
    $response = my_invenio_make_curl_request('PUT', $url, $header, $record_data);
    if ( $response['status_code'] !== 200 ) {
        // die("Failed to update record (code: {$response['status_code']})\n");
		return false;
    }
    return $response['body'];
}

function my_invenio_publish_record_draft($api_url, $header, $record_id) {
    $url = "{$api_url}/records/{$record_id}/draft/actions/publish";
    $response = my_invenio_make_curl_request('POST', $url, $header);
    if ( $response['status_code'] !== 202 ) {
        // die("Failed to update record (code: {$response['status_code']})\n");
		return false;
    }
    return $response['body'];
}

function my_invenio_change_record_access( $record_id, $record_files_access = 'public') {
    $cnf =& ServiceConfig::Instance();
    $api_url = $cnf->Get('settings','invenio-url').'/api';
    $api_token = $cnf->Get('settings', 'invenio-token');

	$header = [
    	"Accept" => "application/json",
	    "Content-Type" => "application/json",
    	"Authorization" => "Bearer $api_token"
	];
    $orig_record = my_invenio_get_record($api_url, $header, $record_id);
	if ( $orig_record === false ) { return false; }
    $record_access = $orig_record['access'];

    $record_access['files'] = $record_files_access;
    $record_access['record'] = $record_files_access;
    $orig_record['access'] = $record_access;

    $updated_record = my_invenio_update_record_draft($api_url, $header, $record_id, $orig_record);
	if ( $updated_record === false ) { return false; }

	return my_invenio_publish_record_draft($api_url, $header, $record_id);
}

