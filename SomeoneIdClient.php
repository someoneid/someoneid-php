<?php
    
    class SomeoneException extends Exception {
        
        var $statusCode;
        
        function __construct($inStatusCode, $inMessage) {
            parent::__construct($inMessage);
            $this->statusCode = $inStatusCode;
        }
        
        function getStatusCode() {
            return $this->statusCode;
        }
        
        function setStatusCode($inStatusCode) {
            $this->statusCode = $inStatusCode;
        }
    }

    class SomeoneClient {
        
        var $logonEndpoint;
        var $tokenEndpoint;
        var $userEndpoint;

        var $clientId;
        var $clientSecret;
        var $callbackUri;
        var $scope = "email";
        
        
        function __construct($inClientId, $inClientSecret, $inCallbackUri) {
            session_start();

            $this->logonEndpoint = "https://www.someone.id/account/logon?";
            $this->userEndpoint = "https://www.someone.id/oauth/Me?";
            $this->tokenEndpoint = "https://www.someone.id/oauth/AccessToken?";
            
            $this->clientId = $inClientId;
            $this->clientSecret = $inClientSecret;
            $this->callbackUri = $inCallbackUri;
        }
        
        
        function logOn() {

            $this->clear_login_state();

            $params = array(
                'response_type' => 'code',
                'client_id' => $this->clientId,
                'scope' => $this->scope,
                'state' => uniqid('', true),
                'redirect_uri' => $this->callbackUri,
            );
            $_SESSION['SOMEONE_CLIENT']['STATE'] = $params['state'];
            $url = $this->logonEndpoint . http_build_query($params);
            header("Location: $url");
            exit;

        }

        function get_oauth_token($code, $state) {

            if (isset($_SESSION['SOMEONE_CLIENT']['STATE']) && $_SESSION['SOMEONE_CLIENT']['STATE'] == $state) {

                $params = array(
                    'grant_type' => 'authorization_code',
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'code' => $code,
                    'redirect_uri' => $this->callbackUri,
                );
                $url_params = http_build_query($params);

                $url = $this->tokenEndpoint . $url_params;
                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, $url);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                $result = curl_exec($curl);
                $result_obj = json_decode($result, true);
                        
                $access_token = $result_obj['access_token']; 
                $expires_in = $result_obj['expires_in'];
                $expires_at = time() + $expires_in;

                if (!$access_token || !$expires_in) {
                    throw new SomeoneException("", "Authorization error");
                }
                else {
                    $_SESSION['SOMEONE_CLIENT']['ACCESS_TOKEN'] = $access_token;
                    $_SESSION['SOMEONE_CLIENT']['EXPIRES_IN'] = $expires_in;
                    $_SESSION['SOMEONE_CLIENT']['EXPIRES_AT'] = $expires_at;
                    return true;
                }


            }

        }

        // clears the login state:
        function clear_login_state() {
            unset($_SESSION['SOMEONE_CLIENT']['STATE']);
            unset($_SESSION["SOMEONE_CLIENT"]["USER_ID"]);
            unset($_SESSION["SOMEONE_CLIENT"]["USER_EMAIL"]);
            unset($_SESSION["SOMEONE_CLIENT"]["ACCESS_TOKEN"]);
            unset($_SESSION["SOMEONE_CLIENT"]["EXPIRES_IN"]);
            unset($_SESSION["SOMEONE_CLIENT"]["EXPIRES_AT"]);
        }

        function get_oauth_identity($token) {

            $params = array(
                'access_token' => $token
            );
            $url_params = http_build_query($params);
            
            $url = $this->userEndpoint . $url_params; // TODO: we probably want to send this using a curl_setopt...
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            $result = curl_exec($curl);
            $result_obj = json_decode($result, true);

            // parse and return the user's oauth identity:
            $oauth_identity = array();
            $oauth_identity['id'] = $result_obj['userId'];
            $oauth_identity['email'] = $result_obj['email'];
            $oauth_identity['nickname'] = $result_obj['nickname'];
            if (!$oauth_identity['id']) {
                throw new SomeoneException("", "User identity not found");
            }
            return $oauth_identity;
        }
        
    }
    
?>
