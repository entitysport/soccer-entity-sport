<?php

class Entity 
{
    static $api_url_for_get ='https://rest.entitysport.com/soccer/' ;
    function __construct(){}
    
    private function CallAPI($method, $url, $data = false){
        $curl = curl_init();
        if (!$curl) {
            die("Couldn't initialize a cURL handle"); 
        }
        switch ($method)
        {
            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);

                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            case "PUT":
                curl_setopt($curl, CURLOPT_PUT, 1);
                break;
            default:
                if ($data)
                    $url = sprintf("%s?%s", $url, self::query_args($data));
        }
        // Set the file URL to fetch through cURL
        curl_setopt($curl, CURLOPT_URL, $url);

        // Set a different user agent string (Googlebot)
        #curl_setopt($curl, CURLOPT_USERAGENT, 'Googlebot/2.1 (+http://www.google.com/bot.html)'); 

        // Follow redirects, if any
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true); 

        // Fail the cURL request if response code = 400 (like 404 errors) 
        curl_setopt($curl, CURLOPT_FAILONERROR, true); 

        // Return the actual result of the curl result instead of success code
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        // Wait for 10 seconds to connect, set 0 to wait indefinitely
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);

        // Execute the cURL request for a maximum of 50 seconds
        curl_setopt($curl, CURLOPT_TIMEOUT, 50);

        // Do not check the SSL certificates
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false); 
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); 

        // Fetch the URL and save the content in $html variable
        $result = curl_exec($curl); 

        // Check if any error has occurred 
        if (curl_errno($curl)) 
        {
            #echo 'Path error: ' . curl_error($curl); 
            return curl_getinfo($curl);
        } 
        else 
        { 
            // cURL executed successfully
            #print_r(curl_getinfo($curl)); 
        }

        // close cURL resource to free up system resources
        curl_close($curl);

        return $result;
    }

    public function api_request( $path, $args = array()) {
        $str = file_get_contents(dirname(__FILE__).'/api_token.json' );
        $settings = json_decode($str,true);
        
        $api_token = $settings['api_token'];
        $token_expires = $settings['token_expires'];
        
        $return_error = new stdClass;
        $return_error->status = 'error';

        $api_url = self::$api_url_for_get;

        // if token expired, re check.
        if( empty($api_token) || empty($token_expires) ||$token_expires < time()){

            $api_access_key = $settings['api_access_key'];
            $api_secret_key = $settings['api_secret_key'];

            // either access token has not been generated or expired
            if( empty($api_access_key) || empty($api_secret_key) ){
                $return_error->response = 'Api access / secret keys missing.';
                return $return_error;
            }

            $ret = self::api_token($api_access_key,$api_secret_key) ;
            if(!empty($ret->token)){
                $settings['api_token'] = $api_token = $ret->token ;
                $settings['token_expires'] = $ret->expires ;
                $set_str = json_encode($settings);
                file_put_contents(dirname(__FILE__).'/api_token.json' ,$set_str);
            }else{
                return $ret ;
            }
            
        }

        $api_url = $api_url . ltrim( $path, '/' );

        // this is a token generated for wiki, for lifetime
        $args['token'] = $api_token;

        $response = self::CallAPI( 'GET',$api_url, $args );

        if(!empty($response['http_code'])){
            if($response['http_code']==401){
                $return_error->response = 'occurs for unauthorized request';
            }elseif($response['http_code']==400){
                $return_error->response = 'Client side error. occurs for invalid request';
            }elseif($response['http_code']==501){
                $return_error->response = 'Server side error. Internal server error, unable to process your request';
            }elseif($response['http_code']==304){
                $return_error->response = 'API request valid, but data was not modified since last accessed (compared using Etag)';
            }

            return $return_error;
        }

        if($response){
            $response = json_decode($response);
        }
        return $response;
    }

    private function api_token( $api_access_key, $api_secret_key )
	{
        $return_error = new stdClass;
        $return_error->status = 'error';

		$api_url = self::$api_url_for_get.'auth';

		$args['access_key'] = $api_access_key;
		$args['secret_key'] = $api_secret_key;

        $response = self::CallAPI( 'POST',$api_url, $args );
        if($response){
            $response = json_decode($response);
        }
        if( ! is_object($response) || ! isset($response->status) ){
            $return_error->response = 'No response from API server';
            return $return_error;
        }
		if( isset($response->status) && $response->status == 'error' ){
			$return_error->response = 'Api access prohibited.';
            return $return_error;
		}

		return isset($response->response) ? $response->response : array();
    }
    
    private function query_args($data){
        $str = '';
        $data = (array)$data;
        if(!empty($data) && is_array($data)){
            $i=1;
            $counts = count($data);
            foreach($data as $key=>$a){
                $str .= $key;
                $str .= '=';
                $str .= str_replace(' ','+',$a);
                if($counts != $i){
                    $str .= '&';
                }
                $i++;
            }
        }
        return $str;
    }

    
}

