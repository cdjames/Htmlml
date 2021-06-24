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

function test_line_processAttribute_exception() : bool {
    $line = new Line("");
    return assert_exception(dirname(__DIR__, 2)."/src/htmlify.php", 
                                                '\htmlify\Line::_processAttribute', 
                                                array("ref0"));
}

function test_line_processLine_success() : bool {
    $line = new Line(" h2  t=Something went wrong!");
    $level = $line->_processLine();
    return assert_equal($level, 1);
}

function test_line_processLine_success2() : bool {
    $line = new Line("  a .nodrum href=# data-src=mysong");
    $level = $line->_processLine();
    return assert_equal($level, 2);
}

function test_line_processLine_success3() : bool {
    $line = new Line("a .nodrum #mya href=# data-src=mysong t=This is the text");
    $level = $line->_processLine();
    return assert_equal($level, 0);
}

function test_line__createHtml_success() : bool {
    $line = new Line("a .nodrum .yermom #mya href=# data-src=mysong t=This is the text");
    $level = $line->_processLine();
    $line->_createHtml();
    $html = $line->getHtml();

    $success_html = "<a id='mya' class='nodrum yermom' href='#' data-src='mysong'>This is the text</a>";
    return assert_equal(implode("", $html), $success_html);
}

function test_line__createHtml_embedded_tags_success() : bool {
    $line = new Line("h2 t=It seems to have worked. <i t=your file> should now be at < a .cool href=google.com t=the url>");
    $level = $line->_processLine();
    $line->_createHtml();
    $html = $line->getHtml();

    $success_html = "<h2>It seems to have worked. <i>your file</i> should now be at <a class='cool' href='google.com'>the url</a></h2>";
    return assert_equal(implode("", $html), $success_html);
}

function test_line__createHtml_embedded_tags_success2() : bool {
    $line = new Line("h2 t=It seems to have worked. <i t=your <b t=file>> should now be at < a .cool href=google.com t=the url>");
    $level = $line->_processLine();
    $line->_createHtml();
    $html = $line->getHtml();

    $success_html = "<h2>It seems to have worked. <i>your <b>file</b></i> should now be at <a class='cool' href='google.com'>the url</a></h2>";
    return assert_equal(implode("", $html), $success_html);
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