<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

<style>

:root{
--primary-blue:#0d6efd;
--dark-slate:#212529;
--glass-bg:rgba(255,255,255,0.95);
}

/* BODY */

body{
background:#f0f2f5;
font-family:'Segoe UI',Roboto,Helvetica,Arial,sans-serif;
color:var(--dark-slate);
}

/* HEADER */

.search-header{
background:linear-gradient(135deg,#0d6efd 0%,#003d99 100%);
padding:45px 0;
margin-bottom:-40px;
color:white;
text-align:center;
}

.search-header h1{
font-size:26px;
font-weight:600;
}

.search-header p{
font-size:14px;
opacity:.9;
}

/* MAIN CONTAINER */

.main-container{
max-width:670px;
}

/* SEARCH CARD */

.search-card{
background:var(--glass-bg);
border:none;
border-radius:12px;
box-shadow:0 10px 25px rgba(0,0,0,0.06);
padding:20px;
}

/* INPUT */

.input-group-custom{
position:relative;
display:flex;
align-items:center;
}

.search-icon{
position:absolute;
left:14px;
color:#adb5bd;
font-size:14px;
z-index:10;
}

.form-control-lg{
height:42px;
border-radius:10px!important;
padding-left:38px;
font-size:14px;
border:2px solid #e9ecef;
transition:all .25s ease;
}

.form-control-lg:focus{
border-color:var(--primary-blue);
box-shadow:0 0 0 0.2rem rgba(13,110,253,.1);
}

.btn-search{
height:42px;
border-radius:10px;
padding:0 20px;
font-size:14px;
font-weight:600;
letter-spacing:.3px;
}

/* TABLE AREA */

.results-section{
margin-top:25px;
background:white;
border-radius:12px;
box-shadow:0 4px 12px rgba(0,0,0,0.05);
padding:10px;
}

/* TABLE SCROLL */

.table-container{
height:300px;
}

.table-scroll-x{
overflow:auto;
height:100%;
}

/* TABLE */

#result{
width:100%;
border-collapse:collapse;
white-space:nowrap;
}

/* TABLE HEADERS */

.table thead th{
position:sticky;
top:0;
background:#f8f9fa;
font-size:13px;
padding:6px 10px;
text-align:left;
}

/* TABLE CELLS */

.table td{
padding:6px 10px;
font-size:13px;
}

/* ALIGN NUMBERS */

.table td:last-child:not(:only-child),
.table th:last-child:not(:only-child){
    text-align:right;
}

/* SCROLLBAR */

.table-scroll-x::-webkit-scrollbar{
height:8px;
width:8px;
}

.table-scroll-x::-webkit-scrollbar-thumb{
background:#c1c1c1;
border-radius:6px;
}

/* LOADER */

.spinner{
display:none;
width:2.5rem;
height:2.5rem;
margin:20px auto;
}

/* FLOATING BUTTON */

#aiWidgetButton{
position:fixed;
bottom:25px;
right:25px;
width:55px;
height:55px;
background:var(--primary-blue);
border-radius:50%;
display:flex;
align-items:center;
justify-content:center;
cursor:pointer;
box-shadow:0 6px 18px rgba(0,0,0,0.25);
z-index:9999;
transition:all .25s ease;
overflow:hidden;
}

#aiWidgetButton img{
width:100%;
height:100%;
object-fit:cover;
border-radius:50%;
}

#aiWidgetButton:hover{
transform:scale(1.1);
box-shadow:0 10px 25px rgba(0,0,0,0.35);
}

/* CLEAR BUTTON */

#clearSearch{
position:absolute;
right:12px;
font-size:18px;
color:#999;
cursor:pointer;
display:none;
}

#clearSearch:hover{
color:#333;
}

/* POPUP PANEL */

#aiSearchPanel{
position:absolute;
bottom:95px;
right:60px;
width:850px;
height:640px;
background:white;
border-radius:30px;
box-shadow:0 12px 30px rgba(0,0,0,0.25);
z-index:9998;
display:flex;
flex-direction:column;
opacity:0;
transform:translateY(40px) scale(.96);
pointer-events:none;
transition:all .3s ease;
min-width:320px;
min-height:420px;
max-width:800px;
max-height:90vh;
}

#aiSearchPanel.active{
opacity:1;
transform:translateY(0) scale(1);
pointer-events:auto;
}

/* PANEL HEADER */

.panel-header{
display:flex;
justify-content:space-between;
align-items:center;
padding:12px 16px;
background:linear-gradient(135deg,#0d6efd,#174bb8);
color:white;
font-weight:600;
font-size:14px;
border-top-left-radius:14px;
border-top-right-radius:14px;
}

.close-btn{
cursor:pointer;
font-size:18px;
opacity:.8;
}

.close-btn:hover{
opacity:1;
}

/* PANEL BODY */

.panel-body{
flex:1;
overflow:hidden;
background:#f6f7fb;
}

/* SEARCH ANIMATION */

#searchArea{
transition:all .35s cubic-bezier(.4,0,.2,1);
margin-top:40px;
}

#searchArea.compact{
margin-top:-10px;
transform:translateY(-40px);
}

/* RESULT FADE */

#resultsWrapper{
opacity:0;
transform:translateY(25px);
transition:all .35s ease;
}

#resultsWrapper.show{
opacity:1;
transform:translateY(0);
}

/* TABLE ROW ANIMATION */

#result tbody tr{
opacity:0;
transform:translateY(12px);
animation:rowFade .35s ease forwards;
}

.order-main{
cursor:pointer;
background:#f8f9fa;
font-weight:600;
}

.arrow{
margin-right:6px;
transition:transform .2s ease;
}

@keyframes rowFade{
to{
opacity:1;
transform:translateY(0);
}
}

</style>
</head>

<body>

<div id="mainUI">

<div class="search-header">
<div class="container">
<h1 class="fw-bold">AZ Auto Parts Search</h1>
<p class="opacity-75">Ask us anything about products, prices, or orders.</p>
</div>
</div>

<div class="container main-container">

<!-- SEARCH CARD -->

<div id="searchArea" class="search-card">

<div class="input-group-custom mb-3">

<i class="fa-solid fa-magnifying-glass search-icon"></i>

<input
type="text"
id="question"
class="form-control form-control-lg"
placeholder="e.g. Show me the price of part number 116471">

<span id="clearSearch" onclick="clearSearch()">✕</span>

</div>

<div class="d-grid gap-2 d-md-flex justify-content-md-end">

<button class="btn btn-primary btn-search" id="searchBtn" onclick="validateAndSearch()">
    <i class="fa-solid fa-bolt me-2"></i>Search
</button>

</div>

</div>

<!-- LOADER -->

<div class="spinner-border text-primary spinner" id="loader"></div>
<!--<div id="sqlBox" style="
    display:none;
    background:#f8f9fa;
    padding:10px;
    border-radius:8px;
    margin-bottom:10px;
    font-size:12px;
    font-family:monospace;
    white-space:pre-wrap;
"></div>-->

<!-- RESULTS -->

<div class="results-section" id="resultsWrapper" style="display:none;">
    <div id="totalSalesBox" style="
    padding:10px;
    background:#f8f9fa;
    border-radius:8px;
    margin-bottom:10px;
    font-weight:600;
    display:none;
"></div>

<div class="table-container">

<div class="table-scroll-x">

<table class="table table-hover" id="result"></table>

</div>

</div>

<!-- PAGINATION -->

<div class="d-flex justify-content-center align-items-center p-2">

<div id="pageNumbers"></div>

</div>

</div>

</div>

</div>

</div>

<!-- FLOATING BOT BUTTON -->

<div id="aiWidgetButton" onclick="toggleSearchPanel()">

<img src="<?= base_url('assets/images/download_dp(men).png') ?>">

</div>

<!-- POPUP PANEL -->

<div id="aiSearchPanel">

<div id="resizeHandle"></div>

<div class="panel-header">

<span>AZ Smart Search</span>

<span class="close-btn" onclick="toggleSearchPanel()">✕</span>

</div>

<div class="panel-body">

<iframe
src="<?= base_url('SmartSearch') ?>"
style="width:100%; height:100%; border:none;">
</iframe>

</div>

<script>

let currentPage=1;
let totalPages=1;

function ask(page=1, selected=null){

var q=document.getElementById("question").value;

if(!q) return;

document.getElementById("clearSearch").style.display="block";

currentPage=page;

document.getElementById("loader").style.display="block";
document.getElementById("resultsWrapper").style.display="none";

fetch("<?= base_url('SmartSearch/ask') ?>",{

method:"POST",

headers:{
"Content-Type":"application/x-www-form-urlencoded"
},

body:
"question="+encodeURIComponent(q)+
"&page="+page+
(selected ? "&selected="+encodeURIComponent(selected) : "")

})

.then(res=>res.json())

.then(data=>{

/* -------------------------------
   HANDLE SELECTION (NEW)
--------------------------------*/
/*if(data.sql){
    document.getElementById("sqlBox").style.display = "block";
    document.getElementById("sqlBox").innerText = data.sql;
}*/
if(data.type === "selection"){

    document.getElementById("loader").style.display="none";
    document.getElementById("resultsWrapper").style.display="block";

    let html = `
    <div style="padding:15px">
        <h6>Select ${data.field}</h6>
    `;

    data.data.forEach(item=>{

        let label = item.label || item;

        html += `
        <div onclick="selectOption('${label.replace(/'/g,"\\'")}')"
             style="padding:10px;margin:6px 0;
             background:#f1f3f5;
             border-radius:8px;
             cursor:pointer;">
            ${label}
        </div>`;
    });

    html += "</div>";

    document.getElementById("result").innerHTML = html;
    document.getElementById("pageNumbers").innerHTML = "";
    return;
}

document.getElementById("loader").style.display="none";
document.getElementById("resultsWrapper").style.display="block";

let searchArea=document.getElementById("searchArea");

if(searchArea){
searchArea.classList.add("compact");
}

document.getElementById("resultsWrapper").classList.add("show");

if(data.error){
document.getElementById("result").innerHTML="<tr><td>"+data.error+"</td></tr>";
document.getElementById("pageNumbers").innerHTML="";
return;
}

var rows=data.data;

// ✅ SMART TOTAL SALES (BASED ON COLUMNS)

if(rows && rows.length > 0){

    let hasSalesColumns =
        rows[0].hasOwnProperty('price') &&
        (
            rows[0].hasOwnProperty('amount') ||
            rows[0].hasOwnProperty('Quantity')
        );

    if(hasSalesColumns){

        let totalSales = 0;
        rows.forEach(r => {

            let qty = r.amount || r.Quantity || 0;
            let price = r.price || 0;

            let val = parseFloat(
                r.total ||
                (qty * price) ||
                0
            );

            totalSales += val;
        });

        document.getElementById("totalSalesBox").style.display = "block";
        document.getElementById("totalSalesBox").innerHTML =
            "Total Sales: ₹ " + totalSales.toFixed(2);

    }else{
        document.getElementById("totalSalesBox").style.display = "none";
    }

}else{
    document.getElementById("totalSalesBox").style.display = "none";
}

totalPages=Math.ceil(data.total/data.limit);

if(!rows || rows.length===0){
document.getElementById("totalSalesBox").style.display="none";
document.getElementById("result").innerHTML="<tr><td class='text-center'>No results found</td></tr>";
document.getElementById("pageNumbers").innerHTML="";
return;
}

/* GROUP DATA BY ORDER ID */
let isOrderData = rows.length > 0 && rows[0].hasOwnProperty('order_id');

let grouped = {};

if(isOrderData && rows.length > 0){
    rows.forEach(row => {
        let id = row.order_id || "single";
        if(!grouped[id]){
            grouped[id] = [];
        }
        grouped[id].push(row);
    });

}else{
    grouped["single"] = rows; // no grouping
}

/* BUILD TABLE */
if(!grouped || Object.keys(grouped).length === 0){
    grouped = {"single": rows};
}
var html="<thead><tr>";

for(var key in rows[0]){
html+="<th>"+key+"</th>";
}

html+="</tr></thead><tbody>";

Object.keys(grouped).forEach(orderId => {

let items = grouped[orderId];
let first = items[0];

/* IF MULTIPLE PRODUCTS */

if(isOrderData && items.length > 1){

html+=`<tr class="order-main" onclick="toggleOrder('${orderId}')" style="cursor:pointer;background:#f1f3f5;font-weight:600;">`;

for(let key in first){

if(key==="order_id"){

html+=`
<td>
<i class="fa-solid fa-chevron-right arrow" id="arrow-${orderId}"></i>
${first[key]}
</td>
`;

}else{

html+=`<td>${first[key] ?? ""}</td>`;

}

}

html+="</tr>";

/* CHILD ROWS */

for(let i=1;i<items.length;i++){

let r = items[i];

html+=`<tr class="order-child child-${orderId}" style="display:none;">`;

for(let key in r){

if(key==="order_id"){
html+="<td></td>";
}else{
html+=`<td>${r[key] ?? ""}</td>`;
}

}

html+="</tr>";

}

}


/* SINGLE PRODUCT */

else{

items.forEach(r => {

html+="<tr>";

for(let key in r){
html+=`<td>${r[key] ?? ""}</td>`;
}

html+="</tr>";

});

}

});

html+="</tbody>";

document.getElementById("result").innerHTML = html;

renderPagination();

});

}

document.getElementById("question").addEventListener("focus",function(){

let searchArea=document.getElementById("searchArea");

if(searchArea){
searchArea.classList.add("compact");
}

});

/* pagination */

function renderPagination(){

if(totalPages <= 1){
document.getElementById("pageNumbers").innerHTML="";
return;
}

let html="";
let start=Math.max(1,currentPage-3);
let end=Math.min(totalPages,currentPage+3);

if(currentPage>1){
html+=`<button class="btn btn-secondary m-1" onclick="ask(${currentPage-1})">Previous</button>`;
}

for(let i=start;i<=end;i++){
html+=`<button class="btn ${i==currentPage?'btn-primary':'btn-light'} m-1" onclick="ask(${i})">${i}</button>`;
}

if(currentPage<totalPages){
html+=`<button class="btn btn-secondary m-1" onclick="ask(${currentPage+1})">Next</button>`;
}

document.getElementById("pageNumbers").innerHTML=html;

}

/* toggle popup */

function toggleSearchPanel(){

var panel=document.getElementById("aiSearchPanel");

panel.classList.toggle("active");

if(panel.classList.contains("active")){

setTimeout(function(){

document.getElementById("question").value="";
document.getElementById("resultsWrapper").style.display="none";
document.getElementById("result").innerHTML="";
document.getElementById("pageNumbers").innerHTML="";
document.getElementById("question").focus();

},300);

}

}

/* hide main UI outside iframe */

if(window.self===window.top){

document.getElementById("mainUI").style.display="none";

}else{

document.getElementById("aiWidgetButton").style.display="none";

}

/* enter key search */

document.getElementById("question").addEventListener("keypress",function(e){
if(e.key==="Enter"){
e.preventDefault();
ask(1,null);
}
});

function toggleClearIcon(){

var input=document.getElementById("question").value;

if(input.length>0){
document.getElementById("clearSearch").style.display="block";
}else{
document.getElementById("clearSearch").style.display="none";
}

}

function clearSearch(){

document.getElementById("totalSalesBox").style.display="none";
document.getElementById("question").value="";
document.getElementById("result").innerHTML="";
document.getElementById("resultsWrapper").style.display="none";
document.getElementById("pageNumbers").innerHTML="";
document.getElementById("clearSearch").style.display="none";

let searchArea=document.getElementById("searchArea");

if(searchArea){
searchArea.classList.remove("compact");
}

document.getElementById("question").focus();

}
document.addEventListener("click", function(e){

let searchArea = document.getElementById("searchArea");
let input = document.getElementById("question");

if(!searchArea) return;

if(!searchArea.contains(e.target) && !input.value){

searchArea.classList.remove("compact");

}

});
function toggleOrder(id){

let rows = document.querySelectorAll(".child-"+id);
let arrow = document.getElementById("arrow-"+id);

rows.forEach(r=>{

if(r.style.display==="none"){
r.style.display="table-row";
}else{
r.style.display="none";
}

});

if(arrow){

if(arrow.style.transform==="rotate(90deg)"){
arrow.style.transform="rotate(0deg)";
}else{
arrow.style.transform="rotate(90deg)";
}

}

}
function selectOption(value){

    document.getElementById("question").value = value;

    ask(1, value); // re-call API with selected value
}
function validateAndSearch(){
    let q = document.getElementById("question").value.trim();

    if(q === ""){
        alert("Please enter something to search");
        return;
    }

    ask(1,null);
}

</script>
</body>
</html>