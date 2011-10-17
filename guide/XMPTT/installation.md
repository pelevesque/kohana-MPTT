# Installation

Before using XMPTT, you have to make sure to have certain modules installed and a properly configured database table.

## Module Dependencies

 - Kohana database

## Module Initialization

Initialize the kohana database module and XMPTT in application/bootstrap.php.

## Database Configuration

Make sure you configure kohana's database module properly so that you can connect and use the database you wish to perform MPTT operations on.

## Database Table Requirements

To use XMPTT with a database table, the table requires the following columns:

 - id (int) - a unique id
 - lft (int) - a node's left value
 - rgt (int) - a node's right value
 - depth (int) - a node's depth
 
If you wish to use scopes, you must also add this column:
 
 - scope (int) - a node's scope
 
You can add as many other columns as you want.
