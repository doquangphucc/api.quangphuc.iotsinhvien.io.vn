-- Update order_status ENUM to include new statuses
-- Run this on an existing database to add the new order status values

USE nangluongmattroi;

-- First, modify the ENUM to include all statuses
ALTER TABLE orders 
MODIFY COLUMN order_status ENUM('pending', 'approved', 'processing', 'shipping', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending';

-- Note: If you have any orders with 'completed' status, update them to 'delivered' first:
-- UPDATE orders SET order_status = 'delivered' WHERE order_status = 'completed';
