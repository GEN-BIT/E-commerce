-- Add country column to users table
-- Backfill existing users with Rwanda as default
ALTER TABLE users ADD COLUMN country VARCHAR(100) NOT NULL DEFAULT 'Rwanda' AFTER address;
