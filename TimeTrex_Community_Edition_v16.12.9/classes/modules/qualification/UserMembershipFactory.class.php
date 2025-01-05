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
 * @package Modules\Qualification
 */
class UserMembershipFactory extends Factory {
	protected $table = 'user_membership';
	protected $pk_sequence_name = 'user_membership_id_seq'; //PK Sequence name
	protected $qualification_obj = null;

	function _getSchemadata( ?array $filter = null ): ?TTS {
		$schema_data = new TTS( $this );

		if ( empty( $filter ) || in_array( 'database', $filter, true ) ) {
			$schema_data->setColumns(
					TTSCols::new(
							TTSCol::new( 'id' )->setFunctionMap( 'ID' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'user_id' )->setFunctionMap( 'User' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'qualification_id' )->setFunctionMap( 'Qualification' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'ownership_id' )->setFunctionMap( 'Ownership' )->setType( 'integer' )->setIsNull( false ),
							TTSCol::new( 'amount' )->setFunctionMap( 'Amount' )->setType( 'numeric' )->setIsNull( false ),
							TTSCol::new( 'currency_id' )->setFunctionMap( 'Currency' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'start_date' )->setFunctionMap( 'StartDate' )->setType( 'integer' )->setIsNull( false ),
							TTSCol::new( 'renewal_date' )->setFunctionMap( 'RenewalDate' )->setType( 'integer' )->setIsNull( false ),
					)->addCreatedAndUpdated()->addDeleted()
			);
		}

		if ( empty( $filter ) || in_array( 'fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {
			$schema_data->setTabs(
					TTSTabs::new(
							TTSTab::new( 'tab_membership' )->setLabel( TTi18n::getText( 'Membership' ) )->setFields(
									new TTSFields(
											TTSField::new( 'user_id' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Employee' ) )->setDataSource( TTSAPI::new( 'APIUser' )->setMethod( 'getUser' ) ),
											TTSField::new( 'qualification_id' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Membership' ) )->setDataSource( TTSAPI::new( 'APIQualification' )->setMethod( 'getQualification' ) ),
											TTSField::new( 'ownership_id' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Ownership' ) )->setDataSource( TTSAPI::new( 'APIUserMembership' )->setMethod( 'getOptions' )->setArg( 'ownership' ) ),
											TTSField::new( 'currency_id' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Currency' ) )->setDataSource( TTSAPI::new( 'APIUserMembership' )->setMethod( 'getOptions' )->setArg( 'currency' ) ),
											TTSField::new( 'amount' )->setType( 'numeric' )->setLabel( TTi18n::getText( 'Amount' ) ),
											TTSField::new( 'start_date' )->setType( 'date' )->setLabel( TTi18n::getText( 'Start Date' ) ),
											TTSField::new( 'renewal_date' )->setType( 'date' )->setLabel( TTi18n::getText( 'Renewal Date' ) ),
											TTSField::new( 'tag' )->setType( 'tag' )->setLabel( TTi18n::getText( 'Tags' ) )
									)
							),
					)->addAttachment()->addAudit()
			);
		}

		if ( empty( $filter ) || in_array( 'search_fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {

			$schema_data->setSearchFields(
					TTSSearchFields::new(
							TTSSearchField::new( 'user_id' )->setType( 'uuid_list' )->setColumn( 'a.user_id' )->setMulti( true ),
							TTSSearchField::new( 'id' )->setType( 'uuid_list' )->setColumn( 'a.id' )->setMulti( true ),
							TTSSearchField::new( 'exclude_id' )->setType( 'not_uuid_list' )->setColumn( 'a.user_id' )->setMulti( true ),
							TTSSearchField::new( 'ownership' )->setType( 'text' )->setColumn( 'a.ownership_id' ),
							TTSSearchField::new( 'qualification_id' )->setType( 'uuid_list' )->setColumn( 'a.qualification_id' )->setMulti( true ),
							TTSSearchField::new( 'qualification' )->setType( 'text' )->setColumn( 'qf.name' ),
							TTSSearchField::new( 'proficiency_id' )->setType( 'numeric_list' )->setColumn( 'usf.proficiency_id' )->setMulti( true ),
							TTSSearchField::new( 'fluency_id' )->setType( 'numeric_list' )->setColumn( 'ulf.fluency_id' )->setMulti( true ),
							TTSSearchField::new( 'competency_id' )->setType( 'numeric_list' )->setColumn( 'ulf.competency_id' )->setMulti( true ),
							TTSSearchField::new( 'ownership_id' )->setType( 'numeric_list' )->setColumn( 'a.ownership_id' )->setMulti( true ),
							TTSSearchField::new( 'currency_id' )->setType( 'uuid_list' )->setColumn( 'a.currency_id' )->setMulti( true ),
							TTSSearchField::new( 'currency' )->setType( 'text' )->setColumn( 'cf.name' ),
							TTSSearchField::new( 'source_type_id' )->setType( 'numeric_list' )->setColumn( 'qf.source_type_id' )->setMulti( true ),
							TTSSearchField::new( 'group_id' )->setType( 'uuid_list' )->setColumn( 'qf.group_id' )->setMulti( true ),
							TTSSearchField::new( 'group' )->setType( 'text' )->setColumn( 'qgf.name' ),
							TTSSearchField::new( 'qualification_type_id' )->setType( 'numeric_list' )->setColumn( 'qf.type_id' )->setMulti( true ),
							TTSSearchField::new( 'default_branch_id' )->setType( 'uuid_list' )->setColumn( 'uf.default_branch_id' )->setMulti( true ),
							TTSSearchField::new( 'default_department_id' )->setType( 'uuid_list' )->setColumn( 'uf.default_department_id' )->setMulti( true ),
							TTSSearchField::new( 'tag' )->setType( 'tag' )->setColumn( 'a.id' ),
							TTSSearchField::new( 'renewal_date' )->setType( 'date_range' )->setColumn( 'a.renewal_date' ),
							TTSSearchField::new( 'start_date' )->setType( 'date_range' )->setColumn( 'a.start_date' ),
							TTSSearchField::new( 'membership_renewal_start_date' )->setType( 'start_date' )->setColumn( 'a.renewal_date' ),
							TTSSearchField::new( 'membership_renewal_end_date' )->setType( 'end_date' )->setColumn( 'a.renewal_date' )
					)
			);
		}

		if ( empty( $filter ) || in_array( 'api_methods', $filter, true ) ) {
			$schema_data->setAPIMethods(
					TTSAPIs::new(
							TTSAPI::new( 'APIUserMembership' )->setMethod( 'getUserMembership' )
									->setSummary( 'Get user membership records.' )
									->setArgs( [ 'data' => [ 'filter_data' => $schema_data->getSearchFields(), 'filter_columns' => $schema_data->getFields() ] ] )
									->setArgsModelDescription( 'Under the "data" arg there must be at least one or more "filter_data" elements specified, and one or more "filter_columns" elements which define the minimum necessary columns of data to return for carrying out other actions. For example if looking up the "id" of a record, just the "id" filter_columns needs to be specified with a value of true.' ),
							TTSAPI::new( 'APIUserMembership' )->setMethod( 'setUserMembership' )
									->setSummary( 'Add or edit user membership records. Will return the record UUID upon success, or a validation error if there is a problem.' )
									->setArgs( [ 'data' => $schema_data->getFields() ] ),
							TTSAPI::new( 'APIUserMembership' )->setMethod( 'deleteUserMembership' )
									->setSummary( 'Delete user membership records by passing in an array of UUIDs.' )
									->setArgs( new TTSFields(
													   TTSField::new( 'data' )->setType( 'multi-dropdown' )->setLabel( TTi18n::getText( 'IDs to Delete' ) )->setDataSource( TTSAPI::new( 'APIUserMembership' )->setMethod( 'getUserMembership' ) ),
											   ) ),
							TTSAPI::new( 'APIUserMembership' )->setMethod( 'getUserMembershipDefaultData' )
									->setSummary( 'Get default user membership data used for creating new user memberships. Use this before calling setUserMembership to get the correct default data.' ),
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
			case 'ownership':
				$retval = [
						10 => TTi18n::gettext( 'Company' ),
						20 => TTi18n::gettext( 'Individual' ),
				];
				break;
			case 'source_type':
				$qf = TTnew( 'QualificationFactory' ); /** @var QualificationFactory $qf */
				$retval = $qf->getOptions( $name );
				break;
			case 'columns':
				$retval = [
						'-1010-first_name'    => TTi18n::gettext( 'First Name' ),
						'-1020-last_name'     => TTi18n::gettext( 'Last Name' ),
						'-2050-qualification' => TTi18n::gettext( 'Membership' ),
						'-2040-group'         => TTi18n::gettext( 'Group' ),
						'-4030-ownership'     => TTi18n::gettext( 'Ownership' ),
						'-1060-amount'        => TTi18n::gettext( 'Amount' ),
						'-2500-currency'      => TTi18n::gettext( 'Currency' ),
						'-1080-start_date'    => TTi18n::gettext( 'Start Date' ),
						'-4040-renewal_date'  => TTi18n::gettext( 'Renewal Date' ),
						'-1300-tag'           => TTi18n::gettext( 'Tags' ),

						'-1090-title'              => TTi18n::gettext( 'Title' ),
						'-1099-user_group'         => TTi18n::gettext( 'Employee Group' ),
						'-1100-default_branch'     => TTi18n::gettext( 'Branch' ),
						'-1110-default_department' => TTi18n::gettext( 'Department' ),

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
						'first_name',
						'last_name',
						'qualification',
						'ownership',
						'amount',
						'currency',
						'start_date',
						'renewal_date',
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
				'id'               => 'ID',
				'user_id'          => 'User',
				'first_name'       => false,
				'last_name'        => false,
				'qualification_id' => 'Qualification',
				'qualification'    => false,
				'group'            => false,
				'ownership_id'     => 'Ownership',
				'ownership'        => false,
				'amount'           => 'Amount',
				'currency_id'      => 'Currency',
				'currency'         => false,

				'start_date' => 'StartDate',

				'renewal_date' => 'RenewalDate',

				'tag'                => 'Tag',
				'default_branch'     => false,
				'default_department' => false,
				'user_group'         => false,
				'title'              => false,
				'deleted'            => 'Deleted',
		];

		return $variable_function_map;
	}

	/**
	 * @return bool
	 */
	function getQualificationObject() {

		return $this->getGenericObject( 'QualificationListFactory', $this->getQualification(), 'qualification_obj' );
	}

	/**
	 * @return bool|mixed
	 */
	function getUser() {
		return $this->getGenericDataValue( 'user_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setUser( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'user_id', $value );
	}

	/**
	 * @return bool
	 */
	function getQualification() {
		return $this->getGenericDataValue( 'qualification_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setQualification( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'qualification_id', $value );
	}


	/**
	 * @return bool|int
	 */
	function getOwnership() {
		return $this->getGenericDataValue( 'ownership_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setOwnership( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'ownership_id', $value );
	}


	/**
	 * @return bool|mixed
	 */
	function getCurrency() {
		return $this->getGenericDataValue( 'currency_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setCurrency( $value ) {
		$value = TTUUID::castUUID( $value );
		Debug::Text( 'Currency ID: ' . $value, __FILE__, __LINE__, __METHOD__, 10 );

		return $this->setGenericDataValue( 'currency_id', $value );
	}

	/*
	function getAmount() {
		return $this->getGenericDataValue( 'amount' );
	}

	function setAmount($int) {
		$int = trim($int);

		if	( empty($int) ) {
			$int = 0;
		}

		if	(	$this->Validator->isNumeric(		'amount',
													$int,
													TTi18n::gettext('Incorrect Amount'))
				) {

			$this->setGenericDataValue( 'amount', $int );

			return TRUE;
		}

		return FALSE;
	}
	*/

	/**
	 * @return bool|string
	 */
	function getAmount() {
		return TTMath::MoneyRound( $this->getGenericDataValue( 'amount' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setAmount( $value ) {
		$value = trim( $value );
		//Pull out only digits and periods.
		$value = $this->Validator->stripNonFloat( $value );

		return $this->setGenericDataValue( 'amount', TTMath::MoneyRound( $value ) );
	}


	/**
	 * @return bool
	 */
	function getStartDate() {
		return $this->getGenericDataValue( 'start_date' );
	}

	/**
	 * @param int $value EPOCH
	 * @return bool
	 */
	function setStartDate( $value ) {
		$value = ( !is_int( $value ) && $value !== null ) ? trim( $value ) : $value;//Dont trim integer values, as it changes them to strings.

		return $this->setGenericDataValue( 'start_date', $value );
	}


	/**
	 * @return bool
	 */
	function getRenewalDate() {
		return $this->getGenericDataValue( 'renewal_date' );
	}

	/**
	 * @param int $value EPOCH
	 * @return bool
	 */
	function setRenewalDate( $value ) {
		$value = ( !is_int( $value ) && $value !== null ) ? trim( $value ) : $value;//Dont trim integer values, as it changes them to strings.

		return $this->setGenericDataValue( 'renewal_date', $value );
	}

	/**
	 * @return bool|string
	 */
	function getTag() {
		//Check to see if any temporary data is set for the tags, if not, make a call to the database instead.
		//postSave() needs to get the tmp_data.
		$value = $this->getGenericTempDataValue( 'tags' );
		if ( $value !== false ) {
			return $value;
		} else if ( is_object( $this->getQualificationObject() ) && $this->getQualificationObject()->getCompany() > 0 && $this->getID() > 0 ) {
			return CompanyGenericTagMapListFactory::getStringByCompanyIDAndObjectTypeIDAndObjectID( $this->getQualificationObject()->getCompany(), 255, $this->getID() );
		}

		return false;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setTag( $value ) {
		$value = trim( $value );

		//Save the tags in temporary memory to be committed in postSave()
		return $this->setGenericTempDataValue( 'tags', $value );
	}

	/**
	 * @param bool $ignore_warning
	 * @return bool
	 */
	function Validate( $ignore_warning = true ) {
		//
		// BELOW: Validation code moved from set*() functions.
		//
		// Employee
		if ( $this->getUser() !== false ) {
			$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
			$this->Validator->isResultSetWithRows( 'user_id',
												   $ulf->getByID( $this->getUser() ),
												   TTi18n::gettext( 'Employee must be specified' )
			);
		}
		// Qualification
		if ( $this->getQualification() !== false ) {
			$qlf = TTnew( 'QualificationListFactory' ); /** @var QualificationListFactory $qlf */
			$this->Validator->isResultSetWithRows( 'qualification_id',
												   $qlf->getById( $this->getQualification() ),
												   TTi18n::gettext( 'Membership must be specified' )
			);
		}
		// Ownership
		if ( $this->getOwnership() !== false ) {
			$this->Validator->inArrayKey( 'ownership_id',
										  $this->getOwnership(),
										  TTi18n::gettext( 'Ownership is invalid' ),
										  $this->getOptions( 'ownership' )
			);
		}
		// Currency
		if ( $this->getCurrency() !== false ) {
			$culf = TTnew( 'CurrencyListFactory' ); /** @var CurrencyListFactory $culf */
			$this->Validator->isResultSetWithRows( 'currency_id',
												   $culf->getByID( $this->getCurrency() ),
												   TTi18n::gettext( 'Currency must be specified' )
			);
		}
		// Amount
		if ( $this->getAmount() == '' ) {
			$this->Validator->isTrue( 'amount',
									  false,
									  TTi18n::gettext( 'Amount must be specified' )
			);
		} else {
			$this->Validator->isFloat( 'amount',
									   $this->getAmount(),
									   TTi18n::gettext( 'Invalid Amount, Must be a numeric value' )
			);
		}

		// Start date
		if ( $this->getStartDate() !== false && $this->getStartDate() != '' ) {
			$this->Validator->isDate( 'start_date',
									  $this->getStartDate(),
									  TTi18n::gettext( 'Start date is invalid' )
			);
		} else if (  $this->Validator->getValidateOnly() == false && empty( $this->getStartDate() ) ) { //Don't check empty on mass edit.
			$this->Validator->isTrue( 'start_date',
									  false,
									  TTi18n::gettext( 'Start Date must be specified' )
			);
		}

		// Renewal date
		if ( $this->getRenewalDate() !== false && $this->getRenewalDate() != '' ) {
			$this->Validator->isDate( 'renewal_date',
									  $this->getRenewalDate(),
									  TTi18n::gettext( 'Renewal date is invalid' )
			);
		} else if (  $this->Validator->getValidateOnly() == false && empty( $this->getRenewalDate() ) ) { //Don't check empty on mass edit.
			$this->Validator->isTrue( 'renewal_date',
									  false,
									  TTi18n::gettext( 'Renewal Date must be specified' )
			);
		}

		//
		// ABOVE: Validation code moved from set*() functions.
		//
		//$this->setProvince( $this->getProvince() ); //Not sure why this was there, but it causes duplicate errors if the province is incorrect.

		return true;
	}

	/**
	 * @return bool
	 */
	function postSave() {
		$this->removeCache( $this->getId() );
		$this->removeCache( $this->getUser() . $this->getQualification() );

		if ( $this->getDeleted() == false ) {
			Debug::text( 'Setting Tags...', __FILE__, __LINE__, __METHOD__, 10 );
			CompanyGenericTagMapFactory::setTags( $this->getQualificationObject()->getCompany(), 255, $this->getID(), $this->getTag() );
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
						case 'start_date':
							$this->setStartDate( TTDate::parseDateTime( $data['start_date'] ) );
							break;
						case 'renewal_date':
							$this->setRenewalDate( TTDate::parseDateTime( $data['renewal_date'] ) );
							break;
						default:
							if ( method_exists( $this, $function ) ) {
								$this->$function( $data[$key] );
							}
							break;
					}
				}
			}

			$this->setCreatedAndUpdatedColumns( $data );

			return true;
		}

		return false;
	}

	/**
	 * @param null $include_columns
	 * @param bool $permission_children_ids
	 * @return array
	 */
	function getObjectAsArray( $include_columns = null, $permission_children_ids = false ) {
		$data = [];
		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach ( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == null || ( isset( $include_columns[$variable] ) && $include_columns[$variable] == true ) ) {

					$function = 'get' . $function_stub;

					switch ( $variable ) {
						case 'qualification':
						case 'group':
						case 'currency':
						case 'first_name':
						case 'last_name':
						case 'title':
						case 'user_group':
						case 'default_branch':
						case 'default_department':
							$data[$variable] = $this->getColumn( $variable );
							break;
						case 'ownership':
							$function = 'get' . $variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						case 'start_date':
							$data[$variable] = TTDate::getAPIDate( 'DATE', $this->getStartDate() );
							break;
						case 'renewal_date':
							$data['renewal_date'] = TTDate::getAPIDate( 'DATE', $this->getRenewalDate() );
							break;
						default:
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = $this->$function();
							}
							break;
					}
				}
			}
			$this->getPermissionColumns( $data, $this->getUser(), $this->getCreatedBy(), $permission_children_ids, $include_columns );

			$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}

	/**
	 * @param $log_action
	 * @return bool
	 */
	function addLog( $log_action ) {
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText( 'Membership' ), null, $this->getTable(), $this );
	}

}

?>
