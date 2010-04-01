<?php
require_once('libseitti-php/TreeMenu.class.php');
/**
 * TemplateMenu class for creating and rendering template driven
 * folding menu structures.
 *
 * Copyringht (c) 2001 Mediaseitti
 *                     Tero Tilus <mutikainen@iki.fi> 
 */
class TemplateMenu extends TreeMenu {
  var $classname = 'TemplateMenu';

  /**
   * PHPLib template object
   */
  var $template;

  var $page_handle;
  var $menu_handle;
  var $item;
  var $sub;

  /**
   * Constructor
   */
  function TemplateMenu(&$templateObject, $pvar='page', $mvar='menu', 
			$ivar='item', $svar='sub') {
    // Call constructor of superclass
    $this->TreeMenu();

    // Initialize properties
    $this->page_handle = $pvar;
    $this->menu_handle = $mvar;
    $this->item = $ivar;
    $this->sub  = $svar;
    $this->setTemplate($templateObject);
  }

  /**
   * Sets PHPLib template object used.
   *
   * @public
   * @param $templateObject PHPLib template object to use.  Passed by
   *        reference.  Must contain blocks with names defined in
   *        $menu and $item prefixed by sufficent amount
   *        of $sub:s depending on level of node. 
   */
  function setTemplate(&$templateObject) {
    if (is_object($templateObject)) {
      $this->template =& $templateObject;
      $this->extractBlocks();
    } else {
      trigger_error('TemplateMenu::setTemplate(): Not an object.', 
		    E_USER_NOTICE);
    }
  }

  /**
   * Extract available menu blocks from template
   */
  function extractBlocks() {
    $this->template->set_block($this->page_handle, $this->menu_handle, $this->menu_handle.'var');
    $this->template->set_block($this->menu_handle, $this->menu_handle.$this->item, 
			       $this->menu_handle.$this->item.'var');
    $i = 1;
    while ( 1 ) {
      // Extract level $i block.  End loop if block is not found.
      $tmp = $this->template->set_block($this->menuItemBlock($i-1),
					$this->menuBlock($i),
					$this->menuBlock($i).'var');
      if ( $tmp == 2 ) { break; }
      $tmp = $this->template->set_block($this->menuBlock($i),
					$this->menuItemBlock($i),
					$this->menuItemBlock($i).'var');
      if ( $tmp == 2 ) { break; }
      $i++;
    }
    $this->resetMenuVars($i);
  }

  /**
   * Clean menu variables
   */
  function resetMenuVars($subcount) {
    for ( $i=0; $i<$subcount; $i++) {
      $this->resetMenuVar($i);
    }
  }

  function resetMenuVar($level) {
    $this->template->set_var($this->menuBlock($level).'var', '');
    $this->template->set_var($this->menuItemBlock($level).'var', '');
  }

  /**
   * Returns the name of menu block of given level
   */
  function menuBlock($level) {
    $prefix = '';
    for ( $i=0; $i<$level; $i++ ) {
      $prefix .= $this->sub;
    }
    return $prefix . $this->menu_handle;
  }

  /**
   * Returns the name of menuitem block of given level
   */
  function menuItemBlock($level) {
    return $this->menuBlock($level) . $this->item;
  }

  function preRenderNode(&$node) {
  }

  function renderNode(&$node) {
  }

  function postRenderNode(&$node) {
    if ( $node->nodename != 'root' ) {
      $level = $node->getLevel()-1;

      $this->template->set_var($node->data);
      $this->template->set_var('status', $node->hasData(ACTIVE)?'active':'inactive');
      $this->template->parse($this->menuItemBlock($level).'var',
			     $this->menuItemBlock($level),
			     true);
      foreach ( array_keys($node->data) as $varname ) {
	$this->template->set_var($varname, '');
      }
      $this->resetMenuVar($level+1);
    }
  }

  function shiftOut(&$node) {
    $level = $node->getLevel();
    $this->template->parse($this->menuBlock($level).'var',
			   $this->menuBlock($level));
  }

  function getRendered() {
    return $this->template->get($this->menu_handle.'var');
  }

} // end class TemplateMenu
?>
