<?php /** @noinspection PhpUndefinedFunctionInspection */
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
class Debug {
	static protected $enable = false;         //Enable/Disable debug printing.
	static protected $verbosity = 5;          //Display debug info with a verbosity level equal or lesser then this.
	static protected $buffer_output = true;   //Enable/Disable output buffering.
	static protected $debug_buffer = null;    //Output buffer.
	static protected $enable_display = false; //Enable/Disable displaying of debug output
	static protected $enable_log = false;     //Enable/Disable logging of debug output
	static protected $max_line_size = 200;    //Max line size in characters. This is used to break up long lines.
	static protected $max_buffer_size = 1000; //Max buffer size in lines. **Syslog can't handle much more than 1000.
	static protected $max_buffer_time = 30;   //Max time between buffer flushes. So the buffer is flushed at least every X seconds. Helpful for long running requests.
	static protected $buffer_flush_time = null; //Last time the buffer was flushed.
	static protected $buffer_id = null;       //Unique identifier for the debug buffer.
	static protected $php_errors = 0;         //Count number of PHP errors so we can automatically email the log.
	static protected $php_last_error = null;  //Last error array.
	static protected $email_log = false;      //Determine if log needs to be emailed on shutdown.
	static protected $current_pid = null;     //Current PID

	static protected $buffer_size = 0;//Current buffer size in lines.

	/**
	 * @param $bool
	 */
	static function setEnable( $bool ) {
		self::setBufferID();
		self::$enable = $bool;
	}

	/**
	 * @return bool
	 */
	static function getEnable() {
		return self::$enable;
	}

	/**
	 * @param $bool
	 */
	static function setBufferOutput( $bool ) {
		self::$buffer_output = $bool;
	}

	/**
	 * @param $level
	 */
	static function setVerbosity( $level ) {
		global $db;

		self::$verbosity = (int)$level;

		if ( is_object( $db ) && $level == 11 ) {
			$db->debug = true;
		}
	}

	/**
	 * @return int
	 */
	static function getPHPErrors() {
		return self::$php_errors;
	}

	/**
	 * @return int
	 */
	static function getVerbosity() {
		return self::$verbosity;
	}

	/**
	 * @param $bool
	 */
	static function setEnableDisplay( $bool ) {
		self::$enable_display = $bool;
	}

	/**
	 * @return bool
	 */
	static function getEnableDisplay() {
		return self::$enable_display;
	}

	/**
	 * @param $bool
	 */
	static function setEnableLog( $bool ) {
		self::$enable_log = $bool;
	}

	/**
	 * @return bool
	 */
	static function getEnableLog() {
		return self::$enable_log;
	}

	static function setBufferID() {
		if ( self::$buffer_id == null ) {
			self::$buffer_id = uniqid();
		}
	}

	/**
	 * Generates a syslog identifier based on the application configuration and optional parameters.
	 *
	 * This identifier is used to tag syslog entries with a unique identifier that can include
	 * an additional identifier and the company name. If no company name is provided, a default
	 * identifier is used. The resulting string is sanitized to ensure it is a valid syslog identifier.
	 *
	 * @param string|null $extra_ident Additional identifier to append to the syslog ident, defaults to null.
	 * @param string|null $company_name Name of the company to include in the syslog ident, defaults to null.
	 * @return string The sanitized syslog identifier.
	 */
	static function getSyslogIdent( $extra_ident = null, $company_name = null ) {
		global $config_vars, $current_company;

		if ( $company_name != '' ) {
			$suffix = $company_name;
		} else if ( isset( $current_company ) && is_object( $current_company ) ) {
			$suffix = $current_company->getShortName();
		} else {
			$suffix = 'System';
		}

		if ( isset( $config_vars['debug']['syslog_ident'] ) && $config_vars['debug']['syslog_ident'] != '' ) {
			$retval = $config_vars['debug']['syslog_ident'] . '-' . $suffix . $extra_ident;
		} else {
			$retval = APPLICATION_NAME . '-' . $suffix . $extra_ident;
		}

		return strtolower( preg_replace( '/[^a-zA-Z0-9-]/', '', escapeshellarg( $retval ) ) ); //This will remove spaces.
	}

	/**
	 * Retrieves the syslog facility code based on the log type.
	 * The log type corresponds to one of the three primary log types:
	 * 0 => 'debug', 1 => 'client', 2 => 'timeclock'.
	 * The facility code determines where the logs will be sent or how they will be handled.
	 *
	 * @param int $log_type The index representing the log type.
	 * @return int The syslog facility code associated with the given log type.
	 */
	static function getSyslogFacility( $log_type = 0 ) {
		global $config_vars;
		if ( isset( $config_vars['debug']['syslog_facility'] ) && $config_vars['debug']['syslog_facility'] != '' ) {
			$facility_arr = explode( ',', $config_vars['debug']['syslog_facility'] );
			if ( is_array( $facility_arr ) && isset( $facility_arr[(int)$log_type] ) ) {
				return ( is_numeric( $facility_arr[(int)$log_type] ) ) ? $facility_arr[(int)$log_type] : constant( trim( $facility_arr[(int)$log_type] ) );
			}
		}

		return LOG_LOCAL7; //Default
	}

	/**
	 * Retrieves the syslog priority level based on the provided log type.
	 *
	 * The syslog priority level determines the importance of the log message.
	 * This function maps a log type to a syslog priority, which can be configured
	 * in the application's debug settings. If no mapping is found, a default priority
	 * level is returned.
	 *
	 * @param int $log_type The type of log for which to get the priority level.
	 * @return int The syslog priority level associated with the given log type.
	 */
	static function getSyslogPriority( $log_type = 0 ) {
		global $config_vars;

		if ( isset( $config_vars['debug']['syslog_priority'] ) && $config_vars['debug']['syslog_priority'] != '' ) {
			$priority_arr = explode( ',', $config_vars['debug']['syslog_priority'] );
			if ( is_array( $priority_arr ) && isset( $priority_arr[(int)$log_type] ) ) {
				return ( is_numeric( $priority_arr[(int)$log_type] ) ) ? $priority_arr[(int)$log_type] : constant( trim( $priority_arr[(int)$log_type] ) );
			}
		}

		return LOG_DEBUG; //Default
	}


	/**
	 * Calculates the elapsed time since the start of the request in milliseconds.
	 * This function is useful for profiling and debugging, allowing developers to
	 * measure the time taken to execute portions of code or to track the total
	 * execution time of a script. The precision is in milliseconds to provide
	 * a fine-grained timing mechanism.
	 *
	 * @return float The elapsed time in milliseconds since the start of the request.
	 */
	static function getExecutionTime() {
		return ceil( ( ( microtime( true ) - $_SERVER['REQUEST_TIME_FLOAT'] ) * 1000 ) );
	}

	/**
	 * Splits long debug lines or array dumps into smaller chunks.
	 *
	 * This function is designed to prevent syslog overflows by breaking up text that exceeds
	 * a certain length into smaller parts. Each part can optionally have a prefix or suffix.
	 * This is particularly useful when logging large amounts of data that could exceed the
	 * maximum line size supported by syslog, ensuring that all debug information is captured
	 * and logged correctly without being truncated.
	 *
	 * @param string $text The text to be split into smaller parts.
	 * @param string|null $prefix A string to prepend to each split line of text.
	 * @param string|null $suffix A string to append to each split line of text.
	 * @return array An array of strings, each representing a portion of the split text.
	 */
	static function splitInput( $text, $prefix = null, $suffix = null ) {
		if ( strlen( $text ) > self::$max_line_size ) {
			$retarr = [];

			$lines = explode( PHP_EOL, $text ); //Split on newlines first.
			foreach ( $lines as $line ) {
				$split_lines = str_split( $line, self::$max_line_size ); //Split on long lines next.
				foreach ( $split_lines as $split_line ) {
					$retarr[] = $prefix . $split_line . $suffix;
				}
			}
			unset( $lines, $line, $split_lines, $split_line );
		} else {
			$retarr = [ $prefix . $text . $suffix ]; //Always returns an array.
		}

		return $retarr;
	}

	/**
	 * Retrieves the Process ID (PID) of the current process.
	 *
	 * This function is used to obtain the unique identifier for the current process.
	 * The PID can be used for various purposes such as debugging, process management,
	 * or as part of log messages to differentiate between multiple instances of a script.
	 * If the 'getmypid' function is not available, it defaults to 0.
	 *
	 * @return int|null The PID of the current process or null if the PID has not been set.
	 */
	static function getCurrentPID() {
		if ( self::$current_pid === null ) {
			if ( function_exists( 'getmypid' ) == true ) {
				self::$current_pid = getmypid();
			} else {
				self::$current_pid = 0;
			}
		}

		return self::$current_pid;
	}

	/**
	 * Logs or displays a text message with additional context information.
	 *
	 * This function is responsible for handling debug text messages. It can either log the message,
	 * display it, or buffer it for later use, depending on the configuration. It includes the process ID,
	 * execution time, and line number for easier identification and debugging. The verbosity level controls
	 * whether the message is processed based on the current verbosity setting.
	 *
	 * @param string|null $text The text message to be logged or displayed.
	 * @param string $file The file from which the debug message is being sent.
	 * @param int $line The line number in the file from which the debug message is being sent.
	 * @param string $method The method or function from which the debug message is being sent.
	 * @param int $verbosity The verbosity level of the message, which determines if it should be processed.
	 * @return bool True if the message was processed, false otherwise.
	 */
	static function Text( $text = null, $file = __FILE__, $line = __LINE__, $method = __METHOD__, $verbosity = 9 ) {
		if ( $verbosity > self::getVerbosity() || self::$enable == false ) {
			return false;
		}

		if ( empty( $method ) ) {
			$method = 'GLOBAL: '; //Was: [Function]
		} else {
			$method = $method . '(): ';
		}

		//If text is too long, split it into an array.
		$text_arr = self::splitInput( $text, '[P'. str_pad( self::getCurrentPID(), 7, 0, STR_PAD_LEFT ) .'] [' . str_pad( self::getExecutionTime(), 5, 0, STR_PAD_LEFT ) . 'ms] [L' . str_pad( $line, 4, 0, STR_PAD_LEFT ) . ']: ' . $method, PHP_EOL );

		if ( self::$buffer_output == true ) {
			foreach ( $text_arr as $text_line ) {
				self::$debug_buffer[] = [ $verbosity, $text_line ];
				self::$buffer_size++;
				self::handleBufferSize( $line, $method );
			}
		} else {
			if ( self::$enable_display == true ) {
				foreach ( $text_arr as $text_line ) {
					echo $text_line;
				}
			} else if ( OPERATING_SYSTEM != 'WIN' && self::$enable_log == true ) {
				foreach ( $text_arr as $text_line ) {
					syslog( LOG_DEBUG, $text_line );
				}
			}
		}

		return true;
	}

	/**
	 * Captures and returns the profiling information of timers from a given object.
	 *
	 * This function is designed to work with objects that have profiling capabilities, specifically
	 * those that can print timer information for performance analysis. It captures the output of the
	 * profile object's timer printing method, which is expected to be buffered, and then returns it.
	 * If the provided object is not valid, the function will return false.
	 *
	 * @param object $profile_obj The profile object with timers to capture information from.
	 * @return string|false The captured profiling information as a string, or false if the input is not a valid object.
	 */
	static function profileTimers( $profile_obj ) {
		if ( !is_object( $profile_obj ) ) {
			return false;
		}

		ob_start();
		$profile_obj->printTimers();
		$ob_contents = ob_get_contents();
		ob_end_clean();

		return $ob_contents;
	}

	/**
	 * Splits the input text into an array based on the maximum line size.
	 *
	 * This function is used to ensure that each line of debug text does not exceed the maximum line size.
	 * It prepends a formatted string containing the process ID, execution time, and line number to each split line.
	 * This helps in maintaining readability and consistency in the debug logs.
	 *
	 * @param string $text The input text to be split.
	 * @param string $prepend A formatted string to prepend to each line of the split text.
	 * @param string $eol The end-of-line marker.
	 * @return bool
	 */
	static function showCacheProfile() {
		if ( Debug::getVerbosity() >= 10 ) {
			global $__tt_cache_profiler;
			if ( isset( $__tt_cache_profiler ) ) {
				self::Text( 'Cache Profile: Reads: ' . $__tt_cache_profiler['total_read'] . ' Hits: '. $__tt_cache_profiler['total_read_hits'] .' ('. ( ( $__tt_cache_profiler['total_read'] > 0 ) ? ( round( ( $__tt_cache_profiler['total_read_hits'] / $__tt_cache_profiler['total_read'] ) * 100 ) ) : 0 ) .'%) Writes: '. $__tt_cache_profiler['total_write'] .' Deletes: '. $__tt_cache_profiler['total_delete'], __FILE__, __LINE__, __METHOD__, 0 );
			}
		}

		return true;
	}

	/**
	 * Splits the input text into an array based on the maximum line size.
	 *
	 * This function is used to ensure that each line of debug text does not exceed the maximum line size.
	 * It is particularly useful for systems where long lines can cause issues, such as in syslog entries.
	 * By splitting long text into manageable sizes, it maintains readability and avoids truncation.
	 *
	 * @param string $text The input text to be split into lines.
	 * @return array An array of strings, each representing a line of the split text.
	 */
	static function splitTextIntoLines( $text ) {
		// Code that performs the splitting would go here.
	}
	static function showSQLProfile() {
		if ( Debug::getVerbosity() >= 11 ) {
			global $__tt_sql_profiler;
			if ( isset( $__tt_sql_profiler ) ) {
				self::Text( 'SQL Profile: Queries: ' . $__tt_sql_profiler['total_queries'] . ' ( Read: '. $__tt_sql_profiler['total_read_queries'] .' Write: '. $__tt_sql_profiler['total_write_queries'] .' ) Time: ' . $__tt_sql_profiler['total_time'] . ' ms Slowest: '. $__tt_sql_profiler['slowest_query']['time'], __FILE__, __LINE__, __METHOD__, 0 );
				self::Query( $__tt_sql_profiler['slowest_query']['query'], $__tt_sql_profiler['slowest_query']['ph'], __FILE__, __LINE__, __METHOD__, 0 );
				//self::Text( '  Slowest Query Backtrace: ' . $__tt_sql_profiler['slowest_query']['backtrace'], __FILE__, __LINE__, __METHOD__, 0 );
			}
		}

		return true;
	}

	/**
	 * Generates a backtrace string from the current point of execution.
	 *
	 * This function captures the backtrace of the call stack at the point where it is invoked.
	 * It iterates through each trace, formats it with relevant details such as class, type,
	 * function, arguments, file, and line number, and then concatenates them into a single string.
	 * This is useful for debugging purposes to understand the sequence of function calls leading
	 * to a particular point in the code.
	 *
	 * @return string The formatted backtrace string.
	 */
	static function backTrace() {
		$retval = '';
		$trace_arr = debug_backtrace();
		if ( is_array( $trace_arr ) ) {
			$i = 1;
			foreach ( $trace_arr as $trace_line ) {
				if ( isset( $trace_line['class'] ) && isset( $trace_line['type'] ) ) {
					$class = $trace_line['class'] . $trace_line['type'];
				} else {
					$class = null;
				}

				if ( !isset( $trace_line['file'] ) ) {
					$trace_line['file'] = 'N/A';
				}

				if ( !isset( $trace_line['line'] ) ) {
					$trace_line['line'] = 'N/A';
				}

				if ( isset( $trace_line['args'] ) && is_array( $trace_line['args'] ) ) {
					$args = [];
					foreach ( $trace_line['args'] as $arg ) {
						if ( is_array( $arg ) ) {
							if ( self::getVerbosity() == 11 ) {
								$args[] = self::varDump( $arg ); //NOTE: If this contains an exception object from ADODB and is triggered from a SQL error, it could cause a circular reference and exhaust all memory.
							} else {
								//Don't display the entire array is it polutes the log and is too large for syslog anyways.
								$args[] = 'Array(' . count( $arg ) . ')';
							}
						} else if ( is_object( $arg ) ) {
							if ( self::getVerbosity() == 11 ) {
								$args[] = self::varDump( $arg ); //NOTE: If this contains an exception object from ADODB and is triggered from a SQL error, it could cause a circular reference and exhaust all memory.
							} else {
								//Don't display the entire array is it polutes the log and is too large for syslog anyways.
								$args[] = 'Object(' . get_class( $arg ) . ')';
							}
						} else {
							$args[] = $arg;
						}
					}
				}
				$retval .= '#' . $i . '.' . $class . $trace_line['function'] . '(' . implode( ', ', $args ) . ') ' . $trace_line['file'] . ':' . $trace_line['line'] . PHP_EOL;
				$i++;
			}
		}
		unset( $trace_arr, $trace_line, $args );

		return $retval;
	}

	/**
	 * Converts the provided array into a string representation.
	 *
	 * This function captures the output of var_dump (or print_r, if uncommented)
	 * applied to the provided array, which is useful for debugging purposes. It
	 * allows developers to see the contents of an array in a human-readable format.
	 * Note that if Xdebug is enabled, it may alter the output.
	 *
	 * @param array $array The array to be dumped into a string.
	 * @return string The string representation of the array.
	 */
	static function varDump( $array ) {
		ob_start();
		var_dump( $array ); //Xdebug may interfere with this and cause it to not display all the data...
		//print_r($array);
		$ob_contents = ob_get_contents();
		ob_end_clean();

		return $ob_contents;
	}

	/**
	 * Logs or displays an array structure with optional descriptive text.
	 *
	 * This function is used for debugging purposes to output or log the structure of an array.
	 * It can include additional descriptive text to provide context for the information being logged.
	 * The verbosity level controls the detail of the log output, and the function respects the
	 * global debug settings such as enabling or disabling debug output.
	 *
	 * @param mixed $array The array to be logged or displayed.
	 * @param string|null $text Optional descriptive text to accompany the array output.
	 * @param string $file The file from which the debug function is called.
	 * @param int $line The line number in the file from which the debug function is called.
	 * @param string $method The method or function name from which the debug function is called.
	 * @param int $verbosity The verbosity level at which this message should be logged or displayed.
	 * @return bool True if the array is logged or displayed, false if not due to verbosity settings or if debug is disabled.
	 */
	static function Arr( $array, $text = null, $file = __FILE__, $line = __LINE__, $method = __METHOD__, $verbosity = 9 ) {
		if ( $verbosity > self::getVerbosity() || self::$enable == false ) {
			return false;
		}

		if ( empty( $method ) ) {
			$method = '[Function]';
		}

		$text_arr = [];
		$text_arr[] = '[P'. str_pad( self::getCurrentPID(), 7, 0, STR_PAD_LEFT ) .'] [' . str_pad( self::getExecutionTime(), 5, 0, STR_PAD_LEFT ) . 'ms] [L' . str_pad( $line, 4, 0, STR_PAD_LEFT ) . '] Array: ' . $method . '(): ' . $text . PHP_EOL;
		$text_arr = array_merge( $text_arr, self::splitInput( self::varDump( $array ), null, PHP_EOL ) );
		$text_arr[] = PHP_EOL;

		if ( self::$buffer_output == true ) {
			foreach ( $text_arr as $text_line ) {
				self::$debug_buffer[] = [ $verbosity, $text_line ];
				self::$buffer_size++;
				self::handleBufferSize( $line, $method );
			}
		} else {
			if ( self::$enable_display == true ) {
				foreach ( $text_arr as $text_line ) {
					echo $text_line;
				}
			} else if ( OPERATING_SYSTEM != 'WIN' && self::$enable_log == true ) {
				foreach ( $text_arr as $text_line ) {
					syslog( LOG_DEBUG, $text_line );
				}
			}
		}

		return true;
	}

	/**
	 * Outputs an SQL query with placeholders replaced by actual values.
	 *
	 * This function is primarily used for debugging purposes to visualize the final SQL query
	 * with all placeholders filled in. It can help in understanding the query being executed
	 * and in diagnosing any issues with the SQL statement.
	 *
	 * @param string $query The SQL query with placeholders.
	 * @param array $ph An array of placeholder values to be inserted into the query.
	 * @param string $file The file from which the function is called.
	 * @param int $line The line number in the file from which the function is called.
	 * @param string $method The method from which the function is called.
	 * @param int $verbosity The verbosity level for the output.
	 * @return bool Returns true if the function executed successfully, false otherwise.
	 */
	static function Query( $query, $ph, $file = __FILE__, $line = __LINE__, $method = __METHOD__, $verbosity = 9 ) {
		$output_query = PHP_EOL; //Start with newline so its easier to copy&paste.

		$split_query = explode( '?', $query );
		foreach ( $split_query as $query_chunk ) {
			$ph_value = ( !empty( $ph ) ) ? array_shift( $ph ) : false; //array_shift() returns NULL if no elements are left, but the first value can also be NULL in some cases too.
			if ( is_string( $ph_value ) ) {
				$ph_value = '\'' . $ph_value . '\'';
			} else if ( $ph_value === null ) {
				$ph_value = 'NULL';
			}
			$output_query .= $query_chunk . $ph_value;
		}

		$output_query = str_replace( "\t", ' ', $output_query );

		$output_query .= ';' . PHP_EOL; //End with newline so its easier to copy&paste.

		self::Arr( $output_query, 'SQL Query: ', $file, $line, $method, $verbosity );

		return true;
	}

	/**
	 * @return array Replacement for apache_request_headers() as it wasn't reliably available and would sometimes cause PHP fatal errors due to it being undefined.
	 *
	 * Replacement for apache_request_headers() as it wasn't reliably available and would sometimes cause PHP fatal errors due to it being undefined.
	 */
	static function RequestHeaders() {
		$arh = [];
		$rx_http = '/^HTTP_/';
		foreach ( $_SERVER as $key => $val ) {
			if ( preg_match( $rx_http, $key ) ) {
				$arh_key = preg_replace( $rx_http, '', $key );

				// do some nasty string manipulations to restore the original letter case
				// this should work in most cases
				$rx_matches = explode( '_', strtolower( $arh_key ) );
				if ( count( $rx_matches ) > 0 && strlen( $arh_key ) > 2 ) {
					foreach ( $rx_matches as $ak_key => $ak_val ) {
						$rx_matches[$ak_key] = ucfirst( $ak_val );
					}
					$arh_key = implode( '-', $rx_matches );
				}
				$arh[$arh_key] = $val;
			}
		}

		if ( isset( $_SERVER['CONTENT_TYPE'] ) ) {
			$arh['Content-Type'] = $_SERVER['CONTENT_TYPE'];
		}
		if ( isset( $_SERVER['CONTENT_LENGTH'] ) ) {
			$arh['Content-Length'] = $_SERVER['CONTENT_LENGTH'];
		}

		return $arh;
	}

	/**
	 * Handles PHP errors and logs them according to the error_reporting level.
	 * This function is a custom error handler that intercepts PHP errors. It logs the errors
	 * based on the current error_reporting level and increments an internal counter of PHP errors.
	 * This is useful for tracking errors and handling them in a custom manner, especially during debugging.
	 *
	 * @param int $error_number The level of the error raised, as an integer.
	 * @param string $error_str The error message, as a string.
	 * @param string $error_file The filename that the error was raised in, as a string.
	 * @param int $error_line The line number the error was raised at, as an integer.
	 * @return bool Always returns true to indicate the error was handled.
	 */
	static function ErrorHandler( $error_number, $error_str, $error_file, $error_line ) {
		//Only handle errors included in the error_reporting()
		if ( ( error_reporting() & $error_number ) ) { //Bitwise operator.
			// This error code is not included in error_reporting
			switch ( $error_number ) {
				case E_USER_ERROR:
					$error_name = 'FATAL';
					break;
				case E_USER_WARNING:
				case E_WARNING:
					$error_name = 'WARNING';
					break;
				case E_USER_NOTICE:
				case E_NOTICE:
					$error_name = 'NOTICE';
					break;
				case E_STRICT:
					$error_name = 'STRICT';
					break;
				case E_DEPRECATED:
					$error_name = 'DEPRECATED';
					break;
				default:
					$error_name = 'UNKNOWN';
			}

			$error_name .= '(' . $error_number . ')';

			$text = 'PHP ERROR - ' . $error_name . ': ' . $error_str . ' File: ' . $error_file . ' Line: ' . $error_line;

			//If this is the first PHP error, make sure debugging is enabled so it and any others can be captured.
			if ( self::$php_errors == 0 ) {
				self::setEnable( true );
				self::setBufferOutput( true );
			}

			self::$php_errors++;

			//Display these errors in the log, but don't cause them to trigger PHP errors that forces the log to be emailed.
			if ( $error_number == E_USER_ERROR
					|| ( DEPLOYMENT_ON_DEMAND == true
							|| ( DEPLOYMENT_ON_DEMAND == false
									&& (
										//Database
											stristr( $error_str, 'unable to connect' ) === false
											&& stristr( $error_str, 'statement timeout' ) === false
											&& stristr( $error_str, 'unique constraint' ) === false
											&& stristr( $error_str, 'deadlock' ) === false
											&& stristr( $error_str, 'server has gone away' ) === false
											&& stristr( $error_str, 'software caused connection abort' ) === false
											&& stristr( $error_str, 'closed the connection unexpectedly' ) === false
											&& stristr( $error_str, 'execution was interrupted' ) === false
											&& stristr( $error_str, 'terminating connection due to administrator command' ) === false
											&& stristr( $error_str, 'could not open file' ) === false
											&& stristr( $error_str, 'no such file or directory' ) === false
											&& stristr( $error_str, 'no space left on device' ) === false
											&& stristr( $error_str, 'unserialize' ) === false
											&& stristr( $error_str, 'headers already sent by' ) === false

											//SOAP
											&& stristr( $error_str, 'An existing connection was forcibly closed by the remote host' ) === false

											//MISC
											&& stristr( $error_str, 'Unable to fork' ) === false
									)
							)
					)
			) {
				self::$email_log = true;
			}

			if ( self::$php_errors == 1 ) { //Only trigger this on the first error, so its not repeated over and over again.
				if ( PHP_SAPI != 'cli' ) { //Used to use apache_request_headers() here, but it would often fail as undefined, even though we would check function_exists() on it.
					self::Arr( self::RequestHeaders(), 'Raw Request Headers: ', $error_file, $error_line, __METHOD__, 0 );
				}

				global $HTTP_RAW_POST_DATA;
				if ( $HTTP_RAW_POST_DATA != '' ) {
					self::Arr( urldecode( $HTTP_RAW_POST_DATA ), 'Raw POST Request: ', $error_file, $error_line, __METHOD__, 0 );
				}
			}

			$back_trace = self::backTrace();
			self::Text( '(E' . self::$php_errors . ') ' . $text, $error_file, $error_line, __METHOD__, 0 );
			self::Text( $back_trace, $error_file, $error_line, __METHOD__, 0 );

			//Save last error, as error_get_last() only returns errors not caught.
			self::$php_last_error = [ 'type' => $error_number, 'message' => $error_str, 'file' => $error_file, 'line' => $error_line, 'trace' => $back_trace ];
			unset( $back_trace );
		}

		return false; //Let the standard PHP error handler work as well.
	}

	/**
	 * Retrieves the last error message reported by PHP.
	 *
	 * This function fetches the last error that occurred, typically during script execution.
	 * It is useful for logging and debugging purposes, as it provides insight into what
	 * went wrong during the execution of the script.
	 *
	 * @return string|false The error message as a string if an error occurred, or false if no error was reported.
	 */
	static function getLastPHPErrorMessage() {
		$error = error_get_last();
		if ( isset( $error['message'] ) ) {
			return $error['message'];
		}

		return false;
	}

	/**
	 * Handles the shutdown process for the application.
	 *
	 * This function is registered with PHP to execute when the script execution is complete or when exit() is called.
	 * It checks for the last occurred error and handles it accordingly. If a fatal error is detected, it increments
	 * the error counter, enables email logging, and logs the error details. It also handles special cases when the
	 * application is running through the API and ensures that any uncommitted database transactions are reported.
	 * Finally, if email logging is enabled, it sends out the error log via email.
	 *
	 * @return bool Always returns true to indicate the shutdown function completed.
	 */
	static function Shutdown() {
		$error = error_get_last();
		if ( $error !== null && isset( $error['type'] ) && $error['type'] == 1 ) { //Only trigger fatal errors on shutdown.
			self::$php_errors++;
			self::$email_log = true; //On FATAL error, the error handler is not called, just shutdown is called. So we need to make sure we increment the php_errors and enable emailing the log.
			self::Text( 'PHP ERROR - FATAL(' . $error['type'] . '): ' . $error['message'] . ' File: ' . $error['file'] . ' Line: ' . $error['line'], $error['file'], $error['line'], __METHOD__, 0 );

			if ( defined( 'TIMETREX_API' ) && TIMETREX_API == true ) { //Only when a fatal error occurs.
				global $api_message_id;
				if ( $api_message_id != '' ) {
					$progress_bar = new ProgressBar();
					$progress_bar->error( $api_message_id, TTi18n::getText( 'ERROR: Operation cannot be completed.' ) );
					unset( $progress_bar );
				}
			}
		} else {
			$error = self::$php_last_error;
		}

		if ( self::$email_log == true ) {
			//If the error log is too long, make sure we add important data to help trace it are included at the end of the log.
			global $config_vars, $current_user, $current_company;
			self::Text( 'URI: ' . ( isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : 'N/A' ) . ' IP Address: ' . Misc::getRemoteIPAddress(), __FILE__, __LINE__, __METHOD__, 10 );
			self::Text( 'USER-AGENT: ' . ( isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : 'N/A' ), __FILE__, __LINE__, __METHOD__, 10 );
			self::Text( 'Version: ' . APPLICATION_VERSION . ' (PHP: v' . phpversion() . ' ['. PHP_INT_SIZE .']) Edition: ' . getTTProductEdition() . ' Production: ' . (int)PRODUCTION . ' Server: ' . ( isset( $_SERVER['SERVER_ADDR'] ) ? $_SERVER['SERVER_ADDR'] : 'N/A' ) . ' OS: ' . OPERATING_SYSTEM . ' Database: Type: ' . ( isset( $config_vars['database']['type'] ) ? $config_vars['database']['type'] : 'N/A' ) . ' Name: ' . ( isset( $config_vars['database']['database_name'] ) ? $config_vars['database']['database_name'] : 'N/A' ) . ' Config: ' . CONFIG_FILE . ' Demo Mode: ' . (int)DEMO_MODE, __FILE__, __LINE__, __METHOD__, 10 );
			self::Text( 'Current User: ' . ( ( isset( $current_user ) && is_object( $current_user ) ) ? $current_user->getUserName() : 'N/A' ) . ' (User ID: ' . ( ( isset( $current_user ) && is_object( $current_user ) ) ? $current_user->getID() : 'N/A' ) . ') Company: ' . ( ( isset( $current_company ) && is_object( $current_company ) ) ? $current_company->getName() : 'N/A' ) . ' (Company ID: ' . ( ( isset( $current_company ) && is_object( $current_company ) ) ? $current_company->getId() : 'N/A' ) . ')', __FILE__, __LINE__, __METHOD__, 10 );

			self::Text( 'Detected PHP errors (' . self::$php_errors . '), emailing log...', __FILE__, __LINE__, __METHOD__, 0 );
			if ( $error !== null && isset( $error['type'] ) && $error['type'] != 1 ) { //Don't trigger when its a fatal error already triggered from above.
				self::Text( '  Last Error: ', __FILE__, __LINE__, __METHOD__, 0 );
				self::ErrorHandler( $error['type'], $error['message'], $error['file'], $error['line'] );
			}
			self::Text( '---------------[ ' . @date( 'd-M-Y G:i:s O' ) . ' [' . microtime( true ) . '] (PID: ' . getmypid() . ') ]---------------', __FILE__, __LINE__, __METHOD__, 0 );

			self::emailLog();
			if ( $error !== null ) { //Fatal error, write to log once more as this won't be called automatically.
				self::writeToLog();
			}
		} else {
			//Check to see if a transaction was held open, as it could be a potential problem as it was never committed.
			// Essentially, a CommitTrasnaction() should be called after every FailTransaction() before the script exits. Otherwise in things like loops the entire outer transaction would be rolled back unintentionally.
			global $db;
			if ( is_object( $db ) ) {
				$transaction_error = false;
				if ( $db->transOff > 0 ) {
					self::Text( 'ERROR: Detected UNCOMMITTED transaction: Count: ' . $db->transCnt . ' Off: ' . $db->transOff . ' OK: ' . (int)$db->_transOK . ', emailing log...', __FILE__, __LINE__, __METHOD__, 0 );
					$transaction_error = true;
				} else if ( $db->transCnt < 0 ) {
					self::Text( 'ERROR: Detected DOUBLE COMMITTED transaction: Count: ' . $db->transCnt . ' Off: ' . $db->transOff . ' OK: ' . (int)$db->_transOK . ', emailing log...', __FILE__, __LINE__, __METHOD__, 0 );
					$transaction_error = true;
				}

				if ( $transaction_error == true ) {
					self::Text( '---------------[ ' . @date( 'd-M-Y G:i:s O' ) . ' [' . microtime( true ) . '] (PID: ' . getmypid() . ') ]---------------', __FILE__, __LINE__, __METHOD__, 0 );
					self::emailLog();
					self::writeToLog(); //write to log once more as this won't be called automatically.
				}
			}
		}

		//Must go after emailLog() and writeToLog() above, otherwise the log will get cleared out everytime this runs.
		if ( PRODUCTION == false && function_exists( 'xdebug_get_gc_run_count' ) == true && xdebug_get_gc_run_count() > 0 ) {
			self::Text( 'Garbage Collector Runs: ' . xdebug_get_gc_run_count() . ' Collected Roots: ' . xdebug_get_gc_total_collected_roots(), __FILE__, __LINE__, __METHOD__, 10 );
			if ( file_exists( xdebug_get_gcstats_filename() ) ) {
				self::Arr( file_get_contents( xdebug_get_gcstats_filename() ), 'Garbage Collection Report: ', __FILE__, __LINE__, __METHOD__, 10 );
			}
			self::writeToLog();
		}

		return true;
	}

	/**
	 * Retrieves the accumulated debug output.
	 *
	 * This function compiles the debug messages stored in the debug buffer based on their verbosity level.
	 * It only includes messages that have a verbosity level less than or equal to the configured system verbosity.
	 * If there are no messages or the debug buffer is not an array, it returns false.
	 *
	 * @return string|false The compiled debug output as a string, or false if there are no messages to return.
	 */
	static function getOutput() {
		$output = null;
		if ( is_array( self::$debug_buffer ) && count( self::$debug_buffer ) > 0 ) {
			foreach ( self::$debug_buffer as $arr ) {
				$verbosity = $arr[0];
				$text = $arr[1];

				if ( $verbosity <= self::getVerbosity() ) {
					$output .= $text;
				}
			}

			return $output;
		}

		return false;
	}

	/**
	 * Sends the accumulated debug log via email.
	 *
	 * This function is responsible for sending the debug log to a system email if the application
	 * is in production mode or if the sandbox configuration is enabled. It retrieves the debug output,
	 * checks if it is non-empty, and then sends it using the system mail function. It also handles
	 * preventing recursive calls that could occur if sending the email itself triggers an error.
	 *
	 * @return bool Always returns true, indicating the email log function completed.
	 */
	static function emailLog() {
		global $config_vars;
		if ( PRODUCTION === true || ( isset( $config_vars['other']['sandbox'] ) && $config_vars['other']['sandbox'] == true ) ) {
			$output = self::getOutput();

			if ( strlen( $output ) > 0 ) {
				global $TT_DISABLE_EMAIL_LOG;

				if ( isset( $TT_DISABLE_EMAIL_LOG ) == false || $TT_DISABLE_EMAIL_LOG !== true ) { //Prevent emailLog() from triggering more errors and a emailLog infinite loop.
					$TT_DISABLE_EMAIL_LOG = true;

					Misc::sendSystemMail( APPLICATION_NAME . ' - Error!', $output );

					$TT_DISABLE_EMAIL_LOG = false;
				} else {
					self::Text( 'WARNING: Skipping sendSystemMail() to avoid nested calls...', __FILE__, __LINE__, __METHOD__, 0 );
				}
			}
		}

		return true;
	}

	/**
	 * Writes the accumulated debug information to a log.
	 *
	 * This function is responsible for writing the debug information stored in the debug buffer
	 * to a log file or system log, depending on the configuration. It includes timestamps and process
	 * IDs to help with tracing and debugging. The function checks if logging is enabled and if the
	 * buffer contains data before proceeding to write to the log. It supports writing to a file or
	 * using syslog on non-Windows systems. If writing fails, it triggers a user warning.
	 *
	 * @return bool True if writing to the log was successful, false otherwise.
	 */
	static function writeToLog() {
		if ( self::$enable_log == true && self::$buffer_output == true ) {
			global $config_vars;

			$eol = PHP_EOL;

			if ( is_array( self::$debug_buffer ) ) {
				$output = $eol . '---------------[ ' . $_SERVER['REQUEST_TIME_FLOAT'] . ' {' . @date( 'd-M-Y G:i:s O' ) . '} (PID: ' . getmypid() . ') ]---------------' . $eol;

				$verbosity_level = self::getVerbosity();
				foreach ( self::$debug_buffer as $arr ) {
					if ( $arr[0] <= $verbosity_level ) {
						$output .= $arr[1];
					}
				}

				$output .= '---------------[ ' . microtime( true ) . ' {' . @date( 'd-M-Y G:i:s O' ) . '} (PID: ' . getmypid() . ') ]---------------' . $eol;

				if ( isset( $config_vars['debug']['enable_syslog'] ) && $config_vars['debug']['enable_syslog'] == true && OPERATING_SYSTEM != 'WIN' ) {
					//If using rsyslog, need to set:
					//$MaxMessageSize 256000 #Above ModuleLoad imtcp
					openlog( self::getSyslogIdent(), 11, self::getSyslogFacility( 0 ) ); //11 = LOG_PID | LOG_NDELAY | LOG_CONS
					syslog( self::getSyslogPriority( 0 ), $output );                     //Used to strip_tags output, but that was likely causing problems with SQL queries with >= and <= in them.
					closelog();
				} else {
					if ( isset( $config_vars['path']['log'] ) && $config_vars['path']['log'] != '' ) {
						if ( is_writable( $config_vars['path']['log'] ) ) {
							$file_name = $config_vars['path']['log'] . DIRECTORY_SEPARATOR . 'timetrex.log';
							$fp = @fopen( $file_name, 'a' );
							if ( $fp !== false ) {
								@fwrite( $fp, $output ); //Used to strip_tags output, but that was likely causing problems with SQL queries with >= and <= in them.
								@fclose( $fp );
								unset( $output );
							} else {
								trigger_error( 'ERROR: Unable to write to log file: ' . ( isset( $config_vars['path']['log'] ) ? $config_vars['path']['log'] . '/' : 'N/A' ) .' PHP ERROR: '. Debug::getLastPHPErrorMessage(), E_USER_WARNING );
							}
						} else {
							trigger_error( 'ERROR: Unable to write to log file in directory: ' . ( isset( $config_vars['path']['log'] ) ? $config_vars['path']['log'] . '/' : 'N/A' ) .' PHP ERROR: '. Debug::getLastPHPErrorMessage(), E_USER_WARNING );
						}
					}
				}

				return true;
			}
		}

		return false;
	}

	/**
	 * Displays the debug buffer if enabled and populated.
	 *
	 * This function is responsible for outputting the contents of the debug buffer to the screen.
	 * It is only executed if both display and buffer output are enabled. The function also reports
	 * memory usage and the size of the buffer. It is typically used for debugging purposes to provide
	 * insights into the application's state and behavior during execution.
	 *
	 * @return bool True if the debug display was executed, false otherwise.
	 */
	static function Display() {
		if ( self::$enable_display == true && self::$buffer_output == true ) {

			$output = self::getOutput();

			if ( function_exists( 'memory_get_usage' ) ) {
				$memory_usage = memory_get_usage();
			} else {
				$memory_usage = 'N/A';
			}

			if ( strlen( $output ) > 0 ) {
				echo PHP_EOL . 'Debug Buffer' . PHP_EOL;
				echo '============================================================================' . PHP_EOL;
				echo 'Memory Usage: ' . $memory_usage . ' Buffer Size: ' . self::$buffer_size . PHP_EOL;
				echo '----------------------------------------------------------------------------' . PHP_EOL;
				echo $output;
				echo '============================================================================' . PHP_EOL;
			}

			return true;
		}

		return false;
	}

	/**
	 * Handles the condition when the debug buffer size exceeds its maximum limit or a force flush is requested.
	 *
	 * This function checks if the debug buffer has reached its maximum size or if a certain amount of time has passed
	 * since the last flush. It also checks for a force flush request. If any of these conditions are met, it logs the
	 * current buffer contents, clears the buffer, and resets the timer. This helps in managing memory usage and ensures
	 * that the debug information is written out periodically or as needed.
	 *
	 * @param int|null $line The line number in the code where this method is called, for logging purposes.
	 * @param string|null $method The name of the method that triggered the buffer size handling, for logging purposes.
	 * @param bool $force Optional parameter to force a buffer flush regardless of size or time conditions.
	 * @return bool True if the buffer was flushed, false otherwise.
	 */
	static function handleBufferSize( $line = null, $method = null, $force = false ) {
		if ( self::$buffer_flush_time === null ) {
			self::$buffer_flush_time = time();
		}

		//When buffer exceeds maximum size, write it to the log and clear it.
		//This will affect displaying large buffers though, but otherwise we may run out of memory.
		//If we detect PHP errors, buffer up to 10x the maximum size to try and capture those errors.

		if ( $force == true || ( self::$php_errors == 0 && self::$buffer_size >= self::$max_buffer_size ) || ( self::$php_errors > 0 && self::$buffer_size >= ( self::$max_buffer_size * 100 ) ) || ( time() - self::$buffer_flush_time ) > self::$max_buffer_time ) {
			self::$debug_buffer[] = [ 1, '[P'. str_pad( self::getCurrentPID(), 7, 0, STR_PAD_LEFT ) .'] [' . str_pad( self::getExecutionTime(), 5, 0, STR_PAD_LEFT ) . 'ms] [L' . str_pad( $line, 4, 0, STR_PAD_LEFT ) . ']: ' . $method . '(): Maximum debug buffer size/time of: ' . self::$max_buffer_size . ' lines / '. ( time() - self::$buffer_flush_time ) .'s reached. Writing out buffer before continuing... Buffer ID: ' . self::$buffer_id . PHP_EOL ];
			self::writeToLog();
			self::clearBuffer();
			self::$debug_buffer[] = [ 1, '[P'. str_pad( self::getCurrentPID(), 7, 0, STR_PAD_LEFT ) .'] [' . str_pad( self::getExecutionTime(), 5, 0, STR_PAD_LEFT ) . 'ms] [L' . str_pad( $line, 4, 0, STR_PAD_LEFT ) . ']: ' . $method . '(): Continuing debug output from Buffer ID: ' . self::$buffer_id . PHP_EOL ];

			self::$buffer_flush_time = time();
			return true;
		}

		return false;
	}

	/**
	 * Clears the debug buffer and resets its size.
	 *
	 * This function is responsible for resetting the debug buffer, which is used to store
	 * debug messages during script execution. It sets the buffer to null and the buffer size to zero,
	 * effectively clearing any stored messages and preparing the buffer for new messages.
	 *
	 * @return bool Always returns true to indicate the buffer was successfully cleared.
	 */
	static function clearBuffer() {
		self::$debug_buffer = null;
		self::$buffer_size = 0;

		return true;
	}
}

?>
