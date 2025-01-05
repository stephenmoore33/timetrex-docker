#!/bin/bash

#
# Requires composer modules, install with: "composer install" in this directory
#

export PHP_IDE_CONFIG="serverName=dev1.office.timetrex.com" # Matches PHPStorm Settings -> PHP -> Debug -> Servers, name.

#Use: export XDEBUG_REMOTE_HOST=10.7.5.9
# or: unset XDEBUG_REMOTE_HOST
if [[ -z "${XDEBUG_REMOTE_HOST}" ]]; then
		php_bin="/usr/bin/php"
else
		#PHP v7.x
		#php_bin="/usr/bin/php -d xdebug.remote_host=${XDEBUG_REMOTE_HOST} -d xdebug.remote_enable=on -d xdebug.remote_autostart=on -d xdebug.remote_connect_back"

		#PHP v8.0 - mode=debug,develop is required to show full stack traces. Just "debug" will not show a full trace.
		php_bin="/usr/bin/php -d xdebug.client_host=${XDEBUG_REMOTE_HOST} -d xdebug.mode=debug,develop -d xdebug.start_with_request=yes -d xdebug.discover_client_host"
fi

#These can't use ../vendor/bin/ versions of the binaries, as those are symlinks and aren't deployed by PHPStorm.
paratest_bin=../vendor/brianium/paratest/bin/paratest
phpunit_bin=../vendor/phpunit/phpunit/phpunit

if [ "$1" == "-v" ] ; then
	#Being called from itself, use quiet mode.
	echo -n "Running: $@ :: ";

	#Capture output to a variable so we show it all if a unit test fails.
	#Always stop on failure in this mode so gitlab pipelines are handled properly.
	PHPUNIT_OUTPUT=$($php_bin $phpunit_bin --configuration config.xml --stop-on-failure $@)
	#Capture the exit status of PHPUNIT and make sure we return that.
	exit_code=${PIPESTATUS[0]};

	if [ $exit_code != 0 ] ; then
		#Unit test failed, show all output
		echo -e "$PHPUNIT_OUTPUT";
	else
		#Unit test succeeded, show summary output
		echo -e "$PHPUNIT_OUTPUT" | tail -n 3 | tr -s "\n" | tr "\n" " "
	fi

	echo ""
	exit $exit_code;
elif [ "$1" == "--coverage" ] ; then
    #Example: ./run.sh --coverage
    # Then load: http://localhost/trunk/unit_tests/coverage
    export XDEBUG_MODE=coverage
    # Run tests with code coverage
    if [ "$2" == "-p1" ] ; then
        # Run a single test with coverage
        $php_bin $phpunit_bin --configuration config.xml --coverage-html coverage ${@:3}
    else
        # Run all tests in parallel with coverage
        echo "Running tests in parallel with code coverage..."
        $paratest_bin --configuration config.xml -f -p8 --max-batch-size=1 --coverage-html coverage --testsuite Default ${@:2}
    fi
elif [ "$1" == "-p1" ] ; then
	# **NOTE** Use this (-p1) when debugger is on.
	# Don't stop on failure when running a single test.
	# Strip the 1st argument ("-p1") when passing the other arguments to phpunit.
	$php_bin $phpunit_bin --configuration config.xml --coverage-html coverage ${@:2}
else
	echo "Running tests in parallel..."

	#Paratest doesn't currently support defaultTestSuite attribute in config.xml, so we have to pass it in here. 06-Jul-2023.
	$paratest_bin --configuration config.xml -f -p8 --max-batch-size=1 --stop-on-failure --testsuite Default $@
fi