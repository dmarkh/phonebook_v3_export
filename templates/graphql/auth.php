<?php

function getBearerToken() {
    // 1. Get header from various server possibilities
    $headers = $_SERVER['Authorization'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? null;

    // Apache-specific fallback
    if ( empty($headers) && function_exists('apache_request_headers') ) {
        $reqHeaders = apache_request_headers();
        $headers = $reqHeaders['Authorization'] ?? null;
    }

    // 2. Extract token from "Bearer <token>"
    if ( !empty($headers) && preg_match('/Bearer\s(\S+)/i', $headers, $matches) ) {
        return $matches[1];
    }

    return null;
}

function checkAuth() {

	$token = getBearerToken();
	if ( empty($token) ) {
        http_response_code(401);
        echo "You are not authorized to access this resource.";
        exit;
	}

	$tokens = [
		'sympa'				=> 'xxx',
		'gittea'			=> 'xxx',
		'disk-allocators'	=> 'xxx'
	];

	if ( in_array( $token, array_values($tokens), true ) ) {
		return true;
	}

	http_response_code(401);
	echo "You are not authorized to access this resource.";
	exit;

}


