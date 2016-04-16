<?php
/*
 * eHR Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.areschina.com/license/LICENSE.txt.
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@areschina.com so we can send you a copy immediately.
 *
 * @category   eHR
 * @package    Form
 * @subpackage Input_List
 * @copyright  (C)1980 - 2008 ARES INERNATIONAL CORPORATION (http://www.areschina.com)
 * @license    http://www.areschina.com/license/LICENSE.txt.
 * @version    $Id:Input_List.class.php Jan 7, 2008 11:08:05 AM Dennis $
 */

/**
 * Forms Element Select 
 * 下拉清单栏位, 有 Pulldown或是 List 方式
 * @category   eHR
 * @package    Form
 * @subpackage Input_List
 * @copyright  (C)1980 - 2008  ARES INERNATIONAL CORPORATION (http://www.areschina.com)
 * @version    1.0
 * @license    http://www.areschina.com/license/LICENSE.txt
 * @author     Dennis 
 */
class Input_List extends Input_Abstract
{
    /**
     * List type. DropDownList/List/MultiRowList
     *
     * @var unknown_type
     */
    //public $listType;

    //public $width;
    public $size;
    /**
     * Allow user multiple select
     *
     * @var boolean
     */
    public $allowMultiSelect;

    /**
     * List 中的值分组显示
     *
     * @var boolean
     */
    public $elementsGrouped;

    /**
     * Selected Item's Value
     * 如果是允许多选的List 这里应该是 array
     * @var mixed, string or array
     */
    protected $_selectedItemValue;

    /**
     * List 资料来源
     *
     * @var array
     */
    protected $_dataSource;

    /**
     * Input List Supported Type
     *
     * @var array
     */
    //private $_listTypeArray = array('DropDownList','List','MultiRowList');

    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->_init($config);

    }// end class constructor()

    protected function _init(array $config)
    {
    	//pr($config);
        if (is_array($config))
        {
            foreach($config as $key => $value)
            {
                switch(strtolower($key))
                {
                    /*
                     case 'width':
                     $this->width = $value;
                     break;

                     case 'listType':
                     if (!in_array($value,$this->_listTypeArray))
                     {
                     $types = '<b>DropDownList</b>,<b>List</b>,<b>MultiRowList</b>';
                     trigger_error(sprintf('Unsupported List Type :<font color="red">%s</font>,Avalid type:%s',$value,$types),E_USER_ERROR);
                     }// end if
                     $this->listType = $value;
                     break;*/
                    case 'size':
                        $this->size = $value;
                        break;
                    case 'allowmultiselect':
                        $this->allowMultiSelect = $value;
                        break;
                    case 'selectedvalue':
                        $this->_selectedItemValue = $value;
                        break;
                    case 'datasource':
                        $this->_dataSource = $value;
                        break;
                    case 'className':
                    	$this->className = $value;
                    	break;
                    default:break;
                }// end switch
            }// end foreach
        }// end if
    }// end _init()

    public function render()
    {
        $input_list_html  = '<select ';
        $input_list_html .= 'name="'.$this->_name.'" ';
        $input_list_html .= 'id="'.$this->_id.'" ';
        $input_list_html .= is_numeric($this->size) && $this->size>0 ? 'size="'.$this->size.'" ' : '';
        //$input_list_html .= isset($this->width) ? 'width = "'.$this->width.'" ':'';
        $input_list_html .= isset($this->allowMultiSelect) && $this->allowMultiSelect ? 'multiple ' : '';
        $input_list_html .= (empty($this->className) && empty($this->requiredFieldStyle)) ? '' :
                            'class="'.((isset($this->requiredFieldStyle) && $this->isRequired) ?
        $this->requiredFieldStyle : $this->className).'" ';

        $align_style = isset($this->align) ? 'text-align:'.$this->align.';' : '';
        $style_html  = empty($align_style) && empty($this->style) ? '' : $align_style.$this->style;
        $input_list_html .= empty($style_html) ? '' : 'style="'.$style_html.';" ';
        $input_list_html .= empty($this->disabled)  ? '' : 'disabled="'.$this->disabled.'" ';
        $input_list_html .= empty($this->hideFocus) ? '' : 'hideFocus="'.$this->hideFocus.'" ';
        $input_list_html .= empty($this->tabIndex)  ? '' : 'tabIndex="'.$this->tabIndex.'" ';
        $input_list_html .= empty($this->tooltip)   ? '' : 'title="'.$this->toolTip.'" ';
        $input_list_html .= '>';
        $input_list_html .= '<option value=""></option>';
        if (is_array($this->_dataSource))
        {
        	$c = count($this->_dataSource);
        	for ($i=0; $i<$c; $i++)
        	{
        		$v = $this->_dataSource[$i][0];
        		$t = $this->_dataSource[$i][1];
        		$input_list_html .= '<option value="'.$v.'" ';
        		if(!is_array($this->_selectedItemValue))
                {
                    if ($v == $this->_selectedItemValue)
                    $input_list_html .= 'selected ';
                }else{// 多个选中值时的处理
                    foreach($this->_selectedItemValue as $key1=>$value1)
                    {
                        if ($v == $value1)
                        {
                            $input_list_html .= 'selected ';
                            break;
                        }// end if
                    }// end foreach
                }// end if
        		$input_list_html .='>'.$t.'</option>';
        	}// end for loop
        }// end if
        $input_list_html .= '</select>';
        return $input_list_html;
    }// end render()
}// end class Input_List
?>