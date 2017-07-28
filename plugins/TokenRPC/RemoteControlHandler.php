<?php

class RemoteControlHandler extends remotecontrol_handle 
{
    /**
    * RPC Routine to return settings of a token/participant of a survey .
    *
    * @access public
    * @param string $sSessionKey Auth credentials
    * @param int $iSurveyID Id of the Survey to get token properties
    * @param int $sToken Token of the participant to check
    * @param array $aTokenProperties The properties to get
    * @return array The requested values
    */
    public function get_participant_properties_by_token($sSessionKey, $iSurveyID, $sToken, $aTokenProperties)
    {
        if ($this->_checkSessionKey($sSessionKey))
        {
            $surveyidExists = Survey::model()->findByPk($iSurveyID);
            if (!isset($surveyidExists))
                return array('status' => 'Error: Invalid survey ID');

            if (Permission::model()->hasSurveyPermission($iSurveyID, 'tokens', 'read'))
            {
                if(!tableExists("{{tokens_$iSurveyID}}"))
                    return array('status' => 'Error: No token table');
                       
                $token = Token::model($iSurveyID)->findByAttributes(array('token' => $sToken));
                if (!isset($token))
                    return array('status' => 'Error: Invalid tokenid');

                $result = array_intersect_key($token->attributes, array_flip($aTokenProperties));
                if (empty($result))
                {
                    return array('status' => 'No valid Data');
                }
                else
                {
                    return $result;
                }
            }
            else
                return array('status' => 'No permission');
        }
        else
            return array('status' => 'Invalid Session Key');
    }

	/**
     * RPC Routine to delete responses of particular tokens in a survey.
     * Returns array
     *
     * @access public
     * @param string $sSessionKey Auth credentials
     * @param int $iSurveyID Id of the survey that participants belong to
     * @param string $aTokenIDs array of unique token ids of specific participants
     * @return array Result of the change action
     */
    public function delete_responses($sSessionKey, $iSurveyID, $aTokenIDs){
    	      
        if ($this->_checkSessionKey($sSessionKey))
    	{
    		$oSurvey = Survey::model()->findByPk($iSurveyID);
    		if ($oSurvey) {
                if(Permission::model()->hasSurveyPermission($iSurveyID,'responses','delete'))
                {
                    foreach($aTokenIDs as $iTokenID)
                    { 
                        $oToken = Token::model($iSurveyID)->findByPk($iTokenID);
                        if ($oToken) {
                            $oResult = Response::model($iSurveyID)->findByAttributes(array('token' => $oToken->token));
                            if ($oResult) {
                                $iResponseID = (int) $oResult['id'];
                                Response::model($iSurveyID)->deleteByPk($iResponseID); 
                            } else {
                                return array('status' => 'No responses for this token');
                            }
                        } else {
                            return array('status' => 'Token could not be found');
                        }
                    }
                } else {
                    return array('status' => 'No permission');
                }
            } else {
                return array('status' => 'Error: Invalid survey ID');
            }    
    	} else {
            return array('status' => 'Invalid Session Key');
        }
    }
}