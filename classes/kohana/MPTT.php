<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Adds basic MPTT functionality to Kohana.
 *
 * @module_version  1.0
 * @Kohana_version  3.3.0
 * @author          Pierre-Emmanuel Lévesque
 * @date            July 15th, 2013
 * @dependencies    Kohana database module
 */
class Kohana_MPTT {

	/**
	 * @var string  Current table name.
	 */
	public $table;

	/**
	 * @var mixed   Current scope.
	 */
	public $scope;

	/**
	 * @var array   Sibling relationships.
	 */
	protected $_sibling_relationships = array(
		'after',
	);

	/**
	 * @var array   Child relationships.
	 */
	protected $_child_relationships = array(
		'first child of',
	);

	/**
	 * Constructor
	 *
	 * Optionally sets the table and scope upon initializing the class.
	 *
	 * @param   string   table [def: NULL]
	 * @param   int      scope [def: NULL]
	 * @return  void
	 */
	public function __construct($table = NULL, $scope = NULL)
	{
		$this->table = $table;
		$this->scope = $scope;
	}

	/**
	 * Creates a root node
	 *
	 * @param   array    custom data array (column => value, …) [def: NULL]
	 * @return  mixed    root id, or FALSE on failure
	 *
	 * @throws  Kohana_Exception   A root node already exists.
	 */
	public function create_root($data = array())
	{
		// Make sure there is no root node.
		if ($this->has_root())
			throw new Kohana_Exception('A root node already exists.');

		// System data.
		$sys_data = array('lft' => 1, 'rgt' => 2);

		// Add scope to system data.
		$this->scope !== NULL AND $sys_data['scope'] = $this->scope;

		// Merge custom data with system data.
		$data = array_merge($sys_data, $data);

		// Create the root node.
		list($insert_id, $affected_rows) = DB::insert($this->table, array_keys($data))
			->values(array_values($data))
			->execute();

		return ($affected_rows > 0) ? $insert_id : FALSE;
	}

	/**
	 * Inserts a node structure at a given position.
	 *
	 * $data accepts two formats:
	 * 
	 * 1 - array(column => value, column => value, ...)
	 * 2 - array(array(column => value, column => value), array(column => value,…)...)
	 *
	 * When passing numerous nodes in data, lft and rgt values
	 * must be included to specify the structure.
	 * In this case, the lft of the root node is always 1.
	 * Their position will automatically be offset when inserting.
	 *
	 * array(
	 *   array('lft' => 1, 'rgt' => 6),
	 *   array('lft' => 2, 'rgt' => 5),
	 *   array('lft' => 3, 'rgt' => 4),
	 * );
	 *
	 * Table specific data is added normally as colum value pairs.
	 * Columns that are omitted will fallback to their database default values.
	 *
	 * @param   array    data
	 * @param   string   relationship to insert with
	 * @param   int      node id to insert to
	 * @return  mixed    insert result array, or FALSE on failure
	 *
	 * @uses    get_root_node()
	 * @uses    _create_gap()
	 * @uses    _check_tree()
	 *
	 * @throws  Kohana_Exception   You must create a root before inserting data.
	 */
	public function insert($data, $relationship, $insert_node_id)
	{
		$inserted = FALSE;

		// Make sure we have a root node.
		if ( ! $this->has_root())
			throw new Kohana_Exception('You must create a root before inserting data.');

		// Make sure data is an array of arrays.
		! is_array(reset($data)) AND $data = array($data);

		// Create the gap for insertion.
		if($gap_lft = $this->_create_gap($relationship, $insert_node_id, count($data) * 2))
		{
			$offset = $gap_lft - 1;

			foreach($data as $node)
			{
				// Add lft and rgt for single inserts.
				if (count($data) == 1)
				{
					$node['lft'] = 1;
					$node['rgt'] = 2;
				}

				// Add scope.
				$this->scope !== NULL AND $node['scope'] = $this->scope;

				// Add node offsets.
				$node['lft'] = $node['lft'] + $offset;
				$node['rgt'] = $node['rgt'] + $offset;

				// Insert the data.
				DB::insert($this->table, array_keys($node))
					->values(array_values($node))
					->execute();

				$inserted = TRUE;
			}
		}

		$inserted AND $inserted = $this->_check_tree();

		return $inserted;
	}

	/**
	 * Moves a node and its children.
	 *
	 * @param   int      node id
	 * @param   string   relationship to move with ('after', 'first child of')
	 * @param   int      node id to move to
	 * @param   bool     moved
	 *
	 * @uses    get_node()
	 * @uses    _create_gap()
	 * @uses    _update_position()
	 * @uses    _check_tree()
	 * @throws  Kohana_Exception   A node cannot be moved unto itself.
	 * @throws  Kohana_Exception   The root node cannot be moved.
	 * @throws  Kohana_Exception   A parent cannot become a child of its own child.
	 */
	public function move($node_id, $relationship, $to_node_id)
	{
		$moved = FALSE;

		// Don't allow a node to be moved unto itself.
		if ($node_id == $to_node_id)
			throw new Kohana_Exception('A node cannot be moved unto itself.');

		// Get the node we are moving and the one we are moving to.
		if ($node = $this->get_node($node_id) AND $to_node = $this->get_node($to_node_id))
		{
			// Don't allow the root node to be moved.
			if ($node['lft'] == 1)
				throw new Kohana_Exception('The root node cannot be moved.');

			// Don't allow a parent to become its own child.
			if (
				in_array($relationship, $this->_child_relationships) AND
				($node['lft'] < $to_node['lft'] AND $node['rgt'] > $to_node['rgt'])
			)
				throw new Kohana_Exception('A parent cannot become a child of its own child.');

			// Kohana_Exception('root node cannot have siblings') is thown in _create_gap().

			// Calculate the size of the gap. (number of node positions we are moving)
			$gap_size = (1 + (($node['rgt'] - ($node['lft'] + 1)) / 2)) * 2;

			// Create the gap to move to.
			if ($this->_create_gap($relationship, $to_node_id, $gap_size))
			{
				// Adjust the node position if it was affected by the gap.
				if ($to_node['rgt'] < $node['lft'])
				{
					$node['lft'] = $node['lft'] + $gap_size;
					$node['rgt'] = $node['rgt'] + $gap_size;
				}

				// Calculate the increment based on the relationship.
				switch ($relationship)
				{
					case 'first child of':
						$increment = $to_node['lft'] + 1 - $node['lft'];
					break;
					case 'after':
						$increment = $to_node['rgt'] + 1 - $node['lft'];
					break;

					// Kohana_Exception('not a supported relationship') is thown in _create_gap().
				}

				// Move the node and its children into the gap.
				$this->_update_position(array('lft', 'rgt'), $increment, array(
					array('lft', '>=', $node['lft']),
					array('rgt', '<=', $node['rgt']),
				));

				// Close the gap created by the moved nodes.
				$limit = $node['lft'] - 1;
				$increment = $gap_size * -1;
				$this->_update_position('lft', $increment, array('lft', '>', $limit));
				$this->_update_position('rgt', $increment, array('rgt', '>', $limit));

				// Make sure the restructured tree is valid.
				if ($this->_check_tree())
				{
					$moved = TRUE;
				}
			}
		}

		return $moved;
	}

	/**
	 * Deletes nodes and their children.
	 *
	 * @param   mixed   node id or array of node ids to delete
	 * @return  mixed   deleted ids, or FALSE on failure
	 *
	 * @uses    get_tree()
	 * @uses    get_node()
	 * @uses    _where_scope()
	 * @uses    _update_position()
	 * @uses    _check_tree()
	 */
	public function delete($node_ids)
	{
		// Make sure node_ids is an array.
		! is_array($node_ids) AND $node_ids = array($node_ids);

		$deleted_ids = array();

		// Loop through all the node ids to delete.
		foreach ($node_ids as $node_id)
		{
			// Get the node to delete.
			$node = $this->get_node($node_id);

			$ids_to_delete = array();

			// Make sure we have a tree.
			if ($tree = $this->get_tree())
			{
				$tree = $tree->as_array();

				// Loop the tree and delete ids.
				foreach ($tree as $k => $v)
				{
					if ($v['lft'] >= $node['lft'] AND $v['rgt'] <= $node['rgt'])
					{
						// Save the ids to delete.
						$ids_to_delete[] = $v['id'];

						// Remove ids that will be deleted from the tree.
						unset($tree[$k]);
					}
				}
			}
			else
			{
				break;
			}

			// Process the deletions.
			if ( ! empty($ids_to_delete))
			{
				// Delete the node and its children.
				$query = DB::delete($this->table);

				foreach ($ids_to_delete as $id_to_delete)
				{
					$query->or_where('id', '=', $id_to_delete);
				}

				$num_deletions = $this->_where_scope($query)->execute();

				// We have deletions.
				if ($num_deletions)
				{
					// Save the newly deleted ids.
					$deleted_ids = array_merge($deleted_ids, $ids_to_delete);

					// Close the gap created by the deletion.
					$increment = ($num_deletions * 2) * -1;
					$this->_update_position('lft', $increment, array('lft', '>', $node['lft']));
					$this->_update_position('rgt', $increment, array('rgt', '>', $node['lft']));
				}
			}
		}

		$deleted_ids = array_unique($deleted_ids);

		if ( ! $this->_check_tree())
		{
			$deleted_ids = FALSE;
		}

		return $deleted_ids;
	}

	/**
	 * Gets a node from a node id.
	 *
	 * @param   int      node id
	 * @return  mixed    node array, or FALSE if node does not exist
	 *
	 * @uses    _where_scope()
	 * @caller  move()
	 * @caller  delete()
	 * @caller  get_tree()
	 * @caller  get_node_value()
	 * @caller  _create_gap()
	 */
	public function get_node($node_id)
	{
		$query = DB::select()
			->from($this->table)
			->where('id', '=', $node_id);

		$query = $this->_where_scope($query);

		return $query->execute()->current();
	}

	/**
	 * Gets the root node
	 *
	 * @return  mixed    root node array, or FALSE if root does not exist.
	 *
	 * @uses    _where_scope()
	 * @caller  insert()
	 */	
	public function get_root_node()
	{
		$query = DB::select()
			->from($this->table)
			->where('lft', '=', 1);

		$query = $this->_where_scope($query);

		return $query->execute()->current();
	}

	/**
	 * Gets the root id
	 *
	 * @return  mixed    root id, or FALSE if root does not exist.
	 *
	 * @uses    get_rood_node()
	 */	
	public function get_root_id()
	{
		$root = $this->get_root_node();

		return isset($root['id']) ? $root['id'] : FALSE;
	}

	/**
	 * Checks if the tree has a root.
	 *
	 * @return  bool   has root
	 *
	 * @uses    get_root_node()
	 */
	public function has_root()
	{
		return (bool) $this->get_root_node();
	}

	/**
	 * Gets the tree with an auto calculated depth column.
	 *
	 * @param   int      root id (start from a given root) [def: NULL]
	 * @return  SQL obj  tree obj, or FALSE on failure
	 *
	 * @uses    get_node()
	 * @caller  delete()
	 * @caller  get_family_values()
	 * @caller  _check_tree()
	 */
	public function get_tree($root_id = NULL)
	{
		$tree = FALSE;

		if (($root_id == NULL AND $this->has_root()) OR $this->get_node($root_id))
		{
			$query = DB::select('*', array(DB::expr('COUNT(`parent`.`id`) - 1'), 'depth'))
				->from(array($this->table, 'parent'), array($this->table, 'child'))
				->where('child.lft', 'BETWEEN', DB::expr('`parent`.`lft` AND `parent`.`rgt`'))
				->group_by('child.id')
				->order_by('child.lft');

			if ($this->scope !== NULL)
			{
				$query->where('parent.scope', '=', $this->scope);
				$query->where('child.scope', '=', $this->scope);
			}

			if ($root_id !== NULL)
			{
				$query->where('child.lft', '>=', DB::select('lft')->from($this->table)->where('id', '=', $root_id));
				$query->where('child.rgt', '<=', DB::select('rgt')->from($this->table)->where('id', '=', $root_id));
			}

			$tree = $query->execute();
		}

		return $tree;
	}

	/**
	 * Checks if a tree is valid.
	 *
	 * Empty trees are considered valid.
	 *
	 * @param   none
	 * @return  bool    valid
	 *
	 * @uses    get_tree()
	 * @caller  insert()
	 * @caller  move()
	 * @caller  delete()
	 */
	protected function _check_tree()
	{
		$valid = TRUE;
		$current_depth;
		$ancestors = array();
		$positions = array();

		if ($tree = $this->get_tree())
		{
			// Loop through the tree.
			foreach ($tree->as_array() as $key => $node)
			{
				// Modify the ancestors on depth change.
				if (isset($current_depth))
				{
					if ($node['depth'] > $current_depth)
					{
						array_push($ancestors, $tree[$key-1]);
					}
					elseif ($node['depth'] < $current_depth)
					{
						for ($i=0; $i<$current_depth-$node['depth']; $i++)
						{
							array_pop($ancestors);
						}
					}
				}

				// If the node has a parent, set it.
				! empty($ancestors) AND $parent = $ancestors[count($ancestors)-1];

				/**
				 * Perform various checks on the node.
				 *
				 * 1) lft must be smaller than rgt.
				 * 2) lft and rgt cannot be used by other nodes.
				 * 3) A Child node must be inside its parent.
				 */
				if (
					/*1*/ ($node['lft'] >= $node['rgt']) OR
					/*2*/ (in_array($node['lft'], $positions) OR in_array($node['rgt'], $positions)) OR
					/*3*/ (isset($parent) AND ($node['lft'] <= $parent['lft'] OR $node['rgt'] >= $parent['rgt']))
				)
				{
					$valid = FALSE;
					break;
				}

				// Set the current depth.
				$current_depth = $node['depth'];

				// Save the positions.
				$positions[] = $node['lft'];
				$positions[] = $node['rgt'];
			}
		}

		// Apply further checks to non-empty trees.
		if ( ! empty($positions))
		{
			// Sort the positions.
			sort($positions);

			// Make sure the last position is not larger than needed.
			if ($positions[count($positions)-1] - $positions[0] + 1 != count($positions))
			{
				$valid = FALSE;
			}
		}

		return $valid;
	}

	/**
	 * Creates a gap in the tree.
	 *
	 * @param   string   relationship to gap with
	 * @param   int      node id to gap against
	 * @param   int      gap size (number of nodes * 2) [def: 2]
	 * @return  mixed    gap lft, FALSE on failure
	 *
	 * @uses    get_node()
	 * @uses    _sibling_relationships
	 * @uses    _update_position()
	 * @caller  insert()
	 * @caller  move()
	 * @throws  Kohana_Exception   Root node cannot have siblings
	 * @throws  Kohana_Exception   Relationship does not exist
	 */
	protected function _create_gap($relationship, $node_id, $size = 2)
	{
		$gap_lft = FALSE;

		// Get the node to move against.
		if ($node = $this->get_node($node_id))
		{
			// Don't allow the root node to have siblings.
			if ($node['lft'] == 1 AND in_array($relationship, $this->_sibling_relationships))
				throw new Kohana_Exception('The root node cannot have siblings');

			// Get parameters depending on the relationship.
			switch ($relationship)
			{
				case 'first child of':
					$limit = $node['lft'];
					$gap_lft = $node['lft'] + 1;
				break;
				case 'after':
					$limit = $node['rgt'];
					$gap_lft = $node['rgt'] + 1;
				break;
				default:
					// Throw an exception if the relationship doesn't exist.
					throw new Kohana_Exception(':relationship is not a supported relationship.',
						array(':relationship' => $relationship));
				break;
			}

			// Update the node positions to create the gap.
			$this->_update_position('lft', $size, array('lft', '>', $limit));
			$this->_update_position('rgt', $size, array('rgt', '>', $limit));
		}

		return $gap_lft;
	}

	/**
	 * Updates lft and/or rgt position columns with where clauses.
	 *
	 * Columns accepts two formats:
	 *
	 * 1 - string 'lft' or 'rgt'
	 * 2 - array('lft', 'rgt')
	 *
	 * Where conditions accept two formats:
	 * 
	 * 1 - array(column, value, condition)
	 * 2 - array(array(column, value, condition, array(…))
	 *
	 * @param   mixed   column(s) (see above)
	 * @param   int     increment
	 * @param   array   where condition(s) (see above)
	 * @return  void
	 *
	 * @uses    _where_scope()
	 * @caller  move()
	 * @caller  delete()
	 * @caller  _create_gap()
	 */
	protected function _update_position($columns, $increment, $where)
	{
		// Make sure columns is an array.
		! is_array($columns) AND $columns = array($columns);

		// Make sure where is an array of arrays.
		! is_array($where[0]) AND $where = array($where);

		// Build and run the query.
		$query = DB::update($this->table);

		foreach ($columns as $column)
		{
			$query->set(array($column => DB::expr("`".$column."` + ".$increment)));
		}

		foreach ($where as $condition)
		{
			$query->where($condition[0], $condition[1], $condition[2]);
		}

		$this->_where_scope($query)->execute();
	}

	/**
	 * Adds a where scope clause in the query.
	 *
	 * @param   SQL obj   query
	 * @return  SQL obj   query
	 *
	 * @caller  delete()
	 * @caller  get_node()
	 * @caller  _update_position()
	 */
	protected function _where_scope($query)
	{
		if ($this->scope !== NULL)
		{
			$query->where('scope', '=', $this->scope);
		}

		return $query;
	}

} // Kohana_MPTT
