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
 * @package ChequeForms
 */

include_once( dirname(__FILE__) . DIRECTORY_SEPARATOR . 'ChequeForms_Base.class.php' );
class ChequeForms_MBL2398 extends ChequeForms_Base {

	public function getTemplateSchema( $name = NULL ) {
		$template_schema = array(
								//Initialize page1
								array(
									'page' => 1,
									'template_page' => 1,

								),

								//'company_name' => array(
								//		'coordinates' => array(
								//				'x' => 57,
								//				'y' => 105,
								//				'h' => 5,
								//				'w' => 100,
								//				'halign' => 'C',
								//		),
								//		'font' => array(
								//				'size' => 10,
								//				'type' => 'B'
								//		),
								//),
								//
								//// Company Full mailing address on one line
								//'company_full_address' => array(
								//		'coordinates' => array(
								//				'x' => 57,
								//				'y' => 110,
								//				'h' => 5,
								//				'w' => 100,
								//				'halign' => 'C',
								//		),
								//		'font' => array(
								//				'size' => 10,
								//				'type' => ''
								//		),
								//),


								// Recipeints Full mailing address.
								'full_address' => array(
										'coordinates' => array(
												'x' => 17,
												'y' => 137,
												'h' => 20,
												'w' => 100,
												'halign' => 'L',
										),
										'font' => array(
												'size' => 10,
												'type' => ''
										),
										'multicell' => TRUE,
								),

								// amount words
								'amount_words' => array(
										'function' => array('filterAmountWords', 'drawNormal'),
										'coordinates' => array(
														'x' => 19,
														'y' => 118,
														'h' => 5,
														'w' => 100,
														'halign' => 'L',
													),
										'font' => array(
														'size' => 10,
														'type' => ''
													)
								),
								// amount cents
								'amount_cents' => array(
										'function' => array('filterAmountCents', 'drawNormal'),
										'coordinates' => array(
														'x' => 119,
														'y' => 118,
														'h' => 5,
														'w' => 15,
														'halign' => 'L',
													),
										'font' => array(
														'size' => 10,
														'type' => ''
													)
								),

								// date
								'date' => array(
										'function' => array('filterDate', 'drawNormal'),
										'coordinates' => array(
														'x' => 173,
														'y' => 106,
														'h' => 5,
														'w' => 25,
														'halign' => 'C',
													),
										'font' => array(
														'size' => 10,
														'type' => ''
													)
								),
								//date format label
								array(
									'function' => array('getDisplayDateFormat', 'drawNormal'),
									'coordinates' => array(
												'x' => 173,
												'y' => 110,
												'h' => 5,
												'w' => 25,
												'halign' => 'C',
									),
									'font' => array(
												'size' => 6,
												'type' => ''
									)
								),

								'alignment_grid' => array( //Print alignment grid around the dollar amount, as it usually needs most fine tuning.
														   'function' => array('drawAlignmentGrid'),
														   'coordinates' => array(
																   'x' => 5,
																   'y' => 109,
																   'h' => 26,
																   'w' => 52,
														   ),
								),

								// amount padded
								'amount_padded' => array(
										'function' => array('filterAmountPadded', 'drawNormal'),
										'coordinates' => array(
														'x' => 175,
														'y' => 118,
														'h' => 5,
														'w' => 23,
														'halign' => 'L',
													),
										'font' => array(
														'size' => 10,
														'type' => ''
													)
								),

								// Signature
								'signature' => array(
										'function' => array('drawImage'),
										'coordinates' => array(
												'x' => 135,
												'y' => 136,
												'h' => 13,
												'w' => 50,
										),
								),

								// left column
								'stub_left_column' => array(
										'function' => 'drawPiecemeal',
										'coordinates' => array(
															array(
																'x' => 15,
																'y' => 12,
																'h' => 75,
																'w' => 92,
																'halign' => 'L',
															),
															array(
																'x' => 15,
																'y' => 188,
																'h' => 75,
																'w' => 92,
																'halign' => 'L',
															),
													),
										'font' => array(
														'size' => 10,
														'type' => ''
													),
										'multicell' => TRUE,
								),
								// right column
								'stub_right_column' => array(
										'function' => 'drawPiecemeal',
										'coordinates' => array(
															array(
																'x' => 107,
																'y' => 12,
																'h' => 75,
																'w' => 91,
																'halign' => 'R',
															),
															array(
																'x' => 107,
																'y' => 188,
																'h' => 75,
																'w' => 91,
																'halign' => 'R',
															),
													),
										'font' => array(
														'size' => 10,
														'type' => '',
													),
										'multicell' => TRUE,
								),

					);

		if ( isset($template_schema[$name]) ) {
			return $name;
		} else {
			return $template_schema;
		}
	}
}
?>