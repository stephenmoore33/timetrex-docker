<?php
/*
 * $License$
 */

/**
 * @package Modules\Install
 */
class InstallSchema_1140A extends InstallSchema_Base {

	/**
	 * @return bool
	 */
	function preInstall() {
		Debug::text( 'preInstall: ' . $this->getVersion(), __FILE__, __LINE__, __METHOD__, 9 );

		return true;
	}

	/**
	 * @return bool
	 */
	function postInstall() {
		Debug::text( 'postInstall: ' . $this->getVersion(), __FILE__, __LINE__, __METHOD__, 9 );

		$clf = TTnew( 'CompanyListFactory' ); /** @var CompanyListFactory $clf */
		$clf->StartTransaction();
		$clf->getAll();
		if ( $clf->getRecordCount() > 0 ) {
			foreach ( $clf as $c_obj ) { /** @var CompanyFactory $c_obj */

				//Custom field parent tables with single selects
				$single_select_parent_tables = [];

				$cflf = TTnew( 'CustomFieldListFactory' ); /** @var CustomFieldListFactory $cflf */
				$cflf->getByCompanyIdAndTypeId( $c_obj->getId(), 2100 );
				if ( $cflf->getRecordCount() > 0 ) {
					foreach ( $cflf as $cf_obj ) { /** @var CustomFieldFactory $cf_obj */
						if ( isset( $single_select_parent_tables[$cf_obj->getParentTable()] ) == false ) {
							$single_select_parent_tables[$cf_obj->getParentTable()] = [];
						}
						Debug::Text( 'Found custom field for ' . $cf_obj->getParentTable() . ' with a Single Select Dropdown: ' . $cf_obj->getParentTable(), __FILE__, __LINE__, __METHOD__, 9 );
						$single_select_parent_tables[$cf_obj->getParentTable()][] = $cf_obj->getId();
					}

					foreach ( $single_select_parent_tables as $parent_table => $custom_field_ids ) {
						$custom_field_select_query_arr = [];
						foreach ( $custom_field_ids as $custom_field_id ) {
							$custom_field_select_query_arr[] = 'custom_field::text LIKE \'%' . $custom_field_id . '%\' ';
						}

						Debug::Arr( $custom_field_ids, '  Searching for records using these single select dropdown custom fields. ', __FILE__, __LINE__, __METHOD__, 9 );

						$records_with_single_dropdown = $this->getDatabaseConnection()->Execute( 'SELECT id, custom_field FROM ' . $parent_table . ' WHERE custom_field IS NOT NULL AND ( ' . implode( ' OR ', $custom_field_select_query_arr ) . ' )' );

						while ( $record = $records_with_single_dropdown->fetchRow() ) {
							$custom_field_data = json_decode( $record['custom_field'], true );
							$has_corrupted_data = false;

							foreach ( $custom_field_ids as $custom_field_id ) {
								if ( isset( $custom_field_data[$custom_field_id] ) && is_array( $custom_field_data[$custom_field_id] ) ) {
									Debug::Text( '    Found corrupted data for custom field ID: ' . $custom_field_id . ' in record ID: ' . $record['id'] . ' Parent Table: ' . $parent_table, __FILE__, __LINE__, __METHOD__, 9 );
									$custom_field_data[$custom_field_id] = $custom_field_data[$custom_field_id][array_key_first( $custom_field_data[$custom_field_id] )];
									$has_corrupted_data = true;
								}
							}

							if ( $has_corrupted_data == true ) {
								Debug::Text( '      Fixing corrupted custom field data by updating record ID: ' . $record['id'] . ' Parent Table: ' . $parent_table, __FILE__, __LINE__, __METHOD__, 9 );
								$custom_field_update_query = 'UPDATE ' . $parent_table . ' SET custom_field = \'' . json_encode( $custom_field_data ) . '\' WHERE id = \'' . $record['id'] . '\' ';
								$this->getDatabaseConnection()->Execute( $custom_field_update_query );
							}
						}
					}
				}
			}
		}

		$clf->CommitTransaction();

		return true;
	}
}

?>
