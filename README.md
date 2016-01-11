Yii2 Backup and Restore Database
===================
Database Backup and Restore functionality

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist fgh151/yii2-backup "*"
```

or add

```
"fgh151/yii2-backup": "*"
```

to the require section of your `composer.json` file.


Usage
-----

Once the extension is installed, simply add it in your config by  :

Basic ```config/web.php```

Advanced ```[backend|frontend|common]/config/main.php```

>
        'modules'    => [
            'backup' => [
                'class' => 'fgh151\modules\backup\Module',
            ],
            ...
            ...
        ],

make sure you create a writable directory named _backup on app root directory.

RBAC
----

You can use RBAC with module. Simply add it in your config:

```
>
        'modules'    => [
             'backup' => [
                    'class' => 'fgh151\modules\backup\Module',
              		'as access' => [
                             'class' => 'yii\filters\AccessControl',
                             'rules' => [
                                     //'allow' => true,
                                     //'roles' => ['admin'],
                             ]
                         ]
                     ],
            ...
            ...
        ],
```

Usage
-----

Pretty Url's ```/backup```

No pretty Url's ```index.php?r=backup```

Console ```yii backup/backup```
