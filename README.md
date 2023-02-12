# SigmaPHP-DB
SigmaPHP-DB is a collection of PHP Database Tools. That support primarily MySQL RDBMS (Other RDBMS support will be added in future). Including migrations with rollback , seeding ,
query builder , ORM and much more , all these features can be used through an elegant CLI script in your terminal.

## Installation

``` 
composer require agashe/sigmaphp-db
```

## Configurations

After installation , you should create a new config file , to include your database connection parameters , and also edit other options like migrations / seeders paths.

To generate new config file , run the following command :

```
php ./vendor/bin/sigma-db create:config
```
A new config file with name **database.php** wil be created in the root of your project's directory.

You can simply change the name and the location of the config file , but this will require you , to pass the config file path , when use the CLI script :

```
php ./vendor/bin/sigma-db migrate --config=/path/to/my-config.php
```

## Documentation
- Migrations
- Seeding
- Query Builder
- ORM

## CLI Usage

In the table below , you can find all available commands :

<table border>
    <thead>
        <tr>
            <td>Command</td>
            <td>Description</td>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>create:config {path}</td>
            <td>
                Create new config file, if no path was provided , a
                default config file (database.php) <br> will be created 
                in the root of the project's folder.
            </td>
        </tr>
        <tr>
            <td>create:migration {migration name}</td>
            <td>
                Create new migration file. It's recommended for the migration file
                to follow class naming <br> rules , like using only nouns and PascalCase .. etc. Also no need to add .php extension. <br>
                It will be included automatically.
            </td>
        </tr>
        <tr>
            <td>create:seeder {seeder name}</td>
            <td>
                Create new seeder file. Please note that , the same rules for naimg the migration file <br> is also applied to the seeder.
            </td>
        </tr>
        <tr>
            <td>drop</td>
            <td>
                Drop all tables in the database. A confirmation message will ask you to confirm <br> before executing this command.
            </td>
        </tr>
        <tr>
            <td>fresh</td>
            <td>
                Drop all tables in the database. then will run
                all migrations and seed the database. <br> (will ask for confirmation)
            </td>
        </tr>
        <tr>
            <td>help</td>
            <td>
                Print a list by all available commands.
            </td>
        </tr>
        <tr>
            <td>migrate {migration name}</td>
            <td>
                Run all migrations files. You can pass migration file name , to run specific migration.
            </td>
        </tr>
        <tr>
            <td>rollback {date}</td>
            <td>
                Rollback latest migration. or choose specific date
                to rollback to.
            </td>
        </tr>
        <tr>
            <td>seed {seeder name}</td>
            <td>
                Run seeders. or run specific seeder.
            </td>
        </tr>
        <tr>
            <td>version</td>
            <td>
                Print the current version of SigmaPHP-DB Package.
            </td>
        </tr>
        <tr>
            <td>truncate</td>
            <td>
                Delete the data in all tables. (will ask for confirmation)
            </td>
        </tr>
    </tbody>
</table>

And here few examples on how to use the commands:


```
php ./vendor/bin/sigma-db create:migration UsersTable
// will create UsersTableMigration.php file into /path/to/migrations

php ./vendor/bin/sigma-db create:seed UsersRolesSeeder
// run seeder UsersRolesSeeder.php

php ./vendor/bin/sigma-db create:rollback 2023-1-20
// rollback all migrations up to 2023-1-20 , the migrations running dates all saved into the migrations logs table (default name is db_logs). And of course you can change it in the config file

php ./vendor/bin/sigma-db drop
// to drop all tables

php ./vendor/bin/sigma-db fresh --config=/path/to/db-testing-config.php
// to drop all tables then migrate and seed , and in this example we assume that you have put the config in your path of choice
```


## License
(SigmaPHP-DB) released under the terms of the MIT license.
