## Query Builder

SigmaPHP-DB offers a simple , lightweight and elegant query builder to help you write your SQL queries. SigmaPHP-DB QueryBuilder is fast and simple in it's core , it converts your PHP statement into SQL statement , that runs directly on your Database , and fetch the result.

To start using SigmaPHP-DB QueryBuilder , just include the vendor and then in your code , create new instance of QueryBuilder class , the class require PDO connection instance.

```
<?php

include 'vendor/autoload.php';

use SigmaPHP\DB\QueryBuilders\QueryBuilder;

$connection = new \PDO(
    "mysql:host=localhost;
    dbname=testing",
    'root',
    'root'
);

$queryBuilder = new QueryBuilder($connection);

$queryBuilder->table('users')
    ->select(['name', 'age'])
    ->get();
```

## Available Methods 

<br>

1- table(string $tableName): <br>
This method is used to set the table that the query will be run on. In all your queries , you will start be setting the table.

```
// getting all users
$queryBuilder->table('users')->getAll();

// also you can set an alias 
$queryBuilder->table('users as u')->getAll();

```
<br>

2- select(array $fields): <br>
The `select` method , is used to set the fields and you can set  aliases for the fields. Also we can use the aggregate functions inside the `select` method.

```
// select the fields to be included in the query
$queryBuilder
    ->table('users')
    ->select(['name', 'email'])
    ->getAll();

// set alias for field 
$queryBuilder
    ->table('users')
    ->select([
        'name as n', 
        'email as e'
    ])
    ->getAll();

// use aggregate methods 
$queryBuilder
    ->table('users')
    ->select(['count(*) as users_count'])
    ->get();

$queryBuilder
    ->table('users')
    ->select(['avg(age) as users_age_avg'])
    ->get();

```
<br>

3- where(string $column, string $operator, string $value): <br>

You can't use more than one `where` in the same query , so to use and / or in your query, you can use `andWhere` / `orWhere`.

```
// basic conditions
$queryBuilder
    ->table('users')
    ->select(['name', 'email', 'address'])
    ->where('id', '=', 5)
    ->get();

$queryBuilder
    ->table('users')
    ->where('age', '>=', 18)
    ->getAll();

// like condition
$queryBuilder
    ->table('users')
    ->where('name', 'like', '%test%')
    ->getAll();

// use is/is not
$queryBuilder
    ->table('users')
    ->where('address', 'is not', 'null')
    ->getAll();

// use date methods
$queryBuilder
    ->table('users')
    ->where(
        'date(joined_at)', '=', 'date_sub(now(),interval 3 year)'
    )
    ->getAll();
```
<br>

4- andWhere(string $column, string $operator, string $value): <br>

```
$queryBuilder
    ->table('users')
    ->select(['name', 'email', 'address'])
    ->where('age', '>=', 18)
    ->andWhere('address', 'is not', 'null')
    ->getAll();
```
<br>

5- orWhere(string $column, string $operator, string $value): <br>

```
$queryBuilder
    ->table('users')
    ->select(['name', 'email', 'address'])
    ->where('address', 'is not', 'null')
    ->orWhere('city', '=', 'test')
    ->getAll();
```
<br>

6- whereBetween(string $column, int $min, int $max): <br>

```
$queryBuilder
    ->table('users')
    ->whereBetween('age', 10, 15)
    ->getAll();
```
<br>

7- whereIn(string $column, array $values): <br>

```
$queryBuilder
    ->table('users')
    ->select(['name', 'email'])
    ->whereIn('city', ['test1', 'test2'])
    ->getAll();
```
<br>

8- having(string $column, string $operator, string $value): <br>

```
$queryBuilder
    ->table('users')
    ->select(['name', 'email', 'avg(age) as age_avg])
    ->having('age_avg', '<', '20')
    ->getAll();
```