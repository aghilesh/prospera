<?php

class Country_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->table = 'country';
    }

    /**
     * Insert record
     */
    public function insert($formData) {
        $this->db->insert($this->table, $formData);
        return ($this->db->affected_rows() == '1') ? TRUE : FALSE;
    }

    /**
     * Update Record
     */
    public function update($formData, $id) {
        $this->db->where('id', $id);
        return $this->db->update($this->table, $formData);
    }

    /**
     * Delete record
     */
    public function delete($id) {
        return $this->db->delete($this->table, array('id' => $id));
    }

    /**
     * All the records
     */
    public function get($id = '') {
        $this->db->select('*');
        if ($id) {
            $this->db->where('id', $id);
        }
        $query = $this->db->get($this->table);
        return ($id) ? $query->row() : $query->result();
    }

}

?>