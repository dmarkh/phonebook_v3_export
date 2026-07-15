<?php

	require_once './auth.php';
	// checkAuth();

	require_once __DIR__ . '/src/ServiceConfig.class.php';
	require_once __DIR__ . '/src/ServiceDb.class.php';
	require_once __DIR__ . '/src/pnb-api.php';

	require_once __DIR__ . '/vendor/autoload.php';
	require_once __DIR__ . '/src/polyfills.php';
	require_once __DIR__ . '/src/get-filters.php';
	require_once __DIR__ . '/src/set-resolvers.php';
	require_once __DIR__ . '/src/get-resolvers.php';
	require_once __DIR__ . '/src/get-phonebook-data.php';
	require_once __DIR__ . '/src/get-phonebook-metadata.php';
	require_once __DIR__ . '/src/get-schema.php';


	use GraphQL\Utils\BuildSchema;
	use GraphQL\GraphQL;
	use GraphQL\Error\FormattedError;
	use GraphQL\Utils;

try {

	$debug = false;

	// create global context
	$metadata = get_phonebook_metadata();
	$dynamicSchema = get_schema( $metadata );
	$schema = BuildSchema::build( $dynamicSchema );

	if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
		$raw = file_get_contents('php://input') ?: '';
		$data = json_decode($raw, true) ? : [];
	} else {
		$data = $_REQUEST;
	}

	$context = get_phonebook_data( ( isset($data['variables']) && isset($data['variables']['entrytime']) ) ? $data['variables']['entrytime'] : false );

	// resolvers
	$resolvers = get_resolvers();
	setResolvers( $resolvers );

	$context['variables'] = isset($data['variables']) ? $data['variables'] : [];

	// execute query
    $result = GraphQL::executeQuery(
	    $schema,
    	$data['query'],
	    null,
    	$context,
	    isset($data['variables']) ? $data['variables'] : []
  	);

    $output = $result->toArray($debug);

} catch (\Exception $error) {
	$httpStatus = 500;
	$output['errors'] = [
		FormattedError::createFromException($error, $debug)
	];
}

	header('Content-Type: application/json; charset=UTF-8', true, 200);
	echo json_encode($output);