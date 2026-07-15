<?php

function invenio_approve_request( $url ) {
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

function invenio_add_community_to_record( $record_id, $community_id ) {
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
            $rc = invenio_approve_request( $approve_url );
			if ( !$rc) { return false; }
        }
    } else {
        return false;
    }

    return true;
}

function invenio_create_community( $community_name, $community_identifier ) {
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

function invenio_search_community( $community_identifier ) {
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
