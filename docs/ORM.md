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

For efficiency , the SigmaPHP-DB CLI tool provide a command to create a new model. So in your terminal run the following command : 

```
php ./vendor/bin/sigma-db create:model Product
```
<br>

Please note : this command will also generate a new migration file to create that table for that model , and it will set the table name automatically.

The model and the migration file both will be created in the default path for the models and the path for  migrations , to change these paths , open the config file "database.php" , and update the paths :

```
'path_to_migrations' => '/path/to/my/migrations',
...
'path_to_models' => '/path/to/my/Models',
```
<br>

(You can check the [Configurations](https://github.com/agashe/SigmaPHP-DB/blob/master/README.md#Configurations) section , For more info)

To use your newly created model , the model's constructor requires 2 parameters , first a PDO connection instance , second the database name :

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

// fetch single model
$users = $userModel->first();

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

You can also use `where` conditions in your model to build complex queries : 

```
<?php

// basic where
$user = $userModel->where('email', '=', 'test@testing.com')->first();

// and where
$usersCount = $userModel
    ->where('age', '>=', 35)
    ->andWhere('gender', '=', 'male')
    ->count();

// or where
$users = $userModel
    ->where('name', 'like', '%test%')
    ->orWhere('role', '=', 'admin')
    ->all();

// search models by relation
$user = $userModel
    ->whereHas('posts', 'published_at', 'is', 'null')
    ->first();

// also you can just check if a model has relation
$users = $userModel->whereHas('posts')->all();
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

## Soft Delete

The ORM supports soft delete for models , to apply the soft delete on your model , first you need to make sure , that a `deleted_at` field was added to the table.

(You can check the Migration section for more info about the soft delete field)

All remaining now , is to use the SoftDelete trait into your model :

```
<?php

namespace MyApp\Models;

use SigmaPHP\DB\ORM\Model;
use SigmaPHP\DB\Traits\SoftDelete;

class Product extends Model
{
    use SoftDelete;
}

```
<br>

Now you're ready to use the soft delete , the default `delete` will automatically use the soft delete field.

```
<?php

$product = $productModel->find(7);

// this will only update the deleted_at field
$product->delete();
```
<br>

The SoftDelete trait also add multiple useful methods to work with the soft deleted models :

```
<?php

// by default all soft models won't appear in the search results
// we use `withTrashed` method to return the soft deleted model in the results

$allProducts = $productModel->withTrashed()->all();

// to check if model is soft deleted 
$isDeleted = $productModel->isTrashed();

// to restore a soft deleted model 
$product = $productModel->isTrashed();

// to fetch only soft deleted models
$onlyTrashedProducts = $productModel->onlyTrashed()->all();

// to delete a soft deleted model permanently you will set the
// `$forceHardDelete` option to true in the delete method
$product->delete(true);

```
<br>

Finally you can allow all of your queries to always return soft deleted models by setting the `fetchTrashed` property to true in the model :

```
<?php

namespace MyApp\Models;

use SigmaPHP\DB\ORM\Model;
use SigmaPHP\DB\Traits\SoftDelete;

class Product extends Model
{
    use SoftDelete;

    protected $fetchTrashed = true;
}

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