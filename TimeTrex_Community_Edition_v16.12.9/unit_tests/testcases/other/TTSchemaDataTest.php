<?php /** @noinspection PhpMissingDocCommentInspection */

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

class TTSchemaDataTest extends PHPUnit\Framework\TestCase {
	protected $company_id = null;
	protected $legal_entity_id = null;
	protected $user_id = null;
	protected $branch_id = null;
	protected $policy_ids = [];
	protected $pay_period_schedule_id = null;
	protected $pay_period_objs = null;
	protected $pay_stub_account_link_arr = null;

	public function setUp(): void {
		Debug::text( 'Running setUp(): ', __FILE__, __LINE__, __METHOD__, 10 );

		$tt_assistant = new TTAssistant(); //This loads the TTAssistant class and all its dependencies.
	}

	public function tearDown(): void {
		Debug::text( 'Running tearDown(): ', __FILE__, __LINE__, __METHOD__, 10 );
	}

	public function createDemoData() {
		global $dd;
		Debug::text( 'Running createDemoData(): ', __FILE__, __LINE__, __METHOD__, 10 );

		TTDate::setTimeZone( 'America/Vancouver', true ); //Due to being a singleton and PHPUnit resetting the state, always force the timezone to be set.

		$dd = new DemoData();
		$dd->setEnableQuickPunch( false ); //Helps prevent duplicate punch IDs and validation failures.
		$dd->setUserNamePostFix( '_' . uniqid( '', true ) ); //Needs to be super random to prevent conflicts and random failing tests.
		$this->company_id = $dd->createCompany();
		$this->legal_entity_id = $dd->createLegalEntity( $this->company_id, 10 );
		Debug::text( 'Company ID: ' . $this->company_id, __FILE__, __LINE__, __METHOD__, 10 );
		$this->assertTrue( TTUUID::isUUID( $this->company_id ) );

		//**NOTE** This must always be done so we can perform actions through the API.
		$dd->createPermissionGroups( $this->company_id, 40 ); //Administrator only.

		$dd->createCurrency( $this->company_id, 10 );

		$dd->createPayStubAccount( $this->company_id );
		$dd->createPayStubAccountLink( $this->company_id );

		$dd->createUserWageGroups( $this->company_id );

		$this->policy_ids['pay_formula_policy'][100] = $dd->createPayFormulaPolicy( $this->company_id, 100 ); //Reg 1.0x
		$this->policy_ids['pay_code'][100] = $dd->createPayCode( $this->company_id, 100, $this->policy_ids['pay_formula_policy'][100] ); //Regular

		$this->user_id = $dd->createUser( $this->company_id, $this->legal_entity_id, 100 );

		$this->assertTrue( TTUUID::isUUID( $this->company_id ) );
		$this->assertTrue( TTUUID::isUUID( $this->user_id ) );
	}

	//Validate that the schema data (TTS) is correct for each factory
	function testSchemaData() {
		global $global_class_map;

		//$tt_assistant = new TTAssistant();
		//$agent = new TTAIAgent();
		//$agent->setModel( 'gpt-3.5-turbo-16k' ); //Use GPT4 potentially for better results, but it's slower and more expensive.

		//Lowercase all factory names just to avoid any potential case issues, even though unlikely.
		$factories_to_skip = [
				'apifactory',
				'factory',
		];

		foreach ( $global_class_map as $class_name => $class_file ) {
			$factory_name_to_check = strtolower( $class_name );

			if ( class_exists( $class_name ) == true && !in_array( $factory_name_to_check, $factories_to_skip, true ) //Must check if class exists so pure edition unit tests still pass.
					&& str_ends_with( $factory_name_to_check, 'factory' ) && !str_contains( $factory_name_to_check, 'listfactory' ) ) {
				$factory = TTnew( $class_name );
				$this->assertTrue( $factory instanceof Factory, $class_name . ' is not a Factory' );

				$schema_data = $factory->getSchemaData();

				$factory_reference_data = [
						'options_columns'                    => Misc::trimSortPrefix( $factory->getOptions( 'columns' ) ),
						'variable_function_map'              => $factory->getVariableToFunctionMap(),
						'table_name'                         => $factory->getTable(),
						'database_columns'                   => $this->getDBColumns( $factory, $factory->getTable() ),

						//Getting function definition (written code) to potentially have AI review it / use without needing to copy paste for esch factory.
						'schema_data_function_defintioh'     => $this->getMethodDefinition( $factory, '_getSchemadata' ),
						'factory_options_function_defintioh' => $this->getMethodDefinition( $factory, '_getFactoryOptions' ),
				];

				//Currenty not having AI review the schema data, but it could be useful in the future.
				//Would be far too slow and expensive to run on every unit test run.
				//$result = $agent->runSinglePrompt( 'Prompt to analyze data:' . json_encode( $factory_reference_data ) );

				$ignore_columns = [
						'created_by_id',
						'updated_by_id',
						'deleted_by_id',
				];

				//Check that all columns match the actual database columns.
				$schema_columns_array = [];
				foreach ( $schema_data->getColumns() ?? [] as $ttscol ) {
					if ( in_array( $ttscol->getName(), $ignore_columns, true ) ) {
						continue;
					}

					if ( $ttscol->getIsSynthetic() ) { //Synthetic columns are not in that tables database fields.
						continue;
					}

					$schema_columns_array[$ttscol->getName()] = [
							'type' => $ttscol->getType(),
							'null' => $ttscol->getIsNull(),
					];
				}

				//Probably don't need to check count, as error messages from diff are more useful.
				//$this->assertCount( count( $factory_reference_data['database_columns'] ), $schema_columns_array, 'Factory: ' . $class_name . ' Number of columns does not match!' );

				$diff = array_diff_key( $factory_reference_data['database_columns'], $schema_columns_array );
				$this->assertTrue( empty( $diff ), 'Factory: ' . $class_name . ' These columns exist in database but not in schema: ' . implode( ', ', array_keys( $diff ) ) );
				$diff = array_diff_key( $schema_columns_array, $factory_reference_data['database_columns'] );
				$this->assertTrue( empty( $diff ), 'Factory: ' . $class_name . ' These columns exist in schema but not in database: ' . implode( ', ', array_keys( $diff ) ) );

				foreach ( $factory_reference_data['database_columns'] as $col_name => $col_data ) {
					if ( !isset( $schema_columns_array[$col_name] ) ) {
						$this->assertTrue( false, 'Column ' . $col_name . ' does not exist in schema for factory: ' . $class_name );
					}

					if ( in_array( $col_name, [ 'created_by', 'updated_by', 'deleted_by' ], true ) ) {
						//addCreatedAndUpdated() and addDeleted set the type as 'string' instead of 'uuid' so avoiding these for now as there is a difference in tts and data ase
						//Just checking they match database uuid type for now.
						$this->assertTrue( $col_data['type'] === 'uuid' && $col_data['null'] === $schema_data['null'], 'Factory: ' . $class_name . ' Column ' . $col_name . ' does not match. Database has type ' . $col_data['type'] . ' and null ' . $col_data['null'] . ', but schema has type ' . $schema_data['type'] . ' and null ' . $schema_data['null'] );
					} else {
						$schema_data = $schema_columns_array[$col_name];
						$this->assertTrue( $col_data['type'] === $schema_data['type'] && $col_data['null'] === $schema_data['null'], 'Factory: ' . $class_name . ' Column ' . $col_name . ' does not match. Database has type ' . $col_data['type'] . ' and null=' . $col_data['null'] . ', but schema has type ' . $schema_data['type'] . ' and null ' . $schema_data['null'] );
					}
				}
			} else {
				Debug::text( '  Skipping Class: ' . $class_name, __FILE__, __LINE__, __METHOD__, 10 );
			}
		}
	}

	function getMethodDefinition( Factory $factory, string $method_name ): ?string {
		try {
			if ( is_string( $method_name ) && method_exists( $factory, $method_name ) ) {
				$reflection_method = new ReflectionMethod( $factory, $method_name );

				// Check if the method is declared in the child class, don't want to get the parent class method.
				if ( $reflection_method->getDeclaringClass()->getName() !== get_class( $factory ) ) {
					return null;
				}

				$file_name = $reflection_method->getFileName();
				$start_line = $reflection_method->getStartLine();
				$end_line = $reflection_method->getEndLine();
				$length = $end_line - $start_line;

				$source = file( $file_name );
				return implode( "", array_slice( $source, $start_line - 1, $length + 1 ) );
			} else {
				return null;
			}
		} catch ( ReflectionException $e ) {
			return null;
		}
	}


	function getDBColumns( Factory $factory, string $table_name ): array {
		$parsed_db_columns = [];
		$columns = $factory->db->MetaColumns( $table_name );

		if ( is_iterable( $columns ) ) {
			foreach ( $columns as $column ) {
				//TODO: This can return "int2" "int4" etc. Do we only want "smallint" and "integer"? Look into this further.
				if ( $column->type === 'int2' ) {
					$column->type = 'smallint';
				} else if ( $column->type === 'int4' ) {
					$column->type = 'integer';
				} else if ( $column->type === 'int8' ) {
					$column->type = 'bigint';
				} else if ( $column->type === 'float4' ) {
					$column->type = 'float';
				} else if ( $column->type === 'float8' ) {
					$column->type = 'float';
				}

				$parsed_db_columns[$column->name] = [ 'type' => $column->type, 'null' => !$column->not_null ];
			}
		}

		return $parsed_db_columns;
	}
}

?>