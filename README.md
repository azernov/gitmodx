# gitmodx

This extension may really improve and speed your development.

With gitmodx you can create snippets, chunks, system settings and context settings directly from IDE.

File-based chunks and snippets will not store in database and can by easily managed by git. Your will not need to create db patches or migrations for saving chunks, snippets and settings in version control system (like git).

Installation
============

Clone the repository
--------------------

Firstly, clone this repository to your core/components/ 
It will looks like this:

/Users/YourUser/websites/YourSite/core/components/gitmodx/

Setup modx system settings
--------------------------
```
parser_class_path = {core_path}components/gitmodx/model/gitmodx/
parser_class = gitModParser
```


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
or `$modx->runSnippet('snippetName')` or `$modx->getObject('modChunk',array('name'=>'chunkName'))`)

Create snippet
--------------
Go to core/components/gitmodx/elements/snippets/

You can make the catalog inside this directory to organize your shippets by purpose.

For example:
```
-core/components/snippets/
--utils
--usersnippets
--productsnippets
```

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

Create system settings
----------------------
If you have often-change system settings you can duplicate them into gitmodx config file.

**But you must remember, that file-based system settings will override system settings stored in database.**

Open core/components/gitmodx/config/config.inc.php

Write some settings:

```php
<?
$gitModxConfig = array(
    'mySetting' => 'someValue',
    'myAnotherSetting' => 'someAnotherValue`,
    //You can override standard system setting
    'site_status' => 0,
    //So if in database you have site_status = 1, real value will be 0
);

return $gitModxConfig;
```

And then you can call this settings by:

```php
$modx->getOption('mySetting');
```

or in template/chunk:

```
[[++mySetting]]
```


Create context settings
-----------------------
To create context setting(s) you should do the same process as with system settings. But you should use another config file:

```
core/components/gitmodx/config/[context_key]/config.inc.php
```

**But you must remember that settings defined in file will be overriden by context settings stored in database**

So if you defined in file `core/components/gitmodx/config/web/config.inc.php` setting `site_status = 0`, and in modx backend
in the web-context settings you defined `site_status = 1` real value will be 1