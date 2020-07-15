<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Uses global cache to optimize queries on tree.
 */
class ilCachedTree extends ilTree
{
    /**
     * @var	\ilTree
     */
    protected $other;

    /**
     * @var \ilGlobalCache
     */
    protected $global_cache;

    /**
     * @var \ilObjectDataCache
     */
    protected $object_data_cache;

    /**
     * @var \ilObjectDefinition
     */
    protected $object_definition;

    /**
    * Constructor
    */
    public function __construct(
        \ilTree $other,
        \ilGlobalCache $global_cache,
        \ilObjectDataCache $object_data_cache,
        \ilObjectDefinition $object_definition,
        int $cache_shard_size = 200
    ) {
        $this->other = $other;
        $this->global_cache = $global_cache;
        $this->object_data_cache = $object_data_cache;
        $this->object_definition = $object_definition;
        $this->cache_shard_size = $cache_shard_size;

        if ($this->other->getTreeImplementation() instanceof \ilNestedSetTree) {
            throw new \LogicException("ilCachedTree only works with ilMaterializedPathTree");
        }
    }

    //--------------------------------------
    // CACHING
    //--------------------------------------

    /**
     * @var array
     */
    protected $cache_shards;

    /**
     * @var	int
     */
    protected $cache_shard_size;

    protected function getCachedChildren($node_id)
    {
        $data = $this->getCacheValue($node_id);
        return $data["children"];
    }

    protected function getCachedData($node_id)
    {
        $data = $this->getCacheValue($node_id);
        return $data["data"];
    }

    protected function hasCachedInfo($node_id)
    {
        $shard_id = $this->getCacheShardIdOf($node_id);
        if (!isset($this->cache_shards[$shard_id])) {
            $this->loadShard($shard_id);
        }
        return isset($this->cache_shards[$shard_id][$node_id]);
    }

    protected function getCacheShardIdOf($node_id)
    {
        return (int) floor($node_id / $this->cache_shard_size);
    }

    protected function getCacheKeyFor($shard_id)
    {
        $tree_id = $this->other->getTreeId();
        return "shard_{$tree_id}_{$shard_id}";
    }

    protected function getCacheValue($node_id)
    {
        $shard_id = $this->getCacheShardIdOf($node_id);
        if (!isset($this->cache_shards[$shard_id])) {
            $this->loadShard($shard_id);
        }
        return $this->cache_shards[$shard_id][$node_id];
    }

    protected function loadShardFromCache($shard_id)
    {
        $key = $this->getCacheKeyFor($shard_id);
        if ($this->global_cache->exists($key)) {
            $this->cache_shards[$shard_id] = $this->global_cache->get($key);
            return true;
        }
        return false;
    }

    protected function loadShard($shard_id)
    {
        if (!$this->loadShardFromCache($shard_id)) {
            $this->refreshShard($shard_id);
        }
    }

    protected function refreshShard($shard_id)
    {
        $data = ["i am" => "here"];
        $l = $shard_id * $this->cache_shard_size;
        $r = ($shard_id + 1) * $this->cache_shard_size;
        for ($i = $l; $i < $r; $i++) {
            if (!$this->other->isInTree($i)) {
                continue;
            }
            $data[$i] = [
                "children" => $this->other->getChilds($i),
                "data" => $this->other->getNodeData($i)
            ];
        }
        $this->cache_shards[$shard_id] = $data;
        $key = $this->getCacheKeyFor($shard_id);
        $this->global_cache->set($key, $data);
    }

    protected function purgeCache($node_id)
    {
        $affected_nodes = array_merge(
            [$node_id],
            $this->getSubTreeIds($node_id),
            $this->getPathId($node_id)
        );

        $purged_shards = [];

        foreach ($affected_nodes as $node) {
            $shard_id = $this->getCacheShardIdOf($node);
            if (in_array($shard_id, $purged_shards)) {
                continue;
            }
            $this->refreshShard($shard_id);
            $purged_shards[] = $shard_id;
        }
    }

    protected function purgeAll()
    {
        $this->global_cache->flush();
    }

    protected function addCurrentObjectData(array $nodes) : array
    {
        global $DIC;
        $ilUser = $DIC["ilUser"];
        $lng = $DIC["lng"];

        $ids = $this->getObjIdsFromNodes($nodes);

        $this->object_data_cache->preloadObjectCache($ids);

        foreach ($nodes as &$node) {
            $obj_id = $node["obj_id"];

            $type = $this->object_data_cache->lookupType($obj_id);
            $node["type"] = $type;
            $node["owner"] = $this->object_data_cache->lookupOwner($obj_id);
            $node["last_update"] = $this->object_data_cache->lookupLastUpdate($obj_id);
            $node["import_id"] = $this->object_data_cache->lookupImportId($obj_id);

            // look into ilTree from where I copied this mess
            $translation_type = $this->object_definition->getTranslationType($type);
            if ($translation_type == "sys") {
                if ($type == "rolf" && $node["obj_id"] !== ROLE_FOLDER_ID) {
                } else {
                    $node["title"] = $lng->txt("obj_{$type}");
                    $node["description"] = $lng->txt("obj_{$type}_desc");
                }
            } else {
                $node["title"] = $this->object_data_cache->lookupTitle($obj_id);
                $node["description"] = $this->object_data_cache->lookupDescription($obj_id);
            }

            if ($type == 'crsr' or $type == 'catr' or $type == 'grpr') {
                $node['title'] = ilContainerReference::_lookupTitle($node['obj_id']);
            }

            $node["desc"] = $node["description"];
        }

        return $nodes;
    }

    protected function getObjIdsFromNodes(array $nodes)
    {
        $ids = [];
        foreach ($nodes as $node) {
            yield $node["obj_id"];
        }
    }


    //--------------------------------------
    // CACHED METHODS
    //--------------------------------------

    /**
    * get child nodes of given node
    * @access	public
    * @param	integer		node_id
    * @param	string		sort order of returned childs, optional (possible values: 'title','desc','last_update' or 'type')
    * @param	string		sort direction, optional (possible values: 'DESC' or 'ASC'; defalut is 'ASC')
    * @return	array		with node data of all childs or empty array
    * @throws InvalidArgumentException
    */
    public function getChilds($a_node_id, $a_order = "", $a_direction = "ASC")
    {
        if (!$this->isInTree($a_node_id)) {
            return [];
        }

        if ($a_order !== "" || $a_direction !== "ASC") {
            return $this->other->getChilds($a_node_id, $a_order, $a_direction);
        }

        if (!isset($a_node_id)) {
            $message = "No node_id given!";
            throw new InvalidArgumentException($message);
        }

        $data = $this->getCachedChildren($a_node_id);

        return $this->addCurrentObjectData($data);
    }

    /**
    * get child nodes of given node by object type
    * @access	public
    * @param	integer		node_id
    * @param	string		object type
    * @return	array		with node data of all childs or empty array
    * @throws InvalidArgumentException
    */
    public function getChildsByType($a_node_id, $a_type)
    {
        $children = $this->getChilds($a_node_id);
        $nodes = [];
        foreach ($children as $node) {
            if ($node["type"] == $a_type) {
                // The "last" key is set on the last array entry by the original
                // getChilds-implementation. The original getChildsByType-impl
                // does not set that key. This is why I remove it here. I do not
                // really know if this changes a thing.
                if (isset($node["last"])) {
                    unset($node["last"]);
                }
                $nodes[] = $node;
            }
        }
        return $nodes;
    }

    /**
    * get child nodes of given node by object type
    * @access	public
    * @param	integer		node_id
    * @param	array		array of object type
    * @return	array		with node data of all childs or empty array
    * @throws InvalidArgumentException
    */
    public function getChildsByTypeFilter($a_node_id, $a_types, $a_order = "", $a_direction = "ASC")
    {
        if ($a_order !== "" || $a_direction !== "ASC") {
            return $this->other->getChildsByTypeFilter($a_node_id, $a_types, $a_order, $a_direction);
        }

        $children = $this->getChilds($a_node_id);
        $nodes = [];
        foreach ($children as $node) {
            if (in_array($node["type"], $a_types)) {
                // The "last" key is set on the last array entry by the original
                // getChilds-implementation. The original getChildsByType-impl
                // does not set that key. This is why I remove it here. I do not
                // really know if this changes a thing.
                if (isset($node["last"])) {
                    unset($node["last"]);
                }
                $nodes[] = $node;
            }
        }

        return $nodes;
    }

    /**
     * Get all ids of subnodes
     * @return
     * @param object $a_ref_id
     */
    public function getSubTreeIds($a_ref_id)
    {
        $node = $this->getNodeData($a_ref_id);
        return iterator_to_array(
            $this->getIdsFromNodes(
                $this->getSubTreeRecursive($node)
            )
        );
    }

    /**
    * get all nodes in the subtree under specified node
    *
    * @access	public
    * @param	array		node_data
    * @param    boolean     with data: default is true otherwise this function return only a ref_id array
    * @return	array		2-dim (int/array) key, node_data of each subtree node including the specified node
    * @throws InvalidArgumentException
    */
    public function getSubTree($a_node, $a_with_data = true, $a_type = "")
    {
        $res = $this->getSubTreeRecursiveWithRoot($a_node);

        if ($a_type !== "") {
            if (!is_array($a_type)) {
                $a_type = [$a_type];
            }

            $res = $this->getNodesWithTypes($a_type, $res);
        }

        if (!$a_with_data) {
            $res = $this->getIdsFromNodes($res);
        }

        return iterator_to_array($res);
    }

    /**
    * get types of nodes in the subtree under specified node
    *
    * @access	public
    * @param	array		node_id
    * @param	array		object types to filter e.g array('rolf')
    * @return	array		2-dim (int/array) key, node_data of each subtree node including the specified node
    */
    public function getSubTreeTypes($a_node_id, $a_filter = 0)
    {
        $node = $this->getNodeData($a_node_id);
        $nodes = $this->getSubTreeRecursiveWithRoot($node);
        if ($a_filter !== 0) {
            $nodes = $this->getNodesWithoutTypes($a_filter, $nodes);
        }
        return iterator_to_array(
            $this->getTypesFromNodes($nodes)
        );
    }

    protected function getSubTreeRecursiveWithRoot($root)
    {
        yield $root;
        foreach ($this->getSubTreeRecursive($root) as $node) {
            yield $node;
        }
    }

    protected function getSubTreeRecursive($node)
    {
        if (!isset($node["child"]) || !$node["child"]) {
            return;
        }
        $children = $this->getChilds($node["child"]);
        foreach ($children as $child) {
            // The "last" key is set on the last array entry by the original
            // getChilds-implementation. The original getChildsByType-impl
            // does not set that key. This is why I remove it here. I do not
            // really know if this changes a thing.
            if (isset($child["last"])) {
                unset($child["last"]);
            }
            yield $child;
            foreach ($this->getSubTreeRecursive($child) as $sub) {
                yield $sub;
            }
        }
    }

    protected function getIdsFromNodes($nodes)
    {
        foreach ($nodes as $node) {
            // There might be broken node-records.
            if (!isset($node["child"])) {
                continue;
            }
            yield $node["child"];
        }
    }

    protected function getNodesWithTypes(array $types, $nodes)
    {
        foreach ($nodes as $node) {
            if (in_array($node["type"], $types)) {
                yield $node;
            }
        }
    }

    protected function getNodesWithoutTypes(array $types, $nodes)
    {
        foreach ($nodes as $node) {
            if (!in_array($node["type"], $types)) {
                yield $node;
            }
        }
    }

    protected function getTypesFromNodes($nodes)
    {
        $seen_types = [];
        foreach ($nodes as $node) {
            if (!in_array($node["type"], $seen_types)) {
                $seen_types[] = $node["type"];
                yield $node["type"];
            }
        }
    }

    /**
    * get path from a given startnode to a given endnode
    * if startnode is not given the rootnode is startnode.
    * This function chooses the algorithm to be used.
    *
    * @access	public
    * @param	integer	node_id of endnode
    * @param	integer	node_id of startnode (optional)
    * @return	array	ordered path info (id,title,parent) from start to end
    */
    public function getPathFull($a_endnode_id, $a_startnode_id = 0)
    {
        $node = $this->getNodeData($a_endnode_id);
        $res = [$node];
        while ($node["child"] != $a_startnode_id && $node["parent"] != 0) {
            $node = $this->getNodeData($node["parent"]);
            $res[] = $node;
        }

        if ($a_startnode_id != 0 && $node["child"] != $a_startnode_id) {
            return [];
        }

        return $this->addCurrentObjectData(array_reverse($res));
    }

    /**
    * get path from a given startnode to a given endnode
    * if startnode is not given the rootnode is startnode
    * @access	public
    * @param	integer		node_id of endnode
    * @param	integer		node_id of startnode (optional)
    * @return	array		all path ids from startnode to endnode
    * @throws InvalidArgumentException
    */
    public function getPathId($a_endnode_id, $a_startnode_id = 0)
    {
        $path = $this->getPathFull($a_endnode_id, $a_startnode_id);
        $res = [];
        foreach ($path as $node) {
            $res[] = $node["child"];
        }
        return $res;
    }

    /**
    * get all information of a node.
    * get data of a specific node from tree and object_data
    * @access	public
    * @param	integer		node id
    * @return	array		2-dim (int/str) node_data
    * @throws InvalidArgumentException
    */
    public function getNodeData($a_node_id, $a_tree_pk = null)
    {
        if (!is_null($a_tree_pk)) {
            return $this->getNodeData($a_node_id, $a_tree_pk);
        }
        return $this->getCachedData($a_node_id);
    }

    /**
    * get all information of a node.
    * get data of a specific node from tree and object_data
    * @access	public
    * @param	integer		node id
    * @return	boolean		true, if node id is in tree
    */
    public function isInTree($a_node_id)
    {
        return $this->hasCachedInfo($a_node_id);
    }

    /**
    * get data of parent node from tree and object_data
    * @access	public
    * @param	integer		node id
    * @return	array
    * @throws InvalidArgumentException
    */
    public function getParentNodeData($a_node_id)
    {
        $node = $this->getNodeData($a_node_id);
        if ($node["parent"] == 0) {
            return [ "desc" => "" ]; // some random (?) edge case behaviour for root
        }
        return $this->getNodeData($node["parent"]);
    }

    /**
    * checks if a node is in the path of an other node
    * @access	public
    * @param	integer		object id of start node
    * @param    integer     object id of query node
    * @return	integer		number of entries
    */
    public function isGrandChild($a_startnode_id, $a_querynode_id)
    {
        $node = $this->getNodeData($a_querynode_id);
        while ($node["parent"] != 0) {
            if ($node["parent"] == $a_startnode_id) {
                return true;
            }
            $node = $this->getNodeData($node["parent"]);
        }
        return false;
    }

    /**
    * get parent id of given node
    * @access	public
    * @param	integer	node id
    * @return	integer	parent id
    * @throws InvalidArgumentException
    */
    public function getParentId($a_node_id)
    {
        return $this->getNodeData($a_node_id)["parent"];
    }

    /**
    * Check for parent type
    * e.g check if a folder (ref_id 3) is in a parent course obj => checkForParentType(3,'crs');
    *
    * @access	public
    * @param	integer	ref_id
    * @param	string type
    * @return	mixed false if item is not in tree,
    * 				  int (object ref_id) > 0 if path container course, int 0 if pathc does not contain the object type
    */
    public function checkForParentType($a_ref_id, $a_type, $a_exclude_source_check = false)
    {
        if (!$this->isInTree($a_ref_id)) {
            return false;
        }

        $node = $this->getNodeData($a_ref_id);
        if ($a_exclude_source_check && $node["parent"] != 0) {
            $node = $this->getNodeData($node["parent"]);
        }
        while ($node["parent"] != 0) {
            if ($node["type"] == $a_type) {
                return $node["child"];
            }
            $node = $this->getNodeData($node["parent"]);
        }

        return 0;
    }

    //--------------------------------------
    // INTERCEPT EVENTS
    //--------------------------------------

    /**
     * @var	array
     */
    protected $intercepted_events = array();

    public function raise(string $module, string $event, array $params)
    {
        $this->intercepted_events[] = [$module, $event, $params];
    }

    public function withInterceptedEvents(\Closure $call)
    {
        $app_event_handler = $GLOBALS["ilAppEventHandler"];
        $GLOBALS["ilAppEventHandler"] = $this;
        $this->intercepted_events = [];

        $ret = $call();

        foreach ($this->intercepted_events as $event) {
            list($module, $event, $params) = $event;
            $app_event_handler->raise($module, $event, $params);
        }

        $this->intercepted_events = [];
        $GLOBALS["ilAppEventHandler"] = $app_event_handler;

        return $ret;
    }

    //--------------------------------------
    // FALLBACKS TO CACHELESS TREE
    //--------------------------------------

    /**
     * Init tree implementation
     */
    public function initTreeImplementation()
    {
        return $this->other->initTreeImplementation();
    }
    
    /**
     * Get tree implementation
     * @return ilTreeImplementation $impl
     */
    public function getTreeImplementation()
    {
        return $this->other->getTreeImplementation();
    }
    
    /**
    * Use Cache (usually activated)
    */
    public function useCache($a_use = true)
    {
        return $this->other->useCache($a_use);
    }
    
    /**
     * Check if cache is active
     * @return bool
     */
    public function isCacheUsed()
    {
        return $this->other->isCacheUsed();
    }
    
    /**
     * Get depth cache
     * @return type
     */
    public function getDepthCache()
    {
        return $this->other->getDepthCache();
    }
    
    /**
     * Get parent cache
     * @return type
     */
    public function getParentCache()
    {
        return $this->other->getParentCache();
    }
    
    /**
    * Store user language. This function is used by the "main"
    * tree only (during initialisation).
    */
    public function initLangCode()
    {
        return $this->other->initLangCode();
    }
    
    /**
     * Get tree table name
     * @return string tree table name
     */
    public function getTreeTable()
    {
        return $this->other->getTreeTable();
    }
    
    /**
     * Get object data table
     * @return type
     */
    public function getObjectDataTable()
    {
        return $this->other->getObjectDataTable();
    }
    
    /**
     * Get tree primary key
     * @return string column of pk
     */
    public function getTreePk()
    {
        return $this->other->getTreePk();
    }
    
    /**
     * Get reference table if available
     */
    public function getTableReference()
    {
        return $this->other->getTableReference();
    }
    
    /**
     * Get default gap	 * @return int
     */
    public function getGap()
    {
        return $this->other->getGap();
    }
    
    /***
     * reset in tree cache
     */
    public function resetInTreeCache()
    {
        return $this->other->resetInTreeCache();
    }


    /**
    * set table names
    * The primary key of the table containing your object_data must be 'obj_id'
    * You may use a reference table.
    * If no reference table is specified the given tree table is directly joined
    * with the given object_data table.
    * The primary key in object_data table and its foreign key in reference table must have the same name!
    *
    * @param	string	table name of tree table
    * @param	string	table name of object_data table
    * @param	string	table name of object_reference table (optional)
    * @access	public
    * @return	boolean
     *
     * @throws InvalidArgumentException
    */
    public function setTableNames($a_table_tree, $a_table_obj_data, $a_table_obj_reference = "")
    {
        return $this->other->setTableNames($a_table_tree, $a_table_obj_data, $a_table_obj_reference);
    }

    /**
    * set column containing primary key in reference table
    * @access	public
    * @param	string	column name
    * @return	boolean	true, when successfully set
    * @throws InvalidArgumentException
    */
    public function setReferenceTablePK($a_column_name)
    {
        return $this->other->setReferenceTablePK($a_column_name);
    }

    /**
    * set column containing primary key in object table
    * @access	public
    * @param	string	column name
    * @return	boolean	true, when successfully set
    * @throws InvalidArgumentException
    */
    public function setObjectTablePK($a_column_name)
    {
        return $this->other->setObjectTablePK($a_column_name);
    }

    /**
    * set column containing primary key in tree table
    * @access	public
    * @param	string	column name
    * @return	boolean	true, when successfully set
    * @throws InvalidArgumentException
    */
    public function setTreeTablePK($a_column_name)
    {
        return $this->other->setTreeTablePK($a_column_name);
    }

    /**
    * build join depending on table settings
    * @access	private
    * @return	string
    */
    public function buildJoin()
    {
        return $this->other->buildJoin();
    }
    
    /**
     * Get relation of two nodes
     * @param int $a_node_a
     * @param int $a_node_b
     */
    public function getRelation($a_node_a, $a_node_b)
    {
        return $this->other->getRelation($a_node_a, $a_node_b);
    }
    
    /**
     * get relation of two nodes by node data
     * @param array $a_node_a_arr
     * @param array $a_node_b_arr
     *
     */
    public function getRelationOfNodes($a_node_a_arr, $a_node_b_arr)
    {
        return $this->other->getRelationOfNodes($a_node_a_arr, $a_node_b_arr);
    }
    
    /**
     * Get node child ids
     * @global type $ilDB
     * @param type $a_node
     * @return type
     */
    public function getChildIds($a_node)
    {
        return $this->other->getChildIds($a_node);
    }

    /**
    * get child nodes of given node (exclude filtered obj_types)
    * @access	public
    * @param	array		objects to filter (e.g array('rolf'))
    * @param	integer		node_id
    * @param	string		sort order of returned childs, optional (possible values: 'title','desc','last_update' or 'type')
    * @param	string		sort direction, optional (possible values: 'DESC' or 'ASC'; defalut is 'ASC')
    * @return	array		with node data of all childs or empty array
    */
    public function getFilteredChilds($a_filter, $a_node, $a_order = "", $a_direction = "ASC")
    {
        return $this->other->getFilteredChilds($a_filter, $a_node, $a_order, $a_direction);
    }
    
    /**
     * Insert node from trash deletes trash entry.
     * If we have database query exceptions we could wrap insertNode in try/catch
     * and rollback if the insert failed.
     *
     * @param type $a_source_id
     * @param type $a_target_id
     * @param type $a_tree_id
     *
     * @throws InvalidArgumentException
     */
    public function insertNodeFromTrash($a_source_id, $a_target_id, $a_tree_id, $a_pos = IL_LAST_NODE, $a_reset_deleted_date = false)
    {
        return $this->withInterceptedEvents(function () use ($a_source_id, $a_target_id, $a_tree_id, $a_pos, $a_reset_deleted_date) {
            $ret = $this->other->insertNodeFromTrash($a_source_id, $a_target_id, $a_tree_id, $a_pos, $a_reset_deleted_date);
            $this->purgeCache($a_source_id);
            $this->purgeCache($a_target_id);
            return $ret;
        });
    }
    
    
    /**
    * insert new node with node_id under parent node with parent_id
    * @access	public
    * @param	integer		node_id
    * @param	integer		parent_id
    * @param	integer		IL_LAST_NODE | IL_FIRST_NODE | node id of preceding child
    * @throws InvalidArgumentException
    */
    public function insertNode($a_node_id, $a_parent_id, $a_pos = IL_LAST_NODE, $a_reset_deletion_date = false)
    {
        return $this->withInterceptedEvents(function () use ($a_node_id, $a_parent_id, $a_pos, $a_reset_deletion_date) {
            $ret = $this->other->insertNode($a_node_id, $a_parent_id, $a_pos, $a_reset_deletion_date);
            $this->purgeCache($a_node_id);
            $this->purgeCache($a_parent_id);
            return $ret;
        });
    }
    
    /**
     * get filtered subtree
     *
     * get all subtree nodes beginning at a specific node
     * excluding specific object types and their child nodes.
     *
     * E.g getFilteredSubTreeNodes()
     *
     * @access public
     * @param
     * @return
     */
    public function getFilteredSubTree($a_node_id, $a_filter = array())
    {
        return $this->other->getFilteredSubTree($a_node_id, $a_filter);
    }

    /**
     * delete node and the whole subtree under this node
     * @access	public
     * @param	array		node_data of a node
     * @throws InvalidArgumentException
     * @throws ilInvalidTreeStructureException
     */
    public function deleteTree($a_node)
    {
        return $this->withInterceptedEvents(function () use ($a_node) {
            $ret = $this->other->deleteTree($a_node);
            $this->purgeCache($a_node);
            return $ret;
        });
    }
    
    /**
     * Validate parent relations of tree
     * @return int[] array of failure nodes
     */
    public function validateParentRelations()
    {
        return $this->other->validateParentRelations();
    }

    /**
     * Preload depth/parent
     *
     * @param
     * @return
     */
    public function preloadDepthParent($a_node_ids)
    {
        return $this->other->preloadDepthParent($a_node_ids);
    }

    /**
    * Returns the node path for the specified object reference.
    *
    * Note: this function returns the same result as getNodePathForTitlePath,
    * but takes ref-id's as parameters.
    *
    * This function differs from getPathFull, in the following aspects:
    * - The title of an object is not translated into the language of the user
    * - This function is significantly faster than getPathFull.
    *
    * @access	public
    * @param	integer	node_id of endnode
    * @param	integer	node_id of startnode (optional)
    * @return	array	ordered path info (depth,parent,child,obj_id,type,title)
    *               or null, if the node_id can not be converted into a node path.
    */
    public function getNodePath($a_endnode_id, $a_startnode_id = 0)
    {
        return $this->other->getNodePath($a_endnode_id, $a_startnode_id);
    }

    // BEGIN WebDAV: getNodePathForTitlePath function added
    /**
    * Converts a path consisting of object titles into a path consisting of tree
    * nodes. The comparison is non-case sensitive.
    *
    * Note: this function returns the same result as getNodePath,
    * but takes a title path as parameter.
    *
    * @access	public
    * @param	Array	Path array with object titles.
    *                       e.g. array('ILIAS','English','Course A')
    * @param	ref_id	Startnode of the relative path.
    *                       Specify null, if the title path is an absolute path.
    *                       Specify a ref id, if the title path is a relative
    *                       path starting at this ref id.
    * @return	array	ordered path info (depth,parent,child,obj_id,type,title)
    *               or null, if the title path can not be converted into a node path.
    */
    public function getNodePathForTitlePath($titlePath, $a_startnode_id = null)
    {
        return $this->other->getNodePathForTitlePath($titlePath, $a_startnode_id);
    }
    // END WebDAV: getNodePathForTitlePath function added
    // END WebDAV: getNodePath function added
    // END WebDAV: getNodePath function added

    /**
    * check consistence of tree
    * all left & right values are checked if they are exists only once
    * @access	public
    * @return	boolean		true if tree is ok; otherwise throws error object
    * @throws ilInvalidTreeStructureException
    */
    public function checkTree()
    {
        return $this->other->checkTree();
    }

    /**
     * check, if all childs of tree nodes exist in object table
     *
     * @param bool $a_no_zero_child
     * @return bool
     * @throws ilInvalidTreeStructureException
    */
    public function checkTreeChilds($a_no_zero_child = true)
    {
        return $this->other->checkTreeChilds($a_no_zero_child);
    }

    /**
     * Return the current maximum depth in the tree
     * @access	public
     * @return	integer	max depth level of tree
     */
    public function getMaximumDepth()
    {
        return $this->other->getMaximumDepth();
    }

    /**
    * return depth of a node in tree
    * @access	private
    * @param	integer		node_id of parent's node_id
    * @return	integer		depth of node in tree
    */
    public function getDepth($a_node_id)
    {
        return $this->other->getDepth($a_node_id);
    }
    
    /**
     * return all columns of tabel tree
     * @param type $a_node_id
     * @return array of table column => values
     *
     * @throws InvalidArgumentException
     */
    public function getNodeTreeData($a_node_id)
    {
        return $this->other->getNodeTreeData($a_node_id);
    }

    
    /**
    * get data of parent node from tree and object_data
    * @access	private
    * @param	object	db	db result object containing node_data
    * @return	array		2-dim (int/str) node_data
    * TODO: select description twice for compability. Please use 'desc' in future only
    */
    public function fetchNodeData($a_row)
    {
        return $this->other->fetchNodeData($a_row);
    }

    /**
     * Get translation data from object cache (trigger in object cache on preload)
     *
     * @param	array	$a_obj_ids		object ids
     */
    protected function fetchTranslationFromObjectDataCache($a_obj_ids)
    {
        return $this->other->fetchTranslationFromObjectDataCache($a_obj_ids);
    }

    /**
    * create a new tree
    * to do: ???
    * @param	integer		a_tree_id: obj_id of object where tree belongs to
    * @param	integer		a_node_id: root node of tree (optional; default is tree_id itself)
    * @return	boolean		true on success
    * @throws InvalidArgumentException
    * @access	public
    */
    public function addTree($a_tree_id, $a_node_id = -1)
    {
        return $this->other->addTree($a_tree_id, $a_node_id);
    }

    /**
     * get nodes by type
     * @param	integer		a_tree_id: obj_id of object where tree belongs to
     * @param	integer		a_type_id: type of object
     * @access	public
     * @throws InvalidArgumentException
     * @return array
     * @deprecated since 4.4.0
     */
    public function getNodeDataByType($a_type)
    {
        return $this->other->getNodeDataByType($a_type);
    }

    /**
    * remove an existing tree
    *
    * @param	integer		a_tree_id: tree to be removed
    * @return	boolean		true on success
    * @access	public
    * @throws InvalidArgumentException
    */
    public function removeTree($a_tree_id)
    {
        return $this->other->removeTree($a_tree_id);
    }
    
    /**
     * Wrapper for saveSubTree
     * @param int $a_node_id
     * @param bool $a_set_deleted
     * @return integer
     * @throws InvalidArgumentException
     */
    public function moveToTrash($a_node_id, $a_set_deleted = false)
    {
        return $this->withInterceptedEvents(function () use ($a_node_id, $a_set_deleted) {
            $ret = $this->other->moveToTrash($a_node_id, $a_set_deleted);
            $this->purgeCache($a_node_id);
            return $ret;
        });
    }

    /**
     * Use the wrapper moveToTrash
     * save subtree: delete a subtree (defined by node_id) to a new tree
     * with $this->other->tree_id -node_id. This is neccessary for undelete functionality
     * @param	integer	node_id
     * @return	integer
     * @access	public
     * @throws InvalidArgumentException
     * @deprecated since 4.4.0
     */
    public function saveSubTree($a_node_id, $a_set_deleted = false)
    {
        return $this->withInterceptedEvents(function () use ($a_node_id, $a_set_deleted) {
            $ret = $this->other->saveSubTree($a_node_id, $a_set_deleted);
            $this->purgeCache($a_node_id);
            return $ret;
        });
    }

    /**
     * This is a wrapper for isSaved() with a more useful name
     * @param int $a_node_id
     */
    public function isDeleted($a_node_id)
    {
        return $this->other->isDeleted($a_node_id);
    }

    /**
     * Use method isDeleted
     * check if node is saved
     * @deprecated since 4.4.0
     */
    public function isSaved($a_node_id)
    {
        return $this->other->isSaved($a_node_id);
    }

    /**
     * Preload deleted information
     *
     * @param array nodfe ids
     * @return bool
     */
    public function preloadDeleted($a_node_ids)
    {
        return $this->other->preloadDeleted($a_node_ids);
    }


    /**
    * get data saved/deleted nodes
    * @return	array	data
    * @param	integer	id of parent object of saved object
    * @access	public
    * @throws InvalidArgumentException
    */
    public function getSavedNodeData($a_parent_id)
    {
        return $this->other->getSavedNodeData($a_parent_id);
    }
    
    /**
    * get object id of saved/deleted nodes
    * @return	array	data
    * @param	array	object ids to check
    * @access	public
    */
    public function getSavedNodeObjIds(array $a_obj_ids)
    {
        return $this->other->getSavedNodeObjIds($a_obj_ids);
    }

    /**
    * get left value of given node
    * @access	public
    * @param	integer	node id
    * @return	integer	left value
    * @throws InvalidArgumentException
    */
    public function getLeftValue($a_node_id)
    {
        return $this->other->getLeftValue($a_node_id);
    }

    /**
    * get sequence number of node in sibling sequence
    * @access	public
    * @param	array		node
    * @return	integer		sequence number
    * @throws InvalidArgumentException
    */
    public function getChildSequenceNumber($a_node, $type = "")
    {
        return $this->other->getChildSequenceNumber($a_node, $type);
    }

    /**
    * read root id from database
    * @param root_id
    * @access public
    * @return int new root id
    */
    public function readRootId()
    {
        return $this->other->readRootId();
    }

    /**
    * get the root id of tree
    * @access	public
    * @return	integer	root node id
    */
    public function getRootId()
    {
        return $this->other->getRootId();
    }

    public function setRootId($a_root_id)
    {
        return $this->other->setRootId($a_root_id);
    }

    /**
    * get tree id
    * @access	public
    * @return	integer	tree id
    */
    public function getTreeId()
    {
        return $this->other->getTreeId();
    }

    /**
    * set tree id
    * @access	public
    * @return	integer	tree id
    */
    public function setTreeId($a_tree_id)
    {
        return $this->other->setTreeId($a_tree_id);
    }

    /**
    * get node data of successor node
    *
    * @access	public
    * @param	integer		node id
    * @return	array		node data array
    * @throws InvalidArgumentException
    */
    public function fetchSuccessorNode($a_node_id, $a_type = "")
    {
        return $this->other->fetchSuccessorNode($a_node_id, $a_type);
    }

    /**
    * get node data of predecessor node
    *
    * @access	public
    * @param	integer		node id
    * @return	array		node data array
    * @throws InvalidArgumentException
    */
    public function fetchPredecessorNode($a_node_id, $a_type = "")
    {
        return $this->other->fetchPredecessorNode($a_node_id, $a_type);
    }

    /**
    * Wrapper for renumber. This method locks the table tree
    * (recursive)
    * @access	public
    * @param	integer	node_id where to start (usually the root node)
    * @param	integer	first left value of start node (usually 1)
    * @return	integer	current left value of recursive call
    */
    public function renumber($node_id = 1, $i = 1)
    {
        $this->purgeAll();
        return $this->other->renumber($node_id, $i);
    }

    // PRIVATE
    /**
    * This method is private. Always call ilTree->renumber() since it locks the tree table
    * renumber left/right values and close the gaps in numbers
    * (recursive)
    * @access	private
    * @param	integer	node_id where to start (usually the root node)
    * @param	integer	first left value of start node (usually 1)
    * @return	integer	current left value of recursive call
    */
    public function __renumber($node_id = 1, $i = 1)
    {
        $this->purgeAll();
        return $this->other->__renumber($node_id, $i);
    }

    /**
    * Check if operations are done on main tree
    *
    * @access	private
    * @return boolean
    */
    public function __isMainTree()
    {
        return $this->other->__isMainTree();
    }

    /**
     * Check for deleteTree()
     * compares a subtree of a given node by checking lft, rgt against parent relation
     *
     * @access	private
     * @param array node data from ilTree::getNodeData()
     * @return boolean
     *
     * @throws ilInvalidTreeStructureException
     * @deprecated since 4.4.0
    */
    public function __checkDelete($a_node)
    {
        return $this->other->__checkDelete($a_node);
    }

    /**
     *
     * @global type $ilDB
     * @param type $a_node_id
     * @param type $parent_childs
     * @return boolean
     * @throws ilInvalidTreeStructureException
     * @deprecated since 4.4.0
     */
    public function __getSubTreeByParentRelation($a_node_id, &$parent_childs)
    {
        return $this->other->__getSubTreeByParentRelation($a_node_id, $parent_childs);
    }

    /**
     * @param $lft_childs
     * @param $parent_childs
     * @return bool
     * @throws ilInvalidTreeStructureException
     * @deprecated since 4.4.0
     */
    public function __validateSubtrees(&$lft_childs, $parent_childs)
    {
        return $this->other->__validateSubtrees($lft_childs, $parent_childs);
    }
    
    /**
     * Move Tree Implementation
     *
     * @access	public
     * @param int source ref_id
     * @param int target ref_id
     * @param int location IL_LAST_NODE or IL_FIRST_NODE (IL_FIRST_NODE not implemented yet)
     * @return bool
     */
    public function moveTree($a_source_id, $a_target_id, $a_location = self::POS_LAST_NODE)
    {
        return $this->withInterceptedEvents(function () use ($a_source_id, $a_target_id, $a_location) {
            $ret = $this->other->moveTree($a_source_id, $a_target_id, $a_location);
            $this->purgeCache($a_source_id);
            $this->purgeCache($a_target_id);
            return $ret;
        });
    }
    
    /**
     * This method is used for change existing objects
     * and returns all necessary information for this action.
     * The former use of ilTree::getSubtree needs to much memory.
     * @param ref_id ref_id of source node
     * @return
     */
    public function getRbacSubtreeInfo($a_endnode_id)
    {
        return $this->other->getRbacSubtreeInfo($a_endnode_id);
    }
    

    /**
     * Get tree subtree query
     * @param type $a_node_id
     * @param type $a_types
     * @param type $a_force_join_reference
     * @return type
     */
    public function getSubTreeQuery($a_node_id, $a_fields = array(), $a_types = '', $a_force_join_reference = false)
    {
        return $this->other->getSubTreeQuery($a_node_id, $a_fields, $a_types, $a_force_join_reference);
    }
    
    
    /**
     * get all node ids in the subtree under specified node id, filter by object ids
     *
     * @param int $a_node_id
     * @param array $a_obj_ids
     * @param array $a_fields
     * @return	array
     */
    public function getSubTreeFilteredByObjIds($a_node_id, array $a_obj_ids, array $a_fields = array())
    {
        return $this->other->getSubTreeFilteredByObjIds($a_node_id, $a_obj_ids, $a_fields);
    }
    
    public function deleteNode($a_tree_id, $a_node_id)
    {
        return $this->withInterceptedEvents(function () use ($a_tree_id, $a_node_id) {
            $ret = $this->other->deleteNode($a_tree_id, $a_node_id);
            $this->purgeCache($a_node_id, $a_tree_id);
            return $ret;
        });
    }

    /**
     * Lookup object types in trash
     * @global type $ilDB
     * @return type
     */
    public function lookupTrashedObjectTypes()
    {
        return $this->other->lookupTrashedObjectTypes();
    }
} // END class.tree
