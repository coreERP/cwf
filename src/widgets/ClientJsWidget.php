<?php

/**
 * @link http://www.vishwayon.com/
 * @copyright Copyright (c) 2008 Vishwayon Software Pvt Ltd
 * @license http://www.vishwayon.com/license/
 */

namespace cwf\widgets;

/**
 * ClientJsWidget: This implements functionality of clientJsCode/Refs
 *
 * @author girish
 */
class ClientJsWidget implements \cwf\base\ICwfHtmlType {
    use \cwf\base\CwfObjectType;
    
    public function __construct() {
        $this->registerType('clientJsCode');
        $this->registerType('clientJsCodeRefs');
    }

    public function emitHtml(\SimpleXMLElement $xel): string {
        $html = '';
        if($xel->getName() == 'clientJsCode') {
            $jsfile = \yii::getAlias((string)$xel);
            $html .= \yii\helpers\Html::tag('script', '', ['src' => $jsfile]);
        } elseif ($xel->getName() == 'clientJsCodeRefs') {
            foreach($xel->children() as $nName => $nDef) {
                $jsfile = \yii::getAlias((string)$nDef);
                $html .= \yii\helpers\Html::tag('script', '', ['src' => $jsfile]);
            }
        }
        return $html;
    }
}
