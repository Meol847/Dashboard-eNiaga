USE eusaha_dashboard;

ALTER TABLE sales
ADD COLUMN cost_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00 AFTER refund_amount;

-- Demo cost assumption for existing sample records: 60% of gross sales.
-- Replace with actual costs when available.
UPDATE sales SET cost_amount = ROUND(sales_amount * 0.60, 2);
