<?php

/**
 * @package Modules\Install
 */
class InstallSchema_1151A extends InstallSchema_Base {

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
			foreach ( $clf as $c_obj ) {
				//Find existing CPP Tax/Deduction records so we can duplicate them for CPP2.
				$cdlf = TTnew( 'CompanyDeductionListFactory' ); /** @var CompanyDeductionListFactory $cdlf */
				$cdlf->getByCompanyIdAndCalculationId( $c_obj->getID(), 90 ); //90=CA - Canada Pension Plan (Employee)
				Debug::text( 'Found CPP related Tax/Deductions: ' . $cdlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 9 );
				if ( $cdlf->getRecordCount() > 0 ) {
					//Get "CPP" and "CPP - Employer" pay stub accounts, so we can duplicate them along with the Debit/Credit account settings.
					$psealf = TTnew( 'PayStubEntryAccountListFactory' ); /** @var PayStubEntryAccountListFactory $psealf */
					$psealf->getByCompanyIdAndTypeAndFuzzyName( $c_obj->getId(), 20, 'CPP' ); //20=EE Deduction
					Debug::text( 'Found CPP PS account: ' . $psealf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 9 );
					if ( $psealf->getRecordCount() == 1 ) {
						$ee_psa_obj = $psealf->getCurrent();
					} else {
						//Create CPP2 Pay Stub Account from scratch if we can't find one.
						$ee_psa_obj = TTnew('PayStubEntryAccountFactory');
					}
					$ee_psa_obj->setId( $ee_psa_obj->getNextInsertId() );
					$ee_psa_obj->setCompany( $c_obj->getId() );
					$ee_psa_obj->setStatus( 10 );
					$ee_psa_obj->setType( 20 ); //20=EE Deduction
					$ee_psa_obj->setName( 'CPP2' );
					$ee_psa_obj->setOrder( 204 );
					if ( $ee_psa_obj->isValid() ) {
						$ee_pay_stub_account_id = $ee_psa_obj->getId();
						$ee_psa_obj->Save( true, true );

						$psealf = TTnew( 'PayStubEntryAccountListFactory' ); /** @var PayStubEntryAccountListFactory $psealf */
						$psealf->getByCompanyIdAndTypeAndFuzzyName( $c_obj->getId(), 30, 'CPP - Employer' ); //30=ER Deduction
						Debug::text( 'Found CPP - Employer PS account: ' . $psealf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 9 );
						if ( $psealf->getRecordCount() == 1 ) {
							$er_psa_obj = $psealf->getCurrent();
						} else {
							//Create CPP2 - Employer Pay Stub Account from scratch if we can't find one.
							$er_psa_obj = TTnew('PayStubEntryAccountFactory');
						}
						$er_psa_obj->setId( $er_psa_obj->getNextInsertId() );
						$er_psa_obj->setCompany( $c_obj->getId() );
						$er_psa_obj->setStatus( 10 );
						$er_psa_obj->setType( 30 ); //30=ER Deduction
						$er_psa_obj->setName( 'CPP2 - Employer' );
						$er_psa_obj->setOrder( 304 );
						if ( $er_psa_obj->isValid() ) {
							$er_pay_stub_account_id = $er_psa_obj->getId();
							$er_psa_obj->Save( true, true );

							if ( TTUUID::isUUID( $ee_pay_stub_account_id ) && TTUUID::isUUID( $er_pay_stub_account_id ) ) {
								foreach ( $cdlf as $cd_obj ) {
									if ( $cd_obj->getCalculation() == 90 ) {
										Debug::text( 'Found CPP calculation: ' . $cd_obj->getId() . ' duplicating it for CPP2...', __FILE__, __LINE__, __METHOD__, 9 );

										//Clone the object for EE and ER parts.
										$ee_cd_obj = clone $cd_obj;
										$er_cd_obj = clone $cd_obj;

										//Create the employee part.
										$ee_cd_obj->setId( $ee_cd_obj->getNextInsertId() );
										$ee_cd_obj->setCalculation( 92 ); //92=CPP2
										$ee_cd_obj->setCalculationOrder( 81 ); //After CPP
										$ee_cd_obj->setName( 'CPP2 - Employee' );
										$ee_cd_obj->setPayStubEntryAccount( $ee_pay_stub_account_id );
										$ee_cd_obj->setIncludePayStubEntryAccount( $cd_obj->getIncludePayStubEntryAccount() );
										$ee_cd_obj->setExcludePayStubEntryAccount( $cd_obj->getExcludePayStubEntryAccount() );
										//$ee_cd_obj->setUser( $cd_obj->getUser() ); //This is handled below in the UserDeduction part, as we need to carry over start/end dates.
										if ( $ee_cd_obj->isValid() ) {
											$ee_cd_obj->Save( false, true );        //Keep the object around.

											//Copy UserDeduction records in case they have start/end dates specified. This also assigns the employee to the new CPP2 Tax/Deduction.
											Debug::Text( 'Copy over per employee data...', __FILE__, __LINE__, __METHOD__, 10 );
											$udlf = TTnew( 'UserDeductionListFactory' ); /** @var UserDeductionListFactory $udlf */
											$udlf->getByCompanyIdAndCompanyDeductionId( $c_obj->getId(), $cd_obj->getId() );
											if ( $udlf->getRecordCount() > 0 ) {
												foreach( $udlf as $ud_obj ) {
													Debug::Text( '  Found UserDeduction Data: User ID: '. $ud_obj->getUser() .' ID: '. $ud_obj->getID() .' Start Date: '. $ud_obj->getStartDate() .' End Date: '. $ud_obj->getEndDate(), __FILE__, __LINE__, __METHOD__, 10 );
													$ud_obj->setId( $ud_obj->getNextInsertId() );
													$ud_obj->setCompanyDeduction( $ee_cd_obj->getId() );
													if ( $ud_obj->isValid() ) {
														$ud_obj->Save( true, true );
													}
												}

											}
											unset( $udlf, $ud_obj );

											//Create the Employer part.
											$er_cd_obj->setId( $er_cd_obj->getNextInsertId() );
											$er_cd_obj->setCalculation( 10 ); //10=Percent
											$er_cd_obj->setCalculationOrder( 86 ); //After CPP
											$er_cd_obj->setName( 'CPP2 - Employer' );
											$er_cd_obj->setUserValue1( 100 ); //100%
											$er_cd_obj->setPayStubEntryAccount( $er_pay_stub_account_id );
											$er_cd_obj->setIncludePayStubEntryAccount( $ee_pay_stub_account_id );
											$er_cd_obj->setUser( $cd_obj->getUser() );
											if ( $er_cd_obj->isValid() ) {
												$er_cd_obj->Save( false, true );

												//Update New hire Defaults
												$udlf = TTnew('UserDefaultListFactory'); /** @var UserDefaultListFactory $udlf */
												$udlf->getByCompanyId( $c_obj->getId() );
												if ( $udlf->getRecordCount() > 0 ) {
													foreach( $udlf as $ud_obj ) { /** @var UserDefaultFactory $ud_obj */
														$ud_company_deduction_ids = $ud_obj->getCompanyDeduction();
														if ( !is_array( $ud_company_deduction_ids ) ) {
															$ud_company_deduction_ids = [];
														}

														if ( in_array( $cd_obj->getId(), $ud_company_deduction_ids ) ) {
															Debug::Text( 'Updating UserDefault: ' . $ud_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );
															$ud_obj->setCompanyDeduction( array_merge( $ud_company_deduction_ids, [ $ee_cd_obj->getId(), $er_cd_obj->getId() ] ) );
														} else {
															Debug::Text( 'Skipping UserDefault: ' . $ud_obj->getId() .' as Tax/Deduction ID: '. $cd_obj->getId() .' is not assigned to it...', __FILE__, __LINE__, __METHOD__, 10 );
														}
													}
												}
												unset( $udlf, $ud_obj );

												//T4 Form Setup
												$urdlf = TTnew( 'UserReportDataListFactory' ); /** @var UserReportDataListFactory $urdlf */
												$urdlf->getByCompanyIdAndScriptAndDefault( $c_obj->getId(), 'T4SummaryReport' );
												if ( $urdlf->getRecordCount() > 0 ) {
													foreach( $urdlf as $urd_obj ) {
														$tmp_urd_data = $urd_obj->getData();
														$tmp_urd_data['employee_cpp2']['include_pay_stub_entry_account'] = [ $ee_pay_stub_account_id ];
														$tmp_urd_data['employer_cpp2']['include_pay_stub_entry_account'] = [ $er_pay_stub_account_id ];
														$urd_obj->setData( $tmp_urd_data );
														if ( $urd_obj->isValid() ) {
															$urd_obj->Save();
														}
														unset( $tmp_urd_data );
													}
												}
												unset( $urdlf, $urd_obj );

												//Remittance Summary Form Setup
												$urdlf = TTnew( 'UserReportDataListFactory' ); /** @var UserReportDataListFactory $urdlf */
												$urdlf->getByCompanyIdAndScriptAndDefault( $c_obj->getId(), 'RemittanceSummaryReport' );
												if ( $urdlf->getRecordCount() > 0 ) {
													foreach( $urdlf as $urd_obj ) {
														$tmp_urd_data = $urd_obj->getData();
														$tmp_urd_data['cpp2']['include_pay_stub_entry_account'] = [ $ee_pay_stub_account_id,  $er_pay_stub_account_id ];
														$urd_obj->setData( $tmp_urd_data );
														if ( $urd_obj->isValid() ) {
															$urd_obj->Save();
														}
														unset( $tmp_urd_data );
													}
												}
												unset( $urdlf, $urd_obj );

												//Check if they have paid pay stubs in the last three months, if so, send them a notification.
												$pslf = TTnew( 'PayStubListFactory' ); /** @var PayStubListFactory $pslf */
												$pslf->getByCompanyIdAndStartDateAndEndDate( $c_obj->getId(), strtotime( '2023-09-01' ), strtotime( '2023-12-31' ), 1 );
												if ( $pslf->getRecordCount() > 0 ) {
													//Sent notification
													$notification_data = [
															'object_id'      => TTUUID::getNotExistID( 1040 ),
															'object_type_id' => 0,
															'priority_id'	 => 2, //High
															'type_id'        => 'system',
															'title_short'    => TTi18n::getText( 'ACTION REQUIRED: CPP2 deductions have been added for 2024.' ),
															'body_short'     => TTi18n::getText( 'Starting January 1, 2024, employee earnings over the maximum pensionable amount will incur additional CPP2 contributions. Settings review required.', [ APPLICATION_NAME ] ),
															'body_long' 	 => TTi18n::getText( 'We want to inform you of an important update to your payroll settings that will be effective from January 1st, 2024.

What\'s Changing:
- The Government of Canada has implemented the second phase of additional Canada Pension Plan contributions, known as CPP2. These contributions will be automatically deducted from earnings that exceed the annual maximum pensionable earnings of $68,500.
- This setup has been completed automatically for your account and applies to all employees who are currently subject to CPP deductions. Eligible employees will see the new deductions on their 2024 pay stubs once their pensionable earnings exceed the annual maximum.

ACTION REQUIRED:
- For step-by-step instructions of what actions are required, please view the release notes by clicking on this message.

As always, please contact our support department if you have any questions or need further clarification.

--
TimeTrex Support', [ APPLICATION_NAME ] ), //Use this to append email footer.
															'payload'        => [ 'link_target' => '_blank', 'link' => 'https://coreapi.timetrex.com/h.php?id=changelog_cpp2&v=' . APPLICATION_VERSION . '&e=' . getTTProductEdition() ],
													];

													Notification::sendNotificationToAllUsers( 80, true, true, $notification_data, ( 7 * 86400 ), $c_obj->getId() ); //Send to all payroll admins or higher.
												} else {
													Debug::text( 'NOTICE: No pay stubs in the last four months of 2023...', __FILE__, __LINE__, __METHOD__, 9 );
												}
											} else {
												Debug::text( 'ERROR: Failed saving CPP2 Employer Tax / Deduction...', __FILE__, __LINE__, __METHOD__, 9 );
											}
										} else {
											Debug::text( 'ERROR: Failed saving CPP2 Employee Tax / Deduction...', __FILE__, __LINE__, __METHOD__, 9 );
										}
										unset( $ee_cd_obj, $er_cd_obj );
									}
								}
							} else {
								Debug::text( 'ERROR: Failed creating CPP2 Pay Stub Accounts...', __FILE__, __LINE__, __METHOD__, 9 );
							}
						} else {
							Debug::text( 'ERROR: Failed saving CPP2 - Employer Pay Stub Account...', __FILE__, __LINE__, __METHOD__, 9 );
						}
						unset( $psealf, $er_psa_obj );

					} else {
						Debug::text( 'ERROR: Failed saving CPP2 Pay Stub Account...', __FILE__, __LINE__, __METHOD__, 9 );
					}
					unset( $psealf, $ee_psa_obj );
				}

				unset( $ee_pay_stub_account_id, $er_pay_stub_account_id, $c_obj );
			}
		}

		//$clf->FailTransaction(); //ZZZ REMOVE ME
		$clf->CommitTransaction();

		return true;
	}
}

?>
