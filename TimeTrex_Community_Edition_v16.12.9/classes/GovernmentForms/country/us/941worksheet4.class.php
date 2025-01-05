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
class GovernmentForms_US_941WorkSheet4 extends GovernmentForms_US {
	public $pdf_template = '941worksheet4.pdf';

	public $credit_percent = 0.50; //Multiplier for Line 1d
	public $retention_credit_percent = 0.70; //Multiplier for Line 3d

	public function getTemplateSchema( $name = null ) {
		$template_schema = [
				'l1a' => [
						'page'          => 1,
						'template_page' => 1,
						'coordinates'   => [
								'x'      => 516,
								'y'      => 142,
								'h'      => 10,
								'w'      => 45,
								'halign' => 'C',
						],
				],
				'l1b' => [
						'page'          => 1,
						'template_page' => 1,
						'coordinates'   => [
								'x'      => 446,
								'y'      => 154,
								'h'      => 10,
								'w'      => 45,
								'halign' => 'C',
						],
				],
				'l1c' => [
						'page'          => 1,
						'template_page' => 1,
						'function'      => [ 'calc' => 'calcL1C', 'draw' => [ 'drawNormal' ] ],
						'coordinates'   => [
								'x'      => 446,
								'y'      => 165,
								'h'      => 10,
								'w'      => 45,
								'halign' => 'C',
						],
				],
				'l1d' => [
						'page'          => 1,
						'template_page' => 1,
						'coordinates'   => [
								'x'      => 446,
								'y'      => 188,
								'h'      => 10,
								'w'      => 45,
								'halign' => 'C',
						],
				],
				'l1e' => [
						'page'          => 1,
						'template_page' => 1,
						'function'      => [ 'calc' => 'calcL1E', 'draw' => [ 'drawNormal' ] ],
						'coordinates'   => [
								'x'      => 446,
								'y'      => 200,
								'h'      => 10,
								'w'      => 45,
								'halign' => 'C',
						],
				],
				'l1f' => [
						'page'          => 1,
						'template_page' => 1,
						'coordinates'   => [
								'x'      => 446,
								'y'      => 216,
								'h'      => 10,
								'w'      => 45,
								'halign' => 'C',
						],
				],
				'l1g' => [
						'page'          => 1,
						'template_page' => 1,
						'function'      => [ 'calc' => 'calcL1G', 'draw' => [ 'drawNormal' ] ],
						'coordinates'   => [
								'x'      => 516,
								'y'      => 227,
								'h'      => 10,
								'w'      => 45,
								'halign' => 'C',
						],
				],

				'l2a' => [
						'page'          => 1,
						'template_page' => 1,
						'coordinates'   => [
								'x'      => 446,
								'y'      => 277,
								'h'      => 10,
								'w'      => 45,
								'halign' => 'C',
						],
				],
				'l2b' => [
						'page'          => 1,
						'template_page' => 1,
						'coordinates'   => [
								'x'      => 446,
								'y'      => 293,
								'h'      => 10,
								'w'      => 45,
								'halign' => 'C',
						],
				],
				'l2c' => [
						'page'          => 1,
						'template_page' => 1,
						'function'      => [ 'calc' => 'calcL2C', 'draw' => [ 'drawNormal' ] ],
						'coordinates'   => [
								'x'      => 446,
								'y'      => 305,
								'h'      => 10,
								'w'      => 45,
								'halign' => 'C',
						],
				],
				'l2d' => [
						'page'          => 1,
						'template_page' => 1,
						'function'      => [ 'calc' => 'calcL2D', 'draw' => [ 'drawNormal' ] ],
						'coordinates'   => [
								'x'      => 516,
								'y'      => 328,
								'h'      => 10,
								'w'      => 45,
								'halign' => 'C',
						],
				],
				'l2e' => [
						'page'          => 1,
						'template_page' => 1,
						'function'      => [ 'calc' => 'calcL2E', 'draw' => [ 'drawNormal' ] ],
						'coordinates'   => [
								'x'      => 446,
								'y'      => 344,
								'h'      => 10,
								'w'      => 45,
								'halign' => 'C',
						],
				],
				'l2f' => [
						'page'          => 1,
						'template_page' => 1,
						'coordinates'   => [
								'x'      => 446,
								'y'      => 369,
								'h'      => 10,
								'w'      => 45,
								'halign' => 'C',
						],
				],
				'l2g' => [
						'page'          => 1,
						'template_page' => 1,
						'function'      => [ 'calc' => 'calcL2G', 'draw' => [ 'drawNormal' ] ],
						'coordinates'   => [
								'x'      => 446,
								'y'      => 379,
								'h'      => 10,
								'w'      => 45,
								'halign' => 'C',
						],
				],
				'l2h' => [
						'page'          => 1,
						'template_page' => 1,
						'function'      => [ 'calc' => 'calcL2H', 'draw' => [ 'drawNormal' ] ],
						'coordinates'   => [
								'x'      => 516,
								'y'      => 395,
								'h'      => 10,
								'w'      => 45,
								'halign' => 'C',
						],
				],
				'l2i' => [
						'page'          => 1,
						'template_page' => 1,
						'function'      => [ 'calc' => 'calcL2I', 'draw' => [ 'drawNormal' ] ],
						'coordinates'   => [
								'x'      => 516,
								'y'      => 412,
								'h'      => 10,
								'w'      => 45,
								'halign' => 'C',
						],
				],
		];

		if ( isset( $template_schema[$name] ) ) {
			return $name;
		} else {
			return $template_schema;
		}
	}

	function calcL1C( $value = null, $schema = null ) {
		$this->l1c = $this->MoneyFormat( TTMath::mul( $this->l1b, $this->credit_percent ) );
		if ( $this->l1c == 0 ) {
			$this->l1c = false;
		}

		return $this->l1c;
	}

	function calcL1E( $value = null, $schema = null ) {
		$this->l1e = $this->MoneyFormat( TTMath::sub( $this->l1c, $this->l1d ) );
		if ( $this->l1e == 0 ) {
			$this->l1e = false;
		}

		return $this->l1e;
	}

	function calcL1G( $value = null, $schema = null ) {
		$this->l1g = $this->MoneyFormat( TTMath::add( $this->l1e, $this->l1f ) );
		if ( $this->l1g == 0 ) {
			$this->l1g = false;
		}
		return $this->l1g;
	}

	function calcL2C( $value = null, $schema = null ) {
		$this->l2c = $this->MoneyFormat( TTMath::add( $this->l2a, $this->l2b ) );

		return $this->l2c;
	}

	function calcL2D( $value = null, $schema = null ) {
		$this->l2d = $this->MoneyFormat( TTMath::mul( $this->l2c, $this->retention_credit_percent ) );

		return $this->l2d;
	}

	function calcL2E( $value = null, $schema = null ) {
		if ( $this->l1a > 0 ) {
			$this->l2e = $this->l1a;
		} elseif ( $this->l1g > 0 ) {
			$this->l2e = $this->l1g;
		}

		return $this->l2e;
	}

	function calcL2G( $value = null, $schema = null ) {
		$this->l2g = $this->MoneyFormat( TTMath::sub( $this->l2e, $this->l2f ) );

		return $this->l2g;
	}

	function calcL2H( $value = null, $schema = null ) {
		$this->l2h = min( $this->l2d, $this->l2g );

		return $this->l2h;
	}

	function calcL2I( $value = null, $schema = null ) {
		$this->l2i = $this->MoneyFormat( TTMath::sub( $this->l2d, $this->l2h ) );

		return $this->l2i;
	}

	function _outputPDF( $type ) {
		//Initialize PDF with template.
		$pdf = $this->getPDFObject();

		if ( $this->getShowBackground() == true ) {
			$pdf->setSourceFile( $this->getTemplateDirectory() . DIRECTORY_SEPARATOR . $this->pdf_template );

			$this->template_index[1] = $pdf->ImportPage( 1 );
		}

		if ( $this->year == '' ) {
			$this->year = $this->getYear();
		}

		//Get location map, start looping over each variable and drawing
		$template_schema = $this->getTemplateSchema();
		if ( is_array( $template_schema ) ) {

			$template_page = null;

			foreach ( $template_schema as $field => $schema ) {
				$this->Draw( $this->$field, $schema );
			}
		}

		return true;
	}
}

?>