<?php

/**
 * Raygun Pulse Plugin for LimeSurvey
 * Implement RayGun Pulse to log your client side activities.
 *
 * @author Frederik Prijck <http://www.frederikprijck.net/>
 * @copyright 2016 Frederik Prijck <http://www.frederikprijck.net/>
 * @license MIT
 * @license https://opensource.org/licenses/MIT MIT License (MIT)
 * @version 1.0.0
 *
 */

class RaygunPulse extends \ls\pluginmanager\PluginBase {
    
    static protected $name = 'RaygunPulse';
    static protected $description = 'Implement RayGun Pulse to log your client side activities.';
    
    protected $storage = 'DbStorage';
    protected $settings = array(
        'apiKey'=>array(
            'type'=>'string',
            'label' => 'The api key to use for raygun pulse'
        ),
        'anonymous'=>array(
            'type'=>'boolean',
            'label' => 'Exclude user information when sending information to raygun pulse',
            'default' => '1'
        )
    );

    public function init() {
        $this->subscribe('beforeSurveyPage');
    }
    
    /*
     * Hook to handle the 'beforeSurveyPage' event
     * See: https://manual.limesurvey.org/BeforeSurveyPage
    */
    public function beforeSurveyPage()
    {
        $apiKey = $this->get('apiKey');
        $isAnonymous = $this->get('anonymous');
        $isApiKeyProvided = trim($apiKey) !== '';
        
        if($isApiKeyProvided){
            $this->injectRaygunJsFiles($apiKey, $isAnonymous);
            // Call RayGun for none-question specific views
            $this->callRayGun($apiKey, $isAnonymous);
        }       
    }
    
    /*
     * Method used to inject the required files in the HTML
    */
    private function injectRaygunJsFiles($apiKey, $isAnonymous){
      // Get the js directory
	  $jsPath=Yii::app()->assetManager->publish(dirname(__FILE__) . '/js/');
	  // Register the js file
      Yii::app()->clientScript->registerScriptFile($jsPath.'/raygunPulse.register.js');
      Yii::app()->clientScript->registerScriptFile($jsPath.'/raygunPulse.js');
    }
    
    /*
     * Method used to call Raygun using the specified apiKey and indicating whether or not the 
     *  data should be anonymous or not. 
     * When isAnonymous is false, a user object will be created and sent to Raygun Pulse.
    */
    private function callRayGun($apiKey, $isAnonymous){
        $options['apiKey'] = $apiKey;
        $options['user'] = $this->getUserInformation($isAnonymous);
        $options['group'] = $this->getGroupInformation();
        $options['surveyId'] =  $this->getSurveyId();
        // Create the javascript code to inject in the page
        $raygunPulseScript = "raygunPulse.init(".ls_json_encode($options).");";
        // Inject js into the page
        Yii::app()->clientScript->registerScript("raygunPulse", $raygunPulseScript, CClientScript::POS_END);
    }
    
    /*
     * Method used to generate the user information for RayGun Pulse
    */
    private function getUserInformation($isAnonymous){
        $surveyId = $this->getSurveyId();
        $surveySession = $this->getSurveySession($surveyId);
        
        // Find the token by session
        $sessionToken = (isset($surveySession['token'])) ? $surveySession['token'] : null;
        // Find the token by request param, if not found use $sessionToken as default
        $token = $this->api->getRequest()->getParam('token', $sessionToken);
        
        // Check if the survey uses tokens
        $canUseToken = trim($token) !== '';
        
        $options['isAnonymous'] = $isAnonymous;
        
        if($canUseToken == false){
            // When tokens are not used: use [surveyId]-[sessionId] as identifier
            $sessionId = Yii::app()->session->getSessionID();
            $options['identifier'] = $surveyId . '-' . $sessionId;
        }else{
            // When tokens are used: use [surveyId]-[token] as identifier
            $options['identifier'] =  $surveyId . '-' . $token;
            if($isAnonymous == false){
                // As the getUser method can return 'NULL', ensure that it's cast to an empty array.
                $options = array_merge($options, (array) $this->getUser($surveyId, $token));
            }
        }
        
        return $options;
    }
    
    /*
     * Method used to get the information of the current group
     * Returns null if currentStep is not set.
    */
    private function getGroupInformation(){
        $surveyId = $this->getSurveyId();
        $surveySession = $this->getSurveySession($this->getSurveyId());
        $currentStep = (isset($surveySession['step'])) ? $surveySession['step'] : null;
        $previousStep = (isset($surveySession['prevstep'])) ? $surveySession['prevstep'] : null;
        $groups = (isset($surveySession['grouplist'])) ? $surveySession['grouplist'] : null;

        // When $currentStep > -1 we are inside the survey
        // $currentStep == -1 means we are on the welcome page
        if($currentStep > -1){
            $currentGroup = $groups[$currentStep];
            
            return Array(
                'id' => $currentGroup['gid'],
                'name' => $currentGroup['group_name'],
                'step' => $currentStep
            );
        }
        
        return null;
    }
    
    /*
     * Method used to get the user information from the token table based on the surveyId and token
    */
    private function getUser($surveyId, $token){
        if($existingToken = TokenDynamic::model($surveyId)->find("token =:token",array(':token' => $token))){
            $user['firstName'] = $existingToken->firstname;
            $user['lastName'] = $existingToken->lastname;
            $user['email'] = $existingToken->email;
            
            return $user;
        }
    }
    
    
    private function getSurveyId(){
        return Yii::app()->session['LEMsid'];
    }
    
    /*
     * Method used to the the session object for the specified surveyId
     * surveyId is optional, LEMsid will be loaded from the session when the surveyId is not provided
    */
    private function getSurveySession($surveyId){
        $surveyId = (isset($surveyId)) ? $surveyId : $this->getSurveyId();
        return Yii::app()->session["survey_{$surveyId}"];
    }
}