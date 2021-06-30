<?php
namespace htmlml;

const SPACE = " ";
const PERIOD = ".";
const HASH = "#";
const EQUALS = "=";
const SQUOTE = "'";
const START_TAG_OPEN = "<";
const END_TAG_OPEN = "</";
const TAG_CLOSE = ">";
const TESTING = 0;

function log_print_r($item) {
    if (TESTING === 1) {
        print_r($item);
    }
}

function log($string, $newline = true) {
    if (TESTING === 1) {
        echo $string;
        if ($newline) {
            echo "\n";
        }
    }
}

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
    private $text_delim;
    const TXT_DELIM = "t=";
    const SPACE_PTRN = "/^\s+/";
    const EMBEDDED_TAG_PTRN = "^<\s*?.*?>+^";

    public function __construct(string $raw_line, string $text_delim = self::TXT_DELIM)
    {
        $this->raw_line = rtrim($raw_line); // no need to keep spaces at the end
        $this->level = 0;
        $this->tag = "";
        $this->classes = [];
        $this->id = "";
        $this->attributes = [];
        $this->text = "";
        $this->html = [];
        $this->text_delim = $text_delim;
        $this->_processLine();
    }

    public function _createHtml() { // kept public for tests
        // assemble html opening starting from tag
        $this->html[0] = START_TAG_OPEN.$this->tag;

        if (strlen($this->id) > 0) {
            $this->html[0] .= SPACE."id".EQUALS.SQUOTE.$this->id.SQUOTE;
        } 
        
        if (count($this->classes) > 0) {
            $this->html[0] .= SPACE."class".EQUALS.SQUOTE;

            foreach ($this->classes as $key => $class) {
                if ($key != 0) {
                    $this->html[0] .= SPACE;
                }
                $this->html[0] .= $class;
            }

            $this->html[0] .= SQUOTE;
        }
        
        if (count($this->attributes) > 0) {
            $this->html[0] .= SPACE;

            foreach ($this->attributes as $key => $attr) {
                if ($key != 0) {
                    $this->html[0] .= SPACE;
                }
                $this->html[0] .= $attr;
            }
        }

        $this->html[0] .= TAG_CLOSE;

        if (strlen($this->text) > 0) {
            $this->html[0] .= $this->text;
        } 

        $this->html[1] = END_TAG_OPEN.$this->tag.TAG_CLOSE;
    }

    public function _processAttribute(string $attr) : string { // kept public for tests
        // surround text after '=' with single quotes
        $parts = explode(EQUALS, $attr, 2);
        if ($parts === false || count($parts) < 2) {
            // not a valid attribute
            throw new \Exception("bad attribute");           
        }
        
        return $parts[0].EQUALS.SQUOTE.$parts[1].SQUOTE;
    }

    public function _processLine() : int { // kept public for tests
        /*  a.nodrum href=# data-src=mysong 'mysong' */
        // look for text by splitting on "text="
        $txt_parts = explode($this->text_delim, $this->raw_line, 2);
        // log_print_r($txt_parts);
        if (count($txt_parts) > 1) {
            $this->_processText($txt_parts[1]);
        }

        $line = rtrim($txt_parts[0]); // get rid of trailing spaces           
        
        // get leading spaces to determine level
        $starts_w_spaces = preg_match(self::SPACE_PTRN, $line, $spaces);
        if ($starts_w_spaces > 0) {
            $this->level = strlen($spaces[0]);
        }

        // remove leading spaces, no longer needed
        $line = ltrim($line);

        /* a .nodrum #mya href=# data-src=mysong */
        // split on spaces to get parts
        $all_parts = explode(SPACE, $line);
        // log_print_r($all_parts);

        // first part is tag
        $this->tag = array_shift($all_parts);

        // other parts are classes, ids, and attributes; wrap attribute values in ''
        foreach ($all_parts as $token) {
            $token = trim($token);
            if (strlen($token)) {
                if (strpos($token, PERIOD) === 0) {
                    $this->classes []= ltrim($token, PERIOD);
                } else if (strpos($token, HASH) === 0) {
                    $this->id = ltrim($token, HASH);
                } else {
                    $this->attributes []= $this->_processAttribute($token);
                }
            }
        }
        // log("id: ");
        // log_print_r($this->id);
        // log("attributes: ");
        // log_print_r($this->attributes);

        // return the level
        return $this->level;
    }

    public function _processText($txt_parts) {
        // look inside for embedded items
        /* It seems to have worked. <i t=your file> should now be at <a .cool href=google.com t=the url> */
        $full_line = $txt_parts;

        $has_sub_items = preg_match_all(self::EMBEDDED_TAG_PTRN, $full_line, $matches);
        if ($has_sub_items > 0) {
            foreach ($matches[0] as $new_line) {
                // remove the tagging
                $trimmed = ltrim($new_line, "< "); // trim first chevron and spaces from left side
                $trimmed = rtrim($trimmed); // trim spaces from right side
                $trimmed = substr($trimmed, 0, -1); // pop off one chevron from the right side

                // recursively process the embedded line
                $line = new Line($trimmed);
                $html = $line->getHtml();

                // find the markup and replace it with html
                $full_line = preg_replace("^".$new_line."^", implode("", $html), $full_line);
            }
        }
        
        $this->text = $full_line;
    }

    public function getHtml() : array {
        if (!count($this->html)) {
            $this->_createHtml();
        }
        return $this->html;
    }

    public function getLevel() : int {
        return $this->level;
    }
}

class Htmlml
{
    private $raw_block;
    private $html;
    private $level;
    private $stack;
    private $delim;
    private $txt_delim;
    const LINE_DELIM = "\n";

    public function __construct(string $raw_block, 
                                string $delim = self::LINE_DELIM,
                                string $txt_delim = Line::TXT_DELIM) {
        $this->raw_block = $raw_block;
        $this->html = "";
        $this->level = 0;
        $this->stack = [];
        $this->delim = $delim;
        $this->txt_delim = $txt_delim;
    }

    public function _assembleHtml() {
        /* Assemble html, starting from first element:
         *            $html                             $closing_tags
         * div   ->   <div>                             </div>
         *  div1  ->  <div><div1>                       </div>, </div1>
         *  div2  ->  <div><div1></div1><div2>          </div>, </div2>
         *   div3 ->  <div><div1></div1><div2><div3>    </div>, </div2>, </div3>
         * div4   ->  <div><div1></div1><div2><div3></div3></div2></div><div4></div4>
         * 
         */
        $closing_tags = [];
        $html = "";
        $level = -1;
        $lowest = -1;
        while (count($this->stack) > 0) {
            // Pop off element
            $top = array_pop($this->stack);
            $top_level = $top->getLevel();
            $top_html = $top->getHtml();

            if ($top_level < $level) {
                $num_levels = $level - $top_level;
                // add num_levels+1 closing tags 
                // (...<ul><li>) </li> </ul>
                for ($i=$num_levels; $i >= 0 ; $i--) { 
                    // grab top tag on stack
                    $top_closing_tag = array_pop($closing_tags);
                    // append
                    $html .= $top_closing_tag;
                }
                // concatanate: (...<ul><li>) </li> </ul> <ul>
                $html .= $top_html[0];
                // log("wrap: ".$html);
            } elseif ($top_level == $level) {
                // grab top tag on stack
                $top_closing_tag = array_pop($closing_tags);
                // concatanate: (...<li>) </li> <li>
                $html .= $top_closing_tag . $top_html[0];
            } else { // greater than
                // concatanate: (...<li>) <li>
                $html .= $top_html[0];
            }
            $closing_tags []= $top_html[1];
            $level = $top_level;
        }   

        // append the remaining closing tags
        while (count($closing_tags) > 0) {
            // grab top tag on stack
            $top_closing_tag = array_pop($closing_tags);
            $html .= $top_closing_tag;
        }

        $this->html .= $html;
        // log("assemble:");
        // log_print_r($this->html);
    }

    public function _processBlock() {
        $current_level = -1;
        // separate into separate lines
        $lines = explode($this->delim, $this->raw_block);
        // log_print_r($lines);
        // check size and operate on each line
        foreach ($lines as $text) {
            $text = trim($text, "\n");
            if (!strlen($text)) {
                continue; // no content, so skip to next line
            }
            // log($text);
            // create a Line object
            $line = new Line($text, $this->txt_delim);
            // log_print_r($line);

            array_unshift($this->stack, $line);
        }

        // if done and still item on stack, assemble the rest
        if (count($this->stack)) {
            $this->_assembleHtml();
        }
        // log("html:");
        // log_print_r($this->html);
    }

    public function getHtml() : string {
        if (!strlen($this->html)) {
            $this->_processBlock();
        }
        return $this->html;
    }
}

$no_path_ext = "mysong";
$trackname = "mysong";
$error_files = "mysong and yoursong";

$var = "
li.added ref=0 >
 a.nodrum href=# data-src=$no_path_ext text=$trackname";

$var2 = "
div#success_wrapper >
 div#error >
  h2 text=Something went wrong! ".$error_files." were not uploaded because they were too large! Please try again or contact the server admin.";

?>