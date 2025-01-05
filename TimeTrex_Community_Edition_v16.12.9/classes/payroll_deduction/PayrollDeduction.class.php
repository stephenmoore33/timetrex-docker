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
 * @package PayrollDeduction
 */
class PayrollDeduction {
	var $obj = null;
	var $data = null;

	protected $version = '1.0.64';
	protected $data_version = '20240131';

	function __construct( $country, $province, $district = null ) {
		if ( $country != '' && ctype_alnum( $country ) == false ) {
			Debug::Text( 'WARNING: Country contains invalid characters: ' . $country, __FILE__, __LINE__, __METHOD__, 10 );
			return false;
		}

		if ( $province != '' && ctype_alnum( $province ) == false ) {
			Debug::Text( 'WARNING: Province contains invalid characters: ' . $province, __FILE__, __LINE__, __METHOD__, 10 );
			return false;
		}

		if ( $district != '' && ctype_alnum( $district ) == false ) {
			Debug::Text( 'WARNING: District contains invalid characters: ' . $district, __FILE__, __LINE__, __METHOD__, 10 );
			return false;
		}

		$this->setCountry( $country );
		$this->setProvince( $province );
		$this->setDistrict( $district );

		$base_path = Environment::getBasePath();
		$base_file_name = $base_path . '/classes/payroll_deduction/PayrollDeduction_Base.class.php';
		$province_file_name = $base_path . '/classes/payroll_deduction/' . $this->getCountry() . '/' . $this->getProvince() . '.class.php';
		$district_file_name = $base_path . '/classes/payroll_deduction/' . $this->getCountry() . '/' . $this->getProvince() . '_' . $this->getDistrict() . '.class.php';
		$country_file_name = $base_path . '/classes/payroll_deduction/' . $this->getCountry() . '.class.php';
		$data_file_name = $base_path . '/classes/payroll_deduction/' . $this->getCountry() . '/Data.class.php';

		if ( $this->getDistrict() != '' && $this->getDistrict() != '00' ) {
			$class_name = 'PayrollDeduction_' . $this->getCountry() . '_' . $this->getProvince() . '_' . $this->getDistrict();
		} else if ( $this->getProvince() != '' ) {
			$class_name = 'PayrollDeduction_' . $this->getCountry() . '_' . $this->getProvince();
		} else {
			$class_name = 'PayrollDeduction_' . $this->getCountry();
		}

		//Debug::text('Country: '. $country_file_name .' Province: '. $province_file_name .' District: '. $district_file_name .' Class: '. $class_name, __FILE__, __LINE__, __METHOD__, 10);
		if ( ( file_exists( $country_file_name ) || ( $this->getProvince() != '' && file_exists( $province_file_name ) ) || ( $this->getDistrict() != '' && file_exists( $district_file_name ) ) ) && file_exists( $data_file_name ) ) {
			//Debug::text('Country File Exists: '. $country_file_name .' Province File Name: '. $province_file_name .' Data File: '. $data_file_name, __FILE__, __LINE__, __METHOD__, 10);

			include_once( $base_file_name );
			include_once( $data_file_name );

			if ( file_exists( $country_file_name ) ) {
				include_once( $country_file_name );
			}
			if ( $this->getProvince() != '' && file_exists( $province_file_name ) ) {
				include_once( $province_file_name );
			}
			if ( $this->getDistrict() != '' && file_exists( $district_file_name ) ) {
				include_once( $district_file_name );
			}

			if ( class_exists( $class_name ) ) {
				$this->obj = new $class_name;
				$this->obj->setCountry( $this->getCountry() );
				$this->obj->setProvince( $this->getProvince() );
				$this->obj->setDistrict( $this->getDistrict() );

				return true;
			} else {
				return false;
			}
		} else {
			Debug::text( 'File DOES NOT Exists Country File Name: ' . $country_file_name . ' Province File: ' . $province_file_name, __FILE__, __LINE__, __METHOD__, 10 );
		}

		return false;
	}

	function getVersion() {
		return $this->version;
	}

	function getDataVersion() {
		return $this->data_version;
	}

	private function getObject() {
		if ( is_object( $this->obj ) ) {
			return $this->obj;
		}

		return false;
	}

	private function setCountry( $country ) {
		$this->data['country'] = strtoupper( substr( trim( (string)$country ), 0, 2 ) ); //Sanitize country to at least be close to a country code.

		return true;
	}

	function getCountry() {
		if ( isset( $this->data['country'] ) ) {
			return $this->data['country'];
		}

		return false;
	}

	private function setProvince( $province ) {
		$this->data['province'] = strtoupper( substr( trim( (string)$province ), 0, 2 ) ); //Sanitize province to at least be close to a country code.

		return true;
	}

	function getProvince() {
		if ( isset( $this->data['province'] ) ) {
			return $this->data['province'];
		}

		return false;
	}

	private function setDistrict( $district ) {
		$this->data['district'] = strtoupper( substr( trim( (string)$district ), 0, 15 ) ); //Sanitize district to at least be close to a district code.

		return true;
	}

	function getDistrict() {
		if ( isset( $this->data['district'] ) ) {
			return $this->data['district'];
		}

		return false;
	}

	function __call( $function_name, $args = [] ) {
		if ( $this->getObject() !== false ) {
			//Debug::text('Calling Sub-Class Function: '. $function_name, __FILE__, __LINE__, __METHOD__, 10);
			if ( is_callable( [ $this->getObject(), $function_name ] ) ) {
				$return = call_user_func_array( [ $this->getObject(), $function_name ], $args );

				return $return;
			}
		}

		Debug::text( 'Sub-Class Function Call FAILED!:' . $function_name, __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}
}

?>
