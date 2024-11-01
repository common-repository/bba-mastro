<?php 
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class BBAM_Api extends WC_Cart {
    protected $token; 
    protected $apiUrl;
    protected $authUrl;
    protected $cURLHandle; 

    private $clientId = "clientapp";
    private $clientSecret = "123456";
    private $authCode = "Basic Y2xpZW50YXBwOjEyMzQ1Ng==";

    public function __construct(){
        $this->authUrl = BBAM_Utils::getConfig('auth_url', null);
        $this->apiUrl  = BBAM_Utils::getConfig('api_url', null);
    }
    
    protected function generateCurlRequest($method, $url, $body, $headers = []){
        $options = [
            CURLOPT_URL => $url, 
            CURLOPT_RETURNTRANSFER  => true, 
            CURLOPT_HEADER => false
        ];

        switch($method) {
            case 'POST': 
                $options[CURLOPT_POST] = true;
                $options[CURLOPT_POSTFIELDS] = $body;
                break;
            case 'GET': 
                $options[CURLOPT_HTTPGET] = true;
                break;
        }

        if(is_array($headers)) {
            $options[CURLOPT_HTTPHEADER] = $headers;
        }

        curl_setopt_array( $this->getCURLHandle(), $options );
    }

    private function getCURLHandle(){
        if(!isset($this->cURLHandle) || is_null($this->cURLHandle)) {
            $this->cURLHandle = curl_init();
        }
        return $this->cURLHandle;
    }

    private function executeCurlSession(){
        return curl_exec( $this->getCURLHandle() );
    }

    private function closeCURLSession(){
        curl_close($this->getCURLHandle());
        $this->cURLHandle = null;
    }

    protected function getAccessToken(){
        $token = BBAM_Token::get_token();

        if(!$token) {
            $tokenObject = $this->authenticate();
            if (!$tokenObject) { return null; }
            
            BBAM_Token::save_token($tokenObject->access_token);
            $created_time = time() + $tokenObject->expires_in;
            BBAM_Token::update_token_created_time(time() + $tokenObject->expires_in);

            return $tokenObject->access_token;
        } 

        $expiration_time = BBAM_Token::get_token_created_time() - (60 * 60);
        $current_time = time();
        
        if ($current_time >= $expiration_time) {
            $tokenObject = $this->authenticate();
            
            if ($tokenObject->access_token) {
                $created_time = time() + $tokenObject->expires_in;
                BBAM_Token::update_token_created_time($created_time);
                BBAM_Token::save_token($tokenObject->access_token);
            } else {
                BBAM_Token::update_token_created_time(0);
                BBAM_Token::save_token(false);
            }
            
            return $tokenObject ? $tokenObject->access_token : null;
        }

        return $token;
    }

    
    public function authenticate(){
        $headers = [];
        $body = [
            'username'  => BBAM_Utils::getConfig('acc_username', null) ? : '',
            'password'  => BBAM_Utils::getConfig('acc_password', null) ? : '',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'grant_type' => 'password',
            'reseller_id' => BBAM_Utils::getConfig('reseller_id', null) ? : ''
        ];

        array_push($headers, 'Authorization: '.$this->authCode);
        array_push($headers, 'Origin: '.BBAM_Utils::setHeaderOriginValue());

        $this->generateCurlRequest('POST', $this->authUrl, $body, $headers);
        $response = $this->executeCurlSession();
        $this->closeCURLSession();
        $result = json_decode($response);

        if(isset($result->access_token)) {
            return $result;
        }

        BBAM_Utils::logger($response, 'AUTHENTICATION ERROR', 'bba-api-request-error');
        return null; // ERROR OCCURRED
    }

    public function sendApiRequest($method, $action, $queries = [], $params = [], $asJson = false){
        $url = $this->apiUrl.BBAM_Utils::trimAction($action);
        if(!empty($queries)) {
            $urlQuery = BBAM_Utils::createUrlQuery($queries);
            $url = $url.'?'.implode('&', $urlQuery);
        }

        $headers = ['Authorization: Bearer '.$this->getAccessToken()];
        array_push($headers, 'Origin: '.BBAM_Utils::setHeaderOriginValue());
		
        $body = '';
        if($asJson){
            array_push( $headers, 'Content-Type: application/json' );
            $body = json_encode($params);
        }

        $this->generateCurlRequest($method, $url, $body, $headers);
        $response = $this->executeCurlSession();
        $this->closeCURLSession();
        $result = json_decode($response);

        if(isset($result->error)) {
            BBAM_Utils::logger($result, 'API REQUEST ERROR', 'bba-api-request-error');
            return null; // ERROR OCCURED
        }

        return $result; 
    }
}