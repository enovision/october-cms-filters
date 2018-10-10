<?php
/**
 * @class FilterWorker
 *
 * @license MIT (2018)
 *
 * @version 1.0.1
 *
 * @author Enovision GmbH, Johan van de Merwe
 *
 * @since 1.0.1
 *
 * Wordpress is licensed GPLv2 (or later) from the Free Software Foundation
 *  - add_filter
 *  - resort_active_iterations
 *  - apply_filters
 *
 */

namespace Enovision\Filters\Classes;

use Enovision\Filters\Helpers\FilterHelper as Helper;

class FilterWorker {

	public $instance = null;

	public $filters = [];
	public $callbacks = [];

	protected $nesting_level = 0;
	protected $iterations = [];
	protected $current_priority = [];
	protected $doing_action = false;

	public function __construct() {
	}

	/**
	 * Hooks a function or method to a specific filter action.
	 * (adapted from the Wordpress CMS)
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
		$idx = Helper::getUniqueId( $this->filters, $tag, $function_to_add, $priority );

		$prio_exists = isset( $this->callbacks[ $priority ] );

		$this->callbacks[ $priority ][ $idx ] = [
			'function'      => $function_to_add,
			'accepted_args' => $accepted_args
		];

		// if we're adding a new priority to the list, put them back in sorted order
		if ( ! $prio_exists && count( $this->callbacks ) > 1 ) {
			ksort( $this->callbacks, SORT_NUMERIC );
		}

		if ( $this->nesting_level > 0 ) {
			$this->resort_active_iterations( $priority, $prio_exists );
		}
	}

	/**
	 * Handles reseting callback priority keys mid-iteration.
	 * (adapted from Wordpress CMS)
	 *
	 * @param bool|int $new_priority Optional. The priority of the new filter being added. Default false,
	 *                                   for no priority being added.
	 * @param bool $priority_existed Optional. Flag for whether the priority already existed before the new
	 *                                   filter was added. Default false.
	 */
	public function resort_active_iterations( $new_priority = false, $priority_existed = false ) {
		$new_priorities = array_keys( $this->callbacks );

		// If there are no remaining hooks, clear out all running iterations.
		if ( ! $new_priorities ) {
			foreach ( $this->iterations as $index => $iteration ) {
				$this->iterations[ $index ] = $new_priorities;
			}

			return;
		}

		$min = min( $new_priorities );
		foreach ( $this->iterations as $index => &$iteration ) {
			$current = current( $iteration );
			// If we're already at the end of this iteration, just leave the array pointer where it is.
			if ( false === $current ) {
				continue;
			}

			$iteration = $new_priorities;

			if ( $current < $min ) {
				array_unshift( $iteration, $current );
				continue;
			}

			while ( current( $iteration ) < $current ) {
				if ( false === next( $iteration ) ) {
					break;
				}
			}

			// If we have a new priority that didn't exist, but ::apply_filters() or ::do_action() thinks it's the current priority...
			if ( $new_priority === $this->current_priority[ $index ] && ! $priority_existed ) {
				/*
				 * ... and the new priority is the same as what $this->iterations thinks is the previous
				 * priority, we need to move back to it.
				 */

				if ( false === current( $iteration ) ) {
					// If we've already moved off the end of the array, go back to the last element.
					$prev = end( $iteration );
				} else {
					// Otherwise, just go back to the previous element.
					$prev = prev( $iteration );
				}
				if ( false === $prev ) {
					// Start of the array. Reset, and go about our day.
					reset( $iteration );
				} elseif ( $new_priority !== $prev ) {
					// Previous wasn't the same. Move forward again.
					next( $iteration );
				}
			}
		}
		unset( $iteration );
	}


	/**
	 * Calls the callback functions added to a filter hook.
	 *
	 * @since 1.0.1
	 *
	 * @param mixed $value The value to filter.
	 * @param array $args Arguments to pass to callbacks.
	 *
	 * @return mixed The filtered value after all hooked functions are applied to it.
	 */
	public function apply_filters( $value, $args ) {

		if ( ! $this->callbacks ) {
			return $value;
		}

		$nesting_level = $this->nesting_level ++;

		$this->iterations[ $nesting_level ] = array_keys( $this->callbacks );
		$num_args                           = count( $args );

		do {
			$this->current_priority[ $nesting_level ] = $priority = current( $this->iterations[ $nesting_level ] );

			foreach ( $this->callbacks[ $priority ] as $callback ) {
				if ( ! $this->doing_action ) {
					$args[0] = $value;
				}

				// Avoid the array_slice if possible.
				if ( $callback['accepted_args'] == 0 ) {
					$value = call_user_func_array( $callback['function'], [] );
				} elseif ( $callback['accepted_args'] >= $num_args ) {
					$value = call_user_func_array( $callback['function'], $args );
				} else {
					$value = call_user_func_array( $callback['function'], array_slice( $args, 0, (int) $callback['accepted_args'] ) );
				}
			}
		} while ( false !== next( $this->iterations[ $nesting_level ] ) );

		unset( $this->iterations[ $nesting_level ] );
		unset( $this->current_priority[ $nesting_level ] );

		$this->nesting_level --;

		return $value;
	}

	public function do_all_hook( &$args ) {
		$nesting_level                      = $this->nesting_level ++;
		$this->iterations[ $nesting_level ] = array_keys( $this->callbacks );

		do {
			$priority = current( $this->iterations[ $nesting_level ] );
			foreach ( $this->callbacks[ $priority ] as $the_ ) {
				call_user_func_array( $the_['function'], $args );
			}
		} while ( false !== next( $this->iterations[ $nesting_level ] ) );

		unset( $this->iterations[ $nesting_level ] );
		$this->nesting_level --;
	}
}
