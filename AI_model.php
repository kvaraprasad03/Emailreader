<?php

class AI_model extends CI_Model {

public function generate_sql($question)
{

$schema = "

Tables:

cscart_products(product_id, product_code, company_id)
cscart_product_descriptions(product_id, product)
cscart_az_vendor_product(product_id, product_price, product_list_price, discount,company_id)
cscart_companies(company_id,company)
cscart_orders(order_id,az_preorder_seq,user_id,total, timestamp,ref_invoice_id,po_ref_no)
cscart_order_details(order_id, product_id, price, amount)
cscart_users(user_id, firstname, lastname)
cscart_az_payments(payment_id,order_id,company_id,status,advance_payment_id_)
cscart_payment_descriptions(payment_id,payment,description)
az_preorder_sequence_numbers(pre_order_seq_id,req_type,status)
az_request_converted_orders(estimate_seq_id,estimate_id,response_id,order_sequence_id)
cscart_az_responses(response_id,request_id,company_id,product_id,available_qty)
cscart_az_requests(request_id,az_preorder_seq,user_id,product_id,product_name,product_code)
Vehicle Tables:

cscart_az_m_oem(OEM_id, OEM_name)
cscart_az_m_model(model_id, model_name, OEM_id)
cscart_az_m_variant(variant_id, variant_name, model_id)
cscart_az_m_productsbyvehicle(product_id, OEM_id, model_id, variant_id)

Relationships:

cscart_products.product_id = cscart_product_descriptions.product_id
cscart_products.product_id = cscart_az_vendor_product.product_id
cscart_products.product_id = cscart_order_details.product_id
cscart_products.product_id = cscart_az_m_productsbyvehicle.product_id
cscart_products.company_id = cscart_companies.company_id
cscart_orders.order_id = cscart_order_details.order_id
cscart_orders.user_id = cscart_users.user_id
cscart_az_m_productsbyvehicle.OEM_id = cscart_az_m_oem.OEM_id
cscart_az_m_productsbyvehicle.model_id = cscart_az_m_model.model_id
cscart_az_m_productsbyvehicle.variant_id = cscart_az_m_variant.variant_id
cscart_az_m_model.OEM_id = cscart_az_m_oem.OEM_id
cscart_az_m_variant.model_id = cscart_az_m_model.model_id
cscart_orders.status=cscart_status_descriptions.status
cscart_orders.payment_id=cscart_az_payments.payment_id
cscart_az_payments.payment_id=cscart_payment_descriptions.payment_id
cscart_orders.az_preorder_seq=az_preorder_sequence_numbers.pre_order_seq_id
cscart_az_preorder_sequence_numbers.az_pre_order_seq_id=az_request_converted_orders.estimate_seq_id when req_type=P
cscart_az_preorder_sequence_numbers.az_pre_order_seq_id=az_request_converted_orders.order_sequence_id when req_type=O
cscart_az_responses.response_id=az_request_converted_orders.response_id
cscart_az_requests.request_id=az_request_converted_orders.estimate_id
cscart_az_requests.az_preorder_seq=cscart_orders.az_preorder_seq
";

$prompt = "

Convert the user input into a MySQL SELECT query.

USER INPUT HANDLING:

1. Detect spelling mistakes
2. Convert to correct business terms
3. Map to database schema
4.Always ensure no duplicate rows are returned.
   Use DISTINCT or GROUP BY to return only one unique record.
top = highest = best
customers = users = buyers
sales = revenue = total_spent

Example:
pendng paymnts → pending payments
invoces of vinod → invoices of vinod

CASE 
WHEN status=0 THEN 'Printing Estimation'
WHEN status=1 THEN 'Estimation'
WHEN status=7 THEN 'OPEN'
WHEN status=8 THEN 'REJECTED'
WHEN status=9 THEN 'CANCELLED'
WHEN status=10 THEN 'PENDING'
WHEN status=11 THEN 'CLOSED'
WHEN status=12 THEN 'REFUNDED'
END
Rules:

While displaying the Tables always use Capital for the first letter in the Word
1. Generate only SELECT queries.
2. Do not generate INSERT, UPDATE, DELETE, DROP, CREATE or ALTER queries.
3. Return only the SQL query. Do not include explanations.
5. For order details of company,product,customer always use cscart_orders table as the basse table
4. Use JOINs whenever multiple tables are needed.
5. For text search use:
   LOWER(column_name) LIKE '%value%'
6. If a timestamp column is used convert it using:
   FROM_UNIXTIME(timestamp)
7. If user asks about price, cost or product price return:
   product_name,
   product_code,
   product_price,
   model,
   variant
   from table:
   cscart_az_vendor_product
   cscart_az_m_productsbyvehicle
8. If user searches using only part number or product number 
  example :price of A-6591059 or A-6591059
  return: select pd.product,p.product_code,p.list_price as Price from cscart_product_descriptions pd 
  JOIN cscart_products p on p.product_id =pd.product_id where p.product_code='A-6591059';
9. If user asks for companies in a place return:
   company,
   address,
   email,
   phone,
   city,
   state,
   country
  If user search for customers or vendors in place return use:
    SELECT 
    CONCAT(u.firstname,' ',u.lastname) AS Customer_Name, 
    u.email as Email,
    u.phone as Phone,
    from cscart_users u
    join cscart_companies c
    on u.company_id=c.company_id
    where LOWER(c.state) LIKE '%telangana%'
    OR LOWER(c.city) LIKE '%hyderabad%'
    OR LOWER(c.country) LIKE '%in%'
10. If user asks about company product sales return:
   order_id,
   Customer_Name,
   product,
   product_code
11. If user asks for sales of a company or product in the last N months use:
   FROM_UNIXTIME(o.timestamp) >= DATE_SUB(CURDATE(), INTERVAL N MONTH)
   and return total sales.
12. If user asks for highest sales in a place or top companies sales return:
   company,
   SUM(order_details.amount * order_details.price) AS sales
   grouped by company,
   ordered by sales DESC,
   limit 10.
13. Always calculate product sales using:
   SUM(order_details.amount * order_details.price)
14. When searching company names use:
   LOWER(c.company) LIKE '%company_keyword%'
   Use the main keyword of the company name.
15.1. Return ONLY SQL.
   2. Always start the query with SELECT.
   3. Do not include explanations.
   4. Always use full table names with prefix cscart_.
   5. Generate valid MySQL syntax only.
16.if user asks about the total sales 
   return:
   SELECT
   o.order_id,
   CONCAT(u.firstname,' ',u.lastname) AS Customer_Name,
   pd.product as Product,
   od.product_code as Product_Code,
   od.amount AS Amount,
   od.price,
   (od.amount * od.price) AS Total,
   FROM_UNIXTIME(o.timestamp) AS Order_date

   FROM cscart_orders o
   JOIN cscart_order_details od
   ON o.order_id = od.order_id
   JOIN cscart_product_descriptions pd
   ON pd.product_id = od.product_id
   JOIN cscart_users u
   ON o.user_id = u.user_id
   ORDER BY o.timestamp DESC;
17. If user asks about:

* top selling products
* most sold products
* best selling products

Return:

pd.product as Product,
p.product_code as Product_Code,
SUM(od.amount) AS Total_Quantity_Sold,
SUM(od.amount * od.price) AS Total_Sales

FROM cscart_order_details od
JOIN cscart_products p ON od.product_id = p.product_id
JOIN cscart_product_descriptions pd ON pd.product_id = p.product_id

GROUP BY p.product_id, pd.product, p.product_code

ORDER BY total_quantity_sold DESC;

18. If query involves:
   * orders
   * customer history
   * company sales
   * product sales
   ALWAYS include:
   od.amount AS Amount,
   od.price,
   (od.amount * od.price) AS Total
   Never rename amount as Quantity or any other name.
19. If user input is a single word (like 'vinod', 'suresh', 'raju'):

   Generate query for user order history:

   SELECT
   o.order_id,
   CONCAT(u.firstname,' ',u.lastname) AS Customer_Name,
   pd.product,
   od.product_code,
   o.ref_invoice_id as Invoice_NO,
   c.company,
   od.amount AS amount,
   od.price,
   (od.amount * od.price) AS total,
   FROM_UNIXTIME(o.timestamp) AS order_date
   FROM cscart_orders o
   JOIN cscart_users u ON o.user_id = u.user_id
   JOIN cscart_order_details od ON o.order_id = od.order_id
   JOIN cscart_products p ON od.product_id = p.product_id
   JOIN cscart_companies c ON c.company_id = o.company_id
   JOIN cscart_product_descriptions pd ON pd.product_id = od.product_id
   WHERE LOWER(u.firstname) LIKE '%name%'
   OR LOWER(u.lastname) LIKE '%name%'
   ORDER BY o.timestamp DESC;
20.if the output contains same ids either order_id,invoive_id or anytype of ids 
   show them definetly in the form of dropdown
21. if user asks about 
   -who payed the bill through check or credit card or any thing 
      cash → C.O.D
   use table cscart_orders, cscart_payment_descriptions and cscart_az_payments
   return:
   SELECT
   o.order_id,
   CONCAT(u.firstname,' ',u.lastname) AS Customer_Name, 
   pd.product,
   c.company, o.total AS Invoice_Amount,o.ref_invoice_id as Invoice_NO,
   pad.payment AS Payment_type, 
   o.payed_amount, o.balance_amount, 
   sd.description AS status, 
   FROM_UNIXTIME(o.timestamp) AS invoice_date 
   FROM cscart_orders o 
   JOIN cscart_status_descriptions sd 
   ON o.status = sd.status 
   JOIN cscart_users u
   ON o.user_id=u.user_id
   JOIN cscart_companies c
   ON o.company_id=c.company_id
   JOIN cscart_payment_descriptions pad
   ON o.payment_id=pad.payment_id
   JOIN cscart_order_details od
   ON o.order_id=od.order_id
   JOIN cscart_product_descriptions pd
   ON od.product_id=pd.product_id
   Order by o.timestamp DESC;
22.if user asks about the inactive customers if any customer who is not ordered 
   any product from last 12 months(o.timestamp),u.user_type='C' and also print last ordered date(FROM_UNIXTIME(o.timestamp) AS Lastorder_date )
   by using this condition return those customers
23.if user asks about 
   -show me the details of invoice AZ/25-26/0001
   -AZ/25-26/0001
   -or by a invoice number
   return:
   order_id,firstname,product_code,product,ref_invoice_id,total,order_date
---------------------------------------------------------------------------------------
Database Interpretation Rules

Users table:
cscart_users
Orders table:
cscart_orders
Order details table:
cscart_order_details
Products table:
cscart_products
Product description table:
cscart_product_descriptions
Companies table:
cscart_companies 
Vendor product table:
cscart_az_vendor_product

----------------------------------------------------------------------------            N month sales of a company
Last N month sales for the company:

If the user asks:
last n month sales for company
sales in last n months for company

Use:

timestamp >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 12 MONTH))

Return:

order_id,
customer_name,
product,
product_code,
ref_invoice_id as Invoice_NO,
company,
mount,
price,
amount * price AS total,
order_date

Tables used:

cscart_orders
cscsart_companies
cscart_products
cscart_product_descriptions
cscart_users
cscart_order_details

Join:

FROM cscart_orders o 
JOIN cscart_order_details od 
ON o.order_id = od.order_id 
JOIN cscart_products p 
ON od.product_id = p.product_id 
JOIN cscart_companies c 
ON o.company_id = c.company_id
JOIN cscart_product_descriptions pd 
ON p.product_id = pd.product_id 
JOIN cscart_users u 
ON o.user_id = u.user_id 

example:
Last 12 months sales for Autozilla solutions

sql template:

SELECT 
o.order_id,
CONCAT(u.firstname,' ',u.lastname) AS Customer_Name, 
pd.product as Product_Name, 
od.product_code, 
o.ref_invoice_id as Invoice_NO,
c.company, 
od.amount, 
od.price, 
(od.amount * od.price) AS Total, 
FROM_UNIXTIME(o.timestamp) AS Order_date 
FROM cscart_orders o 
JOIN cscart_order_details od 
ON o.order_id = od.order_id 
JOIN cscart_companies c 
ON o.company_id = c.company_id 
JOIN cscart_product_descriptions pd 
ON p.product_id = pd.product_id 
JOIN cscart_users u 
ON o.user_id = u.user_id 
WHERE o.timestamp >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 12 MONTH)) 
AND LOWER(c.company) LIKE '%autozilla%'
ORDER BY o.timestamp DESC;
----------------------------------------------------------------------------                customers,vendors list
Customer List Rule

If the user asks:
list customers
show customers
customer list

Return:
firstname,
lastname,
email,
phone,
user_type 'c' as Customer

From:
cscart_users

Filter:
user_type = 'C'


Vendor List Rule

If the user asks:
list vendors
show vendors
vendor list

Return:
firstname,
lastname,
email,
phone,
user_type 'v' as Vendor

From:
cscart_users

Filter:
user_type = 'V'
------------------------------------------------------------------------------------                overall last n month sales
Sales History rules:

If user asks about :
total sales of a compnay.
last n months sales of company.

tables used:

cscart_products
cscart_product_descriptions
cscart_order_details
cscart_orders
cscart_users
cscart_az_vendor_product
cscart_companies

Join:

cscart_orders o
JOIN cscart_users u
ON o.user_id = u.user_id
JOIN cscart_order_details od
ON o.order_id = od.order_id
JOIN cscart_products p
ON od.product_id = p.product_id
JOIN cscart_product_descriptions pd
ON p.product_id = pd.product_id

Return:

order_id,
firstname,
lastname,
product,
ref_invoice_id as Invoice_NO,
amount,
price,
order_total,
FROM_UNIXTIME(o.timestamp) AS order_date
SUM(order_details.amount * order_details.price) AS total_sales

example:
last 3 month sales

sql templete:

Select
o.order_id,
CONCAT(u.firstname,' ',u.lastname) AS Customer_Name,
pd.product as Product_Name,
p.product_code,
o.ref_invoice_id as Invoice_NO,
c.company,
od.amount,
od.price,
FROM_UNIXTIME(o.timestamp) AS order_date

from cscart_orders o
JOIN cscart_users u
ON o.user_id = u.user_id
JOIN cscart_order_details od
ON o.order_id = od.order_id
JOIN cscart_products p
ON od.product_id = p.product_id
JOIN cscart_companies c
on o.company_id=c.company_id
JOIN cscart_product_descriptions pd
ON p.product_id = pd.product_id
WHERE WHERE o.timestamp >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 3 MONTH)) 
ORDER BY o.timestamp DESC;
------------------------------------------------------------------------------------                    customer name history details
User Order History Rule

If input contains a person name return all orders of that user with product information.

Join:

cscart_orders o
JOIN cscart_users u
ON o.user_id = u.user_id
JOIN cscart_order_details od
ON o.order_id = od.order_id
JOIN cscart_products p
ON od.product_id = p.product_id
JOIN cscart_product_descriptions pd
ON p.product_id = pd.product_id

Return:

total_sales,
order_id,
CONCAT(u.firstname,' ',u.lastname) AS customer_name,
product,
ref_invoice_id as Invoice_NO,
quantity,
price,
(od.amount * od.price) AS total,
FROM_UNIXTIME(o.timestamp) AS order_date

Filter:

LOWER(u.firstname) LIKE '%name%'
OR LOWER(u.lastname) LIKE '%name%'

Example

Input:
vinod

SQL Output:

SELECT
o.order_id,
CONCAT(u.firstname,' ',u.lastname) AS Customer_Name,
pd.product as Product_Name,
od.product_code,
o.ref_invoice_id as Invoice_NO,
c.company,
od.amount as Quantity,
od.price,
(od.amount * od.price) AS Total,
FROM_UNIXTIME(o.timestamp) AS Order_Date
FROM cscart_orders o
JOIN cscart_users u
ON o.user_id = u.user_id
JOIN cscart_order_details od
ON od.order_id = o.order_id
JOIN cscart_companies c 
ON c.company_id=o.company_id 
JOIN cscart_product_descriptions pd
ON pd.product_id = od.product_id
WHERE LOWER(u.firstname) LIKE '%vinod%'
OR LOWER(u.lastname) LIKE '%vinod%';
----------------------------------------------------------------                                details of who ordered product
Product Order History Rule

User queries
who ordered product
order history of product
customers who bought product

use tabels:

cscart_product_descriptions
cscart_order_details
cscart_orders
cscart_users
cscart_companies

example :
who ordered oil filter

SQL template:
SELECT
o.order_id,
CONCAT(u.firstname,' ',u.lastname) AS Customer_Name,
pd.product as Product_Name,
od.product_code,
o.ref_invoice_id as Invoice_NO,
c.company,
od.amount,
od.price,
FROM_UNIXTIME(o.timestamp) AS Order_Date
FROM cscart_orders o
JOIN cscart_order_details od
ON o.order_id = od.order_id
JOIN cscart_companies c
ON c.company_id = o.company_id
JOIN cscart_product_descriptions pd
ON pd.product_id = od.product_id
JOIN cscart_users u
ON o.user_id = u.user_id
WHERE LOWER(pd.product) LIKE '%oil%'
OR LOWER(pd.product) LIKE '%filter%'
OR LOWER(od.product_code) LIKE 'EK5049'
ORDER BY o.timestamp DESC;
-----------------------------------------------------------                                 overall company sales
Company Sales History Rule

If the user asks:

sales of company
orders of company
company order history
company sales history
input contains company name only return all the sales of the company

Tables used:

cscart_orders o
cscart_product_descriptions pd
cscart_order_details od
cscart_users u
cscart_companies c

Join rules:

o.order_id = od.order_id
c.company_id = o.company_id
o.user_id = u.user_id
pd.product_id = od.product_id

Return:

o.order_id,
CONCAT(u.firstname,' ',u.lastname) AS Customer_Name,
c.company,
pd.product as Product_Name,
od.product_code as Product_code,
o.ref_invoice_id as Invoice_NO,
od.amount as Quantity,
od.price,
(od.amount * od.price) AS Total,
FROM_UNIXTIME(o.timestamp) AS Order_Date

example:
sales of laxmi ganapathi automobiles

SQL template:

SELECT
o.order_id,
CONCAT(u.firstname,' ',u.lastname) AS Customer_Name,
c.company,
pd.product as Product_Name,
od.product_code as Product_code,
o.ref_invoice_id as Invoice_NO,
od.amount as Quantity,
od.price,
(od.amount * od.price) AS Total,
FROM_UNIXTIME(o.timestamp) AS Order_Date
FROM cscart_orders o
JOIN cscart_order_details od
ON o.order_id = od.order_id

JOIN cscart_companies c
ON c.company_id = o.company_id

JOIN cscart_product_descriptions pd
ON pd.product_id = od.product_id

JOIN cscart_users u
ON o.user_id = u.user_id

WHERE LOWER(c.company) LIKE '%laxmi%'
ORDER BY o.timestamp DESC;
--------------------------------------------------------------------                          details of who ordered product by company
Company Product Sales Rule

If user asks:

company product sales
products sold by company

Use tables:

cscart_orders o
cscart_products p
cscart_product_descriptions pd
cscart_order_details od
cscart_users u
cscart_az_vendor_product vp
cscart_companies c

example:
Who ordered fuel pump by sushil motors

SQL template:

SELECT
o.order_id,
CONCAT(u.firstname,' ',u.lastname) AS Customer_Name,
c.company as Company,
pd.product as Product_Name,
od.product_code as Product_Code,
o.ref_invoice_id as Invoice_NO,
od.amount as Amount,
od.price as Price,
(od.amount * od.price) AS Total,
FROM_UNIXTIME(o.timestamp) AS Order_Date
FROM cscart_orders o

JOIN cscart_order_details od
ON o.order_id = od.order_id

JOIN cscart_companies c
ON c.company_id = o.company_id

JOIN cscart_product_descriptions pd
ON pd.product_id = od.product_id

JOIN cscart_users u
ON o.user_id = u.user_id

WHERE LOWER(c.company) LIKE '%sushil%'
AND LOWER(pd.product) LIKE '%fuel%'
AND LOWER(od.product_code) LIKE 'A-5971096'
ORDER BY o.timestamp DESC;

Return:

order_id,
customer_name,
company,
product_name,
product_code,
ref_invoice_id as Invoice_NO,
amount,
price,
total,
order_date
----------------------------------------------------------------------------------    TOP CUSTOMER AND COMPANY ANALYTICS RULE

If user asks:

- top customers
- top 10 customers
- best customers
- highest spending customers
- customers by sales
- top buyers

Generate:

SELECT
CONCAT(u.firstname,' ',u.lastname) AS Customer_Name,
u.email as Email,
SUM(od.amount * od.price) AS Total_spent
FROM cscart_orders o
JOIN cscart_users u 
ON o.user_id = u.user_id
JOIN cscart_order_details od 
ON o.order_id = od.order_id
WHERE u.user_type = 'C'
ORDER BY total_spent DESC

If user asks:

- top companies
- top 10 companies
- best companies
- highest spending companies
- companies by sales
- top companies
return:
SELECT 
    c.company,
    SUM(od.amount * od.price) AS Total_Sales
FROM cscart_orders o
JOIN cscart_order_details od ON o.order_id = od.order_id
JOIN cscart_companies c ON c.company_id = o.company_id
GROUP BY c.company_id
ORDER BY total_sales DESC;
--------------------------------------------------------------------------------------                           Invoice Rules
INVOICE RULES:

Invoices are stored in cscart_orders table.
important columns :
ref_invoice_id,user_id,order_id

If user asks about:
show me all invoices
show me the details of invoice by AZAP/24-25/0008.
Invoices which are pending,closed,rejected or any status.
example 1:
show me all invoices
return:(status as mentioned in the above prompt)
SELECT
o.order_id,
CONCAT(u.firstname,' ',u.lastname) AS Customer_Name,
c.company,
o.total AS Invoice_Amount,
o.ref_invoice_id AS Invoice_NO,
sn.status,
FROM_UNIXTIME(o.timestamp) AS Invoice_date
FROM cscart_orders o
JOIN az_preorder_sequence_numbers sn
ON o.az_preorder_seq=sn.pre_order_seq_id
JOIN cscart_users u
ON o.user_id = u.user_id
JOIN cscart_companies c
ON o.company_id = c.company_id
ORDER BY o.timestamp DESC;

example 2:
Show me the status of AZAP/24-25/0008
return:
SELECT 
o.order_id,
CONCAT(u.firstname,' ',u.lastname) as Customer_Name,
o.ref_invoice_id AS Invoice_No,
CASE 
    WHEN asp.status = 0 THEN 'Printing Estimation'
    WHEN asp.status = 1 THEN 'Estimation'
    WHEN asp.status = 2 or 3 THEN 'Estimation' 
    WHEN asp.status = 7 THEN 'OPEN'
    WHEN asp.status = 8 THEN 'REJECTED'
    WHEN asp.status = 9 THEN 'CANCELLED'
    WHEN asp.status = 10 THEN 'PENDING'
    WHEN asp.status = 11 THEN 'CLOSED'
    WHEN asp.status = 12 THEN 'REFUNDED'
    ELSE 'UNKNOWN'
END AS Status,asp.pre_order_seq_num as Sequence_number
FROM cscart_orders o
INNER JOIN cscart_users u 
ON u.user_id = o.user_id
INNER JOIN az_preorder_sequence_numbers asp 
ON o.az_preorder_seq = asp.pre_order_seq_id
INNER JOIN cscart_product_descriptions pd 
ON od.product_id = pd.product_id
WHERE o.ref_invoice_id = 'AZAP/24-25/0008';
--------------------------------------------------------------------------------------           Request id rules
if user asks:
   status of JEE-SO17447
   ot by any status id 
   important rule:
   always return enquiry_id as the CONCAT(asn.vendor_code,'-',arco.estimate_seq_id)
   if status is UNKNOWN remove that row
   return:
SELECT DISTINCT
    asn.pre_order_seq_num,
    asn.status (mentioned in the starting of the prompt) ,
    o.ref_invoice_id as Invoice_No,
    CONCAT(o.firstname,' ',o.lastname) AS Customer_Name,
    CASE 
        WHEN arco.order_sequence_id IS NOT NULL 
        THEN CONCAT(ep.vendor_code, '-', ep.pre_order_seq_id)
        ELSE NULL
    END AS Enquiry_id
   FROM az_preorder_sequence_numbers asn
   LEFT JOIN az_request_converted_orders arco
   ON arco.order_sequence_id = asn.pre_order_seq_id
   LEFT JOIN az_preorder_sequence_numbers ep
   ON ep.pre_order_seq_id = arco.estimate_seq_id 
   LEFT JOIN cscart_orders o
   ON o.az_preorder_seq = asn.pre_order_seq_id
   WHERE asn.pre_order_seq_num = 'JEE-SO17447';
if user search by the enquiry id :
   example:details of enquiry id JEE-50385
   return:
   SELECT DISTINCT
    asn.pre_order_seq_num as Order_Num,
    o.ref_invoice_id AS Invoice_No,
    o.order_id,
    CONCAT(u.firstname, ' ', u.lastname) AS Customer_Name
FROM az_request_converted_orders arco
JOIN az_preorder_sequence_numbers asn
    ON asn.pre_order_seq_id = arco.order_sequence_id
JOIN cscart_orders o
    ON o.az_preorder_seq = asn.pre_order_seq_id
JOIN cscart_users u
    ON o.user_id=u.user_id
WHERE arco.estimate_seq_id = SUBSTRING_INDEX('JEE-50385', '-', -1);
-------------------------------------------------------------------------------------- 
Date Interpretation Rules

sales = orders

today:
DATE(FROM_UNIXTIME(timestamp)) = CURDATE()

yesterday:
DATE(FROM_UNIXTIME(timestamp)) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)

this month:
MONTH(FROM_UNIXTIME(timestamp)) = MONTH(CURDATE())
AND YEAR(FROM_UNIXTIME(timestamp)) = YEAR(CURDATE())

last month:
MONTH(FROM_UNIXTIME(timestamp)) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
AND YEAR(FROM_UNIXTIME(timestamp)) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))

lastn months:
MONTH(FROM_UNIXTIME(timestamp)) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))

last year:
YEAR(FROM_UNIXTIME(timestamp)) = YEAR(CURDATE()) - 1

latest month:
orders from month with MAX(timestamp)
---------------------------------------------------------------------------------------
Schema:

$schema

User Input:
$question

";
/* gemini api */

$key = $this->config->item('gemini_key');

$url = "https://generativelanguage.googleapis.com/v1/models/gemini-2.5-flash:generateContent?key=".$key;

$data = [
"contents"=>[
[
"parts"=>[
["text"=>$prompt]
]
]
]
];

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL,$url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
curl_setopt($ch, CURLOPT_POST,true);
curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER,[
"Content-Type: application/json"
]);

$response = curl_exec($ch);
$res = json_decode($response,true);

if(!isset($res['candidates'][0]['content']['parts'][0]['text']))
{
    return "";
}

$sql = $res['candidates'][0]['content']['parts'][0]['text'];

/* remove markdown formatting */

$sql = str_replace("```sql","",$sql);
$sql = str_replace("```","",$sql);

$sql = trim($sql);
return $sql;
}
}