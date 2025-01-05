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

return [
	//Info:
	//  https://community.intuit.com/browse/payroll-compliance-us-en
	//  https://www.zipier.com/home/Forms/

	//Filing Methods: 	EFILE = EFILE by downloading file and uploading to agency.
	// 					MAIL = Print & Mail form
	//
	//Payment Methods:	EPAY = Online payment, ie: EFTPS
	//					CHECK


	//
	//Canada
	//
	'10:CA:00:00:0010' => [
			'T4SD' => [
					'form_code'         => '',
					'form_name'         => TTi18n::getText( 'Source Deductions Payment' ),
					'form_description'  => TTi18n::getText( 'Source Deductions Payment' ),
					'note'              => '',
					'tax_codes'         => [ 'INCOME', 'EI', 'CPP' ],
					'filing_methods'    => [ 'EFILE', ], //Supported by TT
					'payment_methods'   => [ 'EPAY', ], //Supported by TT
					'flags'             => [
							'include_w2'          => false,
							'file_zero_wage'      => false, //File if zero wages were paid.
							'file_zero_liability' => false, //File is zero libability is owed.
							'auto_file'           => true, //TT can automatically files.
							'auto_pay'            => true, //TT can automatically pays.
					],
					'date_restrictions' => [ 'start' => 90, 'end' => 90 ], //Days
					'frequency'         => [ //Weekly,Monthly,Quarterly

											 //Accelerated (Threshold 1)
											 [

													 'status_id'     => 20, //Disabled
													 'frequency_id'  => 50000,
													 'reminder_days' => 4,
											 ],

											 //Accelerated (Threshold 2)
											 [
													 'status_id'     => 20, //Disabled
													 'frequency_id'  => 51000,
													 'reminder_days' => 4,
											 ],

											 //Monthly
											 [
													 'status_id'            => 10, //Enabled
													 'frequency_id'         => 4100, //Monthly
													 'primary_day_of_month' => 15,
													 'reminder_days'        => 7,
											 ],

											 //Quarterly
											 [
													 'status_id'            => 20, //Disabled
													 'frequency_id'         => 3000, //Quarterly
													 'quarter_month'        => 1,
													 'primary_day_of_month' => 15,
													 'reminder_days'        => 7,
											 ],
					],
			],
			'PIER' => [
					'form_code'        => 'PIER',
					'form_name'        => TTi18n::getText( 'PIER Report (Internal Audit)' ),
					'form_description' => TTi18n::getText( 'Pensionable and Insurable Reporting' ),
					'note'             => '',
					'tax_codes'        => [ 'INCOME', 'EI', 'CPP' ],
					'filing_methods'   => [], //Supported by TT
					'payment_methods'  => [], //Supported by TT
					'flags'            => [
							'include_w2'          => false,
							'file_zero_wage'      => false, //File if zero wages were paid.
							'file_zero_liability' => false, //File is zero libability is owed.
							'auto_file'           => false, //TT can automatically files.
							'auto_pay'            => false, //TT can automatically pays.
					],
					'frequency'        => [ //Semi-Annual (YTD)
											[
													'status_id'            => 10, //Enabled
													'frequency_id'         => 2100, //Annual (YTD)
													'primary_month'        => 7, //Jul
													'primary_day_of_month' => 1,
													'reminder_days'        => 0,
											],
											[
													'status_id'            => 10, //Enabled
													'frequency_id'         => 2100, //Annual (YTD)
													'primary_month'        => 12, //Dec
													'primary_day_of_month' => 1,
													'reminder_days'        => 0,
											],
					],
			],
			'T4'   => [
					'form_code'         => 'T4',
					'form_name'         => TTi18n::getText( 'T4 (Filing)' ),
					'form_description'  => TTi18n::getText( 'Annual T4' ),
					'note'              => '',
					'tax_codes'         => [ 'INCOME', 'EI', 'CPP' ],
					'filing_methods'    => [ 'EFILE' ],
					'payment_methods'   => [ 'EPAY' ],
					'flags'             => [
							'include_w2'          => false,
							'file_zero_wage'      => false,
							'file_zero_liability' => false,
							'auto_file'           => true, //TT can automatically files.
							'auto_pay'            => false, //TT only pays through the T4SD event currently. This is needed to allow T4 efiling without DD. Shortfalls need to be paid another way.
					],
					'date_restrictions' => [ 'start' => ( 365 * 2 ), 'end' => 365 ], //Days
					'frequency'         => [ //Annual
											 [
													 'status_id'            => 10, //Enabled
													 'frequency_id'         => 2000, //Annual
													 'primary_month'        => 2, //Feb
													 'primary_day_of_month' => 28,
													 'reminder_days'        => 14,
											 ],
					],
			],
			'T4A'  => [
					'form_code'         => 'T4A',
					'form_name'         => TTi18n::getText( 'T4A (Filing)' ),
					'form_description'  => TTi18n::getText( 'Annual T4A' ),
					'note'              => '',
					'tax_codes'         => [ 'INCOME', 'EI', 'CPP' ],
					'filing_methods'    => [ 'EFILE' ],
					'payment_methods'   => [ 'EPAY' ],
					'flags'             => [
							'include_w2'          => false,
							'file_zero_wage'      => false,
							'file_zero_liability' => false,
							'auto_file'           => true, //TT can automatically files.
							'auto_pay'            => false, //TT only pays through the T4SD event currently. This is needed to allow T4 efiling without DD. Shortfalls need to be paid another way.
					],
					'date_restrictions' => [ 'start' => ( 365 * 2 ), 'end' => 365 ], //Days
					'frequency'         => [ //Annual
											 [
													 'status_id'            => 10, //Enabled
													 'frequency_id'         => 2000, //Annual
													 'primary_month'        => 2, //Feb
													 'primary_day_of_month' => 28,
													 'reminder_days'        => 14,
											 ],
					],
			],

	],
	'10:CA:00:00:0020' => [ //Service Canada [ROE]
							'ROE' => [
									'form_code'         => '',
									'form_name'         => TTi18n::getText( 'Record of Employment (ROE) Filing' ),
									'form_description'  => TTi18n::getText( 'Record of Employment (ROE) Filing' ),
									'note'              => '',
									'tax_codes'         => [],
									'filing_methods'    => [ 'PRINT', 'EFILE' ],
									'payment_methods'   => [],
									'flags'             => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => true, //TT can automatically files.
											'auto_pay'            => false, //TT can automatically pays.
									],
									'date_restrictions' => [ 'start' => ( 365 * 100 ), 'end' => ( 365 * 100 ) ], //Days -- No restrictions.
									'frequency'         => [    //If you have a monthly pay period or 13 pay periods per year (every four weeks), you must issue electronic ROEs by whichever date is earlier:
																//  five calendar days after the end of the pay period in which an employee experiences an interruption of earnings; or
																//  15 calendar days after the first day of an interruption of earnings.

																//On Termination (Pay Period End)
																[
																		'status_id'           => 10, //Enabled
																		'frequency_id'        => 90310, //On Termination Pay Period End
																		'due_date_delay_days' => 5,
																		'reminder_days'       => 0,
																],

																//On Termination
																[
																		'status_id'           => 20, //Disabled
																		'frequency_id'        => 90200, //On Termination Pay Period End
																		'due_date_delay_days' => 15,
																		'reminder_days'       => 0,
																],
									],
							],
	],

	//Canada - Workers Compensation
	'20:CA:AB:00:0100' => [
//			'PAYMENT' => array(  //Invoiced
//					'form_code'        => '',
//					'form_name'        => TTi18n::getText( 'Payment' ),
//					'form_description' => TTi18n::getText( 'Payment' ),
//					'note'             => '',
//					'tax_codes'        => array(''),
//					'filing_methods'   => array(),
//					'payment_methods'  => array('EPAY', 'CHECK'),
//					'flags'            => array(
//							'include_w2'          => FALSE,
//							'file_zero_wage'      => FALSE,
//							'file_zero_liability' => FALSE,
//							'auto_file'           => FALSE,
//							'auto_pay'            => FALSE,
//					),
//					'frequency'        => array(
//
//						//Monthly
//						array(
//								'status_id'            => 10, //Enabled
//								'frequency_id'         => 4100, //Monthly
//								'primary_day_of_month' => 28, //Last day
//								'reminder_days'        => 7,
//						),
//
//						//Quarterly
//						array(
//
//								'status_id'            => 20, //Disabled
//								'frequency_id'         => 3000, //Quarterly
//								'quarter_month'        => 1,
//								'primary_day_of_month' => 15,
//								'reminder_days'        => 7,
//						),
//
//						//Annual
//						array(
//								'status_id'            => 20, //Disabled
//								'frequency_id'         => 2000, //Annual
//								'primary_month'        => 2, //Feb
//								'primary_day_of_month' => 28,
//								'reminder_days'        => 14,
//						),
//					),
//			),
'REPORT' => [
		'form_code'        => '',
		'form_name'        => TTi18n::getText( 'Report' ),
		'form_description' => TTi18n::getText( 'Report' ),
		'note'             => '',
		'tax_codes'        => [ '' ],
		'filing_methods'   => [ 'EFILE', 'MAIL' ],
		'payment_methods'  => [ 'EPAY', 'CHECK' ],
		'flags'            => [
				'include_w2'          => false,
				'file_zero_wage'      => false,
				'file_zero_liability' => false,
				'auto_file'           => false,
				'auto_pay'            => false,
		],
		'frequency'        => [
				[
						'status_id'            => 10, //Enabled
						'frequency_id'         => 2000, //Annual
						'primary_month'        => 2, //Feb
						'primary_day_of_month' => 28,
						'reminder_days'        => 14,
				],

		],
],
	],
	'20:CA:BC:00:0100' => [
			'PAYMENT' => [
					'form_code'        => '',
					'form_name'        => TTi18n::getText( 'Payment' ),
					'form_description' => TTi18n::getText( 'Payment' ),
					'note'             => '',
					'tax_codes'        => [ '' ],
					'filing_methods'   => [],
					'payment_methods'  => [ 'EPAY', 'CHECK' ],
					'flags'            => [
							'include_w2'          => false,
							'file_zero_wage'      => false,
							'file_zero_liability' => false,
							'auto_file'           => false,
							'auto_pay'            => false,
					],
					'frequency'        => [

						//Quarterly
						[
								'status_id'            => 10, //Enabled
								'frequency_id'         => 3000, //Quarterly
								'quarter_month'        => 1,
								'primary_day_of_month' => 20,
								'reminder_days'        => 7,
						],

						//Annual
						[
								'status_id'            => 20, //Disabled
								'frequency_id'         => 2000, //Annual
								'primary_month'        => 2, //Feb
								'primary_day_of_month' => 28,
								'reminder_days'        => 14,
						],
					],
			],
			'REPORT'  => [
					'form_code'        => '',
					'form_name'        => TTi18n::getText( 'Report' ),
					'form_description' => TTi18n::getText( 'Report' ),
					'note'             => '',
					'tax_codes'        => [ '' ],
					'filing_methods'   => [ 'EFILE', 'MAIL' ],
					'payment_methods'  => [ 'EPAY', 'CHECK' ],
					'flags'            => [
							'include_w2'          => false,
							'file_zero_wage'      => false,
							'file_zero_liability' => false,
							'auto_file'           => false,
							'auto_pay'            => false,
					],
					'frequency'        => [
						//Reporting
						[
								'status_id'            => 10, //Enabled
								'frequency_id'         => 2000, //Annual
								'primary_month'        => 2, //Feb
								'primary_day_of_month' => 28,
								'reminder_days'        => 14,
						],
					],
			],
	],
	'20:CA:MB:00:0100' => [
			'PAYMENT' => [
					'form_code'        => '',
					'form_name'        => TTi18n::getText( 'Payment' ),
					'form_description' => TTi18n::getText( 'Payment' ),
					'note'             => '',
					'tax_codes'        => [ '' ],
					'filing_methods'   => [],
					'payment_methods'  => [ 'EPAY', 'CHECK' ],
					'flags'            => [
							'include_w2'          => false,
							'file_zero_wage'      => false,
							'file_zero_liability' => false,
							'auto_file'           => false,
							'auto_pay'            => false,
					],
					'frequency'        => [

						//Annual
						[
								'status_id'            => 10, //Enabled
								'frequency_id'         => 2000, //Annual
								'primary_month'        => 3, //Mar
								'primary_day_of_month' => 31, //Last day
								'reminder_days'        => 14,
						],

						//Quarterly
						[
								'status_id'            => 20, //Disabled
								'frequency_id'         => 3000, //Quarterly
								'quarter_month'        => 3,
								'primary_day_of_month' => 31, //Last Day
								'reminder_days'        => 7,
						],
					],
			],
			'REPORT'  => [
					'form_code'        => '',
					'form_name'        => TTi18n::getText( 'Report' ),
					'form_description' => TTi18n::getText( 'Report' ),
					'note'             => '',
					'tax_codes'        => [ '' ],
					'filing_methods'   => [ 'EFILE', 'MAIL' ],
					'payment_methods'  => [ 'EPAY', 'CHECK' ],
					'flags'            => [
							'include_w2'          => false,
							'file_zero_wage'      => false,
							'file_zero_liability' => false,
							'auto_file'           => false,
							'auto_pay'            => false,
					],
					'frequency'        => [
							[
									'status_id'            => 10, //Enabled
									'frequency_id'         => 2000, //Annual
									'primary_month'        => 2, //Feb
									'primary_day_of_month' => 28, //Last day
									'reminder_days'        => 14,
							],
					],
			],
	],

	'20:CA:NB:00:0100' => [
			'PAYMENT' => [
					'form_code'        => '',
					'form_name'        => TTi18n::getText( 'Payment' ),
					'form_description' => TTi18n::getText( 'Payment' ),
					'note'             => '',
					'tax_codes'        => [ '' ],
					'filing_methods'   => [],
					'payment_methods'  => [ 'EPAY', 'CHECK' ],
					'flags'            => [
							'include_w2'          => false,
							'file_zero_wage'      => false,
							'file_zero_liability' => false,
							'auto_file'           => false,
							'auto_pay'            => false,
					],
					'frequency'        => [ //Annual - Deadline Mar 31, Semi-Annual Mar 31 & Aug 31

											//Annual
											[
													'status_id'            => 10, //Enabled
													'frequency_id'         => 2000, //Annual
													'primary_month'        => 3, //Mar
													'primary_day_of_month' => 31,
													'reminder_days'        => 14,
											],


					],
			],
			'REPORT'  => [ //Form 100
						   'form_code'        => '',
						   'form_name'        => TTi18n::getText( 'Report' ),
						   'form_description' => TTi18n::getText( 'Report' ),
						   'note'             => '',
						   'tax_codes'        => [ '' ],
						   'filing_methods'   => [ 'EFILE', 'MAIL' ],
						   'payment_methods'  => [ 'EPAY', 'CHECK' ],
						   'flags'            => [
								   'include_w2'          => false,
								   'file_zero_wage'      => false,
								   'file_zero_liability' => false,
								   'auto_file'           => false,
								   'auto_pay'            => false,
						   ],
						   'frequency'        => [
								   [
										   'status_id'            => 10, //Enabled
										   'frequency_id'         => 2000, //Annual
										   'primary_month'        => 2, //Feb
										   'primary_day_of_month' => 28,
										   'reminder_days'        => 14,
								   ],

						   ],
			],
	],

	'20:CA:NL:00:0100' => [
		//Issues invoices only.
		//			'PAYMENT' => array(
		//					'form_code'        => '',
		//					'form_name'        => TTi18n::getText( 'Payment' ),
		//					'form_description' => TTi18n::getText( 'Payment' ),
		//					'note'             => '',
		//					'tax_codes'        => array(''),
		//					'filing_methods'   => array(),
		//					'payment_methods'  => array('EPAY', 'CHECK'),
		//					'flags'            => array(
		//							'include_w2'          => FALSE,
		//							'file_zero_wage'      => FALSE,
		//							'file_zero_liability' => FALSE,
		//							'auto_file'           => FALSE,
		//							'auto_pay'            => FALSE,
		//					),
		//					'frequency'        => array(
		//						//Quarterly
		//						array(
		//								'status_id'            => 10, //Enabled
		//								'frequency_id'         => 3000, //Quarterly
		//								'quarter_month'        => 1,
		//								'primary_day_of_month' => 20,
		//								'reminder_days'        => 7,
		//						),
		//					),
		//			),
		'REPORT' => [
				'form_code'        => '',
				'form_name'        => TTi18n::getText( 'Report' ),
				'form_description' => TTi18n::getText( 'Report' ),
				'note'             => '',
				'tax_codes'        => [ '' ],
				'filing_methods'   => [ 'EFILE', 'MAIL' ],
				'payment_methods'  => [ 'EPAY', 'CHECK' ],
				'flags'            => [
						'include_w2'          => false,
						'file_zero_wage'      => false,
						'file_zero_liability' => false,
						'auto_file'           => false,
						'auto_pay'            => false,
				],
				'frequency'        => [
						[
								'status_id'            => 10, //Enabled
								'frequency_id'         => 2000, //Annual
								'primary_month'        => 2, //Feb
								'primary_day_of_month' => 28,
								'reminder_days'        => 14,
						],

				],
		],
	],

	'20:CA:NS:00:0100' => [
			'PAYMENT' => [
					'form_code'        => '',
					'form_name'        => TTi18n::getText( 'Payment' ),
					'form_description' => TTi18n::getText( 'Payment' ),
					'note'             => '',
					'tax_codes'        => [ '' ],
					'filing_methods'   => [],
					'payment_methods'  => [ 'EPAY', 'CHECK' ],
					'flags'            => [
							'include_w2'          => false,
							'file_zero_wage'      => false,
							'file_zero_liability' => false,
							'auto_file'           => false,
							'auto_pay'            => false,
					],
					'frequency'        => [
						//Monthly
						[
								'status_id'            => 10, //Enabled
								'frequency_id'         => 4100, //Monthly
								'primary_day_of_month' => 15, //Last day
								'reminder_days'        => 7,
						],
					],
			],
			'REPORT'  => [
					'form_code'        => '',
					'form_name'        => TTi18n::getText( 'Report' ),
					'form_description' => TTi18n::getText( 'Report' ),
					'note'             => '',
					'tax_codes'        => [ '' ],
					'filing_methods'   => [ 'EFILE', 'MAIL' ],
					'payment_methods'  => [ 'EPAY', 'CHECK' ],
					'flags'            => [
							'include_w2'          => false,
							'file_zero_wage'      => false,
							'file_zero_liability' => false,
							'auto_file'           => false,
							'auto_pay'            => false,
					],
					'frequency'        => [
							[
									'status_id'            => 10, //Enabled
									'frequency_id'         => 4100, //Monthly
									'primary_day_of_month' => 15, //Last day
									'reminder_days'        => 7,
							],
					],
			],
	],

	'20:CA:NT:00:0100' => [
//			'PAYMENT' => array(  //Invoiced
//					'form_code'        => '',
//					'form_name'        => TTi18n::getText( 'Payment' ),
//					'form_description' => TTi18n::getText( 'Payment' ),
//					'note'             => '',
//					'tax_codes'        => array(''),
//					'filing_methods'   => array(),
//					'payment_methods'  => array('EPAY', 'CHECK'),
//					'flags'            => array(
//							'include_w2'          => FALSE,
//							'file_zero_wage'      => FALSE,
//							'file_zero_liability' => FALSE,
//							'auto_file'           => FALSE,
//							'auto_pay'            => FALSE,
//					),
//					'frequency'        => array(
//						//Monthly
//						array(
//								'status_id'            => 10, //Enabled
//								'frequency_id'         => 4100, //Monthly
//								'primary_day_of_month' => 31, //Last day
//								'reminder_days'        => 7,
//						),
//					),
//			),
'REPORT' => [
		'form_code'        => '',
		'form_name'        => TTi18n::getText( 'Report' ),
		'form_description' => TTi18n::getText( 'Report' ),
		'note'             => '',
		'tax_codes'        => [ '' ],
		'filing_methods'   => [ 'EFILE', 'MAIL' ],
		'payment_methods'  => [ 'EPAY', 'CHECK' ],
		'flags'            => [
				'include_w2'          => false,
				'file_zero_wage'      => false,
				'file_zero_liability' => false,
				'auto_file'           => false,
				'auto_pay'            => false,
		],
		'frequency'        => [
				[
						'status_id'            => 10, //Enabled
						'frequency_id'         => 2000, //Annual
						'primary_month'        => 2, //Feb
						'primary_day_of_month' => 28,
						'reminder_days'        => 14,
				],

		],
],
	],

	'20:CA:NU:00:0100' => [
//			'PAYMENT' => array(       //Invoiced
//					'form_code'        => '',
//					'form_name'        => TTi18n::getText( 'Payment' ),
//					'form_description' => TTi18n::getText( 'Payment' ),
//					'note'             => '',
//					'tax_codes'        => array(''),
//					'filing_methods'   => array(),
//					'payment_methods'  => array('EPAY', 'CHECK'),
//					'flags'            => array(
//							'include_w2'          => FALSE,
//							'file_zero_wage'      => FALSE,
//							'file_zero_liability' => FALSE,
//							'auto_file'           => FALSE,
//							'auto_pay'            => FALSE,
//					),
//					'frequency'        => array(
//						//Monthly
//						array(
//								'status_id'            => 10, //Enabled
//								'frequency_id'         => 4100, //Monthly
//								'primary_day_of_month' => 31, //Last day
//								'reminder_days'        => 7,
//						),
//					),
//			),
'REPORT' => [
		'form_code'        => '',
		'form_name'        => TTi18n::getText( 'Report' ),
		'form_description' => TTi18n::getText( 'Report' ),
		'note'             => '',
		'tax_codes'        => [ '' ],
		'filing_methods'   => [ 'EFILE', 'MAIL' ],
		'payment_methods'  => [ 'EPAY', 'CHECK' ],
		'flags'            => [
				'include_w2'          => false,
				'file_zero_wage'      => false,
				'file_zero_liability' => false,
				'auto_file'           => false,
				'auto_pay'            => false,
		],
		'frequency'        => [
				[
						'status_id'            => 10, //Enabled
						'frequency_id'         => 2000, //Annual
						'primary_month'        => 2, //Feb
						'primary_day_of_month' => 28,
						'reminder_days'        => 14,
				],

		],
],
	],

	'20:CA:PE:00:0100' => [
			'PAYMENT' => [
					'form_code'        => '',
					'form_name'        => TTi18n::getText( 'Payment' ),
					'form_description' => TTi18n::getText( 'Payment' ),
					'note'             => '',
					'tax_codes'        => [ '' ],
					'filing_methods'   => [],
					'payment_methods'  => [ 'EPAY', 'CHECK' ],
					'flags'            => [
							'include_w2'          => false,
							'file_zero_wage'      => false,
							'file_zero_liability' => false,
							'auto_file'           => false,
							'auto_pay'            => false,
					],
					'frequency'        => [
						//Monthly
						[
								'status_id'            => 10, //Enabled
								'frequency_id'         => 4100, //Monthly
								'primary_day_of_month' => 28, //Last day
								'reminder_days'        => 7,
						],

						//						//Annual - Unknown date, based on assessment.
						//						array(
						//								'status_id'            => 20, //Enabled
						//								'frequency_id'         => 2000, //Annual
						//								'primary_month'        => 2, //Feb
						//								'primary_day_of_month' => 28,
						//								'reminder_days'        => 14,
						//						),
					],
			],
			'REPORT'  => [
					'form_code'        => '',
					'form_name'        => TTi18n::getText( 'Report' ),
					'form_description' => TTi18n::getText( 'Report' ),
					'note'             => '',
					'tax_codes'        => [ '' ],
					'filing_methods'   => [ 'EFILE', 'MAIL' ],
					'payment_methods'  => [ 'EPAY', 'CHECK' ],
					'flags'            => [
							'include_w2'          => false,
							'file_zero_wage'      => false,
							'file_zero_liability' => false,
							'auto_file'           => false,
							'auto_pay'            => false,
					],
					'frequency'        => [
							[
									'status_id'            => 10, //Enabled
									'frequency_id'         => 2000, //Annual
									'primary_month'        => 2, //Feb
									'primary_day_of_month' => 28,
									'reminder_days'        => 14,
							],
					],
			],
	],

	'20:CA:ON:00:0100' => [
			'PAYMENT' => [
					'form_code'        => '',
					'form_name'        => TTi18n::getText( 'Payment' ),
					'form_description' => TTi18n::getText( 'Payment' ),
					'note'             => '',
					'tax_codes'        => [ '' ],
					'filing_methods'   => [],
					'payment_methods'  => [ 'EPAY', 'CHECK' ],
					'flags'            => [
							'include_w2'          => false,
							'file_zero_wage'      => false,
							'file_zero_liability' => false,
							'auto_file'           => false,
							'auto_pay'            => false,
					],
					'frequency'        => [

						//Monthly
						[
								'status_id'            => 20, //Disabled
								'frequency_id'         => 4100, //Monthly
								'primary_day_of_month' => 31, //Last day
								'reminder_days'        => 7,
						],

						//Quarterly
						[
								'status_id'            => 10, //Enabled
								'frequency_id'         => 3000, //Quarterly
								'quarter_month'        => 1,
								'primary_day_of_month' => 31, //Last day
								'reminder_days'        => 7,
						],

						//Annual
						[
								'status_id'            => 20, //Disabled
								'frequency_id'         => 2000, //Annual
								'primary_month'        => 4, //Apr
								'primary_day_of_month' => 30, //Last Day
								'reminder_days'        => 2,
						],

					],
			],
			'REPORT'  => [
					'form_code'        => '',
					'form_name'        => TTi18n::getText( 'Report' ),
					'form_description' => TTi18n::getText( 'Report' ),
					'note'             => '',
					'tax_codes'        => [ '' ],
					'filing_methods'   => [ 'EFILE', 'MAIL' ],
					'payment_methods'  => [ 'EPAY', 'CHECK' ],
					'flags'            => [
							'include_w2'          => false,
							'file_zero_wage'      => false,
							'file_zero_liability' => false,
							'auto_file'           => false,
							'auto_pay'            => false,
					],
					'frequency'        => [
							[
									'status_id'            => 10, //Enabled
									'frequency_id'         => 2000, //Annual
									'primary_month'        => 3, //Mar
									'primary_day_of_month' => 31, //Last Day
									'reminder_days'        => 14,
							],

					],
			],
	],

	'20:CA:SK:00:0100' => [
			'PAYMENT' => [
					'form_code'        => '',
					'form_name'        => TTi18n::getText( 'Payment' ),
					'form_description' => TTi18n::getText( 'Payment' ),
					'note'             => '',
					'tax_codes'        => [ '' ],
					'filing_methods'   => [],
					'payment_methods'  => [ 'EPAY', 'CHECK' ],
					'flags'            => [
							'include_w2'          => false,
							'file_zero_wage'      => false,
							'file_zero_liability' => false,
							'auto_file'           => false,
							'auto_pay'            => false,
					],
					'frequency'        => [ //Due Apr 1st and Sep 1st

											//Semi-Annual
											[
													'status_id'              => 10, //Enabled
													'frequency_id'           => 2200, //Semi-Annual
													'primary_month'          => 4, //Apr
													'primary_day_of_month'   => 1,
													'secondary_month'        => 9, //Sep
													'secondary_day_of_month' => 1,
													'reminder_days'          => 14,
											],

					],
			],
			'REPORT'  => [
					'form_code'        => '',
					'form_name'        => TTi18n::getText( 'Report' ),
					'form_description' => TTi18n::getText( 'Report' ),
					'note'             => '',
					'tax_codes'        => [ '' ],
					'filing_methods'   => [ 'EFILE', 'MAIL' ],
					'payment_methods'  => [ 'EPAY', 'CHECK' ],
					'flags'            => [
							'include_w2'          => false,
							'file_zero_wage'      => false,
							'file_zero_liability' => false,
							'auto_file'           => false,
							'auto_pay'            => false,
					],
					'frequency'        => [
							[
									'status_id'            => 10, //Enabled
									'frequency_id'         => 2000, //Annual
									'primary_month'        => 2, //Feb
									'primary_day_of_month' => 28,
									'reminder_days'        => 14,
							],

					],
			],
	],
	'20:CA:YT:00:0100' => [
			'PAYMENT' => [
					'form_code'        => '',
					'form_name'        => TTi18n::getText( 'Payment' ),
					'form_description' => TTi18n::getText( 'Payment' ),
					'note'             => '',
					'tax_codes'        => [ '' ],
					'filing_methods'   => [],
					'payment_methods'  => [ 'EPAY', 'CHECK' ],
					'flags'            => [
							'include_w2'          => false,
							'file_zero_wage'      => false,
							'file_zero_liability' => false,
							'auto_file'           => false,
							'auto_pay'            => false,
					],
					'frequency'        => [

						//Annual
						[
								'status_id'            => 20, //Disabled
								'frequency_id'         => 2000, //Annual
								'primary_month'        => 2, //Feb
								'primary_day_of_month' => 28,
								'reminder_days'        => 14,
						],
					],
			],
			'REPORT'  => [
					'form_code'        => '',
					'form_name'        => TTi18n::getText( 'Report' ),
					'form_description' => TTi18n::getText( 'Report' ),
					'note'             => '',
					'tax_codes'        => [ '' ],
					'filing_methods'   => [ 'EFILE', 'MAIL' ],
					'payment_methods'  => [ 'EPAY', 'CHECK' ],
					'flags'            => [
							'include_w2'          => false,
							'file_zero_wage'      => false,
							'file_zero_liability' => false,
							'auto_file'           => false,
							'auto_pay'            => false,
					],
					'frequency'        => [
							[
									'status_id'            => 10, //Enabled
									'frequency_id'         => 2000, //Annual
									'primary_month'        => 2, //Feb
									'primary_day_of_month' => 28,
									'reminder_days'        => 14,
							],
					],
			],
	],


	//Canada - Maintenance Enforcement Program (MEP) [Child Support]
	'20:CA:AB:00:0040' => [ //Child Support
							'SUPPORT' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'Maintenance Enforcement Payment' ),
									'form_description' => TTi18n::getText( 'Maintenance Enforcement Payment' ),
									'note'             => '',
									'tax_codes'        => [ 'SUPPORT' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Per Pay Period
										[
												'status_id'           => 10, //Enabled
												'frequency_id'        => 1000, //Per Pay Period
												'due_date_delay_days' => 15,
												'reminder_days'       => 5,
										],
									],
							],
	],
	'20:CA:BC:00:0040' => [ //Child Support
							'SUPPORT' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'Maintenance Enforcement Payment' ),
									'form_description' => TTi18n::getText( 'Maintenance Enforcement Payment' ),
									'note'             => '',
									'tax_codes'        => [ 'SUPPORT' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Per Pay Period
										[
												'status_id'           => 10, //Enabled
												'frequency_id'        => 1000, //Per Pay Period
												'due_date_delay_days' => 10,
												'reminder_days'       => 5,
										],
									],
							],
	],
	'20:CA:MB:00:0040' => [ //Child Support
							'SUPPORT' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'Maintenance Enforcement Payment' ),
									'form_description' => TTi18n::getText( 'Maintenance Enforcement Payment' ),
									'note'             => '',
									'tax_codes'        => [ 'SUPPORT' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Per Pay Period
										[
												'status_id'           => 10, //Enabled
												'frequency_id'        => 1000, //Per Pay Period
												'due_date_delay_days' => 10,
												'reminder_days'       => 5,
										],
									],
							],
	],
	'20:CA:NB:00:0040' => [ //Child Support
							'SUPPORT' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'Maintenance Enforcement Payment' ),
									'form_description' => TTi18n::getText( 'Maintenance Enforcement Payment' ),
									'note'             => '',
									'tax_codes'        => [ 'SUPPORT' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Per Pay Period
										[
												'status_id'           => 10, //Enabled
												'frequency_id'        => 1000, //Per Pay Period
												'due_date_delay_days' => 10,
												'reminder_days'       => 5,
										],
									],
							],
	],
	'20:CA:NL:00:0040' => [ //Child Support
							'SUPPORT' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'Maintenance Enforcement Payment' ),
									'form_description' => TTi18n::getText( 'Maintenance Enforcement Payment' ),
									'note'             => '',
									'tax_codes'        => [ 'SUPPORT' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Per Pay Period
										[
												'status_id'           => 10, //Enabled
												'frequency_id'        => 1000, //Per Pay Period
												'due_date_delay_days' => 10,
												'reminder_days'       => 5,
										],
									],
							],
	],
	'20:CA:NS:00:0040' => [ //Child Support
							'SUPPORT' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'Maintenance Enforcement Payment' ),
									'form_description' => TTi18n::getText( 'Maintenance Enforcement Payment' ),
									'note'             => '',
									'tax_codes'        => [ 'SUPPORT' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Per Pay Period
										[
												'status_id'           => 10, //Enabled
												'frequency_id'        => 1000, //Per Pay Period
												'due_date_delay_days' => 10,
												'reminder_days'       => 5,
										],
									],
							],
	],
	'20:CA:NT:00:0040' => [ //Child Support
							'SUPPORT' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'Maintenance Enforcement Payment' ),
									'form_description' => TTi18n::getText( 'Maintenance Enforcement Payment' ),
									'note'             => '',
									'tax_codes'        => [ 'SUPPORT' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Per Pay Period
										[
												'status_id'           => 10, //Enabled
												'frequency_id'        => 1000, //Per Pay Period
												'due_date_delay_days' => 10,
												'reminder_days'       => 5,
										],
									],
							],
	],
	'20:CA:NU:00:0040' => [ //Child Support
							'SUPPORT' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'Maintenance Enforcement Payment' ),
									'form_description' => TTi18n::getText( 'Maintenance Enforcement Payment' ),
									'note'             => '',
									'tax_codes'        => [ 'SUPPORT' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Per Pay Period
										[
												'status_id'           => 10, //Enabled
												'frequency_id'        => 1000, //Per Pay Period
												'due_date_delay_days' => 10,
												'reminder_days'       => 5,
										],
									],
							],
	],
	'20:CA:ON:00:0040' => [ //Child Support
							'SUPPORT' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'Maintenance Enforcement Payment' ),
									'form_description' => TTi18n::getText( 'Maintenance Enforcement Payment' ),
									'note'             => '',
									'tax_codes'        => [ 'SUPPORT' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Per Pay Period
										[
												'status_id'           => 10, //Enabled
												'frequency_id'        => 1000, //Per Pay Period
												'due_date_delay_days' => 10,
												'reminder_days'       => 5,
										],
									],
							],
	],
	'20:CA:PE:00:0040' => [ //Child Support
							'SUPPORT' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'Maintenance Enforcement Payment' ),
									'form_description' => TTi18n::getText( 'Maintenance Enforcement Payment' ),
									'note'             => '',
									'tax_codes'        => [ 'SUPPORT' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Per Pay Period
										[
												'status_id'           => 10, //Enabled
												'frequency_id'        => 1000, //Per Pay Period
												'due_date_delay_days' => 10,
												'reminder_days'       => 5,
										],
									],
							],
	],
	'20:CA:QC:00:0040' => [ //Child Support
							'SUPPORT' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'Maintenance Enforcement Payment' ),
									'form_description' => TTi18n::getText( 'Maintenance Enforcement Payment' ),
									'note'             => '',
									'tax_codes'        => [ 'SUPPORT' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Per Pay Period
										[
												'status_id'           => 10, //Enabled
												'frequency_id'        => 1000, //Per Pay Period
												'due_date_delay_days' => 10,
												'reminder_days'       => 5,
										],
									],
							],
	],
	'20:CA:SK:00:0040' => [ //Child Support
							'SUPPORT' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'Maintenance Enforcement Payment' ),
									'form_description' => TTi18n::getText( 'Maintenance Enforcement Payment' ),
									'note'             => '',
									'tax_codes'        => [ 'SUPPORT' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Per Pay Period
										[
												'status_id'           => 10, //Enabled
												'frequency_id'        => 1000, //Per Pay Period
												'due_date_delay_days' => 10,
												'reminder_days'       => 5,
										],
									],
							],
	],
	'20:CA:YT:00:0040' => [ //Child Support
							'SUPPORT' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'Maintenance Enforcement Payment' ),
									'form_description' => TTi18n::getText( 'Maintenance Enforcement Payment' ),
									'note'             => '',
									'tax_codes'        => [ 'SUPPORT' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Per Pay Period
										[
												'status_id'           => 10, //Enabled
												'frequency_id'        => 1000, //Per Pay Period
												'due_date_delay_days' => 10,
												'reminder_days'       => 5,
										],
									],
							],
	],

	//
	//US
	//
	'10:US:00:00:0010' => [ //Internal Revenue Service (IRS) [Federal Tax/Social Security/Medicare]
							'F940' => [
									'form_code'        => '940', //Auto-Includes Schedule A
									'form_name'        => TTi18n::getText( '940 Annual Filing' ),
									'form_description' => TTi18n::getText( 'Employer\'s Annual Federal Unemployment (FUTA) Tax Return' ),
									'note'             => '',
									'tax_codes'        => [ 'FUTA' ],
									'filing_methods'   => [ 'MAIL' ], //No 'EFILE' from TimeTrex yet.
									'payment_methods'  => [ 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [ //Annually
															[
																	'status_id'            => 10, //Enabled
																	'frequency_id'         => 2000, //Annual
																	'primary_month'        => 1, //Jan
																	'primary_day_of_month' => 31,
																	'reminder_days'        => 14,
															],

									],
							],
							'P940' => [ //Payments must be made after any quarter where the total payment amount exceeds $500.
										'form_code'        => '940',
										'form_name'        => TTi18n::getText( '940 Payment' ),
										'form_description' => TTi18n::getText( 'Employers Federal Payments: FUTA' ),
										'note'             => '',
										'tax_codes'        => [ 'FUTA' ],
										'filing_methods'   => [ 'MAIL' ], //No 'EFILE' from TimeTrex yet.
										'payment_methods'  => [ 'EPAY' ],
										'flags'            => [
												'include_w2'          => false,
												'file_zero_wage'      => false,
												'file_zero_liability' => false,
												'auto_file'           => false,
												'auto_pay'            => false,
										],
										'frequency'        => [ //Quarterly,Annually

																//Quarterly
																[

																		'status_id'            => 10, //Enabled
																		'frequency_id'         => 3000, //Quarterly
																		'quarter_month'        => 1,
																		'primary_day_of_month' => 31, //Last day
																		'reminder_days'        => 14,
																],

																//Annual
																[
																		'status_id'            => 20, //Disabled
																		'frequency_id'         => 2000, //Annual
																		'primary_month'        => 1, //Jun
																		'primary_day_of_month' => 31,
																		'reminder_days'        => 14,
																],
										],
							],

							'F941' => [
									'form_code'        => '941', //Includes Schedule B
									'form_name'        => TTi18n::getText( '941 Quarterly Filing' ),
									'form_description' => TTi18n::getText( 'Employer\'s Quarterly Federal Tax Return' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'MAIL' ], //No 'EFILE' from TimeTrex yet.
									'payment_methods'  => [ 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [ //Quarterly
															[
																	'status_id'            => 10, //Enabled
																	'frequency_id'         => 3000, //Quarterly
																	'quarter_month'        => 1,
																	'primary_day_of_month' => 31,
																	'reminder_days'        => 14,
															],

									],
							],
							'P941' => [
									'form_code'        => '941',
									'form_name'        => TTi18n::getText( '941 Payment' ),
									'form_description' => TTi18n::getText( 'Employers Federal Payments' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'MAIL' ], //No 'EFILE' from TimeTrex yet.
									'payment_methods'  => [ 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [ //Next Day,Semiweekly,Monthly,Quarterly
															[ //Next Day
															  'status_id'           => 20, //Disabled
															  'frequency_id'        => 1000, //Per Pay Period
															  'due_date_delay_days' => 1,
															  'reminder_days'       => 1,
															],

															[
																//Semi-Weekly
																'status_id'     => 20, //Disabled
																'frequency_id'  => 64000, //Semi-Weekly
																'reminder_days' => 3,
															],

															[
																//Monthly
																'status_id'            => 10, //Enabled
																'frequency_id'         => 4100, //Monthly
																'primary_day_of_month' => 15,
																'reminder_days'        => 7,
															],

															[
																//Quarterly
																'status_id'            => 20, //Disabled
																'frequency_id'         => 3000, //Quarterly
																'quarter_month'        => 1,
																'primary_day_of_month' => 31, //Last day
																'reminder_days'        => 14,
															],

									],
							],

							'F1099NEC' => [
									'form_code'        => '1099-NEC',
									'form_name'        => TTi18n::getText( '1099-NEC Annual Filing' ),
									'form_description' => TTi18n::getText( 'Miscellaneous Income' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'MAIL' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [ //Annual
															[
																	'status_id'            => 10, //Enabled
																	'frequency_id'         => 2000, //Annual
																	'primary_month'        => 1, //Jan
																	'primary_day_of_month' => 31,
																	'reminder_days'        => 14,
															],
									],
							],

	],
	'10:US:00:00:0020' => [ //Social Security Administration (SSA) [FUTA/Unemployment]
							'FW2' => [
									'form_code'        => 'W2', //Includes W3
									'form_name'        => TTi18n::getText( 'W2 Filing' ),
									'form_description' => TTi18n::getText( 'Wage and Tax Statement' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'EFILE' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [ //Annual
															[
																	'status_id'            => 10, //Enabled
																	'frequency_id'         => 2000, //Annual
																	'primary_month'        => 1, //Jan
																	'primary_day_of_month' => 31,
																	'reminder_days'        => 14,
															],
									],
							],
	],

	'10:US:00:00:0100' => [ //Centers for Medicare & Medical Services (CMS.gov)
							'PBJ' => [
									'form_code'        => 'PBJ',
									'form_name'        => TTi18n::getText( 'Payroll Based Journal (PBJ) Filing' ),
									'form_description' => TTi18n::getText( 'Payroll Based Journal (PBJ) Filing' ),
									'note'             => '',
									'tax_codes'        => [ '' ],
									'filing_methods'   => [ 'EFILE' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [ //Quarterly
															[
																//Quarterly
																'status_id'            => 10, //Enabled
																'frequency_id'         => 3000, //Quarterly
																'quarter_month'        => 2,
																'primary_day_of_month' => 14,
																'reminder_days'        => 14,
															],
									],
							],
	],

	'10:US:00:00:0110' => [ //US - EEO Commission
							'EEO1' => [
									'form_code'        => 'EEO1',
									'form_name'        => TTi18n::getText( 'EEO-1 Filing' ),
									'form_description' => TTi18n::getText( 'EEO-1 Filing' ),
									'note'             => '',
									'tax_codes'        => [ '' ],
									'filing_methods'   => [ 'EFILE' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [ //Annual
															[
																	'status_id'            => 10, //Enabled
																	'frequency_id'         => 2000, //Annual
																	'primary_month'        => 4, //Apr - This seems to change every year, so pick a early date.
																	'primary_day_of_month' => 30,
																	'reminder_days'        => 14,
															],
									],
							],
							'EEO4' => [
									'form_code'        => 'EEO4',
									'form_name'        => TTi18n::getText( 'EEO-4 Filing' ),
									'form_description' => TTi18n::getText( 'EEO-4 Filing' ),
									'note'             => '',
									'tax_codes'        => [ '' ],
									'filing_methods'   => [ 'EFILE' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [ //Annual
															[
																	'status_id'            => 20, //Disabled
																	'frequency_id'         => 2000, //Annual
																	'primary_month'        => 4, //Apr - This seems to change every year, so pick a early date.
																	'primary_day_of_month' => 30,
																	'reminder_days'        => 14,
															],
									],
							],
	],

	//AL - Alabama
	'20:US:AL:00:0010' => [ //State Government [State Income Tax]
							'FW2' => [
									'form_code'        => 'W2', //Includes W3
									'form_name'        => TTi18n::getText( 'W2 State Filing' ),
									'form_description' => TTi18n::getText( 'Wage and Tax Statement' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'EFILE' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Annual - By January 31.
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 1, //Jan
																	 'primary_day_of_month' => 31,
																	 'reminder_days'        => 14,
															 ],
									],
							],
							'A1'  => [
									'form_code'        => 'A1',
									'form_name'        => TTi18n::getText( 'A-1 Filing' ),
									'form_description' => TTi18n::getText( 'Employer\'s Quarterly Return of Income Tax Withheld' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME' ],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [ 'CHECK' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Quarterly - Due the last day of the month following the end of the quarter. (April 30, July 31, October 31, and January 31).
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 3000, //Quarterly
																	 'quarter_month'        => 1,
																	 'primary_day_of_month' => 31, //Last day
																	 'reminder_days'        => 14,
															 ],
									],
							],
							'A3'  => [
									'form_code'        => 'A3',
									'form_name'        => TTi18n::getText( 'A-3 Filing' ),
									'form_description' => TTi18n::getText( 'Annual Reconciliation of Income Tax Withheld' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME' ],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [ 'CHECK' ],
									'flags'            => [
											'include_w2'          => true,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Annual - Due on January 31st. An A-3 must be submitted with a copy of the Form W-2.
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 1, //Jan
																	 'primary_day_of_month' => 31,
																	 'reminder_days'        => 14,
															 ],
									],
							],
							'A6'  => [
									'form_code'        => 'A6',
									'form_name'        => TTi18n::getText( 'A-6 Payment' ),
									'form_description' => TTi18n::getText( 'Employer\'s Monthly Return of Income Tax Withheld' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME' ],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [ 'CHECK' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Monthly - Due the 15th of the following month.
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 4100, //Monthly
																	 'primary_day_of_month' => 15,
																	 'reminder_days'        => 7,
															 ],
									],
							],
	],
	'20:US:AL:00:0020' => [ //State Government [Unemployment Insurance]
							'UCCR4' => [
									'form_code'        => 'UC-CR4',
									'form_name'        => TTi18n::getText( 'UC-CR4 and UC-CR4A Filing' ),
									'form_description' => TTi18n::getText( 'Quarterly Contribution & Wage Report' ),
									'note'             => '',
									'tax_codes'        => [ 'UI' ],
									'filing_methods'   => [ 'EFILE' ],
									'payment_methods'  => [ 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Quarterly - Due the last day of the month following the end of the quarter. (April 30, July 31, October 31, and January 31)
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 3000, //Quarterly
																	 'quarter_month'        => 1,
																	 'primary_day_of_month' => 31, //Last day
																	 'reminder_days'        => 14,
															 ],
									],
							],
	],
	'20:US:AL:00:0030' => [ //State Government [New Hires]
							'NEWHIRE' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'New Hire Report Filing' ),
									'form_description' => TTi18n::getText( 'Report of New Hires' ),
									'note'             => '',
									'tax_codes'        => [],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //As Required, - Due no later than 7 days after the employee is hired, rehired or returns to work. Employers filing electronically may transmit twice monthly, not less than twelve (12) days or more than sixteen (16) days apart.
															 //On Hire
															 [
																	 'status_id'           => 10, //Enabled
																	 'frequency_id'        => 90100, //On Hire
																	 'due_date_delay_days' => 16,
																	 'reminder_days'       => 0,
															 ],
									],
							],
	],
	'20:US:AL:00:0040' => [ //Child Support
							'CHILDSUPPORT' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'Child Support Withholding Payment' ),
									'form_description' => TTi18n::getText( 'Child Support Withholding Payment' ),
									'note'             => '',
									'tax_codes'        => [ 'CHILDSUPPORT' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Per Pay Period
										[
												'status_id'           => 10, //Enabled
												'frequency_id'        => 1000, //Per Pay Period
												'due_date_delay_days' => 14,
												'reminder_days'       => 5,
										],
									],
							],
	],

	//AK - Alaska
	'20:US:AK:00:0010' => [ //State Government [State Income Tax]
	],
	'20:US:AK:00:0020' => [ //State Government [Unemployment Insurance]
							'TQ01C' => [
									'form_code'        => 'TQ01C',
									'form_name'        => TTi18n::getText( 'TQ01C Filing' ),
									'form_description' => TTi18n::getText( 'Quarterly Contribution Report' ),
									'note'             => '',
									'tax_codes'        => [ 'UI' ],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Quarterly - Due the last day of the month following the end of the quarter. (April 30, July 31, October 31, and January 31)
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 3000, //Quarterly
																	 'quarter_month'        => 1,
																	 'primary_day_of_month' => 31, //Last day
																	 'reminder_days'        => 14,
															 ],
									],
							],
	],
	'20:US:AK:00:0030' => [ //State Government [New Hires]
							'NEWHIRE' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'New Hire Report Filing' ),
									'form_description' => TTi18n::getText( 'Report of New Hires' ),
									'note'             => '',
									'tax_codes'        => [],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [ 'CHECK' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //As Required, within 20 days of hire date.
															 //On Hire
															 [
																	 'status_id'           => 10, //Enabled
																	 'frequency_id'        => 90100, //On Hire
																	 'due_date_delay_days' => 20,
																	 'reminder_days'       => 0,
															 ],

									],
							],
	],
	'20:US:AK:00:0040' => [ //Child Support
							'CHILDSUPPORT' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'Child Support Withholding Payment' ),
									'form_description' => TTi18n::getText( 'Child Support Withholding Payment' ),
									'note'             => '',
									'tax_codes'        => [ 'CHILDSUPPORT' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Per Pay Period
										[
												'status_id'           => 10, //Enabled
												'frequency_id'        => 1000, //Per Pay Period
												'due_date_delay_days' => 14,
												'reminder_days'       => 5,
										],
									],
							],
	],


	//AR - Arkansas
	'20:US:AR:00:0010' => [ //State Government [State Income Tax]
							'FW2'    => [
									'form_code'        => 'W2', //Includes W3
									'form_name'        => TTi18n::getText( 'W2 State Filing' ),
									'form_description' => TTi18n::getText( 'Wage and Tax Statement' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'EFILE' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Annual
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 1, //Jan
																	 'primary_day_of_month' => 31,
																	 'reminder_days'        => 14,
															 ],
									],
							],
							'AR941M' => [
									'form_code'        => 'AR-941M',
									'form_name'        => TTi18n::getText( 'AR-941M Payment' ),
									'form_description' => TTi18n::getText( 'Monthly Withholding Report' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Monthly
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 4100, //Monthly
																	 'primary_day_of_month' => 15,
																	 'reminder_days'        => 7,
															 ],
									],
							],
							'AR941A' => [
									'form_code'        => 'AR-941A',
									'form_name'        => TTi18n::getText( 'AR-941A Payment' ),
									'form_description' => TTi18n::getText( 'Annual Withholding Report' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Monthly
															 [
																	 'status_id'            => 20, //Disabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 1, //Jan
																	 'primary_day_of_month' => 31,
																	 'reminder_days'        => 14,
															 ],
									],
							],

							'AR3MAR' => [
									'form_code'        => 'AR3MAR',
									'form_name'        => TTi18n::getText( 'AR3MAR Filing' ),
									'form_description' => TTi18n::getText( 'Employer\'s Annual Reconciliation Of Income Tax Withheld' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => true,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Annual
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 1, //Jan
																	 'primary_day_of_month' => 31,
																	 'reminder_days'        => 14,
															 ],
									],
							],
	],
	'20:US:AR:00:0020' => [ //State Government [Unemployment Insurance]
							'ESDARK209B' => [
									'form_code'        => 'ESD-ARK-209B',
									'form_name'        => TTi18n::getText( 'ESD-ARK-209B Filing' ),
									'form_description' => TTi18n::getText( 'Employer\'s Quarterly Contribution and Wage Report' ),
									'note'             => '',
									'tax_codes'        => [ 'UI' ],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [ 'CHECK' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Quarterly - Due the last day of the month following the end of the quarter. (April 30, July 31, October 31, and January 31)
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 3000, //Quarterly
																	 'quarter_month'        => 1,
																	 'primary_day_of_month' => 31, //Last day
																	 'reminder_days'        => 14,
															 ],
									],
							],
	],
	'20:US:AR:00:0030' => [ //State Government [New Hires]
							'NEWHIRE' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'New Hire Report Filing' ),
									'form_description' => TTi18n::getText( 'Report of New Hires' ),
									'note'             => '',
									'tax_codes'        => [],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //As Required, within 20 days of hire date.
															 //On Hire
															 [
																	 'status_id'           => 10, //Enabled
																	 'frequency_id'        => 90100, //On Hire
																	 'due_date_delay_days' => 20,
																	 'reminder_days'       => 0,
															 ],

									],
							],
	],
	'20:US:AR:00:0040' => [ //Child Support
							'CHILDSUPPORT' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'Child Support Withholding Payment' ),
									'form_description' => TTi18n::getText( 'Child Support Withholding Payment' ),
									'note'             => '',
									'tax_codes'        => [ 'CHILDSUPPORT' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Per Pay Period
										[
												'status_id'           => 10, //Enabled
												'frequency_id'        => 1000, //Per Pay Period
												'due_date_delay_days' => 14,
												'reminder_days'       => 5,
										],
									],
							],
	],


	//AZ - Arizona
	'20:US:AZ:00:0010' => [ //State Government [State Income Tax]
							'FW2'    => [
									'form_code'        => 'W2', //Includes W3
									'form_name'        => TTi18n::getText( 'W2 State Filing' ),
									'form_description' => TTi18n::getText( 'Wage and Tax Statement' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'EFILE' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Annual
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 1, //Jan
																	 'primary_day_of_month' => 31,
																	 'reminder_days'        => 14,
															 ],
									],
							],
							'A1WP'   => [
									'form_code'        => 'A1-WP',
									'form_name'        => TTi18n::getText( 'A1-WP Payment' ),
									'form_description' => TTi18n::getText( 'Payment of AZ Income Tax Withholding' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Next Banking Day: Due the next banking day.
															 // Semiweekly: Due the following Friday (for pay dates that fall on Saturday through Tuesday). Due the following Wednesday (for pay dates that fall on Wednesday through Friday). Exception: If you have two paydays within one deposit period but the paydays are in separate quarters, you must make two seperate payments.
															 // Monthly: Due the 15th of the following month.

															 //Per Pay Period
															 [
																	 'status_id'           => 20, //Disabled
																	 'frequency_id'        => 1000, //Per Pay Period
																	 'due_date_delay_days' => 1,
																	 'reminder_days'       => 7,
															 ],

															 //US - Semi-Weekly
															 [
																	 'status_id'           => 20, //Disabled
																	 'frequency_id'        => 64000, //US - Semi-Weekly
																	 'due_date_delay_days' => 0,
																	 'reminder_days'       => 1,
															 ],

															 //Monthly
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 4100, //Monthly
																	 'primary_day_of_month' => 15,
																	 'reminder_days'        => 7,
															 ],


									],
							],
							'A1R'    => [
									'form_code'        => 'A1-R',
									'form_name'        => TTi18n::getText( 'A1-R Filing' ),
									'form_description' => TTi18n::getText( 'Withholding Reconciliation Return' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => true,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Annual
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 1, //Jan
																	 'primary_day_of_month' => 31,
																	 'reminder_days'        => 14,
															 ],
									],
							],
							'A1-APR' => [
									'form_code'        => 'A1-APR',
									'form_name'        => TTi18n::getText( 'A1-APR Filing' ),
									'form_description' => TTi18n::getText( 'Annual Payment Withholding Tax Return' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [ 'CHECK' ],
									'flags'            => [
											'include_w2'          => true,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Annual
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 1, //Jan
																	 'primary_day_of_month' => 31,
																	 'reminder_days'        => 14,
															 ],
									],
							],
							'A1-QRT' => [
									'form_code'        => 'A1-QRT',
									'form_name'        => TTi18n::getText( 'A1-QRT Filing' ),
									'form_description' => TTi18n::getText( 'Quarterly Withholding Tax Return' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Quarterly
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 3000, //Quarterly
																	 'quarter_month'        => 1,
																	 'primary_day_of_month' => 31, //Last Day
																	 'reminder_days'        => 7,
															 ],
									],
							],

	],
	'20:US:AZ:00:0020' => [ //State Government [Unemployment Insurance]
							'UC018' => [
									'form_code'        => 'ESD-ARK-209B',
									'form_name'        => TTi18n::getText( 'UC-018/UC-020 Filing' ),
									'form_description' => TTi18n::getText( 'Unemployment Tax and Wage Report' ),
									'note'             => '',
									'tax_codes'        => [ 'UI' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Quarterly - Due the last day of the month following the end of the quarter. (April 30, July 31, October 31, and January 31)
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 3000, //Quarterly
																	 'quarter_month'        => 1,
																	 'primary_day_of_month' => 31, //Last day
																	 'reminder_days'        => 14,
															 ],
									],
							],
	],
	'20:US:AZ:00:0030' => [ //State Government [New Hires]
							'NEWHIRE' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'New Hire Report Filing' ),
									'form_description' => TTi18n::getText( 'Report of New Hires' ),
									'note'             => '',
									'tax_codes'        => [],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //As Required, within 20 days of hire date.
															 //On Hire
															 [
																	 'status_id'           => 10, //Enabled
																	 'frequency_id'        => 90100, //On Hire
																	 'due_date_delay_days' => 20,
																	 'reminder_days'       => 0,
															 ],

									],
							],
	],
	'20:US:AZ:00:0040' => [ //Child Support
							'CHILDSUPPORT' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'Child Support Withholding Payment' ),
									'form_description' => TTi18n::getText( 'Child Support Withholding Payment' ),
									'note'             => '',
									'tax_codes'        => [ 'CHILDSUPPORT' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Per Pay Period
										[
												'status_id'           => 10, //Enabled
												'frequency_id'        => 1000, //Per Pay Period
												'due_date_delay_days' => 14,
												'reminder_days'       => 5,
										],
									],
							],
	],


	//CA - California
	'20:US:CA:00:0010' => [ //State Government [State Income Tax]
							'DE9'  => [
									'form_code'        => 'DE 9',
									'form_name'        => TTi18n::getText( 'DE 9/DE 9C Filing' ),
									'form_description' => TTi18n::getText( 'Quarterly Contribution Return and Report of Wages' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE', 'UI' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ '' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Quarterly
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 3000, //Quarterly
																	 'quarter_month'        => 1,
																	 'primary_day_of_month' => 31, //Last day
																	 'reminder_days'        => 14,
															 ],
									],
							],
							'DE88' => [
									'form_code'        => 'DE88',
									'form_name'        => TTi18n::getText( 'DE88 Payment' ),
									'form_description' => TTi18n::getText( 'Payroll Tax Deposit Coupon' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE', 'UI' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Next Day: Due on the next banking day.
										//Semiweekly: Due the following Friday (for pay dates that fall on Saturday through Tuesday). Due the following Wednesday (for pay dates that fall on Wednesday through Friday).
										//Monthly: Due on the 15th of the following month.
										//Quarterly: Due the last day of the month following the end of the quarter. (April 30, July 31, October 31, and January 31)

										//Per Pay Period
										[
												'status_id'           => 20, //Disabled
												'frequency_id'        => 1000, //Per Pay Period
												'due_date_delay_days' => 1,
												'reminder_days'       => 7,
										],

										//US - Semi-Weekly
										[
												'status_id'           => 20, //Disabled
												'frequency_id'        => 64000, //US - Semi-Weekly
												'due_date_delay_days' => 0,
												'reminder_days'       => 1,
										],

										//Monthly
										[
												'status_id'            => 10, //Enabled
												'frequency_id'         => 4100, //Monthly
												'primary_day_of_month' => 15,
												'reminder_days'        => 7,
										],

										//Quarterly
										[
												'status_id'            => 20, //Enabled
												'frequency_id'         => 3000, //Quarterly
												'quarter_month'        => 1,
												'primary_day_of_month' => 31, //Last day
												'reminder_days'        => 14,
										],
									],
							],
	],
	'20:US:CA:00:0020' => [ //State Government [Unemployment Insurance]
	],
	'20:US:CA:00:0030' => [ //State Government [New Hires]
							'NEWHIRE' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'New Hire Report Filing' ),
									'form_description' => TTi18n::getText( 'Report of New Hires' ),
									'note'             => '',
									'tax_codes'        => [],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //As Required, within 20 days of hire date.
															 //On Hire
															 [
																	 'status_id'           => 10, //Enabled
																	 'frequency_id'        => 90100, //On Hire
																	 'due_date_delay_days' => 20,
																	 'reminder_days'       => 0,
															 ],

									],
							],

	],
	'20:US:CA:00:0040' => [ //Child Support
							'CHILDSUPPORT' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'Child Support Withholding Payment' ),
									'form_description' => TTi18n::getText( 'Child Support Withholding Payment' ),
									'note'             => '',
									'tax_codes'        => [ 'CHILDSUPPORT' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Per Pay Period
										[
												'status_id'           => 10, //Enabled
												'frequency_id'        => 1000, //Per Pay Period
												'due_date_delay_days' => 14,
												'reminder_days'       => 5,
										],
									],
							],
	],
	'20:US:CA:00:0050' => [ //State Government [Retirement]
							'RETIREMENT' => [
									'form_code'        => 'RETIREMENT',
									'form_name'        => TTi18n::getText( 'Retirement' ),
									'form_description' => TTi18n::getText( 'Employer\'s Monthly Report' ),
									'note'             => '',
									'tax_codes'        => [ 'RT' ],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [ 'CHECK' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Monthly
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 4100, //Monthly
																	 'primary_day_of_month' => 5, //5th working day of month following the end of the month
																	 'reminder_days'        => 5,
															 ],
									],
							],
	],
	'20:US:CA:00:0110' => [ //State Government - Civil Rights Department
							'EEOCA' => [
									'form_code'        => 'EEOCA',
									'form_name'        => TTi18n::getText( 'EEO Filing' ),
									'form_description' => TTi18n::getText( 'EEO Filing' ),
									'note'             => '',
									'tax_codes'        => [ '' ],
									'filing_methods'   => [ 'EFILE' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [ //Annual
															[
																	'status_id'            => 10, //Enabled
																	'frequency_id'         => 2000, //Annual
																	'primary_month'        => 4, //Apr - This seems to change every year, so pick a early date.
																	'primary_day_of_month' => 30,
																	'reminder_days'        => 14,
															],
									],
							],
	],

	//CO - Colorado
	'20:US:CO:00:0010' => [ //State Government [State Income Tax]
							'FW2'    => [
									'form_code'        => 'W2', //Includes W3
									'form_name'        => TTi18n::getText( 'W2 State Filing' ), //File with DR1093.
									'form_description' => TTi18n::getText( 'Wage and Tax Statement' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'EFILE' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Annual
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 1, //Jan
																	 'primary_day_of_month' => 31,
																	 'reminder_days'        => 14,
															 ],
									],
							],
							'DR1094' => [
									'form_code'        => 'DR1094',
									'form_name'        => TTi18n::getText( 'DR1094 Payment' ),
									'form_description' => TTi18n::getText( 'Income Withholding Tax Return' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Weekly: Remit any Colorado withholding taxes accumulated as of any Friday on or before the third business day following that Friday.
										//Monthly: Due the 15th day of the following month.
										//Quarterly: Due the last day of the month following the end of the quarter. (April 30, July 31, October 31, and January 31)
										//Seasonal: Due the 15th day of the month following the operating month.
										//DR 1094: This form covers both the filing and the payment if your assigned payment schedule is monthly, quarterly or seasonal.

										//Weekly
										[
												'status_id'           => 20, //Disabled
												'frequency_id'        => 5100, //Weekly
												'day_of_week'         => 3, //Wed
												'due_date_delay_days' => 0,
												'reminder_days'       => 2,
										],

										//Monthly
										[
												'status_id'            => 10, //Enabled
												'frequency_id'         => 4100, //Monthly
												'primary_day_of_month' => 15,
												'reminder_days'        => 7,
										],

										//Quarterly
										[
												'status_id'            => 20, //Disabled
												'frequency_id'         => 3000, //Quarterly
												'quarter_month'        => 1,
												'primary_day_of_month' => 31, //Last day
												'reminder_days'        => 14,
										],
									],
							],
							'DR1107' => [
									'form_code'        => 'DR1107',
									'form_name'        => TTi18n::getText( 'DR1107 Payment' ),
									'form_description' => TTi18n::getText( '1099 Income Tax Withholding Tax Return' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Weekly: Remit any Colorado withholding taxes accumulated as of any Friday on or before the third business day following that Friday.
										//Monthly: Due the 15th day of the following month.
										//Quarterly: Due the last day of the month following the end of the quarter. (April 30, July 31, October 31, and January 31)
										//Disable them all by default as 1099's aren't employees and not that common.

										//Weekly
										[
												'status_id'           => 20, //Disabled
												'frequency_id'        => 5100, //Weekly
												'day_of_week'         => 3, //Wed
												'due_date_delay_days' => 0,
												'reminder_days'       => 2,
										],

										//Monthly
										[
												'status_id'            => 20, //Disabled
												'frequency_id'         => 4100, //Monthly
												'primary_day_of_month' => 15,
												'reminder_days'        => 7,
										],

										//Quarterly
										[
												'status_id'            => 20, //Disabled
												'frequency_id'         => 3000, //Quarterly
												'quarter_month'        => 1,
												'primary_day_of_month' => 31, //Last day
												'reminder_days'        => 14,
										],
									],
							],
	],
	'20:US:CO:00:0020' => [ //State Government [Unemployment Insurance]
							'UITR1' => [
									'form_code'        => 'UITR-1',
									'form_name'        => TTi18n::getText( 'UITR-1/UITR-1A Filing' ),
									'form_description' => TTi18n::getText( 'Unemployment Insurance Tax Report' ),
									'note'             => '',
									'tax_codes'        => [ 'UI' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Quarterly - Due the last day of the month following the end of the quarter. (April 30, July 31, October 31, and January 31)
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 3000, //Quarterly
																	 'quarter_month'        => 1,
																	 'primary_day_of_month' => 31, //Last day
																	 'reminder_days'        => 14,
															 ],
									],
							],
	],
	'20:US:CO:00:0030' => [ //State Government [New Hires]
							'NEWHIRE' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'New Hire Report Filing' ),
									'form_description' => TTi18n::getText( 'Report of New Hires' ),
									'note'             => '',
									'tax_codes'        => [],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //As Required, within 20 days of hire date.
															 //On Hire
															 [
																	 'status_id'           => 10, //Enabled
																	 'frequency_id'        => 90100, //On Hire
																	 'due_date_delay_days' => 20,
																	 'reminder_days'       => 0,
															 ],
									],
							],

	],
	'20:US:CO:00:0040' => [ //Child Support
							'CHILDSUPPORT' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'Child Support Withholding Payment' ),
									'form_description' => TTi18n::getText( 'Child Support Withholding Payment' ),
									'note'             => '',
									'tax_codes'        => [ 'CHILDSUPPORT' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Per Pay Period
										[
												'status_id'           => 10, //Enabled
												'frequency_id'        => 1000, //Per Pay Period
												'due_date_delay_days' => 14,
												'reminder_days'       => 5,
										],
									],
							],
	],

	//CT - Connecticut
	'20:US:CT:00:0010' => [ //State Government [State Income Tax]
							'FW2'     => [
									'form_code'        => 'W2', //Includes W3
									'form_name'        => TTi18n::getText( 'W2 State Filing' ),
									'form_description' => TTi18n::getText( 'Wage and Tax Statement' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'EFILE' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Annual
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 1, //Jan
																	 'primary_day_of_month' => 31,
																	 'reminder_days'        => 14,
															 ],
									],
							],
							'CT941'   => [
									'form_code'        => 'CT-941',
									'form_name'        => TTi18n::getText( 'CT-941 Filing' ),
									'form_description' => TTi18n::getText( 'Quarterly Withholding Reconciliation' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Quarterly
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 3000, //Quarterly
																	 'quarter_month'        => 1,
																	 'primary_day_of_month' => 31, //Last day
																	 'reminder_days'        => 14,
															 ],
									],
							],
							'PAYMENT' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'CT-Withholding Payment' ),
									'form_description' => TTi18n::getText( 'CT Withholding Payment' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Weekly: Due on or before the Wednesday following the weekly period during which the wages were paid.
										//Monthly: Due the 15th day of the following month.

										//Weekly
										[
												'status_id'           => 20, //Disabled
												'frequency_id'        => 5100, //Weekly
												'day_of_week'         => 3,//Wed
												'due_date_delay_days' => 0,
												'reminder_days'       => 1,
										],

										//Monthly
										[
												'status_id'            => 10, //Enabled
												'frequency_id'         => 4100, //Monthly
												'primary_day_of_month' => 15,
												'reminder_days'        => 7,
										],
									],
							],
							'CTW3'    => [
									'form_code'        => 'CT-W3',
									'form_name'        => TTi18n::getText( 'CT-W3 Filing' ),
									'form_description' => TTi18n::getText( 'Annual Reconciliation of Withholding' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'EFILE' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => true,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Annual
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 1, //Jan
																	 'primary_day_of_month' => 31,
																	 'reminder_days'        => 14,
															 ],
									],
							],
	],
	'20:US:CT:00:0020' => [ //State Government [Unemployment Insurance]
							'UC2' => [
									'form_code'        => 'UC-2',
									'form_name'        => TTi18n::getText( 'UC-2 Filing' ),
									'form_description' => TTi18n::getText( 'Employer Contribution Return (Electronic Only)' ),
									'note'             => '',
									'tax_codes'        => [ 'UI' ],
									'filing_methods'   => [ 'EFILE' ],
									'payment_methods'  => [ 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Quarterly - Due the last day of the month following the end of the quarter. (April 30, July 31, October 31, and January 31)
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 3000, //Quarterly
																	 'quarter_month'        => 1,
																	 'primary_day_of_month' => 31, //Last day
																	 'reminder_days'        => 14,
															 ],
									],
							],
	],
	'20:US:CT:00:0030' => [ //State Government [New Hires]
							'NEWHIRE' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'New Hire Report Filing' ),
									'form_description' => TTi18n::getText( 'Report of New Hires' ),
									'note'             => '',
									'tax_codes'        => [],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //As Required, within 20 days of hire date.
															 //On Hire
															 [
																	 'status_id'           => 10, //Enabled
																	 'frequency_id'        => 90100, //On Hire
																	 'due_date_delay_days' => 20,
																	 'reminder_days'       => 0,
															 ],
									],
							],

	],
	'20:US:CT:00:0040' => [ //Child Support
							'CHILDSUPPORT' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'Child Support Withholding Payment' ),
									'form_description' => TTi18n::getText( 'Child Support Withholding Payment' ),
									'note'             => '',
									'tax_codes'        => [ 'CHILDSUPPORT' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Per Pay Period
										[
												'status_id'           => 10, //Enabled
												'frequency_id'        => 1000, //Per Pay Period
												'due_date_delay_days' => 14,
												'reminder_days'       => 5,
										],
									],
							],
	],

	//DC - D.C.
	'20:US:DC:00:0010' => [ //State Government [State Income Tax]
							'FW2'    => [
									'form_code'        => 'W2', //Includes W3
									'form_name'        => TTi18n::getText( 'W2 State Filing' ),
									'form_description' => TTi18n::getText( 'Wage and Tax Statement' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'EFILE' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Annual
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 1, //Jan
																	 'primary_day_of_month' => 31,
																	 'reminder_days'        => 14,
															 ],
									],
							],
							'FR900P' => [
									'form_code'        => 'FR 900P',
									'form_name'        => TTi18n::getText( 'FR 900P Payment' ),
									'form_description' => TTi18n::getText( 'Payment Voucher for Withholding Tax' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [ 'CHECK' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Monthly - Due on or before the 20th of the following month
										//Quarterly - Due on or before the 20th of month following the end of the quarter

										//Monthly
										[
												'status_id'            => 10, //Enabled
												'frequency_id'         => 4100, //Monthly
												'primary_day_of_month' => 20,
												'reminder_days'        => 7,
										],

										//Quarterly
										[
												'status_id'            => 20, //Disabled
												'frequency_id'         => 3000, //Quarterly
												'quarter_month'        => 1,
												'primary_day_of_month' => 20,
												'reminder_days'        => 14,
										],
									],
							],
							'FR900Q' => [
									'form_code'        => 'FR 900Q',
									'form_name'        => TTi18n::getText( 'FR 900Q Filing' ),
									'form_description' => TTi18n::getText( 'Employer\'s Withholding Tax - Quarterly Return' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [ 'CHECK' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Quarterly
										[
												'status_id'            => 10, //Enabled
												'frequency_id'         => 3000, //Quarterly
												'quarter_month'        => 1,
												'primary_day_of_month' => 31, //Last day
												'reminder_days'        => 14,
										],
									],
							],
							'FR900A' => [
									'form_code'        => 'FR 900A',
									'form_name'        => TTi18n::getText( 'FR 900A Filing' ),
									'form_description' => TTi18n::getText( 'Employer\'s Withholding Tax - Annual Return' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [ 'CHECK' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Annual
										[
												'status_id'            => 20, //Disabled (Use FR900Q by default)
												'frequency_id'         => 2000, //Annual
												'primary_month'        => 1, //Jan
												'primary_day_of_month' => 31,
												'reminder_days'        => 14,
										],
									],
							],

	],
	'20:US:DC:00:0020' => [ //State Government [Unemployment Insurance]
							'DOESUC30' => [
									'form_code'        => 'DOES-UC30',
									'form_name'        => TTi18n::getText( 'DOES-UC30 Filing' ),
									'form_description' => TTi18n::getText( 'Employer\'s Quarterly Contribution and Wage Report' ),
									'note'             => '',
									'tax_codes'        => [ 'UI' ],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [ 'CHECK' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Quarterly - Due the last day of the month following the end of the quarter. (April 30, July 31, October 31, and January 31)
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 3000, //Quarterly
																	 'quarter_month'        => 1,
																	 'primary_day_of_month' => 31, //Last day
																	 'reminder_days'        => 14,
															 ],
									],
							],
	],
	'20:US:DC:00:0030' => [ //State Government [New Hires]
							'NEWHIRE' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'New Hire Report Filing' ),
									'form_description' => TTi18n::getText( 'Report of New Hires' ),
									'note'             => '',
									'tax_codes'        => [],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //As Required, within 20 days of hire date.
															 //On Hire
															 [
																	 'status_id'           => 10, //Enabled
																	 'frequency_id'        => 90100, //On Hire
																	 'due_date_delay_days' => 20,
																	 'reminder_days'       => 0,
															 ],
									],
							],

	],
	'20:US:DC:00:0040' => [ //Child Support
							'CHILDSUPPORT' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'Child Support Withholding Payment' ),
									'form_description' => TTi18n::getText( 'Child Support Withholding Payment' ),
									'note'             => '',
									'tax_codes'        => [ 'CHILDSUPPORT' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Per Pay Period
										[
												'status_id'           => 10, //Enabled
												'frequency_id'        => 1000, //Per Pay Period
												'due_date_delay_days' => 14,
												'reminder_days'       => 5,
										],
									],
							],
	],

	//DE - Delaware
	'20:US:DE:00:0010' => [ //State Government [State Income Tax]
							'FW2' => [
									'form_code'        => 'W2', //Includes W3
									'form_name'        => TTi18n::getText( 'W2 State Filing' ),
									'form_description' => TTi18n::getText( 'Wage and Tax Statement' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'EFILE' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Annual
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 1, //Jan
																	 'primary_day_of_month' => 31,
																	 'reminder_days'        => 14,
															 ],
									],
							],
							//								 'W3'  => array( //Not required when efiling. Can just be appended to state W2's too.
							//										 'form_code'        => 'W-3',
							//										 'form_name'        => TTi18n::getText( 'W-3 Filing' ),
							//										 'form_description' => TTi18n::getText( 'Annual Reconciliation of Income Tax Withheld' ),
							//										 'note'             => '',
							//										 'tax_codes'        => array('INCOME', 'SS', 'MEDICARE'),
							//										 'filing_methods'   => array('PRINT'),
							//										 'payment_methods'  => array('CHECK'),
							//										 'flags'            => array(
							//												 'include_w2'          => TRUE,
							//												 'file_zero_wage'      => TRUE,
							//												 'file_zero_liability' => TRUE,
							//												 'auto_file'           => FALSE,
							//												 'auto_pay'            => FALSE,
							//										 ),
							//										 'frequency'        => array(  //Annual
							//																	   array(
							//																			   'status_id'            => 10, //Enabled
							//																			   'frequency_id'         => 2000, //Annual
							//																			   'primary_month'        => 1, //Jan
							//																			   'primary_day_of_month' => 31,
							//																			   'reminder_days'        => 14,
							//																	   ),
							//										 ),
							//								 ),
							'W1'  => [
									'form_code'        => 'W-1',
									'form_name'        => TTi18n::getText( 'W-1 Payment' ),
									'form_description' => TTi18n::getText( 'Monthly Tax Return' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [ 'CHECK' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Monthly
										[
												'status_id'            => 10, //Enabled
												'frequency_id'         => 4100, //Monthly
												'primary_day_of_month' => 15,
												'reminder_days'        => 7,
										],
									],
							],
							'W1Q' => [
									'form_code'        => 'W-1Q',
									'form_name'        => TTi18n::getText( 'W-1Q Payment' ),
									'form_description' => TTi18n::getText( 'Quarterly Tax Return' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [ 'CHECK' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Quarterly
										[
												'status_id'            => 20, //Disabled
												'frequency_id'         => 3000, //Quarterly
												'quarter_month'        => 1,
												'primary_day_of_month' => 31, //Last day
												'reminder_days'        => 14,
										],
									],
							],
							'W1A' => [
									'form_code'        => 'W-1A',
									'form_name'        => TTi18n::getText( 'W-1A Payment' ),
									'form_description' => TTi18n::getText( 'Eighth Monthly Tax Return (File and Pay)' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [ 'CHECK' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Due within 3 days after the appropriate tax periods. The tax periods end on the 3rd, 7th, 11th, 15th, 19th, 22nd, 25th and the last day of the month.

										//Eighth Monthly
										[
												'status_id'     => 20, //Disabled
												'frequency_id'  => 63000, //Eighth Monthly
												'reminder_days' => 2,
										],
									],
							],
	],
	'20:US:DE:00:0020' => [ //State Government [Unemployment Insurance]
							'DEUC8' => [
									'form_code'        => 'DEUC8',
									'form_name'        => TTi18n::getText( 'DEUC-8 Filing' ),
									'form_description' => TTi18n::getText( 'Quarterly Contribution Return and Report of Wages' ),
									'note'             => '',
									'tax_codes'        => [ 'UI' ],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [ 'CHECK' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Quarterly - Due the last day of the month following the end of the quarter. (April 30, July 31, October 31, and January 31)
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 3000, //Quarterly
																	 'quarter_month'        => 1,
																	 'primary_day_of_month' => 31, //Last day
																	 'reminder_days'        => 14,
															 ],
									],
							],
	],
	'20:US:DE:00:0030' => [ //State Government [New Hires]
							'NEWHIRE' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'New Hire Report Filing' ),
									'form_description' => TTi18n::getText( 'Report of New Hires' ),
									'note'             => '',
									'tax_codes'        => [],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //As Required, within 20 days of hire date.
															 //On Hire
															 [
																	 'status_id'           => 10, //Enabled
																	 'frequency_id'        => 90100, //On Hire
																	 'due_date_delay_days' => 20,
																	 'reminder_days'       => 0,
															 ],
									],
							],

	],
	'20:US:DE:00:0040' => [ //Child Support
							'CHILDSUPPORT' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'Child Support Withholding Payment' ),
									'form_description' => TTi18n::getText( 'Child Support Withholding Payment' ),
									'note'             => '',
									'tax_codes'        => [ 'CHILDSUPPORT' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Per Pay Period
										[
												'status_id'           => 10, //Enabled
												'frequency_id'        => 1000, //Per Pay Period
												'due_date_delay_days' => 14,
												'reminder_days'       => 5,
										],
									],
							],
	],

	//FL - Florida
	'20:US:FL:00:0010' => [ //State Government [State Income Tax]
	],
	'20:US:FL:00:0020' => [ //State Government [Unemployment Insurance]
							'RT6' => [
									'form_code'        => 'RT-6',
									'form_name'        => TTi18n::getText( 'RT-6 Filing' ),
									'form_description' => TTi18n::getText( 'Employer\'s Quarterly Report' ),
									'note'             => '',
									'tax_codes'        => [ 'UI' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Quarterly - Due the last day of the month following the end of the quarter. (April 30, July 31, October 31, and January 31)
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 3000, //Quarterly
																	 'quarter_month'        => 1,
																	 'primary_day_of_month' => 31, //Last day
																	 'reminder_days'        => 14,
															 ],
									],
							],
	],
	'20:US:FL:00:0030' => [ //State Government [New Hires]
							'NEWHIRE' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'New Hire Report Filing' ),
									'form_description' => TTi18n::getText( 'Report of New Hires' ),
									'note'             => '',
									'tax_codes'        => [],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //As Required, within 20 days of hire date.
															 //On Hire
															 [
																	 'status_id'           => 10, //Enabled
																	 'frequency_id'        => 90100, //On Hire
																	 'due_date_delay_days' => 20,
																	 'reminder_days'       => 0,
															 ],
									],
							],

	],
	'20:US:FL:00:0040' => [ //Child Support
							'CHILDSUPPORT' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'Child Support Withholding Payment' ),
									'form_description' => TTi18n::getText( 'Child Support Withholding Payment' ),
									'note'             => '',
									'tax_codes'        => [ 'CHILDSUPPORT' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Per Pay Period
										[
												'status_id'           => 10, //Enabled
												'frequency_id'        => 1000, //Per Pay Period
												'due_date_delay_days' => 14,
												'reminder_days'       => 5,
										],
									],
							],
	],

	//GA - Georgia
	'20:US:GA:00:0010' => [ //State Government [State Income Tax]
							'FW2'       => [
									'form_code'        => 'W2', //Includes W3
									'form_name'        => TTi18n::getText( 'W2 State Filing' ),
									'form_description' => TTi18n::getText( 'Wage and Tax Statement' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'EFILE' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Annual
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 1, //Jan
																	 'primary_day_of_month' => 31,
																	 'reminder_days'        => 14,
															 ],
									],
							],
							'G7Q'       => [
									'form_code'        => 'G-7',
									'form_name'        => TTi18n::getText( 'G-7 (Quarterly) Filing' ),
									'form_description' => TTi18n::getText( 'Quarterly Return for Quarterly Payers' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Quarterly
															 [
																	 'status_id'            => 20, //Disabled
																	 'frequency_id'         => 3000, //Quarterly
																	 'quarter_month'        => 1,
																	 'primary_day_of_month' => 31, //Last day
																	 'reminder_days'        => 14,
															 ],
									],
							],
							'G7M'       => [
									'form_code'        => 'G-7',
									'form_name'        => TTi18n::getText( 'G-7 (Monthly) Filing' ),
									'form_description' => TTi18n::getText( 'Quarterly Return for Monthly Payers' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Quarterly
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 3000, //Quarterly
																	 'quarter_month'        => 1,
																	 'primary_day_of_month' => 31, //Last day
																	 'reminder_days'        => 14,
															 ],
									],
							],
							'GAV'       => [
									'form_code'        => 'GA-V',
									'form_name'        => TTi18n::getText( 'GA-V Payment' ),
									'form_description' => TTi18n::getText( 'Withholding Payment Voucher' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Monthly
										[
												'status_id'            => 10, //Enabled
												'frequency_id'         => 4100, //Monthly
												'primary_day_of_month' => 15,
												'reminder_days'        => 7,
										],
									],
							],
							'G7'        => [
									'form_code'        => 'G-7',
									'form_name'        => TTi18n::getText( 'G-7 (Semi-Weekly) Filing' ),
									'form_description' => TTi18n::getText( 'Quarterly Return for Semi-Weekly Payer' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'EFILE' ],
									'payment_methods'  => [ 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Quarterly
															 [
																	 'status_id'            => 20, //Disabled
																	 'frequency_id'         => 3000, //Quarterly
																	 'quarter_month'        => 1,
																	 'primary_day_of_month' => 31, //Last day
																	 'reminder_days'        => 14,
															 ],
									],
							],
							'G7PAYMENT' => [
									'form_code'        => 'G-7',
									'form_name'        => TTi18n::getText( 'G-7 (Semi-Weekly) Payment' ),
									'form_description' => TTi18n::getText( 'G-7 Semi-Weekly Payment' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'EFILE' ],
									'payment_methods'  => [ 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Semi-Weekly
										[
												'status_id'     => 20, //Disabled
												'frequency_id'  => 64000, //Semi-Weekly
												'reminder_days' => 2,
										],
									],
							],
	],
	'20:US:GA:00:0020' => [ //State Government [Unemployment Insurance]
							'DOL4' => [
									'form_code'        => 'DOL-4',
									'form_name'        => TTi18n::getText( 'DOL-4N Filing' ),
									'form_description' => TTi18n::getText( 'Employer\'s Quarterly Tax and Wage Report' ),
									'note'             => '',
									'tax_codes'        => [ 'UI' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Quarterly - Due the last day of the month following the end of the quarter. (April 30, July 31, October 31, and January 31)
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 3000, //Quarterly
																	 'quarter_month'        => 1,
																	 'primary_day_of_month' => 31, //Last day
																	 'reminder_days'        => 14,
															 ],
									],
							],
	],
	'20:US:GA:00:0030' => [ //State Government [New Hires]
							'NEWHIRE' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'New Hire Report Filing' ),
									'form_description' => TTi18n::getText( 'Report of New Hires' ),
									'note'             => '',
									'tax_codes'        => [],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //As Required, within 20 days of hire date.
															 //On Hire
															 [
																	 'status_id'           => 10, //Enabled
																	 'frequency_id'        => 90100, //On Hire
																	 'due_date_delay_days' => 20,
																	 'reminder_days'       => 0,
															 ],
									],
							],

	],
	'20:US:GA:00:0040' => [ //Child Support
							'CHILDSUPPORT' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'Child Support Withholding Payment' ),
									'form_description' => TTi18n::getText( 'Child Support Withholding Payment' ),
									'note'             => '',
									'tax_codes'        => [ 'CHILDSUPPORT' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Per Pay Period
										[
												'status_id'           => 10, //Enabled
												'frequency_id'        => 1000, //Per Pay Period
												'due_date_delay_days' => 14,
												'reminder_days'       => 5,
										],
									],
							],
	],

	//HI - Hawaii
	'20:US:HI:00:0010' => [ //State Government [State Income Tax]
							'FW2' => [
									'form_code'        => 'W2', //Includes W3
									'form_name'        => TTi18n::getText( 'W2 State Filing' ),
									'form_description' => TTi18n::getText( 'Wage and Tax Statement' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'EFILE' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Annual
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 1, //Jan
																	 'primary_day_of_month' => 31,
																	 'reminder_days'        => 14,
															 ],
									],
							],
							'HW3' => [
									'form_code'        => 'HW-3',
									'form_name'        => TTi18n::getText( 'HW-3 Filing' ),
									'form_description' => TTi18n::getText( 'Annual Return and Reconciliation' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [ 'CHECK' ],
									'flags'            => [
											'include_w2'          => true,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Annual
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 2, //Feb
																	 'primary_day_of_month' => 29,
																	 'reminder_days'        => 14,
															 ],
									],
							],
							'VP1' => [
									'form_code'        => 'VP-1',
									'form_name'        => TTi18n::getText( 'VP-1 Payment' ),
									'form_description' => TTi18n::getText( 'Tax Payment Voucher' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [ 'CHECK' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Semiweekly: Due the following Friday (for pay dates that fall on Saturday through Tuesday). Due the following Wednesday (for pay dates that fall on Wednesday through Friday). Exception: If you have two paydays within one deposit period but the paydays are in separate quarters, you must make two separate payments.
										//Monthly: Due the 15th of the following month.
										//Quarterly: Due the 15th of the month following the end of the quarter.

										//Semi-Weekly
										[
												'status_id'     => 20, //Disabled
												'frequency_id'  => 64000, //Semi-Weekly
												'reminder_days' => 2,
										],

										//Monthly
										[
												'status_id'            => 20, //Disabled
												'frequency_id'         => 4100, //Monthly
												'primary_day_of_month' => 15,
												'reminder_days'        => 7,
										],

										//Quarterly
										[
												'status_id'            => 10, //Enabled
												'frequency_id'         => 3000, //Quarterly
												'quarter_month'        => 1,
												'primary_day_of_month' => 15,
												'reminder_days'        => 14,
										],
									],
							],

	],
	'20:US:HI:00:0020' => [ //State Government [Unemployment Insurance]
							'UCB6' => [
									'form_code'        => 'UC-B6',
									'form_name'        => TTi18n::getText( 'UC-B6/UC-B6A Filing' ),
									'form_description' => TTi18n::getText( 'Payroll Report to complete HI Quarterly Wage Contribution Report' ),
									'note'             => '',
									'tax_codes'        => [ 'UI' ],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [ 'CHECK' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Quarterly - Due the last day of the month following the end of the quarter. (April 30, July 31, October 31, and January 31)
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 3000, //Quarterly
																	 'quarter_month'        => 1,
																	 'primary_day_of_month' => 31, //Last day
																	 'reminder_days'        => 14,
															 ],
									],
							],
	],
	'20:US:HI:00:0030' => [ //State Government [New Hires]
							'NEWHIRE' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'New Hire Report Filing' ),
									'form_description' => TTi18n::getText( 'Report of New Hires' ),
									'note'             => '',
									'tax_codes'        => [],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //As Required, within 20 days of hire date.
															 //On Hire
															 [
																	 'status_id'           => 10, //Enabled
																	 'frequency_id'        => 90100, //On Hire
																	 'due_date_delay_days' => 20,
																	 'reminder_days'       => 0,
															 ],
									],
							],

	],
	'20:US:HI:00:0040' => [ //Child Support
							'CHILDSUPPORT' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'Child Support Withholding Payment' ),
									'form_description' => TTi18n::getText( 'Child Support Withholding Payment' ),
									'note'             => '',
									'tax_codes'        => [ 'CHILDSUPPORT' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Per Pay Period
										[
												'status_id'           => 10, //Enabled
												'frequency_id'        => 1000, //Per Pay Period
												'due_date_delay_days' => 14,
												'reminder_days'       => 5,
										],
									],
							],
	],

	//IA - Iowa
	'20:US:IA:00:0010' => [ //State Government [State Income Tax]
							'FW2'            => [
									'form_code'        => 'W2', //Includes W3
									'form_name'        => TTi18n::getText( 'W2 State Filing' ),
									'form_description' => TTi18n::getText( 'Wage and Tax Statement' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'EFILE' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Annual
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 1, //Jan
																	 'primary_day_of_month' => 31,
																	 'reminder_days'        => 14,
															 ],
									],
							],
							'PAYMENT+REPORT' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'Employers Withholding Filing' ),
									'form_description' => TTi18n::getText( 'Employer\'s Withholding: Quarterly' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Quarterly
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 3000, //Quarterly
																	 'quarter_month'        => 1,
																	 'primary_day_of_month' => 31, //Last day
																	 'reminder_days'        => 14,
															 ],
									],
							],
							'VSP44007'       => [ //No longer need to be filed as of: 01-Jan-2023 - Must be left here so the dropdown box doesn't appear blank in the Event view.
									'form_code'        => 'VSP (44-007)',
									'form_name'        => TTi18n::getText( 'VSP (44-007) Filing' ),
									'form_description' => TTi18n::getText( 'Annual Verified Summary of Payments Report' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'EFILE' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Annual - No longer need to be filed as of: 01-Jan-2023
															 //[
																//	 'status_id'            => 10, //Enabled
																//	 'frequency_id'         => 2000, //Annual
																//	 'primary_month'        => 1, //Jan
																//	 'primary_day_of_month' => 31,
																//	 'reminder_days'        => 14,
															 //],
									],
							],
							'44105'          => [
									'form_code'        => '44-105',
									'form_name'        => TTi18n::getText( '44-105 Payment' ),
									'form_description' => TTi18n::getText( 'Withholding Payment Voucher' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'EFILE' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Semimonthly: Due the 25th of the month for amounts withheld from the 1st through the 15th. Due the 10th of the following month for amounts withheld from the 16th through the end of the month.
										//Monthly: Due on the 15th of the following month for the 1st and 2nd month of the quarter. Due on the last day of the following month for the 3rd month of the quarter.
										//Quarterly: Due the last day of the month following the end of the quarter.

										//Semi-Monthly
										[
												'status_id'              => 20, //Disabled
												'frequency_id'           => 4200, //Semi-Monthly
												'primary_day_of_month'   => 15,
												'secondary_day_of_month' => 31,
												'due_date_delay_days'    => 10,
												'reminder_days'          => 5,
										],

										//Monthly
										[
												'status_id'     => 20, //Disabled
												'frequency_id'  => 60000, //US - Monthly (15th, 30th on Last MoQ)
												//'primary_day_of_month' => 15,
												'reminder_days' => 7,
										],

										//Quarterly
										[
												'status_id'            => 10, //Enabled
												'frequency_id'         => 3000, //Quarterly
												'quarter_month'        => 1,
												'primary_day_of_month' => 31, //Last day
												'reminder_days'        => 14,
										],
									],
							],
	],
	'20:US:IA:00:0020' => [ //State Government [Unemployment Insurance]
							'655300' => [
									'form_code'        => '65-5300',
									'form_name'        => TTi18n::getText( '65-5300 Filing' ),
									'form_description' => TTi18n::getText( 'Employer\'s Contribution and Payroll Report' ),
									'note'             => '',
									'tax_codes'        => [ 'UI' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Quarterly - Due the last day of the month following the end of the quarter. (April 30, July 31, October 31, and January 31)
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 3000, //Quarterly
																	 'quarter_month'        => 1,
																	 'primary_day_of_month' => 31, //Last day
																	 'reminder_days'        => 14,
															 ],
									],
							],
	],
	'20:US:IA:00:0030' => [ //State Government [New Hires]
							'NEWHIRE' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'New Hire Report Filing' ),
									'form_description' => TTi18n::getText( 'Report of New Hires' ),
									'note'             => '',
									'tax_codes'        => [],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //As Required, within 20 days of hire date.
															 //On Hire
															 [
																	 'status_id'           => 10, //Enabled
																	 'frequency_id'        => 90100, //On Hire
																	 'due_date_delay_days' => 20,
																	 'reminder_days'       => 0,
															 ],
									],
							],

	],
	'20:US:IA:00:0040' => [ //Child Support
							'CHILDSUPPORT' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'Child Support Withholding Payment' ),
									'form_description' => TTi18n::getText( 'Child Support Withholding Payment' ),
									'note'             => '',
									'tax_codes'        => [ 'CHILDSUPPORT' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Per Pay Period
										[
												'status_id'           => 10, //Enabled
												'frequency_id'        => 1000, //Per Pay Period
												'due_date_delay_days' => 14,
												'reminder_days'       => 5,
										],
									],
							],
	],
	'20:US:IA:00:0050' => [ //State Government [Retirement]
							'RETIREMENT' => [
									'form_code'        => 'RETIREMENT',
									'form_name'        => TTi18n::getText( 'Retirement' ),
									'form_description' => TTi18n::getText( 'Employer\'s Monthly Report' ),
									'note'             => '',
									'tax_codes'        => [ 'RT' ],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [ 'CHECK' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Monthly
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 4100, //Monthly
																	 'primary_day_of_month' => 5, //5th working day of month following the end of the month
																	 'reminder_days'        => 5,
															 ],
									],
							],
	],

	//ID - Idaho
	'20:US:ID:00:0010' => [ //State Government [State Income Tax]
							'FW2'  => [
									'form_code'        => 'W2', //Includes W3
									'form_name'        => TTi18n::getText( 'W2 State Filing' ),
									'form_description' => TTi18n::getText( 'Wage and Tax Statement' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'EFILE' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Annual
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 1, //Jan
																	 'primary_day_of_month' => 31,
																	 'reminder_days'        => 14,
															 ],
									],
							],
							'967A' => [
									'form_code'        => '967A',
									'form_name'        => TTi18n::getText( 'Form 967A Filing' ),
									'form_description' => TTi18n::getText( 'Annual Withholding Form 967A (M/Q/Y Filers)' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [ 'CHECK' ],
									'flags'            => [
											'include_w2'          => true,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Annual
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 1, //Jan
																	 'primary_day_of_month' => 31,
																	 'reminder_days'        => 14,
															 ],
									],
							],
							'910'  => [
									'form_code'        => '910',
									'form_name'        => TTi18n::getText( '910 Payment' ),
									'form_description' => TTi18n::getText( 'Withholding Payment Voucher' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Semi Monthly: 1st to 15th - Due 20th of the month in which the deposit period ends. 16th to the end of the month - due the 5th of the following month.
										//Monthly: Due the 20th of the following month.
										//Quarterly: Due the last day of the month following the end of the quarter. (April 30, July 31, October 31, and January 31)
										//Annually: Due on or before January 31st in the next calendar year.

										[
												'status_id'              => 20, //Disabled
												'frequency_id'           => 4200, //Semi-Monthly
												'primary_day_of_month'   => 15,
												'secondary_day_of_month' => 31,
												'due_date_delay_days'    => 5,
												'reminder_days'          => 0,
										],
										//Monthly
										[
												'status_id'            => 10, //Enabled
												'frequency_id'         => 4100, //Monthly
												'primary_day_of_month' => 20,
												'reminder_days'        => 7,
										],

										//Quarterly
										[
												'status_id'            => 20, //Disabled
												'frequency_id'         => 3000, //Quarterly
												'quarter_month'        => 1,
												'primary_day_of_month' => 31, //Last Day
												'reminder_days'        => 7,
										],

										//Annual
										[
												'status_id'            => 20, //Disabled
												'frequency_id'         => 2000, //Annual
												'primary_month'        => 1, //Jan
												'primary_day_of_month' => 31,
												'reminder_days'        => 14,
										],
									],
							],
	],
	'20:US:ID:00:0020' => [ //State Government [Unemployment Insurance]
							'TAX020' => [
									'form_code'        => 'TAX020',
									'form_name'        => TTi18n::getText( 'TAX020/TAX026 Filing' ),
									'form_description' => TTi18n::getText( 'Idaho Employer Quarterly UI Tax Report' ),
									'note'             => '',
									'tax_codes'        => [ 'UI' ],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [ 'CHECK' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Quarterly - Due the last day of the month following the end of the quarter. (April 30, July 31, October 31, and January 31)
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 3000, //Quarterly
																	 'quarter_month'        => 1,
																	 'primary_day_of_month' => 31, //Last day
																	 'reminder_days'        => 14,
															 ],
									],
							],
	],
	'20:US:ID:00:0030' => [ //State Government [New Hires]
							'NEWHIRE' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'New Hire Report Filing' ),
									'form_description' => TTi18n::getText( 'Report of New Hires' ),
									'note'             => '',
									'tax_codes'        => [],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //As Required, within 20 days of hire date.
															 //On Hire
															 [
																	 'status_id'           => 10, //Enabled
																	 'frequency_id'        => 90100, //On Hire
																	 'due_date_delay_days' => 20,
																	 'reminder_days'       => 0,
															 ],
									],
							],

	],
	'20:US:ID:00:0040' => [ //Child Support
							'CHILDSUPPORT' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'Child Support Withholding Payment' ),
									'form_description' => TTi18n::getText( 'Child Support Withholding Payment' ),
									'note'             => '',
									'tax_codes'        => [ 'CHILDSUPPORT' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Per Pay Period
										[
												'status_id'           => 10, //Enabled
												'frequency_id'        => 1000, //Per Pay Period
												'due_date_delay_days' => 14,
												'reminder_days'       => 5,
										],
									],
							],
	],

	//IL - Illinois
	'20:US:IL:00:0010' => [ //State Government [State Income Tax]
							'FW2'   => [
									'form_code'        => 'W2', //Includes W3
									'form_name'        => TTi18n::getText( 'W2 State Filing' ),
									'form_description' => TTi18n::getText( 'Wage and Tax Statement' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'EFILE' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Annual
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 1, //Jan
																	 'primary_day_of_month' => 31,
																	 'reminder_days'        => 14,
															 ],
									],
							],
							'IL501' => [
									'form_code'        => 'IL-501',
									'form_name'        => TTi18n::getText( 'IL-501 Payment' ),
									'form_description' => TTi18n::getText( 'Illinois Withholding Tax Payment' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Semiweekly: Due the following Friday for preceding Saturday, Sunday, Monday, or Tuesday, and the following Wednesday for the preceding Wednesday, Thursday, or Friday
										//Monthly: Due the 15th of the following month.

										//US - Semi-Weekly
										[
												'status_id'           => 20, //Disabled
												'frequency_id'        => 64000, //US - Semi-Weekly
												'due_date_delay_days' => 0,
												'reminder_days'       => 1,
										],

										//Monthly
										[
												'status_id'            => 10, //Enabled
												'frequency_id'         => 4100, //Monthly
												'primary_day_of_month' => 15,
												'reminder_days'        => 7,
										],
									],
							],
							'IL941' => [
									'form_code'        => 'IL-941',
									'form_name'        => TTi18n::getText( 'IL-941 Filing' ),
									'form_description' => TTi18n::getText( 'Illinois Withholding Income Tax Return' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Quarterly
										[
												'status_id'            => 10, //Enabled
												'frequency_id'         => 3000, //Quarterly
												'quarter_month'        => 1,
												'primary_day_of_month' => 31, //Last day
												'reminder_days'        => 14,
										],
									],
							],
	],
	'20:US:IL:00:0020' => [ //State Government [Unemployment Insurance]
							'UI3' => [
									'form_code'        => 'UI-3',
									'form_name'        => TTi18n::getText( 'UI-3/UI-40 Filing' ),
									'form_description' => TTi18n::getText( 'Employer\'s Contribution and Wage Report' ),
									'note'             => '',
									'tax_codes'        => [ 'UI' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Quarterly - Due the last day of the month following the end of the quarter. (April 30, July 31, October 31, and January 31)
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 3000, //Quarterly
																	 'quarter_month'        => 1,
																	 'primary_day_of_month' => 31, //Last day
																	 'reminder_days'        => 14,
															 ],
									],
							],
	],
	'20:US:IL:00:0030' => [ //State Government [New Hires]
							'NEWHIRE' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'New Hire Report Filing' ),
									'form_description' => TTi18n::getText( 'Report of New Hires' ),
									'note'             => '',
									'tax_codes'        => [],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //As Required, within 20 days of hire date.
															 //On Hire
															 [
																	 'status_id'           => 10, //Enabled
																	 'frequency_id'        => 90100, //On Hire
																	 'due_date_delay_days' => 20,
																	 'reminder_days'       => 0,
															 ],
									],
							],

	],
	'20:US:IL:00:0040' => [ //Child Support
							'CHILDSUPPORT' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'Child Support Withholding Payment' ),
									'form_description' => TTi18n::getText( 'Child Support Withholding Payment' ),
									'note'             => '',
									'tax_codes'        => [ 'CHILDSUPPORT' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Per Pay Period
										[
												'status_id'           => 10, //Enabled
												'frequency_id'        => 1000, //Per Pay Period
												'due_date_delay_days' => 14,
												'reminder_days'       => 5,
										],
									],
							],
	],

	//IN - Indiana
	'20:US:IN:00:0010' => [ //State Government [State Income Tax]
							'FW2' => [
									'form_code'        => 'W2', //Includes W3
									'form_name'        => TTi18n::getText( 'W2 State Filing' ),
									'form_description' => TTi18n::getText( 'Wage and Tax Statement' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'EFILE' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Annual
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 1, //Jan
																	 'primary_day_of_month' => 31,
																	 'reminder_days'        => 14,
															 ],
									],
							],
							'WH1' => [
									'form_code'        => 'WH-1',
									'form_name'        => TTi18n::getText( 'WH-1 Payment' ),
									'form_description' => TTi18n::getText( 'Withholding Tax Voucher' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//EFT Early Filer: Due via EFT by the 20th of the following month.
										//Early Filer: Due the 20th of the following month.
										//Monthly: Due the 30th of the following month.
										//Annual: Due January 30th of the following year.


										//Monthly (Early Filer)
										[
												'status_id'            => 20, //Disabled
												'frequency_id'         => 4100, //Monthly
												'primary_day_of_month' => 20,
												'reminder_days'        => 7,
										],

										//Monthly
										[
												'status_id'            => 10, //Enabled
												'frequency_id'         => 4100, //Monthly
												'primary_day_of_month' => 30,
												'reminder_days'        => 7,
										],

										//Annual
										[
												'status_id'            => 20, //Disabled
												'frequency_id'         => 2000, //Annual
												'primary_month'        => 1, //Jan
												'primary_day_of_month' => 30,
												'reminder_days'        => 14,
										],
									],
							],
							'WH3' => [
									'form_code'        => 'WH-3', //Includes W3
									'form_name'        => TTi18n::getText( 'WH-3 Filing' ),
									'form_description' => TTi18n::getText( 'Annual Withholding Tax' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => true,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Annual
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 1, //Jan
																	 'primary_day_of_month' => 31,
																	 'reminder_days'        => 14,
															 ],
									],
							],
	],
	'20:US:IN:00:0020' => [ //State Government [Unemployment Insurance]
							'UC1' => [
									'form_code'        => 'UC-1',
									'form_name'        => TTi18n::getText( 'UI-1 Filing' ),
									'form_description' => TTi18n::getText( 'Quarterly Contribution Report' ),
									'note'             => '',
									'tax_codes'        => [ 'UI' ],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [ 'CHECK' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Quarterly - Due the last day of the month following the end of the quarter. (April 30, July 31, October 31, and January 31)
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 3000, //Quarterly
																	 'quarter_month'        => 1,
																	 'primary_day_of_month' => 31, //Last day
																	 'reminder_days'        => 14,
															 ],
									],
							],
	],
	'20:US:IN:00:0030' => [ //State Government [New Hires]
							'NEWHIRE' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'New Hire Report Filing' ),
									'form_description' => TTi18n::getText( 'Report of New Hires' ),
									'note'             => '',
									'tax_codes'        => [],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //As Required, within 20 days of hire date.
															 //On Hire
															 [
																	 'status_id'           => 10, //Enabled
																	 'frequency_id'        => 90100, //On Hire
																	 'due_date_delay_days' => 20,
																	 'reminder_days'       => 0,
															 ],
									],
							],

	],
	'20:US:IN:00:0040' => [ //Child Support
							'CHILDSUPPORT' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'Child Support Withholding Payment' ),
									'form_description' => TTi18n::getText( 'Child Support Withholding Payment' ),
									'note'             => '',
									'tax_codes'        => [ 'CHILDSUPPORT' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Per Pay Period
										[
												'status_id'           => 10, //Enabled
												'frequency_id'        => 1000, //Per Pay Period
												'due_date_delay_days' => 14,
												'reminder_days'       => 5,
										],
									],
							],
	],

	//KS - Kansas
	'20:US:KS:00:0010' => [ //State Government [State Income Tax]
							'FW2' => [
									'form_code'        => 'W2', //Includes W3
									'form_name'        => TTi18n::getText( 'W2 State Filing' ),
									'form_description' => TTi18n::getText( 'Wage and Tax Statement' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'EFILE' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Annual
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 1, //Jan
																	 'primary_day_of_month' => 31,
																	 'reminder_days'        => 14,
															 ],
									],
							],
							'KW5' => [
									'form_code'        => 'KW-5',
									'form_name'        => TTi18n::getText( 'KW-5 Payment' ),
									'form_description' => TTi18n::getText( 'Withholding Tax Deposit Report' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Quadmonthly: Due within 3 banking days of the 7, 15 and 21 and last day of the month.
										//Semimonthly: Due on the 25th of the same month for the 1st-15th. Due on the 10th of the following month for the 16th through the end of the month.
										//Monthly: Due the 15th of the following month.
										//Quarterly: Due the 25th of the month following the last month of the quarter.
										//Annual: Due January 25th of the following year.

										//Quad-Monthly
										[
												'status_id'           => 20, //Disabled
												'frequency_id'        => 62000, //Quad-Monthly
												'due_date_delay_days' => 0,
												'reminder_days'       => 0,
										],

										//Semi-Monthly
										[
												'status_id'              => 20, //Disabled
												'frequency_id'           => 4200, //Semi-Monthly
												'primary_day_of_month'   => 15,
												'secondary_day_of_month' => 31,
												'due_date_delay_days'    => 10,
												'reminder_days'          => 0,
										],

										//Monthly
										[
												'status_id'            => 10, //Enabled
												'frequency_id'         => 4100, //Monthly
												'primary_day_of_month' => 15,
												'reminder_days'        => 7,
										],

										//Quarterly
										[
												'status_id'            => 20, //Disabled
												'frequency_id'         => 3000, //Quarterly
												'quarter_month'        => 1,
												'primary_day_of_month' => 25,
												'reminder_days'        => 14,
										],
										//Annual
										[
												'status_id'            => 20, //Disabled
												'frequency_id'         => 2000, //Annual
												'primary_month'        => 1, //Jan
												'primary_day_of_month' => 25,
												'reminder_days'        => 14,
										],
									],
							],
							'KW3' => [
									'form_code'        => 'KW-3',
									'form_name'        => TTi18n::getText( 'KW-3 Filing' ),
									'form_description' => TTi18n::getText( 'Annual Withholding Tax Return & Transmittal' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => true,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Annual
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 1, //Jan
																	 'primary_day_of_month' => 31,
																	 'reminder_days'        => 14,
															 ],
									],
							],
							//								//This seems to be the eFile format.
							//								 'KW3E' => array(
							//										 'form_code'        => 'KW-3E',
							//										 'form_name'        => TTi18n::getText( 'KW-3E Filing' ),
							//										 'form_description' => TTi18n::getText( 'Annual Withholding Tax Return (For use by EFT filers only)' ),
							//										 'note'             => '',
							//										 'tax_codes'        => array('INCOME', 'SS', 'MEDICARE'),
							//										 'filing_methods'   => array('PRINT','EFILE'),
							//										 'payment_methods'  => array(),
							//										 'flags'            => array(
							//												 'include_w2'          => TRUE,
							//												 'file_zero_wage'      => FALSE,
							//												 'file_zero_liability' => FALSE,
							//												 'auto_file'           => FALSE,
							//												 'auto_pay'            => FALSE,
							//										 ),
							//										 'frequency'        => array(  //Annual
							//																	   array(
							//																			   'status_id'            => 10, //Enabled
							//																			   'frequency_id'         => 2000, //Annual
							//																			   'primary_month'        => 1, //Jan
							//																			   'primary_day_of_month' => 31,
							//																			   'reminder_days'        => 14,
							//																	   ),
							//										 ),
							//								 ),
	],
	'20:US:KS:00:0020' => [ //State Government [Unemployment Insurance]
							'KCNS100' => [
									'form_code'        => 'K-CNS 100',
									'form_name'        => TTi18n::getText( 'K-CNS 100/K-CNS 101 Filing' ),
									'form_description' => TTi18n::getText( 'Quarterly Wage Report and Unemployment Tax Return' ),
									'note'             => '',
									'tax_codes'        => [ 'UI' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Quarterly - Due the last day of the month following the end of the quarter. (April 30, July 31, October 31, and January 31)
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 3000, //Quarterly
																	 'quarter_month'        => 1,
																	 'primary_day_of_month' => 31, //Last day
																	 'reminder_days'        => 14,
															 ],
									],
							],
	],
	'20:US:KS:00:0030' => [ //State Government [New Hires]
							'NEWHIRE' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'New Hire Report Filing' ),
									'form_description' => TTi18n::getText( 'Report of New Hires' ),
									'note'             => '',
									'tax_codes'        => [],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //As Required, within 20 days of hire date.
															 //On Hire
															 [
																	 'status_id'           => 10, //Enabled
																	 'frequency_id'        => 90100, //On Hire
																	 'due_date_delay_days' => 20,
																	 'reminder_days'       => 0,
															 ],
									],
							],

	],
	'20:US:KS:00:0040' => [ //Child Support
							'CHILDSUPPORT' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'Child Support Withholding Payment' ),
									'form_description' => TTi18n::getText( 'Child Support Withholding Payment' ),
									'note'             => '',
									'tax_codes'        => [ 'CHILDSUPPORT' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Per Pay Period
										[
												'status_id'           => 10, //Enabled
												'frequency_id'        => 1000, //Per Pay Period
												'due_date_delay_days' => 14,
												'reminder_days'       => 5,
										],
									],
							],
	],

	//KY - Kentucky
	'20:US:KY:00:0010' => [ //State Government [State Income Tax]
							'FW2'    => [
									'form_code'        => 'W2', //Includes W3
									'form_name'        => TTi18n::getText( 'W2 State Filing' ),
									'form_description' => TTi18n::getText( 'Wage and Tax Statement' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'EFILE' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Annual
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 1, //Jan
																	 'primary_day_of_month' => 31,
																	 'reminder_days'        => 14,
															 ],
									],
							],
							'K1'     => [
									'form_code'        => 'K-1',
									'form_name'        => TTi18n::getText( 'K-1 Payment' ),
									'form_description' => TTi18n::getText( 'Employer\'s Return of Income Tax Withheld' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//One Day: Due one banking day after $100K accumulation limit is reached during any reporting period.
										//Twicemonthly: January liability is due on February 10th. February through November liabilities for the 1st through the 15th are due on the 25th of the same month. February through November liabilites for the 16th through the end of the month are due on the 10th of the following month. December liabilities for the 1st through the 15th are due on December 26th, and December liabilities for the 16th through the 31 are due on January 31.
										//Monthly: Due the 15th of the following month, for January through November. Note: December liabilities are due January 31 using the K-3 form.
										//Quarterly: Due the last day of the month following the end of the first 3 quarters of the year. (April 30, July 31, and October 31) Note: The fourth quarter deposit is due January 31 with form K-3.


										//Per Pay Period
										[
												'status_id'           => 20, //Disabled
												'frequency_id'        => 1000, //Per Pay Period
												'due_date_delay_days' => 1,
												'reminder_days'       => 7,
										],

										//Monthly
										[
												'status_id'            => 10, //Enabled
												'frequency_id'         => 4100, //Monthly
												'primary_day_of_month' => 15,
												'reminder_days'        => 7,
										],

										//Quarterly
										[
												'status_id'            => 20, //Disabled
												'frequency_id'         => 3000, //Quarterly
												'quarter_month'        => 1,
												'primary_day_of_month' => 31, //Last day
												'reminder_days'        => 14,
										],
									],
							],
							'K3'     => [
									'form_code'        => 'K-3',
									'form_name'        => TTi18n::getText( 'K-3 Filing' ),
									'form_description' => TTi18n::getText( 'Employer\'s Return of Income Tax Withheld' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [ 'CHECK' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Annual
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 1, //Jan
																	 'primary_day_of_month' => 31,
																	 'reminder_days'        => 14,
															 ],
									],
							],
							'42A806' => [
									'form_code'        => '42A806',
									'form_name'        => TTi18n::getText( '42A806 Filing' ),
									'form_description' => TTi18n::getText( 'Transmitter Report for Filing Kentucky Wage Statements' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => true,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Annual
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 1, //Jan
																	 'primary_day_of_month' => 31,
																	 'reminder_days'        => 14,
															 ],
									],
							],
	],
	'20:US:KY:00:0020' => [ //State Government [Unemployment Insurance]
							'UI3' => [
									'form_code'        => 'UI-3',
									'form_name'        => TTi18n::getText( 'UI-3 Filing' ),
									'form_description' => TTi18n::getText( 'Employer\'s Quarterly Unemployment Wage and Tax Report' ),
									'note'             => '',
									'tax_codes'        => [ 'UI' ],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [ 'CHECK' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Quarterly - Due the last day of the month following the end of the quarter. (April 30, July 31, October 31, and January 31)
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 3000, //Quarterly
																	 'quarter_month'        => 1,
																	 'primary_day_of_month' => 31, //Last day
																	 'reminder_days'        => 14,
															 ],
									],
							],
	],
	'20:US:KY:00:0030' => [  //State Government [New Hires]
							 'NEWHIRE' => [
									 'form_code'        => '',
									 'form_name'        => TTi18n::getText( 'New Hire Report Filing' ),
									 'form_description' => TTi18n::getText( 'Report of New Hires' ),
									 'note'             => '',
									 'tax_codes'        => [],
									 'filing_methods'   => [ 'PRINT' ],
									 'payment_methods'  => [],
									 'flags'            => [
											 'include_w2'          => false,
											 'file_zero_wage'      => false,
											 'file_zero_liability' => false,
											 'auto_file'           => false,
											 'auto_pay'            => false,
									 ],
									 'frequency'        => [  //As Required, within 20 days of hire date.
															  //On Hire
															  [
																	  'status_id'           => 10, //Enabled
																	  'frequency_id'        => 90100, //On Hire
																	  'due_date_delay_days' => 20,
																	  'reminder_days'       => 0,
															  ],
									 ],
							 ],

	],
	'20:US:KY:00:0040' => [ //Child Support
							'CHILDSUPPORT' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'Child Support Withholding Payment' ),
									'form_description' => TTi18n::getText( 'Child Support Withholding Payment' ),
									'note'             => '',
									'tax_codes'        => [ 'CHILDSUPPORT' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Per Pay Period
										[
												'status_id'           => 10, //Enabled
												'frequency_id'        => 1000, //Per Pay Period
												'due_date_delay_days' => 14,
												'reminder_days'       => 5,
										],
									],
							],
	],

	//LA - Louisiana
	'20:US:LA:00:0010' => [ //State Government [State Income Tax]
							'FW2' => [
									'form_code'        => 'W2', //Includes W3
									'form_name'        => TTi18n::getText( 'W2 State Filing' ),
									'form_description' => TTi18n::getText( 'Wage and Tax Statement' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'EFILE' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Annual
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 1, //Jan
																	 'primary_day_of_month' => 31,
																	 'reminder_days'        => 14,
															 ],
									],
							],
							'L3'  => [
									'form_code'        => 'L-3',
									'form_name'        => TTi18n::getText( 'L-3 Filing' ),
									'form_description' => TTi18n::getText( 'Transmittal of Withholding Tax Statements' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => true,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Annual
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 1, //Jan
																	 'primary_day_of_month' => 31,
																	 'reminder_days'        => 14,
															 ],
									],
							],
							'L1'  => [
									'form_code'        => 'L-1',
									'form_name'        => TTi18n::getText( 'L-1 Filing' ),
									'form_description' => TTi18n::getText( 'Employer\'s Return of State Income Tax Withheld' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Semimonthly depositors: Quarterly L-1 reconciliation is due the 15th of the month following the end of the quarter.
										//Monthly, Quarterly, and Annual depositors: Quarterly L-1 reconciliation is due the last day of the month following the end of the quarter (April 30, July 31, October 31, and January 31).
										[
												'status_id'            => 10, //Enabled
												'frequency_id'         => 3000, //Quarterly
												'quarter_month'        => 1,
												'primary_day_of_month' => 31, //Last day
												'reminder_days'        => 7,
										],
										[
												'status_id'            => 20, //Disabled
												'frequency_id'         => 4200, //Semi-Monthly
												'quarter_month'        => 1,
												'primary_day_of_month' => 15, //Last day
												'reminder_days'        => 7,
										],

									],
							],
							'L1V' => [
									'form_code'        => 'L1-V',
									'form_name'        => TTi18n::getText( 'L1-V Payment' ),
									'form_description' => TTi18n::getText( 'Withholding Payment Voucher' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'EFILE' ],
									'payment_methods'  => [ 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [    //Monthly
															   [
																	   'status_id'            => 10, //Enabled
																	   'frequency_id'         => 4100, //Monthly
																	   'primary_day_of_month' => 31,
																	   'reminder_days'        => 7,
															   ],
									],
							],
	],
	'20:US:LA:00:0020' => [ //State Government [Unemployment Insurance]
							'UI' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'Employers Wage and Tax Filing' ),
									'form_description' => TTi18n::getText( 'Employers Wage and Tax Report' ),
									'note'             => '',
									'tax_codes'        => [ 'UI' ],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [ 'CHECK' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Quarterly - Due the last day of the month following the end of the quarter. (April 30, July 31, October 31, and January 31)
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 3000, //Quarterly
																	 'quarter_month'        => 1,
																	 'primary_day_of_month' => 31, //Last day
																	 'reminder_days'        => 14,
															 ],
									],
							],
	],
	'20:US:LA:00:0030' => [ //State Government [New Hires]
							'NEWHIRE' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'New Hire Report Filing' ),
									'form_description' => TTi18n::getText( 'Report of New Hires' ),
									'note'             => '',
									'tax_codes'        => [],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //As Required, within 20 days of hire date.
															 //On Hire
															 [
																	 'status_id'           => 10, //Enabled
																	 'frequency_id'        => 90100, //On Hire
																	 'due_date_delay_days' => 20,
																	 'reminder_days'       => 0,
															 ],
									],
							],

	],
	'20:US:LA:00:0040' => [ //Child Support
							'CHILDSUPPORT' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'Child Support Withholding Payment' ),
									'form_description' => TTi18n::getText( 'Child Support Withholding Payment' ),
									'note'             => '',
									'tax_codes'        => [ 'CHILDSUPPORT' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Per Pay Period
										[
												'status_id'           => 10, //Enabled
												'frequency_id'        => 1000, //Per Pay Period
												'due_date_delay_days' => 14,
												'reminder_days'       => 5,
										],
									],
							],
	],

	//MA - Massachusetts
	'20:US:MA:00:0010' => [ //State Government [State Income Tax]
							'FW2'   => [
									'form_code'        => 'W2', //Includes W3
									'form_name'        => TTi18n::getText( 'W2 State Filing' ),
									'form_description' => TTi18n::getText( 'Wage and Tax Statement' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'EFILE' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Annual
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 1, //Jan
																	 'primary_day_of_month' => 31,
																	 'reminder_days'        => 14,
															 ],
									],
							],
							'M941'  => [
									'form_code'        => 'M-941',
									'form_name'        => TTi18n::getText( 'M-941 Payment' ),
									'form_description' => TTi18n::getText( 'Employer\'s Return of Income Taxes Withheld' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Quarter-Monthly: When Massachusetts income tax withheld is $500 or more by the 7th, 15th, 22nd and last day of a month, pay over within three business days thereafter.
										//Monthly: Due on the 15th day of the month following the reporting period. Except for the period of March, June, September and December, these are due on the last day of the month following the monthly tax period.
										//Quarterly: Due the last day of the month following the end of the quarter. (April 30, July 31, October 31, and January 31).
										//Annually: Due on or before the 31st day of January following the annual withholding period.

										//Annual
										[
												'status_id'            => 20, //Disabled
												'frequency_id'         => 2000, //Annual
												'primary_month'        => 1, //Jan
												'primary_day_of_month' => 31,
												'reminder_days'        => 14,
										],

										//Quarterly
										[
												'status_id'            => 10, //Enabled
												'frequency_id'         => 3000, //Quarterly
												'quarter_month'        => 1,
												'primary_day_of_month' => 31, //Last day
												'reminder_days'        => 14,
										],
										//Monthly
										[
												'status_id'     => 20, //Disabled
												'frequency_id'  => 60000, //US - Monthly (Quarter Exceptions)
												'reminder_days' => 7,
										],

										//Quarter-Monthly
										[
												'status_id'     => 20, //Disabled
												'frequency_id'  => 62000, //Quarter-Monthly
												'reminder_days' => 14,
										],
									],
							],
							'M941D' => [
									'form_code'        => 'M-941D',
									'form_name'        => TTi18n::getText( 'M-941D Filing' ),
									'form_description' => TTi18n::getText( 'Quarterly Return of Income Taxes Withheld for Employer Paying Weekly' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Quarterly
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 3000, //Quarterly
																	 'quarter_month'        => 1,
																	 'primary_day_of_month' => 31, //Last day
																	 'reminder_days'        => 14,
															 ],
									],
							],
	],
	'20:US:MA:00:0020' => [ //State Government [Unemployment Insurance]
							'1700EMAC' => [
									'form_code'        => '1700-EMAC',
									'form_name'        => TTi18n::getText( '1700-EMAC/HI and UI Filing' ),
									'form_description' => TTi18n::getText( 'Health Insurance (EMAC 2014) Quarterly Contribution combined with UI report' ),
									'note'             => '',
									'tax_codes'        => [ 'UI' ],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [ 'CHECK' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Quarterly - Due the last day of the month following the end of the quarter. (April 30, July 31, October 31, and January 31)
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 3000, //Quarterly
																	 'quarter_month'        => 1,
																	 'primary_day_of_month' => 31, //Last day
																	 'reminder_days'        => 14,
															 ],
									],
							],
	],
	'20:US:MA:00:0030' => [ //State Government [New Hires]
							'NEWHIRE' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'New Hire Report Filing' ),
									'form_description' => TTi18n::getText( 'Report of New Hires' ),
									'note'             => '',
									'tax_codes'        => [],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //As Required, within 20 days of hire date.
															 //On Hire
															 [
																	 'status_id'           => 10, //Enabled
																	 'frequency_id'        => 90100, //On Hire
																	 'due_date_delay_days' => 20,
																	 'reminder_days'       => 0,
															 ],
									],
							],

	],
	'20:US:MA:00:0040' => [ //Child Support
							'CHILDSUPPORT' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'Child Support Withholding Payment' ),
									'form_description' => TTi18n::getText( 'Child Support Withholding Payment' ),
									'note'             => '',
									'tax_codes'        => [ 'CHILDSUPPORT' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Per Pay Period
										[
												'status_id'           => 10, //Enabled
												'frequency_id'        => 1000, //Per Pay Period
												'due_date_delay_days' => 14,
												'reminder_days'       => 5,
										],
									],
							],
	],

	//MD - Maryland
	'20:US:MD:00:0010' => [ //State Government [State Income Tax]
							'FW2'    => [
									'form_code'        => 'W2', //Includes W3
									'form_name'        => TTi18n::getText( 'W2 State Filing' ),
									'form_description' => TTi18n::getText( 'Wage and Tax Statement' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'EFILE' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Annual
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 1, //Jan
																	 'primary_day_of_month' => 31,
																	 'reminder_days'        => 14,
															 ],
									],
							],
							'MW506'  => [
									'form_code'        => 'MW506',
									'form_name'        => TTi18n::getText( 'MW506 Payment' ),
									'form_description' => TTi18n::getText( 'Employer Return of Income Tax Withheld' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Monthly: Due the 15th of the following month.
															 //Quarterly: Due the 15th of the month following the end of the quarter.
															 //Annual: Due January 31 of the following year.


															 //Annual
															 [
																	 'status_id'            => 20, //Disabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 1, //Jan
																	 'primary_day_of_month' => 31,
																	 'reminder_days'        => 14,
															 ],

															 //Quarterly
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 3000, //Quarterly
																	 'quarter_month'        => 1,
																	 'primary_day_of_month' => 15,
																	 'reminder_days'        => 14,
															 ],
															 //Monthly
															 [
																	 'status_id'            => 20, //Disabled
																	 'frequency_id'         => 4100, //Monthly
																	 'primary_day_of_month' => 15,
																	 'reminder_days'        => 7,
															 ],
									],
							],
							'MW506M' => [
									'form_code'        => 'MW506M',
									'form_name'        => TTi18n::getText( 'MW506M Payment' ),
									'form_description' => TTi18n::getText( 'Employer Return of Income Tax Withheld' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Due 3 business days following the pay date.
															 //Per Pay Period
															 [
																	 'status_id'           => 20, //Disabled
																	 'frequency_id'        => 1000, //Per Pay Period
																	 'due_date_delay_days' => 3,
																	 'reminder_days'       => 7,
															 ],
									],
							],
							'MW508'  => [
									'form_code'        => 'MW508',
									'form_name'        => TTi18n::getText( 'MW508 Filing' ),
									'form_description' => TTi18n::getText( 'Annual Employer Withholding Reconciliation Report' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [ 'CHECK' ],
									'flags'            => [
											'include_w2'          => true,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Annual
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 1, //Jan
																	 'primary_day_of_month' => 31,
																	 'reminder_days'        => 14,
															 ],
									],
							],
	],
	'20:US:MD:00:0020' => [ //State Government [Unemployment Insurance]
							'DLLROUI15' => [
									'form_code'        => 'DLLR/OUI 15',
									'form_name'        => TTi18n::getText( 'DLLR/OUI 15, DLLR/OUI 16 Filing' ),
									'form_description' => TTi18n::getText( 'Health Insurance (EMAC 2014) Quarterly Contribution combined with UI report' ),
									'note'             => '',
									'tax_codes'        => [ 'UI' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Quarterly - Due the last day of the month following the end of the quarter. (April 30, July 31, October 31, and January 31)
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 3000, //Quarterly
																	 'quarter_month'        => 1,
																	 'primary_day_of_month' => 31, //Last day
																	 'reminder_days'        => 14,
															 ],
									],
							],
	],
	'20:US:MD:00:0030' => [ //State Government [New Hires]
							'NEWHIRE' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'New Hire Report Filing' ),
									'form_description' => TTi18n::getText( 'Report of New Hires' ),
									'note'             => '',
									'tax_codes'        => [],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //As Required, within 20 days of hire date.
															 //On Hire
															 [
																	 'status_id'           => 10, //Enabled
																	 'frequency_id'        => 90100, //On Hire
																	 'due_date_delay_days' => 20,
																	 'reminder_days'       => 0,
															 ],
									],
							],

	],
	'20:US:MD:00:0040' => [ //Child Support
							'CHILDSUPPORT' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'Child Support Withholding Payment' ),
									'form_description' => TTi18n::getText( 'Child Support Withholding Payment' ),
									'note'             => '',
									'tax_codes'        => [ 'CHILDSUPPORT' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Per Pay Period
										[
												'status_id'           => 10, //Enabled
												'frequency_id'        => 1000, //Per Pay Period
												'due_date_delay_days' => 14,
												'reminder_days'       => 5,
										],
									],
							],
	],

	//ME - Maine
	'20:US:ME:00:0010' => [ //State Government [State Income Tax]
							'FW2'   => [
									'form_code'        => 'W2', //Includes W3
									'form_name'        => TTi18n::getText( 'W2 State Filing' ),
									'form_description' => TTi18n::getText( 'Wage and Tax Statement' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'EFILE' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Annual
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 1, //Jan
																	 'primary_day_of_month' => 31,
																	 'reminder_days'        => 14,
															 ],
									],
							],
							'900ME' => [
									'form_code'        => '900ME',
									'form_name'        => TTi18n::getText( '900ME Payment' ),
									'form_description' => TTi18n::getText( 'Employer\'s Payment Voucher for Income Tax Withheld.' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [ 'CHECK' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Semiweekly: Due the following Friday (for pay dates that fall on Saturday through Tuesday). Due the following Wednesday (for pay dates that fall on Wednesday through Friday).

															 //US - Semi-Weekly
															 [
																	 'status_id'           => 10, //Enabled
																	 'frequency_id'        => 64000, //US - Semi-Weekly
																	 'due_date_delay_days' => 0,
																	 'reminder_days'       => 1,
															 ],
									],
							],
							'941ME' => [
									'form_code'        => '941ME',
									'form_name'        => TTi18n::getText( '941ME Filing' ),
									'form_description' => TTi18n::getText( 'Employer\'s Return of Maine Income Tax Withheld' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Quarterly
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 3000, //Quarterly
																	 'quarter_month'        => 1,
																	 'primary_day_of_month' => 31, //Last day
																	 'reminder_days'        => 14,
															 ],
									],
							],
							'W3ME'  => [
									'form_code'        => 'W-3ME',
									'form_name'        => TTi18n::getText( 'W-3ME Filing' ),
									'form_description' => TTi18n::getText( 'Employer\'s Return of Maine Income Tax Withheld' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Annual
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 1, //Jan
																	 'primary_day_of_month' => 31,
																	 'reminder_days'        => 14,
															 ],
									],
							],
	],
	'20:US:ME:00:0020' => [ //State Government [Unemployment Insurance]
							'MEUC1' => [
									'form_code'        => 'ME-UC-1',
									'form_name'        => TTi18n::getText( 'ME-UC-1 Filing' ),
									'form_description' => TTi18n::getText( 'ME-UC-1' ),
									'note'             => '',
									'tax_codes'        => [ 'UI' ],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [ 'CHECK' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Quarterly - Due the last day of the month following the end of the quarter. (April 30, July 31, October 31, and January 31)
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 3000, //Quarterly
																	 'quarter_month'        => 1,
																	 'primary_day_of_month' => 31, //Last day
																	 'reminder_days'        => 14,
															 ],
									],
							],
	],
	'20:US:ME:00:0030' => [ //State Government [New Hires]
							'NEWHIRE' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'New Hire Report Filing' ),
									'form_description' => TTi18n::getText( 'Report of New Hires' ),
									'note'             => '',
									'tax_codes'        => [],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //As Required, within 20 days of hire date.
															 //On Hire
															 [
																	 'status_id'           => 10, //Enabled
																	 'frequency_id'        => 90100, //On Hire
																	 'due_date_delay_days' => 20,
																	 'reminder_days'       => 0,
															 ],
									],
							],

	],
	'20:US:ME:00:0040' => [ //Child Support
							'CHILDSUPPORT' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'Child Support Withholding Payment' ),
									'form_description' => TTi18n::getText( 'Child Support Withholding Payment' ),
									'note'             => '',
									'tax_codes'        => [ 'CHILDSUPPORT' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Per Pay Period
										[
												'status_id'           => 10, //Enabled
												'frequency_id'        => 1000, //Per Pay Period
												'due_date_delay_days' => 14,
												'reminder_days'       => 5,
										],
									],
							],
	],

	//MI - Michigan
	'20:US:MI:00:0010' => [ //State Government [State Income Tax]
							'FW2'  => [
									'form_code'        => 'W2', //Includes W3
									'form_name'        => TTi18n::getText( 'W2 State Filing' ),
									'form_description' => TTi18n::getText( 'Wage and Tax Statement' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'EFILE' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Annual
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 1, //Jan
																	 'primary_day_of_month' => 31,
																	 'reminder_days'        => 14,
															 ],
									],
							],
							'5081' => [
									'form_code'        => '5081',
									'form_name'        => TTi18n::getText( 'Form 5081 Filing' ),
									'form_description' => TTi18n::getText( 'Sales, Use and Withholding Taxes Annual Return' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Annual
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 2, //Feb
																	 'primary_day_of_month' => 28,
																	 'reminder_days'        => 14,
															 ],
									],
							],
							'5080' => [
									'form_code'        => '5080',
									'form_name'        => TTi18n::getText( 'Form 5080 Filing' ),
									'form_description' => TTi18n::getText( 'Sales, Use and Withholding Taxes Monthly/Quarterly Return' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Monthly: Due by 20th of the following month.
															 //Quarterly: Due by 20th of the month following quarter end.

															 //Monthly
															 [
																	 'status_id'            => 20, //Disabled
																	 'frequency_id'         => 4100, //Monthly
																	 'primary_day_of_month' => 20,
																	 'reminder_days'        => 7,
															 ],

															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 3000, //Quarterly
																	 'quarter_month'        => 1,
																	 'primary_day_of_month' => 20,
																	 'reminder_days'        => 14,
															 ],
									],
							],
							'5094' => [
									'form_code'        => '5094',
									'form_name'        => TTi18n::getText( 'Form 5094 Payment' ),
									'form_description' => TTi18n::getText( 'Sales, Use and Withholding Payment Voucher' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Monthly: Due by 20th of the following month.
															 //Quarterly: Due by 20th of the month following quarter end.

															 //Monthly
															 [
																	 'status_id'            => 20, //Disabled
																	 'frequency_id'         => 4100, //Monthly
																	 'primary_day_of_month' => 20,
																	 'reminder_days'        => 7,
															 ],

															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 3000, //Quarterly
																	 'quarter_month'        => 1,
																	 'primary_day_of_month' => 20,
																	 'reminder_days'        => 14,
															 ],
									],
							],
	],
	'20:US:MI:00:0020' => [ //State Government [Unemployment Insurance]
							'UI' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'Employer\'s Quarterly Tax Report Filing' ),
									'form_description' => TTi18n::getText( 'Employer\'s Quarterly Tax Report' ),
									'note'             => '',
									'tax_codes'        => [ 'UI' ],
									'filing_methods'   => [ 'EFILE' ],
									'payment_methods'  => [ 'CHECK' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Quarterly - Due the 25th of the month following the end of the quarter. (April 25, July 25, October 25, and January 25)
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 3000, //Quarterly
																	 'quarter_month'        => 1,
																	 'primary_day_of_month' => 25, //Last day
																	 'reminder_days'        => 14,
															 ],
									],
							],
	],
	'20:US:MI:00:0030' => [ //State Government [New Hires]
							'NEWHIRE' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'New Hire Report Filing' ),
									'form_description' => TTi18n::getText( 'Report of New Hires' ),
									'note'             => '',
									'tax_codes'        => [],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //As Required, within 20 days of hire date.
															 //On Hire
															 [
																	 'status_id'           => 10, //Enabled
																	 'frequency_id'        => 90100, //On Hire
																	 'due_date_delay_days' => 20,
																	 'reminder_days'       => 0,
															 ],
									],
							],

	],
	'20:US:MI:00:0040' => [ //Child Support
							'CHILDSUPPORT' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'Child Support Withholding Payment' ),
									'form_description' => TTi18n::getText( 'Child Support Withholding Payment' ),
									'note'             => '',
									'tax_codes'        => [ 'CHILDSUPPORT' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Per Pay Period
										[
												'status_id'           => 10, //Enabled
												'frequency_id'        => 1000, //Per Pay Period
												'due_date_delay_days' => 14,
												'reminder_days'       => 5,
										],
									],
							],
	],

	//MN - Minnesota
	'20:US:MN:00:0010' => [ //State Government [State Income Tax]
							'FW2'    => [
									'form_code'        => 'W2', //Includes W3
									'form_name'        => TTi18n::getText( 'W2 State Filing' ),
									'form_description' => TTi18n::getText( 'Wage and Tax Statement' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'EFILE' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Annual
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 1, //Jan
																	 'primary_day_of_month' => 31,
																	 'reminder_days'        => 14,
															 ],
									],
							],
							'MW5'    => [
									'form_code'        => 'MW-5',
									'form_name'        => TTi18n::getText( 'MW-5 Payment' ),
									'form_description' => TTi18n::getText( 'Withholding Tax Deposit/Payment' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Semiweekly: Due the following Friday (for pay dates that fall on Saturday through Tuesday). Due the following Wednesday (for pay dates that fall on Wednesday through Friday). Exception: If you have two paydays within one deposit period but the paydays are in separate quarters, you must make two separate payments.
															 //Monthly: Due the 15th of the following month.
															 //Quarterly: Due the last day of the month following the end of the quarter. (April 30, July 31, October 31, and January 31).
															 //Annual: Due the last day of the month following the month in which accumulated withholding liabilities exceeds $500. If the tax does not go over $500 prior to December 1, then it is due the last day of January.

															 //US - Semi-Weekly
															 [
																	 'status_id'           => 20, //Enabled
																	 'frequency_id'        => 64000, //US - Semi-Weekly
																	 'due_date_delay_days' => 0,
																	 'reminder_days'       => 1,
															 ],

															 //Annual
															 [
																	 'status_id'            => 20, //Disabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 1, //Jan
																	 'primary_day_of_month' => 31,
																	 'reminder_days'        => 14,
															 ],

															 //Quarterly
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 3000, //Quarterly
																	 'quarter_month'        => 1,
																	 'primary_day_of_month' => 31, //Last Day
																	 'reminder_days'        => 14,
															 ],
															 //Monthly
															 [
																	 'status_id'            => 20, //Disabled
																	 'frequency_id'         => 4100, //Monthly
																	 'primary_day_of_month' => 15,
																	 'reminder_days'        => 7,
															 ],
									],
							],
							'REPORT' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'Employer\'s Withholding Filing' ),
									'form_description' => TTi18n::getText( 'Employer\'s Withholding: Quarterly and Annual' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Quarterly: Due the last day of the month following the end of the quarter. (April 30, July 31, October 31, and January 31).
										//Annual: Due the last day of the month following the month in which accumulated withholding liabilities exceeds $500. If the tax does not go over $500 prior to December 1, then it is due the last day of January.

										//Annual
										[
												'status_id'            => 20, //Disabled
												'frequency_id'         => 2000, //Annual
												'primary_month'        => 1, //Jan
												'primary_day_of_month' => 31,
												'reminder_days'        => 14,
										],

										//Quarterly
										[
												'status_id'            => 10, //Enabled
												'frequency_id'         => 3000, //Quarterly
												'quarter_month'        => 1,
												'primary_day_of_month' => 31, //Last Day
												'reminder_days'        => 14,
										],
									],
							],
	],
	'20:US:MN:00:0020' => [ //State Government [Unemployment Insurance]
							'UI' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'Employer\'s Unemployment Insurance Filing' ),
									'form_description' => TTi18n::getText( 'Employer\'s Unemployment Insurance' ),
									'note'             => '',
									'tax_codes'        => [ 'UI' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Quarterly - Due the last day of the month following the end of the quarter. (April 30, July 31, October 31, and January 31)
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 3000, //Quarterly
																	 'quarter_month'        => 1,
																	 'primary_day_of_month' => 31, //Last day
																	 'reminder_days'        => 14,
															 ],
									],
							],
	],
	'20:US:MN:00:0030' => [ //State Government [New Hires]
							'NEWHIRE' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'New Hire Report Filing' ),
									'form_description' => TTi18n::getText( 'Report of New Hires' ),
									'note'             => '',
									'tax_codes'        => [],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //As Required, within 20 days of hire date.
															 //On Hire
															 [
																	 'status_id'           => 10, //Enabled
																	 'frequency_id'        => 90100, //On Hire
																	 'due_date_delay_days' => 20,
																	 'reminder_days'       => 0,
															 ],
									],
							],

	],
	'20:US:MN:00:0040' => [ //Child Support
							'CHILDSUPPORT' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'Child Support Withholding Payment' ),
									'form_description' => TTi18n::getText( 'Child Support Withholding Payment' ),
									'note'             => '',
									'tax_codes'        => [ 'CHILDSUPPORT' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Per Pay Period
										[
												'status_id'           => 10, //Enabled
												'frequency_id'        => 1000, //Per Pay Period
												'due_date_delay_days' => 14,
												'reminder_days'       => 5,
										],
									],
							],
	],

	//MO - Missouri
	'20:US:MO:00:0010' => [ //State Government [State Income Tax]
							'FW2'    => [
									'form_code'        => 'W2', //Includes W3
									'form_name'        => TTi18n::getText( 'W2 State Filing' ),
									'form_description' => TTi18n::getText( 'Wage and Tax Statement' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'EFILE' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Annual
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 1, //Jan
																	 'primary_day_of_month' => 31,
																	 'reminder_days'        => 14,
															 ],
									],
							],
							'MO941'  => [
									'form_code'        => 'MO-941',
									'form_name'        => TTi18n::getText( 'MO-941 Payment' ),
									'form_description' => TTi18n::getText( 'Employer\'s Return of Income Taxes Withheld' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Monthly: Due the 15th of the following month for the 1st two months of the quarter. Due the last day of the month following the 3rd month of the quarter.
															 //Quarterly: Due the last day of the month following the end of the quarter. (April 30, July 31, October 31, and January 31).
															 //Annual: Due January 31 of the following year. go over $500 prior to December 1, then it is due the last day of February.

															 //Annual
															 [
																	 'status_id'            => 20, //Disabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 1, //Jan
																	 'primary_day_of_month' => 31, //Last Day
																	 'reminder_days'        => 14,
															 ],

															 //Quarterly
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 3000, //Quarterly
																	 'quarter_month'        => 1,
																	 'primary_day_of_month' => 31, //Last Day
																	 'reminder_days'        => 14,
															 ],
															 //Monthly
															 [
																	 'status_id'            => 20, //Disabled
																	 'frequency_id'         => 60000, //US - Monthly (15th, 30th on Last MoQ)
																	 'primary_day_of_month' => 15,
																	 'reminder_days'        => 7,
															 ],
									],
							],
							'MO941P' => [
									'form_code'        => 'MO-941P',
									'form_name'        => TTi18n::getText( 'MO-941P Payment (Electronic Only)' ),
									'form_description' => TTi18n::getText( 'Quarter-Monthly Withholding Tax Payments' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //The first seven days of the calendar month.
															 //2. The 8th to the 15th day of the calendar month.
															 //3. The 16th to the 22nd day of the calendar month.
															 //4. The 23rd day to the end of the calendar month.
															 //As a quarter-monthly filer, you are required to pay at least 90 percent of the actual tax due within three banking day following the end of the quarter-monthly period.

															 //Quarter-Monthly
															 [
																	 'status_id'     => 20, //Disabled
																	 'frequency_id'  => 62000, //Quarter-Monthly
																	 'reminder_days' => 14,
															 ],


									],
							],
	],
	'20:US:MO:00:0020' => [ //State Government [Unemployment Insurance]
							'MODES47' => [
									'form_code'        => 'MODES-4-7',
									'form_name'        => TTi18n::getText( 'MODES-4-7 Filing' ),
									'form_description' => TTi18n::getText( 'Employer\'s Unemployment Insurance' ),
									'note'             => '',
									'tax_codes'        => [ 'UI' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Quarterly - Due the last day of the month following the end of the quarter. (April 30, July 31, October 31, and January 31)
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 3000, //Quarterly
																	 'quarter_month'        => 1,
																	 'primary_day_of_month' => 31, //Last day
																	 'reminder_days'        => 14,
															 ],
									],
							],
	],
	'20:US:MO:00:0030' => [ //State Government [New Hires]
							'NEWHIRE' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'New Hire Report Filing' ),
									'form_description' => TTi18n::getText( 'Report of New Hires' ),
									'note'             => '',
									'tax_codes'        => [],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //As Required, within 20 days of hire date.
															 //On Hire
															 [
																	 'status_id'           => 10, //Enabled
																	 'frequency_id'        => 90100, //On Hire
																	 'due_date_delay_days' => 20,
																	 'reminder_days'       => 0,
															 ],
									],
							],

	],
	'20:US:MO:00:0040' => [ //Child Support
							'CHILDSUPPORT' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'Child Support Withholding Payment' ),
									'form_description' => TTi18n::getText( 'Child Support Withholding Payment' ),
									'note'             => '',
									'tax_codes'        => [ 'CHILDSUPPORT' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Per Pay Period
										[
												'status_id'           => 10, //Enabled
												'frequency_id'        => 1000, //Per Pay Period
												'due_date_delay_days' => 14,
												'reminder_days'       => 5,
										],
									],
							],
	],

	//MS - Mississippi
	'20:US:MS:00:0010' => [ //State Government [State Income Tax]
							'FW2'    => [
									'form_code'        => 'W2', //Includes W3
									'form_name'        => TTi18n::getText( 'W2 State Filing' ),
									'form_description' => TTi18n::getText( 'Wage and Tax Statement' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'EFILE' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Annual
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 1, //Jan
																	 'primary_day_of_month' => 31,
																	 'reminder_days'        => 14,
															 ],
									],
							],
							'89105'  => [
									'form_code'        => '89-105',
									'form_name'        => TTi18n::getText( '89-105 Payment' ),
									'form_description' => TTi18n::getText( 'Employer\'s Withholding Tax Return' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Monthly: Due the 15th of the following month for the 1st two months of the quarter. Due the last day of the month following the 3rd month of the quarter.
															 //Quarterly: Due the 15th of the month following the end of the quarter. (April 30, July 31, October 31, and January 31).
															 //Annual: Due January 31 of the following year. go over $500 prior to December 1, then it is due the last day of February.

															 //Quarterly
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 3000, //Quarterly
																	 'quarter_month'        => 1,
																	 'primary_day_of_month' => 15,
																	 'reminder_days'        => 14,
															 ],
															 //Monthly
															 [
																	 'status_id'            => 20, //Disabled
																	 'frequency_id'         => 4100, //Monthly
																	 'primary_day_of_month' => 15,
																	 'reminder_days'        => 7,
															 ],
									],
							],
							'89-140' => [
									'form_code'        => '89-140',
									'form_name'        => TTi18n::getText( '89-140 Filing' ),
									'form_description' => TTi18n::getText( 'Annual Information Return' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [ 'CHECK' ],
									'flags'            => [
											'include_w2'          => true,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Annual
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 1, //Jan
																	 'primary_day_of_month' => 31,
																	 'reminder_days'        => 14,
															 ],
									],
							],
	],
	'20:US:MS:00:0020' => [ //State Government [Unemployment Insurance]
							'UI2' => [
									'form_code'        => 'UI-2',
									'form_name'        => TTi18n::getText( 'UI-2 Filing' ),
									'form_description' => TTi18n::getText( 'Employer\'s Quarterly Wage Report' ),
									'note'             => '',
									'tax_codes'        => [ 'UI' ],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [ 'CHECK' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Quarterly - Due the last day of the month following the end of the quarter. (April 30, July 31, October 31, and January 31)
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 3000, //Quarterly
																	 'quarter_month'        => 1,
																	 'primary_day_of_month' => 31, //Last day
																	 'reminder_days'        => 14,
															 ],
									],
							],
	],
	'20:US:MS:00:0030' => [ //State Government [New Hires]
							'NEWHIRE' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'New Hire Report Filing' ),
									'form_description' => TTi18n::getText( 'Report of New Hires' ),
									'note'             => '',
									'tax_codes'        => [],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //As Required, within 20 days of hire date.
															 //On Hire
															 [
																	 'status_id'           => 10, //Enabled
																	 'frequency_id'        => 90100, //On Hire
																	 'due_date_delay_days' => 20,
																	 'reminder_days'       => 0,
															 ],
									],
							],

	],
	'20:US:MS:00:0040' => [ //Child Support
							'CHILDSUPPORT' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'Child Support Withholding Payment' ),
									'form_description' => TTi18n::getText( 'Child Support Withholding Payment' ),
									'note'             => '',
									'tax_codes'        => [ 'CHILDSUPPORT' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Per Pay Period
										[
												'status_id'           => 10, //Enabled
												'frequency_id'        => 1000, //Per Pay Period
												'due_date_delay_days' => 14,
												'reminder_days'       => 5,
										],
									],
							],
	],
	'20:US:MS:00:0050' => [ //State Government [Retirement]
							'RETIREMENT' => [
									'form_code'        => 'RETIREMENT',
									'form_name'        => TTi18n::getText( 'Retirement' ),
									'form_description' => TTi18n::getText( 'Employer\'s Monthly Report' ),
									'note'             => '',
									'tax_codes'        => [ 'RT' ],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [ 'CHECK' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Monthly
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 4100, //Monthly
																	 'primary_day_of_month' => 5, //5th working day of month following the end of the month
																	 'reminder_days'        => 5,
															 ],
									],
							],
	],
	//MT - Montana
	'20:US:MT:00:0010' => [ //State Government [State Income Tax]
							'FW2' => [
									'form_code'        => 'W2', //Includes W3
									'form_name'        => TTi18n::getText( 'W2 State Filing' ),
									'form_description' => TTi18n::getText( 'Wage and Tax Statement' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'EFILE' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Annual
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 1, //Jan
																	 'primary_day_of_month' => 31,
																	 'reminder_days'        => 14,
															 ],
									],
							],
							'MW1' => [
									'form_code'        => 'MW-1',
									'form_name'        => TTi18n::getText( 'MW-1 Payment' ),
									'form_description' => TTi18n::getText( 'Withholding Payment Form' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Accelerated: Employers have to deposit State withholding tax at the same frequency as required for their Federal withholding tax deposits.
															 //Semiweekly: Due the following Friday (for pay dates that fall on Saturday through Tuesday). Due the following Wednesday (for pay dates that fall on Wednesday through Friday).
															 //Exception: If you have two paydays within one deposit period but the paydays are in separate quarters, you must make two separate payments.
															 //Monthly: Due the 15th of the following month.
															 //Annual: Due February 28 of the following year.

															 //Semi-Weekly
															 [
																	 'status_id'     => 20, //Disabled
																	 'frequency_id'  => 64000, //Semi-Weekly
																	 'reminder_days' => 3,
															 ],

															 //Monthly
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 4100, //Monthly
																	 'primary_day_of_month' => 15,
																	 'reminder_days'        => 7,
															 ],

															 //Annual
															 [
																	 'status_id'            => 20, //Disabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 2, //Jan
																	 'primary_day_of_month' => 28,
																	 'reminder_days'        => 14,
															 ],
									],
							],
							'MW3' => [
									'form_code'        => 'MW-3',
									'form_name'        => TTi18n::getText( 'MW-3 Filing' ),
									'form_description' => TTi18n::getText( 'Annual Withholding Tax Reconciliation' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK' ],
									'flags'            => [
											'include_w2'          => true,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Annual
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 1, //Jan
																	 'primary_day_of_month' => 31,
																	 'reminder_days'        => 14,
															 ],
									],
							],
	],
	'20:US:MT:00:0020' => [ //State Government [Unemployment Insurance]
							'UI5' => [
									'form_code'        => 'UI-5',
									'form_name'        => TTi18n::getText( 'UI-5 Filing' ),
									'form_description' => TTi18n::getText( 'Employer\'s Unemployment Insurance Quarterly Wage Report' ),
									'note'             => '',
									'tax_codes'        => [ 'UI' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Quarterly - Due the last day of the month following the end of the quarter. (April 30, July 31, October 31, and January 31)
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 3000, //Quarterly
																	 'quarter_month'        => 1,
																	 'primary_day_of_month' => 31, //Last day
																	 'reminder_days'        => 14,
															 ],
									],
							],
	],
	'20:US:MT:00:0030' => [ //State Government [New Hires]
							'NEWHIRE' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'New Hire Report Filing' ),
									'form_description' => TTi18n::getText( 'Report of New Hires' ),
									'note'             => '',
									'tax_codes'        => [],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //As Required, within 20 days of hire date.
															 //On Hire
															 [
																	 'status_id'           => 10, //Enabled
																	 'frequency_id'        => 90100, //On Hire
																	 'due_date_delay_days' => 20,
																	 'reminder_days'       => 0,
															 ],
									],
							],

	],
	'20:US:MT:00:0040' => [ //Child Support
							'CHILDSUPPORT' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'Child Support Withholding Payment' ),
									'form_description' => TTi18n::getText( 'Child Support Withholding Payment' ),
									'note'             => '',
									'tax_codes'        => [ 'CHILDSUPPORT' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Per Pay Period
										[
												'status_id'           => 10, //Enabled
												'frequency_id'        => 1000, //Per Pay Period
												'due_date_delay_days' => 14,
												'reminder_days'       => 5,
										],
									],
							],
	],

	//NC - North Carolina
	'20:US:NC:00:0010' => [ //State Government [State Income Tax]
							'FW2'  => [
									'form_code'        => 'W2', //Includes W3
									'form_name'        => TTi18n::getText( 'W2 State Filing' ),
									'form_description' => TTi18n::getText( 'Wage and Tax Statement' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'EFILE' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Annual
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 1, //Jan
																	 'primary_day_of_month' => 31,
																	 'reminder_days'        => 14,
															 ],
									],
							],
							'NC5Q' => [
									'form_code'        => 'NC-5Q',
									'form_name'        => TTi18n::getText( 'NC-5Q (Semi-weekly) Filing' ),
									'form_description' => TTi18n::getText( 'Withholding Return (Semi-weekly payers only)' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Quarterly
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 3000, //Quarterly
																	 'quarter_month'        => 1,
																	 'primary_day_of_month' => 31, //Last day
																	 'reminder_days'        => 14,
															 ],
									],
							],
							'NC5P' => [
									'form_code'        => 'NC-5P',
									'form_name'        => TTi18n::getText( 'NC-5P Payment' ),
									'form_description' => TTi18n::getText( 'Withholding Payment Voucher' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Due the following Friday (for pay dates that fall on Saturday through Tuesday). Due the following Wednesday (for pay dates that fall on Wednesday through Friday).

															 //US - Semi-Weekly
															 [
																	 'status_id'           => 20, //Disabled
																	 'frequency_id'        => 64000, //US - Semi-Weekly
																	 'due_date_delay_days' => 0,
																	 'reminder_days'       => 1,
															 ],
									],
							],
							'NC5'  => [
									'form_code'        => 'NC-5',
									'form_name'        => TTi18n::getText( 'NC-5 Payment' ),
									'form_description' => TTi18n::getText( 'Withholding Return (Quarterly or Monthly Filers)' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Monthly: Due the 15th of the following month except for December which is due January 31 of the following year.
															 //Quarterly: Due the last day of the month following the end of the quarter. (April 30, July 31, October 31, and January 31).

															 //Monthly
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 4100, //Monthly
																	 'primary_day_of_month' => 15,
																	 'reminder_days'        => 7,
															 ],

															 //Quarterly
															 [
																	 'status_id'            => 20, //Disabled
																	 'frequency_id'         => 3000, //Quarterly
																	 'quarter_month'        => 1,
																	 'primary_day_of_month' => 31, //Last day
																	 'reminder_days'        => 14,
															 ],
									],
							],
							'NC3'  => [
									'form_code'        => 'NC-3',
									'form_name'        => TTi18n::getText( 'NC-3 Filing' ),
									'form_description' => TTi18n::getText( 'Annual Withholding Reconciliation' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => true,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Annual
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 1, //Jan
																	 'primary_day_of_month' => 31,
																	 'reminder_days'        => 14,
															 ],
									],
							],
	],
	'20:US:NC:00:0020' => [ //State Government [Unemployment Insurance]
							'NCUI101' => [
									'form_code'        => 'NCUI 101',
									'form_name'        => TTi18n::getText( 'NCUI 101 Filing' ),
									'form_description' => TTi18n::getText( 'Employer\'s Quarterly Tax and Wage Report' ),
									'note'             => '',
									'tax_codes'        => [ 'UI' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Quarterly - Due the last day of the month following the end of the quarter. (April 30, July 31, October 31, and January 31)
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 3000, //Quarterly
																	 'quarter_month'        => 1,
																	 'primary_day_of_month' => 31, //Last day
																	 'reminder_days'        => 14,
															 ],
									],
							],
	],
	'20:US:NC:00:0030' => [ //State Government [New Hires]
							'NEWHIRE' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'New Hire Report Filing' ),
									'form_description' => TTi18n::getText( 'Report of New Hires' ),
									'note'             => '',
									'tax_codes'        => [],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //As Required, within 20 days of hire date.
															 //On Hire
															 [
																	 'status_id'           => 10, //Enabled
																	 'frequency_id'        => 90100, //On Hire
																	 'due_date_delay_days' => 20,
																	 'reminder_days'       => 0,
															 ],
									],
							],

	],
	'20:US:NC:00:0040' => [ //Child Support
							'CHILDSUPPORT' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'Child Support Withholding Payment' ),
									'form_description' => TTi18n::getText( 'Child Support Withholding Payment' ),
									'note'             => '',
									'tax_codes'        => [ 'CHILDSUPPORT' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Per Pay Period
										[
												'status_id'           => 10, //Enabled
												'frequency_id'        => 1000, //Per Pay Period
												'due_date_delay_days' => 14,
												'reminder_days'       => 5,
										],
									],
							],
	],

	//ND - North Dakota
	'20:US:ND:00:0010' => [ //State Government [State Income Tax]
							'FW2' => [
									'form_code'        => 'W2', //Includes W3
									'form_name'        => TTi18n::getText( 'W2 State Filing' ),
									'form_description' => TTi18n::getText( 'Wage and Tax Statement' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'EFILE' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Annual
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 1, //Jan
																	 'primary_day_of_month' => 31,
																	 'reminder_days'        => 14,
															 ],
									],
							],
							'306' => [
									'form_code'        => '306',
									'form_name'        => TTi18n::getText( 'Form 306 Payment' ),
									'form_description' => TTi18n::getText( 'Income Tax Withholding Return' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Quarterly: Due the last day of the month following the end of the quarter. (April 30, July 31, October 31, and January 31).
															 //Annual: Due January 31 following the year.

															 //Quarterly
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 3000, //Quarterly
																	 'quarter_month'        => 1,
																	 'primary_day_of_month' => 31, //Last day
																	 'reminder_days'        => 14,
															 ],

															 //Annual
															 [
																	 'status_id'            => 20, //Disabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 1, //Jan
																	 'primary_day_of_month' => 31,
																	 'reminder_days'        => 14,
															 ],
									],
							],
							'307' => [
									'form_code'        => '307',
									'form_name'        => TTi18n::getText( 'Form 307 Filing' ),
									'form_description' => TTi18n::getText( 'Transmittal of Wage and Tax Statement' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => true,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Annual
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 1, //Jan
																	 'primary_day_of_month' => 31,
																	 'reminder_days'        => 14,
															 ],
									],
							],
	],
	'20:US:ND:00:0020' => [ //State Government [Unemployment Insurance]
							'SFN41263' => [
									'form_code'        => 'SFN 41263',
									'form_name'        => TTi18n::getText( 'SFN 41263 Filing' ),
									'form_description' => TTi18n::getText( 'Employer\'s Contribution and Wage Report' ),
									'note'             => '',
									'tax_codes'        => [ 'UI' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Quarterly - Due the last day of the month following the end of the quarter. (April 30, July 31, October 31, and January 31)
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 3000, //Quarterly
																	 'quarter_month'        => 1,
																	 'primary_day_of_month' => 31, //Last day
																	 'reminder_days'        => 14,
															 ],
									],
							],
	],
	'20:US:ND:00:0030' => [ //State Government [New Hires]
							'NEWHIRE' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'New Hire Report Filing' ),
									'form_description' => TTi18n::getText( 'Report of New Hires' ),
									'note'             => '',
									'tax_codes'        => [],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //As Required, within 20 days of hire date.
															 //On Hire
															 [
																	 'status_id'           => 10, //Enabled
																	 'frequency_id'        => 90100, //On Hire
																	 'due_date_delay_days' => 20,
																	 'reminder_days'       => 0,
															 ],
									],
							],

	],
	'20:US:ND:00:0040' => [ //Child Support
							'CHILDSUPPORT' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'Child Support Withholding Payment' ),
									'form_description' => TTi18n::getText( 'Child Support Withholding Payment' ),
									'note'             => '',
									'tax_codes'        => [ 'CHILDSUPPORT' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Per Pay Period
										[
												'status_id'           => 10, //Enabled
												'frequency_id'        => 1000, //Per Pay Period
												'due_date_delay_days' => 14,
												'reminder_days'       => 5,
										],
									],
							],
	],
	'20:US:ND:00:0100' => [ //Workers Compensation
							'WC' => [
									'form_code'        => 'WC',
									'form_name'        => TTi18n::getText( 'WC Filing' ),
									'form_description' => TTi18n::getText( 'Workers Compensation Annual Report' ),
									'note'             => '',
									'tax_codes'        => [ 'WC' ],
									'filing_methods'   => [ 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Annual - Based on their own renewal/anniversary date, so just leave disabled by default for now.
										[
												'status_id'            => 20, //Disabled
												'frequency_id'         => 2000, //Annual
												'primary_month'        => 1, //Jan
												'primary_day_of_month' => 31,
												'reminder_days'        => 14,
										],
									],
							],
	],

	//NE - Nebraska
	'20:US:NE:00:0010' => [ //State Government [State Income Tax]
							'FW2'  => [
									'form_code'        => 'W2', //Includes W3
									'form_name'        => TTi18n::getText( 'W2 State Filing' ),
									'form_description' => TTi18n::getText( 'Wage and Tax Statement' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'EFILE' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Annual
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 1, //Jan
																	 'primary_day_of_month' => 31,
																	 'reminder_days'        => 14,
															 ],
									],
							],
							'941N' => [
									'form_code'        => '941N',
									'form_name'        => TTi18n::getText( '941N Filing' ),
									'form_description' => TTi18n::getText( 'Withholding Return (Quarterly or Monthly Filers)' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Quarterly: Due the last day of the month following the end of the quarter. (April 30, July 31, October 31, and January 31).
															 //Annual: Due by January 31 of the following year.

															 //Quarterly
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 3000, //Quarterly
																	 'quarter_month'        => 1,
																	 'primary_day_of_month' => 31, //Last day
																	 'reminder_days'        => 14,
															 ],

															 //Annual
															 [
																	 'status_id'            => 20, //Disabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 1, //Jan
																	 'primary_day_of_month' => 31,
																	 'reminder_days'        => 14,
															 ],
									],
							],
							'W3N'  => [
									'form_code'        => 'W3N',
									'form_name'        => TTi18n::getText( 'W3N Filing' ),
									'form_description' => TTi18n::getText( 'Reconciliation of Income Tax Withheld' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [ 'CHECK' ],
									'flags'            => [
											'include_w2'          => true,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Annual
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 1, //Jan
																	 'primary_day_of_month' => 31,
																	 'reminder_days'        => 14,
															 ],
									],
							],
							'501N' => [
									'form_code'        => '501N',
									'form_name'        => TTi18n::getText( '501N Payment' ),
									'form_description' => TTi18n::getText( 'Monthly Withholding Deposit' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EFT' ],
									'flags'            => [
											'include_w2'          => true,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Due 15th day of the following month for the 1st and 2nd months of the quarter.
										//Note: Form 941N is used to pay the taxes for January, April, July and October.

										//Monthly
										[
												'status_id'            => 10, //Enabled
												'frequency_id'         => 60100, //Monthly (15th, skip Last MoQ)
												'primary_day_of_month' => 15,
												'reminder_days'        => 7,
										],
									],
							],
	],
	'20:US:NE:00:0020' => [ //State Government [Unemployment Insurance]
							'UI11W' => [
									'form_code'        => 'UI 11W',
									'form_name'        => TTi18n::getText( 'UI 11W Filing' ),
									'form_description' => TTi18n::getText( 'Wage Report' ),
									'note'             => '',
									'tax_codes'        => [ 'UI' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Quarterly - Due the last day of the month following the end of the quarter. (April 30, July 31, October 31, and January 31)
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 3000, //Quarterly
																	 'quarter_month'        => 1,
																	 'primary_day_of_month' => 31, //Last day
																	 'reminder_days'        => 14,
															 ],
									],
							],
	],
	'20:US:NE:00:0030' => [ //State Government [New Hires]
							'NEWHIRE' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'New Hire Report Filing' ),
									'form_description' => TTi18n::getText( 'Report of New Hires' ),
									'note'             => '',
									'tax_codes'        => [],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //As Required, within 20 days of hire date.
															 //On Hire
															 [
																	 'status_id'           => 10, //Enabled
																	 'frequency_id'        => 90100, //On Hire
																	 'due_date_delay_days' => 20,
																	 'reminder_days'       => 0,
															 ],
									],
							],
	],
	'20:US:NE:00:0040' => [ //Child Support
							'CHILDSUPPORT' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'Child Support Withholding Payment' ),
									'form_description' => TTi18n::getText( 'Child Support Withholding Payment' ),
									'note'             => '',
									'tax_codes'        => [ 'CHILDSUPPORT' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Per Pay Period
										[
												'status_id'           => 10, //Enabled
												'frequency_id'        => 1000, //Per Pay Period
												'due_date_delay_days' => 14,
												'reminder_days'       => 5,
										],
									],
							],
	],
	'20:US:NE:00:0050' => [ //State Government [Retirement]
							'RETIREMENT' => [
									'form_code'        => 'RETIREMENT',
									'form_name'        => TTi18n::getText( 'Retirement' ),
									'form_description' => TTi18n::getText( 'Employer\'s Monthly Report' ),
									'note'             => '',
									'tax_codes'        => [ 'RT' ],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [ 'CHECK' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Monthly
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 4100, //Monthly
																	 'primary_day_of_month' => 5, //5th working day of month following the end of the month
																	 'reminder_days'        => 5,
															 ],
									],
							],
	],

	//NH - New Hampshire
	'20:US:NH:00:0010' => [ //State Government [State Income Tax]

	],
	'20:US:NH:00:0020' => [ //State Government [Unemployment Insurance]
							//								 'REPORT'         => array(
							//										 'form_code'        => '',
							//										 'form_name'        => TTi18n::getText( 'Employer Quarterly Wage Report Filing' ),
							//										 'form_description' => TTi18n::getText( 'Employer Quarterly Wage Report' ),
							//										 'note'             => '',
							//										 'tax_codes'        => array('UI'),
							//										 'filing_methods'   => array('PRINT', 'EFILE'),
							//										 'payment_methods'  => array(),
							//										 'flags'            => array(
							//												 'include_w2'          => FALSE,
							//												 'file_zero_wage'      => TRUE,
							//												 'file_zero_liability' => TRUE,
							//												 'auto_file'           => FALSE,
							//												 'auto_pay'            => FALSE,
							//										 ),
							//										 'frequency'        => array(  //Quarterly - Due the last day of the month following the end of the quarter. (April 30, July 31, October 31, and January 31)
							//																	   array(
							//																			   'status_id'            => 10, //Enabled
							//																			   'frequency_id'         => 3000, //Quarterly
							//																			   'quarter_month'        => 1,
							//																			   'primary_day_of_month' => 31, //Last day
							//																			   'reminder_days'        => 14,
							//																	   ),
							//										 ),
							//								 ),
							//Report and Payment are combined.
							'UI' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'Employer Quarterly Tax & Wage Filing' ),
									'form_description' => TTi18n::getText( 'Employer Quarterly Tax & Wage Report Filing' ),
									'note'             => '',
									'tax_codes'        => [ 'UI' ],
									'filing_methods'   => [ 'PRINT', ],
									'payment_methods'  => [ 'CHECK' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Quarterly - Due the last day of the month following the end of the quarter. (April 30, July 31, October 31, and January 31)
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 3000, //Quarterly
																	 'quarter_month'        => 1,
																	 'primary_day_of_month' => 31, //Last day
																	 'reminder_days'        => 14,
															 ],
									],
							],

	],
	'20:US:NH:00:0030' => [ //State Government [New Hires]
							'NEWHIRE' => [
									'form_code'        => 'NHES 0085',
									'form_name'        => TTi18n::getText( 'NHES 0085 New Hire Report Filing' ),
									'form_description' => TTi18n::getText( 'Report of New Hires' ),
									'note'             => '',
									'tax_codes'        => [],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //As Required, within 20 days of hire date.
															 //On Hire
															 [
																	 'status_id'           => 10, //Enabled
																	 'frequency_id'        => 90100, //On Hire
																	 'due_date_delay_days' => 20,
																	 'reminder_days'       => 0,
															 ],
									],
							],

	],
	'20:US:NH:00:0040' => [ //Child Support
							'CHILDSUPPORT' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'Child Support Withholding Payment' ),
									'form_description' => TTi18n::getText( 'Child Support Withholding Payment' ),
									'note'             => '',
									'tax_codes'        => [ 'CHILDSUPPORT' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Per Pay Period
										[
												'status_id'           => 10, //Enabled
												'frequency_id'        => 1000, //Per Pay Period
												'due_date_delay_days' => 14,
												'reminder_days'       => 5,
										],
									],
							],
	],

	//NJ - New Jersey
	'20:US:NJ:00:0010' => [ //State Government [State Income Tax]
							'FW2'       => [ //Call this FW2 as it only used for electronic filing, and mostly uses the SSA format anyways.
											 'form_code'        => 'NJ-EFW2-S',
											 'form_name'        => TTi18n::getText( 'NJ-EFW2-S Filing' ),
											 'form_description' => TTi18n::getText( 'NJ-EFW2-S' ),
											 'note'             => '',
											 'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
											 'filing_methods'   => [ 'PRINT', 'EFILE' ],
											 'payment_methods'  => [],
											 'flags'            => [
													 'include_w2'          => true,
													 'file_zero_wage'      => true,
													 'file_zero_liability' => true,
													 'auto_file'           => false,
													 'auto_pay'            => false,
											 ],
											 'frequency'        => [  //Annual
																	  [
																			  'status_id'            => 10, //Enabled
																			  'frequency_id'         => 2000, //Annual
																			  'primary_month'        => 2, //Feb
																			  'primary_day_of_month' => 29, //Last Day
																			  'reminder_days'        => 14,
																	  ],
											 ],
							],
							'NJW3'      => [
									'form_code'        => 'NJ-W-3',
									'form_name'        => TTi18n::getText( 'NJ-W-3 Filing' ),
									'form_description' => TTi18n::getText( 'Gross Income Tax Reconciliation of Tax Withheld' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Annual
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 2, //Feb
																	 'primary_day_of_month' => 29, //Last Day
																	 'reminder_days'        => 14,
															 ],
									],
							],
							'NJ927'     => [
									'form_code'        => 'NJ-927',
									'form_name'        => TTi18n::getText( 'NJ-927 Filing (Electronic Only)' ),
									'form_description' => TTi18n::getText( 'Employers Quarterly Report' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE', 'UI' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [    //Quarterly
															   [
																	   'status_id'            => 10, //Enabled
																	   'frequency_id'         => 3000, //Quarterly
																	   'quarter_month'        => 1,
																	   'primary_day_of_month' => 30,
																	   'reminder_days'        => 7,
															   ],
									],
							],
							'NJPAYMENT' => [
									'form_code'        => 'NJ-PAYMENT',
									'form_name'        => TTi18n::getText( 'NJ-Payment (Electronic Only)' ),
									'form_description' => TTi18n::getText( 'Employer\'s Payment Voucher for Income Tax Withheld.' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [        //Monthly: Due by the 15th of the next month for the 1st and 2nd month of the quarter. Due by the 30th of the next month for the 3rd month of the quarter using Form NJ-927.

																   //Monthly
																   [
																		   'status_id'            => 10, //Enabled
																		   'frequency_id'         => 60000, //Monthly (15th, 30th on Last MoQ)
																		   'primary_day_of_month' => 15,
																		   'reminder_days'        => 7,
																   ],
									],
							],
	],
	'20:US:NJ:00:0020' => [ //State Government [Unemployment Insurance]
							'WR30' => [
									'form_code'        => 'WR-30',
									'form_name'        => TTi18n::getText( 'WR-30 Filing' ),
									'form_description' => TTi18n::getText( 'Employer Report of Wages Paid' ),
									'note'             => '',
									'tax_codes'        => [ 'UI' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Quarterly - Due the 30th of the month following the end of the quarter. (April 30, July 30, October 30, and January 30)
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 3000, //Quarterly
																	 'quarter_month'        => 1,
																	 'primary_day_of_month' => 30, //30th, not the last day.
																	 'reminder_days'        => 14,
															 ],
									],
							],
	],
	'20:US:NJ:00:0030' => [ //State Government [New Hires]
							'NEWHIRE' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'New Hire Report Filing' ),
									'form_description' => TTi18n::getText( 'Report of New Hires' ),
									'note'             => '',
									'tax_codes'        => [],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //As Required, within 20 days of hire date.
															 //On Hire
															 [
																	 'status_id'           => 10, //Enabled
																	 'frequency_id'        => 90100, //On Hire
																	 'due_date_delay_days' => 20,
																	 'reminder_days'       => 0,
															 ],
									],
							],

	],
	'20:US:NJ:00:0040' => [ //Child Support
							'CHILDSUPPORT' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'Child Support Withholding Payment' ),
									'form_description' => TTi18n::getText( 'Child Support Withholding Payment' ),
									'note'             => '',
									'tax_codes'        => [ 'CHILDSUPPORT' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Per Pay Period
										[
												'status_id'           => 10, //Enabled
												'frequency_id'        => 1000, //Per Pay Period
												'due_date_delay_days' => 14,
												'reminder_days'       => 5,
										],
									],
							],
	],

	//NM - New Mexico
	'20:US:NM:00:0010' => [ //State Government [State Income Tax]
							'FW2'      => [
									'form_code'        => 'W2', //Includes W3
									'form_name'        => TTi18n::getText( 'W2 State Filing' ),
									'form_description' => TTi18n::getText( 'Wage and Tax Statement' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'EFILE' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Annual
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 1, //Jan
																	 'primary_day_of_month' => 31,
																	 'reminder_days'        => 14,
															 ],
									],
							],
							'RPD41072' => [
									'form_code'        => 'RPD-41072',
									'form_name'        => TTi18n::getText( 'RPD-41072 Filing' ),
									'form_description' => TTi18n::getText( 'RPD-41072 - Annual Withholding Form' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [ 'CHECK' ],
									'flags'            => [
											'include_w2'          => true,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Annual
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 2, //Feb
																	 'primary_day_of_month' => 29,
																	 'reminder_days'        => 14,
															 ],
									],
							],
							'ES903A'   => [
									'form_code'        => 'ES-903A',
									'form_name'        => TTi18n::getText( 'ES-903A Filing' ),
									'form_description' => TTi18n::getText( 'Employer\'s Quarterly Wage and Contribution Report' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE', 'UI' ],
									'filing_methods'   => [ 'EFILE' ],
									'payment_methods'  => [ 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Quarterly
										[
												'status_id'            => 10, //Enabled
												'frequency_id'         => 3000, //Quarterly
												'quarter_month'        => 1,
												'primary_day_of_month' => 31, //Last day
												'reminder_days'        => 7,
										],
									],
							],
							'CRS1'     => [
									'form_code'        => 'CRS-1',
									'form_name'        => TTi18n::getText( 'CRS-1 Payment' ),
									'form_description' => TTi18n::getText( 'Combined Report Form' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [ //Due on the 25th of the month following the end of your reporting period.

															//Monthly
															[
																	'status_id'            => 20, //Disabled
																	'frequency_id'         => 4100, //Monthly
																	'primary_day_of_month' => 25,
																	'reminder_days'        => 7,
															],

															//Quarterly
															[
																	'status_id'            => 10, //Enabled
																	'frequency_id'         => 3000, //Quarterly
																	'quarter_month'        => 1,
																	'primary_day_of_month' => 25,
																	'reminder_days'        => 7,
															],

									],
							],

	],
	'20:US:NM:00:0020' => [ //State Government [Unemployment Insurance]

	],
	'20:US:NM:00:0030' => [ //State Government [New Hires]
							'NEWHIRE' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'New Hire Report Filing' ),
									'form_description' => TTi18n::getText( 'Report of New Hires' ),
									'note'             => '',
									'tax_codes'        => [],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //As Required, within 20 days of hire date.
															 //On Hire
															 [
																	 'status_id'           => 10, //Enabled
																	 'frequency_id'        => 90100, //On Hire
																	 'due_date_delay_days' => 20,
																	 'reminder_days'       => 0,
															 ],
									],
							],

	],
	'20:US:NM:00:0100' => [ //Workers Compensation
							'WC1' => [
									'form_code'        => 'WC-1',
									'form_name'        => TTi18n::getText( 'WC-1 Fee Filing' ),
									'form_description' => TTi18n::getText( 'Workers\' Compensation Fee Form' ),
									'note'             => '',
									'tax_codes'        => [ 'WC' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Quarterly - Due the last day of the month following the end of the quarter. (April 30, July 31, October 31, and January 31)
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 3000, //Quarterly
																	 'quarter_month'        => 1,
																	 'primary_day_of_month' => 31, //Last day
																	 'reminder_days'        => 14,
															 ],
									],
							],
	],
	'20:US:NM:00:0040' => [ //Child Support
							'CHILDSUPPORT' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'Child Support Withholding Payment' ),
									'form_description' => TTi18n::getText( 'Child Support Withholding Payment' ),
									'note'             => '',
									'tax_codes'        => [ 'CHILDSUPPORT' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Per Pay Period
										[
												'status_id'           => 10, //Enabled
												'frequency_id'        => 1000, //Per Pay Period
												'due_date_delay_days' => 14,
												'reminder_days'       => 5,
										],
									],
							],
	],

	//NV - Nevada
	'20:US:NV:00:0010' => [ //State Government [State Income Tax]

	],
	'20:US:NV:00:0020' => [ //State Government [Unemployment Insurance]
							'RPT3795'    => [ //This superseded the NUCS-4072 form.
											  'form_code'        => 'RPT3795',
											  'form_name'        => TTi18n::getText( 'RPT3795 Filing' ),
											  'form_description' => TTi18n::getText( 'Employer Report of Wages Paid' ),
											  'note'             => '',
											  'tax_codes'        => [ 'UI' ],
											  'filing_methods'   => [ 'PRINT' ],
											  'payment_methods'  => [ 'CHECK' ],
											  'flags'            => [
													  'include_w2'          => false,
													  'file_zero_wage'      => true,
													  'file_zero_liability' => true,
													  'auto_file'           => false,
													  'auto_pay'            => false,
											  ],
											  'frequency'        => [  //Quarterly - Due the last day of the month following the end of the quarter. (April 30, July 31, October 31, and January 31)
																	   [
																			   'status_id'            => 10, //Enabled
																			   'frequency_id'         => 3000, //Quarterly
																			   'quarter_month'        => 1,
																			   'primary_day_of_month' => 31, //Last day
																			   'reminder_days'        => 14,
																	   ],
											  ],
							],
							'TXR-020.04' => [
									'form_code'        => 'TXR-020.04',
									'form_name'        => TTi18n::getText( 'TXR-020.04 Filing' ),
									'form_description' => TTi18n::getText( 'Modified Business Tax Return' ),
									'note'             => '',
									'tax_codes'        => [ 'UI' ],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [ 'CHECK' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Quarterly - Due the last day of the month following the end of the quarter. (April 30, July 31, October 31, and January 31)
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 3000, //Quarterly
																	 'quarter_month'        => 1,
																	 'primary_day_of_month' => 31, //Last day
																	 'reminder_days'        => 14,
															 ],
									],
							],
	],
	'20:US:NV:00:0030' => [ //State Government [New Hires]
							'NEWHIRE' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'New Hire Report Filing' ),
									'form_description' => TTi18n::getText( 'Report of New Hires' ),
									'note'             => '',
									'tax_codes'        => [],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //As Required, within 20 days of hire date.
															 //On Hire
															 [
																	 'status_id'           => 10, //Enabled
																	 'frequency_id'        => 90100, //On Hire
																	 'due_date_delay_days' => 20,
																	 'reminder_days'       => 0,
															 ],
									],
							],

	],
	'20:US:NV:00:0040' => [ //Child Support
							'CHILDSUPPORT' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'Child Support Withholding Payment' ),
									'form_description' => TTi18n::getText( 'Child Support Withholding Payment' ),
									'note'             => '',
									'tax_codes'        => [ 'CHILDSUPPORT' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Per Pay Period
										[
												'status_id'           => 10, //Enabled
												'frequency_id'        => 1000, //Per Pay Period
												'due_date_delay_days' => 14,
												'reminder_days'       => 5,
										],
									],
							],
	],
	'20:US:NV:00:0050' => [ //State Government [Retirement]
							'RETIREMENT' => [
									'form_code'        => 'RETIREMENT',
									'form_name'        => TTi18n::getText( 'Retirement' ),
									'form_description' => TTi18n::getText( 'Employer\'s Monthly Report' ),
									'note'             => '',
									'tax_codes'        => [ 'RT' ],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [ 'CHECK' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Monthly
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 4100, //Monthly
																	 'primary_day_of_month' => 5, //5th working day of month following the end of the month
																	 'reminder_days'        => 5,
															 ],
									],
							],
	],

	//NY - New York
	'20:US:NY:00:0010' => [ //State Government [State Income Tax]
							'NYS45'  => [
									'form_code'        => 'NYS-45',
									'form_name'        => TTi18n::getText( 'NYS-45 Filing' ),
									'form_description' => TTi18n::getText( 'Quarterly Combined Withholding, Wage Reporting, And Unemployment Insurance Return' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE', 'UI' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Quarterly
										[
												'status_id'            => 10, //Enabled
												'frequency_id'         => 3000, //Quarterly
												'quarter_month'        => 1,
												'primary_day_of_month' => 31, //Last day
												'reminder_days'        => 7,
										],
									],
							],
							'MTA305' => [
									'form_code'        => 'MTA-305',
									'form_name'        => TTi18n::getText( 'MTA-305 Transit Filing' ),
									'form_description' => TTi18n::getText( 'Employer\'s Quarterly Metropolitan Transit Tax Form' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME' ],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Quarterly
										[
												'status_id'            => 10, //Enabled
												'frequency_id'         => 3000, //Quarterly
												'quarter_month'        => 1,
												'primary_day_of_month' => 31, //Last day
												'reminder_days'        => 7,
										],
									],
							],
							'NYS1'   => [
									'form_code'        => 'NYS-1',
									'form_name'        => TTi18n::getText( 'NYS-1 Payment' ),
									'form_description' => TTi18n::getText( 'Return of Tax Withheld Payment Coupon' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'EFILE' ],
									'filing_methods'   => [ 'PRINT', 'CHECK' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//3 Days Filers: Within three business days following the payroll period.
										//5 Day Filers: Within 5 business days following the payroll period.

										//Per Pay Period
										[
												'status_id'           => 20, //Disabled
												'frequency_id'        => 1000, //Per Pay Period
												'due_date_delay_days' => 3,
												'reminder_days'       => 2,
										],

										//Per Pay Period
										[
												'status_id'           => 10, //Enabled
												'frequency_id'        => 1000, //Per Pay Period
												'due_date_delay_days' => 5,
												'reminder_days'       => 2,
										],
									],
							],
	],
	//	'20:US:NY:00:0020' => array( //State Government [Unemployment Insurance] //Combined with State Income Tax.
	//
	//	),
	'20:US:NY:00:0030' => [ //State Government [New Hires]
							'NEWHIRE' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'New Hire Report Filing' ),
									'form_description' => TTi18n::getText( 'Report of New Hires' ),
									'note'             => '',
									'tax_codes'        => [],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //As Required, within 20 days of hire date.
															 //On Hire
															 [
																	 'status_id'           => 10, //Enabled
																	 'frequency_id'        => 90100, //On Hire
																	 'due_date_delay_days' => 20,
																	 'reminder_days'       => 0,
															 ],
									],
							],

	],
	'20:US:NY:00:0040' => [ //Child Support
							'CHILDSUPPORT' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'Child Support Withholding Payment' ),
									'form_description' => TTi18n::getText( 'Child Support Withholding Payment' ),
									'note'             => '',
									'tax_codes'        => [ 'CHILDSUPPORT' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Per Pay Period
										[
												'status_id'           => 10, //Enabled
												'frequency_id'        => 1000, //Per Pay Period
												'due_date_delay_days' => 14,
												'reminder_days'       => 5,
										],
									],
							],
	],


	//OH - Ohio
	'20:US:OH:00:0010' => [ //State Government [State Income Tax]
							'FW2'    => [
									'form_code'        => 'W2', //Includes W3
									'form_name'        => TTi18n::getText( 'W2 State Filing' ),
									'form_description' => TTi18n::getText( 'Wage and Tax Statement' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'EFILE' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Annual
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 1, //Jan
																	 'primary_day_of_month' => 31,
																	 'reminder_days'        => 14,
															 ],
									],
							],
							'IT3'    => [
									'form_code'        => 'IT-3',
									'form_name'        => TTi18n::getText( 'IT-3 Filing' ),
									'form_description' => TTi18n::getText( 'Transmittal of Wage and Tax Statements' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => true,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Annual
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 1, //Jan
																	 'primary_day_of_month' => 31,
																	 'reminder_days'        => 14,
															 ],
									],
							],
							'IT941'  => [
									'form_code'        => 'IT-941',
									'form_name'        => TTi18n::getText( 'IT-941 Filing' ),
									'form_description' => TTi18n::getText( 'Employer\'s Annual Reconciliation of Income Tax Withheld' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Annual
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 1, //Jan
																	 'primary_day_of_month' => 31,
																	 'reminder_days'        => 14,
															 ],
									],
							],
							'IT942'  => [
									'form_code'        => 'IT-942',
									'form_name'        => TTi18n::getText( 'IT-942 (EFT Payers) (Electronic Only)' ),
									'form_description' => TTi18n::getText( 'Employer\'s Quarterly Reconciliation of Income Tax Withheld' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [   //Quarterly
															  [
																	  'status_id'            => 20, //Disabled
																	  'frequency_id'         => 3000, //Quarterly
																	  'quarter_month'        => 1,
																	  'primary_day_of_month' => 31, //Last day
																	  'reminder_days'        => 7,
															  ],
									],
							],
							'IT501Q' => [
									'form_code'        => 'IT-501(Q)',
									'form_name'        => TTi18n::getText( 'IT-501(Q) Payment (Electronic Only)' ),
									'form_description' => TTi18n::getText( 'Employer\'s Payment of Tax Withheld - Quarterly Depositors' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Quarterly
										[
												'status_id'            => 10, //Enabled
												'frequency_id'         => 3000, //Quarterly
												'quarter_month'        => 1,
												'primary_day_of_month' => 31, //Last day
												'reminder_days'        => 7,
										],
									],
							],
							'IT501M' => [
									'form_code'        => 'IT-501(M)',
									'form_name'        => TTi18n::getText( 'IT-501(M) Payment (Electronic Only)' ),
									'form_description' => TTi18n::getText( 'Employer\'s Payment of Tax Withheld - Monthly Depositors' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Monthly
										[
												'status_id'            => 20, //Disabled
												'frequency_id'         => 4100, //Monthly
												'primary_day_of_month' => 15,
												'reminder_days'        => 7,
										],
									],
							],

							'SD141' => [
									'form_code'        => 'SD-141',
									'form_name'        => TTi18n::getText( 'SD-141 (Electronic only)' ),
									'form_description' => TTi18n::getText( 'School District Employer\'s Annual Reconciliation' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME' ],
									'filing_methods'   => [],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Annual
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 1, //Jan
																	 'primary_day_of_month' => 31,
																	 'reminder_days'        => 14,
															 ],
									],
							],
							'SD101' => [
									'form_code'        => 'SD-101',
									'form_name'        => TTi18n::getText( 'SD-101 (Electronic only)' ),
									'form_description' => TTi18n::getText( 'Employers Payment of School District Income Tax Withheld' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME' ],
									'filing_methods'   => [],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [

										//Monthly
										[
												'status_id'            => 20, //Enabled
												'frequency_id'         => 4100, //Monthly
												'primary_day_of_month' => 15,
												'reminder_days'        => 7,
										],

										//Quarterly
										[
												'status_id'            => 10, //Enabled
												'frequency_id'         => 3000, //Quarterly
												'quarter_month'        => 1,
												'primary_day_of_month' => 31, //Last day
												'reminder_days'        => 7,
										],
									],
							],
	],
	'20:US:OH:00:0020' => [ //State Government [Unemployment Insurance]
							'JFS20127' => [
									'form_code'        => 'JFS 20127',
									'form_name'        => TTi18n::getText( 'JFS 20127/22128 Filing' ),
									'form_description' => TTi18n::getText( 'Employer\'s Contribution and Quarter Summary/Reimbursing Wage Detail' ),
									'note'             => '',
									'tax_codes'        => [ 'UI' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Quarterly - Due the last day of the month following the end of the quarter. (April 30, July 31, October 31, and January 31)
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 3000, //Quarterly
																	 'quarter_month'        => 1,
																	 'primary_day_of_month' => 31, //Last day
																	 'reminder_days'        => 14,
															 ],
									],
							],
	],
	'20:US:OH:00:0030' => [ //State Government [New Hires]
							'NEWHIRE' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'New Hire Report Filing' ),
									'form_description' => TTi18n::getText( 'Report of New Hires' ),
									'note'             => '',
									'tax_codes'        => [],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //As Required, within 20 days of hire date.
															 //On Hire
															 [
																	 'status_id'           => 10, //Enabled
																	 'frequency_id'        => 90100, //On Hire
																	 'due_date_delay_days' => 20,
																	 'reminder_days'       => 0,
															 ],
									],
							],

	],
	'20:US:OH:00:0040' => [ //Child Support
							'CHILDSUPPORT' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'Child Support Withholding Payment' ),
									'form_description' => TTi18n::getText( 'Child Support Withholding Payment' ),
									'note'             => '',
									'tax_codes'        => [ 'CHILDSUPPORT' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Per Pay Period
										[
												'status_id'           => 10, //Enabled
												'frequency_id'        => 1000, //Per Pay Period
												'due_date_delay_days' => 14,
												'reminder_days'       => 5,
										],
									],
							],
	],
	'30:US:OH:00:0010' => [ //Local Government [Local Income Tax] - RITA.
							'FW2' => [
									'form_code'        => 'W2', //Includes W3
									'form_name'        => TTi18n::getText( 'W2 Local Filing' ),
									'form_description' => TTi18n::getText( 'Wage and Tax Statement' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'EFILE' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Annual
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 2, //Feb
																	 'primary_day_of_month' => 29,
																	 'reminder_days'        => 14,
															 ],
									],
							],
	],

	//OK - Oklahoma
	'20:US:OK:00:0010' => [ //State Government [State Income Tax]
							'FW2'      => [
									'form_code'        => 'W2', //Includes W3
									'form_name'        => TTi18n::getText( 'W2 State Filing' ),
									'form_description' => TTi18n::getText( 'Wage and Tax Statement' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'EFILE' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Annual
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 1, //Jan
																	 'primary_day_of_month' => 31,
																	 'reminder_days'        => 14,
															 ],
									],
							],
							'WTH10001' => [
									'form_code'        => 'WTH 10001',
									'form_name'        => TTi18n::getText( 'WTH 10001 Filing' ),
									'form_description' => TTi18n::getText( 'Oklahoma Wage Withholding Tax Return' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Quarterly - Due the last day of the month following the end of the quarter. (April 30, July 31, October 31, and January 31)
										[
												'status_id'            => 10, //Enabled
												'frequency_id'         => 3000, //Quarterly
												'quarter_month'        => 1,
												'primary_day_of_month' => 20,
												'reminder_days'        => 14,
										],
									],
							],
							'WTH10004' => [
									'form_code'        => 'WTH 10004',
									'form_name'        => TTi18n::getText( 'WTH 10004 Payment' ),
									'form_description' => TTi18n::getText( 'Oklahoma Withholding Payment Coupon' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Semi-Weekly
										[
											//Semi-Weekly
											'status_id'     => 20, //Disabled
											'frequency_id'  => 64000, //Semi-Weekly
											'reminder_days' => 3,
										],

										//Monthly
										[
												'status_id'            => 10, //Enabled
												'frequency_id'         => 4100, //Monthly
												'primary_day_of_month' => 20,
												'reminder_days'        => 7,
										],

										//Quarterly - Due the last day of the month following the end of the quarter. (April 30, July 31, October 31, and January 31)
										[
												'status_id'            => 20, //Disabled
												'frequency_id'         => 3000, //Quarterly
												'quarter_month'        => 1,
												'primary_day_of_month' => 20,
												'reminder_days'        => 14,
										],

									],
							],
	],
	'20:US:OK:00:0020' => [ //State Government [Unemployment Insurance]
							'OES3' => [
									'form_code'        => 'OES-3',
									'form_name'        => TTi18n::getText( 'OES-3 Filing' ),
									'form_description' => TTi18n::getText( 'Employer\'s Quarterly Contribution Report' ),
									'note'             => '',
									'tax_codes'        => [ 'UI' ],
									'filing_methods'   => [ 'EFILE' ],
									'payment_methods'  => [ 'CHECK' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Quarterly - Due the last day of the month following the end of the quarter. (April 30, July 31, October 31, and January 31)
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 3000, //Quarterly
																	 'quarter_month'        => 1,
																	 'primary_day_of_month' => 31, //Last day
																	 'reminder_days'        => 14,
															 ],
									],
							],
	],
	'20:US:OK:00:0030' => [ //State Government [New Hires]
							'NEWHIRE' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'New Hire Report Filing' ),
									'form_description' => TTi18n::getText( 'Report of New Hires' ),
									'note'             => '',
									'tax_codes'        => [],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //As Required, within 20 days of hire date.
															 //On Hire
															 [
																	 'status_id'           => 10, //Enabled
																	 'frequency_id'        => 90100, //On Hire
																	 'due_date_delay_days' => 20,
																	 'reminder_days'       => 0,
															 ],
									],
							],

	],
	'20:US:OK:00:0040' => [ //Child Support
							'CHILDSUPPORT' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'Child Support Withholding Payment' ),
									'form_description' => TTi18n::getText( 'Child Support Withholding Payment' ),
									'note'             => '',
									'tax_codes'        => [ 'CHILDSUPPORT' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Per Pay Period
										[
												'status_id'           => 10, //Enabled
												'frequency_id'        => 1000, //Per Pay Period
												'due_date_delay_days' => 14,
												'reminder_days'       => 5,
										],
									],
							],
	],

	//OR - Oregon
	'20:US:OR:00:0010' => [ //State Government [State Income Tax]
							'FW2' => [
									'form_code'        => 'W2', //Includes W3
									'form_name'        => TTi18n::getText( 'W2 State Filing' ),
									'form_description' => TTi18n::getText( 'Wage and Tax Statement' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'EFILE' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Annual
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 1, //Jan
																	 'primary_day_of_month' => 31,
																	 'reminder_days'        => 14,
															 ],
									],
							],
							'WR'  => [
									'form_code'        => 'WR',
									'form_name'        => TTi18n::getText( 'WR Filing' ),
									'form_description' => TTi18n::getText( 'Annual Withholding Tax Reconciliation Report' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => true,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Annual
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 1, //Jan
																	 'primary_day_of_month' => 31,
																	 'reminder_days'        => 14,
															 ],
									],
							],
							'OQ'  => [
									'form_code'        => 'OQ',
									'form_name'        => TTi18n::getText( 'OQ Filing' ),
									'form_description' => TTi18n::getText( 'Quarterly Combined Tax Report' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE', 'UI' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [    //Quarterly
															   [
																	   'status_id'            => 10, //Enabled
																	   'frequency_id'         => 3000, //Quarterly
																	   'quarter_month'        => 1,
																	   'primary_day_of_month' => 31, //Last day
																	   'reminder_days'        => 7,
															   ],
									],
							],
							'OTC' => [
									'form_code'        => 'OTC',
									'form_name'        => TTi18n::getText( 'OTC Payment' ),
									'form_description' => TTi18n::getText( 'Combined Payroll Tax Payment Coupon-Must be sent with every form if a payment is made.' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE', 'UI' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [    //Next Day: Due on the next banking day after the 100K Federal tax liability is met.
															   //Semiweekly: Due the following Friday (for pay dates that fall on Saturday through Tuesday). Due the following Wednesday (for pay dates that fall on Wednesday through Friday). Exception: If you have 2 paydays within one deposit period but they are separate quarters you must make 2 separate payments.
															   //Monthly: Due the 15th of the following month.
															   //Quarterly: Due the last day of the month following the end of the quarter. (April 30, July 31, October 31, and January 31).

															   //US - Semi-Weekly
															   [
																	   'status_id'           => 20, //Disabled
																	   'frequency_id'        => 64000, //US - Semi-Weekly
																	   'due_date_delay_days' => 0,
																	   'reminder_days'       => 1,
															   ],

															   //Monthly
															   [
																	   'status_id'            => 10, //Enabled
																	   'frequency_id'         => 4100, //Monthly
																	   'primary_day_of_month' => 15,
																	   'reminder_days'        => 7,
															   ],

															   //Quarterly
															   [
																	   'status_id'            => 20, //Disabled
																	   'frequency_id'         => 3000, //Quarterly
																	   'quarter_month'        => 1,
																	   'primary_day_of_month' => 31, //Last day
																	   'reminder_days'        => 7,
															   ],
									],
							],
	],
	'20:US:OR:00:0020' => [ //State Government [Unemployment Insurance]
	],
	'20:US:OR:00:0030' => [ //State Government [New Hires]
							'NEWHIRE' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'New Hire Report Filing' ),
									'form_description' => TTi18n::getText( 'Report of New Hires' ),
									'note'             => '',
									'tax_codes'        => [],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //As Required, within 20 days of hire date.
															 //On Hire
															 [
																	 'status_id'           => 10, //Enabled
																	 'frequency_id'        => 90100, //On Hire
																	 'due_date_delay_days' => 20,
																	 'reminder_days'       => 0,
															 ],
									],
							],

	],
	'20:US:OR:00:0040' => [ //Child Support
							'CHILDSUPPORT' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'Child Support Withholding Payment' ),
									'form_description' => TTi18n::getText( 'Child Support Withholding Payment' ),
									'note'             => '',
									'tax_codes'        => [ 'CHILDSUPPORT' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Per Pay Period
										[
												'status_id'           => 10, //Enabled
												'frequency_id'        => 1000, //Per Pay Period
												'due_date_delay_days' => 14,
												'reminder_days'       => 5,
										],
									],
							],
	],

	//PA - Pennsylvania
	'20:US:PA:00:0010' => [ //State Government [State Income Tax]
							'FW2'   => [
									'form_code'        => 'W2', //Includes W3
									'form_name'        => TTi18n::getText( 'W2 State Filing' ),
									'form_description' => TTi18n::getText( 'Wage and Tax Statement' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'EFILE' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Annual
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 1, //Jan
																	 'primary_day_of_month' => 31,
																	 'reminder_days'        => 14,
															 ],
									],
							],
							'PA501' => [
									'form_code'        => 'PA-501',
									'form_name'        => TTi18n::getText( 'PA-501 Payment (Electronic Only)' ),
									'form_description' => TTi18n::getText( 'Employer Deposit Statement of Withholding Tax' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'EFILE' ],
									'payment_methods'  => [ 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [    //Semiweekly: Due the following Friday (for pay dates that fall on Saturday through Tuesday). Due the following Wednesday (for pay dates that fall on Wednesday through Friday).
															   //Semimonthly: Due 3 banking days after close of the Semimonthly period.
															   //Monthly: Due 15th day of following month for January through November. Due January 31 for December.
															   //Quarterly: Due the last day of the month following the end of the quarter (April 30, July 31, October 31, and January 31).

															   //US - Semi-Weekly
															   [
																	   'status_id'           => 20, //Disabled
																	   'frequency_id'        => 64000, //US - Semi-Weekly
																	   'due_date_delay_days' => 0,
																	   'reminder_days'       => 1,
															   ],

															   //As Required, within 20 days of hire date.
															   [
																	   'status_id'              => 20, //Disabled
																	   'frequency_id'           => 4200, //Semi-Monthly
																	   'primary_day_of_month'   => 15,
																	   'secondary_day_of_month' => 31,
																	   'due_date_delay_days'    => 3,
																	   'reminder_days'          => 0,
															   ],

															   //Monthly
															   [
																	   'status_id'     => 10, //Enabled
																	   'frequency_id'  => 60000, //Monthly: Due 15th day of following month for January through November. Due January 31 for December.
																	   //'primary_day_of_month' => 15,
																	   'reminder_days' => 7,
															   ],

															   //Quarterly
															   [
																	   'status_id'            => 20, //Disabled
																	   'frequency_id'         => 3000, //Quarterly
																	   'quarter_month'        => 1,
																	   'primary_day_of_month' => 31, //Last day
																	   'reminder_days'        => 7,
															   ],
									],
							],
							'PAW3'  => [
									'form_code'        => 'PA-W3',
									'form_name'        => TTi18n::getText( 'PA-W3 Filing' ),
									'form_description' => TTi18n::getText( 'Employer Quarterly Return of Withholding Tax' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Quarterly - Due the last day of the month following the end of the quarter. (April 30, July 31, October 31, and January 31)
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 3000, //Quarterly
																	 'quarter_month'        => 1,
																	 'primary_day_of_month' => 31, //Last day
																	 'reminder_days'        => 14,
															 ],
									],
							],
	],
	'20:US:PA:00:0020' => [ //State Government [Unemployment Insurance]
							'UC2' => [
									'form_code'        => 'UC-2',
									'form_name'        => TTi18n::getText( 'UC-2 Filing' ),
									'form_description' => TTi18n::getText( 'Employer\'s Report for Unemployment Compensation' ),
									'note'             => '',
									'tax_codes'        => [ 'UI' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Quarterly - Due the last day of the month following the end of the quarter. (April 30, July 31, October 31, and January 31)
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 3000, //Quarterly
																	 'quarter_month'        => 1,
																	 'primary_day_of_month' => 31, //Last day
																	 'reminder_days'        => 14,
															 ],
									],
							],
	],
	'20:US:PA:00:0030' => [ //State Government [New Hires]
							'NEWHIRE' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'New Hire Report Filing' ),
									'form_description' => TTi18n::getText( 'Report of New Hires' ),
									'note'             => '',
									'tax_codes'        => [],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //As Required, within 20 days of hire date.
															 //On Hire
															 [
																	 'status_id'           => 10, //Enabled
																	 'frequency_id'        => 90100, //On Hire
																	 'due_date_delay_days' => 20,
																	 'reminder_days'       => 0,
															 ],
									],
							],

	],
	'20:US:PA:00:0040' => [ //Child Support
							'CHILDSUPPORT' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'Child Support Withholding Payment' ),
									'form_description' => TTi18n::getText( 'Child Support Withholding Payment' ),
									'note'             => '',
									'tax_codes'        => [ 'CHILDSUPPORT' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Per Pay Period
										[
												'status_id'           => 10, //Enabled
												'frequency_id'        => 1000, //Per Pay Period
												'due_date_delay_days' => 14,
												'reminder_days'       => 5,
										],
									],
							],
	],

	//RI - Rhode Island
	'20:US:RI:00:0010' => [ //State Government [State Income Tax]
							'FW2' => [
									'form_code'        => 'W2', //Includes W3
									'form_name'        => TTi18n::getText( 'W2 State Filing' ),
									'form_description' => TTi18n::getText( 'Wage and Tax Statement' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'EFILE' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Annual
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 1, //Jan
																	 'primary_day_of_month' => 31,
																	 'reminder_days'        => 14,
															 ],
									],
							],

							'RI941Q'  => [
									'form_code'        => 'RI-941Q',
									'form_name'        => TTi18n::getText( 'RI-941Q Payment' ),
									'form_description' => TTi18n::getText( 'Rhode Island Withholding Tax Return (Quarterly)' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [ 'CHECK' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [ //Quarterly - Due the last day of the month following the end of the quarter. (April 30, July 31, October 31, and January 31)
															[
																	'status_id'            => 10, //Enabled
																	'frequency_id'         => 3000, //Quarterly
																	'quarter_month'        => 1,
																	'primary_day_of_month' => 31, //Last day
																	'reminder_days'        => 14,
															],
									],
							],
							'RI941M'  => [
									'form_code'        => 'RI-941M',
									'form_name'        => TTi18n::getText( 'RI-941M Payment' ),
									'form_description' => TTi18n::getText( 'Rhode Island Withholding Tax Return (Monthly)' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [ 'CHECK' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [ //FIXME: Due on or before the 20th of the following month. Except for March, June, September and December which may be filed the last day of the following month.
															//Monthly
															[
																	'status_id'            => 20, //Disabled
																	'frequency_id'         => 60000, //US - Monthly (15th, 30th on Last MoQ)
																	'primary_day_of_month' => 20,
																	'reminder_days'        => 7,
															],
									],
							],
							'RI941W'  => [
									'form_code'        => 'RI-941W',
									'form_name'        => TTi18n::getText( 'RI-941W Payment' ),
									'form_description' => TTi18n::getText( 'Rhode Island Withholding Tax Return (Weekly)' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [ 'CHECK' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [    //Weekly
															   [
																	   'status_id'           => 20, //Disabled
																	   'frequency_id'        => 5100, //Weekly
																	   'day_of_week'         => 1, //Mon
																	   'due_date_delay_days' => 0,
																	   'reminder_days'       => 2,
															   ],
									],
							],
							'RI941QM' => [ //DISCONTINUED 01-Jan-2020, replaced by Weekly.
										   'form_code'        => 'RI-941QM',
										   'form_name'        => TTi18n::getText( 'RI-941QM Payment' ),
										   'form_description' => TTi18n::getText( 'Rhode Island Withholding Tax Return (Quartermonthly)' ),
										   'note'             => '',
										   'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
										   'filing_methods'   => [ 'PRINT' ],
										   'payment_methods'  => [ 'CHECK' ],
										   'flags'            => [
												   'include_w2'          => false,
												   'file_zero_wage'      => true,
												   'file_zero_liability' => true,
												   'auto_file'           => false,
												   'auto_pay'            => false,
										   ],
										   'frequency'        => [ //Due within 3 banking days after the last day of the quartermonthly period. Quarter monthly periods are monthly dates running from the 1st through 7th, 8th through 15th, 16th through 22nd and the 23rd through the last day of the month.

																   //Quarter-Monthly
																   [
																		   'status_id'           => 20, //Disabled
																		   'frequency_id'        => 62000, //Quarter-Monthly
																		   'due_date_delay_days' => 3,
																		   'reminder_days'       => 14,
																   ],
										   ],
							],
							'RI941D'  => [ //DISCONTINUED 01-Jan-2020, replaced by Weekly.
										   'form_code'        => 'RI-941D',
										   'form_name'        => TTi18n::getText( 'RI-941D Payment' ),
										   'form_description' => TTi18n::getText( 'Rhode Island Withholding Tax Return (Daily)' ),
										   'note'             => '',
										   'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
										   'filing_methods'   => [ 'PRINT' ],
										   'payment_methods'  => [ 'CHECK' ],
										   'flags'            => [
												   'include_w2'          => false,
												   'file_zero_wage'      => true,
												   'file_zero_liability' => true,
												   'auto_file'           => false,
												   'auto_pay'            => false,
										   ],
										   'frequency'        => [    //Per Pay Period
																	  [
																			  'status_id'           => 20, //Disabled
																			  'frequency_id'        => 1000, //Per Pay Period
																			  'due_date_delay_days' => 1,
																			  'reminder_days'       => 2,
																	  ],
										   ],
							],
	],
	'20:US:RI:00:0020' => [ //State Government [Unemployment Insurance]
							'TX17' => [
									'form_code'        => 'TX-17',
									'form_name'        => TTi18n::getText( 'TX-17 Filing' ),
									'form_description' => TTi18n::getText( 'Quarterly Tax and Wage Report' ),
									'note'             => '',
									'tax_codes'        => [ 'UI' ],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [ 'CHECK' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Quarterly - Due the last day of the month following the end of the quarter. (April 30, July 31, October 31, and January 31)
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 3000, //Quarterly
																	 'quarter_month'        => 1,
																	 'primary_day_of_month' => 31, //Last day
																	 'reminder_days'        => 14,
															 ],
									],
							],
	],
	'20:US:RI:00:0030' => [ //State Government [New Hires]
							'NEWHIRE' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'New Hire Report Filing' ),
									'form_description' => TTi18n::getText( 'Report of New Hires' ),
									'note'             => '',
									'tax_codes'        => [],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //As Required, within 14 days of hire date.
															 //On Hire
															 [
																	 'status_id'           => 10, //Enabled
																	 'frequency_id'        => 90100, //On Hire
																	 'due_date_delay_days' => 14,
																	 'reminder_days'       => 0,
															 ],
									],
							],

	],
	'20:US:RI:00:0040' => [ //Child Support
							'CHILDSUPPORT' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'Child Support Withholding Payment' ),
									'form_description' => TTi18n::getText( 'Child Support Withholding Payment' ),
									'note'             => '',
									'tax_codes'        => [ 'CHILDSUPPORT' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Per Pay Period
										[
												'status_id'           => 10, //Enabled
												'frequency_id'        => 1000, //Per Pay Period
												'due_date_delay_days' => 14,
												'reminder_days'       => 5,
										],
									],
							],
	],

	//SC - South Carolina
	'20:US:SC:00:0010' => [ //State Government [State Income Tax]
							'FW2'    => [
									'form_code'        => 'W2', //Includes W3
									'form_name'        => TTi18n::getText( 'W2 State Filing' ),
									'form_description' => TTi18n::getText( 'Wage and Tax Statement' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'EFILE' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Annual
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 1, //Jan
																	 'primary_day_of_month' => 31,
																	 'reminder_days'        => 14,
															 ],
									],
							],
							'WH1601' => [
									'form_code'        => 'WH-1601',
									'form_name'        => TTi18n::getText( 'WH-1601 Payment' ),
									'form_description' => TTi18n::getText( 'Withholding Tax Payment' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //(Semi-weekly and 100K - Next Day [Due the next banking day when Federal liabilities are 100K or more] must be remitted by EFT without payment coupon.)
															 //Monthly: Due the 15th of the following month.
															 //Quarterly: Due the last day of the month following the end of the quarter

															 //Monthly
															 [
																	 'status_id'            => 20, //Disabled
																	 'frequency_id'         => 4100, //Monthly
																	 'primary_day_of_month' => 15,
																	 'reminder_days'        => 7,
															 ],

															 //Quarterly
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 3000, //Quarterly
																	 'quarter_month'        => 1,
																	 'primary_day_of_month' => 31, //Last day
																	 'reminder_days'        => 7,
															 ],
									],
							],
							'WH1605' => [
									'form_code'        => 'WH-1605',
									'form_name'        => TTi18n::getText( 'WH-1605 Filing' ),
									'form_description' => TTi18n::getText( 'Withholding Quarterly Tax Return' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Due the last day of the month following the end of the quarter. (April 30, July 31, and October 31).

										//Quarterly
										[
												'status_id'            => 10, //Enabled
												'frequency_id'         => 59000, //Quarterly, 1-3 only.
												'quarter_month'        => 1,
												'primary_day_of_month' => 31, //Last day
												'reminder_days'        => 7,
										],
									],
							],
							'WH1606' => [
									'form_code'        => 'WH-1606',
									'form_name'        => TTi18n::getText( 'WH-1606 Filing' ),
									'form_description' => TTi18n::getText( 'Withholding Quarterly Tax Return' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//4th Quarter/Annual Reconciliation, Due January 31 following the year to be filed.

										//Annual
										[
												'status_id'            => 10, //Enabled
												'frequency_id'         => 2000, //Annual
												'primary_month'        => 1, //Jan
												'primary_day_of_month' => 31,
												'reminder_days'        => 14,
										],

									],
							],
	],
	'20:US:SC:00:0020' => [ //State Government [Unemployment Insurance]
							'UCE101' => [
									'form_code'        => 'UCE-101',
									'form_name'        => TTi18n::getText( 'UCE-101/UCE-120 Filing' ),
									'form_description' => TTi18n::getText( 'Employer Quarterly Contribution and Wage Reports' ),
									'note'             => '',
									'tax_codes'        => [ 'UI' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Quarterly - Due the last day of the month following the end of the quarter. (April 30, July 31, October 31, and January 31)
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 3000, //Quarterly
																	 'quarter_month'        => 1,
																	 'primary_day_of_month' => 31, //Last day
																	 'reminder_days'        => 14,
															 ],
									],
							],
	],
	'20:US:SC:00:0030' => [ //State Government [New Hires]
							'NEWHIRE' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'New Hire Report Filing' ),
									'form_description' => TTi18n::getText( 'Report of New Hires' ),
									'note'             => '',
									'tax_codes'        => [],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //As Required, within 20 days of hire date.
															 //On Hire
															 [
																	 'status_id'           => 10, //Enabled
																	 'frequency_id'        => 90100, //On Hire
																	 'due_date_delay_days' => 20,
																	 'reminder_days'       => 0,
															 ],
									],
							],

	],
	'20:US:SC:00:0040' => [ //Child Support
							'CHILDSUPPORT' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'Child Support Withholding Payment' ),
									'form_description' => TTi18n::getText( 'Child Support Withholding Payment' ),
									'note'             => '',
									'tax_codes'        => [ 'CHILDSUPPORT' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Per Pay Period
										[
												'status_id'           => 10, //Enabled
												'frequency_id'        => 1000, //Per Pay Period
												'due_date_delay_days' => 14,
												'reminder_days'       => 5,
										],
									],
							],
	],

	//SD - South Dakota
	'20:US:SD:00:0010' => [ //State Government [State Income Tax]
	],
	'20:US:SD:00:0020' => [ //State Government [Unemployment Insurance]
							'DOLUID21' => [
									'form_code'        => 'DOL-UID-21',
									'form_name'        => TTi18n::getText( 'DOL-UID-21 Filing' ),
									'form_description' => TTi18n::getText( 'Employer\'s Quarterly Contribution, Investment Fee, and Wage Report' ),
									'note'             => '',
									'tax_codes'        => [ 'UI' ],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [ 'CHECK' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Quarterly - Due the last day of the month following the end of the quarter. (April 30, July 31, October 31, and January 31)
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 3000, //Quarterly
																	 'quarter_month'        => 1,
																	 'primary_day_of_month' => 31, //Last day
																	 'reminder_days'        => 14,
															 ],
									],
							],
	],
	'20:US:SD:00:0030' => [ //State Government [New Hires]
							'NEWHIRE' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'New Hire Report Filing' ),
									'form_description' => TTi18n::getText( 'Report of New Hires' ),
									'note'             => '',
									'tax_codes'        => [],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //As Required, within 20 days of hire date.
															 //On Hire
															 [
																	 'status_id'           => 10, //Enabled
																	 'frequency_id'        => 90100, //On Hire
																	 'due_date_delay_days' => 20,
																	 'reminder_days'       => 0,
															 ],
									],
							],
	],
	'20:US:SD:00:0040' => [ //Child Support
							'CHILDSUPPORT' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'Child Support Withholding Payment' ),
									'form_description' => TTi18n::getText( 'Child Support Withholding Payment' ),
									'note'             => '',
									'tax_codes'        => [ 'CHILDSUPPORT' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Per Pay Period
										[
												'status_id'           => 10, //Enabled
												'frequency_id'        => 1000, //Per Pay Period
												'due_date_delay_days' => 14,
												'reminder_days'       => 5,
										],
									],
							],
	],

	//TN - Tennessee
	'20:US:TN:00:0010' => [ //State Government [State Income Tax]
	],
	'20:US:TN:00:0020' => [ //State Government [Unemployment Insurance]
							'LB0456' => [
									'form_code'        => 'LB-0456',
									'form_name'        => TTi18n::getText( 'LB-0456 Filing' ),
									'form_description' => TTi18n::getText( 'Premium and Wage Report' ),
									'note'             => '',
									'tax_codes'        => [ 'UI' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Quarterly - Due the last day of the month following the end of the quarter. (April 30, July 31, October 31, and January 31)
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 3000, //Quarterly
																	 'quarter_month'        => 1,
																	 'primary_day_of_month' => 31, //Last day
																	 'reminder_days'        => 14,
															 ],
									],
							],
							//'LB0851' => [ //***This is the same report as the above LB0456 above. They seem to have been combined.
							//		'form_code'        => 'LB-0851',
							//		'form_name'        => TTi18n::getText( 'LB-0851 Filing' ),
							//		'form_description' => TTi18n::getText( 'Wage Report' ),
							//		'note'             => '',
							//		'tax_codes'        => [ 'UI' ],
							//		'filing_methods'   => [ 'PRINT', 'EFILE' ],
							//		'payment_methods'  => [ 'CHECK' ],
							//		'flags'            => [
							//				'include_w2'          => false,
							//				'file_zero_wage'      => true,
							//				'file_zero_liability' => true,
							//				'auto_file'           => false,
							//				'auto_pay'            => false,
							//		],
							//		'frequency'        => [  //Quarterly - Due the last day of the month following the end of the quarter. (April 30, July 31, October 31, and January 31)
							//								 [
							//										 'status_id'            => 10, //Enabled
							//										 'frequency_id'         => 3000, //Quarterly
							//										 'quarter_month'        => 1,
							//										 'primary_day_of_month' => 31, //Last day
							//										 'reminder_days'        => 14,
							//								 ],
							//		],
							//],
	],
	'20:US:TN:00:0030' => [ //State Government [New Hires]
							'NEWHIRE' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'New Hire Report Filing' ),
									'form_description' => TTi18n::getText( 'Report of New Hires' ),
									'note'             => '',
									'tax_codes'        => [],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //As Required, within 20 days of hire date.
															 //On Hire
															 [
																	 'status_id'           => 10, //Enabled
																	 'frequency_id'        => 90100, //On Hire
																	 'due_date_delay_days' => 20,
																	 'reminder_days'       => 0,
															 ],
									],
							],

	],
	'20:US:TN:00:0040' => [ //Child Support
							'CHILDSUPPORT' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'Child Support Withholding Payment' ),
									'form_description' => TTi18n::getText( 'Child Support Withholding Payment' ),
									'note'             => '',
									'tax_codes'        => [ 'CHILDSUPPORT' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Per Pay Period
										[
												'status_id'           => 10, //Enabled
												'frequency_id'        => 1000, //Per Pay Period
												'due_date_delay_days' => 14,
												'reminder_days'       => 5,
										],
									],
							],
	],

	//TX - Texas
	'20:US:TX:00:0010' => [ //State Government [State Income Tax]
	],
	'20:US:TX:00:0020' => [ //State Government [Unemployment Insurance]
							'C3' => [
									'form_code'        => 'C-3',
									'form_name'        => TTi18n::getText( 'C-3 Filing' ),
									'form_description' => TTi18n::getText( 'Employer\'s Quarterly Report' ),
									'note'             => '',
									'tax_codes'        => [ 'UI' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Quarterly - Due the last day of the month following the end of the quarter. (April 30, July 31, October 31, and January 31)
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 3000, //Quarterly
																	 'quarter_month'        => 1,
																	 'primary_day_of_month' => 31, //Last day
																	 'reminder_days'        => 14,
															 ],
									],
							],
	],
	'20:US:TX:00:0030' => [ //State Government [New Hires]
							'NEWHIRE' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'New Hire Report Filing' ),
									'form_description' => TTi18n::getText( 'Report of New Hires' ),
									'note'             => '',
									'tax_codes'        => [],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //As Required, within 20 days of hire date.
															 //On Hire
															 [
																	 'status_id'           => 10, //Enabled
																	 'frequency_id'        => 90100, //On Hire
																	 'due_date_delay_days' => 20,
																	 'reminder_days'       => 0,
															 ],
									],
							],

	],
	'20:US:TX:00:0040' => [ //Child Support
							'CHILDSUPPORT' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'Child Support Withholding Payment' ),
									'form_description' => TTi18n::getText( 'Child Support Withholding Payment' ),
									'note'             => '',
									'tax_codes'        => [ 'CHILDSUPPORT' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Per Pay Period
										[
												'status_id'           => 10, //Enabled
												'frequency_id'        => 1000, //Per Pay Period
												'due_date_delay_days' => 14,
												'reminder_days'       => 5,
										],
									],
							],
	],


	//UT - Utah
	'20:US:UT:00:0010' => [ //State Government [State Income Tax]
							'FW2'     => [
									'form_code'        => 'W2', //Includes W3
									'form_name'        => TTi18n::getText( 'W2 State Filing' ),
									'form_description' => TTi18n::getText( 'Wage and Tax Statement' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'EFILE' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Annual
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 1, //Jan
																	 'primary_day_of_month' => 31,
																	 'reminder_days'        => 14,
															 ],
									],
							],
							'TC941R'  => [
									'form_code'        => 'TC-941R',
									'form_name'        => TTi18n::getText( 'TC-941R Filing' ),
									'form_description' => TTi18n::getText( 'Annual Withholding Reconciliation' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'EFILE' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => true,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Annual
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 1, //Jan
																	 'primary_day_of_month' => 31,
																	 'reminder_days'        => 14,
															 ],
									],
							],
							'TC941'   => [
									'form_code'        => 'TC-941',
									'form_name'        => TTi18n::getText( 'TC-941 Filing' ),
									'form_description' => TTi18n::getText( 'Utah Withholding Return' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'EFILE' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => true,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Annual
															 [
																	 'status_id'            => 20, //Disabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 1, //Jan
																	 'primary_day_of_month' => 31,
																	 'reminder_days'        => 14,
															 ],
															 //Quarterly
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 3000, //Quarterly
																	 'quarter_month'        => 1,
																	 'primary_day_of_month' => 31, //Last day
																	 'reminder_days'        => 7,
															 ],
									],
							],
							'TC941PC' => [
									'form_code'        => 'TC-941PC',
									'form_name'        => TTi18n::getText( 'TC-941PC Payment' ),
									'form_description' => TTi18n::getText( 'Utah Withholding Payment' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [],
									'payment_methods'  => [ 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Annual
															 [
																	 'status_id'            => 20, //Disabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 1, //Jan
																	 'primary_day_of_month' => 31,
																	 'reminder_days'        => 14,
															 ],
															 //Quarterly
															 [
																	 'status_id'            => 20, //Disabled
																	 'frequency_id'         => 3000, //Quarterly
																	 'quarter_month'        => 1,
																	 'primary_day_of_month' => 31, //Last day
																	 'reminder_days'        => 7,
															 ],
															 //Monthly
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 4100, //Monthly
																	 'primary_day_of_month' => 31, //Last Day
																	 'reminder_days'        => 7,
															 ],

									],
							],
	],
	'20:US:UT:00:0020' => [ //State Government [Unemployment Insurance]
							'33H' => [
									'form_code'        => '33H',
									'form_name'        => TTi18n::getText( 'Form 33H Filing' ),
									'form_description' => TTi18n::getText( 'Employer\'s Contribution Report' ),
									'note'             => '',
									'tax_codes'        => [ 'UI' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Quarterly - Due the last day of the month following the end of the quarter. (April 30, July 31, October 31, and January 31)
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 3000, //Quarterly
																	 'quarter_month'        => 1,
																	 'primary_day_of_month' => 31, //Last day
																	 'reminder_days'        => 14,
															 ],
									],
							],
	],
	'20:US:UT:00:0030' => [ //State Government [New Hires]
							'NEWHIRE' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'New Hire Report Filing' ),
									'form_description' => TTi18n::getText( 'Report of New Hires' ),
									'note'             => '',
									'tax_codes'        => [],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //As Required, within 20 days of hire date.
															 //On Hire
															 [
																	 'status_id'           => 10, //Enabled
																	 'frequency_id'        => 90100, //On Hire
																	 'due_date_delay_days' => 20,
																	 'reminder_days'       => 0,
															 ],
									],
							],

	],
	'20:US:UT:00:0040' => [ //Child Support
							'CHILDSUPPORT' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'Child Support Withholding Payment' ),
									'form_description' => TTi18n::getText( 'Child Support Withholding Payment' ),
									'note'             => '',
									'tax_codes'        => [ 'CHILDSUPPORT' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Per Pay Period
										[
												'status_id'           => 10, //Enabled
												'frequency_id'        => 1000, //Per Pay Period
												'due_date_delay_days' => 14,
												'reminder_days'       => 5,
										],
									],
							],
	],

	//VA - Virginia
	'20:US:VA:00:0010' => [ //State Government [State Income Tax]
							'FW2'  => [
									'form_code'        => 'W2', //Includes W3
									'form_name'        => TTi18n::getText( 'W2 State Filing' ),
									'form_description' => TTi18n::getText( 'Wage and Tax Statement' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'EFILE' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Annual
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 1, //Jan
																	 'primary_day_of_month' => 31,
																	 'reminder_days'        => 14,
															 ],
									],
							],
							'VA6'  => [
									'form_code'        => 'VA-6',
									'form_name'        => TTi18n::getText( 'VA-6 Filing' ),
									'form_description' => TTi18n::getText( 'Employer\'s Annual or Final Summary of Income Tax Withheld' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'EFILE' ],
									'payment_methods'  => [ 'EPAY' ],
									'flags'            => [
											'include_w2'          => true,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Annual
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 1, //Jan
																	 'primary_day_of_month' => 31,
																	 'reminder_days'        => 14,
															 ],
									],
							],
							'VA16' => [
									'form_code'        => 'VA-16',
									'form_name'        => TTi18n::getText( 'VA-16 Filing' ),
									'form_description' => TTi18n::getText( 'Employer\'s Payments Quarterly Reconciliation and Return of Income Tax Withheld' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'EFILE' ],
									'payment_methods'  => [ 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [    //Quarterly
															   [
																	   'status_id'            => 10, //Enabled
																	   'frequency_id'         => 3000, //Quarterly
																	   'quarter_month'        => 1,
																	   'primary_day_of_month' => 31, //Last day
																	   'reminder_days'        => 7,
															   ],
									],
							],
							'VA5'  => [
									'form_code'        => 'VA-5',
									'form_name'        => TTi18n::getText( 'VA-5 Payment (Electronic Only)' ),
									'form_description' => TTi18n::getText( 'Employer\'s Return of Virginia Income Tax Withheld' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'EFILE' ],
									'payment_methods'  => [ 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Monthly: Due on the 25th of the following month.
										//Quarterly: Due on or before the last day of the month following the end of the Quarter. (April 30, July 31, October 31, and January 31)
										//Seasonal: Due on the 25th of the following month and only during months of business operation.

										//Quarterly
										[
												'status_id'            => 10, //Enabled
												'frequency_id'         => 3000, //Quarterly
												'quarter_month'        => 1,
												'primary_day_of_month' => 31, //Last day
												'reminder_days'        => 7,
										],

										//Monthly
										[
												'status_id'            => 20, //Disabled
												'frequency_id'         => 4100, //Monthly
												'primary_day_of_month' => 25,
												'reminder_days'        => 7,
										],
									],
							],
							'VA15' => [
									'form_code'        => 'VA-15',
									'form_name'        => TTi18n::getText( 'VA-15 Payment' ),
									'form_description' => TTi18n::getText( 'Employer\'s Voucher for Payment of Income Tax Withheld (Semiweekly)' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'EFILE' ],
									'payment_methods'  => [ 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Due by 3rd banking day after end of Semiweekly period. If the due date falls within three days of the due date for Form VA-16, the payment must be made on Form VA-16.

										//US - Semi-Weekly
										[
												'status_id'           => 20, //Disabled
												'frequency_id'        => 64000, //US - Semi-Weekly
												'due_date_delay_days' => 3,
												'reminder_days'       => 1,
										],
									],
							],
	],
	'20:US:VA:00:0020' => [ //State Government [Unemployment Insurance]
							'VECFC20' => [
									'form_code'        => 'VEC-FC-20',
									'form_name'        => TTi18n::getText( 'VEC-FC-20 Filing' ),
									'form_description' => TTi18n::getText( 'Employer\'s Quarterly Tax Report' ),
									'note'             => '',
									'tax_codes'        => [ 'UI' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Quarterly - Due the last day of the month following the end of the quarter. (April 30, July 31, October 31, and January 31)
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 3000, //Quarterly
																	 'quarter_month'        => 1,
																	 'primary_day_of_month' => 31, //Last day
																	 'reminder_days'        => 14,
															 ],
									],
							],
	],
	'20:US:VA:00:0030' => [ //State Government [New Hires]
							'NEWHIRE' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'New Hire Report Filing' ),
									'form_description' => TTi18n::getText( 'Report of New Hires' ),
									'note'             => '',
									'tax_codes'        => [],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //As Required, within 20 days of hire date.
															 //On Hire
															 [
																	 'status_id'           => 10, //Enabled
																	 'frequency_id'        => 90100, //On Hire
																	 'due_date_delay_days' => 20,
																	 'reminder_days'       => 0,
															 ],
									],
							],

	],
	'20:US:VA:00:0040' => [ //Child Support
							'CHILDSUPPORT' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'Child Support Withholding Payment' ),
									'form_description' => TTi18n::getText( 'Child Support Withholding Payment' ),
									'note'             => '',
									'tax_codes'        => [ 'CHILDSUPPORT' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Per Pay Period
										[
												'status_id'           => 10, //Enabled
												'frequency_id'        => 1000, //Per Pay Period
												'due_date_delay_days' => 14,
												'reminder_days'       => 5,
										],
									],
							],
	],

	//VT - Vermont
	'20:US:VT:00:0010' => [ //State Government [State Income Tax]
							'FW2'    => [
									'form_code'        => 'W2', //Includes W3
									'form_name'        => TTi18n::getText( 'W2 State Filing' ),
									'form_description' => TTi18n::getText( 'Wage and Tax Statement' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'EFILE' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Annual
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 1, //Jan
																	 'primary_day_of_month' => 31,
																	 'reminder_days'        => 14,
															 ],
									],
							],
							'WHT434' => [
									'form_code'        => 'WHT-434',
									'form_name'        => TTi18n::getText( 'WHT-434 Filing' ),
									'form_description' => TTi18n::getText( 'Annual Withholding Reconciliation' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => true,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Annual
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 1, //Jan
																	 'primary_day_of_month' => 31,
																	 'reminder_days'        => 14,
															 ],
									],
							],
							'WHT436' => [
									'form_code'        => 'WHT-436',
									'form_name'        => TTi18n::getText( 'WHT-436 Filing' ),
									'form_description' => TTi18n::getText( 'Quarterly Withholding Reconciliation' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [ 'CHECK' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Due by the 25th of the month following the end of the quarter
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 3000, //Quarterly
																	 'quarter_month'        => 1,
																	 'primary_day_of_month' => 25,
																	 'reminder_days'        => 14,
															 ],
									],
							],
							'WHT430' => [
									'form_code'        => 'WHT-430',
									'form_name'        => TTi18n::getText( 'WHT-430 Payment' ),
									'form_description' => TTi18n::getText( 'Withholding Tax Payment' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [ 'CHECK' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //FIXME: Monthly: Due the 25th of the following month, except for January which is due February 23rd.
															 //Quarterly: Due 25th of the month following the end of the quarter.

															 //Quarterly
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 3000, //Quarterly
																	 'quarter_month'        => 1,
																	 'primary_day_of_month' => 25,
																	 'reminder_days'        => 14,
															 ],

															 //Monthly
															 [
																	 'status_id'            => 20, //Disabled
																	 'frequency_id'         => 4100, //Monthly
																	 'primary_day_of_month' => 25,
																	 'reminder_days'        => 7,
															 ],
									],
							],
	],
	'20:US:VT:00:0020' => [ //State Government [Unemployment Insurance]
							'C101' => [
									'form_code'        => 'C-101',
									'form_name'        => TTi18n::getText( 'C-101 Filing' ),
									'form_description' => TTi18n::getText( 'Employer\'s Quarterly Wage and Contribution Report' ),
									'note'             => '',
									'tax_codes'        => [ 'UI' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Quarterly - Due the last day of the month following the end of the quarter. (April 30, July 31, October 31, and January 31)
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 3000, //Quarterly
																	 'quarter_month'        => 1,
																	 'primary_day_of_month' => 31, //Last day
																	 'reminder_days'        => 14,
															 ],
									],
							],
	],
	'20:US:VT:00:0030' => [ //State Government [New Hires]
							'NEWHIRE' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'New Hire Report Filing' ),
									'form_description' => TTi18n::getText( 'Report of New Hires' ),
									'note'             => '',
									'tax_codes'        => [],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //As Required, within 10 days of hire date.
															 //On Hire
															 [
																	 'status_id'           => 10, //Enabled
																	 'frequency_id'        => 90100, //On Hire
																	 'due_date_delay_days' => 10,
																	 'reminder_days'       => 0,
															 ],
									],
							],

	],
	'20:US:VT:00:0040' => [ //Child Support
							'CHILDSUPPORT' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'Child Support Withholding Payment' ),
									'form_description' => TTi18n::getText( 'Child Support Withholding Payment' ),
									'note'             => '',
									'tax_codes'        => [ 'CHILDSUPPORT' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Per Pay Period
										[
												'status_id'           => 10, //Enabled
												'frequency_id'        => 1000, //Per Pay Period
												'due_date_delay_days' => 14,
												'reminder_days'       => 5,
										],
									],
							],
	],

	//WA - Washington
	'20:US:WA:00:0010' => [ //State Government [State Income Tax]
	],
	'20:US:WA:00:0020' => [ //State Government [Unemployment Insurance]
							'5208A'                  => [
									'form_code'        => '5208 A',
									'form_name'        => TTi18n::getText( '5208 A Filing' ),
									'form_description' => TTi18n::getText( 'Quarterly Tax and Wage Detail Reports' ),
									'note'             => '',
									'tax_codes'        => [ 'UI' ],
									'filing_methods'   => [ 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Quarterly - Due the last day of the month following the end of the quarter. (April 30, July 31, October 31, and January 31)
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 3000, //Quarterly
																	 'quarter_month'        => 1,
																	 'primary_day_of_month' => 31, //Last day
																	 'reminder_days'        => 14,
															 ],
									],
							],
							'PaidFamilyMedicalLeave' => [ //This also includes CARES, in a ICESA like file format. https://paidleave.wa.gov/reporting/
									'form_code'        => 'PaidFamilyMedicalLeave',
									'form_name'        => TTi18n::getText( 'Paid Family and Medical Leave Filing' ),
									'form_description' => TTi18n::getText( 'Quarterly Wage and Hours Reports' ),
									'note'             => '',
									'tax_codes'        => [ 'PLWC' ],
									'filing_methods'   => [ 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Quarterly - Due the last day of the month following the end of the quarter. (April 30, July 31, October 31, and January 31)
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 3000, //Quarterly
																	 'quarter_month'        => 1,
																	 'primary_day_of_month' => 31, //Last day
																	 'reminder_days'        => 14,
															 ],
									],
							],
	],
	'20:US:WA:00:0030' => [ //State Government [New Hires]
							'NEWHIRE' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'New Hire Report Filing' ),
									'form_description' => TTi18n::getText( 'Report of New Hires' ),
									'note'             => '',
									'tax_codes'        => [],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //As Required, within 20 days of hire date.
															 //On Hire
															 [
																	 'status_id'           => 10, //Enabled
																	 'frequency_id'        => 90100, //On Hire
																	 'due_date_delay_days' => 20,
																	 'reminder_days'       => 0,
															 ],
									],
							],

	],
	'20:US:WA:00:0100' => [ //Workers Compensation
							'WC' => [
									'form_code'        => 'WC',
									'form_name'        => TTi18n::getText( 'WC Filing' ),
									'form_description' => TTi18n::getText( 'Workers Compensation Quarterly Report' ),
									'note'             => '',
									'tax_codes'        => [ 'WC' ],
									'filing_methods'   => [ 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Quarterly - Due the last day of the month following the end of the quarter. (April 30, July 31, October 31, and January 31)
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 3000, //Quarterly
																	 'quarter_month'        => 1,
																	 'primary_day_of_month' => 31, //Last day
																	 'reminder_days'        => 14,
															 ],
									],
							],
	],
	'20:US:WA:00:0040' => [ //Child Support
							'CHILDSUPPORT' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'Child Support Withholding Payment' ),
									'form_description' => TTi18n::getText( 'Child Support Withholding Payment' ),
									'note'             => '',
									'tax_codes'        => [ 'CHILDSUPPORT' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Per Pay Period
										[
												'status_id'           => 10, //Enabled
												'frequency_id'        => 1000, //Per Pay Period
												'due_date_delay_days' => 14,
												'reminder_days'       => 5,
										],
									],
							],
	],

	//WI - Wisconsin
	'20:US:WI:00:0010' => [ //State Government [State Income Tax]
							'FW2' => [
									'form_code'        => 'W2', //Includes W3
									'form_name'        => TTi18n::getText( 'W2 State Filing' ),
									'form_description' => TTi18n::getText( 'Wage and Tax Statement' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'EFILE' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Annual
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 1, //Jan
																	 'primary_day_of_month' => 31,
																	 'reminder_days'        => 14,
															 ],
									],
							],
							'WT7' => [
									'form_code'        => 'WT-7',
									'form_name'        => TTi18n::getText( 'WT-7 Filing' ),
									'form_description' => TTi18n::getText( 'Employer\'s Annual Reconciliation of Income Tax Withheld' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EFT' ],
									'flags'            => [
											'include_w2'          => true,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Annual
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 1, //Jan
																	 'primary_day_of_month' => 31,
																	 'reminder_days'        => 14,
															 ],
									],
							],
							'WT6' => [
									'form_code'        => 'WT-6',
									'form_name'        => TTi18n::getText( 'WT-6 Filing' ),
									'form_description' => TTi18n::getText( 'Withholding Tax Deposit Report' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EFT' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Semimonthly: Due the last day of the month for the 1st through the 15th of the same month. Due the 15th of the following month for the 16th through the end of the month.
										//Monthly: Due the last day of the following month.
										//Quarterly: Due the last day of the month following the end of the quarter. (April 30, July 31, October 31, and January 31).

										//Semi-Monthly
										[
												'status_id'              => 20, //Disabled
												'frequency_id'           => 4200, //Semi-Monthly
												'primary_day_of_month'   => 15,
												'secondary_day_of_month' => 31,
												'due_date_delay_days'    => 15,
												'reminder_days'          => 0,
										],

										//Monthly
										[
												'status_id'            => 10, //Enabled
												'frequency_id'         => 4100, //Monthly
												'primary_day_of_month' => 31, //Last day
												'reminder_days'        => 7,
										],

										//Quarterly
										[
												'status_id'            => 20, //Disabled
												'frequency_id'         => 3000, //Quarterly
												'quarter_month'        => 1,
												'primary_day_of_month' => 31, //Last Day
												'reminder_days'        => 14,
										],
									],
							],
	],
	'20:US:WI:00:0020' => [ //State Government [Unemployment Insurance]
							'UCT101' => [
									'form_code'        => 'UCT-101',
									'form_name'        => TTi18n::getText( 'UCT-101 Filing' ),
									'form_description' => TTi18n::getText( 'Quarterly Contribution Report' ),
									'note'             => '',
									'tax_codes'        => [ 'UI' ],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [ 'CHECK' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Quarterly - Due the last day of the month following the end of the quarter. (April 30, July 31, October 31, and January 31)
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 3000, //Quarterly
																	 'quarter_month'        => 1,
																	 'primary_day_of_month' => 31, //Last day
																	 'reminder_days'        => 14,
															 ],
									],
							],
	],
	'20:US:WI:00:0030' => [ //State Government [New Hires]
							'NEWHIRE' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'New Hire Report Filing' ),
									'form_description' => TTi18n::getText( 'Report of New Hires' ),
									'note'             => '',
									'tax_codes'        => [],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //As Required, within 20 days of hire date.
															 //On Hire
															 [
																	 'status_id'           => 10, //Enabled
																	 'frequency_id'        => 90100, //On Hire
																	 'due_date_delay_days' => 20,
																	 'reminder_days'       => 0,
															 ],
									],
							],

	],
	'20:US:WI:00:0040' => [ //Child Support
							'CHILDSUPPORT' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'Child Support Withholding Payment' ),
									'form_description' => TTi18n::getText( 'Child Support Withholding Payment' ),
									'note'             => '',
									'tax_codes'        => [ 'CHILDSUPPORT' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Per Pay Period
										[
												'status_id'           => 10, //Enabled
												'frequency_id'        => 1000, //Per Pay Period
												'due_date_delay_days' => 14,
												'reminder_days'       => 5,
										],
									],
							],
	],

	//WV - West Virginia
	'20:US:WV:00:0010' => [ //State Government [State Income Tax]
							'FW2'      => [
									'form_code'        => 'W2', //Includes W3
									'form_name'        => TTi18n::getText( 'W2 State Filing' ),
									'form_description' => TTi18n::getText( 'Wage and Tax Statement' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'EFILE' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Annual
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 1, //Jan
																	 'primary_day_of_month' => 31,
																	 'reminder_days'        => 14,
															 ],
									],
							],
							'WVIT103'  => [
									'form_code'        => 'WV/IT-103',
									'form_name'        => TTi18n::getText( 'WV/IT-103 Filing' ),
									'form_description' => TTi18n::getText( 'Annual Reconciliation of Income Tax Withheld' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => true,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Annual
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 2000, //Annual
																	 'primary_month'        => 2, //Feb
																	 'primary_day_of_month' => 28,
																	 'reminder_days'        => 14,
															 ],
									],
							],
							'WVIT101Q' => [
									'form_code'        => 'WV/IT-101Q',
									'form_name'        => TTi18n::getText( 'WV/IT-101Q Filing' ),
									'form_description' => TTi18n::getText( 'Employer\'s Quarterly Return of Income Tax Withheld' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [ 'CHECK' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Quarterly - Due the last day of the month following the end of the quarter. (April 30, July 31, October 31, and January 31)
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 3000, //Quarterly
																	 'quarter_month'        => 1,
																	 'primary_day_of_month' => 31, //Last day
																	 'reminder_days'        => 14,
															 ],
									],
							],
							'WVIT101V' => [
									'form_code'        => 'WV/IT-101V',
									'form_name'        => TTi18n::getText( 'WV/IT-101V Payment' ),
									'form_description' => TTi18n::getText( 'Employer\'s Monthly Return of Income Tax Withheld' ),
									'note'             => '',
									'tax_codes'        => [ 'INCOME', 'SS', 'MEDICARE' ],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [ 'CHECK' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Monthly
										[
												'status_id'            => 10, //Enabled
												'frequency_id'         => 4100, //Monthly
												'primary_day_of_month' => 15,
												'reminder_days'        => 7,
										],
									],
							],
	],
	'20:US:WV:00:0020' => [ //State Government [Unemployment Insurance]
							'WVUCA154' => [
									'form_code'        => 'WVUC-A-154',
									'form_name'        => TTi18n::getText( 'WVUC-A-154 Filing' ),
									'form_description' => TTi18n::getText( 'Quarterly Contribution Report' ),
									'note'             => '',
									'tax_codes'        => [ 'UI' ],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [ 'CHECK' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Quarterly - Due the last day of the month following the end of the quarter. (April 30, July 31, October 31, and January 31)
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 3000, //Quarterly
																	 'quarter_month'        => 1,
																	 'primary_day_of_month' => 31, //Last day
																	 'reminder_days'        => 14,
															 ],
									],
							],
	],
	'20:US:WV:00:0030' => [ //State Government [New Hires]
							'NEWHIRE' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'New Hire Report Filing' ),
									'form_description' => TTi18n::getText( 'Report of New Hires' ),
									'note'             => '',
									'tax_codes'        => [],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //As Required, within 20 days of hire date.
															 //On Hire
															 [
																	 'status_id'           => 10, //Enabled
																	 'frequency_id'        => 90100, //On Hire
																	 'due_date_delay_days' => 20,
																	 'reminder_days'       => 0,
															 ],
									],
							],

	],
	'20:US:WV:00:0040' => [ //Child Support
							'CHILDSUPPORT' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'Child Support Withholding Payment' ),
									'form_description' => TTi18n::getText( 'Child Support Withholding Payment' ),
									'note'             => '',
									'tax_codes'        => [ 'CHILDSUPPORT' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Per Pay Period
										[
												'status_id'           => 10, //Enabled
												'frequency_id'        => 1000, //Per Pay Period
												'due_date_delay_days' => 14,
												'reminder_days'       => 5,
										],
									],
							],
	],

	//WY - Wyoming
	'20:US:WY:00:0010' => [ //State Government [State Income Tax]
	],
	'20:US:WY:00:0020' => [ //State Government [Unemployment Insurance]
							'WYO056' => [
									'form_code'        => 'WYO056',
									'form_name'        => TTi18n::getText( 'WYO056 Filing' ),
									'form_description' => TTi18n::getText( 'Quarterly UI/WC Summary Report' ),
									'note'             => '',
									'tax_codes'        => [ 'UI' ],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [ 'CHECK' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => true,
											'file_zero_liability' => true,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //Quarterly - Due the last day of the month following the end of the quarter. (April 30, July 31, October 31, and January 31)
															 [
																	 'status_id'            => 10, //Enabled
																	 'frequency_id'         => 3000, //Quarterly
																	 'quarter_month'        => 1,
																	 'primary_day_of_month' => 31, //Last day
																	 'reminder_days'        => 14,
															 ],
									],
							],
	],
	'20:US:WY:00:0030' => [ //State Government [New Hires]
							'NEWHIRE' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'New Hire Report Filing' ),
									'form_description' => TTi18n::getText( 'Report of New Hires' ),
									'note'             => '',
									'tax_codes'        => [],
									'filing_methods'   => [ 'PRINT' ],
									'payment_methods'  => [],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [  //As Required, within 20 days of hire date.
															 //On Hire
															 [
																	 'status_id'           => 10, //Enabled
																	 'frequency_id'        => 90100, //On Hire
																	 'due_date_delay_days' => 20,
																	 'reminder_days'       => 0,
															 ],
									],
							],

	],
	'20:US:WY:00:0040' => [ //Child Support
							'CHILDSUPPORT' => [
									'form_code'        => '',
									'form_name'        => TTi18n::getText( 'Child Support Withholding Payment' ),
									'form_description' => TTi18n::getText( 'Child Support Withholding Payment' ),
									'note'             => '',
									'tax_codes'        => [ 'CHILDSUPPORT' ],
									'filing_methods'   => [ 'PRINT', 'EFILE' ],
									'payment_methods'  => [ 'CHECK', 'EPAY' ],
									'flags'            => [
											'include_w2'          => false,
											'file_zero_wage'      => false,
											'file_zero_liability' => false,
											'auto_file'           => false,
											'auto_pay'            => false,
									],
									'frequency'        => [
										//Per Pay Period
										[
												'status_id'           => 10, //Enabled
												'frequency_id'        => 1000, //Per Pay Period
												'due_date_delay_days' => 14,
												'reminder_days'       => 5,
										],
									],
							],
	],
];
?>