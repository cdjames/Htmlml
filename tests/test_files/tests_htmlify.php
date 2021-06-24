<?php
use \htmlify\Line;
use \Tests\TestSuite;
use \Tests\en_mode;
use function \Tests\Assertions\assert_equal;
require_once(dirname(__DIR__, 1)."/tests.php"); // test framework
require_once(dirname(__DIR__, 2)."/src/htmlify.php"); // functions to test

/*** string tests ***/
function test_line_constructor() : bool {
    $line = new Line("");
    return true;  
}

/*** run test suite from current directory 
 * TIP: put this code into its own file if using more than one
 * test file. Change __DIR__ if storing test files in different 
 * directory
 * ***/
$ts = new TestSuite($mode=en_mode::VRBS);
$assembled = $ts->assemble_suite_from_directory(__DIR__);

if($assembled) {
    $ts->run_suite();
    $ts->print_current_results();
}
?>