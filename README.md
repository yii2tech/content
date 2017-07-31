<p align="center">
    <a href="https://github.com/yii2tech" target="_blank">
        <img src="https://avatars2.githubusercontent.com/u/12951949" height="100px">
    </a>
    <h1 align="center">Content management system for Yii2</h1>
    <br>
</p>

This extension provides content management system for Yii2.

For license information check the [LICENSE](LICENSE.md)-file.

[![Latest Stable Version](https://poser.pugx.org/yii2tech/content/v/stable.png)](https://packagist.org/packages/yii2tech/content)
[![Total Downloads](https://poser.pugx.org/yii2tech/content/downloads.png)](https://packagist.org/packages/yii2tech/content)
[![Build Status](https://travis-ci.org/yii2tech/content.svg?branch=master)](https://travis-ci.org/yii2tech/content)


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist yii2tech/content
```

or add

```json
"yii2tech/content": "*"
```

to the require section of your composer.json.


Usage
-----

This extension provides basic content management system for Yii2.
There is a common task to provide ability for application administrator to change site content, like static
pages ('About us', 'How it works' and so one), email templates and so on. This task is usually solved be developer
using a dedicated database entities (tables) to store static page or email templates contents. However using just
database entities creates a several problems like defining default (pre-filled) data or updating existing application.
In case you need to setup a default pre-set for static pages, so the deployed application does not look empty or
add extra static page to the existing site with pre-filled content, you'll need to manipulate database records using
DB migration mechanism or something. This is not very practical - it would be better to be able to control list of
static pages or email templates inside program code under the VSC (Git, Mercurial etc).

This extension solves the content management task using 'override' principle: the default set of content is defined by
the source code files, while there is an ability to override default contents using database storage.

This extension provides a special Yii application component - [[\yii2tech\content\Manager]], which provides high level
interface for content management. Manager operates by 2 content storages:

 - [[\yii2tech\content\Manager::$sourceStorage]] - uses project source files as a default contents source
 - [[\yii2tech\content\Manager::$overrideStorage]] - uses DBMS storage to override source contents

Application configuration example:

```php
return [
    'components' => [
        'pageContentManager' => [
            'class' => 'yii2tech\content\Manager',
            'sourceStorage' => [
                'class' => 'yii2tech\content\PhpStorage',
                'filePath' => '@app/data/pages',
            ],
            'overrideStorage' => [
                'class' => 'yii2tech\content\DbStorage',
                'table' => '{{%Page}}',
                'contentAttributes' => [
                    'title',
                    'body',
                ],
            ],
        ],
    ],
    // ...
];
```

In this example default contents for the static pages located in the project files under directory '@app/data/content'.
Each record represented by separated file, like following:

```php
<?php
// file '@app/data/pages/about.php'
return [
    'title' => 'About',
    'body' => 'About page content',
];
```

The override contents will be stored in the database table 'Page', which can be created using following DB migration:

```php
class m??????_??????_createPage extends yii\db\Migration
{
    public function safeUp()
    {
        $tableName = 'Page';
        $columns = [
            'id' => $this->string(),
            'title' => $this->string(),
            'body' => $this->text(),
            'PRIMARY KEY(id)',
        ];
        $this->createTable($tableName, $columns);
    }

    public function safeDown()
    {
        $this->dropTable('Page');
    }
}
```

During the 'About' page rendering you should use abstraction provided by [[\yii2tech\content\Manager]].
Method [[\yii2tech\content\Manager::get()]] returns the content set for particular entity (e.g. particular page),
holding all related content parts: 'title', 'body' and so on.
For example:

```php
<?php
// file '@app/views/site/about.php'

use yii\bootstrap\Html;

/* @var $this yii\web\View */
/* @var $contentManager yii2tech\content\Manager */

$contentManager = Yii::$app->get('pageContentManager');

$this->title = $contentManager->get('about')->render('title');
?>
<div class="site-about">
    <?= $contentManager->get('about')->render('body') ?>
</div>
```

In case there is a record in 'Page' table with 'id' equal to 'about', the data from this record will be rendered,
otherwise the data from '@app/data/content/about.php' file will be used.

This extension provides several implementations for the content storage:

 - [[yii2tech\content\PhpStorage]] - uses a PHP code files for content storage.
 - [[yii2tech\content\JsonStorage]] - uses a files in JSON format for content storage.
 - [[yii2tech\content\DbStorage]] - uses a relational database as a content storage.
 - [[yii2tech\content\MongoDbStorage]] - uses MongoDB as a content storage.
 - [[yii2tech\content\ActiveRecordStorage]] - uses ActiveRecord classes for the content storage.

Please refer to the particular storage class for more details.

> Note: you can use any combination of implementations for 'source' and 'override' storages, but in order to
  make sense in 'content override' approach storage based on project files (e.g. `PhpStorage` or `JsonStorage`)
  should be used  for the 'source'.


## Template rendering <span id="template-rendering"></span>

Storing just a final HTML content usually is not enough. For the most cases content management operates by content
templates, which will be populated by particular data at runtime. For example you may want to use application base
URL inside the 'About' page content, allowing to refer images and create links. Thus default content will look like
following:

```php
<?php
// file '@app/data/pages/about.php'
return [
    'title' => 'About',
    'body' => <<<HTML
<h1>About page content</h1>
<img src="{{appBaseUrl}}/images/about.jpg">
<a href="{{appBaseUrl}}">Home page</a>
...
HTML
];
```

While displaying the content to can pass render parameters to [[\yii2tech\content\Item::render()]] in the same way
as regular view rendering:

```php
<?php
// file '@app/views/site/about.php'

use yii\bootstrap\Html;

/* @var $this yii\web\View */
/* @var $contentManager yii2tech\content\Manager */

$contentManager = Yii::$app->get('pageContentManager');

$this->title = $contentManager->get('about')->render('title');
?>
<div class="site-about">
    <?= $contentManager->get('about')->render('body', [
        'appBaseUrl' => Yii::$app->request->baseUrl
    ]) ?>
</div>
```

You may setup the content renderer via [[\yii2tech\content\Manager::$renderer]].
This extension provides several implementations for the content renderer:

 - [[yii2tech\content\PlaceholderRenderer]] - performs content rendering via simple string placeholder replacement.
 - [[yii2tech\content\PhpEvalRenderer]] - performs content rendering evaluating it as a PHP code.
 - [[yii2tech\content\MustacheRenderer]] - performs content rendering using [Mustache](https://mustache.github.io/).

Please refer to the particular renderer class for more details.

> Note: the actual content template syntax varies depending on actual renderer used.

You may use [[\yii2tech\content\Manager::$defaultRenderData]] to setup default render data to be used for every rendering,
so you do not need to pass them all the time:

```php
return [
    'components' => [
        'pageContentManager' => [
            'class' => 'yii2tech\content\Manager',
            'defaultRenderData' => function () {
                return [
                    'appName' => Yii::$app->name,
                    'appBaseUrl' => Yii::$app->request->baseUrl,
                ];
            },
            // ...
        ],
    ],
    // ...
];
```


## Overriding content <span id="overriding-content"></span>

You may save content override using [[\yii2tech\content\Manager::save()]]. It will write the data into the
'overrideStorage', keeping the one in 'sourceStorage' intact. For example:

```php
/* @var $contentManager yii2tech\content\Manager */
$contentManager = Yii::$app->get('pageContentManager');

$contentManager->save('about', [
    'title' => 'Overridden Title',
    'body' => 'Overridden Body',
]);

echo $contentManager->get('about')->render('title'); // outputs 'Overridden Title'
```

> Note: [[\yii2tech\content\Manager]] does NOT perform any check for content parts name matching
  between source content and overridden one. It is your responsibility to maintain consistence between
  source and overridden contents set. However, you can use [[\yii2tech\content\Item]] methods (see below)
  to solve this task.

You can use [[\yii2tech\content\Manager::reset()]] method in order to restore original (default) value
for particular content set. For example:

```php
/* @var $contentManager yii2tech\content\Manager */
$contentManager = Yii::$app->get('pageContentManager');

$contentManager->reset('about');

echo $contentManager->get('about')->render('title'); // outputs 'About'
```

You can perform same data manipulations using interface provided by [[\yii2tech\content\Item]].
Each content part is accessible via its virtual property with the same name.
For example:

```php
/* @var $contentManager yii2tech\content\Manager */
$contentManager = Yii::$app->get('pageContentManager');

$contentItem = $contentManager->get('about');
$contentItem->title = 'Overridden Title';
$contentItem->body = 'Overridden Body';
$contentItem->save(); // saves override

$contentItem->reset(); // restores origin (source)
```

Usage of [[\yii2tech\content\Item]] solves the problem of verification of matching source and override
contents set.


## Saving extra content <span id="saving-extra-content"></span>

You can add a completely new contents set into 'override' storage even if it has no match in 'source' one.
There is no direct restriction for that. For example:

```php
/* @var $contentManager yii2tech\content\Manager */
$contentManager = Yii::$app->get('pageContentManager');

$contentManager->save('newPage', [
    'title' => 'New page',
    'body' => 'New page body',
]);

echo $contentManager->get('newPage')->render('title'); // outputs 'New page'

$contentManager->reset('newPage');

$contentManager->get('newPage'); // throws an exception
```

This can also be performed using [[\yii2tech\content\Item]]. For example:

```php
use yii2tech\content\Item;

/* @var $contentManager yii2tech\content\Manager */
$contentManager = Yii::$app->get('pageContentManager');

$contentItem = new Item([
    'manager' => $contentManager,
]);
$contentItem->setId('newPage');
$contentItem->setContents([
    'title' => 'New page',
    'body' => 'New page body',
]);
$contentItem->save();
```


## Creating content management web interface <span id="creating-content-management-web-interface"></span>

Class [[\yii2tech\content\Item]] is a descendant of [[\yii\base\Model]], which uses its content parts as the
model attributes. Thus instance of [[\yii2tech\content\Item]] can be used for creating a web forms, populating
from request data and saving.


## Working with meta data <span id="working-with-meta-data"></span>

Working with content templates, you may want to setup some reference information about them, for example
provide description for variables (placeholders) used inside the template. Such information may vary depending
on particular content set, like variables for 'about' page may be different from the ones for 'how-it-works' page.
Thus it is good practice to save such meta information along with the default content, e.g. in the source file.
For example:

```php
<?php
// file '@app/data/pages/about.php'
return [
    'title' => 'About {{appName}}',
    'body' => 'About page content',
    'pageUrl' => ['/site/about'],
    'placeholderHints' => [
        '{{appName}}' => 'Application name'
    ],
];
```

You can declare meta content parts list using [[\yii2tech\content\Manager::$metaDataContentParts]], for example:

```php
return [
    'components' => [
        'pageContentManager' => [
            'class' => 'yii2tech\content\Manager',
            'metaDataContentParts' => [
                'pageUrl',
                'placeholderHints',
            ],
            // ...
        ],
    ],
    // ...
];
```

Content parts listed at [[\yii2tech\content\Manager::$metaDataContentParts]] will not be returned by
[[\yii2tech\content\Manager::get()]] or [[\yii2tech\content\Manager::getAll()]] methods and thus will not
be populated inside [[\yii2tech\content\Item::$contents]]. You should use [[\yii2tech\content\Manager::getMetaData()]]
or [[\yii2tech\content\Item::getMetaData()]] to retrieve them. For example:

```php
/* @var $contentManager yii2tech\content\Manager */
$contentManager = Yii::$app->get('pageContentManager');

$contentItem = $contentManager->get('about');
var_dump($contentItem->has('pageUrl')); // outputs `false`

$metaData = $contentManager->getMetaData('about');
var_dump($metaData['pageUrl']);

$metaData = $contentItem->getMetaData();
var_dump($metaData['pageUrl']);
```

Meta data usage helps to compose user-friendly content management interface.


## Email template management <span id="email-template-management"></span>