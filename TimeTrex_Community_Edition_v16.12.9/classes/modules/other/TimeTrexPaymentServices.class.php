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
 * Class TimeTrexPaymentServices
 */
class TimeTrexPaymentServices {
	protected $url = 'https://paymentservices.timetrex.com/api/soap/api.php';

	protected $user_name = null;
	protected $password = null;

	/**
	 * Constructor.
	 * @param string $user_name
	 * @param string $password
	 */
	function __construct( $user_name = null, $password = null ) {
		require_once( Environment::getBasePath() . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'other' . DIRECTORY_SEPARATOR . 'PaymentServicesClientAPI.class.php' );

		global $PAYMENTSERVICES_USER, $PAYMENTSERVICES_PASSWORD, $PAYMENTSERVICES_URL;
		$PAYMENTSERVICES_USER = $user_name;
		$PAYMENTSERVICES_PASSWORD = $password;
		$PAYMENTSERVICES_URL = $this->url;

		return true;
	}

	/**
	 * Converts a remittance source account object to a remittance bank account array for uploading.
	 * @param $rs_obj
	 * @return array
	 */
	function convertRemittanceSourceAccountObjectToBankAccountArray( $rs_obj ) {
		$remittances_bank_account_data = [
				'_kind'     => 'BankAccount',
				'remote_id' => $rs_obj->getID(),

				'type_id' => 'S', //Settlement

				'name' => $rs_obj->getName(),

				'domiciled_country' => $rs_obj->getCountry(),
				'currency_iso_code' => $rs_obj->getCurrencyObject()->getISOCode(),

				'bank_account_type'   => 'C', //Checking
				'bank_routing_number' => ( $rs_obj->getCountry() == 'CA' ) ? str_pad( $rs_obj->getValue1(), 4, 0, STR_PAD_LEFT ) . str_pad( $rs_obj->getValue2(), 5, 0, STR_PAD_LEFT ) : $rs_obj->getValue2(),
				'bank_account_number' => $rs_obj->getValue3(),

				'deleted' => $rs_obj->getDeleted(),
		];

		Debug::Arr( $remittances_bank_account_data, 'Retval: ', __FILE__, __LINE__, __METHOD__, 10 );

		return $remittances_bank_account_data;
	}

	/**
	 * Converts a remittance agency object to a agency authorization array for uploading.
	 * @param $rae_obj
	 * @return array|bool
	 */
	function convertRemittanceAgencyEventObjectToAgencyAuthorizationArray( $rae_obj ) {
		if ( !is_object( $rae_obj ) ) {
			return false;
		}

		$remittance_agency_data = [
				'_kind'        => 'AgencyAuthorization',
				'remote_id'    => $rae_obj->getID(),

				//'status_id'   => 'P', //Pending Authorization -- This is handled automatically on the remote end.
				'form_type_id' => $rae_obj->getType(),
				'frequency_id' => $rae_obj->getFrequency(),
				'agency_id'    => $rae_obj->getPayrollRemittanceAgencyObject()->getAgency(),

				'primary_identification'   => $rae_obj->getPayrollRemittanceAgencyObject()->getPrimaryIdentification(),
				'secondary_identification' => $rae_obj->getPayrollRemittanceAgencyObject()->getSecondaryIdentification(),
				'tertiary_identification'  => $rae_obj->getPayrollRemittanceAgencyObject()->getTertiaryIdentification(),

				'deleted' => $rae_obj->getDeleted(),
		];

		Debug::Arr( $remittance_agency_data, 'Retval: ', __FILE__, __LINE__, __METHOD__, 10 );

		return $remittance_agency_data;
	}

	/**
	 * Converts a remittance destination account to a remittance bank account array for uploading.
	 * @param $rd_obj
	 * @param $rs_obj
	 * @param $u_obj
	 * @return array
	 */
	function convertRemittanceDestinationAccountObjectToBankAccountArray( $rd_obj, $rs_obj, $u_obj ) {
		$country = $rs_obj->getCountry();

		$remittances_bank_account_data = [
				'_kind'     => 'BankAccount',
				'remote_id' => $rd_obj->getID(),

				'type_id' => 'N', //Normal

				'name' => $u_obj->getFullName( true ),

				'domiciled_country' => $country,
				'currency_iso_code' => $rd_obj->getRemittanceSourceAccountObject()->getCurrencyObject()->getISOCode(),

				'bank_account_type'   => ( $rd_obj->getValue1() == '32' ) ? 'S' : 'C', //S=Savings, C=Checking
				'bank_routing_number' => ( $country == 'CA' ) ? str_pad( $rd_obj->getValue1(), 4, 0, STR_PAD_LEFT ) . str_pad( $rd_obj->getValue2(), 5, 0, STR_PAD_LEFT ) : $rd_obj->getValue2(),
				'bank_account_number' => $rd_obj->getValue3(),

				'deleted' => $rd_obj->getDeleted(),
		];

		Debug::Arr( $remittances_bank_account_data, 'Retval: ', __FILE__, __LINE__, __METHOD__, 10 );

		return $remittances_bank_account_data;
	}


	/**
	 * @param $end_date
	 * @param null $run_id
	 * @return false|string
	 */
	function generateBatchID( $end_date, $run_id = null ) {
		//APR 02 R01 -- Only the first 7 characters are shown on settlement transactions.
		//was: PP APR02 R01
		//$batch_id = 'PP '. date( 'Md', $end_date ) .' R'. str_pad( $run_id, 2, 0, STR_PAD_LEFT ); //Must be 15 or less characters.
		$batch_id = date( 'M d', $end_date );

		if ( $run_id != '' ) {
			$batch_id .= ' R' . str_pad( $run_id, 2, 0, STR_PAD_LEFT ); //Must be 15 or less characters.
		}

		Debug::Text( 'Batch ID: ' . $batch_id, __FILE__, __LINE__, __METHOD__, 10 );

		return $batch_id;
	}

	/**
	 * Converts Pay Stub Transaction objects to a remittance transaction array for uploading.
	 * @param $pst_obj
	 * @param $ps_obj
	 * @param $rs_obj
	 * @param $uf_obj
	 * @param $confirmation_number
	 * @param $batch_id
	 * @return array
	 */
	function convertPayStubTransactionObjectToTransactionArray( $pst_obj, $ps_obj, $rs_obj, $uf_obj, $confirmation_number, $batch_id ) {
		$settlement_bank_account_data = $this->convertRemittanceSourceAccountObjectToBankAccountArray( $rs_obj );
		$bank_account_data = $this->convertRemittanceDestinationAccountObjectToBankAccountArray( $pst_obj->getRemittanceDestinationAccountObject(), $rs_obj, $uf_obj );

		if ( is_object( $ps_obj->getPayPeriodObject() ) ) {
			//PP APR 02R01
			$batch_id = $this->generateBatchID( $ps_obj->getPayPeriodObject()->getEndDate(), $ps_obj->getRun() );
			//$batch_id = 'PP '. date( 'M d', $ps_obj->getPayPeriodObject()->getEndDate() ) .'R'. str_pad( $ps_obj->getRun(), 2, 0, STR_PAD_LEFT ); //Must be 15 or less characters.
		}

		$remittances_transaction_data = [
				'_kind'           => 'Transaction',
				'remote_id'       => $pst_obj->getID(),
				'remote_batch_id' => $batch_id,

				'settlement_bank_account_id' => $settlement_bank_account_data,
				'bank_account_id'            => $bank_account_data,

				'category_id' => 'DD', //Direct Deposit
				'type_id'     => 'C', //Credit

				'name'             => $uf_obj->getFullName( true ),
				'reference_number' => $confirmation_number,

				//Use the Pay Stub Transaction Object Transaction Date, rather than the pay stub transaction date itself, as in cases where the customer might need to back-date the pay stub transaction date, but forward date the direct deposit itself.
				// The batch_id is still based on the pay stub date itself, so that should continue to match.
				'due_date'         => TTDate::getISOTimeStamp( $pst_obj->getTransactionDate() ), //Don't pass epoch as the remote system won't know the timezone.

				'amount' => $pst_obj->getAmount(),
		];

		Debug::Arr( $remittances_transaction_data, 'Retval: ', __FILE__, __LINE__, __METHOD__, 10 );

		return $remittances_transaction_data;
	}

	/**
	 * Converts a client payment object to a remittance bank account array for uploading.
	 * @param $invoice_transaction_obj TransactionFactory
	 * @param $client_payment_obj      ClientPaymentFactory
	 * @param $client_obj              ClientFactory
	 * @param $client_contact_obj      ClientContactFactory
	 * @return array
	 */
	function convertClientPaymentObjectToBankAccountArray( $invoice_transaction_obj, $client_payment_obj, $client_obj, $client_contact_obj ) { //$rd_obj, $rs_obj, $u_obj ) {
		//$country = $client_contact_obj->getCountry();
		if ( $client_payment_obj->getBankAccountType() == 200 ) {
			Debug::Text( 'Using PaymentProcess Factory: EFT', __FILE__, __LINE__, __METHOD__, 10 );
			$country = 'CA';
		} else if ( $client_payment_obj->getBankAccountType() == 201 ) {
			Debug::Text( 'Using PaymentProcess Factory: ACH', __FILE__, __LINE__, __METHOD__, 10 );
			$country = 'US';
		}

		$bank_account_data = [
				'_kind'     => 'BankAccount',
				'remote_id' => $client_payment_obj->getID(),

				'type_id' => 'N', //Normal

				'name' => $client_obj->getCompanyName(),

				'domiciled_country' => $country,
				'currency_iso_code' => $invoice_transaction_obj->getCurrencyObject()->getISOCode(),

				'bank_account_type'   => 'C', //S=Savings, C=Checking
				'bank_routing_number' => ( $country == 'CA' ) ? str_pad( $client_payment_obj->getInstitution(), 4, 0, STR_PAD_LEFT ) . str_pad( $client_payment_obj->getTransit(), 5, 0, STR_PAD_LEFT ) : $client_payment_obj->getTransit(),
				'bank_account_number' => $client_payment_obj->getAccount(),

				'deleted' => $invoice_transaction_obj->getDeleted(),
		];

		Debug::Arr( $bank_account_data, 'Retval: ', __FILE__, __LINE__, __METHOD__, 10 );

		return $bank_account_data;
	}

	/**
	 * Converts Invoice Transaction objects to a remittance transaction array for uploading.
	 * @param $invoice_transaction_obj TransactionFactory
	 * @param $client_payment_obj      ClientPaymentFactory
	 * @param $client_obj              ClientFactory
	 * @param $client_contact_obj      ClientContactFactory
	 * @param $payment_gateway_obj     PaymentGatewayFactory
	 * @param $confirmation_number
	 * @return array
	 */
	function convertInvoiceTransactionObjectToTransactionArray( $invoice_transaction_obj, $client_payment_obj, $client_obj, $client_contact_obj, $payment_gateway_obj, $confirmation_number ) { //, $rs_obj, $uf_obj, $confirmation_number, $batch_id ) {
		//$settlement_bank_account_data = $this->convertRemittanceSourceAccountObjectToBankAccountArray( $rs_obj );
		$settlement_bank_account_data = $payment_gateway_obj->getCustomerID();                                                                                                                  //Customer ID is the UUID of the payment services settlement bank account ID.
		$bank_account_data = $this->convertClientPaymentObjectToBankAccountArray( $invoice_transaction_obj, $client_payment_obj, $client_obj, $client_contact_obj );

		if ( is_object( $invoice_transaction_obj ) ) {
			$batch_id = date( 'M d', $invoice_transaction_obj->getEffectiveDate() ); //Must be 9 or less characters, as its prepended with "TT AR " and has to be less than 15 characters overall.
		}

		$remittances_transaction_data = [
				'_kind'           => 'Transaction',
				'remote_id'       => $invoice_transaction_obj->getID(),
				'remote_batch_id' => $batch_id,

				'settlement_bank_account_id' => $settlement_bank_account_data,
				'bank_account_id'            => $bank_account_data,

				'category_id' => 'AR', //Accounts Receivable
				'type_id'     => 'D', //Debit

				'name'             => $client_obj->getCompanyName(),
				'reference_number' => $confirmation_number,
				'due_date'         => TTDate::getISOTimeStamp( $invoice_transaction_obj->getEffectiveDate() ), //Don't pass epoch as the remote system won't know the timezone.

				'amount' => $invoice_transaction_obj->getAmount(),
		];

		Debug::Arr( $remittances_transaction_data, 'Retval: ', __FILE__, __LINE__, __METHOD__, 10 );

		return $remittances_transaction_data;
	}

	/**
	 * Converts a legal entity object to a remittance organization array for uploading.
	 * @param $le_obj
	 * @return array
	 */
	function convertLegalEntityObjectToOrganizationArray( $le_obj ) {
		$validator = new Validator();

		$obj_class = "\124\124\114\x69\x63\x65\x6e\x73\x65"; $license = @new $obj_class;

		$primary_identification = null;

		//Get federal remittance agency in an attempt to get EIN/Business number.
		$filter_data['legal_entity_id'] = $le_obj->getId();

		if ( strtoupper( $le_obj->getCountry() ) == 'CA' ) {
			$filter_data['agency_id'] = [ '10:CA:00:00:0010' ]; //CA federal
		} else if ( strtoupper( $le_obj->getCountry() ) == 'US' ) {
			$filter_data['agency_id'] = [ '10:US:00:00:0010' ]; //US federal
		}

		$ralf = TTnew( 'PayrollRemittanceAgencyListFactory' ); /** @var PayrollRemittanceAgencyListFactory $ralf */
		$ralf->getAPISearchByCompanyIdAndArrayCriteria( $le_obj->getCompany(), $filter_data );
		if ( $ralf->getRecordCount() > 0 ) {
			$ra_obj = $ralf->getCurrent();

			$primary_identification = $ra_obj->getPrimaryIdentification();
		}

		$legal_entity_data = [
				'_kind' => 'Organization',

				'remote_id' => $le_obj->getID(),

				'legal_name'             => $le_obj->getLegalName(),
				'trade_name'             => $le_obj->getTradeName(),
				'short_name'             => ( $le_obj->getShortName() != '' ) ? $le_obj->getShortName() : substr( $validator->stripNonAlphaNumeric( trim( $le_obj->getTradeName() ) ), 0, 15 ), //Short was recently added to legal entities, so if its not defined, use the first 15 chars of trade name instead.
				'primary_identification' => $primary_identification, //Obtain from federal remittance agency

				'address1'    => $le_obj->getAddress1(),
				'address2'    => $le_obj->getAddress2(),
				'city'        => $le_obj->getCity(),
				'province'    => $le_obj->getProvince(),
				'country'     => $le_obj->getCountry(),
				'postal_code' => $le_obj->getPostalCode(),
				'work_phone'  => $le_obj->getWorkPhone(),

				'extra_data' => [
						'company_name'     => $le_obj->getCompanyObject()->getName(),
						'registration_key' => SystemSettingFactory::getSystemSettingValueByKey( 'registration_key' ),
						'hardware_id'      => $license->getHardwareID(),
						'company_id'       => $le_obj->getCompany(),
						'legal_entity_id'  => $le_obj->getId(),
				],

				'deleted' => $le_obj->getDeleted(),
		];

		Debug::Arr( $legal_entity_data, 'Retval: ', __FILE__, __LINE__, __METHOD__, 10 );

		return $legal_entity_data;
	}

	/**
	 * Converts a user object to a remittance user array for uploading.
	 * @param $u_obj
	 * @param null $remote_organization_id
	 * @return array
	 */
	function convertUserObjectToUserArray( $u_obj, $remote_organization_id = null ) {
		$user_data = [
				'_kind'     => 'User',
				'remote_id' => $u_obj->getID(),

				'user_name' => $u_obj->getUserName(),

				'first_name'  => $u_obj->getFirstName(),
				'middle_name' => $u_obj->getMiddleName(),
				'last_name'   => $u_obj->getLastName(),

				'address1'       => $u_obj->getAddress1(),
				'address2'       => $u_obj->getAddress2(),
				'city'           => $u_obj->getCity(),
				'province'       => $u_obj->getProvince(),
				'country'        => $u_obj->getCountry(),
				'postal_code'    => $u_obj->getPostalCode(),
				'work_phone'     => $u_obj->getWorkPhone(),
				'work_phone_ext' => $u_obj->getWorkPhoneExt(),
				'home_phone'     => $u_obj->getHomePhone(),
				'mobile_phone'   => $u_obj->getMobilePhone(),

				'birth_date' => ( $u_obj->getBirthDate() != '' ) ? TTDate::getISODateStamp( $u_obj->getBirthDate() ) : '',
				'sin'        => $u_obj->getSIN(),

				'work_email' => $u_obj->getWorkEmail(),
				'home_email' => $u_obj->getHomeEmail(),

				'deleted' => $u_obj->getDeleted(),
		];

		if ( $remote_organization_id != '' ) {
			$user_data['organization_id'] = $remote_organization_id;
		}

		Debug::Arr( $user_data, 'Retval: ', __FILE__, __LINE__, __METHOD__, 10 );

		return $user_data;
	}

	/**
	 * Converts getPaymentServicesData() results to an AgencyReport array for uploading.
	 * @param array $report_data
	 * @param object $prae_obj
	 * @param object $pra_obj
	 * @param object $rs_obj
	 * @param object $pra_user_obj
	 * @return array|bool
	 */
	function convertReportPaymentServicesDataToAgencyReportArray( $report_data, $prae_obj, $pra_obj, $rs_obj, $pra_user_obj ) {
		if ( !isset( $report_data['agency_report_data'] ) ) {
			Debug::Arr( $report_data, 'ERROR! Invalid Agency Report Data! ', __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		$settlement_bank_account_data = $this->convertRemittanceSourceAccountObjectToBankAccountArray( $rs_obj );

		if ( isset( $report_data['object'] ) ) {
			Debug::Text( 'Report Object: ' . $report_data['object'], __FILE__, __LINE__, __METHOD__, 10 );
		}

		$agency_report_data = [
				'_kind' => 'AgencyReport',

				'agency_id' => $prae_obj->getPayrollRemittanceAgencyObject()->getAgency(),

				'settlement_bank_account_id' => $settlement_bank_account_data,

				'status_id'    => 'P', //Pending
				'type_id'      => 'D', //Deposit/Estimate
				'form_type_id' => $prae_obj->getType(),

				'period_start_date' => TTDate::getISODateStamp( $prae_obj->getStartDate() ),
				'period_end_date'   => TTDate::getISODateStamp( $prae_obj->getEndDate() ),
				'due_date'          => TTDate::getISOTimeStamp( $prae_obj->getDueDate() ),

				'frequency_id' => $prae_obj->getFrequency(),

		];

		$agency_report_data = array_merge( $agency_report_data, $report_data['agency_report_data'] );

		Debug::Arr( $agency_report_data, 'Retval: ', __FILE__, __LINE__, __METHOD__, 10 );

		return $agency_report_data;
	}

	/**
	 * Converts ROE objects to a agency report array for uploading.
	 * @param $form_obj
	 * @param $rae_obj
	 * @param $ra_obj
	 * @param $remote_id
	 * @param $batch_id
	 * @return array
	 */
	function convertROEToAgencyReportArray( $form_obj, $rae_obj, $ra_obj, $batch_id ) {
		//Separate ROE form object into a separate object for each record.
		foreach( $form_obj->getForms() as $tmp_form_obj ) {
			foreach( $tmp_form_obj->getRecords() as $tmp_form_record ) {
				$gf = new GovernmentForms(); //Always start with a fresh GovernmentForm object so there is no way we can mix forms between employees.
				$new_roe_obj = clone $tmp_form_obj; //Clone the form record we are on, so we can eliminate all other records.
				$new_roe_obj->clearRecords(); //Clear all records so we start from a clean slate
				$new_roe_obj->addRecord( $tmp_form_record ); //Add just this single employees record to the form.
				$gf->addForm( $new_roe_obj ); //Add just this single employees form.
				$xml_data = $gf->output( 'XML' ); //Make sure the ROEHEADER element is always retained as it contains ROE version numbers and such.

				$agency_report_data[] = [
						'_kind' => 'AgencyReport',

						'agency_id' => $rae_obj->getPayrollRemittanceAgencyObject()->getAgency(),

						'status_id'    => 'P', //Pending
						'type_id'      => 'R', //Report
						'form_type_id' => $rae_obj->getType(),

						'period_start_date' => TTDate::getISODateStamp( $tmp_form_record['first_date'] ),
						'period_end_date'   => TTDate::getISODateStamp( $tmp_form_record['last_date'] ),
						'due_date'          => TTDate::getISOTimeStamp( TTDate::getMiddleDayEpoch( ( time() + 86400 ) ) ), //Set due date to the next day.

						'total_employees' => 1,
						'subject_wages'   => 0,
						'taxable_wages'   => 0,
						'amount_withheld' => 0,
						'amount_due'      => 0,

						'extra_data' => $tmp_form_record,

						'xml_data' => $xml_data,

						'frequency_id' => $rae_obj->getFrequency(),

						'remote_id'       => $tmp_form_record['id'],
						'remote_batch_id' => $batch_id,
				];
			}
		}

		Debug::Arr( $agency_report_data, 'Retval: ', __FILE__, __LINE__, __METHOD__, 10 );
		return $agency_report_data;
	}

	/**
	 * Converts T4 objects to a agency report array for uploading.
	 * @param $form_obj
	 * @param $report_data
	 * @param $rae_obj
	 * @param $ra_obj
	 * @param $remote_id
	 * @param $batch_id
	 * @return array
	 */
	function convertT4ToAgencyReportArray( $form_obj, $report_data, $rae_obj, $ra_obj, $remote_id, $batch_id ) {
		$xml_data = $form_obj->output( 'XML' );

		$agency_report_data = [
				'_kind' => 'AgencyReport',

				'agency_id' => $rae_obj->getPayrollRemittanceAgencyObject()->getAgency(),

				'status_id'    => 'P', //Pending
				'type_id'      => 'R', //Report
				'form_type_id' => $rae_obj->getType(),

				'period_start_date' => TTDate::getISODateStamp( $rae_obj->getStartDate() ), //$pay_period_start_date,
				'period_end_date'   => TTDate::getISODateStamp( $rae_obj->getEndDate() ),
				'due_date'          => TTDate::getISOTimeStamp( $rae_obj->getDueDate() ), //They can be filed earlier than their due date.

				'total_employees' => count( $report_data ),
				'subject_wages'   => 0,
				'taxable_wages'   => 0,
				'amount_withheld' => 0,
				'amount_due'      => 0,

				'extra_data' => $report_data,

				'xml_data' => $xml_data,

				'frequency_id' => $rae_obj->getFrequency(),

				'remote_id'       => $remote_id,
				'remote_batch_id' => $batch_id,
		];

		Debug::Arr( $agency_report_data, 'Retval: ', __FILE__, __LINE__, __METHOD__, 10 );

		return $agency_report_data;
	}

	/**
	 * Uploads organization data to TimeTrex PaymentServices.
	 * @param $rows
	 * @return bool
	 */
	function setOrganization( $rows ) {
		if ( isset( $rows['_kind'] ) ) {
			$rows = [ $rows ];
		}

		foreach ( $rows as $row ) {
			$api = new PaymentServicesClientAPI( 'APIOrganization' );
			$api_result = $api->setOrganization( $row );
			if ( $api_result !== false ) {
				if ( $api_result->isValid() === true ) {
					Debug::Text( 'PaymentServices API: Retval: ' . $api_result->getResult(), __FILE__, __LINE__, __METHOD__, 10 );
				} else {
					Debug::Arr( $api_result, 'PaymentServices API: Retval: ' . $api_result->getResult(), __FILE__, __LINE__, __METHOD__, 10 );

					return false; //This will trigger a general error to the user.
				}
			} else {
				//Debug::Arr( $api_result, 'PaymentServices API: Retval: ' . $api_result->getResult(), __FILE__, __LINE__, __METHOD__, 10 );
				return false; //This will trigger a general error to the user.
			}
		}

		return true;
	}


	/**
	 * @param $id
	 * @return bool
	 */
	function getUser( $id ) {
		$api = new PaymentServicesClientAPI( 'APIOrganization' );
		$api_result = $api->getUser( [ 'id' => $id ] );
		if ( $api_result !== false ) {
			if ( $api_result->isValid() === true ) {
				Debug::Text( 'PaymentServices API: Retval: ' . $api_result->getResult(), __FILE__, __LINE__, __METHOD__, 10 );

				return $api_result->getResultData();
			} else {
				Debug::Arr( $api_result, 'PaymentServices API: Retval: ' . $api_result->getResult(), __FILE__, __LINE__, __METHOD__, 10 );
			}
		}

		return false;
	}

	/**
	 * Uploads user data to TimeTrex PaymentServices.
	 * @param $rows
	 * @return bool
	 */
	function setUser( $rows ) {
		if ( isset( $rows['_kind'] ) ) {
			$rows = [ $rows ];
		}

		foreach ( $rows as $row ) {
			$api = new PaymentServicesClientAPI( 'APIUser' );
			$api_result = $api->setUser( $row );
			if ( $api_result !== false ) {
				if ( $api_result->isValid() === true ) {
					Debug::Text( 'PaymentServices API: Retval: ' . $api_result->getResult(), __FILE__, __LINE__, __METHOD__, 10 );
				} else {
					Debug::Arr( $api_result, 'PaymentServices API: Retval: ' . $api_result->getResult(), __FILE__, __LINE__, __METHOD__, 10 );

					return false; //This will trigger a general error to the user.
				}
			} else {
				//Debug::Arr( $api_result, 'PaymentServices API: Retval: ' . $api_result->getResult(), __FILE__, __LINE__, __METHOD__, 10 );
				return false; //This will trigger a general error to the user.
			}
		}

		return true;
	}

	/**
	 * Create organization from Legal Entity
	 * @param $row
	 * @return bool
	 */
	function createNewOrganization( $row ) {
		if ( isset( $row['_kind'] ) ) {
			$row = [ $row ];
		}

		$api = new PaymentServicesClientAPI( 'APIAuthentication' ); //Need to do this before logging in.
		$api_result = $api->setNewOrganization( $row );
		if ( $api_result !== false ) {
			if ( $api_result->isValid() === true ) {
				Debug::Text( 'PaymentServices API: Retval: ' . $api_result->getResult(), __FILE__, __LINE__, __METHOD__, 10 );

				return $api_result->getResult(); //Return the ID so we can link a user to it.
			} else {
				Debug::Arr( $api_result, 'PaymentServices API: Retval: ' . $api_result->getResult(), __FILE__, __LINE__, __METHOD__, 10 );

				return false; //This will trigger a general error to the user.
			}
		} else {
			//Debug::Arr( $api_result, 'PaymentServices API: Retval: ' . $api_result->getResult(), __FILE__, __LINE__, __METHOD__, 10 );
			return false; //This will trigger a general error to the user.
		}
	}

	/**
	 * @param $row
	 * @return bool
	 */
	function createNewUser( $row ) {
		if ( isset( $row['_kind'] ) ) {
			$row = [ $row ];
		}

		$api = new PaymentServicesClientAPI( 'APIAuthentication' ); //Need to do this before logging in.
		$api_result = $api->setNewUser( $row );
		if ( $api_result !== false ) {
			if ( $api_result->isValid() === true ) {
				Debug::Arr( $api_result->getResult(), 'PaymentServices API: Retval: ', __FILE__, __LINE__, __METHOD__, 10 );

				return $api_result->getResult(); //Return the ID so we can link a user to it.
			} else {
				Debug::Arr( $api_result, 'PaymentServices API: Retval: ' . $api_result->getResult(), __FILE__, __LINE__, __METHOD__, 10 );

				return false; //This will trigger a general error to the user.
			}
		} else {
			//Debug::Arr( $api_result, 'PaymentServices API: Retval: ' . $api_result->getResult(), __FILE__, __LINE__, __METHOD__, 10 );
			return false; //This will trigger a general error to the user.
		}
	}


	/**
	 * Deep validation of bank account.
	 * @param $row
	 * @return bool
	 */
	function validateBankAccount( $row ) {
		$api = new PaymentServicesClientAPI( 'APIBankAccount' );
		$api_result = $api->validateBankAccount( $row );
		if ( $api_result !== false ) {
			if ( $api_result->isValid() === true ) {
				Debug::Text( 'PaymentServices API: Retval: ' . $api_result->getResult(), __FILE__, __LINE__, __METHOD__, 10 );

				return true;
			} else {
				Debug::Arr( $api_result, 'PaymentServices API: Retval: ' . $api_result->getResult(), __FILE__, __LINE__, __METHOD__, 10 );

				return $api_result; //Return raw result object so we can get validation errors from it.
				//return FALSE; //This will trigger a general error to the user.
			}
		} else {
			//Debug::Arr( $api_result, 'PaymentServices API: Retval: ' . $api_result->getResult(), __FILE__, __LINE__, __METHOD__, 10 );
			return false; //This will trigger a general error to the user.
		}
	}


	/**
	 * Updates the remote remittance bank account information.
	 * @param $rows
	 * @return bool
	 */
	function setRemittanceSourceAccount( $rows ) {
		if ( isset( $rows['_kind'] ) ) {
			$rows = [ $rows ];
		}

		foreach ( $rows as $row ) {
			if ( isset( $row['deleted'] ) && $row['deleted'] == true ) {
				$api = new PaymentServicesClientAPI( 'APIBankAccount' );
				$api_result = $api->getBankAccount( [ 'filter_data' => [ 'remote_id' => $row['remote_id'] ] ] );
				if ( $api_result->isValid() === true ) {
					$bank_account_id = $api_result->getResult()[0]['id'];
					Debug::Text( 'PaymentServices API: Bank Account ID: ' . $bank_account_id, __FILE__, __LINE__, __METHOD__, 10 );

					$api_result = $api->deleteBankAccount( $bank_account_id );
					if ( $api_result->isValid() !== true ) {
						Debug::Arr( $api_result->getResult(), 'PaymentServices API: Failed deleting Bank Account ID: ' . $bank_account_id, __FILE__, __LINE__, __METHOD__, 10 );
					}
				}
			} else {
				$api = new PaymentServicesClientAPI( 'APIBankAccount' );
				$api_result = $api->setBankAccount( $row );
				if ( $api_result !== false ) {
					if ( $api_result->isValid() === true ) {
						Debug::Text( 'PaymentServices API: Retval: ' . $api_result->getResult(), __FILE__, __LINE__, __METHOD__, 10 );
					} else {
						Debug::Arr( $api_result, 'PaymentServices API: Retval: ' . $api_result->getResult(), __FILE__, __LINE__, __METHOD__, 10 );

						return false; //This will trigger a general error to the user.
					}
				} else {
					//Debug::Arr( $api_result, 'PaymentServices API: Retval: ' . $api_result->getResult(), __FILE__, __LINE__, __METHOD__, 10 );
					return false; //This will trigger a general error to the user.
				}
			}
		}

		return true;
	}

	/**
	 * Updates the remote remittance agency information.
	 * @param $rows
	 * @return bool
	 */
	function setAgencyAuthorization( $rows ) {
		if ( isset( $rows['_kind'] ) ) {
			$rows = [ $rows ];
		}

		foreach ( $rows as $row ) {
			if ( isset( $row['deleted'] ) && $row['deleted'] == true ) {
				$api = new PaymentServicesClientAPI( 'APIAgencyAuthorization' );
				$api_result = $api->getAgencyAuthorization( [ 'filter_data' => [ 'remote_id' => $row['remote_id'] ] ] );
				if ( $api_result->isValid() === true ) {
					$agency_authorization_id = $api_result->getResult()[0]['id'];
					Debug::Text( 'PaymentServices API: Agency Authorization ID: ' . $agency_authorization_id, __FILE__, __LINE__, __METHOD__, 10 );

					$api_result = $api->deleteAgencyAuthorization( $agency_authorization_id );
					if ( $api_result->isValid() !== true ) {
						Debug::Arr( $api_result->getResult(), 'PaymentServices API: Failed deleting Agency Authorization ID: ' . $agency_authorization_id, __FILE__, __LINE__, __METHOD__, 10 );
					}
				}
			} else {
				$api = new PaymentServicesClientAPI( 'APIAgencyAuthorization' );
				$api_result = $api->setAgencyAuthorization( $row );
				if ( $api_result !== false ) {
					if ( $api_result->isValid() === true ) {
						Debug::Text( 'PaymentServices API: Retval: ' . $api_result->getResult(), __FILE__, __LINE__, __METHOD__, 10 );
					} else {
						Debug::Arr( $api_result, 'PaymentServices API: Retval: ' . $api_result->getResult(), __FILE__, __LINE__, __METHOD__, 10 );

						return false; //This will trigger a general error to the user.
					}
				} else {
					//Debug::Arr( $api_result, 'PaymentServices API: Retval: ' . $api_result->getResult(), __FILE__, __LINE__, __METHOD__, 10 );
					return false; //This will trigger a general error to the user.
				}
			}
		}

		return true;
	}

	/**
	 * Uploads Agency Report for processing through TimeTrex PaymentServices.
	 * @param $rows
	 * @return bool
	 */
	function setAgencyReport( $rows ) {
		if ( isset( $rows['_kind'] ) ) {
			$rows = [ $rows ];
		}

		$api = new PaymentServicesClientAPI( 'APIAgencyReport' );
		$api_result = $api->setAgencyReport( $rows );

		return $api_result;
	}


	/**
	 * Uploads Pay Stub transactions for processing through TimeTrex PaymentServices.
	 * @param $rows
	 * @return bool
	 */
	function setPayStubTransaction( $rows ) {
		if ( isset( $rows['_kind'] ) ) {
			$rows = [ $rows ];
		}

		$api = new PaymentServicesClientAPI( 'APITransaction' );
		$api_result = $api->setTransaction( $rows );

		return $api_result;
	}

	/**
	 * @param null $start_date
	 * @param null $end_date
	 * @return bool
	 */
	function getAccountStatementReport( $start_date = null, $end_date = null ) {
		$api = new PaymentServicesClientAPI( 'APIOrganization' );
		$api_result = $api->getAccountStatementReport( $start_date, $end_date );
		if ( $api_result !== false ) {
			if ( $api_result->isValid() === true ) {
				Debug::Text( 'PaymentServices API: Retval: ' . $api_result->getResult(), __FILE__, __LINE__, __METHOD__, 10 );

				return $api_result->getResultData();
			} else {
				Debug::Arr( $api_result, 'PaymentServices API: Retval: ' . $api_result->getResult(), __FILE__, __LINE__, __METHOD__, 10 );
			}
		}

		return false;
	}

	/**
	 * @return mixed
	 */
	function ping() {
		$api = new PaymentServicesClientAPI( 'APIAuthentication' );

		return $api->ping();
	}
}

?>