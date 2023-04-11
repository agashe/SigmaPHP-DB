## ORM

SigmaPHP-DB provides an ORM module that allows you to interact and control your data  without writing any SQL , all in plain PHP objects. The ORM will map your table's columns into model's fields , which you can use to manipulate your data smoothly.

To start using the ORM in you project , all what you need to do , is to create a new model in you path of choice. In that model create new class that will inherit the ORM model class :

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

*(Please Note : the database credentials is just an example)*

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

By default the ORM will generate the table name automatically and then fetch its fields. It will assume that the table name is the plural form of the class name. 

So for a model with name `Product` the generated table name will be `products`.

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

## Create new instance of model

The ORM provides 2 methods to create new instance of your model :

**Using the `create` method** <br>
This method accepts the user data as array , and it returns new instance of the model. So you might have a single instance of the model in your project , and use it to create new instances;

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

$newUser = $userModel->create([
    'name' => 'Jone Doe',
    'age' => 35
]);

echo $newUser->name;
```
<br>


**Using the `save` method** <br>
The `save` method can be used for both create / update , and in this method you will enter each field value manually.

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

$newUser = new User($connection, $dbName);

$newUser->name = 'Jone Doe';
$newUser->age = 35;

$newUser->save();

echo $newUser->name;
```
<br>

## Retrieve models data

```
<?php

// fetch all models
$users = $userModel->all();

// count all models
$usersCount = $userModel->count();

// find single user using the primary key
$user = $userModel->find(5);

// search user by field
$user = $userModel->findBy('age', 35);
```
<br>

## Update model

To update model , first you need to retrieve the model , then update its fields values and finally use the `save` method to save the changes.

```
<?php

// 1- select the user we need to update
$user = $userModel->find(15);

// 2- update the model values
$user->name = 'Mohamed';

// 3- save the changes
$user->save();
```
<br>

## Delete model

```
<?php

// 1- select the user we need to delete
$user = $userModel->find(15);

// 2- call the delete method on it
$user->delete();
```
<br>

## Relations

The ORM supports relations between model using the `hasRelation` method , this method once called it will return an array by all related models.

Let's assume that we have an `User` model and we need to retrieve all this user posts from `Post` model , each of the `Post` models has a field called `user_id` which reference the user's id in the users table.

so we start be define the posts method in the `User` model as following :

```
<?php

namespace MyApp\Models;

use SigmaPHP\DB\ORM\Model;

class User extends Model
{
    /**
     * @return array
     */
    public function posts()
    {
        return $this->hasRelation(
            Post::class,
            'user_id',
            'id'
        );
    }
}

```

The `hasRelation` accepts 3 parameters :

<table border>
    <tr>
        <td>$model</td>
        <td>The model that we have relation with</td>
    </tr>
    <tr>
        <td>$foreignKey</td>
        <td>The field which related to the other model</td>
    </tr>
    <tr>
        <td>$localKey</td>
        <td>The local field that connected to the foreign key in the other table</td>
    </tr>
</table>

Then you can call the `posts` method in your code :


```
<?php

// 1- select the user
$user = $userModel->findBy('name', 'test');

// this will return array of Post models , connected to that user
$posts = $user->posts();

```

In addition we can easily use the `hasRelation` on the `Post` model to retrieve the user who created the post (the reverse relation). 

Please note that in the reverse relation the keys are reversed so the `$localKey` on the `Post` model in this case will be the `user_id` and the `$foreignKey` will be the `id` field on the users table. 

```
<?php

namespace MyApp\Models;

use SigmaPHP\DB\ORM\Model;

class Post extends Model
{
    /**
     * @return \Models\User|null
     */
    public function author()
    {
        return $this->hasRelation(
            Post::class,
            'id'
            'user_id',
        )[0] ?? null;
    }
}

```

## Adding other methods

The models are classes , so you can customize them by adding all the methods , constants , implement interfaces and use traits. So here's an imaginary example on how a full `User` model can look like in blog app :

```
<?php

namespace MyApp\Models;

use SigmaPHP\DB\ORM\Model;

class User extends Model implements MyBaseModelInterface
{
    use MyTrait;

    /**
     * @return \Models\Role|null
     */
    public function fullName()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    /**
     * @return \Models\Role|null
     */
    public function role()
    {
        return $this->hasRelation(
            Role::class,
            'id',
            'role_id'
        )[0] ?? null;
    }

    /**
     * @return array
     */
    public function posts()
    {
        return $this->hasRelation(
            Post::class,
            'user_id',
            'id'
        );
    }
}

```