<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ticket_model extends CI_Model {

    public function insert_ticket($data) {

        $query = $this->db->get_where('tickets', [
            'email_uid' => $data['email_uid']
        ]);

        if (!$query->row()) {
            $this->db->insert('tickets', $data);
        }
    }

    public function get_all_tickets() {
        return $this->db
            ->order_by('created_at', 'DESC')
            ->get('tickets')
            ->result();
   }
}