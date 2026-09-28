const API='api/sales.php';
const EXPENSE_API='api/expenses.php';
let rows=[]; let currentSalesPage=1; const SALES_PAGE_SIZE=5; let salesChart=null; let channelChart=null; let productChart=null; let monthlyChart=null;
const $=id=>document.getElementById(id);
const money=n=>'RM '+Number(n||0).toLocaleString('en-MY',{minimumFractionDigits:2,maximumFractionDigits:2});

// Display dates in day/month format while keeping the database/date input in ISO format.
function formatDateDMY(dateString){
  if(!dateString) return '';
  const parts=String(dateString).slice(0,10).split('-');
  if(parts.length!==3) return String(dateString);
  return `${parts[2]}/${parts[1]}/${parts[0]}`;
}
function formatDateDM(dateString){
  if(!dateString) return '';
  const parts=String(dateString).slice(0,10).split('-');
  if(parts.length!==3) return String(dateString);
  return `${parts[2]}-${parts[1]}`;
}

function esc(s){return String(s).replace(/[&<>'"]/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[c]));}

async function loadData(){
  const params=new URLSearchParams();
  if($('year').value) params.set('year',$('year').value);
  if($('month').value) params.set('month',$('month').value);
  if($('search').value.trim()) params.set('search',$('search').value.trim());
  if($('range').value) params.set('range',$('range').value.toLowerCase());
  try{const res=await fetch(API+'?'+params);const json=await res.json();if(!json.success)throw new Error(json.message);rows=json.data||[];currentSalesPage=1;render(json)}catch(e){alert('Could not load dashboard. Make sure Apache and MySQL are running and the database was imported.\n\n'+e.message)}
}
function render(j){
  $('totalSales').textContent=money(j.kpi.total_sales);$('totalOrders').textContent=Number(j.kpi.total_orders).toLocaleString();$('totalExpenses').textContent=money(j.kpi.total_expenses);$('totalProfit').textContent=money(j.kpi.total_profit);
  renderSalesSummary();
  renderProducts(j.products||[]);renderCharts(j.trend||[],j.channels||[],j.products||[],j.range||$('range').value.toLowerCase());
}
function getSummaryFilteredRows(){
  const from=$('summaryDateFrom')?.value||'', to=$('summaryDateTo')?.value||'';
  const product=$('summaryProduct')?.value||'', channel=$('summaryChannel')?.value||'';
  const minRaw=$('summaryMinSales')?.value||'', maxRaw=$('summaryMaxSales')?.value||'';
  const min=minRaw===''?null:Number(minRaw), max=maxRaw===''?null:Number(maxRaw);
  return rows.filter(r=>{
    const d=String(r.sale_date||'').slice(0,10), amount=Number(r.sales_amount||0);
    return (!from||d>=from)&&(!to||d<=to)&&(!product||r.product===product)&&(!channel||r.channel===channel)&&(min===null||amount>=min)&&(max===null||amount<=max);
  });
}
function populateSummaryFilterOptions(){
  const p=$('summaryProduct'), c=$('summaryChannel'); if(!p||!c)return;
  const pv=p.value, cv=c.value;
  const products=[...new Set(rows.map(r=>r.product).filter(Boolean))].sort((a,b)=>a.localeCompare(b));
  const channels=[...new Set(rows.map(r=>r.channel).filter(Boolean))].sort((a,b)=>a.localeCompare(b));
  p.innerHTML='<option value="">All Products</option>'+products.map(v=>`<option value="${esc(v)}">${esc(v)}</option>`).join('');
  c.innerHTML='<option value="">All Channels</option>'+channels.map(v=>`<option value="${esc(v)}">${esc(v)}</option>`).join('');
  if(products.includes(pv))p.value=pv;if(channels.includes(cv))c.value=cv;
}
function renderSalesSummary(){
  populateSummaryFilterOptions();
  const filteredRows=getSummaryFilteredRows();
  const totalPages=Math.max(1,Math.ceil(filteredRows.length/SALES_PAGE_SIZE));
  if(currentSalesPage>totalPages) currentSalesPage=totalPages;
  const start=(currentSalesPage-1)*SALES_PAGE_SIZE;
  const pageRows=filteredRows.slice(start,start+SALES_PAGE_SIZE);
  $('salesBody').innerHTML=pageRows.map(r=>`<tr><td>${formatDateDMY(r.sale_date)}</td><td>${esc(r.product)}</td><td>${esc(r.channel)}</td><td>${r.orders}</td><td>${money(r.sales_amount)}</td><td><button class="action" onclick="editSale(${r.id})">Edit</button><button class="action delete" onclick="deleteSale(${r.id})">Delete</button></td></tr>`).join('')||'<tr><td colspan="6" style="text-align:center;padding:25px">No sales found.</td></tr>';
  const pager=$('salesPagination');
  const status=$('summaryFilterStatus'); if(status)status.textContent=`Showing ${filteredRows.length} of ${rows.length} sales records`;
  if(filteredRows.length<=SALES_PAGE_SIZE){pager.innerHTML='';return;}
  let html=`<button ${currentSalesPage===1?'disabled':''} onclick="changeSalesPage(${currentSalesPage-1})">‹ Previous</button>`;
  for(let i=1;i<=totalPages;i++) html+=`<button class="${i===currentSalesPage?'active':''}" onclick="changeSalesPage(${i})">${i}</button>`;
  html+=`<button ${currentSalesPage===totalPages?'disabled':''} onclick="changeSalesPage(${currentSalesPage+1})">Next ›</button>`;
  pager.innerHTML=html;
}
window.changeSalesPage=page=>{
  const totalPages=Math.max(1,Math.ceil(getSummaryFilteredRows().length/SALES_PAGE_SIZE));
  currentSalesPage=Math.min(Math.max(1,page),totalPages);
  renderSalesSummary();
};
function renderProducts(items){const max=Math.max(...items.map(x=>Number(x.value)),1);$('products').innerHTML=items.map(x=>`<div class="product-row"><div><div class="product-name">${esc(x.label)}</div><div class="product-meta">${x.orders} orders</div><div class="bar"><span style="width:${Math.max(5,Number(x.value)/max*100)}%"></span></div></div><div class="product-value">${money(x.value)}</div></div>`).join('')||'<p style="color:#999;font-size:12px">No product data.</p>'}
function renderCharts(trend,channels,products,range='daily'){
  if(salesChart)salesChart.destroy();if(channelChart)channelChart.destroy();if(productChart)productChart.destroy();
  salesChart=new Chart($('salesChart'),{type:'line',data:{labels:trend.map(x=>{if(range==='daily')return formatDateDM(x.label); if(range==='weekly')return 'Week '+x.label.slice(5); return x.label;}),datasets:[{label:'Sales',data:trend.map(x=>Number(x.value)),borderColor:'#14b8a6',backgroundColor:'rgba(20,184,166,.10)',fill:true,tension:.3,pointRadius:3}]},options:{responsive:true,plugins:{legend:{display:false},tooltip:{callbacks:{title:items=>items.length?items[0].label:'' ,label:c=>money(c.raw)}}},scales:{y:{beginAtZero:true,ticks:{callback:v=>'RM '+v}},x:{grid:{display:false}}}}});
  channelChart=new Chart($('channelChart'),{type:'doughnut',data:{labels:channels.map(x=>x.label),datasets:[{data:channels.map(x=>Number(x.value)),backgroundColor:['#14b8a6','#2dd4bf','#5eead4','#99f6e4','#0d9488','#5f8f89']}]},options:{responsive:true,plugins:{legend:{position:'bottom',labels:{font:{size:10}}}}}});
  productChart=new Chart($('productChart'),{type:'doughnut',data:{labels:products.map(x=>x.label),datasets:[{data:products.map(x=>Number(x.value)),backgroundColor:['#14b8a6','#2dd4bf','#5eead4','#99f6e4','#0d9488','#5f8f89']}]},options:{responsive:true,plugins:{legend:{position:'bottom',labels:{font:{size:10}}}}}});
}

async function loadMonthly(){
  const year=$('performanceYear').value||new Date().getFullYear();
  try{
    const res=await fetch(API+'?action=monthly&year='+encodeURIComponent(year));const j=await res.json();if(!j.success)throw new Error(j.message);
    const a=j.annual; $('annualSales').textContent=money(a.sales);$('annualOrders').textContent=Number(a.orders).toLocaleString();$('bestMonth').textContent=a.best_month;$('bestMonthValue').textContent=money(a.best_sales);$('avgMonthlySales').textContent=money(a.average_monthly);
    renderMonthlyChart(j.months);renderMonthlyTable(j.months);
  }catch(e){alert('Could not load monthly performance.\n\n'+e.message)}
}
function renderMonthlyChart(months){
  if(monthlyChart)monthlyChart.destroy();
  monthlyChart=new Chart($('monthlySalesChart'),{type:'bar',data:{labels:months.map(m=>m.month_name.slice(0,3)),datasets:[{label:'Sales',data:months.map(m=>Number(m.sales)),backgroundColor:'#14b8a6',borderRadius:3}]},options:{responsive:true,plugins:{legend:{display:false},tooltip:{callbacks:{label:c=>money(c.raw)}}},scales:{y:{beginAtZero:true,ticks:{callback:v=>'RM '+v}},x:{grid:{display:false}}}}});
}
function growthHTML(g){if(g===null||g===undefined)return '<span class="growth neutral">—</span>';const n=Number(g);return `<span class="growth ${n>0?'up':n<0?'down':'neutral'}">${n>0?'▲ ':n<0?'▼ ':''}${Math.abs(n).toFixed(1)}%</span>`}
function renderMonthlyTable(months){
  const max=Math.max(...months.map(m=>Number(m.sales)),1);
  $('monthlyBody').innerHTML=months.map(m=>`<tr><td><b>${m.month_name}</b></td><td>${money(m.sales)}</td><td>${Number(m.orders).toLocaleString()}</td><td>${money(m.expenses)}</td><td><b>${money(m.profit)}</b></td><td>${growthHTML(m.growth)}</td><td><div class="performance-cell"><div class="mini-bar"><span style="width:${Number(m.sales)/max*100}%"></span></div><small>${max?Math.round(Number(m.sales)/max*100):0}%</small></div></td></tr>`).join('');
}

function openModal(record=null){$('modal').classList.add('show');$('modalTitle').textContent=record?'Edit Sale':'Add Sale';$('saleId').value=record?.id||'';$('saleDate').value=record?.sale_date||new Date().toISOString().slice(0,10);$('product').value=record?.product||'';$('channel').value=record?.channel||'Website';$('orders').value=record?.orders||1;$('salesAmount').value=record?.sales_amount||''}
function closeModal(){$('modal').classList.remove('show')}
async function saveSale(e){e.preventDefault();const id=$('saleId').value;const body={id:id||undefined,sale_date:$('saleDate').value,product:$('product').value,channel:$('channel').value,orders:$('orders').value,sales_amount:$('salesAmount').value};const res=await fetch(API,{method:id?'PUT':'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(body)});const j=await res.json();if(!j.success)return alert(j.message);closeModal();loadData();loadMonthly()}
window.editSale=id=>{const r=rows.find(x=>x.id==id);if(r)openModal(r)};
window.deleteSale=async id=>{if(!confirm('Delete this sale record?'))return;const r=await fetch(API,{method:'DELETE',headers:{'Content-Type':'application/json'},body:JSON.stringify({id})});const j=await r.json();if(!j.success)alert(j.message);else{loadData();loadMonthly()}};
function exportCSV(){if(!rows.length)return alert('There are no records to export.');const header=['Date','Product','Channel','Orders','Sales'];const data=rows.map(r=>[formatDateDMY(r.sale_date),r.product,r.channel,r.orders,r.sales_amount]);const csv=[header,...data].map(a=>a.map(v=>'"'+String(v).replaceAll('"','""')+'"').join(',')).join('\n');const blob=new Blob([csv],{type:'text/csv;charset=utf-8'});const a=document.createElement('a');a.href=URL.createObjectURL(blob);a.download='eniaga-create-sales-report.csv';a.click();URL.revokeObjectURL(a.href)}

function openExpenseModal(){$('expenseModal').classList.add('show');$('expenseDate').value=new Date().toISOString().slice(0,10);$('expenseCategory').value='Stock / Inventory';$('expenseDescription').value='';$('expenseAmount').value=''}
function closeExpenseModal(){$('expenseModal').classList.remove('show')}
async function saveExpense(e){e.preventDefault();const body={expense_date:$('expenseDate').value,category:$('expenseCategory').value,description:$('expenseDescription').value,amount:$('expenseAmount').value};try{const res=await fetch(EXPENSE_API,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(body)});const j=await res.json();if(!j.success)return alert(j.message);closeExpenseModal();loadData();loadMonthly()}catch(err){alert('Could not save expense.\n\n'+err.message)}}

function switchTab(tab){
  const valid=['sales','profile','monthly'];
  if(!valid.includes(tab)) tab='sales';
  document.querySelectorAll('.tab-panel').forEach(panel=>{
    const active=panel.dataset.panel===tab;
    panel.classList.toggle('active',active);
    panel.style.display=active?'block':'none';
  });
  document.querySelectorAll('.tab').forEach(button=>{
    const active=button.dataset.tab===tab;
    button.classList.toggle('active',active);
    button.setAttribute('aria-selected',active?'true':'false');
  });
  $('crumb').textContent='MY BUSINESS / '+(tab==='sales'?'SALES REPORTS':tab==='profile'?'BUSINESS PROFILE':'MONTHLY PERFORMANCE');
  $('addBtn').style.display=tab==='sales'?'inline-block':'none';$('addExpenseBtn').style.display=tab==='sales'?'inline-block':'none';
  if(tab==='monthly') loadMonthly();
  history.replaceState(null,'','#'+tab);
}

function hasSSMProof(){
  return !!($('ssmProof').files[0] || localStorage.getItem('eusahaSSMProofName'));
}
function hasTyphoidProof(){
  return !!($('typhoidProof').files[0] || localStorage.getItem('eusahaTyphoidProofName'));
}
function toggleTyphoidRequirement(){
  const isFood=$('mainCategory').value==='Food & Beverages';
  const field=$('typhoidField'), input=$('typhoidProof');
  field.hidden=!isFood;
  input.required=isFood && !localStorage.getItem('eusahaTyphoidProofName');
  $('typhoidSaved').textContent=isFood&&localStorage.getItem('eusahaTyphoidProofName')?'Proof uploaded: '+localStorage.getItem('eusahaTyphoidProofName'):'';
  $('ssmSaved').textContent=localStorage.getItem('eusahaSSMProofName')?'SSM proof uploaded: '+localStorage.getItem('eusahaSSMProofName'):'';
  const btn=$('profileContinueBtn');
  if(btn){
    const missingSSM=!hasSSMProof();
    const missingTyphoid=isFood && !hasTyphoidProof();
    btn.disabled=missingSSM || missingTyphoid;
    btn.title=missingSSM?'Upload your SSM registration proof before continuing.':(missingTyphoid?'Upload proof of typhoid vaccination before continuing.':'');
  }
}
function loadProfile(){
  const p=JSON.parse(localStorage.getItem('eusahaProfile')||'null');
  if(p) Object.keys(p).forEach(k=>{if($(k))$(k).value=p[k]});
  toggleTyphoidRequirement();
}

const summaryFilterIds=['summaryDateFrom','summaryDateTo','summaryProduct','summaryChannel','summaryMinSales','summaryMaxSales'];
function setSummaryFilterPopup(open){
  const overlay=$('summaryFilterOverlay'); if(!overlay)return;
  overlay.hidden=!open;
  document.body.classList.toggle('filter-popup-open',open);
}
function updateSummaryFilterBadge(){
  const count=summaryFilterIds.filter(id=>$(id)&&$(id).value!=='').length;
  const badge=$('summaryFilterBadge'); if(!badge)return;
  badge.textContent=count; badge.hidden=count===0;
}
if($('openSummaryFilters'))$('openSummaryFilters').onclick=()=>setSummaryFilterPopup(true);
if($('closeSummaryFilters'))$('closeSummaryFilters').onclick=()=>setSummaryFilterPopup(false);
if($('summaryFilterOverlay'))$('summaryFilterOverlay').addEventListener('click',e=>{if(e.target===$('summaryFilterOverlay'))setSummaryFilterPopup(false);});
document.addEventListener('keydown',e=>{if(e.key==='Escape')setSummaryFilterPopup(false);});
if($('applySummaryFilters'))$('applySummaryFilters').onclick=()=>{currentSalesPage=1;renderSalesSummary();updateSummaryFilterBadge();setSummaryFilterPopup(false);};
if($('clearSummaryFilters'))$('clearSummaryFilters').onclick=()=>{summaryFilterIds.forEach(id=>{if($(id))$(id).value='';});currentSalesPage=1;renderSalesSummary();updateSummaryFilterBadge();};
$('addBtn').onclick=()=>openModal();$('addExpenseBtn').onclick=openExpenseModal;$('closeModal').onclick=closeModal;$('cancelBtn').onclick=closeModal;$('saleForm').onsubmit=saveSale;$('closeExpenseModal').onclick=closeExpenseModal;$('cancelExpenseBtn').onclick=closeExpenseModal;$('expenseForm').onsubmit=saveExpense;$('refreshBtn').onclick=loadData;$('exportBtn').onclick=exportCSV;$('year').onchange=loadData;$('month').onchange=loadData;$('range').onchange=loadData;$('search').addEventListener('input',()=>{clearTimeout(window.__searchTimer);window.__searchTimer=setTimeout(loadData,250)});$('performanceYear').onchange=loadMonthly;$('monthlyRefresh').onclick=loadMonthly;
$('ssmProof').addEventListener('change',()=>{
  const file=$('ssmProof').files[0];
  if(file){
    const allowed=['image/jpeg','image/png'];
    if(!allowed.includes(file.type)){alert('SSM registration proof must be a JPG, JPEG or PNG image.');$('ssmProof').value='';}
    else if(file.size>5*1024*1024){alert('SSM registration proof must be 5 MB or smaller.');$('ssmProof').value='';}
  }
  toggleTyphoidRequirement();
});
$('mainCategory').addEventListener('change',()=>{localStorage.removeItem('eusahaTyphoidProofName');$('typhoidProof').value='';toggleTyphoidRequirement()});
$('typhoidProof').addEventListener('change',()=>{
  const file=$('typhoidProof').files[0];
  if(file){
    const allowed=['application/pdf','image/jpeg','image/png'];
    if(!allowed.includes(file.type)){alert('Typhoid proof must be a PDF, JPG, JPEG or PNG file.');$('typhoidProof').value='';}
    else if(file.size>5*1024*1024){alert('Typhoid proof must be 5 MB or smaller.');$('typhoidProof').value='';}
  }
  toggleTyphoidRequirement();
});
$('profileForm').onsubmit=e=>{
  e.preventDefault();
  const isFood=$('mainCategory').value==='Food & Beverages', file=$('typhoidProof').files[0], ssmFile=$('ssmProof').files[0];
  if(!ssmFile && !localStorage.getItem('eusahaSSMProofName')){alert('Please upload a photo of your SSM registration before continuing.');$('ssmProof').focus();return;}
  if(ssmFile){
    const ssmAllowed=['image/jpeg','image/png'];
    if(!ssmAllowed.includes(ssmFile.type)){alert('SSM registration proof must be a JPG, JPEG or PNG image.');return;}
    if(ssmFile.size>5*1024*1024){alert('SSM registration proof must be 5 MB or smaller.');return;}
    localStorage.setItem('eusahaSSMProofName',ssmFile.name);
  }
  if(isFood && !file && !localStorage.getItem('eusahaTyphoidProofName')){alert('Food & Beverages businesses must upload proof of typhoid vaccination.');$('typhoidProof').focus();return;}
  if(file){
    const allowed=['application/pdf','image/jpeg','image/png'];
    if(!allowed.includes(file.type)){alert('Typhoid proof must be a PDF, JPG, JPEG or PNG file.');return;}
    if(file.size>5*1024*1024){alert('Typhoid proof must be 5 MB or smaller.');return;}
    localStorage.setItem('eusahaTyphoidProofName',file.name);
  }
  const ids=['businessName','registrationNo','businessType','mainCategory','businessRole','subCategory','facebookPage','instagramPage','marketplace','businessWebsite','exportExperience','websiteType','studyRelated','whatsappBusiness'];
  const p={}; ids.forEach(id=>p[id]=$(id).value);
  localStorage.setItem('eusahaProfile',JSON.stringify(p));
  toggleTyphoidRequirement();
  $('profileStatus').textContent='Profile saved successfully.';
  $('profileStatus').textContent='Profile saved successfully. Continuing to Monthly Performance...';
  setTimeout(()=>{ $('profileStatus').textContent=''; switchTab('monthly'); },700);
};
document.querySelectorAll('.tab').forEach(b=>b.addEventListener('click',function(e){
  e.preventDefault();
  // Navigation is always allowed. Required profile documents only control Save & Continue.
  switchTab(this.dataset.tab);
}));
window.onclick=e=>{if(e.target===$('modal'))closeModal();if(e.target===$('expenseModal'))closeExpenseModal()};

loadProfile();loadData();
const initial=location.hash.replace('#','');if(['sales','profile','monthly'].includes(initial))switchTab(initial);

// Ensure the page always starts on exactly one tab.
switchTab(['sales','profile','monthly'].includes(location.hash.replace('#','')) ? location.hash.replace('#','') : 'sales');
