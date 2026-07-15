<?php 

require_once('./service/auth.php');

$auth = authenticate();

$audata_id = false;
$audata_tk = false;
$audata_rl = false;
$orcid = false;
$mid = false;
$access = '';

if ( !empty($auth) && is_array($auth) ) {
    $orcid = !empty($auth['orcid']) ? $auth['orcid'] : false;
    $mid   = !empty($auth['mid'])   ? $auth['mid']   : false;
    if ( !empty($auth['email']) ) {
        $audata_id = $auth['email'];
        $audata_tk = $auth['pass'];
    } else {
        $audata_id = isset( $auth['role'] ) ? $auth['role'] : 'GUEST';
        $audata_tk = isset( $auth['pass'] ) ? $auth['pass'] : 'guest';
    }
	if ( !empty($auth['member_role']) ) {
		$audata_rl = $auth['member_role'];
	}
} else {
    $audata_id = false;
    $audata_tk = false;
	$audata_rl = false;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset='utf-8'>
	<meta name='viewport' content='width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no'>
	<title>Unknown Collaboration</title>
	<script>
		window.pnb = {
            <?php if ( $orcid ) { ?>
            "orcid": "<?php echo $orcid; ?>",
            <?php } ?>
            <?php if ( $mid ) { ?>
            "mid": <?php echo $mid; ?>,
            <?php } ?>
			"title": "UNKNOWN COLLABORATION",
			"collaboration": "UNKNOWN",
			"graphql": "/graphql/unknown/",
			"build-date": "2026-06-15",
			"basepath": "/phonebook-mui/client",
			"router": "hash",
			"service": "service/",
			"keepalive-interval": 10,
			"logo": {
				"width" : 15,
				"height": 10
			},
			"inspire": {
				"collaboration_id": "unknown",
				"foafName": "UNKNOWN",
				"experimentNumber": "UNKNOWN"
			},
			"worldmap": {
				"marker": {
					"type"   : "dot",
					"radius" : 7,
					"color"  : "#FF0000",
					"bcolor" : "#FFFFFF",
					"bsize"  : 2
				}
			},
			"stats": {
				"tasks": {
					"current-year" : 2026,
					"selected-years": [ 2024, 2025, 2026, 2027 ]
				}
			},
			"self-member-fields": {
				"ORCID": "orcid_id", "FIRST NAME": "name_first", "LAST NAME": "name_last",
				"EMAIL": "email", "ALT EMAIL": "email_alt", "GITHUB ID": "github_login" 
			},
			"self-institution-fields": {
				"INSTITUTION": "name_full", "INSTITUTION ROR ID": "ror_id",
				"COUNTRY": "country", "REGION": "region", "WEBSITE": "website_institution" 
			},
			"self-edit": [
				"name_first", "name_last", "email", "email_alt", "github_login"
			],
			"representative-edit": [
				"orcid_id", "name_first", "name_last", "email", "email_alt", "github_login"
			],
			"institutions": [
				{ "title": "FULL NAME OF THE INSTITUTION", "field": "name_full", "align": "left", "width": "55%" },
				{ "title": "SHORT NAME", "field": "name_short", "align": "left", "width": "15%" },
				{ "title": "COUNTRY", "field": "country", "align": "left", "width": "15%" },
				{ "title": "REGION", "field": "region", "align": "left", "width": "15%" }
			],
			"documents": [
				{ "title": "DocID", "field": "reference_id", "align": "center", "width": "5%" },
				{ "title": "CREATED", "field": "ts", "align": "center", "width": "5%" },
				{ "title": "CATEGORY", "field": "category", "align": "center", "width": "20%" },
				{ "title": "TITLE", "field": "title", "align": "left", "width": "60%" },
				{ "title": "OWNER", "field": "author_id", "align": "center", "width": "5%" },
				{ "title": "GROUP", "field": "group_id", "align": "center", "width": "5%" }
			],
			"tasks": [
				{ "title": "ID", "field": "id", "align": "center", "width": "5%" },
				{ "title": "TITLE", "field": "title", "align": "center", "width": "35%" },
				{ "title": "DECRIPTION", "field": "desc", "align": "center", "width": "50%" }
			],
			"events": [
				{ "title": "START TIME", "field": "start_time", "align": "center", "width": "10%" },
				{ "title": "END TIME", "field": "end_time", "align": "center", "width": "10%" },
				{ "title": "CATEGORY", "field": "category", "align": "center", "width": "10%" },
				{ "title": "NAME", "field": "name", "align": "center", "width": "60%" },
				{ "title": "LOCATION", "field": "location", "align": "center", "width": "10%" }
			],
			"workflows": [
				{ "title": "NAME", "field": "name", "align": "center", "width": "20%" },
				{ "title": "DESCRIPTION", "field": "description", "align": "left", "width": "70%" },
				{ "title": "WEIGHT", "field": "weight", "align": "center", "width": "10%" },
			],
			"members": [
				{ "title": "FIRST NAME", "field": "name_first", "align": "right", "width": "20%" },
				{ "title": "LAST NAME", "field": "name_last", "align": "left", "width": "20%" },
				{ "title": "EMAIL", "field": "email", "align": "left", "width": "20%" },
				{ "title": "INSTITUTION", "field": "institution__name_full", "align": "left", "width": "20%" },
				{ "title": "COUNTRY", "field": "institution__country", "align": "left", "width": "20%" }
			],
			"inst.members": [
				{ "title": "ROLE", "field": "member_role", "align": "right", "width": "16%" },
				{ "title": "FIRST NAME", "field": "name_first", "align": "right", "width": "16%" },
				{ "title": "LAST NAME", "field": "name_last", "align": "left", "width": "16%" },
				{ "title": "EMAIL", "field": "email", "align": "left", "width": "16%" },
				{ "title": "ORCID", "field": "orcid_id", "align": "left", "width": "16%" },
				{ "title": "IS AUTHOR?", "field": "is_author", "align": "left", "width": "16%" }
			],
			"representatives": [
				{ "title": "ROLE", "field": "member_role", "align": "center", "width": "16.6%" },
				{ "title": "FIRST NAME", "field": "name_first", "align": "right", "width": "16.6%" },
				{ "title": "LAST NAME", "field": "name_last", "align": "left", "width": "16.6%" },
				{ "title": "ORCID", "field": "orcid_id", "align": "center", "width": "16.6%" },
				{ "title": "EMAIL", "field": "email", "align": "left", "width": "16.6%" },
				{ "title": "INSTITUTION", "field": "institution__name_full", "align": "left", "width": "16.6%" },
				{ "title": "COUNTRY", "field": "institution__country", "align": "left", "width": "16.6%" }
			],
			"filter-institutions": {
				"display-fields": [ "id", "ror_id", "name_full", "name_short", "country", "region" ],
				"sort-fields": [ "region", "country", "name_full" ]
			},
			"filter-members": {
				"display-fields": [ "id", "orcid_id", "name_first", "name_last", "email", "institution__name_full" ],
				"sort-fields": [ "institution__name_full", "name_last", "name_first" ]
			},
			"filter-documents": {
				"display-fields": [ "id", "ts", "owner", "title", "group__name", "category" ],
				"sort-fields": [ "title" ]
			},
			"filter-tasks": {
				"display-fields": [ "id", "title" ],
				"sort-fields": [ "title" ]
			},
			"filter-events": {
				"display-fields": [ "id", "name" ],
				"sort-fields": [ "name" ]
			},
			"filter-representatives": {
				"display-fields": [ "id", "orcid_id", "member_role", "name_first", "name_last", "email", "institution__name_full", "country" ],
				"sort-fields": [ "institution__name_full", "name_last", "name_first" ]
			},
			"allow-guest-access": 1,
			"graph": {
				"nodewidth": 450,
				"nodeheight": 150
			},
			"xlsx": {
				"institutions-export": "unknown-institutions",
				"members-export": "unknown-members",
				"representatives-export": "unknown-representatives",
				"groups-export": "unknown-groups",
				"tasks-export": "unknown-tasks",
				"assigned-tasks-export": "unknown-assigned-tasks",
				"documents-export": "unknown-documents",
				"institution-tasks-export": "institution-tasks"
			},
			"ai": {
				"modes": {
					"ask-scientist"	: [ "google/gemma-3-27b-it:free", "meta-llama/llama-3.3-70b-instruct:free", "openai/gpt-oss-20b:free" ],
			        "ask-coder"		: [ "google/gemma-3-27b-it:free", "meta-llama/llama-3.3-70b-instruct:free", "openai/gpt-oss-20b:free", "qwen/qwen3-coder:free", "qwen/qwen-2.5-coder-32b-instruct:free" ],
			        "analyze-paper"	: [ "google/gemma-3-27b-it:free", "meta-llama/llama-3.3-70b-instruct:free", "openai/gpt-oss-20b:free" ],
				},
				"k": "xxx",
				"search": true
			},
			"audata": {
				"id": "<?php echo $audata_id; ?>",
				"tk": "<?php echo $audata_tk; ?>",
				"rl": "<?php echo $audata_rl; ?>"
			}
		};
		for ( const k of Object.keys( window.pnb ) ) {
			if ( k !== 'orcid' && k !== 'mid' ) { Object.seal( window.pnb[k] ); }
		}
	<link rel="preconnect" href="https://fonts.googleapis.com" />
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
	<link href="https://fonts.googleapis.com/css2?family=Material+Icons&Roboto+Mono:ital@0;1&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet"/>
	<style></style>

	<link id="page_favicon" href="favicon/favicon.ico" rel="icon" type="image/x-icon" />
	<link rel="apple-touch-icon" sizes="180x180" href="favicon/apple-touch-icon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="favicon/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="favicon/favicon-16x16.png">
	<link rel="manifest" href="favicon/site.webmanifest">

	<style id="dynamic-style"></style>
</head>
<body tabindex=-1>
	<div id="app-container"></div>
</body>
</html>
