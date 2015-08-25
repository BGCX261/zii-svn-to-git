<?php
/**
 * CRudColumn class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

Yii::import('zii.widgets.grid.CGridColumn');

/**
 * CRudColumn represents a grid view column that renders "view", "update" and "delete" buttons in each data cell.
 *
 * The name "rud" stands for "read" (view), "update" and "delete".
 *
 * By configuring {@link template}, one may choose not to display all of the three buttons.
 * For example, setting this property to be "{view} {update}" will only show the "view" and "update" buttons.
 *
 * Note that when clicking on a delete button, a confirmation dialog may be displayed. If confirmed,
 * the server side will receive a deletion request. The content of the grid view will be refreshed automatically after
 * the deletion is completed. For this reason, the deletion action should not render anything.
 * In case an error is detected on the server side, it should throw a {@link CHttpException}.
 *
 * Besides the default view, update and delete buttons, it is also possible to render additional buttons
 * by configuring the {@link buttons} property. Make sure the {@link template} property is also updated
 * accordingly so that the extra buttons are displayed at the desired places.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CRudColumn.php 78 2009-12-13 16:53:29Z qiang.xue $
 * @package zii.widgets.grid
 * @since 1.1
 */
class CRudColumn extends CGridColumn
{
	/**
	 * @var array the HTML options for the data cell tags.
	 */
	public $htmlOptions=array('class'=>'rud-column');
	/**
	 * @var array the HTML options for the header cell tag.
	 */
	public $headerHtmlOptions=array('class'=>'rud-column');
	/**
	 * @var array the HTML options for the footer cell tag.
	 */
	public $footerHtmlOptions=array('class'=>'rud-column');
	/**
	 * @var string the template that is used to render the content in each data cell.
	 * These tokens are recognized: {view}, {update} and {delete}. They will be replaced
	 * with the "view" button, "update" button and "delete" button, respectively.
	 */
	public $template='{view} {update} {delete}';
	/**
	 * @var string the label for the view button. Defaults to "View".
	 * Note that the label will not be HTML-encoded when rendering.
	 */
	public $viewButtonLabel;
	/**
	 * @var string the image URL for the view button. If not set, an integrated image will be used.
	 * You may set this property to be false to render a text link instead.
	 */
	public $viewButtonImageUrl;
	/**
	 * @var string a PHP expression that is evaluated for every view button and whose result is used
	 * as the URL for the view button. In this expression, the variable
	 * <code>$row</code> the row number (zero-based); <code>$data</code> the data model for the row;
	 * and <code>$this</code> the column object.
	 */
	public $viewButtonUrl='Yii::app()->controller->createUrl("view",array("id"=>$data->primaryKey))';
	/**
	 * @var array the HTML options for the view button tag.
	 */
	public $viewButtonOptions=array('class'=>'view');

	/**
	 * @var string the label for the update button. Defaults to "Update".
	 * Note that the label will not be HTML-encoded when rendering.
	 */
	public $updateButtonLabel;
	/**
	 * @var string the image URL for the update button. If not set, an integrated image will be used.
	 * You may set this property to be false to render a text link instead.
	 */
	public $updateButtonImageUrl;
	/**
	 * @var string a PHP expression that is evaluated for every update button and whose result is used
	 * as the URL for the update button. In this expression, the variable
	 * <code>$row</code> the row number (zero-based); <code>$data</code> the data model for the row;
	 * and <code>$this</code> the column object.
	 */
	public $updateButtonUrl='Yii::app()->controller->createUrl("update",array("id"=>$data->primaryKey))';
	/**
	 * @var array the HTML options for the update button tag.
	 */
	public $updateButtonOptions=array('class'=>'update');

	/**
	 * @var string the label for the delete button. Defaults to "Delete".
	 * Note that the label will not be HTML-encoded when rendering.
	 */
	public $deleteButtonLabel;
	/**
	 * @var string the image URL for the delete button. If not set, an integrated image will be used.
	 * You may set this property to be false to render a text link instead.
	 */
	public $deleteButtonImageUrl;
	/**
	 * @var string a PHP expression that is evaluated for every delete button and whose result is used
	 * as the URL for the delete button. In this expression, the variable
	 * <code>$row</code> the row number (zero-based); <code>$data</code> the data model for the row;
	 * and <code>$this</code> the column object.
	 */
	public $deleteButtonUrl='Yii::app()->controller->createUrl("delete",array("id"=>$data->primaryKey))';
	/**
	 * @var array the HTML options for the view button tag.
	 */
	public $deleteButtonOptions=array('class'=>'delete');
	/**
	 * @var string the confirmation message to be displayed when delete button is clicked.
	 * By setting this property to be false, no confirmation message will be displayed.
	 */
	public $deleteConfirmation;
	/**
	 * @var array the configuration for additional buttons. Each array element specifies a single button
	 * which has the following format:
	 * <pre>
	 * 'buttonID' => array(
	 *     'label'=>'...',     // text label of the button
	 *     'url'=>'...',       // the PHP expression for generating the URL of the button
	 *     'imageUrl'=>'...',  // image URL of the button. If not set or false, a text link is used
	 *     'options'=>array(...), // HTML options for the button tag
	 *     'click'=>'...',     // a JS function to be invoked when the button is clicked
	 * )
	 * </pre>
	 * Note that in order to display these additional buttons, the {@link template} property needs to
	 * be configured so that the corresponding button IDs appear as tokens in the template.
	 */
	public $buttons=array();

	/**
	 * Initializes the column.
	 * This method registers necessary client script for the RUD column.
	 * @param CGridView the grid view instance
	 */
	public function init()
	{
		$this->initDefaultButtons();

		foreach($this->buttons as $id=>$button)
		{
			if(strpos($this->template,'{'.$id.'}')===false)
				unset($this->buttons[$id]);
			else if(isset($button['click']))
			{
				if(!isset($button['options']['class']))
					$this->buttons[$id]['options']['class']=$id;
				if(strpos($button['click'],'js:')!==0)
					$this->buttons[$id]['click']='js:'.$button['click'];
			}
		}

		$this->registerClientScript();
	}

	/**
	 * Initializes the default buttons (view, update and delete).
	 */
	protected function initDefaultButtons()
	{
		if($this->viewButtonLabel===null)
			$this->viewButtonLabel=Yii::t('zii','View');
		if($this->updateButtonLabel===null)
			$this->updateButtonLabel=Yii::t('zii','Update');
		if($this->deleteButtonLabel===null)
			$this->deleteButtonLabel=Yii::t('zii','Delete');
		if($this->viewButtonImageUrl===null)
			$this->viewButtonImageUrl=$this->grid->baseScriptUrl.'/view.png';
		if($this->updateButtonImageUrl===null)
			$this->updateButtonImageUrl=$this->grid->baseScriptUrl.'/update.png';
		if($this->deleteButtonImageUrl===null)
			$this->deleteButtonImageUrl=$this->grid->baseScriptUrl.'/delete.png';
		if($this->deleteConfirmation===null)
			$this->deleteConfirmation=Yii::t('zii','Are you sure to delete this item?');

		foreach(array('view','update','delete') as $id)
		{
			if(!isset($this->buttons[$id]))
			{
				$this->buttons[$id]=array(
					'label'=>$this->{$id.'ButtonLabel'},
					'url'=>$this->{$id.'ButtonUrl'},
					'imageUrl'=>$this->{$id.'ButtonImageUrl'},
					'options'=>$this->{$id.'ButtonOptions'},
				);
			}
		}

		if(is_string($this->deleteConfirmation))
			$confirmation="if(!confirm(".CJavaScript::encode($this->deleteConfirmation).")) return false;";
		else
			$confirmation='';
		$this->buttons['delete']['click']=<<<EOD
function(){
	$confirmation
	$.ajax({
		type: 'POST',
		url: $(this).attr('href'),
		success: function() {
			$.fn.yiiGridView.update('{$this->grid->id}');
		},
		error: function(XMLHttpRequest, textStatus, errorThrown) {
			alert(XMLHttpRequest.responseText);
		},
	});
	return false;
}
EOD;
	}

	/**
	 * Registers the client scripts for the RUD column.
	 */
	protected function registerClientScript()
	{
		$js=array();
		foreach($this->buttons as $id=>$button)
		{
			if(isset($button['click']))
			{
				$function=CJavaScript::encode($button['click']);
				$js[]="jQuery('a.{$button['options']['class']}').live('click',$function);";
			}
		}

		if($js!==array())
			Yii::app()->getClientScript()->registerScript(__CLASS__.'#'.$this->id, implode("\n",$js));
	}

	/**
	 * Renders the data cell content.
	 * This method renders the view, update and delete buttons in the data cell.
	 * @param integer the row number (zero-based)
	 * @param mixed the data associated with the row
	 */
	protected function renderDataCellContent($row,$data)
	{
		$tr=array();
		ob_start();
		foreach($this->buttons as $id=>$button)
		{
			$this->renderButton($id,$button,$row,$data);
			$tr['{'.$id.'}']=ob_get_contents();
			ob_clean();
		}
		ob_end_clean();
		echo strtr($this->template,$tr);
	}

	/**
	 * Renders a link button.
	 * @param string the ID of the button
	 * @param array the button configuration which may contain 'label', 'url', 'imageUrl' and 'options' elements.
	 * See {@link buttons} for more details.
	 * @param integer the row number (zero-based)
	 * @param mixed the data object associated with the row
	 */
	protected function renderButton($id,$button,$row,$data)
	{
		$label=isset($button['label']) ? $button['label'] : $id;
		$url=isset($button['url']) ? $this->evaluateExpression($button['url'],array('data'=>$data,'row'=>$row)) : '#';
		$options=isset($button['options']) ? $button['options'] : array();
		if(!isset($options['title']))
			$options['title']=$label;
		if(isset($button['imageUrl']) && is_string($button['imageUrl']))
			echo CHtml::link(CHtml::image($button['imageUrl'],$label),$url,$options);
		else
			echo CHtml::link($label,$url,$options);
	}
}
