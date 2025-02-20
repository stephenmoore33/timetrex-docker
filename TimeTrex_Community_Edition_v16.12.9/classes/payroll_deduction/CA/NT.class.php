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
 * @package PayrollDeduction\CA
 */
class PayrollDeduction_CA_NT extends PayrollDeduction_CA {
	var $provincial_income_tax_rate_options = [
			20240101 => [
					[ 'income' => 50597, 'rate' => 5.9, 'constant' => 0 ],
					[ 'income' => 101198, 'rate' => 8.6, 'constant' => 1366 ],
					[ 'income' => 164525, 'rate' => 12.2, 'constant' => 5009 ],
					[ 'income' => 164525, 'rate' => 14.05, 'constant' => 8053 ],
			],
			20230101 => [
					[ 'income' => 48326, 'rate' => 5.9, 'constant' => 0 ],
					[ 'income' => 96655, 'rate' => 8.6, 'constant' => 1305 ],
					[ 'income' => 157139, 'rate' => 12.2, 'constant' => 4784 ],
					[ 'income' => 157139, 'rate' => 14.05, 'constant' => 7691 ],
			],
			20220101 => [
					[ 'income' => 45462, 'rate' => 5.9, 'constant' => 0 ],
					[ 'income' => 90927, 'rate' => 8.6, 'constant' => 1227 ],
					[ 'income' => 147826, 'rate' => 12.2, 'constant' => 4501 ],
					[ 'income' => 147826, 'rate' => 14.05, 'constant' => 7236 ],
			],
			20210101 => [
					[ 'income' => 44396, 'rate' => 5.9, 'constant' => 0 ],
					[ 'income' => 88796, 'rate' => 8.6, 'constant' => 1199 ],
					[ 'income' => 144362, 'rate' => 12.2, 'constant' => 4395 ],
					[ 'income' => 144362, 'rate' => 14.05, 'constant' => 7066 ],
			],
			20200101 => [
					[ 'income' => 43957, 'rate' => 5.9, 'constant' => 0 ],
					[ 'income' => 87916, 'rate' => 8.6, 'constant' => 1187 ],
					[ 'income' => 142932, 'rate' => 12.2, 'constant' => 4352 ],
					[ 'income' => 142932, 'rate' => 14.05, 'constant' => 6996 ],
			],
			20190101 => [
					[ 'income' => 43137, 'rate' => 5.9, 'constant' => 0 ],
					[ 'income' => 86277, 'rate' => 8.6, 'constant' => 1165 ],
					[ 'income' => 140267, 'rate' => 12.2, 'constant' => 4271 ],
					[ 'income' => 140267, 'rate' => 14.05, 'constant' => 6866 ],
			],
			20180101 => [
					[ 'income' => 42209, 'rate' => 5.9, 'constant' => 0 ],
					[ 'income' => 84420, 'rate' => 8.6, 'constant' => 1140 ],
					[ 'income' => 137248, 'rate' => 12.2, 'constant' => 4179 ],
					[ 'income' => 137248, 'rate' => 14.05, 'constant' => 6718 ],
			],
			20170101 => [
					[ 'income' => 41585, 'rate' => 5.9, 'constant' => 0 ],
					[ 'income' => 83172, 'rate' => 8.6, 'constant' => 1123 ],
					[ 'income' => 135219, 'rate' => 12.2, 'constant' => 4117 ],
					[ 'income' => 135219, 'rate' => 14.05, 'constant' => 6619 ],
			],
			20160101 => [
					[ 'income' => 41011, 'rate' => 5.9, 'constant' => 0 ],
					[ 'income' => 82024, 'rate' => 8.6, 'constant' => 1107 ],
					[ 'income' => 133353, 'rate' => 12.2, 'constant' => 4060 ],
					[ 'income' => 133353, 'rate' => 14.05, 'constant' => 6527 ],
			],
			20150101 => [
					[ 'income' => 40484, 'rate' => 5.9, 'constant' => 0 ],
					[ 'income' => 80971, 'rate' => 8.6, 'constant' => 1093 ],
					[ 'income' => 131641, 'rate' => 12.2, 'constant' => 4008 ],
					[ 'income' => 131641, 'rate' => 14.05, 'constant' => 6443 ],
			],
			20140101 => [
					[ 'income' => 39808, 'rate' => 5.9, 'constant' => 0 ],
					[ 'income' => 79618, 'rate' => 8.6, 'constant' => 1075 ],
					[ 'income' => 129441, 'rate' => 12.2, 'constant' => 3941 ],
					[ 'income' => 129441, 'rate' => 14.05, 'constant' => 6336 ],
			],
			20130101 => [
					[ 'income' => 39453, 'rate' => 5.9, 'constant' => 0 ],
					[ 'income' => 78908, 'rate' => 8.6, 'constant' => 1065 ],
					[ 'income' => 128286, 'rate' => 12.2, 'constant' => 3906 ],
					[ 'income' => 128286, 'rate' => 14.05, 'constant' => 6279 ],
			],
			20120101 => [
					[ 'income' => 38679, 'rate' => 5.9, 'constant' => 0 ],
					[ 'income' => 77360, 'rate' => 8.6, 'constant' => 1044 ],
					[ 'income' => 125771, 'rate' => 12.2, 'constant' => 3829 ],
					[ 'income' => 125771, 'rate' => 14.05, 'constant' => 6156 ],
			],
			20110101 => [
					[ 'income' => 37626, 'rate' => 5.9, 'constant' => 0 ],
					[ 'income' => 75253, 'rate' => 8.6, 'constant' => 1016 ],
					[ 'income' => 122345, 'rate' => 12.2, 'constant' => 3725 ],
					[ 'income' => 122345, 'rate' => 14.05, 'constant' => 5988 ],
			],
			20100101 => [
					[ 'income' => 37106, 'rate' => 5.9, 'constant' => 0 ],
					[ 'income' => 74214, 'rate' => 8.6, 'constant' => 1002 ],
					[ 'income' => 120656, 'rate' => 12.2, 'constant' => 3674 ],
					[ 'income' => 120656, 'rate' => 14.05, 'constant' => 5906 ],
			],
			20090101 => [
					[ 'income' => 36885, 'rate' => 5.9, 'constant' => 0 ],
					[ 'income' => 73772, 'rate' => 8.6, 'constant' => 996 ],
					[ 'income' => 119936, 'rate' => 12.2, 'constant' => 3652 ],
					[ 'income' => 119936, 'rate' => 14.05, 'constant' => 5871 ],
			],
			20080101 => [
					[ 'income' => 35986, 'rate' => 5.90, 'constant' => 0 ],
					[ 'income' => 71973, 'rate' => 8.60, 'constant' => 972 ],
					[ 'income' => 117011, 'rate' => 12.20, 'constant' => 3563 ],
					[ 'income' => 117011, 'rate' => 14.05, 'constant' => 5727 ],
			],
			20070101 => [
					[ 'income' => 35315, 'rate' => 5.90, 'constant' => 0 ],
					[ 'income' => 70631, 'rate' => 8.60, 'constant' => 954 ],
					[ 'income' => 114830, 'rate' => 12.20, 'constant' => 3496 ],
					[ 'income' => 114830, 'rate' => 14.05, 'constant' => 5621 ],
			],
	];
}

?>
