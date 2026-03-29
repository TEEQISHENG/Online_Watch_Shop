
ALTER TABLE orders
MODIFY status ENUM(
'Pending',
'Paid',
'Processing',
'Shipped',
'Out for Delivery',
'Delivered',
'Cancelled'
) DEFAULT 'Pending';
