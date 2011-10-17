# XMPTT

XMPTT is a simple implementation of MPTT (Modified Preorder Tree Traversal). It provides ways to create, move, and delete tree nodes in a database table, and has a few utility methods.

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

# Usage

XMPTT is used to perform MPTT operations on a desired database table. Before continuing, make sure your modules and database are installed and configured properly.

## Initialization

	// Use the pages table.
	$mptt = new MPTT('pages');

	// Use the pages table with a scope of 1.
	$mptt = new MPTT('pages', 1);

## Modify the table and the scope

	// Change the table and scope.
	$mptt->table = 'categories';
	$mptt->scope = 2;

	// Remove the use of a scope.
	$mptt->scope = NULL;

## Insert a node

	$data = array('name' => 'joe', 'active' => '1');
	$mptt->insert($data);

## Move a node and its children

Two relationships are supported for the moving of nodes:

 + after
 + first child of

These two relationships permit all the possible types of allowed movements. There are a few illegal movements that will genarate an exception:

 + If you try to move a node unto itself.
 + If you try to move the root.
 + If you try to make a parent become the child of its own child.

~~~~
	// Move node id 4 after node id 5
	$moved = $mptt->move(4, 'after', 5);

	// Move node id 5 to make it the first child of node id 6
	$moved = $mptt->move(5, 'first child of', 6);
~~~~

## Delete node(s)

To delete a node or nodes, you simply pass a node id, or an array of node ids to the delete method. If the node has children, they will be deleted automatically.

If you attempt to delete the root id, it will be silently ignored. If you delete every node except the root id, the root id will automatically be deleted as well.

	// Delete the node with an id of 2.
	$deleted = $mptt->delete(2);

	// Delete nodes with ids of 2 and 3.
	$deleted = $mptt->delete(array(2, 3));

## Transactions (insert, move, delete)

XMPTT does not create transactions automatically, but it does indicate whether an operation has succeeded or failed. At the end of an insert, move, or delete operation, the tree is checked to make sure its construction is still valid. If the check fails, the method will return a value of FALSE.

An operation should never fail unless there is a database problem. If an operation does fail, this means the tree's construction might no longer be valid. For this reason, it is crucial that you create a transaction for every insert, move, and delete command you perform on the tree.

Transactions were not direcly included in XMPTT to allow the ability to perform other queries in addition to your XMPTT queries. Since MYSQL does not permit nested transactions, this was the most flexible option.

	// Start the transaction.
	DB::query(NULL, "BEGIN WORK")->execute();

	// You can make other queries here.

	$deleted = $mptt->delete(array(3, 4));

	if ($deleted !== FALSE)
	{

		// You can make other queries here.

		// Commit the transaction.
		DB::query(NULL, "COMMIT")->execute();
	}

For the insert and move method you can simply check for a FALSE value like so; `$inserted != FALSE`. This does not work for the deleted method because it will return an empty array if no deletions have been made. In such a case, the tree will remain valid and the operation can still be considered successful. You must thus use `$deleted !== FALSE` to know if a deletion was truly successful.
