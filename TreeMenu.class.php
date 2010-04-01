<?php
require_once('libseitti-php/Tree.class.php');
define('EXPANDED', 'expanded');
define('ACTIVE', 'active');
/**
 * TreeMenu class for creating and rendering folding menu structures.
 *
 * Copyringht (c) 2001 Mediaseitti
 *                     Tero Tilus <mutikainen@iki.fi> 
 *
 * TODO:
 *  - Caching.  cachedRenderMenu() first checks if this menu has
 *    previously been rendered by comparing timestamps of template
 *    (how to get it?), menufile and cache file.  Name of the cache
 *    file could be md5 from joined result of getExpandedNodes().  
 */
class TreeMenu extends Tree {
  var $classname = 'TreeMenu';

  var $rendered;

  /**
   * Constructor
   */
  function TreeMenu() {
    $this->Tree();
    $this->rendered = 'Not rendered yet!';
  }

  /**
   * Parses menutree from file
   *
   * Example:
   * ;1stRootTopicName;title=Root topic 1;url=?page=root1.html;name=value
   * .;topic2.html;title=Subnode for topic 1
   * ..;topic3.html;title=SubSubnode
   * ;2ndRootTopicName;title=2nd root topic;url=?page=root2.html
   *
   * Menufile is $delim separated text. Single line represents single
   * menuitem.  Length of first field indicates the level of item.
   * Second field is the name of the node.  Rest of the fields are
   * split on first $subdelim and appended to menuitem's data array.  
   *
   * FIXME: Menutree file needs a better syntax  
   * sumthn like main1(title=Hep;url=hep.html){ sub1(title=Sub){} sub2(title=Sb){} }
   */
  function parseMenuFromFile($filename='menu.txt', $delim=';', $subdelim='=') {
    if ( file_exists($filename) ) {
      $menuitems = file($filename);
    } else {
      trigger_error("TreeMenu::parseMenuFromFile(): Parse failed"
		    .", file '$filename' not found.", 
		    E_USER_WARNING);
    }
    $this->parseMenuFromItemArray($menuitems, $delim, $subdelim);    
  }

  function parseMenuFromString(&$menustring, $delim=';', $subdelim='=') {
    $menuitems = split("\n", $menustring);
    $this->parseMenuFromItemArray($menuitems, $delim, $subdelim);    
  }

  function parseMenuFromItemArray(&$menuitems, $delim=';', $subdelim='=') {
    // Initialize parent-array.
    $parent = array();
    $parent[0] =& $this->root;

    foreach ( $menuitems as $itemstring ) {
      $itemstring = trim($itemstring);
      if ( ($itemstring != '') and
	   ($itemstring[0] != '#') ) {
	// Split item to fields on $delim
	$item_fields = split($delim, str_replace('\\'.$delim, 
						 '\\x'.bin2hex($delim),
						 $itemstring));
	// print_r($item_fields); echo '<br>';

	// Length of the first field shows level
	$item_level = strlen(array_shift($item_fields));
	// echo $item_level; echo '<br>';

	// Second field contains name
	$item_name  = array_shift($item_fields);
	// echo $item_name; echo '<br>';

	// Create new node object
	$new_item = new Node($item_name);

	// Append rest of the fields to node's data.
	foreach ( $item_fields as $item_field ) {
	  $item_field = trim(stripcslashes($item_field));
	  if ( $item_field != '' ) {
	    list($key, $value) = split($subdelim, trim($item_field), 2);
	    if ( $key != '' ) {
	      $new_item->setData($key, $value);
	    }
	  }
	}
	// print_r($new_item); echo '<br>';

	// This will be parent for following items on next level
	$parent[$item_level+1] =& $new_item;

	// Add new item to tree
	$parent[$item_level]->addChild($new_item);

	// Reference to newly created menuitem must be unset before
	// processing next item.  Otherwise next items happily
	// override all the previous.  This is because until now
	// $parent[$item_level+1] and $new_item have been references
	// to same variable.  Setting $new_item sets all items in
	// $parent unless $new_item is firs unset.
	unset($new_item);
      }
    }
  }

  /**
   * Renders menutree
   *
   * Nodes wich appear expanded are retrieved with getNodesToExpand().
   * Nodes may also be marked expanded directly with
   * markExpandedNode().
   *
   * @public
   * @return String containing rendered menutree 
   */
  function &renderMenu() {
    // First thing to do is to mark nodes to be expanded.  
    $this->markExpandedNodes();
    // Clean result of previous rendering
    $this->setRendered('');
    // Perform rendering
    $this->internalRenderNode($this->root);
    // Return results
    return $this->getRendered();
  }

  /**
   * Calls node rendering functions
   */
  function internalRenderNode(&$node) {
    $this->preRenderNode($node);
    // Render node itself
    $this->renderNode($node);
      // Also render children if node is marked expanded
    if ( $node->hasData(EXPANDED) and
	 count($node->children) > 0 ) {
      $this->shiftIn($node);
      foreach (array_keys($node->children) as $k) {
	$this->internalRenderNode($node->children[$k]);
      }
      $this->shiftOut($node);
    }
    $this->postRenderNode($node);
  }

  /**
   * Renders given node
   *
   * If you want to customize output this method should be overriden
   * to perform desired rendering.  Remember to define renderNode to
   * pass nodes as reference!  
   */
  function renderNode(&$node) {
    $indent = '';
    $level = $node->getLevel();
    for ($i=0; $i<$level; $i++) {
      $indent .= '&nbsp;&nbsp;';
    }
    $this->appendRendered($indent."o Node '".$node->nodename."': (".join(',',$node->data).")\n");
  }

  /**
   * Called before renderNode()
   *
   * If you want to customize output this method should be overriden
   * to perform desired rendering.
   */
  function preRenderNode(&$node) {
  }

  /**
   * Called after renderNode()
   *
   * If you want to customize output this method should be overriden
   * to perform desired rendering.
   */
  function postRenderNode(&$node) {
  }

  /**
   * Called before rendering children of $node
   */
  function shiftIn(&$node) {
  }

  /**
   * Called after rendering children of $node
   */
  function shiftOut(&$node) {
  }

  /**
   * Gets the list of nodes, which are to be expanded
   *
   * Return value of this function contains references to nodes which
   * appear expanded when menu is rendered.  These nodes, their
   * children and all sibblings of their parents are rendered visible.
   *
   * If you want to inherit your own menu from this class, this method
   * should be overriden to return desired nodes.
   *
   * @private
   * @return Reference to array of references to nodes to expand 
   */
  function &getNodesToExpand() {
    return array();
  }

  /**
   * Gets list of nodes which are currently marked as expanded.
   *
   * If node is not marked expanded it's assumed that none of it's
   * children is marked expanded.  
   */
  function &getExpandedNodes() {
    // Initialize empty result array
    $expanded = array();
    // Search
    $this->recursiveGetExpandedNodes($this->root, $expanded);
    // Return result
    return $expanded;
  }

  /**
   * Gets list of nodes which are currently marked as expanded.
   *
   * Starts search from $node.  Appends found nodes to array
   * $expanded.  
   */
  function &recursiveGetExpandedNodes(&$node, &$expanded) {
    $child_names = $node->getChildNames();
    // Browse children
    foreach ( $child_names as $child_name ) {
      if ($node->children[$child_name]->hasData(EXPANDED)) {
	// Append expanded nodes to array.
	$expanded[] =& $node->children[$child_name];
	// Continue search deeper to expanded branch
	$this->recursiveGetExpandedNodes($node->children[$child_name], 
					 $expanded);
      }
    }
  }

  /**
   * Marks given node and recursively all the children expanded.
   */
  function markExpandedTree(&$node) {
    $this->markExpandedNode($node, true);
    // Iterate children
    foreach ( $node->getChildNames() as $child_name ) {
      $this->markExpandedTree($node->children[$child_name]);
    }
  }

  /**
   * Marks given node to be expanded when rendering takes place.
   */
  function markExpandedNode(&$node, $is_recursive_call=false) {
    if (is_object($node)) {
      $foundnode = &$node;
    } else {
      $foundnode =& $this->getNodeByName($node);
      if (!is_object($foundnode)) {
	trigger_error("Node '".$node."' not found and cannot be marked expanded.");
	return;
      }
    }
    // Inherit expanding upwards up to first already expanded node
    if ($foundnode->hasParent() &&
	! ($foundnode->parent->hasData(EXPANDED)) ) {
      $this->markExpandedNode($foundnode->parent, true);
    }
    // Mark expanded
    $foundnode->setData(EXPANDED, true);
    // External (not internal inheriting) call set "active" -flag too
    if ( ! $is_recursive_call ) {
      $foundnode->setData(ACTIVE, true);
    }
  }

  /**
   * Marks expanded nodes given by getNodesToExpand().
   *
   * Nodes wich are expanded are retrieved with getNodesToExpand().
   * Nodes may also be marked expanded directly with
   * markExpandedNode().  
   */
  function markExpandedNodes() {
    // Retrieve array of references to nodes
    $nodesToExpand =& $this->getNodesToExpand();
    // Apply markExpandedNode() to each node.
    foreach (array_keys($nodesToExpand) as $k) {
      $this->markExpandedNode($nodesToExpand[$k]);
    }
  }

  function setRendered($string)    { $this->rendered =& $string; }
  function getRendered()           { return $this->rendered;     }
  function appendRendered($string) { $this->rendered .= $string; }

} // end class TreeMenu
