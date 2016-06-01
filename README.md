# gitmodx

Installation
============

Clone the repository
--------------------

Firstly, clone this repository to your core/components/ 
It will looks like this:

/Users/YourUser/websites/YourSite/core/components/gitmodx/

Setup modx system settings
--------------------------
parser_class_path = {core_path}components/gitmodx/model/gitmodx/
parser_class = gitModParser


Rewrite system index.php files
------------------------------
Yes, I know, that rewriting system code is not right. But I have no another solution.

### index.php in base path

Replace include path of modx class in line 27 to:
```php
MODX_CORE_PATH . "components/gitmodx/model/gitmodx/gitmodx.class.php"
```

It will looks like this:
```php
...
/* include the modX class */
if (!@include_once (MODX_CORE_PATH . "components/gitmodx/model/gitmodx/gitmodx.class.php")) {
    $errorMessage = 'Site temporarily unavailable';
...
```

Then replace name of modx class from modX to gitModX in line 39:
```php
$modx = new gitModx();
```


### index.php in manager path

Do the same operations in manager/index.php

Line 37:
```php
...
if (!(include_once MODX_CORE_PATH . 'components/gitmodx/model/gitmodx/gitmodx.class.php')) {
...
```

Line 43:
```php
$modx= new gitModx('', array(xPDO::OPT_CONN_INIT => array(xPDO::OPT_CONN_MUTABLE => true)));
```

### index.php in connectors path

Do the same operations in connectors/index.php

Line 24-26:
```php
...
if (!include_once(MODX_CORE_PATH . 'components/gitmodx/model/gitmodx/gitmodx.class.php')) die();

$modx = new gitModX('', array(xPDO::OPT_CONN_INIT => array(xPDO::OPT_CONN_MUTABLE => true)));
...
```


Usage
=====
Now you can write your chunks and snippets directly in file.
WARNING!!! File-based snippets and chunks with gitmodx mechanism will not store to database, but they will work if you call
them by modx tags (like `[[$chunkName]]` or `[[$snippetName]]`) or if you call them by modx api (like `$modx->getChunk('chunkName')`
or `$modx->runSnippet('snippetName')`

Create snippet
--------------
Go to core/components/gitmodx/elements/snippets/
You able to make the catalog inside this directory to organize your shippets by purpose.

For example:
-core/components/snippets/
--utils
--usersnippets
--productsnippets


Create new file yourSnippetName.php (case sensitive):
```php
<?php
//Do something
return 'someValue';
```

And then you can call this snippet (f.e., in template):
`[[yourSnippetName]]`

Create chunk
------------
For chunks you should do the same process, but in core/components/chunks/ catalog.