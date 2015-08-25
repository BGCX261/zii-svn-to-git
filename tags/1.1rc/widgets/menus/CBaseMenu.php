<?php
/**
 * CBaseMenu class file.
 *
 * @author Jonah Turnquist <poppitypop@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */
 
 /**
 * CBaseMenu is a base class for menu widgets.
 * 
 * @author Jonah Turnquist <poppitypop@gmail.com>
 * @version $Id: CBaseMenu.php 49 2009-10-11 21:28:18Z poppitypop $
 * @package zii.widgets.menus
 * @since 1.1
 */
abstract class CBaseMenu extends CWidget
{
	/**
	* @var array The menu items to render.
	* 
	* Format:
	* <pre>
	* 	array(
	* 		array('label', 'url', additional options..),
	* 		array('Posts', array('/post/list')),
	* 		//.. more items
	* 	)
	* </pre>
	* 
	* The url may be in any format accepted by {@link CHtml::normalizeUrl()}, or false if the item
	* is not linked.
	* 
	* Possible additional options:
	* <ul>
	* <li>'items': a nested set of items.  Should be in the same format as the parent items.</li>
	* <li>'visible': whether or not the item is rendered.  Useful for access control</li>
	* <li>'pattern': pattern used to check if this item matches the current page.  If it is a string and it
	* 		begins with a slash, it is considered as a regular expression.  If set to false, the url 
	* 		option is used as the pattern.  Defualts to false.</li>
	* <li>'params': GET paramaters that must match in the current request and the pattern for the item
	* 		to be considered active.  Defaults to true, meaning all params in the request must match the params
	* 		in the pattern.  Set to false if none of the params need to match the pattern for the item to be 
	* 		considered active</li>
	* <li>'htmlOptions': extra htmlOptions sent to {@link CHtml::link()}</li>
	* </ul>
	* 
	* Nested menus are also supported.
	*/
	public $items = array();
	
	/**
	* @var string CSS class that should be automatically added to the active item.
	* If false, CSS class will not be appended to the active item (default)
	*/
	public $activeClass = false;
	
	/**
	* @var bool Whether to encode the item labels.  Defaults to true.
	*/
	public $encodeLabels = true;
	
	/**
	* @var array The items for this menu parsed by {@link parseItems()}
	*/
	protected $parsedItems;

	
	/**
	* Parses the menu items so they are ready to be used
	*/
	public function init() {
	   $this->parsedItems = $this->parseItems($this->items);
	}

	/**
	* Executes the widget.
	* This overrides the parent implementation.
	*/
	public function run() {
		$this->renderMenu($this->parsedItems);
	}
	
	/**
	* Renders the menu.
	* 
	* @param mixed $items parsed and normalized list of items
	* @see parseItems()
	*/
	abstract protected function renderMenu($items);
	
	/**
	* Parses items as inputted by user and returns it in a normalized way.
	* Handles nested items.  Output will look like something like this:
	* <pre>
	* array(
	* 	array(
	* 		'label'=>'Yii',
	* 		'url'=>'http://www.yiiframework.com'
	* 	),
	* 	array(
	* 		'label'=>'Posts',
	* 		'url'=>array('/post/list'), // can be false
	* 		'htmlOptions'=>array()
	* 		'active'=>false
	* 		'items'=>array(
	* 			//nested item set in the same format as parent item sets.
	* 		)
	* 	),
	* )
	* </pre>
	* Non-visible items are not included in the returned result.  The 'url' option will be false
	* if the item is not meant to be clickable.
	* 
	* @param array $rawItems Items in user-written format
	* @return array Items ready to be rendered.
	*/
	protected function parseItems($rawItems) {
		$items = array();
		foreach($rawItems as $key => $rawItem) {
			if (isset($rawItem['visible']) && !$rawItem['visible'])
				continue;
			
			if (isset($rawItem['items']))
				$item['items'] = $this->parseItems($rawItem['items']);
				
			$item['label'] = $this->encodeLabels ? CHtml::encode($rawItem[0]) : $rawItem[0];
			$item['url'] = isset($rawItem[1]) ? $rawItem[1] : false;
			$item['htmlOptions'] = isset($rawItem['htmlOptions']) ? $rawItem['htmlOptions'] : array();
			
			$pattern = isset($rawItem['pattern'])&&($rawItem['pattern']!==false) ? $rawItem['pattern'] : $item['url'];
			$params = isset($rawItem['params']) ? $rawItem['params'] : true;
			$item['active'] = $this->isActive($pattern, $params);
			
			if ($item['active'] && ($this->activeClass !== false)) {
				//append CSS class
				if (isset($item['htmlOptions']['class']))
					$item['htmlOptions']['class'] .= ' '.$this->activeClass;
				else
					$item['htmlOptions']['class'] = $this->activeClass;
			}

			$items[]=$item;
		}
		return $items;
	}
	
	/**
	* Decides if $pattern matches the current request.
	* 
	* @param mixed $pattern the pattern to compare with the given request.
	* @param array $params GET paramaters that must match in the $pattern and the current request
	* for $pattern to be considered active.  If true, all params must be matched and
	* present in the given request.  If false, none have to match.  Defaults to true.
	* @return bool whether $pattern is the active page
	*/
	protected function isActive($pattern, $params=true) {
		$controllerID = $this->controller->uniqueId;
		$actionID = $this->controller->action->id;
		
		if (!is_array($pattern)) { //pattern is either route or regular expresstion
			if (strpos($pattern, '/')!==0) //pattern is route
				return ($pattern === $controllerID.'/'.$actionID);
			else { //regular expression
				return preg_match($pattern, $controllerID.'/'.$actionID);
			}
		}
		
		$pattern[0] = trim($pattern[0],'/'); //normalize the pattern a bit
		if (strpos($pattern[0], '/') !== false)
			$matched = $pattern[0]===$controllerID.'/'.$actionID; // controller/action specified in url
		else
			$matched = $pattern[0]===$controllerID; // only controller specified in url
			
		if ($params===false)
			return $matched;
			
		if ($matched && count($pattern)>1) {
			foreach (array_splice($pattern, 1) as $name => $value) {
				if ((($params===true) || (in_array($name, $params))) && (!isset($_GET[$name]) || $_GET[$name]!=$value))
					return false;
			}
			return true;
		}
		else
			return $matched;
	}
}