<?php
class OpenAi
{
    public function __construct()
    {
        
    }
    public function sendRequest($open_ai_key,$openai_params)
    {
         $openai_endpoint = esc_url('https://api.openai.com/v1/completions');
        // Set up the request headers
        $headers = array(
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $open_ai_key,
        );
        // Send the request to OpenAI's API
        $response = wp_remote_post( $openai_endpoint, array(
            'method'      => 'POST',
            'timeout'     => 20,
	        'redirection' => 5,
	        'httpversion' => '1.0',
            'redirection' => 10,
            'httpversion' => '1.0',
            'headers' =>$headers,
            'body' =>json_encode($openai_params)
        ));
        if (is_wp_error($response)) {
            return 'Something went wrong';
        } else {
            $response_body = json_decode(wp_remote_retrieve_body($response));
            return $response_body;
        }
    }
}
