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
class CwfVendorAsset extends \yii\web\AssetBundle {
    public $sourcePath = '@vendor/cwf/vendor-assets';
    
    public $css = [
        'bootstrap4/css/bootstrap.css',
        // DataTable and Collection
        'dataTable/css/jquery.dataTables.min.css',
        'dataTable/css/scroller.dataTables.min.css',
        'dataTable/css/fixedColumns.dataTables.min.css',
    ];
    public $js = [
        'bootstrap4/js/bootstrap.js',
        // Moment for Timezone
        'moment/moment.min.js',
        'moment/moment-timezone-with-data.min.js',
        // DataTable and Collection
        'dataTable/js/jquery.dataTables.min.js',
        'dataTable/js/dataTables.scroller.min.js',
        'dataTable/js/dataTables.fixedColumns.min.js'
    ];
}
