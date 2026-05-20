<?php 

namespace App\Services\AiChat;

 class MetricsDicionary {
        /**
     * 
     * helper method that provide all repport metrics with description 
     * to AI chatbot for give exact and correct answers 
     */

 public function dictionary(): string{

        
 
 return "
        
 
 ERP METRICS DICTIONARY (BUSINESS DEFINITIONS)

==================================================
SALES MODULE
==================================================

sales_volume_by_region:
- region: Geographical grouping based on client address
- order_count: Number of sales orders in the region
- sales_total: Total revenue generated in the region

product_performance:
- quantity_sold: Total number of units sold per product
- sales_total: Total revenue generated per product

sales_trends:
- report_date: Date of sales aggregation
- order_count: Number of sales per day
- sales_total: Total sales revenue per day


==================================================
FINANCIAL MODULE
==================================================

revenue: Total sales revenue before refunds
refunds: Total refunded amount
net_revenue: Revenue after subtracting refunds
confirmed_purchases: Total confirmed supplier purchases
estimated_gross_profit: Revenue minus refunds and confirmed purchases

customer_payments: Total cash received from customers
net_cash_flow: Cash inflow minus cash outflows

cash: Available cash after inflows and outflows
accounts_receivable: Outstanding unpaid customer balances
inventory: Total inventory value
pending_purchase_orders: Unpaid supplier orders
estimated_retained_earnings: Estimated company equity value


==================================================
INVENTORY MODULE
==================================================

total_products: Total number of products in system
total_units: Sum of all product stock quantities
out_of_stock_products: Products with zero stock
low_stock_products: Products below minimum stock threshold

inventory_value: Total monetary value of all stock
turnover_rate: Speed at which product stock is sold
average_cost: Average purchase cost per unit
inventory_valuation: Stock value per product

movement_throughput: Ratio of stock out vs stock in movements
active_sku_ratio: Percentage of products with stock > 0
low_stock_ratio: Percentage of products below minimum stock


==================================================
DEVIS MODULE
==================================================

total_devis: Total number of quotations in period
status_distribution: Breakdown of quotations by status

financial_aggregations:
- draft_total: Value of draft quotations
- sent_total: Value of sent quotations
- accepted_total: Value of accepted quotations
- rejected_total: Value of rejected quotations
- expired_total: Value of expired quotations

conversion_metrics:
- acceptance_rate: Percentage of accepted quotations

pipeline_metrics:
- sent_pending: Sent quotations waiting response
- sent_total_value: Total value of sent quotations

lost_opportunity_metrics:
- rejected_count: Number of rejected quotations
- expired_count: Number of expired quotations
- lost_total: Total lost revenue from rejected and expired quotations

aging_metrics:
- sent_over_7_days: Sent quotations older than 7 days
- sent_over_30_days: Sent quotations older than 30 days


==================================================
PURCHASING MODULE
==================================================

purchase_count: Number of purchases per supplier
confirmed_spend: Total confirmed supplier spending
pending_spend: Total pending purchase value
average_order_value: Average purchase order value

total_spend: Total procurement expenditure
by_category: Spend grouped by product category
top_products: Highest spending products in procurement
";
    }
 }