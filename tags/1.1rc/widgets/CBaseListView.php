<?php
/**
 * CBaseListView class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CBaseListView is the base class for {@link CListView} and {@link CGridView}.
 *
 * CBaseListView implements the common features needed by a view wiget for rendering multiple models.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CBaseListView.php 78 2009-12-13 16:53:29Z qiang.xue $
 * @package zii.widgets
 * @since 1.1
 */
abstract class CBaseListView extends CWidget
{
	/**
	 * @var IDataProvider the data provider for the view.
	 */
	public $dataProvider;
	/**
	 * @var string the tag name for the view container. Defaults to 'div'.
	 */
	public $tagName='div';
	/**
	 * @var array the HTML options for the view container tag.
	 */
	public $htmlOptions=array();
	/**
	 * @var boolean whether to enable sorting. Note that if the {@link IDataProvider::sort} property
	 * of {@link dataProvider} is false, this will be treated as false as well. When sorting is enabled,
	 * sortable columns will have their headers clickable to trigger sorting along that column.
	 * Defaults to true.
	 * @see sortableAttributes
	 */
	public $enableSorting=true;
	/**
	 * @var boolean whether to enable pagination. Note that if the {@link IDataProvider::pagination} property
	 * of {@link dataProvider} is false, this will be treated as false as well. When pagination is enabled,
	 * a pager will be displayed in the view so that it can trigger pagination of the data display.
	 * Defaults to true.
	 */
	public $enablePagination=true;
	/**
	 * @var array the configuration for the pager. Defaults to <code>array('class'=>'CLinkPager')</code>.
	 * @see enablePagination
	 */
	public $pager=array('class'=>'CLinkPager');
	/**
	 * @var string the template to be used to control the layout of various sections in the view.
	 * These tokens are recognized: {summary}, {items} and {pager}. They will be replaced with the
	 * summary text, the items, and the pager.
	 */
	public $template="{summary}\n{items}\n{pager}";
	/**
	 * @var string the summary text template for the view. These tokens are recognized:
	 * {start}, {end} and {count}. They will be replaced with the starting row number, ending row number
	 * and total number of data records.
	 */
	public $summaryText;
	/**
	 * @var string the message to be displayed when {@link dataProvider} does not have any data.
	 */
	public $emptyText;
	/**
	 * @var string the CSS class name for the container of all data item display. Defaults to 'items'.
	 */
	public $itemsCssClass='items';
	/**
	 * @var string the CSS class name for the summary text container. Defaults to 'summary'.
	 */
	public $summaryCssClass='summary';
	/**
	 * @var string the CSS class name for the pager container. Defaults to 'pager'.
	 */
	public $pagerCssClass='pager';

	/**
	 * Initializes the view.
	 * This method will initialize required property values and instantiate {@link columns} objects.
	 */
	public function init()
	{
		if($this->dataProvider===null)
			throw new CException(Yii::t('zii','The "dataProvider" property cannot be empty.'));

		$this->dataProvider->getData();

		$this->htmlOptions['id']=$this->getId();

		if($this->enableSorting && $this->dataProvider->getSort()===false)
			$this->enableSorting=false;
		if($this->enablePagination && $this->dataProvider->getPagination()===false)
			$this->enablePagination=false;
	}

	/**
	 * Renders the view.
	 * This is the main entry of the whole view rendering.
	 * Child classes should mainly override {@link renderContent} method.
	 */
	public function run()
	{
		$this->registerClientScript();

		echo CHtml::openTag($this->tagName,$this->htmlOptions)."\n";

		$this->renderKeys();
		$this->renderContent();

		echo CHtml::closeTag($this->tagName);
	}

	/**
	 * Renders the main content of the view.
	 * The content is divided into sections, such as summary, items, pager.
	 * Each section is rendered by a method named as "renderXyz", where "Xyz" is the section name.
	 * The rendering results will replace the corresponding placeholders in {@link template}.
	 */
	public function renderContent()
	{
		ob_start();
		echo preg_replace_callback("/{(\w+)}/",array($this,'renderSection'),$this->template);
		ob_end_flush();
	}

	/**
	 * Renders a section.
	 * This method is invoked by {@link renderContent} for every placeholder found in {@link template}.
	 * It should return the rendering result that would replace the placeholder.
	 * @param array the matches, where $matches[0] represents the whole placeholder,
	 * while $matches[1] contains the name of the matched placeholder.
	 * @return string the rendering result of the section
	 */
	protected function renderSection($matches)
	{
		$method='render'.$matches[1];
		if(method_exists($this,$method))
		{
			$this->$method();
			$html=ob_get_contents();
			ob_clean();
			return $html;
		}
		else
			return $matches[0];
	}

	/**
	 * Renders the empty message when there is no data.
	 */
	public function renderEmptyText()
	{
		$emptyText=$this->emptyText===null ? Yii::t('zii','No results found.') : $this->emptyText;
		echo CHtml::tag('span', array('class'=>'empty'), $emptyText);
	}

	/**
	 * Renders the key values of the data in a hidden tag.
	 */
	public function renderKeys()
	{
		echo CHtml::openTag('div',array(
			'class'=>'keys',
			'style'=>'display:none',
			'title'=>Yii::app()->getRequest()->getUrl(),
		));
		foreach($this->dataProvider->getKeys() as $key)
			echo "<span>$key</span>";
		echo "</div>\n";
	}

	/**
	 * Renders the summary text.
	 */
	public function renderSummary()
	{
		if(($count=$this->dataProvider->getItemCount())<=0)
			return;

		echo '<div class="'.$this->summaryCssClass.'">';
		if($this->enablePagination)
		{
			if(($summaryText=$this->summaryText)===null)
				$summaryText=Yii::t('zii','Displaying {start}-{end} of {count} result(s).');
			$pagination=$this->dataProvider->getPagination();
			$start=$pagination->currentPage*$pagination->pageSize+1;
			echo strtr($summaryText,array(
				'{start}'=>$start,
				'{end}'=>$start+$count-1,
				'{count}'=>$this->dataProvider->getTotalItemCount(),
			));
		}
		else
		{
			if(($summaryText=$this->summaryText)===null)
				$summaryText=Yii::t('zii','Total {count} result(s).');
			echo strtr($summaryText,array('{count}'=>$count));
		}
		echo '</div>';
	}

	/**
	 * Renders the pager.
	 */
	public function renderPager()
	{
		if($this->dataProvider->getItemCount()<=0 || !$this->enablePagination)
			return;

		$pager=array();
		$class='CLinkPager';
		if(is_string($this->pager))
			$class=$this->pager;
		else if(is_array($this->pager))
		{
			$pager=$this->pager;
			if(isset($pager['class']))
			{
				$class=$pager['class'];
				unset($pager);
			}
		}
		$pager['pages']=$this->dataProvider->getPagination();
		echo '<div class="'.$this->pagerCssClass.'">';
		$this->widget($class,$pager);
		echo '</div>';
	}

	/**
	 * Registers necessary client scripts.
	 * This method is invoked by {@link run}.
	 * Child classes may override this method to register customized client scripts.
	 */
	public function registerClientScript()
	{
	}

	/**
	 * Renders the data items for the view.
	 * Each item is corresponding to a single data model instance.
	 * Child classes should override this method to provide the actual item rendering logic.
	 */
	abstract public function renderItems();
}