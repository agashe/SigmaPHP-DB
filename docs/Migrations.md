## Migrations

You can use migrations to design , organize and control your database schemas smoothly , all in pure PHP , the SigmaPHP-DB migrations has variety of methods to help you write your database migrations.

To create new migration file , run the following command in your terminal:

```
php ./vendor/bin/sigma-db create:migration ProductsTable
```

This command will create a new migration file "ProductsTableMigration.php" in "/path/to/your/project/database/migrations" directory.

To change the default path for your migrations files , open the config file "database.php" (You can check the [Configurations](https://github.com/agashe/SigmaPHP-DB/blob/master/README.md#Configurations) section , for more info).

Then update the migrations path , to your path of choice :

```
'path_to_migrations' => '/path/to/my/schemas',
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
Another unique feature that SigmaPHP-DB supports is the nested directory migrations , so assume you have the following nested migration files:

```
parent-dir
    |
    -- sub-dir-1
        |
        -- sub-dir-2
            |
            -- MyMigration.php
```

Once you run the `migrate` command , SigmaPHP-DB will traverse all sub-directories in the root migrations path , and will automatically run it , so no need to any additional configurations , this will allow you to oraganize your migration files easily.

```
// MyMigration wil be detected and executed 
php ./vendor/bin/sigma-db migrate

// run you could point to specific path relative to the root migrations path
php ./vendor/bin/sigma-db migrate parent-dir/sub-dir-1/sub-dir-2/MyMigration
```

## Available Methods 

In this section you will find all methods provided by SigmaPHP-DB migrations , and you access all of them using `$this` , in your migration class.

### Table Methods

1- Create new table: <br>

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
                Place the fields after another specific field (only works with addColumn)
            </td>
        </tr>
        <tr>
            <td>comment</td>
            <td>All types</td>
            <td>
                Add comment to the field
            </td>
        </tr>
        <tr>
            <td>unsigned</td>
            <td>Numeric</td>
            <td>
                Set numeric field as unsigned
            </td>
        </tr>
        <tr>
            <td>precision</td>
            <td>Numeric</td>
            <td>
                Set the precision for numeric field
            </td>
        </tr>
        <tr>
            <td>scale</td>
            <td>Numeric</td>
            <td>
                Work with the precision option for numeric field
            </td>
        </tr>
        <tr>
            <td>values</td>
            <td>Enum</td>
            <td>
                Set the values for ENUM data type
            </td>
        </tr>
        <tr>
            <td>auto_update</td>
            <td>Date and Time</td>
            <td>
                Set the default value for date and time fields equals to
                CURRENT_TIMESTAMP , and also on update it will set the field's value to CURRENT_TIMESTAMP , this useful when you want to implement `updated_at` field
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
                Add a comment to the table
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

<br>

Also SigmaPHP-DB in addition to the default data types provides some special data types :

<table border>
    <tbody>
        <tr>
            <td>UUID</td>
            <td>
                Instead of making your id field just an integer, you can use UUID field , to use auto generated unique identifier. By default MySQL didn't allow using functions as DEFAULT value until version 8.0.13 , so make sure you're using MySQL version 8.0.13 or above to use this data type. By default any field with type UUID , will be set as primary key for the table
            </td>
        </tr>
        <tr>
            <td>soft_delete</td>
            <td>
                This type will add `deleted_at` to the table , that can be used along side the SoftDelete trait in the models to enable the soft delete functionality in the ORM
            </td>
        </tr>
        <tr>
            <td>timestamps</td>
            <td>
                Add `created_at` and `updated_at` fields to the table
            </td>
        </tr>
    </tbody>
</table>

And here an example :

```
$this->createTable(
    'special_types',
    [
        ['name' => 'id', 'type' => 'uuid'],
        ['name' => 'soft_delete'],
        ['name' => 'timestamps']
    ]
);
```

<br>

2- Update table: <br>

```
$this->updateTable(
    'products',
    [
        'comment' => 'this is products table'
    ]
);
```
This method updates the table properties only , not the fields , to update the table fields , use the columns methods. So for example you can use this method to change the the comment on the table , or the table's collation ... etc

<br>

3- Rename table: <br>

```
$this->renameTable(
    'old_table_name',
    'new_table_name'
);
```
<br>

4- Check if table exists in database: <br>

```
$this->checkTable('users');
```
<br>

5- Change table's primary key: <br>

```
$this->changeTablePrimaryKey(
    'users',
    'old_table_primary_key',
    'new_table_primary_key'
);
```
<br>

6- Drop table: <br>

```
$this->dropTable('comments');
```
<br>

### Columns Methods

<br>

1- Add new column to table: <br>

```
$this->addColumn(
    'users',
    'phone',
    [
        'type' => 'varchar',
        'size' => 25
    ]
);
```
<br>

2- Update column: <br>

```
$this->updateColumn(
    'users',
    'phone',
    [
        'size' => 50,
        'not_null' => true
    ]
);
```
<br>

3- Rename column: <br>

```
$this->renameColumn(
    'users',
    'phone', // old field name
    'phone_number' // new field name
);
```
<br>

4- Check if column exists in table: <br>

```
$this->checkColumn(
    'users',
    'phone'
);
```
<br>

5- Drop column: <br>

```
$this->dropColumn(
    'users',
    'phone'
);
```
<br>

### Index Methods

<br>

1- Add new index to table: <br>

```
$this->addIndex(
    'table_name',
    'index_name',
    ['columns' .....],
    'index_type',
    ['fields_order']
);
```
Where the parameters for the `addIndex` method are :

<table border>
    <tbody>
        <tr>
            <td>table_name</td>
            <td>
                The table to create the index for
            </td>
        </tr>
        <tr>
            <td>index name</td>
            <td>
                Set the index name
            </td>
        </tr>
        <tr>
            <td>columns</td>
            <td>
                The column/s to be included in the index
            </td>
        </tr>
        <tr>
            <td>index_type</td>
            <td>
                An index can be one of 4 types in MySQL : normal , unique , fulltext or descending
            </td>
        </tr>
        <tr>
            <td>order</td>
            <td>
                You can set an order for each column in the index 
            </td>
        </tr>
    </tbody>
</table>

So for example :
```
$this->addIndex(
    'users',
    'user_index',
    ['name', 'email'],
    'normal',
    [
        'name' => 'desc',
        'email' => 'asc'
    ]
);
```
<br>

2- Check if index exists in table: <br>

```
$this->checkIndex(
    'users',
    'user_index'
);
```

<br>

3- Drop index: <br>

```
$this->dropIndex(
    'users',
    'user_index'
);
```
<br>

### Foreign Key Methods
<br>

1- Add new foreign key to table: <br>

```
$this->addForeignKey(
    'constraint',
    'localTable',
    ['localIds' ......],
    'referenceTable',
    ['foreignIds' ......],    
    ['options']
);
```
`addForeignKey` method accepts the following parameters :

<table border>
    <tbody>
        <tr>
            <td>constraint</td>
            <td>
                The foreign key's name
            </td>
        </tr>
        <tr>
            <td>localTable</td>
            <td>
                The table to create the foreign key on
            </td>
        </tr>
        <tr>
            <td>localIds</td>
            <td>
                The primary fields on the local table
            </td>
        </tr>
        <tr>
            <td>referenceTable</td>
            <td>
                The referenced table , which is connected to the local table by the foreign key
            </td>
        </tr>
        <tr>
            <td>foreignIds</td>
            <td>
                the primary fields on the reference table
            </td>
        </tr>
        <tr>
            <td>options</td>
            <td>
                An array contains all the different options for the key. For example NO_ACTION on delete. 
            </td>
        </tr>
    </tbody>
</table>

So for example :
```
$this->addForeignKey(
    'test_foreign_key',
    'test',
    'id',
    'test2',
    'id',
    [
        'on_delete' => 'NO ACTION',
        'on_update' => 'NO ACTION',
    ]
);
```
<br>

2- Check if foreign key exists in table: <br>

```
$this->checkForeignKey(
    'users',
    'user_foreign_key'
);
```
<br>

3- Drop foreign key: <br>

```
$this->dropForeignKey(
    'users',
    'user_foreign_key'
);
```
<br>


## Default migration templates 

The SigmaPHP CLI tool provides default templates for both create new table and add new column operations , since those are the most common operation , we usually use with migrations.

To use this feature , all what we have to do , is to name our migration file using the template naming pattern :

```
CreateXXXXXXXXTable , where your XXXXXXXX is your table name

AddColumnXXXXXXXXToYYYYYYYYTable , where XXXXXXXX is the column name and YYYYYYYY is your table name
```
<br>

So to generate `create table` template , we run the following command :

```
php ./vendor/bin/sigma-db create:migration CreatePostsTable
```
<br>

The migration file `CreatePostsTableMigration.php` will be created with the following content :

```
<?php

use SigmaPHP\DB\Migrations\Migration;

class CreatePostsTableMigration extends Migration
{
    /**
     * @return void
     */
    public function up()
    {
        $this->createTable(
            'posts',
            [
                ['name' => 'id', 'type' => 'bigint', 'primary' => true],
                /**
                 * add your columns !
                 */
                ['name' => 'timestamps']
            ]
        );
    }

    /**
     * @return void
     */
    public function down()
    {
        $this->dropTable('posts');
    }
}
```
<br>

and for `add new column` template :

```
php ./vendor/bin/sigma-db create:migration AddColumnSizeToPostImagesTable
```
<br>

Will create `AddColumnSizeToPostImagesTableMigration.php`

```
<?php

use SigmaPHP\DB\Migrations\Migration;

class AddColumnSizeToPostImagesTableMigration extends Migration
{
    /**
     * @return void
     */
    public function up()
    {
        $this->addColumn(
            'post_images',
            'size',
            [
                'type' => 'SET_COLUMN_TYPE',
                // other options
            ]
        );
    }

    /**
     * @return void
     */
    public function down()
    {
        $this->dropColumn('post_images', 'size');
    }
}
```
<br>

