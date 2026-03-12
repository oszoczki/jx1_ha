-- 1) How many rows per payment_type in transaction_payment_methods (each type separately)
SELECT
    payment_type,
    COUNT(*) AS count
FROM transaction_payment_methods
GROUP BY payment_type
ORDER BY payment_type;

-- 2) Current utilization % per event (tickets.ticket_status, not status)
SELECT
    e.id,
    e.name,
    e.capacity,
    COUNT(CASE WHEN tk.ticket_status = 'sold' THEN 1 END) AS sold,
    ROUND(100 * COUNT(CASE WHEN tk.ticket_status = 'sold' THEN 1 END) / NULLIF(e.capacity, 0), 2) AS utilization_pct
FROM events e
LEFT JOIN tickets tk ON tk.event_id = e.id
GROUP BY e.id, e.name, e.capacity;

-- 3) Daily sales count and gross revenue since 2026-01-01 (transaction_items has no unit_price/quantity; use transactions.total_amount)
SELECT
    DATE(t.created_at) AS sale_date,
    COUNT(DISTINCT t.id) AS transaction_count,
    SUM(t.total_amount) AS gross_revenue
FROM transactions t
WHERE t.transaction_status = 'completed'
  AND t.created_at >= '2026-01-01'
GROUP BY DATE(t.created_at)
ORDER BY sale_date;

-- 4) Top 3 events by tickets sold
SELECT
    e.id,
    e.name,
    COUNT(ti.id) AS tickets_sold
FROM events e
JOIN tickets tk ON tk.event_id = e.id
JOIN transaction_items ti ON ti.ticket_id = tk.id
JOIN transactions tr ON tr.id = ti.transaction_id AND tr.transaction_status = 'completed'
GROUP BY e.id, e.name
ORDER BY tickets_sold DESC
LIMIT 3;
