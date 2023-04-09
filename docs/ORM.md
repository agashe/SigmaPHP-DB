## ORM

SigmaPHP-DB provides an ORM module that allows you to interact and control your data with without writing any SQL , all in plain PHP objects. The ORM will map your table's columns into model's fields , that you can use to manipulate your data smoothly.

To start using the ORM in you project , all what you need to do , is to create new model in you path of choice. In that model create new class that will inherit the ORM class :

```
<?php

namespace MyApp\Models;

use SigmaPHP\DB\ORM\Model;

class User extends Model
{
    // The ORM will fetch table name and columns automatically
}

```
<br>

Then you can use your model in your code , the model's constructor requires 2 parameters , first a PDO connection instance , second the database name :

```
<?php

include 'vendor/autoload.php';

use MyApp\Models\User;

$connection = new \PDO(
    "mysql:host=localhost;
    dbname=testing",
    'root',
    'root'
);

$dbName = 'testing';

$userModel = new User($connection, $dbName);

$users = $userModel->all();
```
<br>

By default the ORM will generate the table name automatically and then fetch its fields. It will assume that the table name is the plural of the class name. 

So for example a model class with name `Product` the generated table name will be `products`.

Also the default primary key is `id` , to change any of these default parameters , all what you need is to override the corresponding variable , as following :

```
<?php

namespace MyApp\Models;

use SigmaPHP\DB\ORM\Model;

class User extends Model
{
    protected $table = 'users_table';

    protected $primary = 'user_id';

    protected $fields = ['user_id', 'name', 'age'];
}

```
<br>