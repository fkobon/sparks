<?php

class Version_Test extends Spark_Test_Case {

    function test_version() {
        $clines = $this->capture_buffer_lines(function($cli) {
            $cli->execute('version'); 
        });
        $this->assertEquals(array(SPARK_VERSION), $clines);
    }

    function test_sources() {
        $clines = $this->capture_buffer_lines(function($cli) {
            $cli->execute('sources');
        });
        $this->assertEquals($this->source_names, $clines);
    }

    function test_bad_command() {
        $clines = $this->capture_buffer_lines(function($cli) {
            $cli->execute('fake');
        });
        $this->assertEquals(array('[ ERROR ]  Uh-oh!', '[ ERROR ]  Unknown action: fake'), $clines);
    }

    function test_search_empty() {
        $clines = $this->capture_buffer_lines(function($cli) {
            $cli->execute('search', array('nothing_found_here'));
        });
        $this->assertEquals(array(), $clines);
    }

    function test_install() {
        ob_start();
        // Test install with a version specified
        $clines = $this->capture_buffer_lines(function($cli) {
            $cli->execute('install', array('-v1.0', 'example-spark'));
        });
        $success = (bool) (strpos(end($clines), '[ SPARK ]  Spark installed') === 0);
        SparkUtils::remove_full_directory(SPARK_PATH . '/example-spark');
        $this->assertEquals(true, $success);

        // Test install without a version specified
        $clines = $this->capture_buffer_lines(function($cli) {
            $cli->execute('install', array('example-spark'));
        });
        $success = (bool) (strpos(end($clines), '[ SPARK ]  Spark installed') === 0);
        SparkUtils::remove_full_directory(SPARK_PATH . '/example-spark');
        $this->assertEquals(true, $success);

        // Test install with invalid spark
        $clines = $this->capture_buffer_lines(function($cli) {
            $cli->execute('install', array('jjks7878erHjhsjdkksj'));
        });
        $success = (bool) (strpos(end($clines), '[ ERROR ]  Unable to find spark') === 0);
        SparkUtils::remove_full_directory(SPARK_PATH . '/example-spark');
        $this->assertEquals(true, $success);

        // Test install with invalid spark version
        $clines = $this->capture_buffer_lines(function($cli) {
            $cli->execute('install', array('v9.4', 'example-spark'));
        });
        $success = (bool) (strpos(reset($clines), '[ ERROR ]  Uh-oh!') === 0);
        SparkUtils::remove_full_directory(SPARK_PATH . '/example-spark');
        $this->assertEquals(true, $success);
        ob_end_clean();
    }

    function test_remove() {
        ob_start();
        // Test install with a version specified
        $this->cli->execute('install', array('-v1.0', 'example-spark')); // Spark needs installed first
        $clines = $this->capture_buffer_lines(function($cli) {
            $cli->execute('remove', array('-v1.0', 'example-spark'));
        });
        $success = (bool) (strpos(end($clines), '[ SPARK ]  Spark removed') === 0 && ! is_dir(SPARK_PATH.'/example-spark'));
        $this->assertEquals(true, $success);

        // Test install without a version or -f specified
        $this->cli->execute('install', array('-v1.0', 'example-spark')); // Spark needs installed first
        $clines = $this->capture_buffer_lines(function($cli) {
            $cli->execute('remove', array('example-spark'));
        });
        $success = (bool) (strpos(end($clines), '[ ERROR ]  Please specify') === 0 && is_dir(SPARK_PATH.'/example-spark'));
        $this->assertEquals(true, $success);

        // Test install with a -f specified
        $this->cli->execute('install', array('-v1.0', 'example-spark')); // Spark needs installed first
        $clines = $this->capture_buffer_lines(function($cli) {
            $cli->execute('remove', array('-f', 'example-spark'));
        });
        $success = (bool) (strpos(end($clines), '[ SPARK ]  Spark removed') === 0 && ! is_dir(SPARK_PATH.'/example-spark'));
        $this->assertEquals(true, $success);

        // Test install with a wrong version specified
        $this->cli->execute('install', array('-v1.0', 'example-spark')); // Spark needs installed first
        $clines = $this->capture_buffer_lines(function($cli) {
            $cli->execute('remove', array('-v33.4', 'example-spark'));
        });
        $success = (bool) (strpos(end($clines), '[ SPARK ]  Looks like that spark isn\'t installed') === 0 && is_dir(SPARK_PATH.'/example-spark'));
        $this->assertEquals(true, $success);
        ob_end_clean();
    }


}
