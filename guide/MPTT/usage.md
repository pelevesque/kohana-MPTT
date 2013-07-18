# Usage

MPTT is used to perform MPTT operations on a desired database table. Before continuing, make sure your modules and database are installed and configured properly. The following examples will assume you are using MPTT on a categories table.

## Initialization

    // Use the categories table.
    $mptt = new MPTT('categories');

    // Use the categories table with a scope of 1.
    $mptt = new MPTT('categories', 1);

## Modify Table And Scope

    // Change the table and scope.
    $mptt->table = 'pages';
    $mptt->scope = 2;

    // Remove the use of a scope.
    $mptt->scope = NULL;

## Create A Root Node

You will get an exception under these circumstances:

 - If a root already exists.

~~~~
    // Create a basic root.
    $root_id = $mptt->create_root();

    // Add a description for the category field.
    $root_id = $mptt->create_root(array('category' => 'root'));
~~~~

When you don't set a custom field's value, the database should be setup to fallback to a default. In the above basic root example, the category field should fallback to blank or NULL.

## Insert A Node Or Nodes

You can use two relationships when inserting. This covers all insertion points.

 - after
 - first child of

You will get an exception under these circumstances:

 - If you try to insert data before creating a node.
 - If you try to insert a sibling for the root node.

~~~~
    // Insert a node.
    $data = array('category' => 'joe', 'num_products' => '1');
    $inserted_ids = $mptt->insert($data, 'first child of', $root_id);

    // Insert many nodes. (node structure)
    $data = array(
        array('lft' => 1, 'rgt' => 6, 'category' => 'shoes', 'num_products' => 10),
        array('lft' => 2, 'rgt' => 3, 'category' => 'sport', 'num_products' => 4),
        array('lft' => 4, 'rgt' => 5, 'category' => 'casual', 'num_products' => 6),
    );
    $inserted_ids = $mptt->insert($data, 'after', 2);
~~~~

You can insert entire node structures using the method above. The lft and rgt values are used to determing the structure. lft should always start at one, it will be offset when inserted at the proper position.

## Move A Node

You can use two relationships when moving. This covers all movements.

 - after
 - first child of

You will get an exception under these circumstances:

 - If you try to move a node unto itself.
 - If you try to move the root.
 - If you try to make a parent become the child of its own child.

~~~~
    // Move node id 4 after node id 5
    $moved = $mptt->move(4, 'after', 5);

    // Move node id 5 to make it the first child of node id 6
    $moved = $mptt->move(5, 'first child of', 6);
~~~~

## Delete A Node Or Nodes

To delete a node or nodes, you simply pass a node id, or an array of node ids to the delete method. If the node has children, they will be deleted automatically.

    // Delete the node with an id of 2.
    $deleted_ids = $mptt->delete(2);

    // Delete nodes with ids of 2 and 3.
    $deleted_ids = $mptt->delete(array(2, 3));

## Utility methods

    // Gets a node.
    $node_array = $mptt->get_node($node_id);

    // Gets the root node.
    $root_node_array = $mptt->get_root_node();

    // Gets the root id.
    $root_id = $mptt->get_root_id();

    // Checks to see if there is a root.
    $has_root = $mptt->has_root();

    // Returns the tree object.
    $tree_obj = $mptt->get_tree();

    // Returns the tree obj starting from a specific node.
    $tree_obj = $mptt->get_tree($node_id);

    // Checks if a tree is valid.
    $valid_tree = $mptt->validate_tree();

## Database Transactions (insert, move, delete)

MPTT does not create database transactions automatically. An operation should never fail unless there is a database problem. However, if an operation does fail, this means the tree's structure might no longer be valid. For this reason, it is crucial that you create a transaction for every insert, move, and delete command you perform on the tree.

Transactions were not direcly included in MPTT to allow the ability to perform other queries in addition to your MPTT queries. Since MYSQL does not permit nested transactions, this was the most flexible option.

Use validate_tree() method to confirm if a transaction should be commited.

    // Start the transaction.
    DB::query(NULL, "BEGIN WORK")->execute();

    // Perform queries.
    $mptt->delete(array(3, 4));
    $mptt->move(2, 'first child of', 5);
    $mptt->insert(array('category' => 'blouses', 'num_products' => 10), 'after', 5);

    // More queries here.

    // Commit the transaction if the tree is still valid.
    if ($mptt->validate_tree())
    {
        // Commit the transaction.
        DB::query(NULL, "COMMIT")->execute();
    }
