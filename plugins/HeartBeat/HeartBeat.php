<?php

class HeartBeat extends PluginBase {
    
    static protected $name = 'HeartBeat';
    static protected $description = 'Implement a heartbeat to ensure the session is kept alive.';
    
    protected $storage = 'DbStorage';
    protected $settings = array(
        'interval'=>array(
            'type'=>'int',
            'label' => 'Interval of the heartbeat (min: 5000 ms)',
            'default' => 10000
        )
    );

    public function __construct(PluginManager $manager, $id) {
        parent::__construct($manager, $id);

        $this->subscribe('beforeSurveyPage');
        $this->subscribe('newDirectRequest');
    }

    public function beforeSurveyPage()
    {
        // Get the js directory
	$jsPath=Yii::app()->assetManager->publish(dirname(__FILE__) . '/js/');
	// Register the js file
        Yii::app()->clientScript->registerScriptFile($jsPath.'/heartbeat.js');
	// Create the endpoint url
        $aOption['endpoint']=$this->api->createUrl('plugins/direct', array('plugin' => get_class($this),'function' => 'beat'));
        // Get the settings
        $interval = (int)$this->get('interval');
        // Ensure the interval is not < 5000.
        $aOption['interval']= $interval < 5000 ? 5000 : $interval;
	// Create the javascript code to inject in the page
        $heartBeatScript="heartBeat.beat(".ls_json_encode($aOption).");";
	// Inject js into the page
        Yii::app()->clientScript->registerScript("heartbeat", $heartBeatScript, CClientScript::POS_END);
    }

    public function newDirectRequest()
    {
        $oEvent = $this->event;
        $sAction=$oEvent->get('function');
        if ($oEvent->get('target') == "HeartBeat")
        {
            if($sAction == 'beat')
                $this->actionBeat();
            else
                throw new CHttpException(404,'Unknow action');
        }
    }

    private function actionBeat()
    {
	// Access the session to make sure it stays alive
        $iSurveyId=Yii::app()->session['LEMsid'];
    }
}