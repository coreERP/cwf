<?php

/**
 * @link http://www.vishwayon.com/
 * @copyright Copyright (c) 2008 Vishwayon Software Pvt Ltd
 * @license http://www.vishwayon.com/license/
 */

namespace cwf\widgets;

/**
 * DisplayFieldWidget: This implements functionality of CwfDisplayField
 *
 * @author girish
 */
class DisplayFieldWidget implements \cwf\base\ICwfHtmlType, \cwf\base\ICwfArrayType {
    use \cwf\base\CwfObjectType;
    
    public $fieldTempl = '<table class="table"><thead class="thead-light"><tr>{cols}</tr></thead><tbody><tr></tr></tbody></table>';
    
    public function __construct() {
        $this->registerType('displayField');
    }

    public function emitHtml(\SimpleXMLElement $xel): string {
        $html = '';
        foreach($xel->children() as $nName => $nDef) {
            $html .= \yii\helpers\Html::tag('th', $nDef['displayName'], [
               'id' =>  $nDef['columnName'],
                'cwf-format' => \cwf\utils\XmlHelper::getAttr($nDef, 'format', null),
                'cwf-wrapIn' => \cwf\utils\XmlHelper::getAttr($nDef, 'wrapIn', null),
                'cwf-style' => \cwf\utils\XmlHelper::getAttr($nDef, 'style', null),
                'class' => ''. \cwf\utils\XmlHelper::getAttr($nDef, 'class', ''),
                'scope' => 'col'
            ]);
        }
        return strtr($this->fieldTempl, [
            '{cols}' => $html
        ]);
    }

    public function emitArray(\SimpleXMLElement $xel): array {
        $dispFields = [];
        foreach($xel->children() as $nName => $nDef) {
            $dispFields[] = [
                'columnName' => (string)$nDef['columnName'],
                'displayName' => (string)$nDef['displayName'][0],
                'format' => \cwf\utils\XmlHelper::getAttr($nDef, 'format', ''),
                'wrapIn' => \cwf\utils\XmlHelper::getAttr($nDef, 'wrapIn', null),
                'style' => \cwf\utils\XmlHelper::getAttr($nDef, 'style', null),
                'class' => \cwf\utils\XmlHelper::getAttr($nDef, 'style', ''),
            ];
        }
        return $dispFields;
    }

}
