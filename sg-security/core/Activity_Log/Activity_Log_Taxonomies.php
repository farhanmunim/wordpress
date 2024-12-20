<?php
namespace SG_Security\Activity_Log;

/**
 * Activity Log Taxonomies main class
 */
class Activity_Log_Taxonomies extends Activity_Log_Helper {
	/**
	 * Log term create.
	 *
	 * @since  1.0.0
	 *
	 * @param  int $term_id  Term ID.
	 * @param  int $tt_id    Term Taxonomy ID.
	 * @param  int $taxonomy Taxonomy.
	 */
	public function log_term_create( $term_id, $tt_id, $taxonomy ) {
		$tax = get_taxonomy( $taxonomy );
		$activity = __( 'Created', 'sg-security' ) . ' ' . $tax->labels->singular_name;
		$this->log_event( array(
			'activity'    => $activity,
			'description' => $this->get_term_description( $term_id, $taxonomy, $activity ),
			'object_id'   => $term_id,
			'type'        => 'term',
			'action'      => 'create',
		) );
	}

	/**
	 * Log term edit.
	 *
	 * @since  1.0.0
	 *
	 * @param  int $term_id  Term ID.
	 * @param  int $tt_id    Term Taxonomy ID.
	 * @param  int $taxonomy Taxonomy.
	 */
	public function log_term_edit( $term_id, $tt_id, $taxonomy ) {
		$tax = get_taxonomy( $taxonomy );
		$activity = __( 'Edited', 'sg-security' ) . ' ' . $tax->labels->singular_name;
		$this->log_event( array(
			'activity'    => $activity,
			'description' => $this->get_term_description( $term_id, $taxonomy, $activity ),
			'object_id'   => $term_id,
			'type'        => 'term',
			'action'      => 'edit',
		) );
	}

	/**
	 * Log term delete.
	 *
	 * @since  1.0.0
	 *
	 * @param  int    $term_id     Term ID.
	 * @param  int    $tt_id       Term Taxonomy ID.
	 * @param  int    $taxonomy    Taxonomy.
	 * @param  object $deleted_term Deleted Term.
	 */
	public function log_term_delete( $term_id, $tt_id, $taxonomy, $deleted_term ) {
		$tax = get_taxonomy( $taxonomy );
		$activity = __( 'Deleted', 'sg-security' ) . ' ' . $tax->labels->singular_name;
		$this->log_event( array(
			'activity'    => $activity,
			'description' => $activity . ' - ' . $deleted_term->name,
			'object_id'   => $term_id,
			'type'        => 'term',
			'action'      => 'delete',
		) );
	}

	/**
	 * Get post log description.
	 *
	 * @since  1.0.0
	 *
	 * @param  int    $term_id     Term ID.
	 * @param  int    $taxonomy    Term Taxonomy.
	 * @param  string $activity    The activity type.
	 *
	 * @return string           The description.
	 */
	public function get_term_description( $term_id, $taxonomy, $activity ) {
		$term = get_term( $term_id, $taxonomy );
		return $activity . ' - ' . $term->name;
	}
}
