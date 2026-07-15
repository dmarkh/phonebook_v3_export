<?php

function setResolvers($resolvers) {
	\GraphQL\Executor\Executor::setDefaultFieldResolver(
		function ($source, $args, $context, \GraphQL\Type\Definition\ResolveInfo $info) use ($resolvers) {
		$fieldName = $info->fieldName;
		if (is_null($fieldName)) {
			throw new \Exception('Could not get $fieldName from ResolveInfo');
		}
		if (is_null($info->parentType)) {
			throw new \Exception('Could not get $parentType from ResolveInfo');
		}
		$parentTypeName = $info->parentType->name;
		if (isset($resolvers[$parentTypeName])) {
			$resolver = $resolvers[$parentTypeName];
			if (is_array($resolver)) {
				if (array_key_exists($fieldName, $resolver)) {
					$value = $resolver[$fieldName];
					return is_callable($value) ? $value($source, $args, $context, $info) : $value;
				}
			}
			if (is_object($resolver)) {
				if (isset($resolver->{$fieldName})) {
					$value = $resolver->{$fieldName};
					return is_callable($value) ? $value($source, $args, $context, $info) : $value;
				}
			}
		}
		return \GraphQL\Executor\Executor::defaultFieldResolver($source, $args, $context, $info);
	});
}
