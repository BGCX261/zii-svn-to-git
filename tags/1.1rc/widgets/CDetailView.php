<?php
/**
 * CDetailView class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CDetailView displays the detail of a single model instance.
 *
 * CDetailView is best used for displaying a model in a regular format (e.g. each model attribute
 * is displayed as a row in a table.)
 *
 * CDetailView uses the {@link attributes} property to determines which model attributes
 * should be displayed and how they should be formatted.
 *
 * A typical usage of CDetailView is as follows:
 * <pre>
 * $this->widget('zii.widgets.CDetailView', array(
 *     'model'=>$model,
 *     'attributes'=>array(
 *         'title',             // title attribute (in plain text)
 *         'owner.name',        // an attribute of the related object "owner"
 *         'description:html',  // description attribute in HTML
 *         array(               // related city displayed as a link
 *             'label'=>'City',
 *             'value'=>CHtml::link(CHtml::encode($model->city->name),
 *                                  array('city/view','id'=>$model->city->id)),
 *         ),
 *     ),
 * ));
 * </pre>
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CDetailView.php 78 2009-12-13 16:53:29Z qiang.xue $
 * @package zii.widgets
 * @since 1.1
 */
class CDetailView extends CWidget
{
	/**
	 * @var mixed the data model whose details are to be displayed. This can be either a {@link CModel} instance
	 * (e.g. a {@link CActiveRecord} object or a {@link CFormModel} object) or an associative array.
	 */
	public $model;
	/**
	 * @var array a list of attributes to be displayed in the detail view. Each array element
	 * represents the specification for displaying one particular attribute.
	 *
	 * An attribute can be specified as a string in the format of "Name:Type:Label", where "Name"
	 * refers to the attribute name, "Type" indicates the type of the attribute and how the attriute
	 * value should be formatted for display, and "Label" means the label associated
	 * with the attribute. Both "Type" and "Label" are optional. "Name" can be either a property
	 * (e.g. "title") or a sub-property (e.g. "owner.username"). "Label" represents the label for the
	 * attribute display. If it is not given, "Name" will be used to generate the appropriate label.
	 * "Type" represents the type of the attribute. It determines how the attribute value should be formatted and displayed.
	 * "Type" can be one of the followings:
	 * <ul>
	 * <li>text: the attribute value will be HTML-encoded when rendering. This is the default type when "TypeName" is not specified.</li>
	 * <li>ntext: the {@link formatNtext} method will be called to format the attribute value as a HTML-encoded plain text with newlines converted as the HTML &lt;br /&gt; tags.</li>
	 * <li>html: the attribute value will be rendered as is without any encoding.</li>
	 * <li>date: the {@link formatDate} method will be called to format the attribute value as a date.</li>
	 * <li>time: the {@link formatTime} method will be called to format the attribute value as a time.</li>
	 * <li>datetime: the {@link formatDatetime} method will be called to format the attribute value as a date with time.</li>
	 * <li>boolean: the {@link formatBoolean} method will be called to format the attribute value as a boolean display.</li>
	 * <li>number: the {@link formatNumber} method will be called to format the attribute value as a number display.</li>
	 * <li>email: the {@link formatEmail} method will be called to format the attribute value as a mailto link.</li>
	 * <li>image: the {@link formatImage} method will be called to format the attribute value as an image tag where the attribute value is the image URL.</li>
	 * <li>url: the {@link formatUrl} method will be called to format the attribute value as a hyperlink where the attribute value is the URL.</li>
	 * </ul>
	 * If a child class extending CDetailView implements additional format methods, they may also be recognized as
	 * extra types. For example, a method named "formatXyz" will introduce the type "xyz" (case-insensitive).
	 *
	 * An attribute can also be specified in terms of an array with the following elements:
	 * <ul>
	 * <li>label: the label associated with the attribute. If this is not specified, the following "name" element
	 * will be used to generate an appropriate label.</li>
	 * <li>value: the value to be displayed. If this is not specified, the following "name" element will be used
	 * to retrieve the corresponding attribute value for display. Note that this value will NOT be HTML-encoded.</li>
	 * <li>name: the name of the attribute. This can be either a property or a sub-property of the model.
	 * If the above "value" element is specified, this will be ignored.</li>
	 * <li>type: the type of the attribute that determines how the attribute value would be formatted.
	 * Please see above for possible values. If the above "value" element is specified, this will be ignored.</li>
	 * </ul>
	 */
	public $attributes;
	/**
	 * @var string the text to be displayed when an attribute value is null. Defaults to "Not set".
	 */
	public $nullDisplay;
	/**
	 * @var string the format string to be used to format a date using PHP date() function. Defaults to 'Y/m/d'.
	 */
	public $dateFormat='Y/m/d';
	/**
	 * @var string the format string to be used to format a time using PHP date() function. Defaults to 'h:i:s A'.
	 */
	public $timeFormat='h:i:s A';
	/**
	 * @var string the format string to be used to format a date and time using PHP date() function. Defaults to 'Y/m/d h:i:s A'.
	 */
	public $datetimeFormat='Y/m/d h:i:s A';
	/**
	 * @var array the format used to format a number with PHP number_format() function.
	 * Three elements may be specified: "decimals", "decimalSeparator" and "thousandSeparator". They
	 * correspond to the number of digits after the decimal point, the character displayed as the decimal point,
	 * and the thousands separator character.
	 */
	public $numberFormat=array('decimals'=>null, 'decimalSeparator'=>null, 'thousandSeparator'=>null);
	/**
	 * @var string the text to be displayed when formatting a boolean value which is true. Defaults to 'Yes'.
	 */
	public $trueText;
	/**
	 * @var string the text to be displayed when formatting a boolean value which is false. Defaults to 'No'.
	 */
	public $falseText;
	/**
	 * @var string the name of the tag for rendering the detail view. Defaults to 'table'.
	 * @see itemTemplate
	 */
	public $tagName='table';
	/**
	 * @var string the template used to render a single attribute. Defaults to a table row.
	 * These tokens are recognized: "{class}", "{label}" and "{value}". They will be replaced
	 * with the CSS class name for the item, the label and the attribute value, respectively.
	 * @see itemCssClass
	 */
	public $itemTemplate="<tr class=\"{class}\"><th>{label}</th><td>{value}</td></tr>\n";
	/**
	 * @var array the CSS class names for the items displaying attribute values. If multiple CSS class names are given,
	 * they will be assigned to the items sequentially and repeatedly.
	 * Defaults to <code>array('odd', 'even')</code>.
	 */
	public $itemCssClass=array('odd','even');
	/**
	 * @var array the HTML options used for {@link tagName}
	 */
	public $htmlOptions=array('class'=>'detail-view');
	/**
	 * @var string the base script URL for all detail view resources (e.g. javascript, CSS file, images).
	 * Defaults to null, meaning using the integrated detail view resources (which are published as assets).
	 */
	public $baseScriptUrl;
	/**
	 * @var string the URL of the CSS file used by this detail view. Defaults to null, meaning using the integrated
	 * CSS file. If this is set false, you are responsible to explicitly include the necessary CSS file in your page.
	 */
	public $cssFile;

	/**
	 * Initializes the detail view.
	 * This method will initialize required property values.
	 */
	public function init()
	{
		if($this->model===null)
			throw new CException(Yii::t('zii','Please specify the "model" property.'));
		if($this->attributes===null)
		{
			if($this->model instanceof CModel)
				$this->attributes=$this->model->attributeNames();
			else if(is_array($this->model))
				$this->attributes=array_keys($this->model);
			else
				throw new CException(Yii::t('zii','Please specify the "attributes" property.'));
		}
		if($this->nullDisplay===null)
			$this->nullDisplay='<span class="null">'.Yii::t('zii','Not set').'</span>';
		if($this->trueText===null)
			$this->trueText=Yii::t('zii','Yes');
		if($this->falseText===null)
			$this->falseText=Yii::t('zii','No');
		$this->htmlOptions['id']=$this->getId();

		if($this->baseScriptUrl===null)
			$this->baseScriptUrl=Yii::app()->getAssetManager()->publish(Yii::getPathOfAlias('zii.widgets.assets')).'/detailview';

		if($this->cssFile!==false)
		{
			if($this->cssFile===null)
				$this->cssFile=$this->baseScriptUrl.'/styles.css';
			Yii::app()->getClientScript()->registerCssFile($this->cssFile);
		}
	}

	/**
	 * Renders the detail view.
	 * This is the main entry of the whole detail view rendering.
	 */
	public function run()
	{
		echo CHtml::openTag($this->tagName,$this->htmlOptions);

		$n=is_array($this->itemCssClass) ? count($this->itemCssClass) : 0;
		foreach($this->attributes as $i=>$attribute)
		{
			if(is_string($attribute))
			{
				if(!preg_match('/^([\w\.]+)(:(\w*))?(:(.*))?$/',$attribute,$matches))
					throw new CException(Yii::t('zii','The attribute must be specified in the format of "Name:Type:Label", where "Type" and "Label" are optional.'));
				$attribute=array(
					'name'=>$matches[1],
					'type'=>isset($matches[3]) ? $matches[3] : 'text',
				);
				if(isset($matches[5]))
					$attribute['label']=$matches[5];
			}

			$tr=array('{label}'=>'', '{class}'=>$n ? $this->itemCssClass[$i%$n] : '');

			if(isset($attribute['label']))
				$tr['{label}']=$attribute['label'];
			else if(isset($attribute['name']) && $this->model instanceof CModel)
				$tr['{label}']=$this->model->getAttributeLabel($attribute['name']);

			if(!isset($attribute['type']))
				$attribute['type']='text';
			if(isset($attribute['value']))
				$tr['{value}']=$attribute['value'];
			else if(isset($attribute['name']))
			{
				$value=CHtml::value($this->model,$attribute['name']);
				$method='format'.$attribute['type'];
				if(method_exists($this,$method))
				{
					if($value!==null)
						$tr['{value}']=$this->$method($value);
				}
				else
					throw new CException(Yii::t('zii','Unknown type "{type}".',array('{type}'=>$attribute['type'])));
			}

			if(!isset($tr['{value}']))
				$tr['{value}']=$this->nullDisplay;

			echo strtr($this->itemTemplate,$tr);
		}

		echo CHtml::closeTag($this->tagName);
	}

	/**
	 * Formats the attribute as a HTML-encoded plain text.
	 * @param mixed attribute value
	 * @return string the formatted result
	 */
	public function formatText($value)
	{
		return CHtml::encode($value);
	}

	/**
	 * Formats the attribute as a HTML-encoded plain text and converts newlines with HTML br tags.
	 * @param mixed attribute value
	 * @return string the formatted result
	 */
	public function formatNtext($value)
	{
		return nl2br(CHtml::encode($value));
	}

	/**
	 * Formats the attribute as HTML text without any encoding.
	 * @param mixed attribute value
	 * @return string the formatted result
	 */
	public function formatHtml($value)
	{
		return $value;
	}

	/**
	 * Formats the attribute as a date.
	 * @param mixed attribute value
	 * @return string the formatted result
	 * @see dateFormat
	 */
	public function formatDate($value)
	{
		return date($this->dateFormat,$value);
	}

	/**
	 * Formats the attribute as a time.
	 * @param mixed attribute value
	 * @return string the formatted result
	 * @see timeFormat
	 */
	public function formatTime($value)
	{
		return date($this->timeFormat,$value);
	}

	/**
	 * Formats the attribute as a date and time.
	 * @param mixed attribute value
	 * @return string the formatted result
	 * @see datetimeFormat
	 */
	public function formatDatetime($value)
	{
		return date($this->datetimeFormat,$value);
	}

	/**
	 * Formats the attribute as a boolean.
	 * @param mixed attribute value
	 * @return string the formatted result
	 * @see trueText
	 * @see falseText
	 */
	public function formatBoolean($value)
	{
		return $value ? $this->trueText : $this->falseText;
	}

	/**
	 * Formats the attribute as a mailto link.
	 * @param mixed attribute value
	 * @return string the formatted result
	 */
	public function formatEmail($value)
	{
		return CHtml::mailto($value);
	}

	/**
	 * Formats the attribute as an image tag.
	 * @param mixed attribute value
	 * @return string the formatted result
	 */
	public function formatImage($value)
	{
		return CHtml::image($value);
	}

	/**
	 * Formats the attribute as a hyperlink.
	 * @param mixed attribute value
	 * @return string the formatted result
	 */
	public function formatUrl($value)
	{
		$url=$value;
		if(strpos($url,'http://')!==0 && strpos($url,'https://')!==0)
			$url='http://'.$url;
		return CHtml::link(CHtml::encode($value),$url);
	}

	/**
	 * Formats the attribute as a number using PHP number_format() function.
	 * @param mixed attribute value
	 * @return string the formatted result
	 * @see numberFormat
	 */
	public function formatNumber($value)
	{
		return number_format($value,$this->numberFormat['decimals'],$this->numberFormat['decimalSeparator'],$this->numberFormat['thousandSeparator']);
	}
}
