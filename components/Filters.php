<?php
/*
 * @class Filters
 *
 * @license MIT (2018)
 *
 * @version 1.0.1
 *
 * @author Enovision GmbH, Johan van de Merwe
 *
 * @since 1.0.1
 *
 * Placeholder Component for placing filters
 *
 * Filters can be placed in the 'onRun' routine, where the filter callback function can be added
 * as independent functions to this component. When used in other components the guideline to follow
 * is exactly the same.
 *
 * Don't forget to place:
 *
 * use Illuminate\Support\Facades\App;
 *
 * below the namespace, when using in components of other plugins.
 *
 */

namespace Enovision\Filters\Components;

use Illuminate\Support\Facades\App;

class Filters extends \Cms\Classes\ComponentBase {

	/**
	 * @return array
	 */
	public function componentDetails() {
		return [
			'name'        => 'FilterService',
			'description' => 'Placeholder for filters'
		];
	}

	/**
	 * This code will be executed when the page or layout is loaded
	 * and the component is attached to it.
	 */
	public function onRun() {
		$filterService = App::make( 'Enovision\FilterService' );
		//$filterService->add_filter( 'filter_post_title', array( $this, 'sample_title_filter' ), 10, 1 );
		//$filterService->add_filter( 'filter_change_title', array($this, 'sample_title_italics'), 10, 1 );
	}

	/**
	 * Callback functions
	 */
	public function sample_title_filter( $title ) {
		return 'Filtered title: ' . $title;
	}

	function sample_title_italics( $title ) {
		return '<i>' . $title . '</i>';
	}
}