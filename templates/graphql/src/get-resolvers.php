<?php

require_once('invenio-integration.php');

function flatten_group_name( $name ) {
    $res = strtolower(trim($name));
    $res = preg_replace( '/[^a-zA-Z0-9]+/', '_', $res );
    return $res;
}

function convert_role_to_invenio( $name ) {
	$res = strtolower($name);
	if ( $res === '' || $res === 'member' ) {
		return 'reader';
	} else {
		return 'manager';
	}
}

function get_resolvers() {
    $resolvers = [
        'Query' => [
            'echo' => function( $root, $args, $context, $info ) {
                return $args['message'];
            },
			'events' => function( $root, $args, $context, $info ) {
				return get_filters( $context['events'], ( $context['variables'] && $context['variables']['filters'] ) ? $context['variables']['filters'] : [] );
			},
			'documents' => function( $root, $args, $context, $info ) {
				return get_filters( $context['documents'], ( $context['variables'] && $context['variables']['filters'] ) ? $context['variables']['filters'] : [] );
			},
			'members' => function( $root, $args, $context, $info ) {
				return get_filters( $context['members'], ( $context['variables'] && $context['variables']['filters'] ) ? $context['variables']['filters'] : [] );
			},
			'institutions' => function( $root, $args, $context, $info ) {
				return get_filters( $context['institutions'], ( $context['variables'] && $context['variables']['filters'] ) ? $context['variables']['filters'] : [] );
			},
			'tasks' => function( $root, $args, $context, $info ) {
				return get_filters( $context['tasks'], ( $context['variables'] && $context['variables']['filters'] ) ? $context['variables']['filters'] : [] );
			},
			'assigned_tasks' => function( $root, $args, $context, $info ) {
				return get_filters( $context['assigned_tasks'], ( $context['variables'] && $context['variables']['filters'] ) ? $context['variables']['filters'] : [] );
			},
			'member' => function( $root, $args, $context, $info ) {
				foreach($context['members'] as $k => $v ) {
					if ( isset($v['orcid_id']) && $v['orcid_id'] == $args['orcid'] ) {
						return $v;
					} else if ( isset($v['id']) && $v['id'] == $args['orcid'] ) {
						return $v;
					} else if ( isset($v['id']) && $v['id'] == $args['id'] ) {
						return $v;
					}
				}
				return NULL;
			},
			'institution' => function( $root, $args, $context, $info ) {
				foreach($context['institutions'] as $k => $v ) {
					if ( isset($v['ror_id']) && $v['ror_id'] == $args['rorid'] ) {
						return $v;
					} else if ( isset($v['id']) && $v['id'] == $args['rorid'] ) {
						return $v;
					} else if ( isset($v['id']) && $v['id'] == $args['id'] ) {
						return $v;
					}
				}
				return NULL;
			},
			'task' => function( $root, $args, $context, $info ) {
				foreach($context['tasks'] as $k => $v ) {
					if ( isset($v['id']) && $v['id'] == $args['id'] ) {
						return $v;
					}
				}
				return NULL;
			},
			'group' => function( $root, $args, $context, $info ) {
				foreach($context['groups'] as $k => $v ) {
					if ( isset($v['id']) && $v['id'] == $args['id'] ) {
						return $v;
					}
				}
				return NULL;
			},
			'groups' => function( $root, $args, $context, $info ) {
				$res = [];
				foreach( $context['groups'] as $k => $v ) {
					$group = $v;
					$group['roles'] = [];
					foreach( $context['groups_roles'][ $v['id'] ] as $k2 => $v2 ) {
						$group['roles'][] = $v2;
					}
					$res[] = $group;
				}
				return $res;
			},
			'memberGroups' => function( $root, $args, $context, $info ) {
				$member = false;
				foreach($context['members'] as $k => $v ) {
					if ( isset($v['orcid_id']) && $v['orcid_id'] == $args['orcid'] ) {
						$member = $v;
						break;
					}
				}
				if ( !$member ) { return [ [ 'id' => 0, 'name' => 'no-member-found', 'category' => 'n/a', 'role' => '', 'roles' => [] ] ]; }
				$groups = $context['groups_members_roles']['members'][ $member['id'] ];
				$res = [];
				foreach( $groups as $k => $v ) {
					$res[] = [
						'id' => $v['group_id'],
						'name' => $context['groups'][ $v['group_id'] ]['name'],
						'category' => $context['groups'][ $v['group_id'] ]['category'],
						'role' => $context['groups_roles'][ $v['group_id'] ][ $v['role_id'] ],
						'roles' => []
					];
				}

				return $res;
			},
			'invenioGroups' => function( $root, $args, $context, $info ) {
				$member = false;
				foreach($context['members'] as $k => $v ) {
					if ( isset($v['orcid_id']) && $v['orcid_id'] == $args['orcid'] ) {
						$member = $v;
						break;
					}
				}
				if ( !$member ) { return [ [ 'id' => 0, 'name' => 'no-member-found', 'category' => 'n/a', 'role' => '', 'roles' => [] ] ]; }
				$groups = $context['groups_members_roles']['members'][ $member['id'] ];
				$res = [];
				foreach( $groups as $k => $v ) {
					$res[] = [
						'id' => $v['group_id'],
						'name' => 'eic-'.flatten_group_name( $context['groups'][ $v['group_id'] ]['name'] ),
						'category' => $context['groups'][ $v['group_id'] ]['category'],
						'role' => 'eic-'.flatten_group_name( $context['groups'][ $v['group_id'] ]['name'] ).'-'.convert_role_to_invenio( $context['groups_roles'][ $v['group_id'] ][ $v['role_id'] ] ),
						'roles' => []
					];
				}
				return $res;
			},
            'invenioSearchCommunity' => function( $root, $args, $context, $info ) {
                // $args['slug']
                return invenio_search_community( $args['slug'] );
            },
            'invenioCreateCommunity' => function( $root, $args, $context, $info ) {
                // $args['name'], $args['slug']
                return invenio_create_community( $args['name'], $args['slug'] );
            },
        ],
		'AssignedTask' => [
			'task' => function( $root, $args, $context, $info ) {
                return $context['tasks'][ $root['task_id'] ];
            },
			'member' => function( $root, $args, $context, $info ) {
                return $context['members'][ $root['member_id'] ];
            },
			'group' => function( $root, $args, $context, $info ) {
                return $context['groups'][ $root['group_id'] ];
            }
		],
		'Institution' => [
			'members' => function( $root, $args, $context, $info ) {
				return array_filter( $context['members'], fn($m) => $m['institution_id'] == $root['id'] );
			}
		],
        'Member' => [
            'institution' => function( $root, $args, $context, $info ) {
                return $context['institutions'][ $root['institution_id'] ];
            },
			'assigned_tasks' => function( $root, $args, $context, $info ) {
				$res = [];
				foreach( $context['assigned_tasks'] as $k => $v ) {
					if ( $v['member_id'] != $root['id'] ) { continue; }
					$res[] = $v;
				}
				return $res;
			},
			'groups' => function( $root, $args, $context, $info ) {
				$groups = $context['groups_members_roles']['members'][ $root['id'] ];
                $res = [
                    [ 'id' => 0, 'name' => 'epic-members', 'category' => '', 'role' => 'epic-members-reader', 'roles' => [] ]
                ];
				foreach( $groups as $k => $v ) {
					if ( !isset($context['groups'][ $v['group_id'] ]) ) { continue; }
					$res[] = [
						'id' => $v['group_id'],
						'name' => $context['groups'][ $v['group_id'] ]['name'],
						'category' => $context['groups'][ $v['group_id'] ]['category'],
						'role' => $context['groups_roles'][ $v['group_id'] ][ $v['role_id'] ],
						'roles' => []
					];
				}
				return $res;
			},
			'igroups' => function( $root, $args, $context, $info ) {
				$groups = $context['groups_members_roles']['members'][ $root['id'] ];
                $res = [
                    [ 'id' => 0, 'name' => 'epic-members', 'category' => '', 'role' => 'epic-members-reader', 'roles' => [] ]
                ];
				foreach( $groups as $k => $v ) {
					if ( !isset($context['groups'][ $v['group_id'] ]) ) { continue; }
					$res[] = [
						'id' => $v['group_id'],
						'name' => 'eic-'.flatten_group_name( $context['groups'][ $v['group_id'] ]['name'] ),
						'category' => $context['groups'][ $v['group_id'] ]['category'],
						'role' => 'eic-'.flatten_group_name( $context['groups'][ $v['group_id'] ]['name'] ).'-'.convert_role_to_invenio( $context['groups_roles'][ $v['group_id'] ][ $v['role_id'] ] ),
						'roles' => []
					];
				}
				return $res;
			}
        ]
    ];
	return $resolvers;
}