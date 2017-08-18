# SuperSQL
SlickInject and Medoo on steroids - The most advanced and lightweight library of its kind.

## Purpose

1. To provide a very fast and efficient way to edit sql databases
2. To provide a easy method of access

### Main Features

1. Very small - 24.2KB one file (Unminified, `dist/SuperSQL.php`. Minified version: 10.2KB)
2. Simple and easy - Very easy to lean. We also provide a simple and advanced API
3. Compatability - Supports major SQL databases
4. Efficiency - This module was built with speed in mind.
5. Complexity - This module allows you to make all kinds of complex queries
6. Security - This module prevents SQL injections.
7. Availability - This module is FREE. Licensed under the MIT license.

## Notes
#### Missing features.
* `GROUP` - prs welcome
* `[<>] and [><]` shortcut operators

## Usage
You may either

1. Use the built file (/dist/SuperSQL.php)
2. Use the library (include index.php)

```php
new SuperSQL($dsn,$user,$pass);
```
```php
use SuperSQL\SuperSQL;

// MySql setup
$host = "localhost";
$db = "test";
$user = "root";
$pass = "1234";

$dsn = "mysql:host=$host;port=3306;dbname=$db;charset=utf8";
$SuperSQL = new SuperSQL($dsn,$user,$pass);
```

```php
use SuperSQL\SQLHelper;

// MySql setup
$host = "localhost";
$db = "test";
$user = "root";
$pass = "1234";

$SuperSQL = SQLHelper::connect($host, $db, $user,$pass);
```

## Build
To build this library, do 

> node builder.js

It will build to /dist/SuperSQL.php

## Documentation

Full documentation is here: http://supersql.tk/

![supersql.tk](https://user-images.githubusercontent.com/13282284/29477701-7e6385c6-8437-11e7-9e87-74a12393c49a.png)

## FAQ

**Whats the difference between this and Medoo?**

Not much - they are basically the equivalant - However, SuperSQL is slightly more advanced.

* Response class - SuperSQL has a response class to access crucial information, such as errors
* Helper - SuperSQL comes with a helper class, with helper functions, while meedoo has it built right in.
* Smaller & lightweight - SuperSQL is smaller than medoo
* Development - SuperSQL's code is well structured and it is commented - so you can understand it more
* SuperSQL is faster - Using xdebug, we found that superSQL is faster than medoo. (x1000,6%)

## Special thanks
* @LegitSoulja - SlickInject
* @catfan - Medoo

## License

```
MIT License

Copyright (c) 2017 Andrew S (Andrews54757_at_gmail.com)

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
```

