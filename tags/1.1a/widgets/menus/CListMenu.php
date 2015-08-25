<?php
/**
 * CListMenu class file.
 *
 * @author Jonah Turnquist <poppitypop@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */
 
 /**
 * CListMenu displays a list menu.
 * 
 * @author Jonah Turnquist <poppitypop@gmail.com>
 * @version $Id: CListMenu.php 34 2009-09-26 05:35:56Z poppitypop $
 * @package zii.widgets.menus
 * @since 1.1
 */
class CListMenu extends CBaseMenu
{
	/**
	* @var string The CSS class to have on the active item.  Defaults to 'active'
	*/
	public $activeClass='active';
	
	 /**
	 * Executes the widget.
	 * This overrides the parent implementation.
	 */
	public function run() {
		$items = $this->parseItems($this->items);
		$this->renderMenu($items);
	}
	
	/**
	* Renders the menu.
	* 
	* @param mixed $items normalized list of items
	*/
	protected function renderMenu($items) {
		echo '<ul>';
		foreach($items as $item) {
			echo '<li>';
			
			if ($item['url']!==false)
				echo CHtml::link($item['label'], $item['url'], $item['htmlOptions']);
			else
				echo CHtml::encode($item['label']);
				
			if (isset($item['subMenu']))
				$this->renderMenu($item['subMenu']);
			
			echo '</li>';
		}
		echo '</ul>';
	}

}