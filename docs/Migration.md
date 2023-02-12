# SigmaPHP-DB
PHP Database Tools.

## Features
- 

## Installation

``` 
composer require agashe/sigmaphp-db

```

## Documentation

After installation is done , include the class in your project by:
* including vendor/autoload.php for Native PHP projects
* or adding the class to your framework config , *for example app/config/app.php for laravel* , 

You can choose between use the static method **generate** directly , or 
define a new instance and call the **create** method in your app.

```
<?php

include 'vendor/autoload.php';
use PassGen;

// the static way
echo PassGen\PassGen::generate();

// the instance way
$pass = new PassGen\PassGen();

echo $pass->create();

```

We can use 2 parameters to control both the length and the type of the characters used in for the password

|   Parameter    | Data Type |                Constraints                |
| :------------: | :-------: | ----------------------------------------- | 
| passwordLength |  integer  | password's length between 1 to 50 digits. | 
| passwordType   |   string  | compination of 4 options (capital , small , numeric & symbols) sparated by "\|" or you<br>can leave empty to use all of them!<br>|

and also you can use the CLI version to generate passwords for your accounts.
In your command line:

```
$ php ./vendor/bin/passgen
```
and you can use "-l" to set the length and "-t" to set the type!

## Examples

```
$ php ./vendor/bin/sigma-db

```
## License
(SigmaPHP-DB) released under the terms of the MIT license.
