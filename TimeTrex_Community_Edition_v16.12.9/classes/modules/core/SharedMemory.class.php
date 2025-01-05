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
 * @package Core
 */

/**
 * Class SharedMemory
 */
class SharedMemory {
	protected $obj = null;

	/**
	 * SharedMemory constructor.
	 */
	function __construct() {
		global $config_vars;

		$cache_options = [
				'caching'                => true,
				'cacheDir'               => $config_vars['cache']['dir'] . DIRECTORY_SEPARATOR,
				'lifeTime'               => 86400, //One day, cache should be cleared when the data is modified
				'fileLocking'            => true,
				'writeControl'           => true,
				'readControl'            => true,
				'memoryCaching'          => false,
				'onlyMemoryCaching'      => false,
				'automaticSerialization' => true,
				//'hashedDirectoryLevel'   => 1,
				'fileNameProtection'     => false,
				'redisHost'              => ( isset( $config_vars['cache']['redis_host'] ) ) ? $config_vars['cache']['redis_host'] : '',
				'redisDB'                => ( isset( $config_vars['cache']['redis_db'] ) ) ? $config_vars['cache']['redis_db'] : '',
				'redisPassword'          => ( isset( $config_vars['cache']['redis_password'] ) ) ? $config_vars['cache']['redis_password'] : '',
		];

		if ( isset( $config_vars['cache']['redis_host'] ) && $config_vars['cache']['redis_host'] != '' ) {
			//To reduce the number of connections to REDIS, share the same connection across all SharedMemory instances.
			//  Both ProgressBar and RateLimit classes instantiate SharedMemory.
			global $tt_shared_memory_obj;
			if ( is_object( $tt_shared_memory_obj ) ) {
				$this->obj = $tt_shared_memory_obj;
			} else {
				$split_server = explode( ',', $config_vars['cache']['redis_host'] );
				$cache_options['redisHost'] = $split_server[0]; //Use just the master server, as shared memory doesn't need to span servers.

				require_once( Environment::getBasePath() . 'classes' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'other' . DIRECTORY_SEPARATOR . 'Redis_Cache_Lite.class.php' );
				$tt_shared_memory_obj = $this->obj = new Redis_Cache_Lite( $cache_options );
			}
		} else {
			$this->obj = new Cache_Lite( $cache_options );
		}

		return true;
	}

	/**
	 * @param $key
	 * @param $value
	 * @return bool
	 */
	function set( $key, $value ) {
		if ( is_string( $key ) ) {
			return $this->obj->save( $value, $key, 'sharedmemory' );
		}

		return false;
	}

	/**
	 * @param $key
	 * @return bool
	 */
	function get( $key ) {
		if ( is_string( $key ) ) {
			return $this->obj->get( $key, 'sharedmemory' );
		}

		return false;
	}

	/**
	 * @param $key
	 * @return bool
	 */
	function delete( $key ) {
		if ( is_string( $key ) ) {
			return $this->obj->remove( $key, 'sharedmemory' );
		}

		return false;
	}
}

?>
