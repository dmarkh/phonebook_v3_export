<?php

function get_schema( $metadata ) {
	// $metadata = [ 'member_fields' => [], 'institution_fields' => [] ]
$schema = 
'schema {
	query: Query
}

type Query {
	echo(message: String!): String!
	members: [Member]
	institutions: [Institution]
	documents: [Document]
	events: [Event]
	member(orcid: String!): Member
	institution(rorid: String!): Institution
	groups: [Group]
	tasks: [Task]
	assigned_tasks: [AssignedTask]
	memberGroups(orcid: String!): [Group]
	invenioGroups(orcid: String!): [Group]
	invenioSearchCommunity(slug: String!): String!
	invenioCreateCommunity(name: String!, slug: String!): String!
}

type Event {
	id: ID!'."\n";
	foreach( $metadata['event_fields'] as $k => $v ) {
		switch( $v['type'] ) {
			case 'int':
				$schema .= "\t".$v['name_fixed'].': Int'."\n";
				break;
			case 'string':
			case 'text':
				$schema .= "\t".$v['name_fixed'].': String'."\n";
				break;
			case 'date':
				$schema .= "\t".$v['name_fixed'].': String'."\n";
				break;
			default:
				break;
		}
	}
$schema .= '}

type Document {
	id: ID!'."\n";
	foreach( $metadata['document_fields'] as $k => $v ) {
		switch( $v['type'] ) {
			case 'int':
				$schema .= "\t".$v['name_fixed'].': Int'."\n";
				break;
			case 'string':
			case 'text':
				$schema .= "\t".$v['name_fixed'].': String'."\n";
				break;
			case 'date':
				$schema .= "\t".$v['name_fixed'].': String'."\n";
				break;
			default:
				break;
		}
	}
$schema .= '}

type Institution {
	id: ID!
	members: [Member!]'."\n";
	foreach( $metadata['institution_fields'] as $k => $v ) {
		switch( $v['type'] ) {
			case 'int':
				$schema .= "\t".$v['name_fixed'].': Int'."\n";
				break;
			case 'string':
			case 'text':
				$schema .= "\t".$v['name_fixed'].': String'."\n";
				break;
			case 'date':
				$schema .= "\t".$v['name_fixed'].': String'."\n";
				break;
			default:
				break;
		}
	}
$schema .= '}

type Member {
	id: ID!
	institution: Institution!
	groups: [Group]
	igroups: [Group]
	assigned_tasks: [AssignedTask]'."\n";
	foreach( $metadata['member_fields'] as $k => $v ) {
		switch( $v['type'] ) {
			case 'int':
				$schema .= "\t".$v['name_fixed'].': Int'."\n";
				break;
			case 'string':
			case 'text':
				$schema .= "\t".$v['name_fixed'].': String'."\n";
				break;
			case 'date':
				$schema .= "\t".$v['name_fixed'].': String'."\n";
				break;
			default:
				break;
		}
	}

$schema .= '}

type Group {
	id: ID!
	name: String!
	category: String
	url: String,
	role: String
	roles: [String]
}'."\n";

$schema .= '
type Task {
	id: ID!'."\n";
	foreach( $metadata['task_fields'] as $k => $v ) {
		switch( $v['type'] ) {
			case 'int':
				$schema .= "\t".$v['name_fixed'].': Int'."\n";
				break;
			case 'string':
			case 'text':
				$schema .= "\t".$v['name_fixed'].': String'."\n";
				break;
			case 'date':
				$schema .= "\t".$v['name_fixed'].': String'."\n";
				break;
			default:
				break;
		}
	}
$schema .= '}

type AssignedTask {
	id: ID!
	task: Task!
	member: Member!
	group: Group!
	fte: Float
	begin_time: String
	end_time: String
	validated: Int
}'."\n";

$schema .= "\n";

	return $schema;
}