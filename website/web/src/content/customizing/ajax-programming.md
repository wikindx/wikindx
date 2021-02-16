+++
title = "Ajax programming"
date = 2021-01-30T00:08:41+01:00
weight = 5
#pre = "<b>1. </b>"
+++


The AJAX implementation in WIKINDX consists of core PHP and javascript
files in `core/javascript/ajax` and PHP and javascript files in folder locations
pertaining to their function. There should be no need to edit the
`core/javascript/ajax` files and everything you need to do can be done by calling up
the core files, and their classes and methods, in your own AJAX PHP and
javascript files which can then be placed where you wish.

The implementation is powerful and flexible, allowing single AJAX
actions per form, multiple AJAX actions per form (from a single trigger
or multiple triggers) and even sequences of AJAX actions each dependent
upon the result of the previous one. With power and flexibility comes
complexity...but follow the steps below and you should be OK.

AJAX programming in WIKINDX requires PHP functions and javascript
functions and the advice is to place these in separate files
in specific folders away from `core/javascript/ajax`. For example, the AJAX
functionality on the Quick Search page, hiding and showing various
select boxes depending upon the selection of options in other select
boxes, is driven by `core/modules/list/searchSelect.js` and files
in core/modules/list/ including `LISTSHOWHIDE.php`. In the example
given below, the scripts referred to write the innerHTML property
of the DIV given in $jsonArray['targetDiv'] with code produced by
$jsonArray['script'] when the user clicks on an option in the form
select box with the ID $jsonArray['triggerField'] thus setting in
action the javascript function included with $ajax->loadJavascript()
and referred to in $jsonArray['startFunction'].

1. Include the AJAX files in the HTML output of your page with the form.

~~~~php
   $ajax = FACTORY_AJAX::getInstance();
   $ajax->loadJavascript('core/modules/list/searchSelect.js?ver=' . WIKINDX_PUBLIC_VERSION);
~~~~

In this case, `searchSelect.js` is the javascript for Quick Search -- you
will include your own javascript file. loadJavascript() can also take
an array of javascript include files if you happen to have several.

2. Add the AJAX instructions to your form element(s). In this case,
there are two AJAX actions to be performed when the user clicks on an
<OPTION> in the select box trigger_Field 'search_Type'. An example from
core/modules/list/SEARCHRESOURCES.php is:

~~~~php
   $jScript = 'index.php?action=list_LISTSHOWHIDE_CORE&method=initCategories&type=search';
   $jsonArray[] = array(
      'startFunction' => 'triggerFromMultiSelect',
      'script' => "$jScript",
      'triggerField' => 'search_Type',
      'targetDiv' => 'category'
   );
   $jScript = 'index.php?action=list_LISTSHOWHIDE_CORE&method=initKeywords&type=search';
   $jsonArray[] = array(
      'startFunction' => 'triggerFromMultiSelect',
      'script' => "$jScript",
      'triggerField' => 'search_Type',
      'targetDiv' => 'keyword'
   );
   $ajax->jActionForm('onclick', $jsonArray);
~~~~

jActionForm() inserts a javascript function into the first form element
that is created in your PHP script immediately following. You need to
do this for each form element requiring an AJAX action. 'onclick' could
be another form action such as 'onsubmit' or 'onchange' etc.

$jsonArray is an array of arrays in which you specify the initial
javascript function to be run (in this case, on 'onclick'), and any
other parameters you wish to pass to your AJAX javascript.

$jsonArray['startFunction'] should _always_ be given and is the initial
javascript function run when the user actions the form element.

$jsonArray['startFunctionVars'] is optional and, if supplied, is an array
of variables passed to 'startFunction'. For example (NB the quotes);

~~~~php
$jsonArray['startFunctionVars'] = array('"var1"', '"var2"');
// or
$jsonArray['startFunctionVars'] = array('"' . $var1 . '"', '"' . $var2 . '"');
~~~~

$jsonArray['script'] and the other array elements could be compiled
in the javascript function triggerFromMultiSelect(). Additionally,
the AJAX object also has the property 'processedScript' which is
typically created upon the basis of 'script' (as above) in your initial
javascript function (see 2b) below). In any case, the doXmlHttp()
method of ajax.js expects there to be a 'targetObj' property set in the
AJAX object and it is your responsibility to do this (see 3c) below).

In $jsonArray, you can add any other parameters you wish to be passed
to your javascript.

3. Write the javascript you require ensuring you have the function
named in $jsonArray['startFunction']. If the above steps are followed,
ajax.js will automatically create an AJAXOBJECT for each AJAX action
required and this can be accessed in your javascript as:

~~~~js
   A_OBJ[gateway.aobj_index]
~~~~

where gateway.aobj_index is an integer starting from 0 that increments
each time an AJAXOBJECT is instantiated.

If the return from 'startFunction' is defined and 'false', then
gateway() will bail out -- if 'startFunction' has been put into play by
the submit button of a form, then the form will not be submitted.

The AJAXOBJECT has several properties and methods available to use:

   a) A_OBJ[gateway.aobj_index].input -- this is a duplicate of
      $jsonArray set in the PHP script above. So, for example, the PHP
      $jsonArray['script'] element can be accessed in your javascript as
      A_OBJ[gateway.aobj_index].input.script

   b) A_OBJ[gateway.aobj_index].processedScript -- this must be
      set if you are going to use A_OBJ[gateway.aobj_index].doXmlHttp
      (see below). If your javascript function processes the output of
      $jsonArray['triggerField'] to build up a script with querystring,
      then you might do:

~~~~js
         A_OBJ[gateway.aobj_index].processedScript =
            A_OBJ[gateway.aobj_index].input.script + '<&key=value&key=value>';
~~~~

   c) A_OBJ[gateway.aobj_index].targetObj -- this must be set if
      you are going to use A_OBJ[gateway.aobj_index].doXmlHttp (see
      below). This is HTML element whose innerHTML property will be set by
      A_OBJ[gateway.aobj_index].doXmlHttp. Thus, you might do (based on
      $jsonArray above -- for coreGetElementById(), see below):

~~~~js
         A_OBJ[gateway.aobj_index].targetObj =
            coreGetElementById(A_OBJ[gateway.aobj_index].input.targetDiv);
~~~~

   d) A_OBJ[gateway.aobj_index].phpResponse -- the return array from
      the PHP script called by AJAX (see below).

   e) A_OBJ[gateway.aobj_index].checkInput -- a method that checks
      $jsonArray elements are defined. It requires an array as input
      parameter:

~~~~js
   if(!A_OBJ[gateway.aobj_index].checkInput(['triggerField', 'targetDiv', 'script']))
      return false;
~~~~

   f) A_OBJ[gateway.aobj_index].doXmlHttp -- the method that executes the
   AJAX action. It requires A_OBJ[gateway.aobj_index].input.targetDiv
   to be a valid DIV element in the HTML page and that
   A_OBJ[gateway.aobj_index].processedScript (the target PHP script) be
   set. Upon executing, it will store the response back from the target
   PHP script in A_OBJ[gateway.aobj_index].phpResponse and will set the
   innerHTML property of A_OBJ[gateway.aobj_index].input.targetDiv to
   A_OBJ[gateway.aobj_index].phpResponse.innerHtml (see below).

   g) A_OBJ[gateway.aobj_index].triggerFromMultiSelect, A_OBJ[gateway.aobj_index].triggerFromSelect,
   and A_OBJ[gateway.aobj_index].triggerFromCheckbox (do what it says on the tin).

core/coreJavascript.js has several other functions that are commonly used in the WIKINDX AJAX implementation:

   a) coreGetElementById(id) -- returns an object of an HTML element given by
      its ID (e.g. $jsonArray['triggerField']).
   b) coreIsArray(input) -- if input is an array, return true, otherwise
      false. There is no reason $jsonArray could not be an array of arrays of
      strings and/or arrays...
   c) coreTrim(str), coreLTrim(str) and coreRTrim(str).
   d) coreSearchArray(haystack, needle) -- return array index if array
      element found, otherwise -1. Like javascript 1.5's indexOf() method
      which Firefox supports but IE does not.

See `core/modules/list/searchSelect.js` for an example implementation.

4. Finally, you need to write the PHP script that will be referenced by
$jsonArray['script'] above. Parameters are returned to this script from
the javascript as part of the URL's query string so will be available
in the standard $this->vars array of WIKINDX. The output of this script
is returned to the javascript's A_OBJ[gateway.aobj_index].phpResponse
where, in particular, A_OBJ[gateway.aobj_index].phpResponse.innerHTML
is used to set the innerHTML property of $jsonArray['targetDiv'] as
originally supplied in the first PHP script. So, after doing whatever
the PHP script does with the query string returned from javascript, you
might then send a response back to the javascript thus:

~~~~php
   $ajax = FACTORY_AJAX::getInstance();
   $jsonResponseArray = array();
   $jsonResponseArray = array(
      'innerHTML' => "$div",
      'next' => 'TRUE',
      'startFunction' => 'setDiv',
      'targetDiv' => "subcategory",
      'targetContent' => "$div2"
   );
   GLOBALS::buildOutputString($ajax->encode_jArray($jsonResponseArray));
   FACTORY_CLOSERAW::getInstance();
~~~~

The very minimum required in $jsonResponseArray is the 'innerHTML'
element which, in this case, is an HTML DIV element that appears in
the javascript as A_OBJ[gateway.aobj_index].phpResponse.innerHTML;
this is used to set the innerHTML property of the original
$jsonArray['targetDiv'] we started with. The circle has been
squared. If 'innerHTML' => false, then setting the innerHTML of
$jsonArray['targetDiv'] will be skipped -- useful if you just want to
run the 'next' function (see below).

Alternatively, if $jsonResponseArray has an 'ERROR' key (which might
be populated in PHP with the content of message's field returned by error_get_last()),
then the error message will be printed in an alert box and
the AJAX javascript will exit.

See `core/modules/list/LISTSHOWHIDE.php` for an example implementation.

However, just to be clever, the $jsonResponseArray above has four
optional elements in addition to the minimum 'innerHTML'. The important
one is 'next' and, if present, A_OBJ[gateway.aobj_index].doXmlHttp
will _continue_ onto the javascript function defined in
$jsonResponseArray['startFunction']. If 'next' is present in
$jsonResponseArray, then 'startFunction' must be too. In this case,
setDiv() is a non-core function that sets the innerHTML of the DIV
element referred to by the ID 'subcategory' to whatever $div2 is set
to; no 'script' is needed as no PHP functionality is required for
this but there is no reason not to use PHP for this continue function
which might then return another $jsonResponseArray with another 'next'
element...

NB In 3b) above, I sent a querystring to PHP with:

~~~~
'<&key=value&key=value>'
~~~~

For more complex querystrings, in javascript you can define an object,
JSON.stringify() it then, in PHP, JSON decode it. For example, my
querystring might be composed in javascript as:

~~~~js
   var jObj = new Object;
   jObj.index = 1;
   var ajaxReturn = '&ajaxReturn=' + JSON.stringify(jObj);
   A_OBJ[gateway.aobj_index].processedScript =
      A_OBJ[gateway.aobj_index].input.script + ajaxReturn;
~~~~

Then, in the PHP script which receives it, you would need to have:

~~~~php
   $jArray = $this->ajax->decode_jString($this->vars['ajaxReturn']);
~~~~

$jArray is then a PHP associative array.
