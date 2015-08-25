<?php
/**
 * CDataColumn class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

Yii::import('zii.widgets.grid.CGridColumn');

/**
 * CDataColumn represents a grid view column that is associated with a data attribute or expression.
 *
 * Either {@link dataField} or {@link dataExpression} should be specified. The former specifies
 * a data attribute name, while the latter a PHP expression whose value should be rendered instead.
 *
 * The property {@link sortable} determines whether the grid view can be sorted according to this column.
 * Note that the {@link dataField} should always be set if the column needs to be sortable. The {@link dataField}
 * value will be used by {@link CSort} to render a clickable link in the header cell to trigger the sorting.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CDataColumn.php 73 2009-12-10 16:01:01Z qiang.xue $
 * @package zii.widgets.grid
 * @since 1.1
 */
class CDataColumn extends CGridColumn
{
	/**
	 * @var string the attribute name of the data model. The corresponding attribute value will be rendered
	 * in each data cell. If {@link dataExpression} is specified, this property will be ignored
	 * unless the column needs to be sortable.
	 * @see dataExpression
	 * @see sortable
	 */
	public $dataField;
	/**
	 * @var string a PHP expression that will be evaluated for every data cell and whose result will be rendered
	 * as the content of the data cells. In this expression, the variable
	 * <code>$row</code> the row number (zero-based); <code>$data</code> the data model for the row;
	 * and <code>$this</code> the column object.
	 */
	public $dataExpression;
	/**
	 * @var boolean whether the attribute value corresponding to {@link dataField} should be HTML-encoded
	 * before rendering. Defaults to true. Note that this property does not apply to the values evaluated with {@link dataExpression}.
	 */
	public $encodeData=true;
	/**
	 * @var boolean whether the column is sortable. If so, the header celll will contain a link that may trigger the sorting.
	 * Defaults to true. Note that if {@link dataField} is not set, or if {@link dataField} is not allowed by {@link CSort},
	 * this property will be treated as false.
	 * @see dataField
	 */
	public $sortable=true;

	/**
	 * Initializes the column.
	 */
	public function init()
	{
		parent::init();
		if($this->dataField===null)
			$this->sortable=false;
	}

	/**
	 * Renders the header cell content.
	 * This method will render a link that can trigger the sorting if the column is sortable.
	 */
	protected function renderHeaderCellContent()
	{
		if($this->grid->enableSorting && $this->sortable && $this->dataField!==null)
			echo $this->grid->dataProvider->getSort()->link($this->dataField,$this->header);
		else
			parent::renderHeaderCellContent();
	}

	/**
	 * Renders the data cell content.
	 * This method evaluates {@link dataExpression} or {@link dataField} and renders the result.
	 * @param integer the row number (zero-based)
	 * @param mixed the data associated with the row
	 */
	protected function renderDataCellContent($row,$data)
	{
		if($this->dataExpression!==null)
			echo $this->evaluateExpression($this->dataExpression,array('data'=>$data,'row'=>$row));
		else if($this->dataField!==null)
		{
			$value=CHtml::value($data,$this->dataField);
			if($this->encodeData===true)
				$value=CHtml::encode($value);
			echo $value;
		}
		else
			parent::renderDataCellContent($row,$data);
	}
}
