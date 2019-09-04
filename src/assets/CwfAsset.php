<?php

/**
 * @link http://www.vishwayon.com/
 * @copyright Copyright (c) 2008 Vishwayon Software Pvt Ltd
 * @license http://www.vishwayon.com/license/
 */

namespace cwf\assets;

/**
 * Renders The Cwf Assets
 *
 * @author girish
 */
class CwfAsset extends \yii\web\AssetBundle {
    public $sourcePath = '@vendor/cwf/src/assets';
    
    public $css = [
        ];
    public $js = [
        'js/cwfclient.js'
    ];
    
    public $depends = [
        'cwf\assets\CwfVendorAsset'
    ];
}
