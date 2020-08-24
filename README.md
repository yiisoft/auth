<p align="center">
    <a href="https://github.com/yiisoft" target="_blank">
        <img src="https://avatars0.githubusercontent.com/u/993323" height="100px">
    </a>
    <h1 align="center">Yii Auth</h1>
    <br>
</p>

The package provides various authentication methods, and a set of abstractions to implement in your application.

[![Latest Stable Version](https://poser.pugx.org/yiisoft/auth/v/stable.png)](https://packagist.org/packages/yiisoft/auth)
[![Total Downloads](https://poser.pugx.org/yiisoft/auth/downloads.png)](https://packagist.org/packages/yiisoft/auth)
[![Build status](https://github.com/yiisoft/auth/workflows/build/badge.svg)](https://github.com/yiisoft/auth/actions)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/yiisoft/auth/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/yiisoft/auth/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/yiisoft/auth/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/yiisoft/auth/?branch=master)

## Installation

```
composer require yiisoft/auth
```

## General usage

### 

### Getting identity in following middleware

In order to get an identity instance if the following middleware:

```php
public function actionIndex(\Psr\Http\Message\ServerRequestInterface $request): \Psr\Http\Message\ResponseInterface
{
    $identity = $request->getAttribute(\Yiisoft\Auth\Middleware\Authentication::class);
    // ...
}
```
