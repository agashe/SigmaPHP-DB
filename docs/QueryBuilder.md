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
<br>

SigmaPHP-DB QueryBuilder returns the result in associative array format. So in the example above the return value will be.

```
[
    'name' => 'john doe',
    'age' => 30
]
```

## Available Methods 

<br>

**1- table(string $tableName)** <br>
This method is used to set the table that the query will be run on. In all your queries , you will start be setting the table.

```
// getting all users
$queryBuilder->table('users')->getAll();

// also you can set an alias 
$queryBuilder->table('users as u')->getAll();

```
<br>

**2- select(array $fields)** <br>
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

**3- where(string $column, string $operator, string $value)** <br>

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

**4- andWhere(string $column, string $operator, string $value)** <br>

```
$queryBuilder
    ->table('users')
    ->select(['name', 'email', 'address'])
    ->where('age', '>=', 18)
    ->andWhere('address', 'is not', 'null')
    ->getAll();
```
<br>

**5- orWhere(string $column, string $operator, string $value)** <br>

```
$queryBuilder
    ->table('users')
    ->select(['name', 'email', 'address'])
    ->where('address', 'is not', 'null')
    ->orWhere('city', '=', 'test')
    ->getAll();
```
<br>

**6- whereBetween(string $column, int $min, int $max)** <br>

```
$queryBuilder
    ->table('users')
    ->whereBetween('age', 10, 15)
    ->getAll();
```
<br>

**7- whereIn(string $column, array $values)** <br>

```
$queryBuilder
    ->table('users')
    ->select(['name', 'email'])
    ->whereIn('city', ['test1', 'test2'])
    ->getAll();
```
<br>

**8- having(string $column, string $operator, string $value)** <br>

```
$queryBuilder
    ->table('users')
    ->select(['name', 'email', 'avg(age) as age_avg'])
    ->having('age_avg', '<', '20')
    ->getAll();
```
<br>

**9- distinct()** <br>

Remove duplicated results.

```
$queryBuilder
    ->table('users')
    ->select(['name'])
    ->distinct()
    ->getAll();
```
<br>

**10- limit(int $count, int $offset)** <br>

Set the number of results to be returned. And optionally you can set the offset to start fetching from.

```
// fetch first 5 users
$queryBuilder
    ->table('users')
    ->select(['name', 'email'])
    ->limit(5)
    ->getAll();

// fetch first 10 users starting from id = 15
$queryBuilder
    ->table('users')
    ->select(['name', 'email'])
    ->limit(10, 15)
    ->getAll();
```
<br>

**11- orderBy(array $columns)** <br>

You can set multiple fields for ordering , and set for each of them to sort ascending or descending.

```
$queryBuilder
    ->table('users')
    ->orderBy(['id asc', 'name desc'])
    ->getAll();
```
<br>

**12- groupBy(array $columns)** <br>

You can set multiple fields for grouping.

```
$queryBuilder
    ->table('users')
    ->groupBy(['id', 'name'])
    ->getAll();
```
<br>

**13- union(QueryBuilder $query, bool $all)** <br>

To union multiple query results , we can use the `union` method , which accept 2 method , first the query to union , second the `$all` flag , when set to true , it will allow all values including the duplicated ones , by default it's set to false. And it's always recommended to match the fields in both queries.

```
// we should have multiple queries to use union

$query1 = new QueryBuilder($connection);

// don't fetch the results yet
$query1
    ->table('customers')
    ->select(['id', 'name']);

$query2 = new QueryBuilder($connection);

$query2
    ->table('users')
    ->select(['id', 'name'])
    ->union($query1, true)
    ->getAll();
```
<br>

**12- join(
        string $table,
        string $column1,
        string $operator,
        string $column2,
        string $type
    )** <br>

You can join multiple tables using the `join` method , and you can set the tables and the condition to , but keep in mind that you should pass the fields names as `table.column` syntax , to avoid conflicts. And also the default join type is the inner join , use the the `$type` to set the join type. 

```
$this->queryBuilder
    ->table('users')
    ->select(['users.name as username', 'roles.name as role'])
    ->join('roles', 'users.role_id', '=', 'roles.id')
    ->getAll();

// you can also set tables aliases
$this->queryBuilder
    ->table('users as u')
    ->select(['u.name', 'r.name'])
    ->join('roles as r', 'u.role_id', '=', 'r.id')
    ->getAll();
```
<br>

**13- get()** <br>

Fetch single result from the query.

```
$queryBuilder->table('users')->get();
```
<br>

**14- getAll()** <br>

Fetch all results from the query.

```
$queryBuilder->table('users')->getAll();
```
<br>

*15- print()* <br>

You can use the `print` method to print the SQL query that will be executed , this method is useful for debugging , testing and optimization. 

```
echo $queryBuilder->table('users')->print();

// This will print
SELECT * FROM test; 
```