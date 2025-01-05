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


include_once( 'US.class.php' );

//Example of how other software handles 1099-NEC through FIRE:
//  https://docs.cmicglobal.com/portal/Content/E_Reference_Material/1099/Reference/Setup/1099-NEC_and_1099-MISC.htm

/**
 * @package GovernmentForms
 */
class GovernmentForms_US_1099NEC extends GovernmentForms_US {
	public $pdf_template = '1099nec.pdf';

	function getOptions( $name ) {
		$retval = null;
		switch ( $name ) {
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
		if ( isset( $this->_type ) ) {
			return $this->_type;
		}

		return false;
	}

	function setType( $value ) {
		$this->_type = trim( $value );

		return true;
	}

	function getShowInstructionPage() {
		if ( isset( $this->_show_instruction_page ) ) {
			return $this->_show_instruction_page;
		}

		return false;
	}

	function setShowInstructionPage( $value ) {
		$this->_show_instruction_page = (bool)trim( $value );

		return true;
	}

	public function getTemplateSchema( $name = null ) {
		$template_schema = [
			[
				//'template_page' => 2, //All template pages
				'value'         => $this->year,
				'on_background' => true,
				'coordinates'   => [
						'x'          => 400,
						'y'          => 94,
						'h'          => 12,
						'w'          => 65,
						'halign'     => 'C',
						'fill_color' => [ 255, 255, 255 ],
				],
				'font'          => [
						'size' => 10,
				],
			],
			[
				//'template_page' => 2, //All template pages
				'value'         => '', //Hide the "Rev. January 2022" at top right.
				'on_background' => true,
				'coordinates'   => [
						'x'          => 398,
						'y'          => 72,
						'h'          => 10,
						'w'          => 68,
						'halign'     => 'L',
						'fill_color' => [ 255, 255, 255 ],
				],
				'font'          => [
						'size' => 8,
						//'type' => 'B',
				],
			],
			[
				//'template_page' => 2, //All template pages
				'value'         => '', //Hide the "Rev. 1-2022" at bottom left
				'on_background' => true,
				'coordinates'   => [
						'x'          => 118,
						'y'          => 241,
						'h'          => 10,
						'w'          => 50,
						'halign'     => 'L',
						'fill_color' => [ 255, 255, 255 ],
				],
				'font'          => [
						'size' => 8,
						//'type' => 'B',
				],
			],

			//Finish initializing page 1.
			'payer_id'     => [
					'function'      => [ 'draw' => [ 'filterTINS', 'drawNormal' ] ],
					'coordinates' => [
							'x'      => 52,
							'y'      => 119,
							'h'      => 12,
							'w'      => 115,
							'halign' => 'L',
					],
			],
			'recipient_id' => [
					'function'      => [ 'draw' => [ 'filterTINS', 'formatSSN', 'drawNormal' ] ],
					'coordinates' => [
							'x'      => 174,
							'y'      => 119,
							'h'      => 12,
							'w'      => 115,
							'halign' => 'L',
					],
			],

			'trade_name'      => [
					'coordinates' => [
							'x'      => 52,
							'y'      => 55,
							'h'      => 15,
							'w'      => 230,
							'halign' => 'L',
					],
			],
			'company_address' => [
					'function'    => [ 'draw' => [ 'filterCompanyAddress', 'drawNormal' ] ],
					'coordinates' => [
							'x'      => 52,
							'y'      => 70,
							'h'      => 38,
							'w'      => 230,
							'halign' => 'L',
					],
					'font'        => [
							'size' => 8,
							'type' => '',
					],
					'multicell'   => true,
			],

			'name'           => [
					'function'    => [ 'draw' => [ 'filterName', 'drawNormal' ] ],
					'coordinates' => [
							'x'      => 52,
							'y'      => 142,
							'h'      => 15,
							'w'      => 180,
							'halign' => 'L',
					],
			],
			'address'        => [
					'function'    => [ 'draw' => [ 'filterAddress', 'drawNormal' ] ],
					'coordinates' => [
							'x'      => 52,
							'y'      => 180,
							'h'      => 12,
							'w'      => 180,
							'halign' => 'L',
					],
					'font'        => [
							'size' => 8,
							'type' => '',
					],
					'multicell'   => true,
			],
			'city'           => [
					'function'    => [ 'draw' => [ 'filterCity', 'drawNormal' ] ],
					'coordinates' => [
							'x'      => 52,
							'y'      => 202,
							'h'      => 12,
							'w'      => 180,
							'halign' => 'L',
					],
			],
			'account_number' => [
					'coordinates' => [
							'x'      => 52,
							'y'      => 226,
							'h'      => 12,
							'w'      => 180,
							'halign' => 'L',
					],
			],
			'l1' => [
					'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
					'coordinates' => [
							'x'      => 305,
							'y'      => 119,
							'h'      => 12,
							'w'      => 80,
							'halign' => 'L',
					],
			],
			'l4' => [
					'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
					'coordinates' => [
							'x'      => 305,
							'y'      => 191,
							'h'      => 12,
							'w'      => 80,
							'halign' => 'L',
					],
			],
			'l5a' => [
					'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
					'coordinates' => [
							'x'      => 305,
							'y'      => 216,
							'h'      => 10,
							'w'      => 60,
							'halign' => 'L',
					],
			],
			'l5b' => [
					'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
					'coordinates' => [
							'x'      => 305,
							'y'      => 229,
							'h'      => 10,
							'w'      => 60,
							'halign' => 'L',
					],
			],
			'l6a' => [
					'coordinates' => [
							'x'      => 380,
							'y'      => 216,
							'h'      => 10,
							'w'      => 80,
							'halign' => 'L',
					],
			],
			'l6b' => [
					'coordinates' => [
							'x'      => 380,
							'y'      => 229,
							'h'      => 10,
							'w'      => 80,
							'halign' => 'L',
					],
			],

			'l7a' => [
					'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
					'coordinates' => [
							'x'      => 505,
							'y'      => 216,
							'h'      => 10,
							'w'      => 60,
							'halign' => 'L',
					],
			],
			'l7b' => [
					'function'    => [ 'draw' => [ 'MoneyFormat', 'drawNormal' ] ],
					'coordinates' => [
							'x'      => 505,
							'y'      => 229,
							'h'      => 10,
							'w'      => 60,
							'halign' => 'L',
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
		$retarr[] = $this->company_address1;
		if ( $this->company_address2 != '' ) {
			$retarr[] = $this->company_address2;
		}
		$retarr[] = $this->company_city . ', ' . $this->company_state . ' ' . $this->company_zip_code;
		$retarr[] = $this->company_phone;

		return implode( "\n", $retarr );
	}

	function filterName( $value ) {
		//return $this->first_name . ', ' . $this->last_name . ' ' . $this->middle_name;
		return $this->first_name . ' ' . $this->last_name; //Example in 1099-MISC shows just first and last name separated with space. Haven't found instructions on an exact format though.
	}

	function filterAddress( $value ) {
		//Combine employee address for single line display.
		$retval = $this->address1;
		if ( $this->address2 != '' ) {
			$retval .= ' '. $this->address2;
		}

		return $retval;
	}

	function filterCity( $value ) {
		return $this->city . ', ' . $this->state . ' ' . $this->zip_code;
	}

	function filterTINS( $value ) {
		////Truncate TINS on the employees copy only.
		//if ( in_array( (int)$this->current_template_index, [ 3 ] ) ) {
		//	return false;
		//}

		return $value;
	}


	function _outputPDF( $type ) {
		//Initialize PDF with template.
		$pdf = $this->getPDFObject();

		if ( $this->getShowBackground() == true ) {
			$pdf->setSourceFile( $this->getTemplateDirectory() . DIRECTORY_SEPARATOR . $this->pdf_template );

			for ( $tp = 1; $tp <= 8; $tp++ ) {
				$this->template_index[$tp] = $pdf->ImportPage( $tp );
			}
		}
		Debug::Arr( $this->template_index, 'Template Index ', __FILE__, __LINE__, __METHOD__, 10 );

		if ( $this->year == '' ) {
			$this->year = $this->getYear();
		}

		if ( $this->getType() == 'government' ) {
			$employees_per_page = 3;
			$n = 1;                             //Don't loop the same employee.
			$form_template_pages = [ 2, 3, 7 ]; //Template pages to use.
		} else {
			$employees_per_page = 1;
			$n = 1;                          //Loop the same employee twice.
			$form_template_pages = [ 4, 6 ]; //Template pages to use.
		}

		//Get location map, start looping over each variable and drawing
		$records = $this->getRecords();
		if ( is_array( $records ) && count( $records ) > 0 ) {
			$template_schema = $this->getTemplateSchema();

			foreach ( $form_template_pages as $form_template_page ) {
				//Set the template used.
				Debug::Text( 'Template Page: ' . $form_template_page, __FILE__, __LINE__, __METHOD__, 10 );
				$template_schema[0]['template_page'] = $form_template_page;

				if ( $this->getType() == 'government' && count( $records ) > 1 ) {
					$template_schema[0]['combine_templates'] = [
							[ 'template_page' => $form_template_page, 'x' => 0, 'y' => 0 ],
							[ 'template_page' => $form_template_page, 'x' => 0, 'y' => 255 ], //Place three templates on the same page.
							[ 'template_page' => $form_template_page, 'x' => 0, 'y' => 510 ] //Place three templates on the same page.
					];
				} else {
					Debug::Text( 'zTemplate Page: ' . $form_template_page . ' C: ' . count( $records ) . ' B: ' . $this->getShowBackground() . ' D: ' . $this->getType() . ' X: ' . $this->_type, __FILE__, __LINE__, __METHOD__, 10 );
				}

				$e = 0;
				foreach ( $records as $employee_data ) {
					//Debug::Arr($employee_data, 'Employee Data: ', __FILE__, __LINE__, __METHOD__,10);
					//Debug::Text(' E: '. $e .' T: '. $form_template_page .' Employee : '. $employee_data['first_name'], __FILE__, __LINE__, __METHOD__,10);
					$this->arrayToObject( $employee_data ); //Convert record array to object

					for ( $i = 0; $i < $n; $i++ ) {
						$this->setTempPageOffsets( $this->getPageOffsets( 'x' ), $this->getPageOffsets( 'y' ) );

						if ( ( $employees_per_page == 1 && $i > 0 )
								|| ( $employees_per_page == 3 && $e % $employees_per_page != 0 )
						) {
							$this->setTempPageOffsets( $this->getPageOffsets( 'x' ), ( $template_schema[0]['combine_templates'][($e % 3)]['y'] + $this->getPageOffsets( 'y' ) ) );
						}

						foreach ( $template_schema as $field => $schema ) {
							$this->Draw( $this->$field, $schema );
						}
					}

					if ( $employees_per_page == 1 || ( $employees_per_page == 3 && $e % $employees_per_page == ( $employees_per_page - 1 ) ) ) {
						$this->resetTemplatePage();
						//if ( $this->getShowInstructionPage() == TRUE ) {
						//	$this->addPage( array('template_page' => 2) );
						//}
					}

					$this->revertToOriginalDataState();

					$e++;
				}
			}
		}

		return true;
	}
}

?>