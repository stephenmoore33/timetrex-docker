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


include_once( 'CA.class.php' );

/**
 * @package GovernmentForms
 */
class GovernmentForms_CA_ROE extends GovernmentForms_CA {

	public $xml_schema = 'PayrollExtractXmlV2.xsd'; //http://www.esdc.gc.ca/en/ei/roe/user_requirements/appendix_d.page
	public $pdf_template = 'roe.pdf';

	public $template_offsets = [ -10, 0 ];

	function getOptions( $name ) {
		$retval = null;
		switch ( $name ) {
			case 'status':
				$retval = [
						'-1010-O' => TTi18n::getText( 'Original' ),
						'-1020-A' => TTi18n::getText( 'Amended' ),
						'-1030-C' => TTi18n::getText( 'Cancel' ),
				];
				break;
			case 'type':
				$retval = [
						'government' => TTi18n::gettext( 'Government (Multiple Employees/Page)' ),
						'employee'   => TTi18n::gettext( 'Employee (One Employee/Page)' ),
				];
				break;
		}

		return $retval;
	}

	//Set the type of form to display/print. Typically this would be:
	// government or employee.
	function getType() {
		if ( isset( $this->type ) ) {
			return $this->type;
		}

		return false;
	}

	function setType( $value ) {
		$this->type = trim( $value );

		return true;
	}

	//Set the submission status. Original, Amended, Cancel.
	function getStatus() {
		if ( isset( $this->status ) ) {
			return $this->status;
		}

		return 'O'; //Original
	}

	function setStatus( $value ) {
		$this->status = strtoupper( trim( $value ) );

		return true;
	}

	function getShowInstructionPage() {
		if ( isset( $this->show_instruction_page ) ) {
			return $this->show_instruction_page;
		}

		return false;
	}

	function setShowInstructionPage( $value ) {
		$this->show_instruction_page = (bool)trim( $value );

		return true;
	}

	public function getTemplateSchema( $name = null ) {
		$template_schema = [
			//Initialize page1, replace years on template.
			[
					'page'          => 1,
					'template_page' => 1,
					'on_background' => true,
			],

			//Serial
			'serial' => [
					'coordinates' => [
							'x'      => 35,
							'y'      => 58,
							'h'      => 8,
							'w'      => 115,
							'halign' => 'R',
					],
					'font'        => [
							'size' => 12,
							'type' => '',
					],
			],

			'payroll_reference_number' => [
					'coordinates' => [
							'x'      => 370,
							'y'      => 58,
							'h'      => 10,
							'w'      => 210,
							'halign' => 'R',
					],
					'font'        => [
							'size' => 12,
							'type' => '',
					],
			],

			//Employer Info
			//Company information
			'company_name'             => [
					'coordinates' => [
							'x'      => 35,
							'y'      => 90,
							'h'      => 10,
							'w'      => 310,
							'halign' => 'L',
					],
					'font'        => [
							'size' => 12,
							'type' => '',
					],
			],

			'company_address'     => [
					'function'    => [ 'draw' => [ 'filterCompanyAddress', 'drawNormal' ] ],
					'coordinates' => [
							'x'      => 35,
							'y'      => 105,
							'h'      => 10,
							'w'      => 310,
							'halign' => 'L',
					],
					'font'        => [
							'size' => 12,
							'type' => '',
					],
					'multicell'   => true,
			],
			'company_postal_code' => [

					'coordinates' => [
							'x'      => 280,
							'y'      => 138,
							'h'      => 10,
							'w'      => 65,
							'halign' => 'C',
					],
					'font'        => [
							'size' => 10,
							'type' => '',
					],
			],

			//Business Number
			'business_number'     => [
					'coordinates' => [
							'x'      => 370,
							'y'      => 85,
							'h'      => 10,
							'w'      => 210,
							'halign' => 'R',
					],
					'font'        => [
							'size' => 12,
							'type' => '',
					],
			],
			//Employee info
			'employee_full_name'  => [
					'coordinates' => [
							'x'      => 35,
							'y'      => 165,
							'h'      => 10,
							'w'      => 310,
							'halign' => 'L',
					],
					'font'        => [
							'size' => 12,
							'type' => '',
					],
			],

			'employee_address' => [
					'function'    => [ 'draw' => [ 'filterEmployeeAddress', 'drawNormal' ] ],
					'coordinates' => [
							'x'      => 35,
							'y'      => 180,
							'h'      => 10,
							'w'      => 310,
							'halign' => 'L',
					],
					'font'        => [
							'size' => 12,
							'type' => '',
					],
					'multicell'   => true,
			],

			//Pay Period Type
			'pay_period_type'  => [

					'coordinates' => [
							'x'      => 370,
							'y'      => 112,
							'h'      => 10,
							'w'      => 210,
							'halign' => 'R',
					],
					'font'        => [
							'size' => 12,
							'type' => '',
					],
			],
			//SIN
			'sin'              => [
					'coordinates' => [
							'x'      => 370,
							'y'      => 136,
							'h'      => 10,
							'w'      => 210,
							'halign' => 'R',
					],
					'font'        => [
							'size' => 12,
							'type' => '',
					],
			],

			//Employee Title
			'title'            => [
					'coordinates' => [
							'x'      => 35,
							'y'      => 240,
							'h'      => 10,
							'w'      => 310,
							'halign' => 'L',
					],
					'font'        => [
							'size' => 12,
							'type' => '',
					],
			],
			//First Day Worked
			'first_date'       => [
					'function'    => [ 'draw' => [ 'filterDate', 'drawSegments' ] ],
					'coordinates' => [
							[
									'x'      => 490,
									'y'      => 160,
									'h'      => 15,
									'w'      => 20,
									'halign' => 'C',
							],
							[
									'x'      => 512,
									'y'      => 160,
									'h'      => 15,
									'w'      => 26,
									'halign' => 'C',
							],
							[
									'x'      => 540,
									'y'      => 160,
									'h'      => 15,
									'w'      => 40,
									'halign' => 'C',
							],
					],
					'font'        => [
							'size' => 10,
							'type' => '',
					],
			],


			//Last day paid

			'last_date'           => [
					'function'    => [ 'draw' => [ 'filterDate', 'drawSegments' ] ],
					'coordinates' => [
							[
									'x'      => 490,
									'y'      => 185,
									'h'      => 17,
									'w'      => 21,
									'halign' => 'C',
							],
							[
									'x'      => 513,
									'y'      => 185,
									'h'      => 17,
									'w'      => 25,
									'halign' => 'C',
							],
							[
									'x'      => 540,
									'y'      => 185,
									'h'      => 17,
									'w'      => 40,
									'halign' => 'C',
							],
					],
					'font'        => [
							'type' => '',
							'size' => 10,
					],
			],

			//Pay Period End Date
			'pay_period_end_date' => [
					'function'    => [ 'draw' => [ 'filterDate', 'drawSegments' ] ],
					'coordinates' => [
							[
									'x'      => 490,
									'y'      => 210,
									'h'      => 18,
									'w'      => 21,
									'halign' => 'C',
							],
							[
									'x'      => 513,
									'y'      => 210,
									'h'      => 18,
									'w'      => 25,
									'halign' => 'C',
							],
							[
									'x'      => 540,
									'y'      => 210,
									'h'      => 18,
									'w'      => 40,
									'halign' => 'C',
							],
					],
					'font'        => [
							'type' => '',
							'size' => 10,
					],
			],

			'recall_date'        => [
					'function'    => [ 'draw' => [ 'filterDate', 'drawSegments' ] ],
					'coordinates' => [
							[
									'x'      => 490,
									'y'      => 240,
									'h'      => 18,
									'w'      => 21,
									'halign' => 'C',
							],
							[
									'x'      => 513,
									'y'      => 240,
									'h'      => 18,
									'w'      => 25,
									'halign' => 'C',
							],
							[
									'x'      => 540,
									'y'      => 240,
									'h'      => 18,
									'w'      => 40,
									'halign' => 'C',
							],
					],
					'font'        => [
							'type' => '',
							'size' => 10,
					],

			],

			// not returning
			'not_returning'      => [
					'function'    => [ 'draw' => [ 'drawCheckBox' ] ],
					'coordinates' => [
							[
									'x'      => 423,
									'y'      => 242,
									'h'      => 8,
									'w'      => 11,
									'halign' => 'C',
							],
					],
					'font'        => [
							'type' => '',
							'size' => 10,
					],
			],
			//Insurable Hours
			'insurable_hours'    => [
					'coordinates' => [
							'x'      => 170,
							'y'      => 268,
							'h'      => 10,
							'w'      => 85,
							'halign' => 'R',
					],
					'font'        => [
							'type' => '',
							'size' => 10,
					],

			],

			//Insurable Earnings
			'insurable_earnings' => [
					'function'    => [ 'draw' => [ 'drawSplitDecimalFloat' ] ],
					'coordinates' => [
							[
									'x'      => 180,
									'y'      => 298,
									'h'      => 10,
									'w'      => 60,
									'halign' => 'R',
							],
							[
									'x'      => 239,
									'y'      => 298,
									'h'      => 10,
									'w'      => 15,
									'halign' => 'L',
							],
					],
					'font'        => [
							'type' => '',
							'size' => 10,
							//'text_color' => array( 255, 0, 0 ),
					],
			],

			'insurable_earnings_pay_periods' => [
					'function'    => [ 'draw' => [ 'filterInsurableEarningsPayPeriods', 'drawNormal' ] ],
					'coordinates' => [
							'x'      => 37,
							'y'      => 304,
							'h'      => 5,
							'w'      => 70,
							'halign' => 'C',
					],
					'font'        => [
							'type' => '',
							'size' => 8,
					],
			],

			//Enter Code
			'code_id'                        => [
					'coordinates' => [
							'x'      => 542,
							'y'      => 268,
							'h'      => 10,
							'w'      => 20,
							'halign' => 'L',
					],
					'font'        => [
							'type' => '',
							'size' => 10,
					],
			],
			'code_label'        => [
					'coordinates' => [
							'x'      => 270,
							'y'      => 270,
							'h'      => 10,
							'w'      => 270,
							'halign' => 'L',
					],
					'font'        => [
							'size' => 10,
							'type' => '',
					],
			],
			//Further Information Contact Name
			'created_user_full_name'         => [
					'function' => [ 'draw' => [ 'drawPiecemeal' ] ],

					'coordinates' => [
							[
									'x'      => 270,
									'y'      => 294,
									'h'      => 10,
									'w'      => 310,
									'halign' => 'L',
							],
							[
									'x'      => 275,
									'y'      => 710,
									'h'      => 10,
									'w'      => 210,
									'halign' => 'L',
							],
					],
					'font'        => [
							'type' => '',
							'size' => 10,
					],
			],
			'created_user_work_phone'        => [
					'function'    => [ 'draw' => [ 'drawPiecemeal' ] ],
					'coordinates' => [
							[
									'x'      => 330,
									'y'      => 305,
									'h'      => 8,
									'w'      => 250,
									'halign' => 'L',
							],
							[
									'x'      => 155,
									'y'      => 715,
									'h'      => 10,
									'w'      => 110,
									'halign' => 'L',
							],
					],
					'font'        => [
							'type' => '',
							'size' => 10,
					],
			],


			'vacation_pay' => [
					'function'    => [ 'draw' => [ 'drawSplitDecimalFloat', 'showVacationPayWarning' ] ],
					'coordinates' => [
							[
									'x'      => 510,
									'y'      => 351,
									'h'      => 10,
									'w'      => 42,
									'halign' => 'R',
							],
							[
									'x'      => 552,
									'y'      => 351,
									'h'      => 10,
									'w'      => 16,
									'halign' => 'L',
							],
					],
					'font'        => [
							'type' => '',
							'size' => 10,
					],

			],
			'comments'     => [
					'coordinates' => [
							'x'      => 290,
							'y'      => 540,
							'h'      => 45,
							'w'      => 290,
							'halign' => 'L',
					],
					'font'        => [
							'type' => '',
							'size' => 10,
					],
			],

			//English
			'english'      => [
					'function'    => [ 'draw' => [ 'drawCheckBox' ] ],
					'coordinates' => [
							[
									'x'      => 40,
									'y'      => 713,
									'h'      => 8,
									'w'      => 15,
									'halign' => 'L',
							],
					],
					'font'        => [
							'type' => '',
							'size' => 10,
					],
			],

			'created_date' => [
					'function'    => [ 'draw' => [ 'filterDate', 'drawSegments' ] ],
					'coordinates' => [
							[
									'x'      => 490,
									'y'      => 717,
									'h'      => 8,
									'w'      => 25,
									'halign' => 'C',
							],
							[
									'x'      => 518,
									'y'      => 717,
									'h'      => 8,
									'w'      => 25,
									'halign' => 'C',
							],
							[
									'x'      => 544,
									'y'      => 717,
									'h'      => 8,
									'w'      => 35,
									'halign' => 'C',
							],
					],
					'font'        => [
							'type' => '',
							'size' => 10,
					],

			],

			'pay_period_earnings' => [
					'function'    => [ 'draw' => [ 'drawGrid', 'showPayPeriod1Warning'  ] ],
					'grid'        => [
							'column'     => 3, // total columns
							'top_left_x' => 35, // start x
							'top_left_y' => 368,   // start y
							'h'          => 18,  // Each of the height of the grid
							'w'          => 60, // Each of the width of the grid
							'step_x'     => 84,
							'step_y'     => 18,
					],
					'coordinates' => [
							'halign' => 'R',
							// not in here handle .
							//'x' => 35,
							//'y' => 368,
							//'h' => 18,
							//'w' => 60,
					],
					'font'        => [
							'type' => '',
							'size' => 8,
					],
			],
			'statutory_holiday'   => [
					'function'    => [ 'draw' => [ 'drawSplitDecimalFloatGrid' ] ],
					'coordinates' => [
							[
									[
											'x'      => 352,
											'y'      => 380,
											'h'      => 10,
											'w'      => 47,
											'halign' => 'R',
									],
									[
											'x'      => 396,
											'y'      => 380,
											'h'      => 10,
											'w'      => 17,
											'halign' => 'L',

									],

							],
							[
									[
											'x'      => 507,
											'y'      => 380,
											'h'      => 10,
											'w'      => 47,
											'halign' => 'R',
									],
									[
											'x'      => 551,
											'y'      => 380,
											'h'      => 10,
											'w'      => 17,
											'halign' => 'L',
									],
							],
							[
									[
											'x'      => 352,
											'y'      => 397,
											'h'      => 10,
											'w'      => 47,
											'halign' => 'R',

									],
									[
											'x'      => 396,
											'y'      => 397,
											'h'      => 10,
											'w'      => 17,
											'halign' => 'L',
									],
							],
							[
									[
											'x'      => 507,
											'y'      => 397,
											'h'      => 10,
											'w'      => 47,
											'halign' => 'R',
									],
									[
											'x'      => 551,
											'y'      => 397,
											'h'      => 10,
											'w'      => 17,
											'halign' => 'L',
									],
							],
							[
									[
											'x'      => 352,
											'y'      => 414,
											'h'      => 10,
											'w'      => 47,
											'halign' => 'R',
									],
									[
											'x'      => 396,
											'y'      => 414,
											'h'      => 10,
											'w'      => 17,
											'halign' => 'L',
									],
							],
							[
									[
											'x'      => 507,
											'y'      => 414,
											'h'      => 10,
											'w'      => 47,
											'halign' => 'R',
									],
									[
											'x'      => 551,
											'y'      => 414,
											'h'      => 10,
											'w'      => 17,
											'halign' => 'L',
									],
							],
							[
									[
											'x'      => 352,
											'y'      => 429,
											'h'      => 10,
											'w'      => 47,
											'halign' => 'R',
									],
									[
											'x'      => 396,
											'y'      => 429,
											'h'      => 10,
											'w'      => 17,
											'halign' => 'L',
									],
							],
							[
									[
											'x'      => 507,
											'y'      => 429,
											'h'      => 10,
											'w'      => 47,
											'halign' => 'R',
									],
									[
											'x'      => 551,
											'y'      => 429,
											'h'      => 10,
											'w'      => 17,
											'halign' => 'L',
									],
							],
							[
									[
											'x'      => 352,
											'y'      => 446,
											'h'      => 10,
											'w'      => 47,
											'halign' => 'R',
									],
									[
											'x'      => 396,
											'y'      => 446,
											'h'      => 10,
											'w'      => 17,
											'halign' => 'L',
									],
							],
							[
									[
											'x'      => 507,
											'y'      => 446,
											'h'      => 10,
											'w'      => 47,
											'halign' => 'R',
									],
									[
											'x'      => 551,
											'y'      => 446,
											'h'      => 10,
											'w'      => 17,
											'halign' => 'L',
									],
							],

					],
					'font'        => [
							'type' => '',
							'size' => 10,
					],


			],
			'other_monies'        => [
					'function'    => [ 'draw' => [ 'drawSplitDecimalFloatGrid' ] ],
					'coordinates' => [
							[
									[
											'x'      => 507,
											'y'      => 476,
											'h'      => 10,
											'w'      => 47,
											'halign' => 'R',
									],
									[
											'x'      => 551,
											'y'      => 476,
											'h'      => 10,
											'w'      => 17,
											'halign' => 'L',
									],
							],
							[
									[
											'x'      => 507,
											'y'      => 494,
											'h'      => 10,
											'w'      => 47,
											'halign' => 'R',
									],
									[
											'x'      => 551,
											'y'      => 494,
											'h'      => 10,
											'w'      => 17,
											'halign' => 'L',
									],
							],
							[
									[
											'x'      => 507,
											'y'      => 514,
											'h'      => 10,
											'w'      => 47,
											'halign' => 'R',
									],
									[
											'x'      => 551,
											'y'      => 514,
											'h'      => 10,
											'w'      => 17,
											'halign' => 'L',
									],
							],
					],
					'font'        => [
							'type' => '',
							'size' => 10,
					],
			],
			'other_monies_label'        => [
					'function'    => [ 'draw' => [ 'drawNormalGrid' ] ],
					'coordinates' => [
							[
									'x'      => 285,
									'y'      => 476,
									'h'      => 10,
									'w'      => 207,
									'halign' => 'R',
							],
							[
									'x'      => 285,
									'y'      => 494,
									'h'      => 10,
									'w'      => 207,
									'halign' => 'R',
							],
							[
									'x'      => 285,
									'y'      => 514,
									'h'      => 10,
									'w'      => 207,
									'halign' => 'R',
							],

					],
					'font'        => [
							'type' => '',
							'size' => 10,
					],
			],

		];

		if ( isset( $template_schema[$name] ) ) {
			return $name;
		} else {
			return $template_schema;
		}
	}


	function filterMiddleName( $value ) {
		//Return just initial
		$value = substr( $value, 0, 1 );

		return $value;
	}

	function filterCompanyAddress( $value ) {
		//Combine company address for multicell display.
		//Dont specify postal code though, as thats in a separate box.
		return Misc::formatAddress( null, $this->company_address1, $this->company_address2, $this->company_city, $this->company_province );
	}

	function filterEmployeeAddress( $value ) {
		//Combine employee address for multicell display.
		return Misc::formatAddress( null, $this->employee_address1, $this->employee_address2, $this->employee_city, $this->employee_province, $this->employee_postal_code );
	}

	function filterDate( $value ) {
		if ( $value != '' || $value != null ) {
			$value = getdate( $value );
			$value = [ $value['mday'], $value['mon'], $value['year'] ];
		}

		return $value;
	}

	function filterInsurableEarningsPayPeriods( $value ) {
		$retval = (int)$value;
		$total_pay_periods_with_earnings = ( is_array( $this->pay_period_earnings ) ) ? count( $this->pay_period_earnings ) : 0;
		if ( $value > $total_pay_periods_with_earnings ) {
			return null; //No need to display this msg as all PPs are included.
		} else {
			return '*' . TTi18n::getText( 'Includes PP: 1 to %1', [ $retval ] );
		}
	}

	function showPayPeriod1Warning() {
		if ( isset( $this->pay_period_earnings ) && is_array( $this->pay_period_earnings ) && count( $this->pay_period_earnings ) > 0 && isset( $this->pay_period_earnings[0] ) ) {
			$last_pay_period_earnings = $this->pay_period_earnings[0];
			Debug::Text( 'First PP Earnings: ' . $last_pay_period_earnings, __FILE__, __LINE__, __METHOD__, 10 );

			if ( $last_pay_period_earnings < 1.00 ) { //Pay Period #1 (last PP) can not be less than $1.
				Debug::Text( 'Pay Period #1 must have insurable earnings of at least $1', __FILE__, __LINE__, __METHOD__, 10 );
				$this->addMessage( 'warning', 'Pay Period #1 must have insurable earnings of at least $1.', [ 'page' => $this->getCurrentPage(), 'x' => ( 33 + $this->getTempPageOffsets( 'x' ) + $this->getPageMargins( 'x' ) ), 'y' => ( 368 + $this->getTempPageOffsets( 'y' ) + $this->getPageMargins( 'y' ) ) ] );
			}
		}

		return true;
	}

	function showVacationPayWarning() {
		if ( isset( $this->vacation_pay ) && $this->vacation_pay > 0 && isset( $this->pay_period_earnings ) && is_array( $this->pay_period_earnings ) && count( $this->pay_period_earnings ) > 0 && isset( $this->pay_period_earnings[0] ) ) {
			$last_pay_period_earnings = $this->pay_period_earnings[0];
			Debug::Text( 'First PP Earnings: ' . $last_pay_period_earnings . ' Vacation Pay: ' . $this->vacation_pay, __FILE__, __LINE__, __METHOD__, 10 );

			if ( $this->vacation_pay >= $last_pay_period_earnings ) {
				Debug::Text( 'Vacation Pay equals or exceeds last PP earnings, which is not allowed, as there must also be some insurable earnings in that pay period too.', __FILE__, __LINE__, __METHOD__, 10 );
				$this->addMessage( 'warning', 'Vacation pay must not match Pay Period #1 earnings.', [ 'page' => $this->getCurrentPage(), 'x' => ( 322 + $this->getTempPageOffsets( 'x' ) + $this->getPageMargins( 'x' ) ), 'y' => ( 330 + $this->getTempPageOffsets( 'y' ) + $this->getPageMargins( 'y' ) ) ] );
			}
		}

		return true;
	}

	function _outputXML( $type = null ) {
		$records = $this->getRecords();
		Debug::Arr( $records, 'Output XML Records: ', __FILE__, __LINE__, __METHOD__, 10 );

		if ( is_array( $records ) && count( $records ) > 0 ) {

			$pay_period_type_options = [
				//5 => TTi18n::gettext('Manual'),
				10  => 'W',
				20  => 'B',
				30  => 'S',
				50  => 'M',
				100 => 'W', //Weekly 53/year
				200 => 'B', //Bi-Weekly 27/year
			];


			$xml = new SimpleXMLElement( '<ROEHEADER FileVersion="W-2.0" SoftwareVendor="' . APPLICATION_NAME . '" ProductName="' . APPLICATION_NAME . '"></ROEHEADER>' );
			$this->setXMLObject( $xml );

			$e = 0;
			foreach ( $records as $employee_data ) {

				//Debug::Arr($employee_data, 'Employee Data: ', __FILE__, __LINE__, __METHOD__,10);
				$this->arrayToObject( $employee_data ); //Convert record array to object

				$xml->addChild( 'ROE' );
				$xml->ROE[$e]->addAttribute( 'PrintingLanguage', 'E' );                                           //E=English, F=French
				$xml->ROE[$e]->addAttribute( 'Issue', 'S' );                                                      //S=Submit, D=Draft

				// Box2
				if ( $this->serial != '' ) {
					$xml->ROE[$e]->addChild( 'B2', substr( $this->serial, 0, 9 ) ); //maxLength 9  minOccurs="0" -- This is the serial number of the ROE amended or replaced.
				}

				// Box3
				if ( $this->payroll_reference_number != '' ) {
					$xml->ROE[$e]->addChild( 'B3', substr( $this->payroll_reference_number, 0, 15 ) ); //maxLength 15  minOccurs="0"
				}
				// Box5
				$xml->ROE[$e]->addChild( 'B5', substr( str_replace( ' ', '', $this->business_number ), 0, 15 ) ); //maxLength 15 - Remove any spaces as well, to prevent business numbers like: 123456789 RP 0001 from causing problems.
				// Box6
				$xml->ROE[$e]->addChild( 'B6', $pay_period_type_options[$this->pay_period_type_id] );             //maxLength 1
				// Box8
				$xml->ROE[$e]->addChild( 'B8', ( $this->sin != '' ) ? $this->sin : '999999999' );                 //maxLength 9 - This must be specified, but allow all 9's for cases where the employee may be out of the country?
				// Box9
				$xml->ROE[$e]->addChild( 'B9' );
				$xml->ROE[$e]->B9->addChild( 'FN', substr( $this->first_name, 0, 20 ) ); //maxLength 20
				if ( $this->middle_name != '' ) {
					$xml->ROE[$e]->B9->addChild( 'MN', substr( $this->middle_name, 0, 4 ) ); //maxLength 4  minOccurs="0"
				}
				$xml->ROE[$e]->B9->addChild( 'LN', substr( $this->last_name, 0, 28 ) ); //maxLength 28
				$xml->ROE[$e]->B9->addChild( 'A1', substr( Misc::stripHTMLSpecialChars( $this->employee_address1 ) . ' ' . Misc::stripHTMLSpecialChars( $this->employee_address2 ), 0, 35 ) ); //maxLength 35
				if ( $this->employee_city != '' ) {
					$xml->ROE[$e]->B9->addChild( 'A2', substr( $this->employee_city, 0, 35 ) );//maxLength 35  minOccurs="0"
				}
				if ( $this->employee_province != '' ) {
					$xml->ROE[$e]->B9->addChild( 'A3', $this->employee_province ); //maxLength 35  minOccurs="0"
				}
				if ( $this->employee_postal_code != '' ) {
					$xml->ROE[$e]->B9->addChild( 'PC', $this->employee_postal_code );
				}

				// Box10
				$xml->ROE[$e]->addChild( 'B10', date( 'Y-m-d', $this->first_date ) );                                                                                                         //maxLength 8
				// Box11
				$xml->ROE[$e]->addChild( 'B11', date( 'Y-m-d', $this->last_date ) );                                                                                                          //maxLength 8
				// Box12
				$xml->ROE[$e]->addChild( 'B12', date( 'Y-m-d', $this->pay_period_end_date ) );                                                                                                //maxLength 8
				// Box13
				if ( $this->title != '' ) {
					$xml->ROE[$e]->addChild( 'B13', substr( Misc::stripHTMLSpecialChars( $this->title ), 0, 40 ) ); //maxLength 40  minOccurs="0"
				}
				// Box14
				$xml->ROE[$e]->addChild( 'B14' );                                                                                                                                             // minOccurs="0"
				$xml->ROE[$e]->B14->addChild( 'CD', ( ( $this->recall_date != '' ) ? 'Y' : 'N' ) );                                                                                           //maxLength 1
				$xml->ROE[$e]->B14->addChild( 'DT', ( ( $this->recall_date != '' ) ? date( 'Y-m-d', $this->recall_date ) : '' ) );                                                            //maxLength 8   minOccurs="0"

				// Box15A
				$xml->ROE[$e]->addChild( 'B15A', substr( round( $this->insurable_hours ), 0, 4 ) );                                                                                           //maxLength 4
				// Box15B
				//$xml->ROE[$e]->addChild('B15B', (float)substr($this->insurable_earnings, -9, 9)); //maxLength 9

				// Box15C
				$xml->ROE[$e]->addChild( 'B15C' );

				if ( is_array( $this->pay_period_earnings ) ) {
					$i = 1;
					$x = 0;
					foreach ( $this->pay_period_earnings as $pay_period_earning ) {
						if ( $x == 53 ) {
							break;
						}
						$xml->ROE[$e]->B15C->addChild( 'PP' ); //maxOccurs="53"
						$xml->ROE[$e]->B15C->PP[$x]->addAttribute( 'nbr', $i );
						$xml->ROE[$e]->B15C->PP[$x]->addChild( 'AMT', (float)substr( $pay_period_earning, -9, 9 ) ); //maxLength 9
						$i++;
						$x++;
					}
				} else {
					$xml->ROE[$e]->B15C->addChild( 'PP' ); //maxOccurs="53"
					$xml->ROE[$e]->B15C->PP->addAttribute( 'nbr', 1 );
					$xml->ROE[$e]->B15C->PP->addChild( 'AMT', 0 ); //maxLength 9
				}
				// Box16
				$xml->ROE[$e]->addChild( 'B16' );
				$xml->ROE[$e]->B16->addChild( 'CD', $this->code_id );                 //maxLength 1
				$xml->ROE[$e]->B16->addChild( 'FN', substr( $this->created_user_first_name, 0, 20 ) ); //maxLength 20
				$xml->ROE[$e]->B16->addChild( 'LN', substr( $this->created_user_last_name, 0, 28 ) );  //maxLength 28

				if ( $this->created_user_work_phone != '' ) {
					$phone = $this->created_user_work_phone;
				} else if ( $this->company_work_phone != '' ) {
					$phone = $this->company_work_phone;
				}

				$validator = new Validator();
				$phone = $validator->stripNonNumeric( $phone );

				$xml->ROE[$e]->B16->addChild( 'AC', substr( $phone, 0, 3 ) );  //maxLength 3
				$xml->ROE[$e]->B16->addChild( 'TEL', substr( $phone, 3, 7 ) ); //maxLength 7

				// Box17A
				if ( $this->vacation_pay > 0 ) {
					//$xml->ROE[$e]->addChild('B17A', (float)substr($this->vacation_pay, -9, 9)); // maxLength 9   minOccurs="0"
					$xml->ROE[$e]->addChild( 'B17A' );
					$xml->ROE[$e]->B17A->addChild( 'VP' );
					$xml->ROE[$e]->B17A->VP->addAttribute( 'nbr', 1 );

					//FIXME: Detect if the vacation pay is not linked to an accrual account, then select "Included with each Pay".
					$xml->ROE[$e]->B17A->VP->addChild( 'CD', 2 ); //1=Included with each pay, 2=Paid because no longer working
					$xml->ROE[$e]->B17A->VP->addChild( 'AMT', (float)substr( $this->vacation_pay, -9, 9 ) );
				}
				//else {
				//	$xml->ROE[$e]->addChild( 'B17A' );
				//	$xml->ROE[$e]->B17A->addChild( 'VP' );
				//	$xml->ROE[$e]->B17A->VP->addAttribute( 'nbr', 1 );
				//	$xml->ROE[$e]->B17A->VP->addChild( 'CD', 1 ); //1=Included with each pay, 2=Paid because no longer working
				//}

				// Box17B
				if ( is_array( $this->statutory_holiday ) ) {
					$xml->ROE[$e]->addChild( 'B17B' ); // minOccurs="0"

					$x = 0;
					$i = 0;
					foreach ( $this->statutory_holiday as $holiday ) {

						if ( $x == 3 ) {
							break;
						}
						if ( is_array( $holiday ) ) {
							$xml->ROE[$e]->B17B->addChild( 'SH' ); //minOccurs="0" maxOccurs="3"
							$xml->ROE[$e]->B17B->SH[$x]->addAttribute( 'nbr', $i );
							$xml->ROE[$e]->B17B->SH[$x]->addChild( 'DT', date( 'Y-m-d', $holiday['date'] ) );           //maxLength 8
							$xml->ROE[$e]->B17B->SH[$x]->addChild( 'AMT', (float)substr( $holiday['amount'], -9, 9 ) ); //maxLength 9

							$x++;
							$i++;
						} else {
							continue;
						}
					}
				}


				// Box17C
				if ( is_array( $this->other_monies ) ) {
					$xml->ROE[$e]->addChild( 'B17C' ); //minOccurs="0"

					$x = 0;
					$i = 1;
					foreach ( $this->other_monies as $monies ) {
						if ( $x == 3 ) {
							break;
						}
						if ( is_array( $monies ) ) {
							$xml->ROE[$e]->B17C->addChild( 'OM' ); //minOccurs="0" maxOccurs="3"
							$xml->ROE[$e]->B17C->OM[$x]->addAttribute( 'nbr', $i );
							$xml->ROE[$e]->B17C->OM[$x]->addChild( 'CD', $monies['code'] );               //minLength: 3 maxLength: 3 See Other Monies Codes: https://www.canada.ca/en/employment-social-development/programs/ei/ei-list/ei-roe/user-requirements/appendix-d.html  -- "Table 12: 17C.1 – Other Monies Information"
							$xml->ROE[$e]->B17C->OM[$x]->addChild( 'AMT', (float)substr( $monies['amount'], -9, 9 ) ); //maxLength 9

							$x++;
							$i++;
						} else {
							continue;
						}
					}
				}

				// Box18
				if ( $this->comments != '' ) {
					$xml->ROE[$e]->addChild( 'B18', substr( Misc::stripHTMLSpecialChars( $this->comments ), 0, 160 ) ); //minOccurs="0", maxLength=160
				}


				// Box19   //minOccurs="0"


				// Box20
				$xml->ROE[$e]->addChild( 'B20', 'E' );                         //Language //minOccurs="0"  //maxLength 1

				$this->revertToOriginalDataState();

				$e++;
			}
		}

		return true;
	}


	function _outputPDF( $type ) {
		//Initialize PDF with template.
		$pdf = $this->getPDFObject();

		if ( $this->getShowBackground() == true ) {

			$pdf->setSourceFile( $this->getTemplateDirectory() . DIRECTORY_SEPARATOR . $this->pdf_template );

			$this->template_index[1] = $pdf->ImportPage( 1 );
			//$this->template_index[2] = $pdf->ImportPage(2);
			//$this->template_index[3] = $pdf->ImportPage(3);
		}

		if ( $this->year == '' ) {
			$this->year = $this->getYear();
		}

		//Get location map, start looping over each variable and drawing
		$records = $this->getRecords();
		if ( is_array( $records ) && count( $records ) > 0 ) {

			$template_schema = $this->getTemplateSchema();

			$e = 0;
			foreach ( $records as $employee_data ) {
				//Debug::Arr($employee_data, 'Employee Data: ', __FILE__, __LINE__, __METHOD__,10);
				$this->arrayToObject( $employee_data ); //Convert record array to object

				foreach ( $template_schema as $field => $schema ) {
					//Debug::text('Drawing Cell... Field: '. $field, __FILE__, __LINE__, __METHOD__, 10);
					$this->Draw( $this->$field, $schema );
				}

				$this->resetTemplatePage();

				$this->revertToOriginalDataState();

				$e++;
			}
		}

		$this->drawMessages();

		return true;
	}
}

?>