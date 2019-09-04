<?php

/**
 * @link http://www.vishwayon.com/
 * @copyright Copyright (c) 2008 Vishwayon Software Pvt Ltd
 * @license http://www.vishwayon.com/license/
 */

namespace cwf\widgets;

/**
 * CollectionViewWidget: This implements functionality of CwfCollectionView
 *
 * @author girish
 */
class CollectionViewWidget extends \cwf\widgets\WidgetBase implements \cwf\base\ICwfHtmlType {
    use \cwf\base\CwfObjectType;
    /**
     * Template for rendering the partial view
     * @var string 
     */
    protected $viewTempl = '<div id="{id}-cv" class="row">
                                {view}
                            </div>';
    
    /**
     * Template for rendering standard filters
     * @var string
     */
    protected $filterTempl = '<div id="{id}-cv-filter" class="row">\n{filter}\n</div>';
    
    /**
     * Template for rendering custom filters if any
     * @var string 
     */
    protected $customFilterTempl = '<div id="{id}-cv-cust-filter" class="row">\n{customFilter}\n</div>';
    
    protected $tableTempl = '<div id="cv-data">{data}</div>';
    
    public function __construct() {
        $this->registerType('collectionView');
    }


    public function emitHtml(\SimpleXMLElement $xel): string {
        $html = '';
        $rstr = [];
        $header = '';
        if(isset($xel->header)) {
            $header .= \yii\helpers\Html::tag('h1', (string)$xel->header);
            $header .= \yii\helpers\Html::a('view data', 'collection-view-data', [
                'id' => 'data-lnk'
            ]);
        }
        $dbType = ''; $cmm;
        if(isset($xel->collectionSection)) {
            if(isset($xel->collectionSection->connectionType)) {
                $dbType = $xel->collectionSection->connectionType->children()[0]->getName();
            }
            if(isset($xel->collectionSection->sql)) {
                $cmm = $this->runSqlCommandType($xel->collectionSection->sql);
            }
            $dt = \cwf\data\DataConnect::getData($cmm, $dbType);
        }
        
        $html = strtr($this->viewTempl, [
            '{id}' => $xel->attributes()['id'],
            '{view}' => $header
        ]);
        $html .= strtr($this->tableTempl, [
            '{data}' => '' // (new DisplayFieldWidget())->emitHtml($xel->collectionSection->displayFields)
        ]);
        return $html;
    }
    
    /**
     * This method puts together data required for display to the user and
     * returns a json encoded string
     * 
     * @param \SimpleXMLElement $xel
     * @param array $filters
     */
    public function getCollData(\SimpleXMLElement $xel, array $filters): string {
        $dfw = new DisplayFieldWidget();
        $dfs = $dfw->emitArray($xel->collectionSection->displayFields);
        $dbType = \cwf\data\ConnectionBuilder::DB_DEFAULT;
        if(isset($xel->collectionSection->connectionType)) {
            $dbType = $xel->collectionSection->connectionType->children()[0]->getName();
        }
        $displayFields = [];
        foreach($dfs as $df) {
            $displayFields[] = new \cwf\utils\GenericElement($df);
        }
        $gelCV = new \cwf\utils\GenericElement([
            'bo_type' => \cwf\base\CwfType::BO_MASTER,
            'cn_dbType' => $dbType,
            'al_allowed' => \cwf\base\CwfType::AL_READONLY,
            'sqlCommand' => $this->runSqlCommandType($xel->collectionSection->sql),
            'displayFields' => $displayFields
        ]);
        $result = \cwf\helpers\CollectionViewHelper::getCollectionData($gelCV, []);
        return $result;
    }

}
