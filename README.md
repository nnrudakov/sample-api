SAMPLE API
============================

SAMPLE API based on [Yii 2](http://www.yiiframework.com/) framework (basic application).

[![Latest SAMPLE API Stable Version](https://img.shields.io/badge/sample--api-0.1.0-blue.svg?colorA=888&longCache=true)](https://github.com/nnrudakov/sample-api/archive/v0.1.0.tar.gz)
[![Latest Yii2 Stable Version](https://poser.pugx.org/yiisoft/yii2/v/stable)](https://packagist.org/packages/yiisoft/yii2)
[![Build Yii2 Status](https://travis-ci.org/yiisoft/yii2.svg?branch=master)](https://travis-ci.org/yiisoft/yii2)

## Введение

Приложение предоставляет `API` интерфейс для публичной части системы.

## Требования к разработке ##
 
 В качестве IDE для разработки рекомендуется использовать [PhpStorm](https://www.jetbrains.com/phpstorm/specials/phpstorm/phpstorm.html).
 Во всех проектах используется система контроля версий [Git](https://git-scm.com/). Для именования веток и выполнения
 слияний используется практика [git-flow](https://jeffkreeftmeijer.com/git-flow/) и соответствующий
 [плагин](https://plugins.jetbrains.com/plugin/7315-git-flow-integration) для `PhpStorm`. При его настройке указываются
 значения по умолчанию, за исключением префикса версий — `v`. В настройках `PhpStorm` должна быть включена настройка
 `Enable EditorConfig support`.
 
### Требования к оформлению кода
 
 Разработка должна быть с синтаксисом для версии `PHP 7.4` без обратной совместимости.
 
 Любой `PHP` файл должен иметь объявление директивы `declare`:
  
  ```php
     declare(strict_types=1);
  ```
  
  Любой файл класса должен быть оформлен по следующему шаблону:
  
  ```php

     /**
      * Copyright (c) 2020-текущий_год. Nikolaj Rudakov
      */
     
     declare(strict_types=1);
     
     namespace ...;
     
     use ...;
     
     /**
      * Описание класса.
      *
      * @package    Соответствие в точности пространству имён. Например, app\controllers
      * @author     Nikolaj Rudakov <nnrudakov@gmail.com>
      * @copyright  2019-текущий_год Nikolaj Rudakov
      */
     class ИмяКласса
     {
         //
     }
     
 ```
 
  Подключаемые классы группировать по последней вложенности в алфавитном порядке:
  
  ```php

     // верно
     use some\name\other_space\C;
     use some\name\space\{A, B};
      
     // неверно
     use some\name\{space\A, space\B, other_space\C};

  ```
  
  Свойства класса, методы класса, параметры методов должны быть документированы используя синтаксис `phpDocumentor`.
  Если метод возвращает конкретный тип данных, он должен быть указан при объявлении метода. Типы параметров также должны
  быть типизированы. Исключить возвращение функциями/методами смешанного результата `@return mixed`.
  
### Наследование
  
  Все контроллеры должны быть наследованы от базового абстрактного класса [BaseController](controllers/BaseController.php).
  
  Все конечные обработчики методов `API` должны быть оформлены отдельными классами в пространстве имён
  `app\controllers\id_контроллера` и наследованы от базового абстрактного класса [BaseAction](controllers/BaseAction.php).
  
### Требования к документации

 Для документации разработчиков используется спецификация формата [phpDocumentor](https://www.phpdoc.org/docs/latest/index.html).
 
 `API` документация должна быть оформлена согласно спецификации [Swagger](https://swagger.io/specification/). Описание
 должно быть в конечных действиях (наследников `BaseAction`), которые возвращают результат, вместо описания всех методов
 в контроллере. Исключение составляют стандартные действия `Yii` и если они не были переопределены. В этом случае
 документация оформляется в контроллере. 

## Правила именования версий ##

 Номер версии указывается в формате: `vX.Y.Z`, где X - старшая версия, Y - младшая версия, Z - минорная версия.
 Пока основная ветка `master` не загружена на боевые серверы, т.е. ведётся полноценная разработка, старшая версия
 всегда 0. После первой выгрузки версия становится `v1.0.0` и меняется при больших изменениях архитектуры, рефакторинга
 большого числа модулей (больше 3). Младшая версия увеличивается при изменениях в базе данных (добавление таблиц,
 столбцов), которые без сложностей решаются миграциями, которые можно отменить. Также версия меняется при добавлении,
 удалении или существенном рефакторинге единичных классов, методов, функций. Минорная версия увеличивается после
 исправления ошибок или изменениях существующего кода.

 Во время коммита ветки релиза список изменений должен быть внесён в файл `CHANGELOG.md` проекта. Версия релиза должна
 быть указана в файле `config/web.php` проекта.
 
## Требования к тестированию

 Требования к тестированию указаны в файле [README](tests/README.md).
