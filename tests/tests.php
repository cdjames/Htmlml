<?php

namespace Tests\Assertions {
    function turn_on_assertions() {
        assert_options(ASSERT_ACTIVE, 1);
        assert_options(ASSERT_WARNING, 1);
        assert_options(ASSERT_QUIET_EVAL, 1);
        assert_options(ASSERT_BAIL, 1);
    }

    function assert_exception(string $function, Array $args=[], string $e_msg="") : bool {
        $STR_PREAMBLE = "[".__FUNCTION__."] ";
        
        $result = 0;
        try {
            call_user_func_array($function, $args);
        } catch (\Exception $e) {
            if($e_msg == "") {
                $result = 1;
            } else {
                // echo "error: ".$e->getMessage()."\n";
                if($e->getMessage() == $e_msg) {
                    $result = 1;
                }
            }
        } finally {
            return assert($result===1, $STR_PREAMBLE."$function failed with args: ".implode(", ", $args));
        }
    }
    
    function assert_equal($left, $right) : bool {
        $STR_PREAMBLE = "[".__FUNCTION__."] ";
        
        return assert($left === $right, $STR_PREAMBLE."'$left' not equal to '$right'");
    }
    
    function assert_unequal($left, $right) : bool {
        $STR_PREAMBLE = "[".__FUNCTION__."] ";
        
        return assert($left !== $right, $STR_PREAMBLE."'$left' equal to '$right'");
    }
    /*** test ***/
    // require_once(dirname(__DIR__)."/src/php/helpers.php");
    // \Tests\Assertions\assert_exception('\Helpers\Strings\add_values', array('/(\.mp3)$/', '---', "hi"));
    // \Tests\Assertions\assert_equal(\Helpers\Strings\add_values('/(\.mp3)$/', '---', "hi.mp3"), "hi---.mp3");
    // \Tests\Assertions\assert_unequal(\Helpers\Strings\add_values('/(\.mp3)$/', '---', "hi.mp3"), "hi--.mp3");

}

namespace Tests {
    abstract class en_mode { // an approximation of enumerations in php < 8
        const NRML = 0;
        const VRBS = 1;
    }

    require_once("helpers.php");

    interface TestTmplt 
    {
        public function run_test() : bool;
        public function print_current_results();
    }

    interface TestSuiteTmplt 
    {
        public function assemble_suite_from_directory(string $path) : bool;
        public function run_suite() : bool;
        public function print_current_results();
    }

    class Test implements TestTmplt
    {
        private $num_passed;
        private $num_runs;
        private $func;
        private $args;

        public function __construct(string $function, Array $args=[])
        {
            $this->num_passed = 0;
            $this->num_runs = 0;
            $this->func = $function;
            $this->args = $args;
        }
        
        public function run_test() : bool {
            $result = false;
            $this->num_runs += 1;
            try {
                $result = call_user_func_array($this->func, $this->args);
            } catch (\Exception $e) {
                echo "$e";
            } finally {
                $this->num_passed += (int)$result;
                return $result;
            }
        }

        public function print_current_results() {
            echo $this->num_passed." test(s) passed out of ".$this->num_runs."\n";
        }

        public function get_func_name() {
            return $this->func;
        }
    }
    
    /*** test ***/
    // function test_sample() : bool {
    //     return \Tests\Assertions\assert_unequal(\Helpers\Strings\add_values('/(\.mp3)$/', '---', "hi.mp3"), "hi--.mp3");
    // }

    // require_once('../src/php/helpers.php');
    // $test = new Test('\Tests\test_sample');
    // $test->run_test();
    // $test->print_current_results();

    class TestSuite implements TestSuiteTmplt 
    {
        private $filekey;
        private $func_key;
        private $delim;
        private $tests;
        private $num_passed;
        private $num_runs;
        private $func_grep;
        private $test_functions;
        private $mode;
        const FUNC_IDX = 1;

        public function __construct($mode = en_mode::NRML,
                                    string $filekey = "tests", 
                                    string $func_key = "test",
                                    string $delim = "_"
                                    )
        {
            $this->filekey = $filekey;
            $this->func_key = $func_key;
            $this->delim = $delim;
            $this->tests = [];
            $this->num_passed = 0;
            $this->num_runs = 0;
            $this->func_grep = "/\s*(?<!\/\/\s)function\s+($this->func_key$this->delim.*)\s*?\(\s*?\)\s*?:\s*?bool/";
            $this->test_functions = [];
            $this->mode = $mode;
            /* Regex explanation
             * /s*  -- 0 or more spaces
             * (?<!\/\/\s) -- not preceded by '// '
             * function\s+ -- 'function' followed by 1 or more spaces
             * ($this->func_key$this->delim.*) -- 'test_' plus 0 or more characters
             * \s*? -- 0 or more spaces
             * \(\s*?\) -- '()' with 0 or more spaces in between
             * \s*?:\s*?bool -- ' : bool' with 0 or more spaces
            */
        }

        private function _parse_test_file(string $contents) : bool {
            $result = false;

            $grep_result = preg_match_all($this->func_grep, $contents, $matches, PREG_PATTERN_ORDER);
            if($grep_result === false) {
                throw new \Exception("grep error");
            }

            $func_array = $matches[self::FUNC_IDX];
            if(count($func_array)) {
                $result = true;
                $this->test_functions = $func_array;
                // print_r($this->test_functions);
            }
            
            return $result;
        }

        public function assemble_suite_from_directory(string $path) : bool {
            $result = false;
            if(!is_dir($path)){ // make sure the directory actually exists
                throw new \Exception("$dir is not a directory");
            }
            
            $file_pattern = \Helpers\Files\pathjoin(array($path, "$this->filekey*.php"));
            $all_files = glob($file_pattern);
            if(count($all_files) == 0 || $all_files === false) {
                throw new \Exception("no php files beginning with '$this->filekey' found");
            }

            // print_r($all_files);

            foreach ($all_files as $file) {
                $f_handle = fopen($file, 'r');
                if($f_handle === false) {
                    throw new \Exception("no such file: '$file'");
                }

                $f_contents = fread($f_handle, filesize($file));
                if($f_contents === false) {
                    fclose($f_handle);
                    throw new \Exception("error reading '$f_contents'");
                }

                // echo "file contents:\n$f_contents\n";

                $tests_found = $this->_parse_test_file($f_contents);
                if($tests_found) {
                    $result = true;
                    // require the containing file
                    require_once($file);
                    // create the tests
                    foreach ($this->test_functions as $function) {
                        $this->tests[]=new Test($function);
                    }
                    // print_r($this->tests);
                }

                fclose($f_handle);

            }
            return $result;
        }

        public function run_suite() : bool {
            $result = false;

            if (count($this->tests)) {
                $result = true;
                foreach ($this->tests as $key => $test) {
                    if($this->mode == en_mode::VRBS) {
                        echo "***** running ".$test->get_func_name()." *****\n";
                    }
                    $test_rslt = $test->run_test();
                    $this->num_runs += 1;
                    $this->num_passed += (int)$test_rslt;
                }
            }

            return $result;
        }

        public function print_current_results() {
            echo $this->num_passed." test(s) passed out of ".$this->num_runs."\n";
        }
    }

    /*** tests ***/
    // $ts = new TestSuite();
    // $assembled = $ts->assemble_suite_from_directory(__DIR__.'/test_files');

    // if($assembled) {
    //     $ts->run_suite();
    //     $ts->print_current_results();
    // }
}
?>