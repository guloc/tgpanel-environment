<?php defined('ROCKET_SCRIPT') OR die(header('Location: /not_found'));

class Users extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        if ( ! RS_STATUS)
            $this->load->view('technical_works');
        $this->security_model->check_visit();
        $this->security_model->check_auth();
        $this->security_model->only_admin();
    }
    
    public function index($data=array())
    {
        if (isset($_POST['user'])) {
            $data['good_message'] = false;
            $data['message'] = lang('something_goes_wrong');
            $user_data = $_POST['user'];
            $this->load->library('form_validation');
            $this->form_validation->set_data($user_data);
            if ($this->form_validation->run()) {
                $result = $this->user_model->create($user_data, $user_data['type']);
                if ($result !== true) {
                    $data['message'] = $result;
                } else {
                    $data['message'] = lang('user_created');
                    $data['good_message'] = true;
                }
            } else {
                $data['message'] = validation_errors();
            }
        } elseif (isset($_POST['update_user'])) {
            $data['good_message'] = false;
            $data['message'] = lang('something_goes_wrong');
            $user_data = $_POST['update_user'];
            if (empty($user_data['id']) or empty($user_data['password']) or empty($user_data['password2'])) {
                $data['message'] = lang('incorrect_input');
            } elseif ($user_data['password'] !== $user_data['password2']) {
                $data['message'] = lang('pass_confirm_error');
            } else {
                $user = $this->user_model->get($user_data['id']);
                if (empty($user)) {
                    $data['message'] = lang('user_not_found');
                } else {
                    $this->db->where('id', $user->id)->update('user', [
                        'pswd' => $this->security_model->hash_password($user_data['password'])
                    ]);
                    $data['good_message'] = true;
                    $data['message'] = lang('pass_changed');
                }
            }
        }
        $data = $this->info_model->get_basic_info($data);
        $data['users'] = $this->user_model->get('all');
        $this->load->view('users', $data);
        $this->security_model->debugger($data);
    }

    public function delete()
    {
        $user_id = $_POST['user_id'] ?? '';
        if (empty($user_id) or $user_id == 1)
            die(json_encode([
                'result' => 'error',
                'error' => lang('incorrect_input')
            ]));
        $result = $this->user_model->delete($user_id);
        if ($result === false)
            die(json_encode([
                'result' => 'error',
                'error' => lang('something_goes_wrong')
            ]));
        $posts = $this->db->where('user_id', $user_id)->get('post')->result();
        foreach ($posts as $post)
            $this->post_model->delete($post->id);
        $channels = $this->db->where('user_id', $user_id)->get('channel')->result();
        foreach ($channels as $channel)
            $this->channel_model->delete($channel->id);
        $groups = $this->db->where('user_id', $user_id)->get('group')->result();
        foreach ($groups as $group)
            $this->group_model->delete($group->id);
        echo json_encode([ 'result' => 'ok' ]);
    }

}
