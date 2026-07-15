<?php

function service_auth_handler( $params ) {

	$res = false;

	$roles = [
		'GUEST' => [
            'access-phonebook' => 1,
   	        'authorlists-view' => 1,
       	    'institutions-view' => 1,
           	'members-view' => 1,
            'descriptors-view' => 1,
   	        'fields-public-view' => 1
		],
		'ADMIN' => [
            'access-phonebook' => 1,
   	        'authorlists-view' => 1,
            'members-view' => 1,
            'members-create': 1,
   	        'members-edit': 1,
       	    'members-history': 1,
           	'members-bulk-import': 1,
            'members-bulk-update': 1,
   	        'institutions-view': 1,
       	    'institutions-create': 1,
           	'institutions-edit': 1,
            'institutions-history': 1,
   	        'institutions-bulk-import': 1,
       	    'institutions-bulk-update': 1,
            'descriptors-view': 1,
   	        'descriptors-edit': 1,
       	    'fields-public-view': 1,
   	        'fields-private-view': 1,
            'fields-admin-view': 1
		]
	];

	$auth = [
		'guest' => [
			'pass' => 'guest',
			'role' => 'GUEST',
			'token' => '123123123'
		],
		'admin' => [
			'pass' => 'admin',
			'role' => 'ADMIN',
			'token' => '345345345'
		]
	];

	if ( !empty( $auth[ $params['login'] ] ) && $auth[ $params['login'] ]['pass'] === $params['pass'] ) {
		$res = [
			'role' => $auth[ $params['login'] ]['role'],
			'token' => $auth[ $params['login'] ]['token'],
			'grants' => $roles[ $auth[ $params['login'] ]['role'] ]
		];
	} else {
		$res = false;
	}

    return json_encode( $res );
}