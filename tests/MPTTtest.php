<?php defined('SYSPATH') or die('No direct script access.');
/**
 * This is a dirty homebrewed test controller for MPTT.
 *
 * It was created very quickly, but it works.
 * To use it, you must install MPTT's MYSQL test scheme,
 * and Kohana's database module.
 */
class Controller_MPTTtest extends Controller {

    public function action_index()
    {
        ///////////////////////////////////////
        // CONTRUCTOR TEST
        ///////////////////////////////////////
        $mptt = new MPTT();
        $t1 = $mptt->table;
        $s1 = $mptt->scope;
        $mptt = new MPTT('categories');
        $t2 = $mptt->table;
        $s2 = $mptt->scope;
        $mptt = new MPTT('pools', 2);
        $t3 = $mptt->table;
        $s3 = $mptt->scope;
        if (
            $t1 == NULL AND $s1 == NULL AND
            $t2 == 'categories' AND $s2 == NULL AND
            $t3 == 'pools' AND $s3 == 2
        )
        {
            $constructorTEST = 'PASSED';
        }
        else
        {
            $constructorTEST = 'FAILED';
        }

        ///////////////////////////////////////
        // TABLE VARIABLE TEST
        ///////////////////////////////////////
        $mptt->table = 'gyms';
        if ($mptt->table == 'gyms') { $tableVarTEST = 'PASSED'; } else { $tableVarTEST = 'FAILED'; }

        ///////////////////////////////////////
        // SCOPE VARIABLE TEST
        ///////////////////////////////////////
        $mptt->scope = 8;
        if ($mptt->scope == 8) { $scopeVarTEST = 'PASSED'; } else { $scopeVarTEST = 'FAILED'; }

        ///////////////////////////////////////
        // CREATE_ROOT() TEST
        ///////////////////////////////////////
        $this->erase_T('MPTT_test1');
        $mptt = new MPTT('MPTT_test1');
        $cr1 = $mptt->create_root(array('name' => 'root'));
        $this->erase_T('MPTT_test2');
        $mptt = new MPTT('MPTT_test2', 2);
        $cr2 = $mptt->create_root(array('name' => 'root'));
        if ($cr1 == TRUE AND $cr2 == TRUE)
        { $createrootTEST = 'PASSED'; } else { $createrootTEST = 'FAILED'; }

        ///////////////////////////////////////
        // INSERT() TEST
        ///////////////////////////////////////
        $this->erase_T('MPTT_test1');
        $mptt = new MPTT('MPTT_test1');
        $id = $mptt->create_root(array('name' => 'root'));
        $mptt->insert(array('name' => 'mike'), 'first child of', $id);
        $t1 = $mptt->get_tree()->as_array();
        $t1r = array(
            array('id' => 1, 'lft' => 1, 'rgt' => 4, 'name' => 'root', 'depth' => 0),
            array('id' => 2, 'lft' => 2, 'rgt' => 3, 'name' => 'mike', 'depth' => 1),
        );
        $mptt->insert(array('name' => 'jim'), 'first child of', 2);
        $t2 = $mptt->get_tree()->as_array();
        $t2r = array(
            array('id' => 1, 'lft' => 1, 'rgt' => 6, 'name' => 'root', 'depth' => 0),
            array('id' => 2, 'lft' => 2, 'rgt' => 5, 'name' => 'mike', 'depth' => 1),
            array('id' => 3, 'lft' => 3, 'rgt' => 4, 'name' => 'jim', 'depth' => 2),
        );
        $mptt->insert(array('name' => 'carl'), 'after', 3);
        $t3 = $mptt->get_tree()->as_array();
        $t3r = array(
            array('id' => 1, 'lft' => 1, 'rgt' => 8, 'name' => 'root', 'depth' => 0),
            array('id' => 2, 'lft' => 2, 'rgt' => 7, 'name' => 'mike', 'depth' => 1),
            array('id' => 3, 'lft' => 3, 'rgt' => 4, 'name' => 'jim', 'depth' => 2),
            array('id' => 4, 'lft' => 5, 'rgt' => 6, 'name' => 'carl', 'depth' => 2),
        );
        $mptt->insert(array(
            array('lft' => 1, 'rgt' => 6, 'name' => 'venice'),
            array('lft' => 2, 'rgt' => 3, 'name' => 'france'),
            array('lft' => 4, 'rgt' => 5, 'name' => 'coffee')), 
            'after', 4
        );
        $t4 = $mptt->get_tree()->as_array();	
        $t4r = array(
            array('id' => 1, 'lft' => 1, 'rgt' => 14, 'name' => 'root', 'depth' => 0),
            array('id' => 2, 'lft' => 2, 'rgt' => 13, 'name' => 'mike', 'depth' => 1),
            array('id' => 3, 'lft' => 3, 'rgt' => 4, 'name' => 'jim', 'depth' => 2),
            array('id' => 4, 'lft' => 5, 'rgt' => 6, 'name' => 'carl', 'depth' => 2),
            array('id' => 5, 'lft' => 7, 'rgt' => 12, 'name' => 'venice', 'depth' => 2),
            array('id' => 6, 'lft' => 8, 'rgt' => 9, 'name' => 'france', 'depth' => 3),
            array('id' => 7, 'lft' => 10, 'rgt' => 11, 'name' => 'coffee', 'depth' => 3),
        );
        $mptt->insert(array('name' => 'florance'), 'first child of', 7);
        $t5 = $mptt->get_tree()->as_array();
        $t5r = array(
            array('id' => 1, 'lft' => 1, 'rgt' => 16, 'name' => 'root', 'depth' => 0),
            array('id' => 2, 'lft' => 2, 'rgt' => 15, 'name' => 'mike', 'depth' => 1),
            array('id' => 3, 'lft' => 3, 'rgt' => 4, 'name' => 'jim', 'depth' => 2),
            array('id' => 4, 'lft' => 5, 'rgt' => 6, 'name' => 'carl', 'depth' => 2),
            array('id' => 5, 'lft' => 7, 'rgt' => 14, 'name' => 'venice', 'depth' => 2),
            array('id' => 6, 'lft' => 8, 'rgt' => 9, 'name' => 'france', 'depth' => 3),
            array('id' => 7, 'lft' => 10, 'rgt' => 13, 'name' => 'coffee', 'depth' => 3),
            array('id' => 8, 'lft' => 11, 'rgt' => 12, 'name' => 'florance', 'depth' => 4),
        );
        $this->erase_T('MPTT_test2');
        $mptt = new MPTT('MPTT_test2', 1);
        $id = $mptt->create_root(array('name' => 'root'));
        $mptt->insert(array('name' => 'mike'), 'first child of', $id);
        $t6 = $mptt->get_tree()->as_array();
        $t6r = array(
            array('id' => 1, 'lft' => 1, 'rgt' => 4, 'scope' => 1, 'name' => 'root', 'depth' => 0),
            array('id' => 2, 'lft' => 2, 'rgt' => 3, 'scope' => 1, 'name' => 'mike', 'depth' => 1),
        );
        $mptt->scope = 2;
        $id = $mptt->create_root(array('name' => 'root'));
        $mptt->insert(array('name' => 'carl'), 'first child of', $id);
        $t7 = $mptt->get_tree()->as_array();
        $t7r = array(
            array('id' => 3, 'lft' => 1, 'rgt' => 4, 'scope' => 2, 'name' => 'root', 'depth' => 0),
            array('id' => 4, 'lft' => 2, 'rgt' => 3, 'scope' => 2, 'name' => 'carl', 'depth' => 1),
        );
        if ($t1 == $t1r AND $t2 == $t2r AND $t3 == $t3r AND $t4 == $t4r AND $t5 == $t5r AND $t6 == $t6r AND $t7 == $t7r)
        { $insertTEST = 'PASSED'; } else { $insertTEST = 'FAILED'; }		

        ///////////////////////////////////////
        // MOVE() TEST
        ///////////////////////////////////////
        $this->erase_T('MPTT_test1');
        $mptt = new MPTT('MPTT_test1');
        $id = $mptt->create_root(array('name' => 'root'));
        $mptt->insert(array('name' => 'mike'), 'first child of', $id);
        $mptt->insert(array('name' => 'jim'), 'after', 2);
        $mptt->move(2, 'after', 3);
        $t1 = $mptt->get_tree()->as_array();
        $t1r = array(
            array('id' => 1, 'lft' => 1, 'rgt' => 6, 'name' => 'root', 'depth' => 0),
            array('id' => 3, 'lft' => 2, 'rgt' => 3, 'name' => 'jim', 'depth' => 1),
            array('id' => 2, 'lft' => 4, 'rgt' => 5, 'name' => 'mike', 'depth' => 1),
        );
        $mptt->move(3, 'first child of', 2);
        t2 = $mptt->get_tree()->as_array();
        $t2r = array(
            array('id' => 1, 'lft' => 1, 'rgt' => 6, 'name' => 'root', 'depth' => 0),
            array('id' => 2, 'lft' => 2, 'rgt' => 5, 'name' => 'mike', 'depth' => 1),
            array('id' => 3, 'lft' => 3, 'rgt' => 4, 'name' => 'jim', 'depth' => 2),
        );
        $this->erase_T('MPTT_test2');
        $mptt = new MPTT('MPTT_test2', 1);
        $id = $mptt->create_root(array('name' => 'root1'));
        $mptt->scope = 2;
        $id = $mptt->create_root(array('name' => 'root2'));
        $mptt->insert(array('name' => 'mike'), 'first child of', $id);
        $mptt->insert(array(
            array('lft' => 1, 'rgt' => 10, 'name' => 'paul'),
            array('lft' => 2, 'rgt' => 3, 'name' => 'mark'),
            array('lft' => 4, 'rgt' => 9, 'name' => 'eric'),
            array('lft' => 5, 'rgt' => 8, 'name' => 'maude'),
            array('lft' => 6, 'rgt' => 7, 'name' => 'venessa')),
            'first child of', 3
        );
        $mptt->move(6, 'first child of', 5);
        $t3 = $mptt->get_tree()->as_array();
        $t3r = array(
            array('id' => 2, 'lft' => 1, 'rgt' => 14, 'scope' => 2, 'name' => 'root2', 'depth' => 0),
            array('id' => 3, 'lft' => 2, 'rgt' => 13, 'scope' => 2, 'name' => 'mike', 'depth' => 1),
            array('id' => 4, 'lft' => 3, 'rgt' => 12, 'scope' => 2, 'name' => 'paul', 'depth' => 2),
            array('id' => 5, 'lft' => 4, 'rgt' => 11, 'scope' => 2, 'name' => 'mark', 'depth' => 3),
            array('id' => 6, 'lft' => 5, 'rgt' => 10, 'scope' => 2, 'name' => 'eric', 'depth' => 4),
            array('id' => 7, 'lft' => 6, 'rgt' => 9, 'scope' => 2, 'name' => 'maude', 'depth' => 5),
            array('id' => 8, 'lft' => 7, 'rgt' => 8, 'scope' => 2, 'name' => 'venessa', 'depth' => 6),
        );
        $mptt->move(4, 'after', 3);
        $t4 = $mptt->get_tree()->as_array();
        $t4r = array(
            array('id' => 2, 'lft' => 1, 'rgt' => 14, 'scope' => 2, 'name' => 'root2', 'depth' => 0),
            array('id' => 3, 'lft' => 2, 'rgt' => 3, 'scope' => 2, 'name' => 'mike', 'depth' => 1),
            array('id' => 4, 'lft' => 4, 'rgt' => 13, 'scope' => 2, 'name' => 'paul', 'depth' => 1),
            array('id' => 5, 'lft' => 5, 'rgt' => 12, 'scope' => 2, 'name' => 'mark', 'depth' => 2),
            array('id' => 6, 'lft' => 6, 'rgt' => 11, 'scope' => 2, 'name' => 'eric', 'depth' => 3),
            array('id' => 7, 'lft' => 7, 'rgt' => 10, 'scope' => 2, 'name' => 'maude', 'depth' => 4),
            array('id' => 8, 'lft' => 8, 'rgt' => 9, 'scope' => 2, 'name' => 'venessa', 'depth' => 5),
        );
        $t5 = $mptt->move(10, 'after', 11);
        $t5r = FALSE;
        $t6 = $mptt->move(2, 'after', 14);
        $t6r = FALSE;
        $t7 = $mptt->move(14, 'after', 2);
        $t7r = FALSE;
        if ($t1 == $t1r AND $t2 == $t2r AND $t3 == $t3r AND $t4 == $t4r AND $t5 == $t5r AND $t6 == $t6r AND $t7 == $t7r)
        { $moveTEST = 'PASSED'; } else { $moveTEST = 'FAILED'; }

        ///////////////////////////////////////
        // DELETE() TEST
        ///////////////////////////////////////
        $this->erase_T('MPTT_test1');
        $mptt = new MPTT('MPTT_test1');
        $id = $mptt->create_root(array('name' => 'root'));
        $mptt->insert(array('name' => 'mike'), 'first child of', $id);
        $mptt->insert(array('name' => 'carl'), 'after', 2);
        $mptt->delete(2);
        $t1 = $mptt->get_tree()->as_array();
        $t1r = array(
            array('id' => 1, 'lft' => 1, 'rgt' => 4, 'name' => 'root', 'depth' => 0),
            array('id' => 3, 'lft' => 2, 'rgt' => 3, 'name' => 'carl', 'depth' => 1),
        );
        $mptt->insert(array(
            array('lft' => 1, 'rgt' => 6, 'name' => 'paris'),
            array('lft' => 2, 'rgt' => 5, 'name' => 'france'),
            array('lft' => 3, 'rgt' => 4, 'name' => 'abraham')),
            'first child of', 3
        );
        $mptt->delete(5);
        $t2 = $mptt->get_tree()->as_array();
        $t2r = array(
            array('id' => 1, 'lft' => 1, 'rgt' => 6, 'name' => 'root', 'depth' => 0),
            array('id' => 3, 'lft' => 2, 'rgt' => 5, 'name' => 'carl', 'depth' => 1),
        array('id' => 4, 'lft' => 3, 'rgt' => 4, 'name' => 'paris', 'depth' => 2),
        );
        $mptt->insert(array('name' => 'joe'), 'after', 4);
        $mptt->insert(array('name' => 'clarice'), 'first child of', 7);
        $mptt->delete(array(4, 8));
        $t3 = $mptt->get_tree()->as_array();
        $t3r = array(
            array('id' => 1, 'lft' => 1, 'rgt' => 6, 'name' => 'root', 'depth' => 0),
            array('id' => 3, 'lft' => 2, 'rgt' => 5, 'name' => 'carl', 'depth' => 1),
            array('id' => 7, 'lft' => 3, 'rgt' => 4, 'name' => 'joe', 'depth' => 2),
        );
        $mptt->delete(1);
        $t4 = $mptt->get_tree()->as_array();
        $t4r = array();
        $this->erase_T('MPTT_test2');
        $mptt = new MPTT('MPTT_test2', 1);
        $id = $mptt->create_root(array('name' => 'root1'));
        $mptt->insert(array('name' => 'mike'), 'first child of', $id);
        $mptt->scope = 2;
        $id = $mptt->create_root(array('name' => 'root2'));
        $mptt->insert(array('name' => 'carl'), 'first child of', $id);
        $mptt->scope = 1;
        $mptt->delete(2);
        $t5 = $mptt->get_tree()->as_array();
        $t5r = array(
            array('id' => 1, 'lft' => 1, 'rgt' => 2, 'scope' => 1, 'name' => 'root1', 'depth' => 0)
        );
        $t6 = $mptt->delete(2);
        $t6r = array();
        if ($t1 == $t1r AND $t2 == $t2r AND $t3 == $t3r AND $t4 == $t4r AND $t5 == $t5r AND $t6 == $t6r)
        { $deleteTEST = 'PASSED'; } else { $deleteTEST = 'FAILED'; }

        ///////////////////////////////////////
        // GET_NODE() TEST
        ///////////////////////////////////////
        $this->erase_T('MPTT_test1');
        $mptt = new MPTT('MPTT_test1');
        $m1 = $mptt->get_node(1);
        $mptt->create_root(array('name' => 'root'));
        $mptt->insert(array('name' => 'mike'), 'first child of', 1);
        $m2 = $mptt->get_node(2);
        if ($m1 == FALSE AND is_array($m2) AND $m2['id'] == 2 AND $m2['lft'] == 2 AND $m2['rgt'] == 3 AND $m2['name'] == 'mike')
        { $getnodeTEST = 'PASSED'; } else { $getnodeTEST = 'FAILED'; }

        ///////////////////////////////////////
        // GET_ROOT_NODE() TEST
        ///////////////////////////////////////
        $this->erase_T('MPTT_test1');
        $mptt = new MPTT('MPTT_test1');
        $r1 = $mptt->get_root_node();
        $mptt->create_root(array('name' => 'root'));
        $r2 = $mptt->get_root_node();
        $this->erase_T('MPTT_test2');
        $mptt = new MPTT('MPTT_test2', 2);
        $mptt->create_root(array('name' => 'root'));
        $mptt->scope = 1;
        $r3 = $mptt->get_root_node();
        $mptt->scope = 2;
        $r4 = $mptt->get_root_node();
        if ($r1 == FALSE AND
            is_array($r2) AND $r2['id'] == 1 AND $r2['lft'] == 1 AND $r2['rgt'] == 2 AND $r2['name'] == 'root' AND
            $r3 == FALSE AND
            is_array($r4) AND $r4['id'] == 1 AND $r4['lft'] == 1 AND $r4['rgt'] == 2 AND $r4['scope'] == 2 AND $r4['name'] == 'root'
        )
        { $getrootnodeTEST = 'PASSED'; } else { $getrootnodeTEST = 'FAILED'; }

        ///////////////////////////////////////
        // GET_ROOT_ID() TEST
        ///////////////////////////////////////
        $this->erase_T('MPTT_test1');
        $mptt = new MPTT('MPTT_test1');
        $r1 = $mptt->get_root_id(); 
        $mptt->create_root(array('name' => 'root'));
        $r2 = $mptt->get_root_id();
        $this->erase_T('MPTT_test2');
        $mptt = new MPTT('MPTT_test2', 2);
        $mptt->create_root(array('name' => 'root'));
        $mptt->scope = 1;
        $r3 = $mptt->get_root_id();
        $mptt->scope = 2;
        $r4 = $mptt->get_root_id();
        if ($r1 == FALSE AND $r2 == 1 AND $r3 == FALSE AND $r4 == 1)
        { $getrootidTEST = 'PASSED'; } else { $getrootidTEST = 'FAILED'; }

        ///////////////////////////////////////
        // HAS_ROOT() TEST
        ///////////////////////////////////////
        $this->erase_T('MPTT_test1');
        $mptt = new MPTT('MPTT_test1');
        $hr1 = $mptt->has_root();
        $mptt->create_root(array('name' => 'root'));
        $mptt->insert(array('name' => 'mike'), 'first child of', 1);
        $hr2 = $mptt->has_root();
        $this->erase_T('MPTT_test2');
        $mptt = new MPTT('MPTT_test2', 2);
        $mptt->create_root(array('name' => 'root'));
        $mptt->insert(array('name' => 'mike'), 'first child of', 1);
        $hr3 = $mptt->has_root();
        $mptt->scope = 1;
        $hr4 = $mptt->has_root();
        if ($hr1 == FALSE AND $hr2 == TRUE AND $hr3 == TRUE AND $hr4 == FALSE)
        { $hasrootTEST = 'PASSED'; } else { $hasrootTEST = 'FAILED'; }

        ///////////////////////////////////////
        // GET_TREE() TEST
        ///////////////////////////////////////
        $this->erase_T('MPTT_test2');
        $mptt = new MPTT('MPTT_test2', 1);
        $t1 = $mptt->get_tree()->as_array();		
        $mptt->create_root(array('name' => 'root'));
        $mptt->insert(array('name' => 'mike'), 'first child of', 1);
        $t2 = $mptt->get_tree()->as_array();
        if ($t1 == array() AND
            $t2[0]['id'] == 1 AND $t2[0]['lft'] == 1 AND $t2[0]['rgt'] == 4 AND $t2[0]['scope'] = 1 AND $t2[0]['name'] == 'root' AND
            $t2[1]['id'] == 2 AND $t2[1]['lft'] == 2 AND $t2[1]['rgt'] == 3 AND $t2[1]['scope'] = 1 AND $t2[1]['name'] == 'mike'
        )
        { $gettreeTEST = 'PASSED'; } else { $gettreeTEST = 'FAILED'; }

        ///////////////////////////////////////
        // TEST RESULTS
        ///////////////////////////////////////
        echo '<table border="1">';
        echo '<tr><th colspan="2">TESTS</th></tr>';
        echo '<tr><th>__contructor()</th><td>' . $constructorTEST . '</td></tr>';
        echo '<tr><th>table</th><td>' . $tableVarTEST . '</td></tr>';
        echo '<tr><th>scope</th><td>' . $scopeVarTEST . '</td></tr>';
        echo '<tr><th>create_root()</th><td>' . $createrootTEST . '</td></tr>';
        echo '<tr><th>insert()</th><td>' . $insertTEST . '</td></tr>';
        echo '<tr><th>move()</th><td>' . $moveTEST . '</td></tr>';
        echo '<tr><th>delete()</th><td>' . $deleteTEST . '</td></tr>';
        echo '<tr><th>get_node()</th><td>' . $getnodeTEST . '</td></tr>';
        echo '<tr><th>get_root_node()</th><td>' . $getrootnodeTEST . '</td></tr>';
        echo '<tr><th>get_root_id()</th><td>' . $getrootidTEST . '</td></tr>';
        echo '<tr><th>has_root()</th><td>' . $hasrootTEST . '</td></tr>';
        echo '<tr><th>get_tree()</th><td>' . $gettreeTEST . '</td></tr>';
        echo '</table>';
    }

    public function erase_T($table)
    {
        $query = DB::query(Database::DELETE, 'TRUNCATE TABLE ' . $table);
        $query->execute();
    }

} // End Controller_MPTTtest
