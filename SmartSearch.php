<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/** @property CI_input $input
 *  @property CI_AI_model $AI_model
 *  @property CI_db $db
 */
class SmartSearch extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('AI_model');
        //$this->load->database();
    }

    public function index()
    {
        $this->load->view('smart_search_view');
    }

public function ask()
{

$question = $this->input->post('question');

$selected = $this->input->post('selected');

/* -----------------------------------
   UNIVERSAL SELECTION (SAFE ADDITION)
-----------------------------------*/

if(!$selected){

    $term = strtolower(trim($question));

    /* CUSTOMER */
    $this->db->select("CONCAT(firstname,' ',lastname) as label");
    $this->db->from("cscart_users");
    $this->db->like("firstname", $term);
    $this->db->or_like("lastname", $term);
    $customers = $this->db->get()->result_array();

    if(count($customers) > 1){
        echo json_encode([
            "type"=>"selection",
            "field"=>"customer",
            "data"=>$customers
        ]);
        return;
    }

    /* PRODUCT */
    $this->db->select("product as label");
    $this->db->from("cscart_product_descriptions");
    $this->db->like("product", $term);
    $products = $this->db->get()->result_array();

    if(count($products) > 1){
        echo json_encode([
            "type"=>"selection",
            "field"=>"product",
            "data"=>$products
        ]);
        return;
    }

    /* COMPANY */
    $this->db->select("company as label");
    $this->db->from("cscart_companies");
    $this->db->like("company", $term);
    $companies = $this->db->get()->result_array();

    if(count($companies) > 1){
        echo json_encode([
            "type"=>"selection",
            "field"=>"company",
            "data"=>$companies
        ]);
        return;
    }
}

$page = $this->input->post('page');

$limit = 20;
$page = $page ? $page : 1;
$offset = ($page - 1) * $limit;

if($selected){
    $question = $selected;
}
/* generate SQL */

$sql = $this->AI_model->generate_sql($question);
$sql = trim(rtrim($sql,';'));

/* validation */

if(!$sql){
echo json_encode(["error"=>"Please Change Your API KEY(Limit exceeded)"]);
return;
}

if(!preg_match('/^select/i',$sql)){
echo json_encode(["error"=>"Only Retriving of Data is allowed"]);
return;
}

/* total rows */

$count_query = $this->db->query($sql);

if(!$count_query){
echo json_encode(["error"=>"Database query failed"]);
return;
}

$total_rows = $count_query->num_rows();

/* detect if total_sales is needed */

$total_sales = null;

$is_sales_query = false;

/* check keywords */

if (
    stripos($question, 'sale') !== false ||
    stripos($question, 'order') !== false ||
    stripos($question, 'revenue') !== false ||
    stripos($question, 'total') !== false
) {
    $is_sales_query = true;
}

/* also check if SQL contains amount/price (order query) */
if (
    stripos($sql, 'od.amount') !== false ||
    stripos($sql, 'sum(') !== false
) {
    $is_sales_query = true;
}

/* ONLY calculate if needed */

if ($is_sales_query && stripos($sql, 'over()') === false) {

    $total_sales_sql = "
    SELECT SUM(od.amount * od.price) AS total_sales
    FROM cscart_orders o
    JOIN cscart_order_details od ON o.order_id = od.order_id
    JOIN cscart_products p ON p.product_id = od.product_id
    JOIN cscart_companies c ON c.company_id = o.company_id
    JOIN cscart_product_descriptions pd ON pd.product_id = p.product_id
    ";

    if (stripos($sql, 'where') !== false) {
        $where_part = substr($sql, stripos($sql, 'where'));
        $where_part = preg_replace('/order\s+by.*/i', '', $where_part);
        $total_sales_sql .= " " . $where_part;
    }

    $total_query = $this->db->query($total_sales_sql);

    if ($total_query) {
        $row = $total_query->row_array();
        $total_sales = $row['total_sales'] ?? 0;
    }
}

/* pagination */

$paginated_sql = $sql." LIMIT $limit OFFSET $offset";

$query = $this->db->query($paginated_sql);

if(!$query){
echo json_encode(["error"=>"Unable to process your Question"]);
return;
}

$result = $query->result_array();

/* response */

echo json_encode([
"data"=>$result,
"total"=>$total_rows,
"page"=>$page,
"limit"=>$limit,
"total_sales"=>$total_sales
]);

}
}