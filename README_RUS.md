# gitmodx

Расширение для MODx Revolution, которое позволяет реально увеличить скорость разработки.

С gitmodx вы сможете создавать сниппеты, чанки, плагины, настройки системы и контекста прямо из среды разработки!

Файловые чанки, сниппеты, плагины, системные настройки и настройки контекста не сохраняются в базу данных, что существенно упрощает версионный контроль.
Вам не понадобится после внесения изменений создавать патчи или миграции базы данных!

Установка
=========

Клонирование репозитория
------------------------

Сперва склонируйте репозиторий в папку core/components/
Путь к папке будет выглядеть примерно так:

/Users/YourUser/websites/YourSite/core/components/gitmodx/

Задайте в настройках системы две настройки
------------------------------------------
```
parser_class_path = {core_path}components/gitmodx/model/gitmodx/
parser_class = gitModParser
```


Скорректируйте файлы index.php
------------------------------
Да, я понимаю, что перезапись файлов ядра - плохое решение. Но, к сожалению, другого решения я не нашел. И тем не менее эти изменения минимальны.

### index.php в корневом каталоге сайта

Замените подключаемый путь файла с классом modx на строке 27 на:
```php
MODX_CORE_PATH . "components/gitmodx/model/gitmodx/gitmodx.class.php"
```

Должно получиться как-то так:
```php
...
/* include the modX class */
if (!@include_once (MODX_CORE_PATH . "components/gitmodx/model/gitmodx/gitmodx.class.php")) {
    $errorMessage = 'Site temporarily unavailable';
...
```

Затем заменить имя класса modx с modX на gitModX в районе 39 строки:
```php
$modx = new gitModx();
```


### index.php в папке manager

Делаем аналогичные операции с файлом manager/index.php

Строка 37:
```php
...
if (!(include_once MODX_CORE_PATH . 'components/gitmodx/model/gitmodx/gitmodx.class.php')) {
...
```

Строка 43:
```php
$modx= new gitModx('', array(xPDO::OPT_CONN_INIT => array(xPDO::OPT_CONN_MUTABLE => true)));
```

### index.php в папке с коннекторами

Делаем аналогичные операции с файлом connectors/index.php

Строки 24-26:
```php
...
if (!include_once(MODX_CORE_PATH . 'components/gitmodx/model/gitmodx/gitmodx.class.php')) die();

$modx = new gitModX('', array(xPDO::OPT_CONN_INIT => array(xPDO::OPT_CONN_MUTABLE => true)));
...
```


Применение
==========
Теперь вы можете создавать чанки, плагины, сниппеты прямо в файловой системе.
ВАЖНО!!! Чанки, плагины и сниппеты, созданные в файловой системе с применением gitmodx не сохраняются в базе данных,
но они работают, если вы их вызываете стандартным способом через теги modx
(например `[[$chunkName]]` или `[[$snippetName]]`) или если вы вызываете их через API (например `$modx->getChunk('chunkName')`
или `$modx->runSnippet('snippetName')` или `$modx->getObject('modChunk',array('name'=>'chunkName'))`)

Создание сниппетов
------------------
Идем в папку core/components/gitmodx/elements/snippets/

Вы можете создавать сниппеты как в этой папке, так и создавая подкаталоги для организации сниппетов по их назначению.

Например:
```
-core/components/snippets/
--utils
--usersnippets
--productsnippets
```

Создайте новый файл yourSnippetName.php (регистрозависимый):
```php
<?php
//Do something
return 'someValue';
```

После этого вы можете его вызывать (например, в шаблоне):
`[[yourSnippetName]]`

Создание чанков
---------------
Процесс создания чанков полностью аналогичен, но в каталоге core/components/chunks/

Файлы чанков должны иметь расширение `.tpl`

Создание плагинов
-----------------
Идем в каталог core/components/gitmodx/elements/plugins/

Сперва создайте php-файл с кодом вашего плагина. Например myPlugin.php

Затем укажите, на какие события данный плагин должен реагировать:

Идем в файл plugins.inc.php и добавляем события.

Пример:
```
<?php
return array(
    'OnHandleRequest' => array(
        'myPlugin'
    ),
    'OnLoadWebDocument' => array(
        'myPlugin
    )
);
```

Создание системных настроек
---------------------------
Если у вас есть список системных настроек, которые вы часто меняете, вы можете их продублировать
в gitmodx-config файл (core/components/gitmodx/config/config.inc.php).

**Вы должны помнить, что настройки, сохраненные в config-файле, переопределяют настройки, сохраненные в базе данных.**

Вы также можете группировать настройки, разделив по различным файлам с расширением .inc.php

Например:
```
core/components/gitmodx/config/config.inc.php
core/components/gitmodx/config/mycomponent.inc.php
core/components/gitmodx/config/minishop2.inc.php
```

Откройте core/components/gitmodx/config/config.inc.php

Создайте несколько настроек:

```php
<?
$gitModxConfig = array(
    'mySetting' => 'someValue',
    'myAnotherSetting' => 'someAnotherValue`,
    //Вы можете переопределить стандартную системную настройку site_status
    'site_status' => 0,
    //Если в базе данных настройка site_status = 1, реальным значением будет 0!
);

return $gitModxConfig;
```

После этого вы можете использовать эти настройки, как и всегда:

```php
$modx->getOption('mySetting');
```

или в шаблоне/чанке:

```
[[++mySetting]]
```

Создание настроек контекста
---------------------------
Для создания настроек контекста вам необходимо повторить те же действия, что и с системными настройками. Но это нужно делать в файле:

```
core/components/gitmodx/config/[context_key]/config.inc.php
```

Вы также можете группировать настройки, разделив по различным файлам с расширением .inc.php

Например:
```
core/components/gitmodx/config/web/config.inc.php
core/components/gitmodx/config/web/mycomponent.inc.php
core/components/gitmodx/config/web/minishop2.inc.php
```

**В отличие от системных настроек, вы должны помнить, что настройки контекста, записанные в config-файле будут переопределены настройками, сохраненными в базе данных**

Таким образом, если в файле `core/components/gitmodx/config/web/config.inc.php` настройка `site_status = 0`, а в админке, в интерфейсе настроек
контекста web вы определите настройку `site_status = 1` реальным значением будет 1