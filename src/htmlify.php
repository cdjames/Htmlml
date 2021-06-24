<?php
namespace htmlify;

class Line
{
    private $raw_line;
    private $level;
    private $tag;
    private $classes;
    private $id;
    private $attributes;
    private $text;
    private $html;

    public function __construct(string $raw_line)
    {
        $this->raw_line = $raw_line;
        $this->level = 0;
        $this->tag = "";
        $this->classes = [];
        $this->id = "";
        $this->attributes = [];
        $this->text = "";
        $this->html = [2];
    }

    private function createHtml() {
        // assemble html opening starting from tag
        $html[0] = "<$this->tag";

        if ($this->id != "") {
            $html[0] .= " id='$this->id'";
        } 
        
        if (count($this->classes)) {
            $html[0] .= " class='";

            foreach ($this->classes as $key => $class) {
                if ($key != 0) {
                    $html[0] .= " ";
                }
                $html[0] .= $class;
            }

            $html[0] .= "'";
        }
        
        if (count($this->attributes)) {
            foreach ($this->attributes as $attr) {
                if ($key != 0) {
                    $html[0] .= " ";
                }
                $html[0] .= $attr;
            }
        }

        if ($this->text != "") {
            $html[0] .= $this->text;
        } 

        $html[0] .= ">";

        $html[1] = "</$this->tag>";
    }

    private function processAttribute(string $attr) : string {
        // surround text after '=' with single quotes
        $parts = explode("=", $attr, 1);
        if ($parts === false || !count($parts)) {
            // not a valid attribute
            throw new \Exception("bad attribute", 1, __FILE__, __LINE__);           
        }
        
        return $parts[0]."='".$parts[1]."'";
    }

    private function processLine() : int {
        /*  a.nodrum href=# data-src=mysong 'mysong' */

        // look for text by splitting on "'"

        // get leading spaces to determine level

        // remove leading spaces

        /* a.nodrum href=# data-src=mysong */
        // split on spaces to get parts

        // first part is tag plus classes and id

        // other parts are attributes; wrap attribute values in ''

        // return the level
    }

    public function getHtml() : array {
        return $html;
    }
}

// class Htmlify
// {
//     private $num_passed;
//     private $num_runs;
//     private $func;
//     private $args;

//     public function __construct(string $function, Array $args=[])
//     {
//         $this->num_passed = 0;
//         $this->num_runs = 0;
//         $this->func = $function;
//         $this->args = $args;
//     }
// }

function htmlify(string $markup)
{
    // split on line delimiter '>'

    // check size and operate on each line

        // look for text by splitting on "'"

        // get leading spaces to determine level
}

$no_path_ext = "mysong";
$trackname = "mysong";
$error_files = "mysong and yoursong";

$var = "
li.added ref=0 >
 a.nodrum href=# data-src=$no_path_ext '$trackname'";

$var2 = "
div#success_wrapper >
 div#error >
  h2 'Something went wrong! ".$error_files." were not uploaded because they were too large! Please try again or contact the server admin.'";
?>