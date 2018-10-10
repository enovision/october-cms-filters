<?php
namespace Enovision\Filters\Helpers;

abstract class FilterHelper {

	/**
	 * Adapted from Wordpress
	 * License: GPLv2 (or later) from the Free Software Foundation
	 *
	 * Build Unique ID for storage and retrieval.
	 *
	 * The old way to serialize the callback caused issues and this function is the
	 * solution. It works by checking for objects and creating a new property in
	 * the class to keep track of the object and new objects of the same class that
	 * need to be added.
	 *
	 * It also allows for the removal of actions and filters for objects after they
	 * change class properties. It is possible to include the property $wp_filter_id
	 * in your class and set it to "null" or a number to bypass the workaround.
	 * However this will prevent you from adding new classes and any new classes
	 * will overwrite the previous hook by the same class.
	 *
	 * Functions and static method callbacks are just returned as strings and
	 * shouldn't have any speed penalty.
	 *
	 * @since 1.0.1
	 * @access private
	 *
	 * @staticvar int $filter_id_count
	 *
	 * @param array $filters Used in counting how many hooks were applied
	 * @param string $tag Used in counting how many hooks were applied
	 * @param callable $function Used for creating unique id
	 * @param int|bool $priority Used in counting how many hooks were applied. If === false
	 *                           and $function is an object reference, we return the unique
	 *                           id only if it already has one, false otherwise.
	 *
	 * @return string|false Unique ID for usage as array key or false if $priority === false
	 *                      and $function is an object reference, and it does not already have
	 *                      a unique id.
	 */
	static function getUniqueId( $filters, $tag, $function, $priority ) {
		static $filter_id_count = 0;

		if ( is_string( $function ) ) {
			return $function;
		}

		if ( is_object( $function ) ) {
			// Closures are currently implemented as objects
			$function = [ $function, '' ];
		} else {
			$function = (array) $function;
		}

		if ( is_object( $function[0] ) ) {
			// Object Class Calling

			$obj_idx = get_class( $function[0] ) . $function[1];
			if ( ! isset( $function[0]->filter_id ) ) {
				if ( false === $priority ) {
					return false;
				}
				$obj_idx .= isset( $filters[ $tag ][ $priority ] ) ? count( (array) $filters[ $tag ][ $priority ] ) : $filter_id_count;
				$function[0]->filter_id = $filter_id_count;
				++ $filter_id_count;
			} else {
				$obj_idx .= $function[0]->filter_id;
			}

			return $obj_idx;

		} elseif ( is_string( $function[0] ) ) {
			// Static Calling
			return $function[0] . '::' . $function[1];
		}
	}
}