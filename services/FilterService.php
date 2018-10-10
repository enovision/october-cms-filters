<?php
/**
 * @class FilterService
 *
 * @license MIT (2018)
 *
 * @version 1.0.1
 *
 * @author Enovision GmbH, Johan van de Merwe
 *
 * @since 1.0.1
 *
 * Following functions are adapted from the Wordpress CMS:
 *
 * Wordpress is licensed GPLv2 (or later) from the Free Software Foundation
 *  - add_filter
 *  - apply_filters
 *
 */

namespace Enovision\Filters\Services;

use Enovision\Filters\Classes\FilterWorker as FilterWorker;

class FilterService {

	public $id;

	public $current_filter;
	public $__filter = [];

	public function __construct() {
		// singleton check
		if ( $this->id === null ) {
			$this->id = uniqid();
		}
	}

	/**
	 * Hooks a function or method to a specific filter action.
	 *
	 * @param string $tag The name of the filter to hook the $function_to_add callback to.
	 * @param callable $function_to_add The callback to be run when the filter is applied.
	 * @param int $priority The order in which the functions associated with a
	 *                                  particular action are executed. Lower numbers correspond with
	 *                                  earlier execution, and functions with the same priority are executed
	 *                                  in the order in which they were added to the action.
	 * @param int $accepted_args The number of arguments the function accepts.
	 */
	public function add_filter( $tag, $function_to_add, $priority, $accepted_args ) {
		if ( ! isset( $this->__filter[ $tag ] ) ) {
			$this->__filter[ $tag ] = new FilterWorker;
		}

		$this->__filter[ $tag ]->add_filter( $tag, $function_to_add, $priority, $accepted_args );

		return true;
	}

	/**
	 * Call the functions added to a filter hook.
	 *
	 * The callback functions attached to filter hook $tag are invoked by calling
	 * this function. This function can be used to create a new filter hook by
	 * simply calling this function with the name of the new hook specified using
	 * the $tag parameter.
	 *
	 * The function allows for additional arguments to be added and passed to hooks.
	 *
	 *     // Our filter callback function
	 *     function example_callback( $string, $arg1, $arg2 ) {
	 *         // (maybe) modify $string
	 *         return $string;
	 *     }
	 *     add_filter( 'example_filter', 'example_callback', 10, 3 );
	 *
	 *     /*
	 *      * Apply the filters by calling the 'example_callback' function we
	 *      * "hooked" to 'example_filter' using the add_filter() function above.
	 *      * - 'example_filter' is the filter hook $tag
	 *      * - 'filter me' is the value being filtered
	 *      * - $arg1 and $arg2 are the additional arguments passed to the callback.
	 *     $value = apply_filters( 'example_filter', 'filter me', $arg1, $arg2 );
	 *
	 * @param string $tag The name of the filter hook.
	 * @param mixed $value The value on which the filters hooked to `$tag` are applied on.
	 * @param mixed $var,... Additional variables passed to the functions hooked to `$tag`.
	 *
	 * @return mixed The filtered value after all hooked functions are applied to it.
	 */
	public function apply_filters( $tag, $value ) {

		$args = [];

		// Do 'all' actions first.
		if ( isset( $this->__filter['all'] ) ) {
			$this->current_filter[] = $tag;
			$args                   = func_get_args();
			$this->all_hooks( $args );
		}

		if ( ! isset( $this->__filter[ $tag ] ) ) {
			if ( isset( $this->__filter['all'] ) ) {
				array_pop( $this->current_filter );
			}

			return $value;
		}

		if ( ! isset( $this->__filter['all'] ) ) {
			$this->current_filter[] = $tag;
		}

		if ( empty( $args ) ) {
			$args = func_get_args();
		}

		// don't pass the tag name to Hook
		array_shift( $args );

		$filtered = $this->__filter[ $tag ]->apply_filters( $value, $args );

		array_pop( $this->current_filter );

		return $filtered;

	}

	/**
	 * Processes the functions hooked into the 'all' hook.
	 *
	 * @param array $args Arguments to pass to the hook callbacks. Passed by reference.
	 *
	 */
	function all_hooks( $args ) {
		$this->__filter['all']->do_all_hook( $args );
	}
}