<?php

class Openai_model extends CI_Model
{
    private $log = RS_MODE == 'development';
    public $voices = [
        'alloy', 'echo', 'fable', 'onyx', 'nova', 'shimmer'
    ];
    public $languages = [
        'english'    => 'en', 'chinese'    => 'cn', 'danish'     => 'da',
        'dutch'      => 'nl', 'french'     => 'fr', 'german'     => 'de',
        'hindi'      => 'hi', 'indonesian' => 'id', 'italian'    => 'it',
        'japanese'   => 'ja', 'korean'     => 'ko', 'polish'     => 'pl',
        'portuguese' => 'pt', 'russian'    => 'ru', 'spanish'    => 'es',
        'turkish'    => 'tr', 'ukrainian'  => 'uk'
    ];
    private $tts_model = 'tts-1'; // 'tts-1-hd';
    private $stt_model = 'whisper-1';
    private $api_key;
    private $ai_model = 'gpt-3.5-turbo';
    private $img_size = '1024x1024';
    private $api_url = 'https://api.openai.com/v1/chat/completions';
    private $image_url = 'https://api.openai.com/v1/images/generations';
    private $tts_url = 'https://api.openai.com/v1/audio/speech';
    private $stt_url = 'https://api.openai.com/v1/audio/transcriptions';
    private $proxy_ip = '';
    private $proxy_port = '';
    private $proxy_login = '';
    private $proxy_pass = '';

    function __construct() {
        parent::__construct();
        $this->api_key = $this->config_model->get('openai_api_key');
        $this->ai_model = $this->config_model->get('openai_ai_model');
        $this->img_size = $this->config_model->get('openai_img_size');
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

        // Trying up to 3 times
        for ($i = 0; $i < 3; $i++)
        {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->api_url);
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

        return $aioutput;   
    }

    public function get_image($prompt)
    {
        // $data = [
        //     "prompt" => $prompt,
        //     "size" => $this->img_size,
        // ];
        $data = [
            "model"   => "dall-e-3",
            "prompt"  => $prompt,
            "size"    => $this->img_size,
            "quality" => "standard",
            "n" => 1,
        ];
        if ($this->log)
            data_log('REQUEST', $data);
        $post_json = json_encode($data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->image_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_json);

        $headers = [];
        $headers[] = "Content-Type: application/json";
        $headers[] = "Authorization: Bearer {$this->api_key}";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if ( ! empty($this->proxy_ip) and  ! empty($this->proxy_port)) {
            $proxy = "{$this->proxy_ip}:{$this->proxy_port}";
            if ( ! empty($this->proxy_login) and  ! empty($this->proxy_pass)) {
                $proxy = "{$this->proxy_login}:{$this->proxy_pass}@" . $proxy;
            }
            curl_setopt($ch, CURLOPT_PROXY, $proxy);
        }

        $result = curl_exec($ch);
        if ($this->log)
            data_log('RESPONSE', unjson($result) ? unjson($result) : $result);

        $gettext = @json_decode($result, true);
        $aioutput = $gettext["data"][0]["url"] ?? '';
        if (empty($aioutput) and ! $this->log) {
            data_log('REQUEST', $data);
            data_log('RESPONSE', unjson($result) ? unjson($result) : $result);
        }

        curl_close($ch);
        return $aioutput;
    }
    
    public function text_to_speech($text, $voice=false, $speed=1)
    {
        $user = $this->user_model->get();

        if ( ! in_array($voice, $this->voices))
            $voice = $this->voices[0];
        if ( ! in_array($speed, range(0.25, 4, 0.25)))
            $speed = 1;
        $data = [
            'model' => $this->tts_model,
            'voice' => $voice,
            'input' => $text,
            'speed' => $speed,
        ];
        if ($this->log)
            data_log('REQUEST', $data);
        $post_json = json_encode($data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->tts_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_json);

        $headers = [];
        $headers[] = "Content-Type: application/json";
        $headers[] = "Authorization: Bearer {$this->api_key}";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if ( ! empty($this->proxy_ip) and  ! empty($this->proxy_port)) {
            $proxy = "{$this->proxy_ip}:{$this->proxy_port}";
            if ( ! empty($this->proxy_login) and  ! empty($this->proxy_pass)) {
                $proxy = "{$this->proxy_login}:{$this->proxy_pass}@" . $proxy;
            }
            curl_setopt($ch, CURLOPT_PROXY, $proxy);
        }

        $result = curl_exec($ch);
        if ($this->log)
            data_log('RESPONSE', unjson($result) ? unjson($result) : $result);

        if (empty($result)) {
            $file_name = false;
        } else {
            $file_name = preg_replace('/[^\d]/', '', now())
                       . '_' . mt_rand(1000, 9999);
            $audio_path = 'assets/audio/' . $user->id . '/';
            if ( ! file_exists($audio_path))
                mkdir($audio_path, '0755');
            if (is_writable($audio_path))
                @file_put_contents($audio_path . $file_name . '.mp3', $result);
        }
        curl_close($ch);
        return $file_name;
    }

    // supported: mp3, mp4, mpeg, mpga, m4a, wav, and webm
    // 25 MB
    public function speech_to_text($file_path, $prompt='', $language=false, $temperature=0)
    {
        $file = new CURLFile(realpath($file_path));
        $data = [
            'model' => $this->stt_model,
            'file'  => $file,
            'response_format' => 'verbose_json',
            'temperature' => in_array($temperature, range(0, 1, 0.1))
                           ? $temperature
                           : 0
        ];
        if ( ! empty($prompt))
            $data['prompt'] = $prompt;
        if (in_array($language, array_keys($this->prompt_model->languages)))
            $data['language'] = $language;
        if ($this->log)
            data_log('REQUEST', $data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->stt_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $headers = [];
        $headers[] = "Content-Type: multipart/form-data";
        $headers[] = "Authorization: Bearer {$this->api_key}";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if ( ! empty($this->proxy_ip) and  ! empty($this->proxy_port)) {
            $proxy = "{$this->proxy_ip}:{$this->proxy_port}";
            if ( ! empty($this->proxy_login) and  ! empty($this->proxy_pass)) {
                $proxy = "{$this->proxy_login}:{$this->proxy_pass}@" . $proxy;
            }
            curl_setopt($ch, CURLOPT_PROXY, $proxy);
        }

        $response = curl_exec($ch);
        if ($this->log)
            data_log('RESPONSE', unjson($response) ? unjson($response) : $response);

        $result = json_decode($response, true);
        if (empty(@$result['text'])) {
            if ( ! empty(@$result['error']['message']))
                logg('Error: ' . $result['error']['message']);
            else
                data_log('Error - Incorrect response: ', $response);
            $text = $this->llm_model->error_text;
        } else {
            $text = $result['text'];
        }

        curl_close($ch);
        return $text;
    }
}