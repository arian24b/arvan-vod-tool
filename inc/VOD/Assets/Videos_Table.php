<?php
namespace WP_Arvan\Engine\VOD\Assets;

require_once( ABSPATH . '/wp-admin/includes/class-wp-media-list-table.php');

class Videos_Table extends \WP_Media_List_Table {


	public function __construct( $args = array() )
	{
		parent::__construct($args);
	}

	protected function get_column_info() {
		$columns = parent::get_column_info();

		$columns[0] = [
			'cb' => '<input type="checkbox" />',
			'title' => 'Video',
			'author' => 'Author',
			'parent' => 'Uploaded to',
			'comments' => '<span class="vers comment-grey-bubble" title="Comments"><span class="screen-reader-text">Comments</span></span>',
			'date' => 'Date',
		];

		return $columns;
	}
}
