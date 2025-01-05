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
class GovernmentForms_US_941WorkSheet1 extends GovernmentForms_US {
	public $pdf_template = '941worksheet1.pdf';

	public $credit_percent = 0.50; //Multiplier for Line 1b
	public $employer_medicare_rate = 0.0145; //1.45%
	public $employer_social_security_rate = 0.062; //6.2%

	public function getTemplateSchema( $name = null ) {
		$template_schema = [
			'l1a' => [
					'page'          => 1,
					'template_page' => 1,
					'coordinates'   => [
							'x'      => 446,
							'y'      => 116,
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
							'y'      => 126,
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
							'y'      => 137,
							'h'      => 10,
							'w'      => 45,
							'halign' => 'C',
					],
			],
			'l1d' => [
					'page'          => 1,
					'template_page' => 1,
					'function'      => [ 'calc' => 'calcL1D', 'draw' => [ 'drawNormal' ] ],
					'coordinates'   => [
							'x'      => 446,
							'y'      => 148,
							'h'      => 10,
							'w'      => 45,
							'halign' => 'C',
					],
			],
			'l1e' => [
					'page'          => 1,
					'template_page' => 1,
					'coordinates'   => [
							'x'      => 446,
							'y'      => 172,
							'h'      => 10,
							'w'      => 45,
							'halign' => 'C',
					],
			],
			'l1f' => [
					'page'          => 1,
					'template_page' => 1,
					'function'      => [ 'calc' => 'calcL1F', 'draw' => [ 'drawNormal' ] ],
					'coordinates'   => [
							'x'      => 446,
							'y'      => 184,
							'h'      => 10,
							'w'      => 45,
							'halign' => 'C',
					],
			],
			'l1g' => [
					'page'          => 1,
					'template_page' => 1,
					'coordinates'   => [
							'x'      => 446,
							'y'      => 200,
							'h'      => 10,
							'w'      => 45,
							'halign' => 'C',
					],
			],
			'l1h' => [
					'page'          => 1,
					'template_page' => 1,
					'function'      => [ 'calc' => 'calcL1H', 'draw' => [ 'drawNormal' ] ],
					'coordinates'   => [
							'x'      => 516,
							'y'      => 211,
							'h'      => 10,
							'w'      => 45,
							'halign' => 'C',
					],
			],
			'l1i' => [
					'page'          => 1,
					'template_page' => 1,
					'coordinates'   => [
							'x'      => 446,
							'y'      => 222,
							'h'      => 10,
							'w'      => 45,
							'halign' => 'C',
					],
			],
			'l1j' => [
					'page'          => 1,
					'template_page' => 1,
					'coordinates'   => [
							'x'      => 446,
							'y'      => 234,
							'h'      => 10,
							'w'      => 45,
							'halign' => 'C',
					],
			],
			'l1ji' => [
					'page'          => 1,
					'template_page' => 1,
					'coordinates'   => [
							'x'      => 446,
							'y'      => 245,
							'h'      => 10,
							'w'      => 45,
							'halign' => 'C',
					],
			],
			'l1k' => [
					'page'          => 1,
					'template_page' => 1,
					'function'      => [ 'calc' => 'calcL1K', 'draw' => [ 'drawNormal' ] ],
					'coordinates'   => [
							'x'      => 516,
							'y'      => 262,
							'h'      => 10,
							'w'      => 45,
							'halign' => 'C',
					],
			],
			'l1l' => [
					'page'          => 1,
					'template_page' => 1,
					'function'      => [ 'calc' => 'calcL1L', 'draw' => [ 'drawNormal' ] ],
					'coordinates'   => [
							'x'      => 516,
							'y'      => 272,
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
							'y'      => 300,
							'h'      => 10,
							'w'      => 45,
							'halign' => 'C',
					],
			],
			'l2ai' => [
					'page'          => 1,
					'template_page' => 1,
					'coordinates'   => [
							'x'      => 446,
							'y'      => 324,
							'h'      => 10,
							'w'      => 45,
							'halign' => 'C',
					],
			],
			'l2aii' => [
					'page'          => 1,
					'template_page' => 1,
					'function'      => [ 'calc' => 'calcL2AII', 'draw' => [ 'drawNormal' ] ],
					'coordinates'   => [
							'x'      => 446,
							'y'      => 336,
							'h'      => 10,
							'w'      => 45,
							'halign' => 'C',
					],
			],
			'l2aiii' => [
					'page'          => 1,
					'template_page' => 1,
					'coordinates'   => [
							'x'      => 446,
							'y'      => 351,
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
							'y'      => 368,
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
							'y'      => 384,
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
							'y'      => 396,
							'h'      => 10,
							'w'      => 45,
							'halign' => 'C',
					],
			],

			'l2e' => [
					'page'          => 1,
					'template_page' => 1,
					'coordinates'   => [
							'x'      => 446,
							'y'      => 407,
							'h'      => 10,
							'w'      => 45,
							'halign' => 'C',
					],
			],
			'l2ei' => [
					'page'          => 1,
					'template_page' => 1,
					'coordinates'   => [
							'x'      => 446,
							'y'      => 431,
							'h'      => 10,
							'w'      => 45,
							'halign' => 'C',
					],
			],
			'l2eii' => [
					'page'          => 1,
					'template_page' => 1,
					'function'      => [ 'calc' => 'calcL2EII', 'draw' => [ 'drawNormal' ] ],
					'coordinates'   => [
							'x'      => 446,
							'y'      => 442,
							'h'      => 10,
							'w'      => 45,
							'halign' => 'C',
					],
			],
			'l2eiii' => [
					'page'          => 1,
					'template_page' => 1,
					'coordinates'   => [
							'x'      => 446,
							'y'      => 458,
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
							'y'      => 476,
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
							'y'      => 491,
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
							'y'      => 503,
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
							'y'      => 514,
							'h'      => 10,
							'w'      => 45,
							'halign' => 'C',
					],
			],
			'l2j' => [
					'page'          => 1,
					'template_page' => 1,
					'function'      => [ 'calc' => 'calcL2J', 'draw' => [ 'drawNormal' ] ],
					'coordinates'   => [
							'x'      => 516,
							'y'      => 537,
							'h'      => 10,
							'w'      => 45,
							'halign' => 'C',
					],
			],
			'l2k' => [
					'page'          => 1,
					'template_page' => 1,
					'function'      => [ 'calc' => 'calcL2K', 'draw' => [ 'drawNormal' ] ],
					'coordinates'   => [
							'x'      => 516,
							'y'      => 561,
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
		$this->l1c = $this->MoneyFormat( TTMath::add( $this->l1a, $this->l1b ) );

		return $this->l1c;
	}

	function calcL1D( $value = null, $schema = null ) {
		$this->l1d = $this->MoneyFormat( TTMath::mul( $this->l1c, $this->credit_percent ) );

		return $this->l1d;
	}

	function calcL1F( $value = null, $schema = null ) {
		$this->l1f = $this->MoneyFormat( TTMath::sub( $this->l1d, $this->l1e ) );

		return $this->l1f;
	}

	function calcL1H( $value = null, $schema = null ) {
		$this->l1h = $this->MoneyFormat( TTMath::add( $this->l1f, $this->l1g ) );

		return $this->l1h;
	}

	function calcL1K( $value = null, $schema = null ) {
		$this->l1k = $this->MoneyFormat( TTMath::add( $this->l1i, TTMath::add( $this->l1j, $this->l1ji ) ) );

		return $this->l1k;
	}

	function calcL1L( $value = null, $schema = null ) {
		$this->l1l = $this->MoneyFormat( TTMath::sub( $this->l1h, $this->l1k ) );

		return $this->l1l;
	}

	function calcL2AII( $value = null, $schema = null ) {
		$this->l2aii = $this->MoneyFormat( TTMath::add( $this->l2a, $this->l2ai ) );

		return $this->l2aii;
	}

	function calcL2C( $value = null, $schema = null ) {
		$this->l2c = $this->MoneyFormat( TTMath::mul( $this->l2aii, $this->employer_medicare_rate ) );

		return $this->l2c;
	}

	function calcL2D( $value = null, $schema = null ) {
		$this->l2d = $this->MoneyFormat( TTMath::add( $this->l2aii, TTMath::add( $this->l2aiii, TTMath::add( $this->l2b, $this->l2c ) ) ) );

		return $this->l2d;
	}

	function calcL2EII( $value = null, $schema = null ) {
		$this->l2eii = $this->MoneyFormat( TTMath::add( $this->l2e, $this->l2ei ) );

		return $this->l2eii;
	}

	function calcL2G( $value = null, $schema = null ) {
		$this->l2g = $this->MoneyFormat( TTMath::mul( $this->l2eii, $this->employer_medicare_rate ) );

		return $this->l2g;
	}

	function calcL2H( $value = null, $schema = null ) {
		$this->l2h = $this->MoneyFormat( TTMath::add( $this->l2eii, TTMath::add( $this->l2eiii, TTMath::add( $this->l2f, $this->l2g ) ) ) );

		return $this->l2h;
	}

	function calcL2I( $value = null, $schema = null ) {
		$this->l2i = $this->MoneyFormat( TTMath::add( $this->l2d, $this->l2h ) );

		return $this->l2i;
	}

	function calcL2J( $value = null, $schema = null ) {
		$this->l2j = min( $this->l1l, $this->l2i );

		return $this->l2j;
	}

	function calcL2K( $value = null, $schema = null ) {
		$this->l2k = $this->MoneyFormat( TTMath::sub( $this->l2i, $this->l2j ) );

		return $this->l2k;
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