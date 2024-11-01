<?php

class Openrouter_model extends CI_Model
{
    private $log = RS_MODE == 'development';
    private $api_key;
    private $ai_model;
    private $api_url = 'https://openrouter.ai/api/v1/chat/completions';
    private $proxy_ip = '';
    private $proxy_port = '';
    private $proxy_login = '';
    private $proxy_pass = '';

    function __construct() {
        parent::__construct();
        $this->api_key = $this->config_model->get('openrouter_api_key');
        $this->ai_model = $this->config_model->get('openrouter_ai_model');
        $this->proxy_ip = $this->config_model->get('proxy_ip');
        $this->proxy_port = $this->config_model->get('proxy_port');
        $this->proxy_login = $this->config_model->get('proxy_login');
        $this->proxy_pass = $this->config_model->get('proxy_pass');
    }

    public function get_text(
        $prompt,
        $context = "",
        $max_tokens=3000,
        $temperature = 0.7,
        $top_p = 1,
        $frequency_penalty = 0,
        $presence_penalty = 0,
        $userid = ""
    )
    {
        $messages = [];
        if ($context != "") {
            foreach ($context as $item) {
                $messages []= [
                    'role' => $item['role'],
                    'content' => $item['content'],
                ];
            }
        }
        $messages []= [
            "role" => "user",
            "content" => $prompt
        ];

        $data = [
            "messages"    => $messages,
            "model"       => $this->ai_model,
            "max_tokens"  => (int) $max_tokens,
            "temperature" => (double) $temperature
        ];

        if ($this->log)
            data_log('REQUEST', $data);

        if ($userid != "") $data["user"] = $userid;
        $post_json = json_encode($data);
        $url = $this->api_url;

        // Trying up to 3 times
        for ($i = 0; $i < 3; $i++)
        {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_json);

            $headers = [];
            $headers[] = "Content-Type: application/json";
            $headers[] = "Authorization: Bearer {$this->api_key}";
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            if ( ! empty($this->proxy_ip) and ! empty($this->proxy_port)) {
                $ipv6 = filter_var($proxy_ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
                $proxy = $ipv6
                       ? "[{$this->proxy_ip}]"
                       : $this->proxy_ip;
                $proxy .= ":{$this->proxy_port}";
                if ( ! empty($this->proxy_login) and ! empty($this->proxy_pass)) {
                    $proxy = "{$this->proxy_login}:{$this->proxy_pass}@" . $proxy;
                }
                curl_setopt($ch, CURLOPT_PROXY, $proxy);
            }

            $result = curl_exec($ch);
            if ($result === false)
                logg('Error: ' . curl_error($ch));
            curl_close($ch);

            if ($this->log)
                data_log('RESPONSE', unjson($result) ? unjson($result) : $result);

            $ai_response = $gettext = json_decode($result, true);

            $error = false;
            if (isset($gettext['error'])) {
                if (isset($gettext['error']['message']))
                    logg('Error: ' . $gettext['error']['message']);
                $error = true;
            } else {
                if (empty($gettext['choices'])
                  or empty($gettext['choices'][0])
                  or empty($gettext['choices'][0]['message'])
                  or empty($gettext['choices'][0]['message']['content'])
                )
                    $error = true;
                else
                    $aioutput = $gettext['choices'][0]['message']['content'];
            }

            if ( ! $error)
                break;
        }

        if ($error) {
            if ( ! $this->log) {
                data_log('REQUEST', $data);
                data_log('RESPONSE', unjson($result) ? unjson($result) : $result);
            }
            return $this->llm_model->error_text;
        }
    
        $this->db->reconnect();
        
        return $aioutput;   
    }

}