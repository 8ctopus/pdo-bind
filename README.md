# PDOWrap

<!---
[![Latest Stable Version](http://poser.pugx.org/8ctopus/pdo-wrap/v)](https://packagist.org/packages/8ctopus/pdo-wrap)
[![Total Downloads](http://poser.pugx.org/8ctopus/pdo-wrap/downloads)](https://packagist.org/packages/8ctopus/pdo-wrap)
[![License](http://poser.pugx.org/8ctopus/pdo-wrap/license)](https://packagist.org/packages/8ctopus/pdo-wrap)
[![PHP Version Require](http://poser.pugx.org/8ctopus/pdo-wrap/require/php)](https://packagist.org/packages/8ctopus/pdo-wrap)
-->

A simple PDO helper that automatically binds parameters that turns this:

```php
$sql = <<<SQL
SELECT
    name, colour, calories
FROM
    fruits
WHERE
    calories < :calories AND
    colour = :colour
SQL;

$sth = $db->prepare($sql);

$sth->bindValue('calories', 150, PDO::PARAM_INT);
$sth->bindValue('colour', 'red', PDO::PARAM_STR);
$sth->execute();
```

into this:

```php
$sql = <<<SQL
SELECT
    name, colour, calories
FROM
        fruits
WHERE
    calories < :calories AND
    colour = :colour
SQL;

$sth = $db->prepare($sql);
$sth->execute([
    'calories' => 150,
    'colour' => 'red',
]);
```

# A word of caution

Automatic binding can be dangerous if not used carefully. Here's an example:

```sql
DELETE FROM
    `fruits`
WHERE
    `name` = :name
```

In this statement, if name is a string then all is fine, however if name happens to be zero then we have a big problem as all the table records are deleted!

```sql
DELETE FROM
    `fruits`
WHERE
    `name` = 0
```

To avoid this, do not use user input without sanitizing it first.

## features

- automatically binds parameters

## install and demo

Since the package is not published on packagist, you need to add the repository to `composer.json`:

```json
{
    "repositories": [
        {
            "type": "git",
            "url": "https://github.com/8ctopus/pdo-wrap"
        }
    ],
}
```

```sh
composer require 8ctopus/pdo-wrap
```

## tests

```sh
composer test
```
