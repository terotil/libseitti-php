<?php
require_once('libseitti-php/Node.class.php');
/**
 * Tree class for handling tree structure.
 *
 * Copyringht (c) 2001 Mediaseitti
 *                     Tero Tilus <mutikainen@iki.fi>
 */
class Tree {
  var $classname = 'Tree';

  /**
   * Root node of tree
   */
  var $root;

  /**
   * Constructor for Tree
   */
  function Tree() {
    $new_root = new Node('root');
    $this->setRoot($new_root);
  }

  /**
   * Gets root node
   *
   * @public
   * @return Reference to root node.  Use =&amp; to assign return
   *         value!  
   */
  function &getRoot() {
    return $this->root;
  }

  /**
   * Sets root node
   */
  function setRoot(&$new_root) {
    $this->root =& $new_root;
  }

  /**
   * Gets first node with given name
   *
   * @public * @return Reference to first node with given name.  Use
   *         =&amp; to assign return value!  
   */
  function &getNodeByName($name) {
    if ( $name == 'root' ) {
      return $this->root;
    }
    return $this->root->getChildByName($name);
  }

} // end class Tree
?>
