CREATE DATABASE IF NOT EXISTS eusaha_dashboard CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE eusaha_dashboard;

DROP TABLE IF EXISTS sales;
CREATE TABLE sales (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sale_date DATE NOT NULL,
    product VARCHAR(150) NOT NULL,
    channel VARCHAR(50) NOT NULL,
    orders INT UNSIGNED NOT NULL DEFAULT 1,
    sales_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    refund_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    cost_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_sale_date (sale_date),
    INDEX idx_channel (channel),
    INDEX idx_product (product)
);

INSERT INTO sales (sale_date, product, channel, orders, sales_amount, refund_amount) VALUES
('2026-01-05','Rose Perfume','Website',7,560.00,0.00),
('2026-01-12','Gift Set','Shopee',5,375.00,20.00),
('2026-01-24','Body Mist','TikTok Shop',8,360.00,0.00),
('2026-02-03','Rose Perfume','Website',9,720.00,0.00),
('2026-02-14','Gift Set','Instagram',7,525.00,25.00),
('2026-02-25','Body Mist','Shopee',10,450.00,0.00),
('2026-03-06','Rose Perfume','TikTok Shop',11,880.00,40.00),
('2026-03-15','Gift Set','Website',8,600.00,0.00),
('2026-03-27','Body Mist','Instagram',9,405.00,0.00),
('2026-04-04','Rose Perfume','Website',12,960.00,50.00),
('2026-04-18','Gift Set','Shopee',9,675.00,0.00),
('2026-04-26','Body Mist','TikTok Shop',11,495.00,25.00),
('2026-05-07','Rose Perfume','Website',10,800.00,0.00),
('2026-05-16','Gift Set','Instagram',12,900.00,50.00),
('2026-05-28','Body Mist','Shopee',8,360.00,0.00),
('2026-06-02','Rose Perfume','TikTok Shop',13,1040.00,40.00),
('2026-06-14','Gift Set','Website',10,750.00,0.00),
('2026-06-25','Body Mist','Instagram',12,540.00,20.00),
('2026-07-05','Rose Perfume','Website',14,1120.00,60.00),
('2026-07-17','Gift Set','Shopee',11,825.00,0.00),
('2026-07-29','Body Mist','TikTok Shop',13,585.00,25.00),
('2026-08-07','Gift Set','Website',12,900.00,0.00),
('2026-08-18','Body Mist','Shopee',15,675.00,20.00),
('2026-08-29','Rose Perfume','TikTok Shop',13,1040.00,40.00),
('2026-09-01','Rose Perfume','Website',8,640.00,0.00),
('2026-09-02','Rose Perfume','Shopee',6,480.00,0.00),
('2026-09-03','Gift Set','TikTok Shop',5,375.00,25.00),
('2026-09-04','Body Mist','Website',7,315.00,0.00),
('2026-09-05','Gift Set','Shopee',9,675.00,50.00),
('2026-09-06','Rose Perfume','TikTok Shop',10,800.00,0.00),
('2026-09-07','Body Mist','Instagram',4,180.00,0.00),
('2026-09-08','Rose Perfume','Website',12,960.00,80.00),
('2026-10-06','Rose Perfume','Website',15,1200.00,60.00),
('2026-10-15','Gift Set','Shopee',12,900.00,0.00),
('2026-10-26','Body Mist','TikTok Shop',14,630.00,30.00),
('2026-11-04','Rose Perfume','TikTok Shop',16,1280.00,80.00),
('2026-11-16','Gift Set','Website',13,975.00,0.00),
('2026-11-27','Body Mist','Instagram',15,675.00,25.00),
('2026-12-03','Rose Perfume','Website',18,1440.00,70.00),
('2026-12-12','Gift Set','Shopee',15,1125.00,50.00),
('2026-12-20','Body Mist','TikTok Shop',17,765.00,30.00);

-- Dummy sales records for 2025 (3 records per month)
INSERT INTO sales (sale_date, product, channel, orders, sales_amount, refund_amount) VALUES
('2025-01-06','Rose Perfume','Website',5,400.00,0.00),
('2025-01-15','Gift Set','Shopee',4,300.00,15.00),
('2025-01-25','Body Mist','Instagram',6,270.00,0.00),
('2025-02-04','Rose Perfume','Website',6,480.00,20.00),
('2025-02-13','Gift Set','Shopee',5,375.00,0.00),
('2025-02-24','Body Mist','TikTok Shop',7,315.00,10.00),
('2025-03-05','Rose Perfume','TikTok Shop',7,560.00,0.00),
('2025-03-16','Gift Set','Website',6,450.00,25.00),
('2025-03-28','Body Mist','Shopee',8,360.00,0.00),
('2025-04-03','Rose Perfume','Website',8,640.00,30.00),
('2025-04-14','Gift Set','Instagram',7,525.00,0.00),
('2025-04-26','Body Mist','TikTok Shop',9,405.00,15.00),
('2025-05-07','Rose Perfume','Website',9,720.00,20.00),
('2025-05-17','Gift Set','Shopee',8,600.00,30.00),
('2025-05-29','Body Mist','Instagram',10,450.00,0.00),
('2025-06-04','Rose Perfume','TikTok Shop',10,800.00,40.00),
('2025-06-15','Gift Set','Website',9,675.00,0.00),
('2025-06-27','Body Mist','Shopee',11,495.00,20.00),
('2025-07-06','Rose Perfume','Website',11,880.00,35.00),
('2025-07-18','Gift Set','Shopee',10,750.00,0.00),
('2025-07-29','Body Mist','TikTok Shop',12,540.00,25.00),
('2025-08-05','Rose Perfume','Website',12,960.00,50.00),
('2025-08-16','Gift Set','Instagram',11,825.00,0.00),
('2025-08-27','Body Mist','Shopee',13,585.00,20.00),
('2025-09-04','Rose Perfume','TikTok Shop',10,800.00,30.00),
('2025-09-15','Gift Set','Website',9,675.00,0.00),
('2025-09-26','Body Mist','Instagram',11,495.00,15.00),
('2025-10-06','Rose Perfume','Website',13,1040.00,40.00),
('2025-10-17','Gift Set','Shopee',12,900.00,25.00),
('2025-10-28','Body Mist','TikTok Shop',14,630.00,0.00),
('2025-11-05','Rose Perfume','TikTok Shop',14,1120.00,60.00),
('2025-11-16','Gift Set','Website',13,975.00,0.00),
('2025-11-27','Body Mist','Instagram',15,675.00,30.00),
('2025-12-03','Rose Perfume','Website',16,1280.00,50.00),
('2025-12-12','Gift Set','Shopee',14,1050.00,35.00),
('2025-12-21','Body Mist','TikTok Shop',16,720.00,20.00);


-- Demo cost assumption for the sample data: 60% of gross sales.
-- Replace these values with actual business costs when available.
UPDATE sales SET cost_amount = ROUND(sales_amount * 0.60, 2);


-- Business expenses used by the Total Expenses KPI and profit calculation.
CREATE TABLE IF NOT EXISTS expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    expense_date DATE NOT NULL,
    category VARCHAR(100) NOT NULL,
    description VARCHAR(255) NOT NULL,
    amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Sample expense records for the demo dashboard.
INSERT INTO expenses (expense_date, category, description, amount) VALUES
('2025-01-10','Stock / Inventory','January stock purchase',420.00),
('2025-04-15','Marketing','Online advertising',180.00),
('2025-08-12','Delivery / Shipping','Courier and delivery costs',145.00),
('2025-12-05','Office','Packaging and office supplies',120.00),
('2026-01-05','Stock / Inventory','January stock purchase',390.00),
('2026-01-12','Marketing','Social media promotion',150.00),
('2026-01-24','Delivery / Shipping','Courier fees',95.00),
('2026-02-10','Equipment','Photography accessories',210.00),
('2026-03-08','Office','Packaging materials',85.00),
('2026-04-18','Marketing','Campaign expense',175.00),
('2026-05-20','Utilities','Internet and utilities',130.00),
('2026-06-14','Stock / Inventory','Mid-year stock purchase',460.00),
('2026-07-09','Delivery / Shipping','Delivery charges',115.00),
('2026-08-16','Marketing','Promotional materials',160.00),
('2026-09-03','Office','Office supplies',90.00);
