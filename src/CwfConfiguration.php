<?php

/**
 * @link http://www.vishwayon.com/
 * @copyright Copyright (c) 2008 Vishwayon Software Pvt Ltd
 * @license http://www.vishwayon.com/license/
 */

namespace cwf;

/**
 * CwfConfiguration: Used for loading all configuration
 * required as part of yii->bootstrap. 
 * Do not call any long running process from here. It would directly
 * impact performance, as the class is called on every yii->app->init 
 * @author girish
 */
class CwfConfiguration implements \yii\base\BootstrapInterface {

    /**
     * Loads default configuration
     * @param \yii\web\Application $app
     */
    public function bootstrap($app) {
        // Register All Singletons
        $singletons = [
            'ConnectionBuilder' => [
                ['class' => 'cwf\data\ConnectionBuilder'],
                [\yii::$app->params['cwfconfig']['dbInfo']]
            ]
        ];
        \yii::$container->setSingletons($singletons);
        
        // Register All other Classes
        $comps = [
            'UserAuth' => [
                'class' => 'cwf\security\UserAuth'
            ],
        ];
        \yii::$container->setDefinitions($comps);
        
        // The following event handler ensures that unauthenticated users cannot access secure pages.
        \yii\base\Event::on(\yii\web\Application::className(), \yii\web\Application::EVENT_BEFORE_REQUEST, function($event) {
            //Yii::trace('App init event fired');
            $req = $event->sender->getRequest();
            $ua = new \cwf\security\UserAuth();
            $ua->preProcessAuth($req);
        });
    }
}
