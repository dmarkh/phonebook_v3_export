<?php

function workflows_addprogress_handler($params) {
  $cnf =& ServiceConfig::Instance();
  $db =& ServiceDb::Instance('phonebook_api');
  $db_name = $cnf->Get('phonebook_api','database');

  if ( empty($params['data']) ) { return json_encode(false); }
  if (
	!isset($params['data']['document_id']) ||
	!isset($params['data']['workflow_id']) ||
	!isset($params['data']['step_id']) ||
	!isset($params['data']['operation'])
  ) { return json_encode(false); }

	if ( !isset($params['data']['member_id'] ) ) {
		$params['data']['member_id'] = 0;
	}
	if ( !isset($params['data']['member_name'] ) ) {
		$params['data']['member_name'] = '';
	}
	if ( !isset($params['data']['metadata'] ) ) {
		$params['data']['metadata'] = '';
	}

	$document_id = intval($params['data']['document_id']);
	$workflow_id = intval($params['data']['workflow_id']);
	$step_id = intval($params['data']['step_id']);
	$member_id = intval($params['data']['member_id']);
	$member_name = $params['data']['member_name'];
	$operation = $params['data']['operation'];
	$metadata = $params['data']['metadata'];

#  `document_id` int unsigned NOT NULL,
#  `workflow_id` int unsigned NOT NULL,
#  `step_id` int unsigned NOT NULL,
#  `member_id` int unsigned NOT NULL,
#  `member_name` varchar(255) NOT NULL,
#  `operation` enum('notify-member','notify-maillist','decline','accept','comment') NOT NULL DEFAULT 'comment',
#  `metadata` varchar (2048) NOT NULL DEFAULT '',

  $query = 'INSERT INTO `'.$db_name.'`.`workflows_progress` (`workflow_id`, `document_id`, `step_id`, `member_id`, `member_name`, `operation`, `metadata` )'
	.' VALUES ('.$workflow_id.', '.$document_id.', '.$step_id.', '.$member_id.', "'.$db->Escape($member_name).'", "'.$db->Escape($operation).'", "'. $db->Escape($metadata).'")';

  $db->Query($query);
  $id = $db->LastID();
  if ( empty($id) ) {
 	return json_encode(false);
  }

  switch( $operation ) {
	case 'decline':

        $cnf =& ServiceConfig::Instance();
        $collaboration = $cnf->Get('settings', 'collaboration');
        $document_url = $cnf->Get('settings', 'url') . '/document/' . $document_id . '/workflow';
        $document_title = wfl_get_document_field( $document_id, 'title' );
        $subject = '[CRISP-'.$cnf->Get('settings','collaboration').'] negative review for '.$document_title;
        $message = 'Hello,'."\n"
			.$member_name.' has declined your document: '."\n\n"
            .$document_title."\n"
            .$document_url."\n\n"
			.'REVIEWER COMMENT:'."\n"
			.$metadata."\n\n"
            .'Automated CRISP Workflow Engine'."\n"
            .'Please do not reply to this email'."\n";
        wfl_email_to_owner( $document_id, $subject, $message );
		break;

	case 'accept':

        $cnf =& ServiceConfig::Instance();
        $collaboration = $cnf->Get('settings', 'collaboration');
        $document_url = $cnf->Get('settings', 'url') . '/document/' . $document_id . '/workflow';
        $document_title = wfl_get_document_field( $document_id, 'title' );
        $subject = '[CRISP-'.$cnf->Get('settings','collaboration').'] positive review for '.$document_title;
        $message = 'Hello,'."\n"
			.$member_name.' has accepted your document: '."\n\n"
            .$document_title."\n"
            .$document_url."\n\n"
			.'REVIEWER COMMENT:'."\n"
			.$metadata."\n\n"
            .'Automated CRISP Workflow Engine'."\n"
            .'Please do not reply to this email'."\n";
        wfl_email_to_owner( $document_id, $subject, $message );
		break;

	case 'comment':
        $cnf =& ServiceConfig::Instance();
        $collaboration = $cnf->Get('settings', 'collaboration');
        $document_url = $cnf->Get('settings', 'url') . '/document/' . $document_id . '/workflow';
        $document_title = wfl_get_document_field( $document_id, 'title' );
        $subject = '[CRISP-'.$cnf->Get('settings','collaboration').'] comment for '.$document_title;
        $message = 'Hello, '."\n"
			.$member_name.' has commented on your document: '."\n\n"
            .$document_title."\n"
            .$document_url."\n\n"
			.'REVIEWER COMMENT:'."\n"
			.$metadata."\n\n"
            .'Automated CRISP Workflow Engine'."\n"
            .'Please do not reply to this email'."\n";
        wfl_email_to_owner( $document_id, $subject, $message );
		break;
	default:
		break;
  }

  return json_encode([ 'id' => $id ]);
}
