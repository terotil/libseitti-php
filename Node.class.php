<?php
/**
 * Node class for building tree structures.
 *
 * Copyringht (c) 2001 Mediaseitti
 *                     Tero Tilus <mutikainen@iki.fi>
 */
class Node {
  var $classname = 'Node';

  var $nodename = 'Default';

  /**
   * Parent node
   *
   * Next node upwards from $this in tree structure.  Root node has
   * parent null. 
   */
  var $parent;

  /**
   * Child nodes
   *
   * Array containing child nodes of $this.  Must be either null or
   * array of Nodes.  
   */
  var $children;

  /**
   * Free data
   */
  var $data;

  /**
   * Constructor
   */
  function Node($name) {
    $this->nodename = $name;
    $this->data = array();
    $this->children = array();
  }

  /**
   * Checks if given array has given key.
   */
  function array_haskey(&$arr, $k) {
    return in_array($k, array_keys($arr));
  }

  /**
   * Does this node have parent?
   */
  function hasParent() {
    return (isset($this->parent) and is_object($this->parent));
  }

  /**
   * Sets parent of node
   */
  function setParent(&$parentNode, $removed_from_childlist = false) {
    // First check if this node allready has parent and in that case,
    // unregister.  See full explanation in delChild's comments!
    if ( !$removed_from_childlist and $this->hasParent()) {
      $this->parent->delChild($this->nodename, true);
    }
    // References must be used to prevent nodes getting cloned.
    $this->parent = &$parentNode;
  }

  /**
   * Gets level of node in tree
   *
   * Node without parent (root node) has level 0.  Otherwise level is
   * level of parent + 1.  
   *
   * @public
   * @return Level of node.  Root level is 0.
   */
  function getLevel() {
    // Parent node must be object
    if ($this->hasParent()) {
      return $this->parent->getLevel() + 1;
    }
    // If not, node is regarded as root node
    return 0;
  }

  /**
   * Checks if there is a child with given name
   */
  function hasChild($name) {
    return $this->array_haskey($this->children, $name);
  }

  /**
   * Checks if there is data with given name
   */
  function hasData($name) {
    return $this->array_haskey($this->data, $name);
  }

  /**
   * Adds new child node
   *
   * If given node already exists in some tree it first gets removed (by
   * setParent) and then is added as child to this node.  
   */
  function addChild(&$childNode) {
    // References must be used to prevent nodes getting cloned.
    // First set parent of new child to reference $this
    $childNode->setParent($this);
    // Indexing is done with nodenames.
    $this->children[$childNode->nodename] = &$childNode;
  }

  /**
   * Removes a child node by it's name
   *
   * @param $childname Name of the child to be removed
   * @param $parent_unregistered True value means delChild was called
   *        internally by setParent and child's parent need not to be
   *        set.  When false (default) child's parent property needs
   *        to be nulled.  In this case setParent must be called
   *        with similar internal flag telling that node has
   *        allready been removed from child -list.  
   */
  function delChild($childname, $parent_unregistered = false) {
    // If there's no such child, notify and do nothing
    if ($this->hasChild($childname)) {
      if ( !$parent_unregistered ) {
	$this->$children[$childname]->setParent(null, true); 
      }
      // Remove child.
      unset($this->$children[$childname]);
    } else {
      trigger_error("Node::deleteChild(): No child '$childname'", 
		    E_USER_NOTICE);
    }
  }

  /**
   * Gets child by name recursively.  Search ends when first child
   * with given name is found 
   */
  function &getChildByName($name) {
    $child_names = $this->getChildNames();
    if ( in_array($name, $child_names) ) {
      return $this->children[$name];
    }
    foreach ( $child_names as $child_name ) {
      $found =& $this->children[$child_name]->getChildByName($name);
      if ( $found ) {
	return $found;
      }
    }
    return false;
  }

  /**
   * Gets the names of children
   *
   * @public 
   * @return Array containing names of the child nodes.  Empty array,
   *         if there's no children.  
   */
  function getChildNames() {
    return array_keys($this->children);
  }

  /**
   * Sets data
   */
  function setData($name, $value) {
    $this->data[$name] = $value;
  }

  /**
   * Gets data
   */
  function getData($name) {
    if (in_array($name, array_keys($this->data))) {
      return $this->data[$name];
    }
    return null;
  }

  /**
   * Deletes data
   */
  function delData($name) {
    if ( $this->hasData($name) ) {
      unset($this->data[$name]);
    }
  }

} // end class Node

?>