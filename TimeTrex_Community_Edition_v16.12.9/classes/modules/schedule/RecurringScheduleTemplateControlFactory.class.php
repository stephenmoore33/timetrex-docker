<?php
/*********************************************************************************
 *
 * TimeTrex is a Workforce Management program developed by
 * TimeTrex Software Inc. Copyright (C) 2003 - 2021 TimeTrex Software Inc.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by
 * the Free Software Foundation with the addition of the following permission
 * added to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED
 * WORK IN WHICH THE COPYRIGHT IS OWNED BY TIMETREX, TIMETREX DISCLAIMS THE
 * WARRANTY OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 *
 *
 * You should have received a copy of the GNU Affero General Public License along
 * with this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 *
 *
 * You can contact TimeTrex headquarters at Unit 22 - 2475 Dobbin Rd. Suite
 * #292 West Kelowna, BC V4T 2E9, Canada or at email address info@timetrex.com.
 *
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 *
 *
 * In accordance with Section 7(b) of the GNU Affero General Public License
 * version 3, these Appropriate Legal Notices must retain the display of the
 * "Powered by TimeTrex" logo. If the display of the logo is not reasonably
 * feasible for technical reasons, the Appropriate Legal Notices must display
 * the words "Powered by TimeTrex".
 *
 ********************************************************************************/


/**
 * @package Modules\Schedule
 */
class RecurringScheduleTemplateControlFactory extends Factory {
	protected $table = 'recurring_schedule_template_control';
	protected $pk_sequence_name = 'recurring_schedule_template_control_id_seq'; //PK Sequence name

	protected $company_obj = null;

	function _getSchemadata( ?array $filter = null ): ?TTS {
		$schema_data = new TTS( $this );

		if ( empty( $filter ) || in_array( 'database', $filter, true ) ) {
			$schema_data->setColumns(
					TTSCols::new(
							TTSCol::new( 'id' )->setFunctionMap( 'ID' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'company_id' )->setFunctionMap( 'Company' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'name' )->setFunctionMap( 'Name' )->setType( 'varchar' )->setIsNull( false ),
							TTSCol::new( 'description' )->setFunctionMap( 'Description' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'in_use' )->setFunctionMap( 'In Use' )->setType( 'integer' )->setIsNull( false )->setIsSynthetic( true ),
					)->addCreatedAndUpdated()->addDeleted()
			);
		}

		if ( empty( $filter ) || in_array( 'fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {

			$schema_data->setTabs(
					TTSTabs::new(
							TTSTab::new( 'tab_recurring_template' )->setLabel( 'Recurring Template' )->setFields(
									new TTSFields(
											TTSField::new( 'id' )->setType( 'uuid' )->setVisible( [ 'UI' ], false ), //Hidden from UI, but visible to API and AI.
											TTSField::new( 'name' )->setType( 'text' )->setLabel( TTi18n::getText( 'Name' ) )->setWidth( '100%' ),
											TTSField::new( 'description' )->setType( 'text' )->setLabel( TTi18n::getText( 'Description' ) )->setWidth( '100%' ),
											TTSField::new( 'created_by_id' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Created By' ) )->setDataSource( TTSAPI::new( 'APIUser' )->setMethod( 'getUser' ) ),
									)
							),
					)->addAttachment()->addAudit(),
			);
		}

		if ( empty( $filter ) || in_array( 'search_fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {

			$schema_data->setSearchFields(
					TTSSearchFields::new(
							TTSSearchField::new( 'permission_children_ids' )->setType( 'uuid_list' )->setColumn( 'a.created_by' )->setMulti( true ),
							TTSSearchField::new( 'id' )->setType( 'uuid_list' )->setColumn( 'a.id' )->setMulti( true ),
							TTSSearchField::new( 'exclude_id' )->setType( 'not_uuid_list' )->setColumn( 'a.id' )->setMulti( true ),
							TTSSearchField::new( 'schedule_policy_id' )->setType( 'uuid_list' )->setColumn( 'a.id' )->setMulti( true ),
							TTSSearchField::new( 'name' )->setType( 'text' )->setColumn( 'a.name' ),
							TTSSearchField::new( 'description' )->setType( 'text' )->setColumn( 'a.description' )
					)
			);
		}

		if ( empty( $filter ) || in_array( 'api_methods', $filter, true ) ) {

			$schema_data->setAPIMethods(
					TTSAPIs::new(
							TTSAPI::new( 'APIRecurringScheduleTemplateControl' )->setMethod( 'getRecurringScheduleTemplateControl' )
									->setSummary( 'Get recurring schedule template control records.' )
									->setArgs( [ 'data' => [ 'filter_data' => $schema_data->getSearchFields(), 'filter_columns' => $schema_data->getFields() ] ] )
									->setArgsModelDescription( 'Under the "data" arg there must be at least one or more "filter_data" elements specified, and one or more "filter_columns" elements which define the minimum necessary columns of data to return for carrying out other actions. For example if looking up the "id" of a record, just the "id" filter_columns needs to be specified with a value of true.' ),
							TTSAPI::new( 'APIRecurringScheduleTemplateControl' )->setMethod( 'setRecurringScheduleTemplateControl' )
									->setSummary( 'Add or edit recurring schedule template control records. Will return the record UUID upon success, or a validation error if there is a problem.' )
									->setArgs( [ 'data' => $schema_data->getFields() ] ),
							TTSAPI::new( 'APIRecurringScheduleTemplateControl' )->setMethod( 'deleteRecurringScheduleTemplateControl' )
									->setSummary( 'Delete recurring schedule template control records by passing in an array of UUIDs.' )
									->setArgs( new TTSFields(
													   TTSField::new( 'data' )->setType( 'multi-dropdown' )->setLabel( TTi18n::getText( 'IDs to Delete' ) )->setDataSource( TTSAPI::new( 'APIRecurringScheduleTemplateControl' )->setMethod( 'getRecurringScheduleTemplateControl' ) ),
											   ) ),
							TTSAPI::new( 'APIRecurringScheduleTemplateControl' )->setMethod( 'getRecurringScheduleTemplateControlDefaultData' )
									->setSummary( 'Get default recurring schedule template control data used for creating new records. Use this before calling setRecurringScheduleTemplateControl to get the correct default data.' ),
					),
			);
		}

		return $schema_data;
	}

	/**
	 * @param $name
	 * @param null|mixed $parent
	 * @return array|null
	 */
	function _getFactoryOptions( $name, $params = null ) {
		$retval = null;
		switch ( $name ) {
			case 'columns':
				$retval = [
						'-1030-name'        => TTi18n::gettext( 'Name' ),
						'-1040-description' => TTi18n::gettext( 'Description' ),

						'-1900-in_use' => TTi18n::gettext( 'In Use' ),

						'-2000-created_by'   => TTi18n::gettext( 'Created By' ),
						'-2010-created_date' => TTi18n::gettext( 'Created Date' ),
						'-2020-updated_by'   => TTi18n::gettext( 'Updated By' ),
						'-2030-updated_date' => TTi18n::gettext( 'Updated Date' ),
				];
				break;
			case 'list_columns':
				$retval = Misc::arrayIntersectByKey( $this->getOptions( 'default_display_columns' ), Misc::trimSortPrefix( $this->getOptions( 'columns' ) ) );
				break;
			case 'default_display_columns': //Columns that are displayed by default.
				$retval = [
						'name',
						'description',
						'updated_date',
						'updated_by',
				];
				break;
		}

		return $retval;
	}

	/**
	 * @param $data
	 * @return array
	 */
	function _getVariableToFunctionMap( $data ) {
		$variable_function_map = [
				'id'          => 'ID',
				'company_id'  => 'Company',
				'name'        => 'Name',
				'description' => 'Description',
				'in_use'      => false,
				'deleted'     => 'Deleted',
				'created_by'  => 'CreatedBy', //Needed to change the "owner" of the template for permission purposes.
		];

		return $variable_function_map;
	}

	/**
	 * @return bool
	 */
	function getCompanyObject() {
		return $this->getGenericObject( 'CompanyListFactory', $this->getCompany(), 'company_obj' );
	}

	/**
	 * @return bool|mixed
	 */
	function getCompany() {
		return $this->getGenericDataValue( 'company_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setCompany( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'company_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getName() {
		return $this->getGenericDataValue( 'name' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setName( $value ) {
		$value = trim( $value );

		/*
						AND	$this->Validator->isTrue(	'name',
														$this->isUniqueName($name),
														TTi18n::gettext('Name is already in use')
														)
		*/

		return $this->setGenericDataValue( 'name', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getDescription() {
		return $this->getGenericDataValue( 'description' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setDescription( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'description', $value );
	}

	/**
	 * @return bool
	 */
	function Validate() {
		//
		// BELOW: Validation code moved from set*() functions.
		//
		// Company
		$clf = TTnew( 'CompanyListFactory' ); /** @var CompanyListFactory $clf */
		$this->Validator->isResultSetWithRows( 'company',
											   $clf->getByID( $this->getCompany() ),
											   TTi18n::gettext( 'Company is invalid' )
		);
		// Name
		if ( $this->getName() !== false ) {
			$this->Validator->isLength( 'name',
										$this->getName(),
										TTi18n::gettext( 'Name is invalid' ),
										2, 50
			);

			$this->Validator->isHTML( 'name',
									  $this->getName(),
									  TTi18n::gettext( 'Name contains invalid special characters' ),
			);
		}
		// Description
		$this->Validator->isLength( 'description',
									$this->getDescription(),
									TTi18n::gettext( 'Description is too short or too long' ),
									0, 255
		);

		$this->Validator->isHTML( 'description',
								  $this->getDescription(),
								  TTi18n::gettext( 'Description contains invalid special characters' ),
		);

		//
		// ABOVE: Validation code moved from set*() functions.
		//

		if ( $this->getDeleted() == true ) {
			//Check to make sure nothing else references this, so we can be sure its okay to delete it.
			$rsclf = TTnew( 'RecurringScheduleControlListFactory' ); /** @var RecurringScheduleControlListFactory $rsclf */
			$rsclf->getByCompanyIdAndTemplateID( $this->getCompany(), $this->getId() );
			if ( $rsclf->getRecordCount() > 0 ) {
				$this->Validator->isTRUE( 'in_use',
										  false,
										  TTi18n::gettext( 'This recurring template is currently in use' ) );
			}
		}

		return true;
	}

	/**
	 * @return bool
	 */
	function postSave() {
		//
		//**THIS IS DONE IN recalculateRecurringScheduleForJobQueue, RecurringScheduleControlFactory, RecurringScheduleTemplateControlFactory, HolidayFactory postSave() as well.
		//

		//Loop through all RecurringScheduleControl rows associated with this template, so we can recalculate the recurring schedules for them.
		$rsclf = TTNew( 'RecurringScheduleControlListFactory' ); /** @var RecurringScheduleControlListFactory $rsclf */
		$rsclf->getByCompanyIdAndTemplateID( $this->getCompany(), $this->getId() );
		if ( $rsclf->getRecordCount() > 0 ) {
			Debug::text( 'Found RecurringScheduleControl records assigned to this template: ' . $rsclf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );

			global $config_vars;
			foreach ( $rsclf as $rsc_obj ) {
				//Handle generating recurring schedule rows, so they are as real-time as possible.
				$current_epoch = TTDate::getBeginWeekEpoch( TTDate::getBeginWeekEpoch( time() ) - 86400 );

				if ( !isset( $config_vars['other']['enable_job_queue'] ) || $config_vars['other']['enable_job_queue'] == true ) {
					SystemJobQueue::Add( TTi18n::getText( 'Recalculating Recurring Schedule' ), $this->getAPIMessageID(), 'RecurringScheduleFactory', 'recalculateRecurringScheduleForJobQueue', [ $rsc_obj->getCompany(), $rsc_obj->getID(), $rsc_obj->getUser(), $current_epoch, $rsc_obj->getMaximumEndDate( $current_epoch ), $this->getDeleted() ], 95 );
				} else {
					$rsf = TTnew( 'RecurringScheduleFactory' ); /** @var RecurringScheduleFactory $rsf */
					$rsf->setAPIMessageID( $this->getAPIMessageID() );
					$rsf->StartTransaction();
					$rsf->clearRecurringSchedulesFromRecurringScheduleControl( $rsc_obj->getID(), ( $current_epoch - ( 86400 * 720 ) ), ( $current_epoch + ( 86400 * 720 ) ) );
					if ( $this->getDeleted() == false ) {
						Debug::text( 'Recurring Schedule ID: ' . $rsc_obj->getID() . ' Start Date: ' . TTDate::getDate( 'DATE+TIME', $current_epoch ) . ' Maximum End Date: ' . TTDate::getDate( 'DATE+TIME', $rsc_obj->getMaximumEndDate( $current_epoch ) ), __FILE__, __LINE__, __METHOD__, 10 );
						$rsf->addRecurringSchedulesFromRecurringScheduleControl( $rsc_obj->getCompany(), $rsc_obj->getID(), $current_epoch, $rsc_obj->getMaximumEndDate( $current_epoch ) );
					}
					$rsf->CommitTransaction();
				}
			}
		}

		if ( $this->getDeleted() == true ) {
			//Unassign all committed shifts from this recurring schedule as its now deleted anyways.
			//  This prevents 'Invalid Recurring Schedule Template' error messages if a user were to try to edit/save such schedule shift.
			$f = TTnew( 'ScheduleFactory' ); /** @var ScheduleFactory $f */
			$query = 'UPDATE ' . $f->getTable() . ' SET recurring_schedule_template_control_id = \'' . TTUUID::getZeroID() . '\' WHERE recurring_schedule_template_control_id = \'' . TTUUID::castUUID( $this->getId() ) . '\'';
			$f->ExecuteSQL( $query );
			Debug::Text( 'Schedule Query: ' . $query . ' Affected Rows: ' . $f->getAffectedRows(), __FILE__, __LINE__, __METHOD__, 10 );
		}

		return true;
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function setObjectFromArray( $data ) {
		if ( is_array( $data ) ) {
			$variable_function_map = $this->getVariableToFunctionMap();
			foreach ( $variable_function_map as $key => $function ) {
				if ( isset( $data[$key] ) ) {

					$function = 'set' . $function;
					switch ( $key ) {
						default:
							if ( method_exists( $this, $function ) ) {
								$this->$function( $data[$key] );
							}
							break;
					}
				}
			}

			$this->setCreatedAndUpdatedColumns( $data, $variable_function_map );

			return true;
		}

		return false;
	}

	/**
	 * @param null $include_columns
	 * @return array
	 */
	function getObjectAsArray( $include_columns = null ) {
		$variable_function_map = $this->getVariableToFunctionMap();
		$data = [];
		if ( is_array( $variable_function_map ) ) {
			foreach ( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == null || ( isset( $include_columns[$variable] ) && $include_columns[$variable] == true ) ) {

					$function = 'get' . $function_stub;
					switch ( $variable ) {
						case 'in_use':
							$data[$variable] = $this->getColumn( $variable );
							break;
						default:
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = $this->$function();
							}
							break;
					}
				}
			}
			$this->getPermissionColumns( $data, $this->getCreatedBy(), $this->getCreatedBy(), false, $include_columns );
			$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}

	/**
	 * @param $log_action
	 * @return bool
	 */
	function addLog( $log_action ) {
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText( 'Recurring Schedule Template' ) . ': ' . $this->getName(), null, $this->getTable(), $this );
	}
}

?>
