Yii2 ICheck
==========

[![Latest Stable Version](https://poser.pugx.org/yiichina/yii2-icheck/v/stable.png)](https://packagist.org/packages/yiichina/yii2-icheck)
[![Total Downloads](https://poser.pugx.org/yiichina/yii2-icheck/downloads.png)](https://packagist.org/packages/yiichina/yii2-icheck)
[![License](https://poser.pugx.org/yiichina/yii2-icheck/license)](https://packagist.org/packages/yiichina/yii2-icheck)
[![Build Status](https://img.shields.io/travis/yiichina/yii2-icheck.svg)](http://travis-ci.org/yiichina/yii2-icheck)
[![Yii2](https://img.shields.io/badge/Powered_by-Yii_Framework-green.svg?style=flat)](http://www.yiiframework.com/)

用于yii2的icheck扩展

安装
----

安装这个扩展的首选方法是通过 [composer](http://getcomposer.org/download/)。

可以运行

```
composer require --prefer-dist yiichina/yii2-icheck "*"
```

也可以添加

```
"yiichina/yii2-icheck": "*"
```

到你的 `composer.json` 文件的包含部分。


使用
-----

一旦扩展安装完成，你可以简单的使用它如以下代码：

```php
<?php
use yiichina\icheck\ICheck;
?>


<?= ICheck::widget(['type' => ICheck::TYPE_RADIO_LIST, 'skin' => ICheck::SKIN_MIMIMAL, 'color' => ICheck::COLOR_GREEN, 'model' => $model, 'attribute' => $attribute, 'items' => $items]) ?>
<?= ICheck::widget(['type' => ICheck::TYPE_CHECBOX_LIST, 'skin' => ICheck::SKIN_MIMIMAL, 'color' => ICheck::COLOR_GREEN, 'model' => $model, 'attribute' => $attribute, 'items' => $items]) ?>
```
