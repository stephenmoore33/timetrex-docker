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
class GovernmentForms_US_941WorkSheet3 extends GovernmentForms_US {
	public $pdf_template = '941worksheet3.pdf';

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
							'y'      => 108,
							'h'      => 10,
							'w'      => 45,
							'halign' => 'C',
					],
			],
			'l1b' => [
					'page'          => 1,
					'template_page' => 1,
					'function'      => [ 'calc' => 'calcL1B', 'draw' => [ 'drawNormal' ] ],
					'coordinates'   => [
							'x'      => 446,
							'y'      => 119,
							'h'      => 10,
							'w'      => 45,
							'halign' => 'C',
					],
			],
			'l1c' => [
					'page'          => 1,
					'template_page' => 1,
					'coordinates'   => [
							'x'      => 446,
							'y'      => 143,
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
							'y'      => 154,
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
							'y'      => 170,
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
							'x'      => 518,
							'y'      => 181,
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
							'y'      => 214,
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
							'y'      => 246,
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
							'y'      => 257,
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
							'y'      => 280,
							'h'      => 10,
							'w'      => 45,
							'halign' => 'C',
					],
			],
			'l2aiv' => [
					'page'          => 1,
					'template_page' => 1,
					'function'      => [ 'calc' => 'calcL2AIV', 'draw' => [ 'drawNormal' ] ],
					'coordinates'   => [
							'x'      => 446,
							'y'      => 292,
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
							'y'      => 308,
							'h'      => 10,
							'w'      => 45,
							'halign' => 'C',
					],
			],
			'l2c' => [
					'page'          => 1,
					'template_page' => 1,
					'coordinates'   => [
							'x'      => 446,
							'y'      => 325,
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
							'x'      => 446,
							'y'      => 342,
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
							'y'      => 357,
							'h'      => 10,
							'w'      => 45,
							'halign' => 'C',
					],
			],
			'l2f' => [
					'page'          => 1,
					'template_page' => 1,
					'function'      => [ 'calc' => 'calcL2F', 'draw' => [ 'drawNormal' ] ],
					'coordinates'   => [
							'x'      => 518,
							'y'      => 369,
							'h'      => 10,
							'w'      => 45,
							'halign' => 'C',
					],
			],
			'l2g' => [
					'page'          => 1,
					'template_page' => 1,
					'coordinates'   => [
							'x'      => 446,
							'y'      => 385,
							'h'      => 10,
							'w'      => 45,
							'halign' => 'C',
					],
			],
			'l2gi' => [
					'page'          => 1,
					'template_page' => 1,
					'coordinates'   => [
							'x'      => 446,
							'y'      => 417,
							'h'      => 10,
							'w'      => 45,
							'halign' => 'C',
					],
			],
			'l2gii' => [
					'page'          => 1,
					'template_page' => 1,
					'function'      => [ 'calc' => 'calcL2GII', 'draw' => [ 'drawNormal' ] ],
					'coordinates'   => [
							'x'      => 446,
							'y'      => 427,
							'h'      => 10,
							'w'      => 45,
							'halign' => 'C',
					],
			],
			'l2giii' => [
					'page'          => 1,
					'template_page' => 1,
					'coordinates'   => [
							'x'      => 446,
							'y'      => 452,
							'h'      => 10,
							'w'      => 45,
							'halign' => 'C',
					],
			],
			'l2giv' => [
					'page'          => 1,
					'template_page' => 1,
					'function'      => [ 'calc' => 'calcL2GIV', 'draw' => [ 'drawNormal' ] ],
					'coordinates'   => [
							'x'      => 446,
							'y'      => 462,
							'h'      => 10,
							'w'      => 45,
							'halign' => 'C',
					],
			],
			'l2h' => [
					'page'          => 1,
					'template_page' => 1,
					'coordinates'   => [
							'x'      => 446,
							'y'      => 479,
							'h'      => 10,
							'w'      => 45,
							'halign' => 'C',
					],
			],
			'l2i' => [
					'page'          => 1,
					'template_page' => 1,
					'coordinates'   => [
							'x'      => 446,
							'y'      => 495,
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
							'x'      => 446,
							'y'      => 512,
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
							'x'      => 446,
							'y'      => 529,
							'h'      => 10,
							'w'      => 45,
							'halign' => 'C',
					],
			],
			'l2l' => [
					'page'          => 1,
					'template_page' => 1,
					'function'      => [ 'calc' => 'calcL2L', 'draw' => [ 'drawNormal' ] ],
					'coordinates'   => [
							'x'      => 518,
							'y'      => 539,
							'h'      => 10,
							'w'      => 45,
							'halign' => 'C',
					],
			],
			'l2m' => [
					'page'          => 1,
					'template_page' => 1,
					'function'      => [ 'calc' => 'calcL2M', 'draw' => [ 'drawNormal' ] ],
					'coordinates'   => [
							'x'      => 518,
							'y'      => 551,
							'h'      => 10,
							'w'      => 45,
							'halign' => 'C',
					],
			],
			'l2n' => [
					'page'          => 1,
					'template_page' => 1,
					'coordinates'   => [
							'x'      => 446,
							'y'      => 582,
							'h'      => 10,
							'w'      => 45,
							'halign' => 'C',
					],
			],
			'l2o' => [
					'page'          => 1,
					'template_page' => 1,
					'coordinates'   => [
							'x'      => 446,
							'y'      => 605,
							'h'      => 10,
							'w'      => 45,
							'halign' => 'C',
					],
			],
			'l2p' => [
					'page'          => 1,
					'template_page' => 1,
					'function'      => [ 'calc' => 'calcL2P', 'draw' => [ 'drawNormal' ] ],
					'coordinates'   => [
							'x'      => 446,
							'y'      => 617,
							'h'      => 10,
							'w'      => 45,
							'halign' => 'C',
					],
			],
			'l2q' => [
					'page'          => 1,
					'template_page' => 1,
					'function'      => [ 'calc' => 'calcL2Q', 'draw' => [ 'drawNormal' ] ],
					'coordinates'   => [
							'x'      => 518,
							'y'      => 633,
							'h'      => 10,
							'w'      => 45,
							'halign' => 'C',
					],
			],
			'l2r' => [
					'page'          => 1,
					'template_page' => 1,
					'function'      => [ 'calc' => 'calcL2R', 'draw' => [ 'drawNormal' ] ],
					'coordinates'   => [
							'x'      => 518,
							'y'      => 656,
							'h'      => 10,
							'w'      => 45,
							'halign' => 'C',
					],
			],
			'l2s' => [
					'page'          => 1,
					'template_page' => 1,
					'function'      => [ 'calc' => 'calcL2S', 'draw' => [ 'drawNormal' ] ],
					'coordinates'   => [
							'x'      => 518,
							'y'      => 680,
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

	function calcL1B( $value = null, $schema = null ) {
		$this->l1b = $this->MoneyFormat( TTMath::mul( $this->l1a, $this->credit_percent ) );

		return $this->l1b;
	}

	function calcL1D( $value = null, $schema = null ) {
		$this->l1d = $this->MoneyFormat( TTMath::sub( $this->l1b, $this->l1c ) );

		return $this->l1d;
	}

	function calcL1F( $value = null, $schema = null ) {
		$this->l1f = $this->MoneyFormat( TTMath::add( $this->l1d, $this->l1e ) );

		return $this->l1f;
	}

	function calcL2AII( $value = null, $schema = null ) {
		$this->l2aii = $this->MoneyFormat( TTMath::sub( $this->l2a, $this->l2ai ) );

		return $this->l2aii;
	}

	function calcL2AIV( $value = null, $schema = null ) {
		$this->l2aiv = $this->MoneyFormat( TTMath::sub( $this->l2aii, $this->l2aiii ) );

		return $this->l2aiv;
	}

	function calcL2D( $value = null, $schema = null ) {
		$this->l2d = $this->MoneyFormat( TTMath::mul( $this->l2aiv, $this->employer_social_security_rate ) );

		return $this->l2d;
	}

	function calcL2E( $value = null, $schema = null ) {
		$this->l2e = $this->MoneyFormat( TTMath::mul( $this->l2aii, $this->employer_medicare_rate ) );

		return $this->l2e;
	}

	function calcL2F( $value = null, $schema = null ) {
		$this->l2f = $this->MoneyFormat( TTMath::add( $this->l2a, TTMath::add( $this->l2b, TTMath::add( $this->l2c, TTMath::add( $this->l2d, $this->l2e ) ) ) ) );

		return $this->l2f;
	}

	function calcL2GII( $value = null, $schema = null ) {
		$this->l2gii = $this->MoneyFormat( TTMath::sub( $this->l2g, $this->l2gi ) );

		return $this->l2gii;
	}

	function calcL2GIV( $value = null, $schema = null ) {
		$this->l2giv = $this->MoneyFormat( TTMath::sub( $this->l2gii, $this->l2giii ) );

		return $this->l2giv;
	}

	function calcL2J( $value = null, $schema = null ) {
		$this->l2j = $this->MoneyFormat( TTMath::mul( $this->l2giv, $this->employer_social_security_rate ) );

		return $this->l2j;
	}

	function calcL2K( $value = null, $schema = null ) {
		$this->l2k = $this->MoneyFormat( TTMath::mul( $this->l2gii, $this->employer_medicare_rate ) );

		return $this->l2k;
	}

	function calcL2L( $value = null, $schema = null ) {
		$this->l2l = $this->MoneyFormat( TTMath::add( $this->l2g, TTMath::add( $this->l2h, TTMath::add( $this->l2i, TTMath::add( $this->l2j, $this->l2k ) ) ) ) );

		return $this->l2l;
	}

	function calcL2M( $value = null, $schema = null ) {
		$this->l2m = $this->MoneyFormat( TTMath::add( $this->l2f, $this->l2l ) );

		return $this->l2m;
	}

	function calcL2P( $value = null, $schema = null ) {
		$this->l2p = $this->MoneyFormat( TTMath::add( $this->l2n, $this->l2o ) );

		return $this->l2p;
	}

	function calcL2Q( $value = null, $schema = null ) {
		$this->l2q = $this->MoneyFormat( TTMath::sub( $this->l2m, $this->l2p ) );

		return $this->l2q;
	}

	function calcL2R( $value = null, $schema = null ) {
		$this->l2r = min( $this->l1f, $this->l2q );

		return $this->l2r;
	}

	function calcL2S( $value = null, $schema = null ) {
		$this->l2s = $this->MoneyFormat( TTMath::sub( $this->l2q, $this->l2r ) );

		return $this->l2s;
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