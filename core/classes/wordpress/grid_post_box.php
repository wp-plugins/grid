<?php
/**
 * @author Palasthotel <rezeption@palasthotel.de>
 * @copyright Copyright (c) 2014, Palasthotel
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2
 * @package Palasthotel\Grid-WordPress
 */
/**
* Meta type "CONTENT"
*
* Creates a new meta type used as category for boxes.
*/
class grid_post_box extends grid_box {

	/**
	* Class contructor
	*
	* Initializes editor widgets for backend
	*/
	public function __construct() {
		$this->content = new Stdclass();
	}

	/**
	* Sets box type
	*
	* @return string
	*/
	public function type() {
		return 'post';
	}

	/**
	* Box renders its menu label and its content in here.
	*
	* @uses array date("Y-m-d h:i:s",$post->post_date).")
	*
	* @return string
	*/
	public function build( $editmode ) {
		$post = get_post( $this->content->postid ); // Returns post id or FALSE
		if ( $post == false ) {
			return 'Post is lost';
		}
		if ( $editmode ) {
			return $post->post_type.': '.$post->post_title.' ('.$post->post_date.' - '.$post->post_status.')';
		} else {
			$query = new WP_Query( array( 
				'p' => $this->content->postid
			) );
			if ( $query->have_posts() ) {
				$query->the_post();
				ob_start();
				$found = false;
				if ( $this->storage->templatesPath != null ) {
					if ( file_exists( $this->storage->templatesPath.'/post_content.tpl.php' ) ) {
						$found = true;
						include $this->storage->templatesPath.'/post_content.tpl.php';
					}
				}
				if ( ! $found ) {
					include dirname( __FILE__ ).'/../../templates/wordpress/post_content.tpl.php';
				}
				$output = ob_get_clean();
				wp_reset_postdata();
				/**
				 * post publish flag to hide from frontend
				 */
				$this->content->publish = get_post_status();
				$this->content->output = $output;
				return $this->content;
			}
		}
	}

	/**
	* Checks if class is meta type
	*
	* Makes post_box a meta type
	*
	* @return boolean
	*/
	public function isMetaType() {
		return true;
	}

	/**
	* Determines name of meta type that is shown in Grid menu
	*
	* @return string
	*/
	public function metaTitle() {
		return t( 'Contents' );
	}

	/**
	* Criteria for meta search
	*
	* @return string[]
	*/
	public function metaSearchCriteria() {
		return array( 'title' );
	}

	/**
	* Implements meta search
	*
	* @param string $criteria
	*
	* @param mixed $search
	*
	* @return array
	*/
	public function metaSearch( $criteria, $search ) {
		if ( $search == '' ) {
			return array();
		}
		$results = array();
		// START of WordPress Loop
		$query = new WP_Query( array( 
			'post_type' => array( 'post', 'page' ), 
			'grid_title' => $search 
		) );
		while ( $query->have_posts() ) {
			$query->the_post();
			$post = get_post();
			$box = new grid_post_box();
			$box->storage = $this->storage;
			$box->content = new StdClass();
			$box->content->viewmode = 'excerpt';
			$box->content->postid = $post->ID;
			$box->content->publish = $post->post_status;
			$results[] = $box;
		}
		wp_reset_postdata();
		return $results;
		// END of WordPress Loop
	}

	/**
	* Determines editor widgets used in backend
	*
	* @return array
	*/
	public function contentStructure () {
		$params = array(
			array(
				'key' => 'viewmode',
				'type' => 'select',
				'label' => t('Viewmode'),
				'selections' => array( array( 'key' => 'excerpt', 'text' => t('Excerpt') ), array( 'key' => 'full', 'text' => t('Full') ) ),
			),
			array(
				'key' => 'postid',
				'type' => 'hidden',
			),
			array(
				'key' => 'publish',
				'type' => 'hidden',
			)
		);
		return $params;
	}
}
