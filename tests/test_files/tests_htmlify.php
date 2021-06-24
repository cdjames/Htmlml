<?php
use \htmlify\Line;
use \Tests\TestSuite;
use \Tests\en_mode;
use function \Tests\Assertions\assert_equal;
use function \Tests\Assertions\assert_exception;
require_once(dirname(__DIR__, 1)."/tests.php"); // test framework
require_once(dirname(__DIR__, 2)."/src/htmlify.php"); // functions to test

/*** string tests ***/
function test_line_constructor() : bool {
    $line = new Line("");
    return true;  
}

function test_line_processAttribute_success() : bool {
    $line = new Line("");
    $attr = $line->_processAttribute("ref=0");
    return assert_equal($attr, "ref='0'");
}

function test_line_processAttribute_success2() : bool {
    $line = new Line("");
    $attr = $line->_processAttribute("data-src=mysong");
    return assert_equal($attr, "data-src='mysong'");
}

function test_line_processLine_success() : bool {
    $line = new Line("h2 'Something went wrong!'");
    $level = $line->_processLine();
    return assert_equal($level, 0);
}

function test_line_processAttribute_exception() : bool {
    $line = new Line("");
    return assert_exception(dirname(__DIR__, 2)."/src/htmlify.php", 
                                                '\htmlify\Line::_processAttribute', 
                                                array("ref0"));
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