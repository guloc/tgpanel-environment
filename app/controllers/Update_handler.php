<?php defined('ROCKET_SCRIPT') OR die(header('Location: /not_found'));

class Update_handler extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public $log = false;
    
    public function index()
    {
        if (@$_GET['key'] !== RS_KEY)
            die(header("Location: /not_found"));
        
        $t = time();

        $update = unjson(@file_get_contents("php://input"));

        if (@$update['_'] !== 'danog\\MadelineProto\\EventHandler\\Message\\ChannelMessage')
            return;

        // if ($this->log) {
        //     data_log($update);
        // }
        
        $channel = $this->db->where('uid', $update['chatId'])
                            ->where('type', 'source')
                            ->where('active', true)
                            ->get('channel')
                            ->row();
        if (empty($channel))
            return;

        $update['_'] = 'message';
        if (isset($update['entities'])) {
            foreach ($update['entities'] as &$entity)
                $entity['_'] = str_replace(
                    'danog\\MadelineProto\\EventHandler\\Message\\Entities\\',
                    'messageEntity',
                    $entity['_']
                );
        }
        if (isset($update['media'])) {
            if (empty($update['media']['fileExt'])) {
                unset($update['media']);
            } else {
                $media_type = media_type($update['media']['fileExt']);
                $update['media'] = [
                    'id' => $update['media']['botApiFileId'],
                    'ext' => $update['media']['fileExt'],
                    'name' => preg_replace('/(.+)_\d+(\.\w+)$/', '$1$2', $update['media']['fileName']),
                ];
            }
            if (empty($update['media']) and empty($update['message']))
                return;
        }

        if (empty($update['groupedId'])) {
            $this->madeline_model->handle_messages($channel, [ $update ]);
        } else {
            $update['grouped_id'] = $update['groupedId'];
            $this->db->insert('message_group', [
                'grouped_id' => $update['groupedId'],
                'channel_id' => $channel->id,
                'message' => json_encode($update),
                'created_at' => now()
            ]);
        }
    }

}

