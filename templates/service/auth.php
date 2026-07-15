<?php

require_once( dirname(__FILE__).'/member-lookup-by-orcid.php' );

function get_roles() {
	return [
        'GUEST' => [
			'groups-view' => 1,
            'access-phonebook' => 1,
            'authorlists-view' => 1,
            'institutions-view' => 1,
            'representatives-view' => 1,
            'members-view' => 1,
			'worldmap-view' => 1,
            'fields-public-view' => 1
        ],

        'MEMBER' => [
			'groups-view' => 1,
            'access-phonebook' => 1,
            'authorlists-view' => 1,
            'institutions-view' => 1,
            'representatives-view' => 1,
            'members-view' => 1,
			'reviews-view' => 1,
            'fields-public-view' => 1,
			'worldmap-view' => 1,

            'group-tasks-view' => 0,
            'group-members-view' => 1,
            'institution-tasks-view' => 1,
            'member-tasks-view' => 1,

			'assigned-tasks-edit' => 1,

			'own-task-view' => 1,
			'own-task-create' => 1,
			'own-task-edit' => 1,

            'representative-task-create' => 1,
            'representative-task-edit' => 1,

            'members-view-details' => 1,

			'statistics-view' => 1
        ],
        'EDITOR' => [
            'access-phonebook' => 1,
            'authorlists-view' => 1,
            'representatives-view' => 1,
			'worldmap-view' => 1,

            'members-view' => 1,
            'members-create' => 1,
            'members-edit' => 1,
            'members-history' => 1,
			'members-assign-role' => 1,

            'institutions-view' => 1,
            'institutions-create' => 1,
            'institutions-edit' => 1,
            'institutions-history' => 1,
            'fields-public-view' => 1,
            'fields-private-view' => 1,
            'members-view-details' => 1,
			'statistics-view' => 1
        ],
        'ADMIN' => [
            'access-phonebook' => 1,
            'authorlists-view' => 1,
            'representatives-view' => 1,
			'worldmap-view' => 1,

			'documents-view' => 1,
			'documents-create' => 1,
			'documents-edit' => 1,
			'documents-admin' => 1,
			'documents-history' => 1,

			'tasks-view' => 1,
			'tasks-create' => 1,
			'tasks-edit' => 1,
			'tasks-history' => 1,

			'assigned-tasks-view' => 1,
			'assigned-tasks-edit' => 1,

			'own-task-view' => 1,
			'own-task-create' => 1,
			'own-task-edit' => 1,

			'group-tasks-view' => 1,
			'group-tasks-edit' => 1,
			'group-tasks-create' => 1,
			'group-members-view' => 1,

			'institution-tasks-view' => 1,
			'institution-tasks-edit' => 1,
			'institution-tasks-create' => 1,

			'member-tasks-view' => 1,
			'member-tasks-edit' => 1,
			'member-tasks-create' => 1,

			'representative-task-create' => 1,
			'representative-task-edit' => 1,

            'members-view-details' => 1,

			'events-view' => 1,
			'events-create' => 1,
			'events-edit' => 1,
			'events-history' => 1,

            'groups-view' => 1,
            'groups-create' => 1,
            'groups-edit' => 1,

            'members-view' => 1,
            'members-tasks' => 1,
            'members-groups' => 1,
            'members-documents' => 1,
            'members-create' => 1,
            'members-edit' => 1,
            'members-history' => 1,
            'members-bulk-import' => 1,
            'members-bulk-update' => 1,
			'members-changes' => 1,

			'members-assign-role' => 1,

            'institutions-view' => 1,
            'institutions-create' => 1,
            'institutions-edit' => 1,
            'institutions-history' => 1,
            'institutions-bulk-import' => 1,
            'institutions-bulk-update' => 1,
			'institutions-changes' => 1,

			'workflows-view' => 1,
			'workflows-edit' => 1,
			'comments-add' => 1,
			'comments-view' => 1,
			'reviews-view' => 1,

            'descriptors-view' => 1,
            'descriptors-edit' => 1,
            'fields-public-view' => 1,
            'fields-private-view' => 1,
            'fields-admin-view' => 1,

			'event-poll-create' => 1,
			'event-poll-view' => 1,

			'event-display-view' => 1,
			'ai-tools' => 1,
			'statistics-view' => 1,

			'statistics-view-tasks' => 1,
			'statistics-view-per-institution' => 1,
			'statistics-view-per-group' => 1,
			'statistics-view-per-task' => 1
        ],
		'CLI' => [
            'access-phonebook' => 1,
            'representatives-view' => 1,
            'members-view' => 1,
            'members-create' => 1,
            'members-edit' => 1,
            'members-history' => 1,
            'institutions-view' => 1,
            'institutions-create' => 1,
            'institutions-edit' => 1,
            'institutions-history' => 1,
            'descriptors-view' => 1,
            'fields-public-view' => 1,
            'fields-private-view' => 1,
            'fields-admin-view' => 1
		]
    ];
}

function get_accounts() {
    return [
        'guest' => [
            'pass' => 'guest',
            'role' => 'GUEST',
            'token' => 'xxx'
        ],
        'member' => [
            'pass' => 'member',
            'role' => 'MEMBER',
            'token' => 'xxx'
        ],
        'admin' => [
            'pass' => 'epic2030',
            'role' => 'ADMIN',
            'token' => 'xxx',
			'orcid' => '0000-0000-0000-0000'
        ],
        'cli' => [
            'pass' => 'cli',
            'role' => 'CLI',
            'token' => 'xxx'
        ],
		'foo@bar.gov' => [
			'role' => 'ADMIN',
			'pass' => 'xxx',
			'token'=> 'xxx',
			'orcid' => '0000-0000-0000-0000'
		]
    ];
}

function get_token() {
	$headers = apache_request_headers();
	$token = false;
	if ( isset($headers['Authorization']) ) {
    	$headers = trim( $headers['Authorization'] );
		if ( preg_match('/Bearer\s(\S+)/', $headers, $matches) ) {
            $token = $matches[1];
        }
    }
	if ( $token === false ) {
		if ( !empty( $_GET['token'] ) ) {
			$token = $_GET['token'];
		} else if ( !empty( $_POST['token'] ) ) {
			$token = $_POST['token'];
		}
	}
	return $token;
}

function authenticate() {

	$token = get_token();
	$roles = get_roles();
	$auth = get_accounts();
	$headers = apache_request_headers();

	$login = empty($_POST['login']) ? 'N/A' : $_POST['login'];
	$pass  = empty($_POST['pass']) ? 'N/A' : $_POST['pass'];

	if ( !empty($token) ) {
		// already authenticated

		foreach ( $auth as $k => $account ) {
			if ( $account['token'] === $token ) { return true; }
		}

	} else if ( !empty($login) && !empty($pass) ) {
		// auth by login / password

		if ( !empty( $auth[ $login ] ) && $auth[ $login ]['pass'] === $pass ) {
        	$res = [
		    	'token' => $auth[ $login ]['token'],
            	'role' => $auth[ $login ]['role'],
         		'grants' => $roles[ $auth[ $login ]['role'] ]
        	];
			if ( !empty( $auth[ $login ]['orcid'] ) ) {
				$data = member_lookup_by_orcid( $auth[ $login ]['orcid'] );
				if ( !empty($data) ) {
					if ( !empty($data['orcid']) ) {
						$res['orcid'] = $data['orcid'];
					}
					if ( !empty($data['mid']) ) {
						$res['mid'] = $data['mid'];
					}
					if ( !empty($data['member_role']) ) {
						$res['member_role'] = $data['member_role'];
					}
					if ( !empty($data['name_first']) || !empty($data['name_last']) ) {
						$res['name'] = $data['name_first'].' '.$data['name_last'];
					}
				}
			}
			if ( $res['role'] == 'ADMIN' ) {
				$res['roles'] = get_roles();
			}
			return $res;
		}
	} else if ( !empty($headers) ) {

		// auth by OpenID
        $email = isset( $headers['OIDC_CLAIM_email'] ) ? $headers['OIDC_CLAIM_email'] : false;
        $orcid = isset( $headers['OIDC_CLAIM_orcid'] ) ? $headers['OIDC_CLAIM_orcid'] : false;

        $res = [
 	       'pass' => $auth['guest']['pass'],
           'token' => $auth['guest']['token'],
           'role' => 'GUEST',
           'grants' => $roles['GUEST']
        ];

        if ( !empty($email) ) {

            if ( !empty($auth[$email]) && isset($auth[$email] ) ) {
                $res = [
                    'email' => $email,
                    'pass' => $auth[$email]['pass'],
                    'token' => $auth[ $email ]['token'],
                    'role' => $auth[ $email ]['role'],
                    'grants' => $roles[ $auth[ $email ]['role'] ]
                ];
            }

            if ( !empty( $orcid ) ) {
                $res['orcid'] = $orcid;
                $data = member_lookup_by_orcid( $orcid );
                if ( !empty($data) ) {
                    if ( !empty($data['orcid']) ) {
                        $res['orcid'] = $data['orcid'];
                    }
                    if ( !empty($data['mid']) ) {
                        $res['mid'] = $data['mid'];
                    }
					if ( !empty($data['member_role']) ) {
						$res['member_role'] = $data['member_role'];
					}
					if ( $res['role'] == 'GUEST' ) {
	                    $res['pass'] = $auth['member']['pass'];
    	               	$res['token'] = $auth['member']['token'];
        	            $res['role'] = 'member';
            	        $res['grants'] = $roles['MEMBER'];
					}
					if ( !empty($data['name_first']) || !empty($data['name_last']) ) {
						$res['name'] = $data['name_first'].' '.$data['name_last'];
					}
                }
            }
			if ( $res['role'] == 'ADMIN' ) {
				$res['roles'] = get_roles();
			}
            return $res;
        }
	}

	return false;
}
