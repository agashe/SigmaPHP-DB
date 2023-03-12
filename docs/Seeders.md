## Seeders

After creating your database schema with Migrations. Now you need a way to fill your tables with data , to start working on your project
, and here comes the seeders , a seeder , is a class which is used to fill a table with data , for example , adding roles and permissions , set products categories or add some users to login to your app.  

To create a new seeder file , use the following command in your terminal , please note that you don't need to add seeder keyword to the seeder name , since it will be added automatically.

```
php ./vendor/bin/sigma-db create:seeder Products
```

This command will create a new seeder file "ProductsSeeder.php" in "/path/to/your/project/database/seeders" directory.

To change the default path for your seeder files , open the config file "database.php" (You can check the [Configurations](https://github.com/agashe/SigmaPHP-DB/blob/master/README.md#Configurations) section , for more info).

Then update the seeders path , to your path of choice :

```
'path_to_seeders'  => '/path/to/my/seeders',
```

After opening "ProductsSeeder.php" you will notice the default seeder template , which includes the `run()` method , all your statements will goes inside it.

```
<?php

use SigmaPHP\DB\Seeders\Seeder;

class ProductsSeeder extends Seeder
{
    /**
     * @return void
     */
    public function run()
    {
        // your instructions 
    }
}
```
To run all of your seeders , once you are done writing them , use the following command:

```
// run all seeders
php ./vendor/bin/sigma-db seed
```
Or you can choose to run specific seeder:

```
// run specific seeder
php ./vendor/bin/sigma-db seed ProductsSeeder
```

## Available Methods 

<br>

1- Insert data:

```
<?php

use SigmaPHP\DB\Seeders\Seeder;

class ProductsSeeder extends Seeder
{
    /**
     * @return void
     */
    public function run()
    {
        $this->insert(
            'products',
            [
                ['name' => 'Cell Phone', 'price' => 100.00],
                ['name' => 'Laptop', 'price' => 500.00],
                ['name' => 'TV', 'price' => 1000.00],
            ]
        );
    }
}
```
<br>

2- Update data:

```
<?php

use SigmaPHP\DB\Seeders\Seeder;

class ProductsSeeder extends Seeder
{
    /**
     * @return void
     */
    public function run()
    {
        $this->update(
            'products',
            ['price' => 200.00] // update values
            ['name' => 'Cell Phone'] // search condition
        );
    }
}
```
<br>

3- Delete data:

```
<?php

use SigmaPHP\DB\Seeders\Seeder;

class ProductsSeeder extends Seeder
{
    /**
     * @return void
     */
    public function run()
    {
        $this->delete(
            'products',
            ['name' => 'Cell Phone'] // search condition
        );
    }
}
```

Please Note : you can ignore the condition , but this will delete all the data in the table.

<br>

4- Use the query builder:

SigmaPHP-DB has a query builder , that you can already use inside the seeder , to perform more operations on your seeder.

```
<?php

use SigmaPHP\DB\Seeders\Seeder;

class ProductsSeeder extends Seeder
{
    /**
     * @return void
     */
    public function run()
    {
        $category = $this->queryBuilder
            ->table('product_categories')
            ->where('id', '=', 5)
            ->get()
        
        $this->update(
            'products',
            [
                'category' => $category['name']
            ]
        );
    }
}
```

To know more about the query builder , and how to use it , kindly please check the [Query Builder Documentation](https://github.com/agashe/SigmaPHP-DB/blob/master/docs/QueryBuilder.md)