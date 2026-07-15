<?php

function get_phonebook_metadata() {
	$metadata = [
		'member_fields'			=> get_members_fields(),
		'institution_fields'	=> get_institutions_fields(),
		'document_fields'		=> get_documents_fields(),
		'event_fields'			=> get_events_fields(),
		'task_fields'			=> get_tasks_fields()
	];
	return $metadata;
}