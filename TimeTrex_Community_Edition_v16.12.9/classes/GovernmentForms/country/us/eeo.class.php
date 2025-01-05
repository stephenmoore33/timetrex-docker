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

/**
 * @package GovernmentForms
 */
class GovernmentForms_US_EEO extends GovernmentForms_US {
	function getOptions( $name ) {
		$retval = null;

		switch ( $name ) {
			case 'eeo1_report_type':
				$retval = [
						1 => TTi18n::getText('Type 1 - Single-establishment'),
						//2 => TTi18n::getText('Type 2 - Multi-establishment: Consolidated'),
						3 => TTi18n::getText('Type 3 - Multi-establishment: Headquarters'),
						4 => TTi18n::getText('Type 4 - Multi-establishment: Individual establishment'), //50EE/establishment or less
						//5 => TTi18n::getText('Type 5 - Multi-establishment employer, special'), -- Discontinued
						//6 => TTi18n::getText('Type 6 - Multi-establishment employer, list'), -- Discontinued
						8 => TTi18n::getText('Type 8 - Multi-establishment: Individual establishment'), //50EE/establishment or more
						9 => TTi18n::getText('Type 9 - Multi-establishment: First time establishment'), //new “Establishment-Level Report” that has never been reported in prior-year EEO-1 Component 1 reports and has never been assigned an Establishment-Level Unit ID.
				];
				break;
			case 'gender':
				$retval = [
						5 => TTi18n::getText('Unspecified'),
						10 => TTi18n::getText('Male'),
						20 => TTi18n::getText('Female'),
						100 => TTi18n::getText('Non-Binary'),
				];
				break;
			case 'eeo4_salary_band':
			case 'eeo4_salary_band_with_zero':
				$retval = [
						15999 => TTi18n::getText('$0.1 - $15.9'),
						19999 => TTi18n::getText('$16.0 - $19.9'),
						24999 => TTi18n::getText('$20.0 - $24.9'),
						32999 => TTi18n::getText('$25.0 - $32.9'),
						42999 => TTi18n::getText('$33.0 - $42.9'),
						54999 => TTi18n::getText('$43.0 - $54.9'),
						69999 => TTi18n::getText('$55.0 - $69.9'),
						70000 => TTi18n::getText('$70.0 PLUS'),
				];
				if ( $name == 'eeo4_salary_band_with_zero' ) {
					$retval[99] = TTi18n::getText('$0');
				}
				ksort( $retval );
				break;
			case 'ca_eeo_salary_band':
				//**Note: if changing, update the band code below too.
				$retval = [
						19239 => TTi18n::getText('(1) $19,239 and under'),
						24959 => TTi18n::getText('(2) $19,240 – $24,959'),
						32239 => TTi18n::getText('(3) $24,960 – $32,239'),
						41079 => TTi18n::getText('(4) $32,240 – $41,079'),
						53039 => TTi18n::getText('(5) $41,080 – $53,039'),
						68119 => TTi18n::getText('(6) $53,040 – $68,119'),
						87359 => TTi18n::getText('(7) $68,120 – $87,359'),
						112319 => TTi18n::getText('(8) $87,360 – $112,319'),
						144559 => TTi18n::getText('(9) $112,320 – $144,559'),
						186159 => TTi18n::getText('(10) $144,560 – $186,159'),
						239199 => TTi18n::getText('(11) $186,160 – $239,199'),
						239200 => TTi18n::getText('(12) $239,200 and over'),
				];
				break;
			case 'ca_eeo_salary_band_code':
				//**Note: if changing, update the band labels above too.
				$retval = [
						19239 => 1,
						24959 => 2,
						32239 => 3,
						41079 => 4,
						53039 => 5,
						68119 => 6,
						87359 => 7,
						112319 => 8,
						144559 => 9,
						186159 => 10,
						239199 => 11,
						239200 => 12,
				];
				break;
			case 'eeo4_function':
				$retval = [
						1 => TTi18n::getText('Financial Administration'),
						2 => TTi18n::getText('Streets and Highways'),
						3 => TTi18n::getText('Public Welfare'),
						4 => TTi18n::getText('Police Protection'),
						5 => TTi18n::getText('Fire Protection'),
						6 => TTi18n::getText('Natural Resources'),
						7 => TTi18n::getText('Hospitals'),
						8 => TTi18n::getText('Health'),
						9 => TTi18n::getText('Housing'),
						10 => TTi18n::getText('Community Development'),
						11 => TTi18n::getText('Corrections'),
						12 => TTi18n::getText('Utilities and Transportation'),
						13 => TTi18n::getText('Sanitation and Sewage'),
						14 => TTi18n::getText('Employment Security'),
						15 => TTi18n::getText('Other'),
				];
				break;
		}

		return $retval;
	}

	function getEEOSalaryBand( $salary, $type ) {
		if ( $type == 'eeo4' ) {
			$salary_bands = $this->getOptions( 'eeo4_salary_band_with_zero' );
		} else if ( $type == 'eeo_ca' ) {
			$salary_bands = $this->getOptions( 'ca_eeo_salary_band' );
		} else if ( $type == 'eeo_ca_code' ) {
			$salary_bands = $this->getOptions( 'ca_eeo_salary_band_code' );
		} else {
			$salary_bands = [];
		}

		$prev_value = 0;
		$total_rates = ( count( $salary_bands ) - 1 );
		$i = 0;
		foreach ( $salary_bands as $band_salary => $label ) {
			if ( $salary == 0 || ( $salary > $prev_value && $salary <= $band_salary ) ) {
				return $label;
			} else if ( $i == $total_rates ) {
				return $label;
			}

			$prev_value = $band_salary;
			$i++;
		}

		return false;
	}

	function getEEOJobCategoryOptions() {
		switch ( $this->efile_export_type ) {
			case 'eeo1':
				//This maps to the matrix row.
				$retarr = [
						1 => TTi18n::getText('Executive/Senior Level Officials and Managers'),
						2 => TTi18n::getText('First/Mid-Level Officials and Managers'),
						3 => TTi18n::getText('Professionals'),
						4 => TTi18n::getText('Technicians'),
						5 => TTi18n::getText('Sales Workers'),
						6 => TTi18n::getText('Administrative Support Workers'),
						7 => TTi18n::getText('Craft Workers'),
						8 => TTi18n::getText('Operatives'),
						9 => TTi18n::getText('Laborers and Helpers'),
						10 => TTi18n::getText('Service Workers'),
				];

				break;
			case 'eeo4':
				$retarr = [
						1 => TTi18n::getText('Officials – Administrators'),
						2 => TTi18n::getText('Professionals'),
						3 => TTi18n::getText('Technicians'),
						4 => TTi18n::getText('Protective Service'),
						5 => TTi18n::getText('Paraprofessionals'),
						6 => TTi18n::getText('Administrative Support'),
						7 => TTi18n::getText('Skilled Craft'),
						8 => TTi18n::getText('Service – Maintenance'),
				];

				break;
			case 'eeo_ca':
				$retarr = [
						1 => TTi18n::getText('Executive senior level officials and managers'),
						2 => TTi18n::getText('First or mid-level officials and managers'),
						3 => TTi18n::getText('Professionals'),
						4 => TTi18n::getText('Technicians'),
						5 => TTi18n::getText('Sales workers'),
						6 => TTi18n::getText('Administrative support workers'),
						7 => TTi18n::getText('Craft workers'),
						8 => TTi18n::getText('Operatives'),
						9 => TTi18n::getText('Laborers and helpers'),
						10 => TTi18n::getText('Service workers'),
				];

				break;
		}

		return $retarr;
	}

	//Maps ethnicty/gender to a matrix column, then CSV column.
	function getEEOEthnicityGenderMatrixOptions( $gender ) {
		$retarr = [];

		switch ( $this->efile_export_type ) {
			case 'eeo1':
			case 'eeo4':
				switch ( strtolower( $gender ) ) {
					case 'male':
						//This maps the to the row *OFFSET* in the matrix.
						$retarr = [
								'1' => TTi18n::getText('Hispanic or Latino'), //A
								'3' => TTi18n::getText('White'), //C
								'4' => TTi18n::getText('Black or African American'), //D
								'5' => TTi18n::getText('Native Hawaiian or Other Pacific Islander'), //E
								'6' => TTi18n::getText('Asian'), //F
								'7' => TTi18n::getText('American Indian or Alaska Native'), //G
								'8' => TTi18n::getText('Two or More Races'), //H
						];
						break;
					case 'female': //Female
						$retarr = [
								'2' => TTi18n::getText('Hispanic or Latino'), //B
								'9' => TTi18n::getText('White'), //I
								'10' => TTi18n::getText('Black or African American'), //J
								'11' => TTi18n::getText('Native Hawaiian or Other Pacific Islander'), //K
								'12' => TTi18n::getText('Asian'), //L
								'13' => TTi18n::getText('American Indian or Alaska Native'), //M
								'14' => TTi18n::getText('Two or More Races'), //N
						];
						break;
				}
				break;
			case 'eeo_ca':
				switch ( strtolower( $gender ) ) {
					case 'male':
						//This maps the to the row *OFFSET* in the matrix.
						$retarr = [
								'A10' => TTi18n::getText('Hispanic or Latino'), //A
								'B10' => TTi18n::getText('White'), //C
								'B20' => TTi18n::getText('Black or African American'), //D
								'B30' => TTi18n::getText('Native Hawaiian or Other Pacific Islander'), //E
								'B40' => TTi18n::getText('Asian'), //F
								'B50' => TTi18n::getText('American Indian or Alaska Native'), //G
								'B60' => TTi18n::getText('Two or More Races'), //H
						];
						break;
					case 'female': //Female
						$retarr = [
								'A20' => TTi18n::getText('Hispanic or Latino'), //B
								'C10' => TTi18n::getText('White'), //C
								'C20' => TTi18n::getText('Black or African American'), //D
								'C30' => TTi18n::getText('Native Hawaiian or Other Pacific Islander'), //E
								'C40' => TTi18n::getText('Asian'), //F
								'C50' => TTi18n::getText('American Indian or Alaska Native'), //G
								'C60' => TTi18n::getText('Two or More Races'), //H
						];
						break;
					case 'other': //Non-Binary
						$retarr = [
								'A30' => TTi18n::getText('Hispanic or Latino'), //B
								'D10' => TTi18n::getText('White'), //C
								'D20' => TTi18n::getText('Black or African American'), //D
								'D30' => TTi18n::getText('Native Hawaiian or Other Pacific Islander'), //E
								'D40' => TTi18n::getText('Asian'), //F
								'D50' => TTi18n::getText('American Indian or Alaska Native'), //G
								'D60' => TTi18n::getText('Two or More Races'), //H
						];
						break;
				}
				break;
		}

		return $retarr;
	}

	function getEEOFunctionMatrixRow( $function ) {
		$retval = Misc::findClosestMatch( $function, $this->getOptions('eeo4_function'), 90 );
		return $retval;
	}

	function getEEOSalaryBandMatrixRow( $salary_band ) {
		$salary_bands = $this->getOptions('eeo4_salary_band');
		$retval = Misc::findClosestMatch( $salary_band, array_values( $salary_bands ), 100 );
		if ( $retval !== false ) {
			$retval += 1; //array_values starts at 0, so add 1 to properly map to spreadsheet row.
		}
		return $retval;
	}

	function getEEOJobCategoryMatrixRow( $job_category ) {
		$retval = Misc::findClosestMatch( $job_category, $this->getEEOJobCategoryOptions(), 90 );
		return $retval;
	}

	function getEEOEthnicityGenderMatrixColumn( $ethnicity, $gender ) {
		$retval = Misc::findClosestMatch( $ethnicity, $this->getEEOEthnicityGenderMatrixOptions( $gender ) , 90 );
		return $retval;
	}

	public function getTemplateSchema( $name = null ) {
		$template_schema = [];

		if ( isset( $template_schema[$name] ) ) {
			return $name;
		} else {
			return $template_schema;
		}
	}

	function _outputEFILE( $type = null ) {
		$records = $this->getRecords();

		//Debug::Arr($records, 'Output EFILE Records: ',__FILE__, __LINE__, __METHOD__, 10);

		if ( is_array( $records ) && count( $records ) > 0 ) {
			switch( strtolower( $this->efile_export_type ) ) {
				case 'eeo1':
					//Split records based on establishment ID
					foreach( $records as $record ) {
						if ( $record['establishment_id'] == '' ) {
							$record['establishment_id'] = 'N/A';
						}

						$establishment_records[$record['establishment_id']][] = $record;
					}

					$spreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
					$sheet = $spreadsheet->getActiveSheet();

					$primary_row_num = 1;
					foreach( $establishment_records as $records ) {
						//Write company specific data.
						$sheet->setCellValue( [ 1, $primary_row_num ], $records[0]['company_id'] ); //Company ID - Unique Identifier For Entire Company. Should be 7 characters.
						$sheet->setCellValue( [ 2, $primary_row_num ], (int)$this->eeo1_report_type ); //Status Code (1 = Single Establishment Employer)
						$sheet->setCellValue( [ 3, $primary_row_num ], $records[0]['establishment_id'] ); //Company ID For single-establishment companies, this is the same as Field 1.
						$sheet->setCellValue( [ 4, $primary_row_num ], $records[0]['establishment_name'] ); //Company Name
						$sheet->setCellValue( [ 5, $primary_row_num ], $records[0]['establishment_address1'] ); //Company Address 1
						$sheet->setCellValue( [ 6, $primary_row_num ], $records[0]['establishment_address2'] ); //Company Address 2
						$sheet->setCellValue( [ 7, $primary_row_num ], $records[0]['establishment_city'] ); //City
						$sheet->setCellValue( [ 8, $primary_row_num ], $records[0]['establishment_province'] ); //State
						$sheet->setCellValue( [ 9, $primary_row_num ], $records[0]['establishment_postal_code'] ); //Zip Code

						$sheet->setCellValue( [ 10, $primary_row_num ], null ); //2023 - No longer used.
						$sheet->setCellValue( [ 11, $primary_row_num ], null ); //2023 - No longer used.
						$sheet->setCellValue( [ 12, $primary_row_num ], null ); //2023 - No longer used.
						$sheet->setCellValue( [ 13, $primary_row_num ], ( trim( $records[0]['establishment_uei'] ) != '' ? 'Y' : 'N' ) ); //Federal Contractor Designation, Y=Yes, N=No - If a federal contractor (UEI) is specified, this should be Yes.
						$sheet->setCellValue( [ 14, $primary_row_num ], null );
						$sheet->setCellValue( [ 15, $primary_row_num ], null ); //County Name
						$sheet->setCellValue( [ 16, $primary_row_num ], (string)date( 'mdY', $this->snapshot_start_date ).(string)date( 'mdY', $this->snapshot_end_date ) ); //TODO: Dates of Workforce Snapshot Pay Period Used - Workforce Snapshot Pay Period Used For The Report in format of MMDDYYYYMMDDYYYY. Time Frame May Be Any Pay Period In October, November Or December Of The Report Year and must be less than 31 days.
						$sheet->setCellValue( [ 17, $primary_row_num ], null );
						$sheet->setCellValue( [ 18, $primary_row_num ], null ); //2023 - No longer used. - Title Of Certifying Official
						$sheet->setCellValue( [ 19, $primary_row_num ], null ); //2023 - No longer used. - Name Of Certifying Official
						$sheet->setCellValue( [ 20, $primary_row_num ], null ); //2023 - No longer used. - Telephone Number
						$sheet->setCellValue( [ 21, $primary_row_num ], null ); //2023 - No longer used. - Email

						$matrix_column_offset = 21; //20=Offset from Matrix Column 1.
						$matrix_total_columns = 15;


						//Convert grouped data to spreadsheet columns/matrix.
						$grouped_records = $records; //Group-by overwrites this data, so make a copy of it first.
						$group_by = [ 'job_category' => true, 'gender' => true, 'ethnicity' => true, 'total_user' => 'sum' ];
						$grouped_data = Group::GroupBy( $grouped_records, $group_by );
						foreach( $grouped_data as $row ) {
							$job_category_matrix_row = $this->getEEOJobCategoryMatrixRow( $row['job_category'] );
							$ethnicity_gender_maxtrix_row = $this->getEEOEthnicityGenderMatrixColumn( $row['ethnicity'], $row['gender'] );
							if ( $job_category_matrix_row !== false && $ethnicity_gender_maxtrix_row !== false ) {
								$row_num = ( $job_category_matrix_row - 1 );
								$column_num = ( $ethnicity_gender_maxtrix_row + ( $matrix_column_offset + ( $row_num * $matrix_total_columns ) ) );

								$sheet->setCellValue( [ $column_num, $primary_row_num ], $row['total_user'] );
							} else {
								Debug::Text( 'Invalid: Unable to find Job Category Matrix Row or Ethnicity Matrix Row for: Job Category: '. $row['job_category'] .' Ethnicity: '. $row['ethnicity'] .' Gender: '. $row['gender'], __FILE__, __LINE__, __METHOD__, 10 );
							}
						}


						//Total males/females for each row
						$gender_records = $records; //Group-by overwrites this data, so make a copy of it first.
						$gender_group_by = [ 'job_category' => true, 'total_user' => 'sum' ];
						$gender_grouped_data = Group::GroupBy( $gender_records, $gender_group_by );
						foreach( $gender_grouped_data as $row ) {
							$job_category_matrix_row = $this->getEEOJobCategoryMatrixRow( $row['job_category'] );
							if ( $job_category_matrix_row !== false ) {
								$row_num = $job_category_matrix_row; //Don't minus 1 from this, as the total column is at the end.
								$column_num = ( $matrix_column_offset + ( $row_num * $matrix_total_columns ) );

								$sheet->setCellValue( [ $column_num, $primary_row_num ], $row['total_user'] );
							} else {
								Debug::Text( 'Invalid: Unable to find Job Category Matrix Row for: '. $row['job_category'], __FILE__, __LINE__, __METHOD__, 10 );
							}
						}


						$overall_total = 0;

						//Totals for each ethnicity/gender
						$ethnicity_records = $records; //Group-by overwrites this data, so make a copy of it first.
						$ethnicity_group_by = [ 'gender' => true, 'ethnicity' => true, 'total_user' => 'sum' ];
						$ethnicity_grouped_data = Group::GroupBy( $ethnicity_records, $ethnicity_group_by );
						foreach( $ethnicity_grouped_data as $row ) {
							$row_num = 10;

							$ethnicity_gender_maxtrix_row = $this->getEEOEthnicityGenderMatrixColumn( $row['ethnicity'], $row['gender'] );
							if ( $ethnicity_gender_maxtrix_row !== false ) {
								$column_num = ( $ethnicity_gender_maxtrix_row + ( $matrix_column_offset + ( $row_num * $matrix_total_columns ) ) );

								$sheet->setCellValue( [ $column_num, $primary_row_num ], $row['total_user'] );
								$overall_total += $row['total_user'];
							} else {
								Debug::Text( 'Invalid: Unable to find Ethnicity Matrix Row for: Ethnicity: '. $row['ethnicity'] .' Gender: '. $row['gender'], __FILE__, __LINE__, __METHOD__, 10 );
							}
						}
						//\PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString('GD'); -- Convert Column string to numeric index.
						$sheet->setCellValue( [ ( $matrix_column_offset + ( 11 * $matrix_total_columns ) ), $primary_row_num ], $overall_total );

						$sheet->setCellValue( [ ( ( $matrix_column_offset + ( 11 * $matrix_total_columns ) ) + 1 ), $primary_row_num ], (string)$this->ein ); //Column: GE = EIN
						$sheet->setCellValue( [ ( ( $matrix_column_offset + ( 11 * $matrix_total_columns ) ) + 2 ), $primary_row_num ], $records[0]['establishment_uei'] ); //Column: GF = Unique Entity ID (UEI) - Length 12
						$sheet->setCellValue( [ ( ( $matrix_column_offset + ( 11 * $matrix_total_columns ) ) + 3 ), $primary_row_num ], '' ); //Column: GG = Employer/HQ/Establishment Comments

						$primary_row_num++;
					}

					//Get final data into a string for writing to a file.
					$writer = PhpOffice\PhpSpreadsheet\IOFactory::createWriter( $spreadsheet, 'Csv' );

					break;
				case 'eeo_ca': //https://calcivilrights.ca.gov/wp-content/uploads/sites/32/2021/01/CA-Pay-Data-Reporting-User-Guide.pdf
					//Get records grouped by Establishment ID, and the combined unique keys of: Job Category, Race/Ethnicity/Sex, Pay Band
					foreach( $records as $record ) {
						$record['median_hourly_rate'] = $record['hourly_rate']; //Duplicate hourly rate to median_hourly_rate so we can handle it below.
						$establishment_records[$record['establishment_id']][] = $record; //First record for each establisment

						if ( !isset( $establishments_data[$record['establishment_id']] ) ) {
							$establishments_data[$record['establishment_id']] = $record; //First record for each establisment
						}
					}

					$spreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
					$sheet = $spreadsheet->getActiveSheet();

					//Add column headers.
					$col = 1;
					$sheet->setCellValue( [ $col++, 1 ], 'Establishment Name*' ); //Company ID - Unique Identifier For Entire Company. Should be 7 characters.
					$sheet->setCellValue( [ $col++, 1 ], 'Address Line 1*' ); //Company Address 1
					$sheet->setCellValue( [ $col++, 1 ], 'Address Line 2' ); //Company Address 2
					$sheet->setCellValue( [ $col++, 1 ], 'City*' ); //City
					$sheet->setCellValue( [ $col++, 1 ], 'State*' ); //State
					$sheet->setCellValue( [ $col++, 1 ], 'ZIP Code*' ); //Zip Code

					$sheet->setCellValue( [ $col++, 1 ], 'NAICS Code*' ); //NAICS
					$sheet->setCellValue( [ $col++, 1 ], 'Major Activity*' ); //Major Activity
					$sheet->setCellValue( [ $col++, 1 ], 'Total Number of Employees at Establishment*' ); //Total Employees in Establishment
					$sheet->setCellValue( [ $col++, 1 ], 'Was a California Pay Data Report filed for this establishment last year?*' ); //Was a california pay data report filed for this establishment last year?
					$sheet->setCellValue( [ $col++, 1 ], 'Was an EEO-1 Report filed for this establishment last year?*' ); //Was an EEO-1 Report filed for this establishment last year?
					$sheet->setCellValue( [ $col++, 1 ], 'Is this establishment the employer\'s headquarters?*' ); //Is this establishment the employers headquarters?

					$sheet->setCellValue( [ $col++, 1 ], 'Job Category*' ); //Job Category
					$sheet->setCellValue( [ $col++, 1 ], 'Race/Ethnicity/Sex*' ); //Race/Ethnicity/Sex
					$sheet->setCellValue( [ $col++, 1 ], 'Pay Band*' ); //Pay Band
					$sheet->setCellValue( [ $col++, 1 ], 'Number of Employees*' ); //Total Employees
					$sheet->setCellValue( [ $col++, 1 ], 'Mean - Hourly Rate*' ); //Mean Hourly Rate
					$sheet->setCellValue( [ $col++, 1 ], 'Median - Hourly Rate*' ); //Median Hourly Rate
					$sheet->setCellValue( [ $col++, 1 ], 'Total Hours*' ); //Total Hours
					$sheet->setCellValue( [ $col++, 1 ], 'Row-Level Clarifying Remarks' ); //Clarifying Remarks.

					$primary_row_num = 2;
					foreach( $establishment_records as $establishment_id => $tmp_establishment_records ) {
						$establishment_data = $establishments_data[$establishment_id];

						//Convert grouped data to spreadsheet columns/matrix.
						$grouped_records = $tmp_establishment_records; //Group-by overwrites this data, so make a copy of it first.
						$group_by = [ 'job_category' => true, 'gender' => true, 'ethnicity' => true, 'annual_salary_band_code' => true, 'total_user' => 'sum', 'total_hours' => 'sum', 'hourly_rate' => 'avg', 'median_hourly_rate' => 'median' ];
						$grouped_data = Group::GroupBy( $grouped_records, $group_by );
						foreach( $grouped_data as $row ) {
							$row['eeo_ca_race_ethnicity_sex'] = $this->getEEOEthnicityGenderMatrixColumn( $row['ethnicity'], $row['gender'] );
							$row['eeo_ca_job_category_id'] = $this->getEEOJobCategoryMatrixRow( $row['job_category'] );

							//If we return errors, then they must exclude employees (ie: generic admin employees) every time they run the report. But because this is all grouped, we don't actually know the individual employees/rows causing the error.
							//if ( $row['eeo_ca_race_ethnicity_sex'] === false ) {
							//	return [
							//			'api_retval'  => false,
							//			'api_details' => [
							//					'code'        => 'VALIDATION',
							//					'description' => TTi18n::getText( 'Race/Ethnicity/Gender is invalid for Ethnicity: %1 Gender: %2', [ $row['ethnicity'] ?? TTi18n::getText( '[Blank]' ), $row['gender'] ?? TTi18n::getText( '[Blank]' ) ] ),
							//			],
							//	];
							//}
							//
							//if ( $row['eeo_ca_job_category_id'] === false ) {
							//	return [
							//			'api_retval'  => false,
							//			'api_details' => [
							//					'code'        => 'VALIDATION',
							//					'description' => TTi18n::getText( 'Job Category is invalid for Title: %1', [ $row['job_category'] ?? TTi18n::getText( '[Blank]' ) ] ),
							//			],
							//	];
							//}
							//
							//if ( (int)$row['annual_salary_band_code'] == 0 ) {
							//	return [
							//			'api_retval'  => false,
							//			'api_details' => [
							//					'code'        => 'VALIDATION',
							//					'description' => TTi18n::getText( 'Annual Salary Band Code is invalid' ),
							//			],
							//	];
							//}

							if ( $row['eeo_ca_race_ethnicity_sex'] !== false && $row['eeo_ca_job_category_id'] !== false && (int)$row['annual_salary_band_code'] !== 0 ) {
								$col = 1;
								$sheet->setCellValue( [ $col++, $primary_row_num ], $establishment_id );                                //Company ID - Unique Identifier For Entire Company. Should be 7 characters.
								$sheet->setCellValue( [ $col++, $primary_row_num ], $establishment_data['establishment_address1'] );    //Company Address 1
								$sheet->setCellValue( [ $col++, $primary_row_num ], $establishment_data['establishment_address2'] );    //Company Address 2
								$sheet->setCellValue( [ $col++, $primary_row_num ], $establishment_data['establishment_city'] );        //City
								$sheet->setCellValue( [ $col++, $primary_row_num ], $establishment_data['establishment_province'] );    //State
								$sheet->setCellValue( [ $col++, $primary_row_num ], $establishment_data['establishment_postal_code'] ); //Zip Code

								$sheet->setCellValue( [ $col++, $primary_row_num ], $establishment_data['establishment_naics_code'] );                           //NAICS
								$sheet->setCellValue( [ $col++, $primary_row_num ], $establishment_data['establishment_major_activity'] );                       //Major Activity
								$sheet->setCellValue( [ $col++, $primary_row_num ], count( $tmp_establishment_records ) );                                       //Total Employees in Establishment
								$sheet->setCellValue( [ $col++, $primary_row_num ], 'Yes' );                                                                     //Was a california pay data report filed for this establishment last year?
								$sheet->setCellValue( [ $col++, $primary_row_num ], 'Yes' );                                                                     //Was an EEO-1 Report filed for this establishment last year?
								$sheet->setCellValue( [ $col++, $primary_row_num ], ( ( $establishment_data['establishment_is_hq'] == true ) ? 'Yes' : 'No' ) ); //Is this establishment the employers headquarters?

								$sheet->setCellValue( [ $col++, $primary_row_num ], (int)$row['eeo_ca_job_category_id'] );              //Job Category
								$sheet->setCellValue( [ $col++, $primary_row_num ], $row['eeo_ca_race_ethnicity_sex'] );                //Race/Ethnicity/Sex
								$sheet->setCellValue( [ $col++, $primary_row_num ], (int)$row['annual_salary_band_code'] );         //Pay Band
								$sheet->setCellValue( [ $col++, $primary_row_num ], (int)$row['total_user'] );                          //Total Employees
								$sheet->setCellValue( [ $col++, $primary_row_num ], TTMath::MoneyRound( $row['hourly_rate'] ) );        //Mean Hourly Rate
								$sheet->setCellValue( [ $col++, $primary_row_num ], TTMath::MoneyRound( $row['median_hourly_rate'] ) ); //Median Hourly Rate
								$sheet->setCellValue( [ $col++, $primary_row_num ], (int)$row['total_hours'] );                         //Total Hours
								$sheet->setCellValue( [ $col++, $primary_row_num ], null );                                             //Clarifying Remarks.

								$primary_row_num++;
							} else {
								Debug::Text( 'Invalid: Job Category: '. $row['job_category'] .' Salary Band Code: '. ( $row['annual_salary_band_code'] ?? 'N/A' ) .' Ethnicity: '. $row['ethnicity'] .' Gender: '. $row['gender'], __FILE__, __LINE__, __METHOD__, 10);
							}
						}
					}

					//Get final data into a string for writing to a file.
					$writer = PhpOffice\PhpSpreadsheet\IOFactory::createWriter( $spreadsheet, 'Csv' );

					break;
				case 'eeo4':
					$tmp_records = [ 'full_time' => [], 'part_time' => [], 'new_hire' => [] ];

					//Filter records for each spreadsheet tab: Full-Time, Other Than Full-Time, New Hires
					$new_hire_start_date = mktime( 0, 0, 0, 7, 1, ( TTDate::getYear( $this->snapshot_end_date ) - 1 ) );
					$new_hire_end_date = mktime( 0, 0, 0, 6, 30, ( TTDate::getYear( $this->snapshot_end_date ) ) );
					Debug::Text( 'New Hire Period: Start: '. TTDate::getDate('DATE', $new_hire_start_date ) .' End: '. TTDate::getDate('DATE', $new_hire_end_date )	, __FILE__, __LINE__, __METHOD__, 10);
					foreach( $records as $record ) {
						if ( isset( $record['employment_status'] ) && stripos( $record['employment_status'], 'p' ) !== false ) { //Part-Time
							$tmp_records['part_time'][] = $record;
						} else {
							$tmp_records['full_time'][] = $record;

							//Filers must provide a breakout of new permanent
							//full-time hires during the fiscal year (i.e., July 1st, 2020 – June 30th, 2021). Data for such new
							//hires covers the entire fiscal year which ends on June 30th of the reporting year (i.e., 2021).
							//The relevant time period for the 2021 reporting year would be July 1st, 2020 – June 30th, 2021.

							//Please note that the data for new hires only includes permanent full-time new hires. Parttime/temporary new hires would not be included in the “New Hires during Fiscal Year”
							//sub-section under section 5 D. Employment Data as of June 30th. However, any new
							//permanent full-time hires covered by the payroll period which includes June 30th, 2021,
							//should also be included in the “Full-Time Employees” sub-section under section 5 D. Employment Data as of June 30th.
							if ( isset( $record['hire_date'] ) && TTDate::isTimeOverLap( $new_hire_start_date, $new_hire_end_date, $record['hire_date'], $record['hire_date'] ) == true ) {
								$tmp_records['new_hire'][] = $record;
							}
						}

					}
					Debug::Text( 'Record Counts: Full-Time: '. count( $tmp_records['full_time'] ) .' Part-Time: '. count( $tmp_records['part_time'] ) .' New Hire: '. count( $tmp_records['new_hire'] ), __FILE__, __LINE__, __METHOD__, 10);

					$template_file = $this->getTemplateDirectory() . DIRECTORY_SEPARATOR . 'eeo4.xlsx';

					$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
					$spreadsheet = $reader->load( $template_file );

					$matrix_row_offset = 1;

					//Full-Time
					if ( isset( $tmp_records['full_time'] ) && !empty( $tmp_records['full_time'] ) ) {
						$spreadsheet->setActiveSheetIndex( 1 ); //Full-Time
						$sheet = $spreadsheet->getActiveSheet();

						$matrix_column_offset = 3;

						$total_job_categories = count( $this->getEEOJobCategoryOptions() );
						$total_salary_bands = count( $this->getOptions( 'eeo4_salary_band' ) );

						//Convert grouped data to spreadsheet columns/matrix.
						$grouped_records = $tmp_records['full_time']; //Group-by overwrites this data, so make a copy of it first.
						$group_by = [ 'function' => true, 'job_category' => true, 'annual_salary_band' => true, 'gender' => true, 'ethnicity' => true, 'total_user' => 'sum' ];
						$grouped_data = Group::GroupBy( $grouped_records, $group_by );
						foreach( $grouped_data as $row ) {
							$function_matrix_row = $this->getEEOFunctionMatrixRow( $row['function'] );
							$job_category_matrix_row = $this->getEEOJobCategoryMatrixRow( $row['job_category'] );
							$salary_band_matrix_row = $this->getEEOSalaryBandMatrixRow( $row['annual_salary_band'] );
							$ethnicity_gender_maxtrix_row = $this->getEEOEthnicityGenderMatrixColumn( $row['ethnicity'], $row['gender'] );

							if ( $function_matrix_row !== false && $job_category_matrix_row !== false && $salary_band_matrix_row !== false && $ethnicity_gender_maxtrix_row !== false ) {
								$function_modifier = ( ( $function_matrix_row - 1 ) * $total_job_categories * $total_salary_bands );
								$job_category_modifier = ( ( $job_category_matrix_row - 1 ) * $total_salary_bands );
								$salary_band_modifier = $salary_band_matrix_row;

								$row_num = ( $matrix_row_offset + $function_modifier + $job_category_modifier + $salary_band_modifier );
								$column_num = ( $matrix_column_offset + $ethnicity_gender_maxtrix_row );

								//Debug::Arr( $row, 'Column Num: '. $column_num .' Row Num: '. $row_num .' Total Users: '. $row['total_user'], __FILE__, __LINE__, __METHOD__, 10);
								$sheet->setCellValue( [ $column_num, $row_num ], $row['total_user'] );
							} else {
								Debug::Text( 'Invalid: Full-Time Function: '. $row['function'] .' Job Category: '. $row['job_category'] .' Salary Band: '. $row['annual_salary_band'] .' Ethnicity: '. $row['ethnicity'] .' Gender: '. $row['gender'], __FILE__, __LINE__, __METHOD__, 10);
							}
						}
					}

					//Part-time
					if ( isset( $tmp_records['part_time'] ) && !empty( $tmp_records['part_time'] ) ) {
						$spreadsheet->setActiveSheetIndex( 2 ); //Other than Full-Time
						$sheet = $spreadsheet->getActiveSheet();

						$matrix_column_offset = 2;

						$total_job_categories = count( $this->getEEOJobCategoryOptions() );

						//Convert grouped data to spreadsheet columns/matrix.
						$grouped_records = $tmp_records['part_time']; //Group-by overwrites this data, so make a copy of it first.
						$group_by = [ 'function' => true, 'job_category' => true, 'gender' => true, 'ethnicity' => true, 'total_user' => 'sum' ];
						$grouped_data = Group::GroupBy( $grouped_records, $group_by );
						foreach( $grouped_data as $row ) {
							$function_matrix_row = $this->getEEOFunctionMatrixRow( $row['function'] );
							$job_category_matrix_row = $this->getEEOJobCategoryMatrixRow( $row['job_category'] );
							$ethnicity_gender_maxtrix_row = $this->getEEOEthnicityGenderMatrixColumn( $row['ethnicity'], $row['gender'] );

							if ( $function_matrix_row !== false && $job_category_matrix_row !== false && $ethnicity_gender_maxtrix_row !== false ) {
								$function_modifier = ( ( $function_matrix_row - 1 ) * $total_job_categories );
								$job_category_modifier = $job_category_matrix_row;

								$row_num = ( $matrix_row_offset + $function_modifier + $job_category_modifier );
								$column_num = ( $matrix_column_offset + $ethnicity_gender_maxtrix_row );

								//Debug::Arr( $row, 'Column Num: '. $column_num .' Row Num: '. $row_num .' Total Users: '. $row['total_user'], __FILE__, __LINE__, __METHOD__, 10);
								$sheet->setCellValue( [ $column_num, $row_num ], $row['total_user'] );
							} else {
								Debug::Text( 'Invalid: Full-Time Function: '. $row['function'] .' Job Category: '. $row['job_category'] .' Ethnicity: '. $row['ethnicity'] .' Gender: '. $row['gender'], __FILE__, __LINE__, __METHOD__, 10);
							}
						}
					}

					if ( isset( $tmp_records['new_hire'] ) && !empty( $tmp_records['new_hire'] ) ) {
						$spreadsheet->setActiveSheetIndex( 3 ); //New Hires During Fiscal Year
						$sheet = $spreadsheet->getActiveSheet();

						$matrix_column_offset = 2;

						$total_job_categories = count( $this->getEEOJobCategoryOptions() );

						//Convert grouped data to spreadsheet columns/matrix.
						$grouped_records = $tmp_records['new_hire']; //Group-by overwrites this data, so make a copy of it first.
						$group_by = [ 'function' => true, 'job_category' => true, 'gender' => true, 'ethnicity' => true, 'total_user' => 'sum' ];
						$grouped_data = Group::GroupBy( $grouped_records, $group_by );
						foreach( $grouped_data as $row ) {
							$function_matrix_row = $this->getEEOFunctionMatrixRow( $row['function'] );
							$job_category_matrix_row = $this->getEEOJobCategoryMatrixRow( $row['job_category'] );
							$ethnicity_gender_maxtrix_row = $this->getEEOEthnicityGenderMatrixColumn( $row['ethnicity'], $row['gender'] );

							if ( $function_matrix_row !== false && $job_category_matrix_row !== false && $ethnicity_gender_maxtrix_row !== false ) {
								$function_modifier = ( ( $function_matrix_row - 1 ) * $total_job_categories );
								$job_category_modifier = $job_category_matrix_row;

								$row_num = ( $matrix_row_offset + $function_modifier + $job_category_modifier );
								$column_num = ( $matrix_column_offset + $ethnicity_gender_maxtrix_row );

								//Debug::Arr( $row, 'Column Num: '. $column_num .' Row Num: '. $row_num .' Total Users: '. $row['total_user'], __FILE__, __LINE__, __METHOD__, 10);
								$sheet->setCellValue( [ $column_num, $row_num ], $row['total_user'] );
							} else {
								Debug::Text( 'Invalid: Full-Time Function: '. $row['function'] .' Job Category: '. $row['job_category'] .' Ethnicity: '. $row['ethnicity'] .' Gender: '. $row['gender'], __FILE__, __LINE__, __METHOD__, 10);
							}
						}
					}

					//Get final data into a string for writing to a file.
					$writer = PhpOffice\PhpSpreadsheet\IOFactory::createWriter( $spreadsheet, 'Xlsx' );
					break;
				default:
					break;

			}

			$fp = fopen( 'php://memory', 'rw' );
			$writer->save( $fp );
			rewind( $fp );

			$retval = '';
			while (!feof($fp)) {
				$retval .= fread($fp, 8000);
			}
		}

		if ( isset( $retval ) ) {
			return $retval;
		}

		return false;
	}
}

?>