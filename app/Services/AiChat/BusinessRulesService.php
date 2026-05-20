<?php

namespace App\Services\AiChat;

class BusinessRulesService
{

// difine business rules to improve understanding of AI to your data 
    public function salesBusinessRules(): string
    {
        return "
SALES BUSINESS RULES

1. sales_volume_by_region + sales_trends + product_performance are related.

2. Total sales trends revenue SHOULD approximately align with regional sales totals.
If mismatch is significant:
Data inconsistency detected.

3. High sales_total with low order_count may indicate:
- large order values
- enterprise customers
Do NOT assume cause without supporting data.

4. Product performance identifies:
- top selling products
- low selling products
- revenue concentration risk.

5. Regions with:
- low sales_total
- low order_count
may indicate weak market performance.

6. Missing sales trends or empty product performance:
Reporting gap detected.

7. Never infer customer satisfaction or marketing performance unless data explicitly supports it.
";
    }

    public function inventoryBusinessRules(): string
    {
        return "
INVENTORY BUSINESS RULES

1. stock_levels + inventory_valuation + turnover_rates + warehouse_efficiency are related.

2. Products with:
- current_stock = 0
AND
- sold_quantity > 0
indicate stock depletion risk.

3. Products with:
- high current_stock
AND
- low turnover_rate
may indicate overstock risk.

4. Products with:
- low_stock_products > 0
OR
- low_stock_ratio high
indicate replenishment risk.

5. High turnover_rate generally indicates faster inventory movement.
Do NOT label as positive or negative without supporting context.

6. inventory_value SHOULD align with inventory_valuation totals.
Major mismatch:
Data inconsistency detected.

7. warehouse_efficiency metrics describe operational movement only.
Do NOT assume warehouse quality or staff performance.

8. Missing turnover data:
Reporting gap detected.
";
    }

    public function devisBusinessRules(): string
    {
        return "
DEVIS BUSINESS RULES

1. Accepted devis are automatically converted into sales.
This is a confirmed ERP rule.

2. NEVER:
- recommend converting accepted devis
- suggest improving accepted-to-sales conversion
- imply manual conversion.

3. total_devis SHOULD equal:
draft + sent + accepted + rejected + expired.
Mismatch:
Data inconsistency detected.

4. sent_pending represents active pipeline.

5. High:
- rejected_count
- expired_count
- lost_total
indicate lost revenue opportunities.

6. sent_over_7_days and sent_over_30_days indicate pipeline aging.

7. High sent aging may indicate:
- delayed customer response
- pipeline stagnation
Do NOT assume exact reason.

8. acceptance_rate reflects accepted devis ratio only.

9. accepted_total contributes to realized sales pipeline because accepted devis become sales.

10. Zero sent_total with high accepted_total may indicate:
- previously processed pipeline
Do NOT assume anomaly automatically.

11. Missing rejected or expired metrics:
Reporting gap detected.
";
    }

    public function financialBusinessRules(): string
    {
        return "
FINANCIAL BUSINESS RULES

1. Financial statements are related:
- income_statement
- balance_sheet
- cash_flow_statement
- general_ledger

2. net_revenue SHOULD equal:
revenue - refunds.

3. estimated_gross_profit SHOULD equal:
revenue - refunds - confirmed_purchases.

4. net_cash_flow SHOULD equal:
customer_payments - refunds - confirmed_purchases.

5. Negative or unusually low profitability may indicate:
- margin pressure
- expense risk
Do NOT assume cause.

6. High accounts_receivable may indicate:
- unpaid customer balances
Do NOT assume collection failure.

7. cash represents calculated liquidity only.
Do NOT assume bank balance accuracy.

8. Major mismatch between ledger and statements:
Data inconsistency detected.

9. Missing financial sections:
Reporting gap detected.
";
    }

    public function purchasingBusinessRules(): string
    {
        return "
PURCHASING BUSINESS RULES

1. supplier_performance + pending_purchase_orders + expenditure_analysis are related.

2. confirmed_spend + pending_spend represent procurement exposure.

3. High pending_spend may indicate:
- purchasing backlog
Do NOT assume operational failure.

4. average_order_value identifies purchasing behavior only.

5. total_spend SHOULD approximately align with category + top product spending.
Large mismatch:
Data inconsistency detected.

6. Heavy spend concentration in few products or categories may indicate:
- procurement dependency risk.

7. Pending purchase orders represent unconfirmed purchasing commitments.

8. Missing supplier or expenditure data:
Reporting gap detected.

9. Never evaluate supplier quality without explicit performance metrics.
";
    }

    public function globalBusinessRules(): string
    {
        return "
GLOBAL ERP ANALYSIS RULES

1. REPORT DATA is the ONLY source of truth.

2. Metrics Dictionary is NOT report data.
It is ONLY:
- metric description
- semantic reference
- naming explanation.

3. NEVER generate insights from Metrics Dictionary alone.

4. NEVER assume metric existence because it exists in dictionary.

5. First read REPORT DATA.

6. Only if metric meaning is unclear:
consult Metrics Dictionary.

7. If metric does not exist inside REPORT DATA:
treat it as missing data.

8. Never create calculations unless:
- all required metrics exist
- relationship is explicitly supported.

9. If user requests unavailable metric:
Insufficient data available.

10. Business Rules explain relationships only.
They do NOT create new facts.
";
    }
}
