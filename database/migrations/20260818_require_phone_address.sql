-- Make phone and address required for all users
-- First, backfill any existing NULL values
UPDATE users SET phone = '' WHERE phone IS NULL;
UPDATE users SET address = '' WHERE address IS NULL;

-- Then enforce NOT NULL
ALTER TABLE users MODIFY COLUMN phone VARCHAR(30) NOT NULL;
ALTER TABLE users MODIFY COLUMN address VARCHAR(255) NOT NULL;
