# Installation

Before using MPTT, you need to activate and configure Kohana's database module, and create a database table to perform MPTT operations on.

## Module Dependencies

 - Kohana database

## Module Activation

Activate Kohana's database module and the MPTT module in application/bootstrap.php.

## Database Configuration

Make sure you properly configure Kohana's database module in order to connect and use the database you wish to perform MPTT operations on.

## Database Table Requirements

To use MPTT, you need a database table with the following columns:

 - id (int) - a unique id
 - lft (int) - a node's left value
 - rgt (int) - a node's right value

If you wish to use scopes, you must also add this column:

 - scope (int) - a node's scope

You can add as many other columns as you wish depending on your intended use of MPTT.

## Example Table

A table of categories using scopes with a product count for each category:

 - id (int) - a unique id
 - lft (int) - a node's left value
 - rgt (int) - a node's right value
 - scope (int) - a node's scope
 - category (string) - the category's name
 - num_products (int) - the number of products in the category
