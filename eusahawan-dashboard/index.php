<?php
function h($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="0">
<title>eNiaga@create | Business Performance Portal</title>
<link rel="stylesheet" href="dashboard.css?v=profile3">
<style>

.tab-panel { display:none !important; }
.tab-panel.active { display:block !important; }
</style>
</head>
<body>
<header class="topbar">
  <div class="brand create-brand"><img class="create-logo" src="create-logo.jpg" alt="CREATE - Centre for Enterprise and Technopreneurship Development"><div class="eniaga-brand-text"><strong>eNiaga@create</strong><span>Business Performance Portal</span></div></div>
  <nav>
    <a>HOME</a><a class="active">MY BUSINESS</a><a>MORE</a>
  </nav>
  <div class="user"><div class="avatar">U</div><div><b>Business Owner</b><small>My Account ▾</small></div></div>
</header>

<main class="container">
  <div class="page-head">
    <div><div class="crumb" id="crumb">MY BUSINESS / SALES REPORTS</div><h1>My Business</h1><p>Manage your sales, business profile and monthly performance.</p></div>
    <div class="page-actions"><button class="primary" id="addBtn">＋ Add Sale</button><button class="secondary expense-btn" id="addExpenseBtn">＋ Add Expense</button></div>
  </div>

  <div class="tabs" id="businessTabs" role="tablist" aria-label="My Business sections">
    <button type="button" class="tab active" data-tab="sales" role="tab" aria-selected="true">Sales Reports</button>
    <button type="button" class="tab" data-tab="profile" role="tab" aria-selected="false">Business Profile</button>
    <button type="button" class="tab" data-tab="monthly" role="tab" aria-selected="false">Monthly Performance</button>
  </div>

  <!-- SALES REPORTS -->
  <section class="tab-panel active" id="salesPanel" data-panel="sales">
    <section class="filters card">
      <div><label>YEAR</label><select id="year"><option value="">All Years</option><option value="2026">2026</option><option value="2025">2025</option></select></div>
      <div><label>MONTH</label><select id="month"><option value="">All Months</option><option value="1">January</option><option value="2">February</option><option value="3">March</option><option value="4">April</option><option value="5">May</option><option value="6">June</option><option value="7">July</option><option value="8">August</option><option value="9">September</option><option value="10">October</option><option value="11">November</option><option value="12">December</option></select></div>
      <div class="search"><label>SEARCH</label><input id="search" placeholder="Product or channel..."></div>
      <button class="secondary" id="refreshBtn">↻ Refresh</button>
    </section>
    <section class="kpis">
      <div class="kpi"><span>Total Sales</span><strong id="totalSales">RM 0.00</strong><small>Gross sales</small></div>
      <div class="kpi"><span>Total Orders</span><strong id="totalOrders">0</strong><small>Completed orders</small></div>
      <div class="kpi"><span>Total Expenses</span><strong id="totalExpenses">RM 0.00</strong><small>Recorded business expenses</small></div>
      <div class="kpi"><span>Total Profit</span><strong id="totalProfit">RM 0.00</strong><small>Sales − expenses</small></div>
    </section>
    <section class="charts-grid">
      <div class="card chart-card sales-performance-chart"><div class="card-title"><div><h2>Sales Performance</h2><p>Sales trend for the selected period</p></div><select id="range"><option>Daily</option><option>Weekly</option><option>Monthly</option></select></div><canvas id="salesChart" height="230"></canvas></div>
      <div class="card chart-card"><div class="card-title"><div><h2>Sales by Channel</h2><p>Where your customers buy</p></div></div><canvas id="channelChart" height="230"></canvas></div>
      <div class="card chart-card"><div class="card-title"><div><h2>Sales by Product</h2><p>Sales contribution by product</p></div></div><canvas id="productChart" height="230"></canvas></div>
    </section>
    <section class="grid-two bottom">
      <div class="card sales-summary-card"><div class="card-title"><div><h2>Sales Summary</h2><p>Latest transactions</p></div><div class="summary-actions"><button class="filter-trigger" id="openSummaryFilters" type="button">Filter <span id="summaryFilterBadge" class="filter-badge" hidden>0</span></button><button class="linkbtn" id="exportBtn">Export CSV</button></div></div>
        <div class="summary-filter-overlay" id="summaryFilterOverlay" hidden>
          <div class="summary-filter-modal" role="dialog" aria-modal="true" aria-labelledby="summaryFilterTitle">
            <div class="summary-filter-head"><div><h3 id="summaryFilterTitle">Filter Sales Summary</h3><p>Choose one or more filters, then apply.</p></div><button type="button" class="summary-filter-close" id="closeSummaryFilters" aria-label="Close">&times;</button></div>
        <div class="summary-filters">
          <div><label>FROM DATE</label><input type="date" id="summaryDateFrom"></div>
          <div><label>TO DATE</label><input type="date" id="summaryDateTo"></div>
          <div><label>PRODUCT</label><select id="summaryProduct"><option value="">All Products</option></select></div>
          <div><label>CHANNEL</label><select id="summaryChannel"><option value="">All Channels</option></select></div>
          <div><label>MIN SALES (RM)</label><input type="number" id="summaryMinSales" min="0" step="0.01" placeholder="0.00"></div>
          <div><label>MAX SALES (RM)</label><input type="number" id="summaryMaxSales" min="0" step="0.01" placeholder="No limit"></div>
        </div>
            <div class="summary-filter-footer"><button type="button" class="secondary summary-clear" id="clearSummaryFilters">Clear</button><button type="button" class="primary" id="applySummaryFilters">Apply Filters</button></div>
          </div>
        </div>
        <div class="summary-filter-status" id="summaryFilterStatus"></div>
        <div class="table-wrap"><table><thead><tr><th>Date</th><th>Product</th><th>Channel</th><th>Orders</th><th>Sales</th><th></th></tr></thead><tbody id="salesBody"></tbody></table></div><div class="pagination" id="salesPagination"></div></div>
      <div class="card"><div class="card-title"><div><h2>Top Products</h2><p>Best performers</p></div></div><div id="products" class="products"></div></div>
    </section>
  </section>

  <!-- BUSINESS PROFILE -->
  <section class="tab-panel" id="profilePanel" data-panel="profile">
    <div class="card profile-card">
      <div class="section-heading"><div><div class="section-kicker">MY BUSINESS / BUSINESS PROFILE</div><h2>Business Profile</h2><p>Complete your business information. Fields marked * are required.</p></div></div>
      <form id="profileForm">
        <div class="profile-grid numbered-profile">
          <div class="profile-field"><label><span class="field-no">1</span>Company Name <em>*</em></label><input id="businessName" placeholder="Business Name" required></div>
          <div class="profile-field"><label><span class="field-no">2</span>Company Registration No.</label><input id="registrationNo" placeholder="Company Registration No."></div>
          <div class="profile-field"><label><span class="field-no">3</span>Type Of Business <em>*</em></label><select id="businessType" required><option value="">Select type of business</option><option>Online Business</option><option>Physical Store</option><option>Online & Physical</option><option>Service Business</option></select></div>
          <div class="profile-field"><label><span class="field-no">4</span>Business Category <em>*</em></label><select id="mainCategory" required><option value="">Select business category</option><option>Food & Beverages</option><option>Beauty & Personal Care</option><option>Fashion & Apparel</option><option>Health & Wellness</option><option>Electronics & Technology</option><option>Home & Living</option><option>Services</option><option>Other</option></select></div>
          <div class="profile-field"><label><span class="field-no">5</span>Business Role <em>*</em></label><select id="businessRole" required><option value="">Select business role</option><option>Owner</option><option>Co-Owner</option><option>Manager</option><option>Representative</option></select></div>
          <div class="profile-field"><label><span class="field-no">6</span>Business Sub-Category <em>*</em></label><input id="subCategory" placeholder="e.g. Bakery, Clothing, Web Services" required></div>
          <div class="profile-field"><label><span class="field-no">7</span>Facebook Page</label><input id="facebookPage" placeholder="Facebook Page"></div>
          <div class="profile-field"><label><span class="field-no">8</span>Business Instagram Page</label><input id="instagramPage" placeholder="www.instagram.com/profile"></div>
          <div class="profile-field"><label><span class="field-no">9</span>Marketplace</label><select id="marketplace"><option value="">Select market</option><option>Shopee</option><option>TikTok Shop</option><option>Lazada</option><option>Own Website</option><option>Physical Only</option><option>Other</option></select></div>
          <div class="profile-field"><label><span class="field-no">10</span>Business Website</label><input id="businessWebsite" placeholder="www.website.com"></div>
          <div class="profile-field"><label><span class="field-no">11</span>Experience in International Export</label><select id="exportExperience"><option value="">Select an option</option><option>No experience</option><option>Less than 1 year</option><option>1–3 years</option><option>More than 3 years</option></select></div>
          <div class="profile-field"><label><span class="field-no">12</span>Type Of Website</label><select id="websiteType"><option value="">Select website type</option><option>E-Commerce</option><option>Corporate / Business</option><option>Marketplace Store</option><option>Social Commerce</option><option>None</option></select></div>
          <div class="profile-field"><label><span class="field-no">13</span>Business Related to Your Study Field</label><select id="studyRelated"><option value="">Select an option</option><option>Yes</option><option>No</option><option>Partially</option></select></div>
          <div class="profile-field"><label><span class="field-no">14</span>WhatsApp for Business</label><input id="whatsappBusiness" placeholder="WhatsApp / Business number"></div>
          <div class="profile-field document-upload-field" id="ssmField"><label><span class="field-no">15</span>SSM Registration Proof <em>*</em></label><input id="ssmProof" type="file" accept=".jpg,.jpeg,.png,image/jpeg,image/png"><small>Required for all businesses. Upload a clear JPG, JPEG or PNG photo of your SSM registration (max 5 MB).</small><div id="ssmSaved" class="file-note"></div></div>
          <div class="profile-field typhoid-field" id="typhoidField" hidden><label><span class="field-no">16</span>Proof of Typhoid Vaccination <em>*</em></label><input id="typhoidProof" type="file" accept=".pdf,.jpg,.jpeg,.png"><small>Required for Food & Beverages businesses. Accepted: PDF, JPG, JPEG, PNG (max 5 MB).</small><div id="typhoidSaved" class="file-note"></div></div>
        </div>
        <div class="profile-actions"><span id="profileStatus"></span><button class="primary" id="profileContinueBtn" type="submit">Save & Continue</button></div>
      </form>
    </div>
  </section>

  <!-- MONTHLY PERFORMANCE -->
  <section class="tab-panel" id="monthlyPanel" data-panel="monthly">
    <div class="monthly-toolbar card">
      <div><label>PERFORMANCE YEAR</label><select id="performanceYear"><option value="2026">2026</option><option value="2025">2025</option></select></div>
      <button class="secondary" id="monthlyRefresh">↻ Refresh</button>
    </div>
    <section class="performance-kpis">
      <div class="performance-kpi"><span>Annual Sales</span><strong id="annualSales">RM 0.00</strong><small id="annualSalesSub">Gross sales</small></div>
      <div class="performance-kpi"><span>Annual Orders</span><strong id="annualOrders">0</strong><small>Completed orders</small></div>
      <div class="performance-kpi"><span>Best Month</span><strong id="bestMonth">—</strong><small id="bestMonthValue">RM 0.00</small></div>
      <div class="performance-kpi"><span>Average Monthly Sales</span><strong id="avgMonthlySales">RM 0.00</strong><small>Across 12 months</small></div>
    </section>
    <div class="card chart-card monthly-chart-card"><div class="card-title"><div><h2>Monthly Sales Performance</h2><p>Compare your sales from January to December</p></div></div><canvas id="monthlySalesChart" height="110"></canvas></div>
    <div class="card monthly-table-card"><div class="card-title"><div><h2>Monthly Performance Summary</h2><p>Sales, orders, expenses, profit and growth</p></div></div><div class="table-wrap"><table class="monthly-table"><thead><tr><th>Month</th><th>Sales</th><th>Orders</th><th>Expenses</th><th>Profit</th><th>Growth</th><th>Performance</th></tr></thead><tbody id="monthlyBody"></tbody></table></div></div>
  </section>
</main>

<div class="modal" id="modal"><div class="modal-box"><div class="modal-head"><h2 id="modalTitle">Add Sale</h2><button id="closeModal">×</button></div><form id="saleForm"><input type="hidden" id="saleId"><div class="form-grid"><div><label>Date</label><input type="date" id="saleDate" required></div><div><label>Product</label><input id="product" required placeholder="e.g. Rose Perfume"></div><div><label>Channel</label><select id="channel"><option>Website</option><option>Shopee</option><option>TikTok Shop</option><option>Instagram</option><option>Facebook</option><option>Other</option></select></div><div><label>Orders</label><input type="number" id="orders" min="1" value="1" required></div><div><label>Sales Amount (RM)</label><input type="number" id="salesAmount" min="0" step="0.01" required></div></div><div class="modal-actions"><button type="button" class="secondary" id="cancelBtn">Cancel</button><button class="primary" type="submit">Save Sale</button></div></form></div></div>

<div class="modal" id="expenseModal"><div class="modal-box"><div class="modal-head"><h2>Add Expense</h2><button id="closeExpenseModal">×</button></div><form id="expenseForm"><div class="form-grid"><div><label>Date</label><input type="date" id="expenseDate" required></div><div><label>Expense Category</label><select id="expenseCategory"><option>Stock / Inventory</option><option>Marketing</option><option>Delivery / Shipping</option><option>Utilities</option><option>Equipment</option><option>Office</option><option>Other</option></select></div><div><label>Description</label><input id="expenseDescription" required placeholder="e.g. Packaging materials"></div><div><label>Expense Amount (RM)</label><input type="number" id="expenseAmount" min="0" step="0.01" required></div></div><div class="modal-actions"><button type="button" class="secondary" id="cancelExpenseBtn">Cancel</button><button class="primary" type="submit">Save Expense</button></div></form></div></div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script><script src="dashboard.js?v=profile3"></script>
</body></html>
