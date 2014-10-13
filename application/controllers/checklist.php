<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Checklist extends CI_Controller {

    public function Checklist() {
        parent::__construct();

        (!$this->authentication->check_logged_in()) ? redirect('') : '';

        $this->gen_contents['site_name'] = $this->config->item('site_name');
        $this->gen_contents['dynamic_views'] = array();
        $this->load->library('pagination');
        $this->load->library('form_validation');
        $this->load->helper('form');
        $this->load->helper('url');

        $this->load->model('checklist_model', 'checklist');
        $this->gen_contents['dynamic_views'] = array();
        $this->gen_contents['load_css'] = array();
        $this->gen_contents['site_name'] = $this->config->item('site_name');
        $this->gen_contents['page_title'] = 'Check List';
        $this->gen_contents['title'] = $this->gen_contents['site_name'] . ' : ' . $this->gen_contents['page_title'];
        $this->gen_contents['leftmenu_selected'] = 'checklist';
        $this->gen_contents['load_js'] = array("checklist.js");
        $this->gen_contents['selectedCountryId'] = strip_quotes(strip_tags(trim($this->uri->segment(3))));
        $this->gen_contents['paths'] = array(
            'add' => 'checklist/add/'.$this->gen_contents['selectedCountryId'],
            'delete' => 'checklist/delete/'.$this->gen_contents['selectedCountryId'],
            'deleteWoId' => 'checklist/delete',
            'list' => $this->config->item('base_url').'checklist/index/'.$this->gen_contents['selectedCountryId'],
            'listUrlWoId' => $this->config->item('base_url').'checklist/index',
            'updatAll' =>  'checklist/updateall/'.$this->gen_contents['selectedCountryId']
        );
        $this->entity = 'Check List';
        $this->form_validation->set_error_delimiters('<br/>');
        $this->load->model('country_model', 'country');
    }

    public function index() {
        $this->gen_contents['title'] = $this->gen_contents['site_name'] . ': Check List';
        $this->gen_contents['page_title'] = 'Check List';
        $this->gen_contents['leftmenu_selected'] = 'checklist';
        $this->gen_contents['dynamic_views'][] = $this->config->item('pages') . 'checklist/list';
        $this->gen_contents['checklist'] = $this->checklist->getWhere(array('country_id'=>$this->gen_contents['selectedCountryId']));
        $this->gen_contents['countries'] = prepareOptionList($this->country->get(), array('key' => 'id', 'value' => 'country'));
        $this->load->view($this->config->item('common_page') . 'template', $this->gen_contents);
    }

    public function add() {
        if ($_POST) {
            $this->__saveChecklistData();
        }
        
        $this->gen_contents['title'] = $this->gen_contents['site_name'] . ': Add new check list';
        $this->gen_contents['page_title'] = 'Add new check list';
        $this->gen_contents['leftmenu_selected'] = 'checklist';
        $this->gen_contents['dynamic_views'][] = $this->config->item('pages') . 'checklist/add';
        $this->gen_contents['countries'] = prepareOptionList($this->country->get(), array('key' => 'id', 'value' => 'country'));
        $this->load->view($this->config->item('common_page') . 'template', $this->gen_contents);
    }
    
    private function __saveChecklistData() {
        $formData = array();
        $post = $this->input->post();
        $this->__setFormValidationRules();
        $validationError = array();
        if ($this->form_validation->run() == FALSE) {
            $validationError[] = validation_errors();
        }

        if($validationError) {
            $this->gen_contents['message'] = implode('<br/>', $validationError);
            $this->gen_contents['msg_class'] = 'error_message';
            return;
        }
        
        $formData['countryIds'] = $post['country_id'];
        $formData['checkList']  = $post['checklist'];

        if ($this->checklist->insert($formData)) {
            $this->session->set_flashdata('message', $this->entity . ' was added successfully.');
            $this->session->set_flashdata('msg_class', 'success_message');
            redirect($this->gen_contents['paths']['list']);
        } else {
            $this->session->set_flashdata('message', 'There was some problem in adding the ' . $this->entity . '.');
            $this->session->set_flashdata('msg_class', 'error_message');
            redirect($this->gen_contents['paths']['add']);
        }
    }
    
    public function edit() {
        $this->gen_contents['title'] = $this->gen_contents['site_name'] . ': Edit Check List';
        $this->gen_contents['page_title'] = 'Edit Check List';
        $this->gen_contents['leftmenu_selected'] = 'checklist';
        $this->gen_contents['dynamic_views'][] = $this->config->item('pages') . 'checklist/edit';
        $this->gen_contents['selectedCheckListId'] = strip_quotes(strip_tags(trim($this->uri->segment(3))));
        $this->gen_contents['checklist'] = $this->checklist->get($this->gen_contents['selectedCheckListId']);
        $this->gen_contents['countries'] = prepareOptionList($this->country->get(), array('key' => 'id', 'value' => 'country'));
        $this->load->view($this->config->item('common_page') . 'template', $this->gen_contents);
    }
  
    public function delete() {
        $id = strip_quotes(strip_tags(trim($this->uri->segment(4))));
        $this->checklist->delete($id);
        $this->session->set_flashdata('message', $this->entity . ' was deleted successfully.');
        $this->session->set_flashdata('msg_class', 'success_message');
        redirect($this->gen_contents['paths']['list']);
    }
    
   public function updateall() {
       $post = $this->input->post();
       $formData = array();
       foreach($post['checkListRows'] as $checkListId=>$description) {
           $formData[] = array('id'=>$checkListId, 'description'=>$description);
       }
       if ($this->checklist->updateAll($formData)) {
            $this->session->set_flashdata('message', $this->entity . ' items were updated successfully.');
            $this->session->set_flashdata('msg_class', 'success_message');
            redirect($this->gen_contents['paths']['list'].'/'.strip_quotes(strip_tags(trim($this->uri->segment(3)))));
        } else {
            $this->session->set_flashdata('message', 'There was some problem in adding the ' . $this->entity . '.');
            $this->session->set_flashdata('msg_class', 'error_message');
            redirect($this->gen_contents['paths']['add']);
        }
   }
    
    protected function __setFormValidationRules(){
        $this->form_validation->set_rules('country_id[]', 'Country', 'required|trim|xss_clean');
        $this->form_validation->set_rules('checklist[]', 'Check List', 'required|trim|xss_clean');
    }

}