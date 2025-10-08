-- === CHỌN DATABASE ===
-- USE badminton_booking;

-- === BẢNG courts (nếu chưa có) ===
CREATE TABLE IF NOT EXISTS courts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  price_per_hour INT NOT NULL DEFAULT 0,
  image VARCHAR(255) NULL,
  active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 12 sân mẫu
INSERT INTO courts (name, price_per_hour, image, active, created_at)
SELECT * FROM ( 
  SELECT 'Sân 1', 100000, NULL, 1, NOW() UNION ALL
  SELECT 'Sân 2', 100000, NULL, 1, NOW() UNION ALL
  SELECT 'Sân 3', 100000, NULL, 1, NOW() UNION ALL
  SELECT 'Sân 4', 100000, NULL, 1, NOW() UNION ALL
  SELECT 'Sân 5', 100000, NULL, 1, NOW() UNION ALL
  SELECT 'Sân 6', 100000, NULL, 1, NOW() UNION ALL
  SELECT 'Sân 7', 120000, NULL, 1, NOW() UNION ALL
  SELECT 'Sân 8', 120000, NULL, 1, NOW() UNION ALL
  SELECT 'Sân 9', 120000, NULL, 1, NOW() UNION ALL
  SELECT 'Sân 10',120000, NULL, 1, NOW() UNION ALL
  SELECT 'Sân 11',120000, NULL, 1, NOW() UNION ALL
  SELECT 'Sân 12',120000, NULL, 1, NOW()
) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM courts LIMIT 1);

-- === BẢNG timeslots (nếu chưa có) ===
CREATE TABLE IF NOT EXISTS timeslots (
  id INT AUTO_INCREMENT PRIMARY KEY,
  label VARCHAR(20) NOT NULL,      -- "07:00-08:00"
  start_time TIME NOT NULL,
  end_time TIME NOT NULL,
  active TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed timeslots nếu rỗng
INSERT INTO timeslots(label,start_time,end_time)
SELECT * FROM (
  SELECT '07:00-08:00','07:00:00','08:00:00' UNION ALL
  SELECT '08:00-09:00','08:00:00','09:00:00' UNION ALL
  SELECT '09:00-10:00','09:00:00','10:00:00' UNION ALL
  SELECT '17:00-18:00','17:00:00','18:00:00' UNION ALL
  SELECT '18:00-19:00','18:00:00','19:00:00' UNION ALL
  SELECT '19:00-20:00','19:00:00','20:00:00'
) AS t
WHERE NOT EXISTS (SELECT 1 FROM timeslots LIMIT 1);

-- === BẢNG bookings (nếu chưa có) ===
CREATE TABLE IF NOT EXISTS bookings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  court_id INT NOT NULL,
  booking_date DATE NOT NULL,
  timeslot_id INT NOT NULL,
  status ENUM('pending','approved','cancelled','rejected') NOT NULL DEFAULT 'pending',
  total_price INT NOT NULL DEFAULT 0,
  deposit INT NOT NULL DEFAULT 0,
  deposit_status ENUM('pending','paid','refunded') NOT NULL DEFAULT 'pending',
  notes VARCHAR(255) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_user (user_id),
  INDEX idx_court_date (court_id, booking_date),
  CONSTRAINT fk_book_court FOREIGN KEY (court_id) REFERENCES courts(id) ON DELETE CASCADE,
  CONSTRAINT fk_book_timeslot FOREIGN KEY (timeslot_id) REFERENCES timeslots(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Thêm UNIQUE để chặn trùng lịch (một sân, một ngày, một slot chỉ có 1 booking)
ALTER TABLE bookings
  ADD UNIQUE KEY IF NOT EXISTS uniq_court_date_slot (court_id, booking_date, timeslot_id);
