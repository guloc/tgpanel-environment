<?php
class Migrate_model extends CI_Model
{
    public $version = '1.4.2';
    public $version_ts = 1729946856;
    private $migrations = [
        1720846277 => '1.2',
        1724085241 => '1.3',
        1725009023 => '1.3.1',
        1728639385 => '1.4',
        1728645264 => '1.4.1',
        1729946856 => '1.4.2'
    ];

    function __construct()
    {
        parent::__construct();
        $version = $this->config_model->get('version');
        if ($version and strpos($version, '__') !== false) {
            list($this->version, $this->version_ts) = explode('__', $version);
        } else {
            $this->config_model->create('version', "{$this->version}__{$this->version_ts}");
        }
        foreach ($this->migrations as $timestamp => $version)
            if ($this->version_ts < $timestamp)
                $this->migrate($timestamp, $version);
    }

    public function migrate($timestamp, $version)
    {
        if ($timestamp == 1720846277)
        {
            // Add 'files' parsing option 
            $sources = $this->db->where('type', 'source')
                                ->get('channel')
                                ->result();
            foreach ($sources as $channel) {
                $config = unjson($channel->config);
                if ( ! isset($config['data']['files']))
                    $config['data']['files'] = false;
                $this->channel_model->update($channel->id, [
                    'config' => json_encode($config)
                ]);
            }
        }
        else if ($timestamp == 1724085241)
        {
            // Add 'moderation' post status
            $this->db->query("
                ALTER TABLE `post` CHANGE `status`
                `status` ENUM('draft','queued','posted','moderation') CHARACTER
                SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'draft'; 
            ");
            // Add 'platform' to channels
            $this->db->query("
                ALTER TABLE `channel`
                ADD `platform` ENUM('telegram','vk') NOT NULL DEFAULT 'telegram'
                AFTER `type`;
            "); 
            // Add 'access_token' to channels
            $this->db->query("
                ALTER TABLE `channel`
                ADD `access_token` VARCHAR(512) NULL
                AFTER `last_post_id`; 
            ");
            // Add 'moderation' parsing option
            $sources = $this->db->where('type', 'source')
                                ->get('channel')
                                ->result();
            foreach ($sources as $channel) {
                $config = unjson($channel->config);
                if ( ! isset($config['moderation']))
                    $config['moderation'] = false;
                $this->channel_model->update($channel->id, [
                    'config' => json_encode($config)
                ]);
            }
            // Add vk config
            $vk_config = $this->config_model->get('vk_config', true);
            if (empty($vk_config))
                $this->config_model->create('vk_config', json_encode([
                    'app_id' => '',
                    'private_key' => '',
                    'service_key' => '',
                ]));
            // Add config 'update_available'
            $this->config_model->create('update_available');
        }
        else if ($timestamp == 1728639385) {
            // Disable DB debug 
            $this->db->db_debug = FALSE;
            // Add 'attempt' post fields
            $this->db->query("
                ALTER TABLE `post`
                ADD `attempt` INT NOT NULL DEFAULT '0' AFTER `pub_date`; 
            ");
            // Add 'wordpress' channel platform
            $this->db->query("
                ALTER TABLE `channel`
                CHANGE `platform` `platform` ENUM('telegram','vk','wordpress')
                CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'telegram';
            ");
            // Add 'access_name' channel field
            $this->db->query("
                ALTER TABLE `channel`
                ADD `access_name` VARCHAR(256) NULL AFTER `access_token`; 
            ");

            // Add 'start words' parsing option 
            $sources = $this->db->where('type', 'source')
                                ->get('channel')
                                ->result();
            foreach ($sources as $channel) {
                $config = unjson($channel->config);
                if ( ! isset($config['start_words']))
                    $config['start_words'] = [];
                $this->channel_model->update($channel->id, [
                    'config' => json_encode($config)
                ]);
            }
            
            // Enable DB debug 
            $this->db->db_debug = TRUE;
        }
        else if ($timestamp == 1729946856) {
            // Add 'start words' and 'subscript' parsing option 
            $sources = $this->db->where('type', 'source')
                                ->get('channel')
                                ->result();
            foreach ($sources as $channel) {
                $config = unjson($channel->config);
                if ( ! isset($config['start_words']))
                    $config['start_words'] = [];
                if ( ! isset($config['subscript']))
                    $config['subscript'] = '';
                $this->channel_model->update($channel->id, [
                    'config' => json_encode($config)
                ]);
            }
        }
        
        $this->config_model->update('version', "{$version}__{$timestamp}");
        logg("Updated to v{$version} [{$timestamp}]");
    }

    public function check_updates()
    {
        return;
        $response = unjson($this->api_request('check_update', [
            'product_id' => '4E8379D8',
            'current_version' => $this->version
        ]));
        if ( ! empty(@$response['status'])) {
            $update = [
                'update_id' => $response['update_id'],
                'version'   => $response['version'],
                'summary'   => $response['summary'],
                'changelog' => $response['changelog'],
            ];
            $cfg_update = $this->config_model->get('update_available', true);
            if (empty($cfg_update) or empty($cfg_update['version'])
              or $cfg_update['version'] !== $update['version']
            ) {
                $this->config_model->update('update_available', json_encode($update));
            }
        }
    }

    public function api_request($method, $data=[])
    {
        $url = 'https://licenseai.org/api/' . $method;
        $host = empty($_SERVER['HTTP_HOST'])
              ? 'no-domain.com'
              : $_SERVER['HTTP_HOST'];
        $ip = empty($_SERVER['SERVER_ADDR'])
            ? '127.0.0.1'
            : $_SERVER['SERVER_ADDR'];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'LB-API-KEY: 5D07BA276CE5DA8852C7',
            "LB-URL: https://{$host}",
            "LB-IP: {$ip}",
            'LB-LANG: english',
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

}