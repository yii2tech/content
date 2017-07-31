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
        'contentManager' => [
            'class' => 'yii2tech\content\Manager',
            'sourceStorage' => [
                'class' => 'yii2tech\content\PhpStorage',
                'filePath' => '@app/data/content',
            ],
            'sourceStorage' => [
                'class' => 'yii2tech\content\DbStorage',
                'table' => '{{%Page}}',
                'contentAttributes' => [
                    'title',
                    'body',
                ],
            ],
        ],
    ],
    ...
];
```

In this example default contents for the static pages located in the project files under directory '@app/data/content'.
Each record represented by separated file, like following:

```php
<?php
// file '@app/data/content/about.php'
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

$contentManager = Yii::$app->get('contentManager');

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
// file '@app/data/content/about.php'
return [
    'title' => 'About',
    'body' => <<<HTML
<h1>About page content</h1>
<img src="{appBaseUrl}/images/about.jpg">
<a href="{appBaseUrl}">Home page</a>
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

$contentManager = Yii::$app->get('contentManager');

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
        'contentManager' => [
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


## Email template management <span id="email-template-management"></span>


## Creating content management web interface <span id="creating-content-management-web-interface"></span>


## Working with meta data <span id="working-with-meta-data"></span>

