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

/*

How this needs to work
----------------------

Abstraction layer. Store all data in a "TimeTrex" format, then use
different classes to export that data to each foramt.

Add:


/*
Example Usage:

$gle = new GeneralLedgerExport();
$gle->setFileFormat('Simply');
	$je = new GeneralLedgerExport_JournalEntry();

	$je->setDate( time() );
	$je->setSource( 0000101 );
	$je->setComment( "Benoit, Wendy" );

	$record = new GeneralLedgerExport_Record();
	$record->setAccount( 2500 );
	$record->setType('CREDIT'); //Or Debit?
	$record->setAmount( 10.00 );

	$je->setRecord($record);

$gle->setJournalEntry($je);

$gle->compile();

$eft->save('/tmp/gl01.txt');
*/

/**
 * @package Other\GeneralLedgerExport
 */
class GeneralLedgerExport {

	var $file_format_options = [ 'CSV' ];
	var $file_format = null; //File format
	var $set_journal_entry_errors = 0;
	var $journal_entry_error_msgs = [];

	/**
	 * @var bool|string
	 */
	private $compiled_data;

	/**
	 * @var array
	 */
	private $data;

	/**
	 * GeneralLedgerExport constructor.
	 */
	function __construct() {
		Debug::Text( ' Contruct... ', __FILE__, __LINE__, __METHOD__, 10 );

		return true;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function isFloat( $value ) {
		if ( preg_match( '/^[-0-9\.]+$/', $value ) ) {
			return true;
		}

		return false;
	}

	/**
	 * @return bool|null
	 */
	function getFileFormat() {
		if ( isset( $this->file_format ) ) {
			return $this->file_format;
		}

		return false;
	}

	/**
	 * @param $format
	 * @return bool
	 */
	function setFileFormat( $format ) {
		$this->file_format = $format;

		return true;
	}

	/**
	 * @param object $obj
	 * @return bool
	 */
	function setJournalEntry( $obj ) {
		//Make sure accounts balance.
		if ( is_object( $obj ) && $obj->checkBalance() == true && $obj->combineRecords() == true ) {
			$this->data[] = $obj;

			return true;
		} else {
			//Count errors, so we can NOT compile data if something doesn't balance
			Debug::Text( ' Journal Entry did not balance: Errors: ' . $this->set_journal_entry_errors, __FILE__, __LINE__, __METHOD__, 10 );
			$this->set_journal_entry_errors++;
			$this->journal_entry_error_msgs[] = $obj->journal_entry_error_msg;
		}

		return false;
	}

	/*
	Functions to help process the data.
	*/

	/**
	 * @return bool
	 */
	function getCompiledData() {
		if ( isset( $this->compiled_data ) && $this->compiled_data !== null && $this->compiled_data !== false ) {
			return $this->compiled_data;
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function compile() {
		if ( !isset( $this->data ) || $this->set_journal_entry_errors > 0 ) {
			Debug::Text( ' No Data, or Journal Entry did not balance: Errors: ' . $this->set_journal_entry_errors, __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		switch ( strtolower( $this->getFileFormat() ) ) {
			case 'simply':
				$file_format_obj = new GeneralLedgerExport_File_Format_Simply( $this->data );
				break;
			case 'quickbooks':
				$file_format_obj = new GeneralLedgerExport_File_Format_QuickBooks( $this->data );
				break;
			case 'xero':
				$file_format_obj = new GeneralLedgerExport_File_Format_Xero( $this->data );
				break;
			case 'sage300':
				$file_format_obj = new GeneralLedgerExport_File_Format_Sage300( $this->data );
				break;
			case 'csv':
			case 'export_csv':
				$file_format_obj = new GeneralLedgerExport_File_Format_CSV( $this->data );
				break;
			case 'csv_flat':
			case 'export_csv_flat':
				$file_format_obj = new GeneralLedgerExport_File_Format_CSVFlat( $this->data );
				break;
		}

		Debug::Text( 'aData Lines: ' . count( $this->data ), __FILE__, __LINE__, __METHOD__, 10 );

		$compiled_data = $file_format_obj->_compile();
		if ( $compiled_data !== false ) {
			$this->compiled_data = $compiled_data;

			return true;
		}


		return false;
	}


	/**
	 * @param $file_name
	 * @return bool
	 */
	function save( $file_name ) {
		//saves processed data to a file.

		if ( $this->getCompiledData() !== false ) {

			if ( is_writable( dirname( $file_name ) ) && !file_exists( $file_name ) ) {
				if ( file_put_contents( $file_name, $this->getCompiledData(), LOCK_EX ) > 0 ) {
					Debug::Text( 'Write successfull:', __FILE__, __LINE__, __METHOD__, 10 );

					return true;
				} else {
					Debug::Text( 'Write failed:', __FILE__, __LINE__, __METHOD__, 10 );
				}
			} else {
				Debug::Text( 'File is not writable, or already exists:', __FILE__, __LINE__, __METHOD__, 10 );
			}
		}

		Debug::Text( 'Save Failed!:', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}
}


/**
 * @package Other\GeneralLedgerExport
 */
class GeneralLedgerExport_JournalEntry extends GeneralLedgerExport {
	var $journal_entry_data = null;
	var $journal_entry_error_msg = null;
	var $ignore_balance_check = false;

	/**
	 * GeneralLedgerExport_JournalEntry constructor.
	 * @param bool $ignore_balance_check
	 */
	function __construct( $ignore_balance_check = false ) {
		Debug::Text( ' GLE_JournalEntry Contruct... ', __FILE__, __LINE__, __METHOD__, 10 );
		$this->ignore_balance_check = $ignore_balance_check;

		return true;
	}

	/**
	 * @return bool
	 */
	function getDate() {
		if ( isset( $this->journal_entry_data['date'] ) ) {
			return $this->journal_entry_data['date'];
		}

		return false;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setDate( $value ) {
		if ( $value != '' ) {
			$this->journal_entry_data['date'] = $value;

			return true;
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function getSource() {
		if ( isset( $this->journal_entry_data['source'] ) ) {
			return $this->journal_entry_data['source'];
		}

		return false;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setSource( $value ) {
		$value = trim( $value );

		$this->journal_entry_data['source'] = $value; //Was max 13 chars, moved length restrictions into format specific classes instead.

		return true;
	}

	/**
	 * @return bool
	 */
	function getComment() {
		if ( isset( $this->journal_entry_data['comment'] ) ) {
			return $this->journal_entry_data['comment'];
		}

		return false;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setComment( $value ) {
		$value = trim( $value );

		$this->journal_entry_data['comment'] = $value; //Was max 39 chars, moved length restrictions into format specific classes instead.

		return false;
	}

	/**
	 * @return bool
	 */
	function getRecords() {
		if ( isset( $this->journal_entry_data['records'] ) && $this->journal_entry_data['records'] != null ) {
			return $this->journal_entry_data['records'];
		}

		return false;
	}

	/**
	 * @param object $obj
	 * @return bool
	 */
	function setRecord( $obj ) {
		if ( $obj->Validate() == true ) {
			$this->journal_entry_data['records'][] = $obj;

			return true;
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function combineRecords() {
		//See if there are multiple records with the same type AND account
		//If so, combine them in to one.
		$records = $this->getRecords();

		$i = 0;
		$account_list = [];
		$new_records = [];
		foreach ( $records as $record ) {
			if ( isset( $account_list[$record->getType()][$record->getAccount()] ) ) {
				$original_id = $account_list[$record->getType()][$record->getAccount()];
				Debug::Text( ' Found duplicate Account, combining: ' . $i . ' with ' . $original_id . ' Type: ' . $record->getType() . ' Account: ' . $record->getAccount(), __FILE__, __LINE__, __METHOD__, 10 );

				//Combine two accounts in to one.
				$new_record = new GeneralLedgerExport_Record();
				$new_record->setAccount( $record->getAccount() );
				$new_record->setType( $record->getType() );
				$new_record->setAmount( $new_records[$original_id]->getAmount() + $record->getAmount() );

				$new_records[$original_id] = $new_record;

				unset( $new_record );
			} else {
				$account_list[$record->getType()][$record->getAccount()] = $i;

				$new_records[$i] = $record;
			}


			$i++;
		}

		$this->journal_entry_data['records'] = $new_records;

		return $this->checkBalance();
	}

	/**
	 * @return bool
	 */
	function checkBalance() {
		Debug::Text( ' Checking Balance of Journal Entry...', __FILE__, __LINE__, __METHOD__, 10 );
		$records = $this->getRecords();
		if ( $records == false ) {
			return false;
		}

		$debit_amount = 0;
		$credit_amount = 0;

		$i = 0;
		foreach ( $records as $record ) {
			Debug::Text( $i . '. Type: ' . $record->getType() . ' Amount: ' . $record->getAmount() . ' Account: ' . $record->getAccount(), __FILE__, __LINE__, __METHOD__, 10 );
			if ( $record->getType() == 'debit' ) {
				$debit_amount += $record->getAmount();
			} else if ( $record->getType() == 'credit' ) {
				$credit_amount += $record->getAmount();
			} else {
				Debug::Text( 'NO ACCOUNT TYPE BAD!!', __FILE__, __LINE__, __METHOD__, 10 );
			}

			$i++;
		}

		Debug::Text( ' Debit Amount: ' . $debit_amount . ' Credit Amount: ' . $credit_amount, __FILE__, __LINE__, __METHOD__, 10 );
		if ( $debit_amount != 0 && $credit_amount != 0
				&& round( $debit_amount, 2 ) == round( $credit_amount, 2 ) ) {
			Debug::Text( ' JE balances!', __FILE__, __LINE__, __METHOD__, 10 );

			return true;
		}

		Debug::Text( ' Journal Entry DOES NOT BALANCE!', __FILE__, __LINE__, __METHOD__, 10 );
		if ( $this->ignore_balance_check == true ) {
			Debug::Text( ' Skipping Balance of Journal Entry...', __FILE__, __LINE__, __METHOD__, 10 );

			return true;
		} else {
			$this->journal_entry_error_msg = TTi18n::getText( 'Debit: %1 Credit: %2', [ $debit_amount, $credit_amount ] );

			return false;
		}
	}


}


/**
 * @package Other\GeneralLedgerExport
 */
class GeneralLedgerExport_Record extends GeneralLedgerExport_JournalEntry {

	var $record_data = null;
	/*
		function __construct( $options = NULL ) {
			Debug::Text(' GLE_Record Contruct... ', __FILE__, __LINE__, __METHOD__, 10);

			return TRUE;
		}
	*/
	/**
	 * @return bool
	 */
	function getType() {
		if ( isset( $this->record_data['type'] ) ) {
			return $this->record_data['type'];
		}

		return false;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setType( $value ) {
		$value = strtolower( $value );

		if ( $value == 'credit' || $value == 'debit' ) {
			$this->record_data['type'] = $value;

			return true;
		}

		return false;
	}

	/**
	 * @return bool|string
	 */
	function getAmount() {
		if ( isset( $this->record_data['amount'] ) ) {
			return number_format( $this->record_data['amount'], 2, '.', '' );
		}

		return false;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setAmount( $value ) {
		//Allow negative values, for example if someone is trying to export negative values (for things like vacation accrual)
		//Used to check: strlen( $value ) <= 10, however that would break foriegn currencies that use large amounts.
		if ( $this->isFloat( $value ) && $value != 0 ) {
			$this->record_data['amount'] = $value;

			return true;
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function getAccount() {
		if ( isset( $this->record_data['account'] ) ) {
			return $this->record_data['account'];
		}

		return false;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setAccount( $value ) {
		$value = trim( $value );

		$this->record_data['account'] = $value; //Was max 100 chars - Allow long account values for more job tracking.

		return true;
	}

	/**
	 * @param bool $ignore_warning
	 * @return bool
	 */
	function Validate( $ignore_warning = true ) {
		if ( $this->getType() == false || $this->getAccount() == false || $this->getAmount() == false ) {
			Debug::Text( ' ERROR: Validation Failed! Amount: ' . $this->getAmount() . ' Type: ' . $this->getType() . ' Account: ' . $this->getAccount(), __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		return true;
	}

}


/**
 * @package Other\GeneralLedgerExport
 */
class GeneralLedgerExport_File_Format_Simply Extends GeneralLedgerExport {
	var $data = null;

	/**
	 * GeneralLedgerExport_File_Format_Simply constructor.
	 * @param $data
	 */
	function __construct( $data ) {
		Debug::Text( ' General Ledger Format Simply Contruct... ', __FILE__, __LINE__, __METHOD__, 10 );

		$this->data = $data;

		return true;
	}

	/**
	 * @param int $epoch EPOCH
	 * @return false|string
	 */
	private function toDate( $epoch ) {
		return date( 'm-d-y', $epoch );
	}


	/**
	 * @return array|bool
	 */
	private function compileRecords() {
		//gets all Detail records.

		if ( count( $this->data ) == 0 ) {
			Debug::Text( 'No data records:', __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		$line1 = [];
		$line = [];
		$retval = [];
		foreach ( $this->data as $journal_entry ) {
			//Debug::Arr($record, 'Record Object:', __FILE__, __LINE__, __METHOD__, 10);

			$line1[] = $this->toDate( $journal_entry->getDate() );
			$line1[] = '"' . substr( $journal_entry->getSource(), 0, 20 ) . '"';
			$line1[] = '"' . substr( $journal_entry->getComment(), 0, 75 ) . '"';

			$line1 = implode( ',', $line1 );
			Debug::Text( 'Line 1: ' . $line1, __FILE__, __LINE__, __METHOD__, 10 );
			$retval[] = $line1;

			$records = $journal_entry->getRecords();
			foreach ( $records as $record ) {
				$line[] = $record->getAccount();
				if ( $record->getType() == 'credit' ) {
					//Credits are negative.
					$amount = number_format( ( $record->getAmount() * -1 ), 2, '.', '' );
				} else {
					$amount = $record->getAmount();
				}
				$line[] = $amount;
				$line = implode( ',', $line );
				Debug::Text( 'Line: ' . $line, __FILE__, __LINE__, __METHOD__, 10 );
				$retval[] = $line;

				unset( $line );
			}
			unset( $line1 );
		}

		if ( empty( $retval ) == false ) {
			Debug::Text( 'Returning Compiled Records: ', __FILE__, __LINE__, __METHOD__, 10 );

			return $retval;
		}

		return false;
	}

	/**
	 * @return bool|string
	 */
	function _compile() {
		//Processes all the data, padding it, converting dates to julian, incrementing
		//record numbers.

		$compiled_data = @implode( "\r\n", $this->compileRecords() );

		//Make sure the length of at least 3 records exists.
		if ( strlen( $compiled_data ) >= 10 ) {
			return $compiled_data;
		}

		Debug::Text( 'Not enough compiled data!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}
}


/**
 * @package Other\GeneralLedgerExport
 */
class GeneralLedgerExport_File_Format_CSV Extends GeneralLedgerExport {
	var $data = null;

	/**
	 * GeneralLedgerExport_File_Format_CSV constructor.
	 * @param $data
	 */
	function __construct( $data ) {
		Debug::Text( ' General Ledger Format CSV Contruct... ', __FILE__, __LINE__, __METHOD__, 10 );

		$this->data = $data;

		return true;
	}

	/**
	 * @param int $epoch EPOCH
	 * @return false|string
	 */
	private function toDate( $epoch ) {
		return date( 'm-d-y', $epoch );
	}


	/**
	 * @return array|bool
	 */
	private function compileRecords() {
		//gets all Detail records.

		if ( count( $this->data ) == 0 ) {
			Debug::Text( 'No data records:', __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		$retval = [];
		$line1 = [];
		$line = [];
		//Column headers
		$retval[] = 'Date, Source, Comment, Account, Debit, Credit';

		foreach ( $this->data as $journal_entry ) {
			//Debug::Arr($record, 'Record Object:', __FILE__, __LINE__, __METHOD__, 10);

			$line1[] = $this->toDate( $journal_entry->getDate() );
			$line1[] = '"' . $journal_entry->getSource() . '"';
			$line1[] = '"' . $journal_entry->getComment() . '"';
			$line1[] = null;
			$line1[] = null;
			$line1[] = null;

			$line1 = implode( ',', $line1 );
			Debug::Text( 'Line 1: ' . $line1, __FILE__, __LINE__, __METHOD__, 10 );
			$retval[] = $line1;

			$records = $journal_entry->getRecords();
			foreach ( $records as $record ) {
				for ( $i = 0; $i < 3; $i++ ) {
					$line[] = null;
				}

				$line[] = '"' . $record->getAccount() . '"';
				if ( $record->getType() == 'debit' ) {
					$line[] = $record->getAmount();
					$line[] = null;
				} else {
					$line[] = null;
					$line[] = $record->getAmount();
				}

				$line = implode( ',', $line );
				Debug::Text( 'Line: ' . $line, __FILE__, __LINE__, __METHOD__, 10 );
				$retval[] = $line;

				unset( $line );
			}
			unset( $line1 );
		}

		if ( empty( $retval ) == false ) {
			Debug::Text( 'Returning Compiled Records: ', __FILE__, __LINE__, __METHOD__, 10 );

			return $retval;
		}

		return false;
	}

	/**
	 * @return bool|string
	 */
	function _compile() {
		//Processes all the data, padding it, converting dates to julian, incrementing
		//record numbers.

		$compiled_data = @implode( "\r\n", $this->compileRecords() );

		//Make sure the length of at least 3 records exists.
		if ( strlen( $compiled_data ) >= 10 ) {
			return $compiled_data;
		}

		Debug::Text( 'Not enough compiled data!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}
}

/**
 * @package Other\GeneralLedgerExport
 */
class GeneralLedgerExport_File_Format_CSVFlat Extends GeneralLedgerExport {
	var $data = null;

	/**
	 * GeneralLedgerExport_File_Format_CSV constructor.
	 * @param $data
	 */
	function __construct( $data ) {
		Debug::Text( ' General Ledger Format CSV Contruct... ', __FILE__, __LINE__, __METHOD__, 10 );

		$this->data = $data;

		return true;
	}

	/**
	 * @param int $epoch EPOCH
	 * @return false|string
	 */
	private function toDate( $epoch ) {
		return date( 'm-d-y', $epoch );
	}


	/**
	 * @return array|bool
	 */
	private function compileRecords() {
		//gets all Detail records.

		if ( count( $this->data ) == 0 ) {
			Debug::Text( 'No data records:', __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		$retval = [];
		$line = [];
		//Column headers
		$retval[] = 'Reference, Date, Source, Comment, Account, CostCenter1, CostCenter2, Debit, Credit';

		$i = 1;
		foreach ( $this->data as $journal_entry ) {
			//Debug::Arr($record, 'Record Object:', __FILE__, __LINE__, __METHOD__, 10);

			$records = $journal_entry->getRecords();
			foreach ( $records as $record ) {
				$line[] = $i;
				$line[] = $this->toDate( $journal_entry->getDate() );
				$line[] = '"' . $journal_entry->getSource() . '"';
				$line[] = '"' . $journal_entry->getComment() . '"';

				// To use Other1 or Other2, use '|' to separate them in the Account name, ie: Payroll Expenses|Customer Name|Job Name
				if ( strpos( $record->getAccount(), '|' ) !== false ) {
					$split_account = explode( '|', trim( $record->getAccount() ) );
					if ( isset( $split_account[0] ) ) {
						$line[] = '"' . trim( $split_account[0] ) . '"';
					}

					if ( isset( $split_account[1] ) ) {
						$line[] = '"' . trim( $split_account[1] ) . '"'; //CostCenter1
					} else {
						$line[] = null; //Name
					}

					if ( isset( $split_account[2] ) ) {
						$line[] = '"' . trim( $split_account[2] ) . '"'; //CostCenter2
					} else {
						$line[] = null; //Class
					}
					unset( $split_account );
				} else {
					$line[] = '"' . $record->getAccount() . '"';
					$line[] = null; //CostCenter1
					$line[] = null; //CostCenter2
				}


				if ( $record->getType() == 'debit' ) {
					$line[] = $record->getAmount();
					$line[] = null;
				} else {
					$line[] = null;
					$line[] = $record->getAmount();
				}

				$line = implode( ',', $line );
				Debug::Text( 'Line: ' . $line, __FILE__, __LINE__, __METHOD__, 10 );
				$retval[] = $line;

				unset( $line );
			}

			$i++;
		}

		if ( empty( $retval ) == false ) {
			Debug::Text( 'Returning Compiled Records: ', __FILE__, __LINE__, __METHOD__, 10 );

			return $retval;
		}

		return false;
	}

	/**
	 * @return bool|string
	 */
	function _compile() {
		//Processes all the data, padding it, converting dates to julian, incrementing
		//record numbers.

		$compiled_data = @implode( "\r\n", $this->compileRecords() );

		//Make sure the length of at least 3 records exists.
		if ( strlen( $compiled_data ) >= 10 ) {
			return $compiled_data;
		}

		Debug::Text( 'Not enough compiled data!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}
}

/**
 * @package Other\GeneralLedgerExport
 */
class GeneralLedgerExport_File_Format_QuickBooks Extends GeneralLedgerExport {
	var $data = null;

	/**
	 * GeneralLedgerExport_File_Format_QuickBooks constructor.
	 * @param $data
	 */
	function __construct( $data ) {
		Debug::Text( ' General Ledger Format QuickBooks Contruct... ', __FILE__, __LINE__, __METHOD__, 10 );

		$this->data = $data;

		return true;
	}

	/**
	 * @param int $epoch EPOCH
	 * @return false|string
	 */
	private function toDate( $epoch ) {
		return date( 'm/d/y', $epoch );
	}


	/**
	 * @return array|bool
	 */
	private function compileRecords() {
		//gets all Detail records.

		if ( count( $this->data ) == 0 ) {
			Debug::Text( 'No data records:', __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}


		/*
		 * "NAME" column can be either: Customer, Vendor, Employee, Other
		!TRNS	TRNSID	TRNSTYPE		DATE	ACCNT	NAME	CLASS	AMOUNT	DOCNUM	MEMO
		!SPL	SPLID	TRNSTYPE		DATE	ACCNT	NAME	CLASS	AMOUNT	DOCNUM	MEMO
		!ENDTRNS
		TRNS			GENERAL JOURNAL 7/1/1998		Checking					650
		SPL				GENERAL JOURNAL 7/1/1998		Expense Account				-650
		ENDTRNS
		*/
		//Column headers
		$retval = [];
		$retval[] = "!TRNS\tTRNSID\tTRNSTYPE\tDATE\tACCNT\tNAME\tCLASS\tAMOUNT\tDOCNUM\tMEMO";
		$retval[] = "!SPL\tSPLID\tTRNSTYPE\tDATE\tACCNT\tNAME\tCLASS\tAMOUNT\tDOCNUM\tMEMO";
		$retval[] = '!ENDTRNS';

		$line = [];
		foreach ( $this->data as $journal_entry ) {
			//Debug::Arr($record, 'Record Object:', __FILE__, __LINE__, __METHOD__, 10);

			$records = $journal_entry->getRecords();
			$i = 0;
			foreach ( $records as $record ) {
				if ( $i == 0 ) {
					$line[] = 'TRNS';
				} else {
					$line[] = 'SPL';
				}

				$line[] = null;              //TRANSID
				$line[] = 'GENERAL JOURNAL'; //TRNSTYPE
				$line[] = $this->toDate( $journal_entry->getDate() );

				//If you're trying to use a sub-accounts, include the account's full name in the ACCNT field on TRNS or SPL rows.
				//For example, if you have a Travel account with a sub-account called Airfare, then include the account "Travel:Airfare" in the IIF file ACCNT fields.
				//All sub-accounts also have unique account numbers, so using just account numbers may be the better appraoch.
				//  Sub-Accounts may also be referred to as "Payroll Item" in the journal entry transactions.
				// To use Name or Class, use '|' to separate them in the Account name, ie: Payroll Expenses|Customer Name|Class
				if ( strpos( $record->getAccount(), '|' ) !== false ) {
					$split_account = explode( '|', trim( $record->getAccount() ) );
					if ( isset( $split_account[0] ) ) {
						$line[] = trim( $split_account[0] );
					}

					if ( isset( $split_account[1] ) ) {
						$line[] = trim( $split_account[1] ); //Name
					} else {
						$line[] = null; //Name
					}

					if ( isset( $split_account[2] ) ) {
						$line[] = trim( $split_account[2] ); //Class
					} else {
						$line[] = null; //Class
					}
					unset( $split_account );
				} else {
					$line[] = $record->getAccount();
					$line[] = null; //Name
					$line[] = null; //Class
				}

				if ( $record->getType() == 'debit' ) {
					$line[] = $record->getAmount();
				} else {
					$line[] = ( $record->getAmount() * -1 ); //Credits are negative.
				}

				$line[] = null;                         //DOCNUM
				$line[] = $journal_entry->getComment(); //Memo

				$line = implode( "\t", $line );
				Debug::Text( 'Line: ' . $line, __FILE__, __LINE__, __METHOD__, 10 );
				$retval[] = $line;

				unset( $line );

				$i++;
			}

			$retval[] = 'ENDTRNS';
		}

		if ( isset( $retval ) ) {
			Debug::Text( 'Returning Compiled Records: ', __FILE__, __LINE__, __METHOD__, 10 );

			return $retval;
		}

		return false;
	}

	/**
	 * @return bool|string
	 */
	function _compile() {
		//Processes all the data, padding it, converting dates to julian, incrementing
		//record numbers.

		$compiled_data = @implode( "\r\n", $this->compileRecords() ) . "\r\n"; //Grr!!! Quickbooks requires a blank line at the end of the file!

		//Make sure the length of at least 3 records exists.
		if ( strlen( $compiled_data ) >= 10 ) {
			return $compiled_data;
		}

		Debug::Text( 'Not enough compiled data!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}
}

/**
 * @package Other\GeneralLedgerExport
 */
class GeneralLedgerExport_File_Format_Xero Extends GeneralLedgerExport {
	var $data = null;

	/**
	 * GeneralLedgerExport_File_Format_CSV constructor.
	 * @param $data
	 */
	function __construct( $data ) {
		Debug::Text( ' General Ledger Format CSV Contruct... ', __FILE__, __LINE__, __METHOD__, 10 );

		$this->data = $data;

		return true;
	}

	/**
	 * @param int $epoch EPOCH
	 * @return false|string
	 */
	private function toDate( $epoch ) {
		return date( 'Y/m/d', $epoch );
	}


	/**
	 * @return array|bool
	 */
	private function compileRecords() {
		//gets all Detail records.

		if ( count( $this->data ) == 0 ) {
			Debug::Text( 'No data records:', __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		$retval = [];
		$line = [];
		//Column headers
		//$retval[] = 'Reference, Date, Source, Comment, Account, CostCenter1, CostCenter2, Debit, Credit';
		$retval[] = 'Number, *Narration, *Date, Description, *AccountCode, *TaxRate, *Amount, TrackingName1, TrackingOption1, TrackingName2, TrackingOption2';

		$i = 1;
		foreach ( $this->data as $journal_entry ) {
			//Debug::Arr($record, 'Record Object:', __FILE__, __LINE__, __METHOD__, 10);

			$records = $journal_entry->getRecords();
			foreach ( $records as $record ) {
				$line[] = $i; //Number
				$line[] = '"'. $journal_entry->getSource() .'"'; //Narration
				$line[] = $this->toDate( $journal_entry->getDate() ); //Date
				$line[] = '"'. $journal_entry->getComment() .'"'; //Description

				if ( $record->getType() == 'debit' ) {
					$amount = $record->getAmount();
				} else {
					$amount = ( $record->getAmount() * -1 );
				}

				// To use TrackingName1, TrackingOption1, TrackingName2, TrackingOption2, use '|' to separate them in the Account name, ie: Payroll Expenses|Name1|Option1|Name2|Option2
				if ( strpos( $record->getAccount(), '|' ) !== false ) {
					$split_account = explode( '|', trim( $record->getAccount() ) );
					if ( isset( $split_account[0] ) ) {
						$line[] = '"' . trim( $split_account[0] ) . '"';
					}

					$line[] = 'Tax Exempt'; //Tax Rate
					$line[] = $amount;

					if ( isset( $split_account[1] ) ) {
						$line[] = '"' . trim( $split_account[1] ) . '"'; //TrackingName1
					} else {
						$line[] = null; //TrackingName1
					}
					if ( isset( $split_account[2] ) ) {
						$line[] = '"' . trim( $split_account[2] ) . '"'; //TrackingOption1
					} else {
						$line[] = null; //TrackingOption1
					}

					if ( isset( $split_account[3] ) ) {
						$line[] = '"' . trim( $split_account[3] ) . '"'; //TrackingName2
					} else {
						$line[] = null; //TrackingName2
					}
					if ( isset( $split_account[4] ) ) {
						$line[] = '"' . trim( $split_account[4] ) . '"'; //TrackingOption2
					} else {
						$line[] = null; //TrackingOption2
					}
					unset( $split_account );
				} else {
					$line[] = '"' . $record->getAccount() . '"';
					$line[] = 'Tax Exempt'; //Tax Rate
					$line[] = $amount;
					$line[] = null; //TrackingName1
					$line[] = null; //TrackingOption1
					$line[] = null; //TrackingName2
					$line[] = null; //TrackingOption2
				}

				$line = implode( ',', $line );
				Debug::Text( 'Line: ' . $line, __FILE__, __LINE__, __METHOD__, 10 );
				$retval[] = $line;

				unset( $line );
			}

			$i++;
		}

		if ( empty( $retval ) == false ) {
			Debug::Text( 'Returning Compiled Records: ', __FILE__, __LINE__, __METHOD__, 10 );

			return $retval;
		}

		return false;
	}

	/**
	 * @return bool|string
	 */
	function _compile() {
		//Processes all the data, padding it, converting dates to julian, incrementing
		//record numbers.

		$compiled_data = @implode( "\r\n", $this->compileRecords() );

		//Make sure the length of at least 3 records exists.
		if ( strlen( $compiled_data ) >= 10 ) {
			return $compiled_data;
		}

		Debug::Text( 'Not enough compiled data!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}
}

/**
 * @package Other\GeneralLedgerExport
 */
class GeneralLedgerExport_File_Format_Sage300 Extends GeneralLedgerExport {
	var $data = null;

	/**
	 * GeneralLedgerExport_File_Format_Sage300 constructor.
	 * @param $data
	 */
	function __construct( $data ) {
		Debug::Text( ' General Ledger Format Sage300 Contruct... ', __FILE__, __LINE__, __METHOD__, 10 );

		$this->data = $data;

		return true;
	}

	/**
	 * @param int $epoch EPOCH
	 * @return false|string
	 */
	private function toDate( $epoch ) {
		return date( 'Ymd', $epoch );
	}

	/**
	 * @return array|bool
	 */
	private function compileRecords() {
		//gets all Detail records.

		if ( count( $this->data ) == 0 ) {
			Debug::Text( 'No data records:', __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}
		/*
		RECTYPE – this is to identify which of the three parts it is 1 = Journal_Header 2 = Journal_Details, 3 = Journal_Detail_Optional_Fields
		BATCHID – This is the Batch Number and this can be set to “000001” because this just inherits the batch number that you are importing into.
		BTCHENTRY – This is the entry number so if there are more than one entries going into the batch you would increment by one, in this case it is just going to be one entry so the values can be set to “00001”
		SRCELEDGER – This can be set to “GL” in this case.  Used to identify where the entry came from.
		SRCETYPE – This can be set to “JE” this is the type of entry.
		DATEENTRY – This is the date of the entry date format is YYYYMMDD
		JOURNALID – this is the same and the BTCHENTRY in the Journal_Header
		TRANSNBR – this is to identify the line of the journal entry and it increments by 20
		ACCTID – this is the account number
		TRANSAMT – This is the transaction amount positive DEBIT negative is a CREDIT*
		*/

		//Column headers
		$retval = [];
		$retval[] = '"RECTYPE","BATCHID","BTCHENTRY","SRCELEDGER","SRCETYPE","DATEENTRY"';
		$retval[] = '"RECTYPE","BATCHNBR","JOURNALID","TRANSNBR","ACCTID","TRANSAMT"';
		$retval[] = '"RECTYPE","BATCHNBR","JOURNALID","TRANSNBR","OPTFIELD"';

		$line = [];
		$line1 = [];
		$entry_number = 1;
		foreach ( $this->data as $journal_entry ) {
			Debug::Arr( $journal_entry, 'Record Object:', __FILE__, __LINE__, __METHOD__, 10 );

			$line1[] = 1;                                              //RecType: Header
			$line1[] = '000001';                                       //BatchID
			$line1[] = str_pad( $entry_number, 5, '0', STR_PAD_LEFT ); //BatchEntry
			$line1[] = 'GL';                                           //SRCELEDGER
			$line1[] = 'JE';                                           //SRCETYPE
			$line1[] = $this->toDate( $journal_entry->getDate() );

			$line1 = implode( '","', $line1 );
			Debug::Text( 'Line 1 (Header): ' . $line1, __FILE__, __LINE__, __METHOD__, 10 );
			$retval[] = '"' . $line1 . '"';

			$transaction_number = 20;
			$records = $journal_entry->getRecords();
			foreach ( $records as $record ) {
				$line[] = 2;                                                    //RecType: Details
				$line[] = '000001';                                             //BatchID
				$line[] = str_pad( $entry_number, 5, '0', STR_PAD_LEFT );       //BatchEntry
				$line[] = str_pad( $transaction_number, 5, '0', STR_PAD_LEFT ); //TransNBR

				$line[] = $record->getAccount();
				if ( $record->getType() == 'debit' ) {
					$line[] = $record->getAmount(); //Positive on DEBIT
				} else {
					$line[] = ( $record->getAmount() * -1 ); //Negative on CREDIT.
				}

				$line = implode( '","', $line );
				Debug::Text( 'Line: ' . $line, __FILE__, __LINE__, __METHOD__, 10 );
				$retval[] = '"' . $line . '"';

				$transaction_number += 20; //Increases by 20 each time.
				unset( $line );
			}
			unset( $line1 );

			$entry_number++;
		}

		if ( isset( $retval ) ) {
			Debug::Text( 'Returning Compiled Records: ', __FILE__, __LINE__, __METHOD__, 10 );

			return $retval;
		}

		return false;
	}

	/**
	 * @return bool|string
	 */
	function _compile() {
		//Processes all the data, padding it, converting dates to julian, incrementing
		//record numbers.

		$compiled_data = @implode( "\r\n", $this->compileRecords() );

		//Make sure the length of at least 3 records exists.
		if ( strlen( $compiled_data ) >= 10 ) {
			return $compiled_data;
		}

		Debug::Text( 'Not enough compiled data!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}
}

?>
