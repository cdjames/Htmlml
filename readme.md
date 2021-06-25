# Htmlify
Create html from markup

## Use case
When adding html in PHP, I want to type this:

```
div .mydiv
 p .super tx=some text
div #another
 span
  a href=google.com tx=google"
```

... instead of this:

```
<div class='mydiv'>
    <p class='super'>some text</p>
</div>
<div id='another'>
    <span>
        <a href='google.com'>google</a>
    </span>
</div>
```

## How to use
1. Create a variable with your markup:

```
$markup = 
"
div .mydiv
 p .super tx=some text
div #another
 span
  a href=nc.collinjam.es tx=nextcloud";
```

2. Create an object and get your html

```
$htmlified = new Htmlify($markup);
$html = $htmlified->getHtml();
```

## Markup format
### Basic line format
Italics is optional; () means replace with your content:
- tag *.(class)* *#(id)* *(attr)=(myvalue)* *tx=(mytext)*

#### Example

```
a #anchor .styled .upper name=anchor href=github.com tx=Github
```

This line contains a tag, an id, two classes, two attributes, and some text.
It would be translated to:

```
<a id='anchor' class='styled upper' name='anchor' href='github.com'>Github</a>
```

### Multiline format
- each leading space represents a level
- by default, a newline character is the delimiter

#### Example

```
div .mydiv
 p .super tx=some text
div #another
 span
  a href=google.com tx=google"
```

Would be translated to:

```
<div class='mydiv'><p class='super'>some text</p></div><div id='another'><span><a href='google.com'>google</a></span></div>
```

### Personalizing
You can change the line delimiter ("\n") and/or the text key ("tx="):

```
$htmlified = new Htmlify($markup, "...", "t=");
$html = $htmlified->getHtml();
```

In this case, `$markup` would look something like this:
```
div .mydiv...
 p .super t=some text...
div #another...
 span...
  a href=google.com t=google"
```

If you only want to change the text delimiter, just use `Htmlify::LINE_DELIM` (or "\n") as the second parameter.