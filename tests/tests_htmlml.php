<?php
use \htmlml\Line;
use \htmlml\Htmlml;
use \KissTests\TestSuite;
use \KissTests\en_mode;
use function \KissTests\Assertions\assert_equal;
use function \KissTests\Assertions\assert_exception;
require_once(dirname(__DIR__, 1)."/submodules/KissTests/kiss_tests.php"); // test framework
require_once(dirname(__DIR__, 1)."/src/htmlml.php"); // functions to test

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

    return assert_exception([$line, "htmlml\Line::_processAttribute"], dirname(__DIR__, 1) . "/src/htmlml.php", ["ref0"], "bad attribute");
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
    $html = $line->getHtml();

    $success_html = "<a id='mya' class='nodrum yermom' href='#' data-src='mysong'>This is the text</a>";
    return assert_equal(implode("", $html), $success_html);
}

function test_line_custom_delim_success() : bool {
    $line = new Line("a .nodrum .yermom #mya href=# data-src=mysong txt=This is the text", "txt=");
    $html = $line->getHtml();

    $success_html = "<a id='mya' class='nodrum yermom' href='#' data-src='mysong'>This is the text</a>";
    return assert_equal(implode("", $html), $success_html);
}

function test_line__createHtml_embedded_tags_success() : bool {
    $line = new Line("h2 t=It seems to have worked. <i t=your file> should now be at < a .cool href=google.com t=the url>");
    $html = $line->getHtml();

    $success_html = "<h2>It seems to have worked. <i>your file</i> should now be at <a class='cool' href='google.com'>the url</a></h2>";
    return assert_equal(implode("", $html), $success_html);
}

function test_line__createHtml_embedded_tags_success2() : bool {
    $line = new Line("h2 t=It seems to have worked. <i t=your <b t=file>> should now be at <a .cool href=google.com t=the url>");
    $html = $line->getHtml();

    $success_html = "<h2>It seems to have worked. <i>your <b>file</b></i> should now be at <a class='cool' href='google.com'>the url</a></h2>";
    return assert_equal(implode("", $html), $success_html);
}

function test_htmlify_constructor() : bool {
    $htmlml = new Htmlml("text");
    
    return true;
}

function test_htmlify_success() : bool {
    $raw_text = 
    "
div .mydiv
 p t=some text
div #another";
    $htmlml = new Htmlml($raw_text);
    $html = $htmlml->getHtml();
    $success_html = "<div class='mydiv'><p>some text</p></div><div id='another'></div>";
    return assert_equal($html, $success_html);
}

function test_htmlify_default_delims_success() : bool {
    $raw_text = 
    "
div .mydiv
 p .super t=some text
div #another
 span
  a href=nc.collinjam.es t=nextcloud";
    $htmlml = new Htmlml($raw_text);
    $html = $htmlml->getHtml();
    $success_html = "<div class='mydiv'><p class='super'>some text</p></div><div id='another'><span><a href='nc.collinjam.es'>nextcloud</a></span></div>";
    return assert_equal($html, $success_html);
}

function test_htmlify_custom_delim_success() : bool {
    $raw_text = 
    "
div .mydiv,,,
 p .super t=some text,,,
div #another t=<span t=my span>"; //works but not recommended style
    $htmlml = new Htmlml($raw_text, ",,,");
    $html = $htmlml->getHtml();
    $success_html = "<div class='mydiv'><p class='super'>some text</p></div><div id='another'><span>my span</span></div>";
    return assert_equal($html, $success_html);
}

function test_htmlify_custom_text_delim_success() : bool {
    $raw_text = 
    "
div .mydiv
 p .super tx=some text
div #another
 span
  a href=nc.collinjam.es tx=nextcloud";
    $htmlml = new Htmlml($raw_text, Htmlml::LINE_DELIM, 'tx=');
    $html = $htmlml->getHtml();
    $success_html = "<div class='mydiv'><p class='super'>some text</p></div><div id='another'><span><a href='nc.collinjam.es'>nextcloud</a></span></div>";
    return assert_equal($html, $success_html);
}

function test_htmlify_custom_line_and_text_delim_success() : bool {
    $raw_text = 
    "
div .mydiv...
 p .super tx=some text...
div #another...
 span...
  a href=nc.collinjam.es tx=nextcloud";
    $htmlml = new Htmlml($raw_text, '...', 'tx=');
    $html = $htmlml->getHtml();
    $success_html = "<div class='mydiv'><p class='super'>some text</p></div><div id='another'><span><a href='nc.collinjam.es'>nextcloud</a></span></div>";
    return assert_equal($html, $success_html);
}

function test_htmlify_tabs_success() : bool {
    $raw_text = 
    "
    div .mydiv
        p .super t=some text
    div #another
        span
            a href=nc.collinjam.es t=nextcloud";
    $htmlml = new Htmlml($raw_text);
    $html = $htmlml->getHtml();
    $success_html = "<div class='mydiv'><p class='super'>some text</p></div><div id='another'><span><a href='nc.collinjam.es'>nextcloud</a></span></div>";
    return assert_equal($html, $success_html);
}

function test_htmlify_default_urls_success() : bool {
    $raw_text = 
    "
div .mydiv
 p .super t=some text
div #another
 span
  a href=https://nc.collinjam.es/nc t=nextcloud/nc";
    $htmlml = new Htmlml($raw_text);
    $html = $htmlml->getHtml();
    $success_html = "<div class='mydiv'><p class='super'>some text</p></div><div id='another'><span><a href='https://nc.collinjam.es/nc'>nextcloud/nc</a></span></div>";
    return assert_equal($html, $success_html);
}

function test_htmlify_equal_levels_success() : bool {
    $raw_text = "
    div #success_wrapper
     div #success
      h2 t=It seems to have worked. <i t=your files> should now be at <a href=https://yourfiles.com t=your files.>";
    $raw_text .= "
      h3 t=An email has been sent to your mom.";
    $htmlml = new Htmlml($raw_text);
    $html = $htmlml->getHtml();
    $success_html = "<div id='success_wrapper'><div id='success'><h2>It seems to have worked. <i>your files</i> should now be at <a href='https://yourfiles.com'>your files.</a></h2><h3>An email has been sent to your mom.</h3></div></div>";
    return assert_equal($html, $success_html);
}

function test_htmlify_equal_levels_success2() : bool {
    $raw_text = "
    div #success_wrapper
     div #success
      h2 t=It seems to have worked. <i t=your files> should now be at <a href=https://yourfiles.com t=your files.>
      h3 t=An email has been sent to your mom.
      h4 t=hi.";
    $htmlml = new Htmlml($raw_text);
    $html = $htmlml->getHtml();
    $success_html = "<div id='success_wrapper'><div id='success'><h2>It seems to have worked. <i>your files</i> should now be at <a href='https://yourfiles.com'>your files.</a></h2><h3>An email has been sent to your mom.</h3><h4>hi.</h4></div></div>";
    return assert_equal($html, $success_html);
}

function test_htmlify_equal_to_greater_success() : bool {
    $raw_text = "
    div #success_wrapper
     div #success
      h2 t=It seems to have worked. <i t=your files> should now be at <a href=https://yourfiles.com t=your files.>
      h3 t=An email has been sent to your mom.
      h4 t=hi.
       span t=hi.";
    $htmlml = new Htmlml($raw_text);
    $html = $htmlml->getHtml();
    $success_html = "<div id='success_wrapper'><div id='success'><h2>It seems to have worked. <i>your files</i> should now be at <a href='https://yourfiles.com'>your files.</a></h2><h3>An email has been sent to your mom.</h3><h4>hi.<span>hi.</span></h4></div></div>";
    return assert_equal($html, $success_html);
}

function test_htmlify_equal_to_greater_to_lesser_success() : bool {
    $raw_text = "
    div #success_wrapper
     div #success
      h2 t=It seems to have worked. <i t=your files> should now be at <a href=https://yourfiles.com t=your files.>
      h3 t=An email has been sent to your mom.
      h4 t=hi.
       span t=hi.
      div";
    $htmlml = new Htmlml($raw_text);
    $html = $htmlml->getHtml();
    $success_html = "<div id='success_wrapper'><div id='success'><h2>It seems to have worked. <i>your files</i> should now be at <a href='https://yourfiles.com'>your files.</a></h2><h3>An email has been sent to your mom.</h3><h4>hi.<span>hi.</span></h4><div></div></div></div>";
    return assert_equal($html, $success_html);
}

function test_htmlify_equal_to_greater_to_lesser2_success() : bool {
    $raw_text = "
    div #success_wrapper
     div #success
      h2 t=It seems to have worked. <i t=your files> should now be at <a href=https://yourfiles.com t=your files.>
      h3 t=An email has been sent to your mom.
      h4 t=hi.
       span t=hi.
     div";
    $htmlml = new Htmlml($raw_text);
    $html = $htmlml->getHtml();
    $success_html = "<div id='success_wrapper'><div id='success'><h2>It seems to have worked. <i>your files</i> should now be at <a href='https://yourfiles.com'>your files.</a></h2><h3>An email has been sent to your mom.</h3><h4>hi.<span>hi.</span></h4></div><div></div></div>";
    return assert_equal($html, $success_html);
}

function test_htmlify_equal_to_greater_to_lesser3_success() : bool {
    $raw_text = "
    div #success_wrapper
     div #success
      h2 t=It seems to have worked. <i t=your files> should now be at <a href=https://yourfiles.com t=your files.>
      h3 t=An email has been sent to your mom.
      h4 t=hi.
       span t=hi.
    div";
    $htmlml = new Htmlml($raw_text);
    $html = $htmlml->getHtml();
    $success_html = "<div id='success_wrapper'><div id='success'><h2>It seems to have worked. <i>your files</i> should now be at <a href='https://yourfiles.com'>your files.</a></h2><h3>An email has been sent to your mom.</h3><h4>hi.<span>hi.</span></h4></div></div><div></div>";
    return assert_equal($html, $success_html);
}

function test_htmlify_all_equal_success() : bool {
    $raw_text = "
      h2 t=It seems to have worked. <i t=your files> should now be at <a href=https://yourfiles.com t=your files.>
      h3 t=An email has been sent to your mom.
      h4 t=hi.";
    $htmlml = new Htmlml($raw_text);
    $html = $htmlml->getHtml();
    $success_html = "<h2>It seems to have worked. <i>your files</i> should now be at <a href='https://yourfiles.com'>your files.</a></h2><h3>An email has been sent to your mom.</h3><h4>hi.</h4>";
    return assert_equal($html, $success_html);
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