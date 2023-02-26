## Migrations

You can use migrations to design , organize and control your database schemas smoothly , all in pure PHP , the SigmaPHP-DB migrations has variety of methods to help you write your database migrations.

To create new migration file , run the following command in your terminal:

```
php ./vendor/bin/sigma-db create:migration ProductsTable
```

This command will create a new migration file "ProductsTableMigration.php" in "/path/to/your/project/database/migrations" directory.

To change the default path for your migrations files , open the config file "database.php" (You can check [Configuration](https://github.com/agashe/SigmaPHP-DB/blob/master/README.md#Configurations) section , for more info).

Then update the migrations path , to your path of choice :

```
'path_to_migrations'  => '/path/to/my/schemas',
```

Now open "ProductsTableMigration.php" , you will see the default template for the migrations files :

```
<?php

use SigmaPHP\DB\Migrations\Migration;

class ProductsTableMigration extends Migration
{
    /**
     * @return void
     */
    public function up()
    {
        // up method
    }

    /**
     * @return void
     */
    public function down()
    {
        // down method
    }
}
```

You write all of your statements to design your schema in the `up()` method , and your write all the statements for rollback into `down()` method , so for example if you created a new table in the `up()` method , you write the code to drop it in the `down()` method.


```
<?php

use SigmaPHP\DB\Migrations\Migration;

class ProductsTableMigration extends Migration
{
    /**
     * @return void
     */
    public function up()
    {
        $this->createTable('products', ..........);
    }

    /**
     * @return void
     */
    public function down()
    {
        $this->dropTable('products');
    }
}
```

To execute your database migrations , run the following command:

```
// run all migration files
php ./vendor/bin/sigma-db migrate

// run specific migration file
php ./vendor/bin/sigma-db migrate ProductsTableMigration
```

## Available Methods 

In this section you will find all methods provided by SigmaPHP-DB migrations , and you access all of them using `$this` , in your migration class.

### Table Methods

1- Create new Table: <br>

```
$this->createTable(
    'products',
    [
        ['name' => 'id', 'type' => 'bigint', 'primary' => true],
        ['name' => 'title', 'type' => 'varchar', 'size' => 25],
        ['name' => 'price', 'type' => 'decimal'],
    ],
    [
        'engine' => 'innodb',
        'comment' => 'this is products table'
    ]
);
```

Accept 3 parameters , first `$name` a string contains the table name, `$fields` a multi dimensional array for the columns and finally `$options` an associated array , contains the table options.

To set the table fields , you add the name , type and options to the `$fields` array. SigmaPHP-DB supports all MySQL default data types for the fields. Here some of the common types : 

* Strings : char , varchar , text , longtext , enum , blob
* Numeric : tinyint , bool , smallint , int , bigint , float , decimal
* Date/Time : date , datetime , timestamp , time , year 

For the full list of data types , Check please the [Official MySQL Documentation](https://dev.mysql.com/doc/refman/8.0/en/data-types.html)

<br>
And you can find all available fields options in the table below:

<table border>
    <thead>
        <tr>
            <td>Option</td>
            <td>Field Type</td>
            <td>Description</td>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>primary</td>
            <td>All types</td>
            <td>
                Select field as primary key
            </td>
        </tr>
        <tr>
            <td>size</td>
            <td>All types</td>
            <td>
                Set the field's size , for both numeric and strings types 
            </td>
        </tr>
        <tr>
            <td>not_null</td>
            <td>All types</td>
            <td>
                The field doesn't allow `NULL` values
            </td>
        </tr>
        <tr>
            <td>default</td>
            <td>All types</td>
            <td>
                Set default value for the field
            </td>
        </tr>
        <tr>
            <td>after</td>
            <td>All types</td>
            <td>
                Place the fields after another specific field
            </td>
        </tr>
        <tr>
            <td>comment</td>
            <td>All types</td>
            <td>
                Add comment to the field
            </td>
        </tr>
    </tbody>
</table>

<br>
In addition to fields options. Each table has 4 options:

<table border>
    <thead>
        <tr>
            <td>Option</td>
            <td>Description</td>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>engine</td>
            <td>
                Set the table engine , the default for MySQL is `innoDB`
            </td>
        </tr>
        <tr>
            <td>collation</td>
            <td>
                Set the table collation like `utf8mb4_unicode_ci`
            </td>
        </tr>
        <tr>
            <td>comment</td>
            <td>
                Add a comment to the table.
            </td>
        </tr>
        <tr>
            <td>row_format</td>
            <td>
                Set the table row format , the default is `DYNAMIC`
            </td>
        </tr>
    </tbody>
</table>



