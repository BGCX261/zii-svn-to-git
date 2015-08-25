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
 * @version $Id: CListMenu.php 49 2009-10-11 21:28:18Z poppitypop $
 * @package zii.widgets.menus
 * @since 1.1
 */
class CListMenu extends CBaseMenu
{
	/**
	* @var string CSS class that should be automatically added to the active item.
	* If false, CSS class will not be appended to the active item.  Defaults to 'active'
	*/
	public $activeClass='active';
	
	/**
	* @var array HTML attributes for the menu's containing <ul> tag.
	*/
	public $htmlOptions=array();
	
	/**
	* @var array HTML attributes to be applied to item labels that are not linked.
	* These HTML options are applied to a <span> tag that wraps the item labels.
	*/
	public $labelOptions=array();
	
	/**
	* Renders the menu.
	* 
	* @param mixed $items normalized list of items
	*/
	protected function renderMenu($items) {
		$htmlOptions = $this->htmlOptions;
		if (!isset($htmlOptions['id']))
			$htmlOptions['id']=$this->getId();
		if(!isset($htmlOptions['class']))
			$htmlOptions['class']='yiiListMenu';
			
		echo '<ul'.CHtml::renderAttributes($htmlOptions).'>';
		$this->renderMenuRecurse($items);
		echo '</ul>';
	}
	
	/**
	* Recurses through the menu and displays it
	* 
	* @param mixed $items normalized list of items
	*/
	protected function renderMenuRecurse($items) {
		foreach($items as $item) {
			echo '<li>';
			
			if ($item['url']!==false)
				echo CHtml::link($item['label'], $item['url'], $item['htmlOptions']);
			else
				echo CHtml::tag('span', $this->labelOptions, $item['label']);
				
			if (isset($item['items'])) {
				echo '<ul>';
				$this->renderMenu($item['items']);
				echo '</ul>';
			}
			
			echo '</li>';
		}
	}

}