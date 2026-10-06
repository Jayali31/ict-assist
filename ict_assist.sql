-- =====================================================================
-- ICT Assist - MySQL / MariaDB database (XAMPP compatible)
-- Import via phpMyAdmin -> Import, or: mysql -u root < ict_assist.sql
-- Demo password for ALL seeded users: Ict@12345  (bcrypt, PHP password_verify)
-- =====================================================================

DROP DATABASE IF EXISTS ict_assist;
CREATE DATABASE ict_assist CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ict_assist;

-- ---------------------------------------------------------------------
-- Lookup tables
-- ---------------------------------------------------------------------
CREATE TABLE branches (
  id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE departments (
  id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE categories (
  id   TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(30) NOT NULL UNIQUE      -- Hardware | Network | Software | Telephone
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Users (employee / coordinator / staff)
-- ---------------------------------------------------------------------
CREATE TABLE users (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name          VARCHAR(100) NOT NULL,            -- display name e.g. A.M. Emandi
  full_name     VARCHAR(150) NOT NULL,
  email         VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role          ENUM('employee','coordinator','staff') NOT NULL,
  role_title    VARCHAR(100) NULL,
  department_id INT UNSIGNED NULL,
  branch_id     INT UNSIGNED NULL,
  phone         VARCHAR(30)  NULL,
  avatar        VARCHAR(5)   NULL,                -- initials e.g. AE
  is_active     TINYINT(1) NOT NULL DEFAULT 1,
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_users_dept   FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,
  CONSTRAINT fk_users_branch FOREIGN KEY (branch_id)     REFERENCES branches(id)    ON DELETE SET NULL,
  INDEX idx_users_role (role)
) ENGINE=InnoDB;

-- Extra profile for field technicians (1:1 with users where role='staff')
CREATE TABLE technicians (
  id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id        INT UNSIGNED NOT NULL UNIQUE,
  specialization ENUM('Hardware','Network','Software','Telephone') NOT NULL,
  status         ENUM('Free','Busy') NOT NULL DEFAULT 'Free',
  CONSTRAINT fk_tech_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Tickets
-- Ticket number shown in the UI = CONCAT('TX-', id)
-- ---------------------------------------------------------------------
CREATE TABLE tickets (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title         VARCHAR(200) NOT NULL,
  description   TEXT NOT NULL,
  category_id   TINYINT UNSIGNED NOT NULL,
  priority      ENUM('High','Medium','Low') NOT NULL DEFAULT 'Medium',
  status        ENUM('Pending','In Progress','Resolved') NOT NULL DEFAULT 'Pending',
  department_id INT UNSIGNED NULL,
  branch_id     INT UNSIGNED NULL,
  created_by    INT UNSIGNED NOT NULL,
  assigned_tech_id INT UNSIGNED NULL,
  attachment    VARCHAR(255) NULL,                -- stored file name / path
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  resolved_at   DATETIME NULL,
  CONSTRAINT fk_tk_cat    FOREIGN KEY (category_id)      REFERENCES categories(id),
  CONSTRAINT fk_tk_dept   FOREIGN KEY (department_id)    REFERENCES departments(id) ON DELETE SET NULL,
  CONSTRAINT fk_tk_branch FOREIGN KEY (branch_id)        REFERENCES branches(id)    ON DELETE SET NULL,
  CONSTRAINT fk_tk_creator FOREIGN KEY (created_by)      REFERENCES users(id),
  CONSTRAINT fk_tk_tech   FOREIGN KEY (assigned_tech_id) REFERENCES technicians(id) ON DELETE SET NULL,
  INDEX idx_tk_status (status),
  INDEX idx_tk_creator (created_by),
  INDEX idx_tk_tech (assigned_tech_id)
) ENGINE=InnoDB AUTO_INCREMENT=2020;

-- Progress notes / timeline (replaces progressNotes[])
CREATE TABLE ticket_updates (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ticket_id  INT UNSIGNED NOT NULL,
  author_id  INT UNSIGNED NULL,
  note       VARCHAR(500) NOT NULL,
  new_status ENUM('Pending','In Progress','Resolved') NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_tu_ticket FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
  CONSTRAINT fk_tu_author FOREIGN KEY (author_id) REFERENCES users(id)   ON DELETE SET NULL,
  INDEX idx_tu_ticket (ticket_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Notifications (per user) and notification preferences
-- ---------------------------------------------------------------------
CREATE TABLE notifications (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id    INT UNSIGNED NOT NULL,
  ticket_id  INT UNSIGNED NULL,
  title      VARCHAR(200) NOT NULL,
  subtitle   VARCHAR(300) NULL,
  type       ENUM('info','success','warning') NOT NULL DEFAULT 'info',
  is_read    TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_nt_user   FOREIGN KEY (user_id)   REFERENCES users(id)   ON DELETE CASCADE,
  CONSTRAINT fk_nt_ticket FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE SET NULL,
  INDEX idx_nt_user_read (user_id, is_read)
) ENGINE=InnoDB;

CREATE TABLE notification_settings (
  user_id               INT UNSIGNED PRIMARY KEY,
  email_notif           TINYINT(1) NOT NULL DEFAULT 1,
  sms_notif             TINYINT(1) NOT NULL DEFAULT 0,
  ticket_status_changes TINYINT(1) NOT NULL DEFAULT 1,
  tech_assigned         TINYINT(1) NOT NULL DEFAULT 1,
  weekly_summary        TINYINT(1) NOT NULL DEFAULT 0,
  CONSTRAINT fk_ns_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Views (ready for dashboards / reports)
-- ---------------------------------------------------------------------
CREATE VIEW v_tickets AS
SELECT t.id,
       CONCAT('TX-', t.id)  AS ticket_no,
       t.title, t.description,
       c.name  AS category,
       t.priority, t.status,
       d.name  AS department,
       b.name  AS branch,
       u.name  AS created_by,
       tu.name AS assigned_to,
       t.assigned_tech_id,
       t.attachment, t.created_at, t.updated_at, t.resolved_at
FROM tickets t
JOIN categories c        ON c.id = t.category_id
JOIN users u             ON u.id = t.created_by
LEFT JOIN departments d  ON d.id = t.department_id
LEFT JOIN branches b     ON b.id = t.branch_id
LEFT JOIN technicians te ON te.id = t.assigned_tech_id
LEFT JOIN users tu       ON tu.id = te.user_id;

CREATE VIEW v_dashboard_counts AS
SELECT COUNT(*)                          AS total_requests,
       SUM(status = 'Resolved')          AS resolved,
       SUM(status = 'Pending')           AS pending,
       SUM(status = 'In Progress')       AS in_progress
FROM tickets;

CREATE VIEW v_category_breakdown AS
SELECT c.name AS category, COUNT(t.id) AS total
FROM categories c LEFT JOIN tickets t ON t.category_id = c.id
GROUP BY c.id, c.name;

CREATE VIEW v_technician_stats AS
SELECT te.id AS technician_id, u.name, u.avatar, te.specialization, te.status,
       COALESCE(SUM(t.status = 'In Progress'),0) AS active_count,
       COALESCE(SUM(t.status = 'Resolved'),0)    AS resolved_count,
       COUNT(t.id)                   AS assigned_total
FROM technicians te
JOIN users u ON u.id = te.user_id
LEFT JOIN tickets t ON t.assigned_tech_id = te.id
GROUP BY te.id, u.name, u.avatar, te.specialization, te.status;

-- ---------------------------------------------------------------------
-- Seed data (taken from src/data/initialData.js)
-- ---------------------------------------------------------------------
INSERT INTO categories (name) VALUES ('Hardware'),('Network'),('Software'),('Telephone');

INSERT INTO branches (name) VALUES
 ('Colombo Main Office'),('Headquarters'),('Field Services'),('Kandy Branch'),('Galle Branch');

INSERT INTO departments (name) VALUES
 ('Accounts & Finance'),('ICT Support & Dispatch'),('Hardware Maintenance'),
 ('Telecom & Admin'),('Server Room Admin'),('Finance'),('Human Resources'),
 ('Customer Service'),('Network Support'),('Software Support'),('Telephone Support');

-- users: 1 employee, 2 coordinator, 3-6 technicians, 7-8 other employees
INSERT INTO users (id,name,full_name,email,password_hash,role,role_title,department_id,branch_id,phone,avatar) VALUES
 (1,'A.M. Emandi','M.M.J.C. Deermini','a.m.emandi@ictassist.com','$2y$10$uIUHDJEKpRjAbLL2i.8J1eV6CJNHgQKHBnXh/l6zZNo5JnIACWHZC','employee','Employee',
    (SELECT id FROM departments WHERE name='Accounts & Finance'),(SELECT id FROM branches WHERE name='Colombo Main Office'),'+94 77 123 4567','AE'),
 (2,'Sarah Jayasinghe','Sarah Jayasinghe','coordinator@ictassist.com','$2y$10$uIUHDJEKpRjAbLL2i.8J1eV6CJNHgQKHBnXh/l6zZNo5JnIACWHZC','coordinator','Helpdesk Coordinator',
    (SELECT id FROM departments WHERE name='ICT Support & Dispatch'),(SELECT id FROM branches WHERE name='Headquarters'),'+94 71 987 6543','SJ'),
 (3,'K.P. Ratnasiri','K.P. Ratnasiri','ratnasiri@ictassist.com','$2y$10$uIUHDJEKpRjAbLL2i.8J1eV6CJNHgQKHBnXh/l6zZNo5JnIACWHZC','staff','Hardware Technician',
    (SELECT id FROM departments WHERE name='Hardware Maintenance'),(SELECT id FROM branches WHERE name='Field Services'),'+94 70 555 1234','KR'),
 (4,'A.S. Fernando','A.S. Fernando','fernando@ictassist.com','$2y$10$uIUHDJEKpRjAbLL2i.8J1eV6CJNHgQKHBnXh/l6zZNo5JnIACWHZC','staff','Network Technician',
    (SELECT id FROM departments WHERE name='Network Support'),(SELECT id FROM branches WHERE name='Field Services'),NULL,'AF'),
 (5,'M.E. Perera','M.E. Perera','perera@ictassist.com','$2y$10$uIUHDJEKpRjAbLL2i.8J1eV6CJNHgQKHBnXh/l6zZNo5JnIACWHZC','staff','Software Technician',
    (SELECT id FROM departments WHERE name='Software Support'),(SELECT id FROM branches WHERE name='Field Services'),NULL,'MP'),
 (6,'N.L. Silva','N.L. Silva','silva@ictassist.com','$2y$10$uIUHDJEKpRjAbLL2i.8J1eV6CJNHgQKHBnXh/l6zZNo5JnIACWHZC','staff','Telephone Technician',
    (SELECT id FROM departments WHERE name='Telephone Support'),(SELECT id FROM branches WHERE name='Field Services'),NULL,'NS'),
 (7,'Kamal Perera','Kamal Perera','kamal.perera@ictassist.com','$2y$10$uIUHDJEKpRjAbLL2i.8J1eV6CJNHgQKHBnXh/l6zZNo5JnIACWHZC','employee','Employee',
    (SELECT id FROM departments WHERE name='Finance'),(SELECT id FROM branches WHERE name='Kandy Branch'),NULL,'KP'),
 (8,'Sunil Silva','Sunil Silva','sunil.silva@ictassist.com','$2y$10$uIUHDJEKpRjAbLL2i.8J1eV6CJNHgQKHBnXh/l6zZNo5JnIACWHZC','employee','Employee',
    (SELECT id FROM departments WHERE name='Customer Service'),(SELECT id FROM branches WHERE name='Galle Branch'),NULL,'SS');

INSERT INTO technicians (id,user_id,specialization,status) VALUES
 (1,3,'Hardware','Free'),(2,4,'Network','Free'),(3,5,'Software','Free'),(4,6,'Telephone','Busy');

INSERT INTO notification_settings (user_id) SELECT id FROM users;

INSERT INTO tickets (id,title,description,category_id,priority,status,department_id,branch_id,created_by,assigned_tech_id,attachment,created_at,resolved_at) VALUES
 (2020,'IP Phone extension 402 silent dial tone','VoIP phone screen functions properly but handset receiver produces no dial tone.',
   4,'Medium','Resolved',(SELECT id FROM departments WHERE name='Customer Service'),(SELECT id FROM branches WHERE name='Galle Branch'),8,4,NULL,'2024-09-12 09:00:00','2024-09-13 15:00:00'),
 (2021,'Windows 11 update crash & Office suite','Outlook and Excel crashed continuously following automated security update KB503412.',
   3,'Low','Resolved',(SELECT id FROM departments WHERE name='Human Resources'),(SELECT id FROM branches WHERE name='Colombo Main Office'),1,3,NULL,'2024-09-15 09:00:00','2024-09-17 16:30:00'),
 (2024,'Printer not working','Paper jam in main office network laser printer. Continuous error 50.4 displayed on control panel.',
   1,'High','In Progress',(SELECT id FROM departments WHERE name='Telecom & Admin'),(SELECT id FROM branches WHERE name='Colombo Main Office'),1,1,'printer_error_screen.jpg','2024-09-18 09:00:00',NULL),
 (2025,'Server room AC filters maintenance','Quarterly cleaning and replacement of high-density air filters inside primary rack enclosures.',
   1,'Medium','Pending',(SELECT id FROM departments WHERE name='Server Room Admin'),(SELECT id FROM branches WHERE name='Colombo Main Office'),1,NULL,NULL,'2024-09-19 09:00:00',NULL),
 (2026,'VPN Connection failing across subnet','Remote staff unable to establish IPsec VPN tunnel with ERP database. Gateway timeout.',
   2,'High','Pending',(SELECT id FROM departments WHERE name='Finance'),(SELECT id FROM branches WHERE name='Kandy Branch'),7,NULL,NULL,'2024-09-20 09:00:00',NULL);

INSERT INTO ticket_updates (ticket_id,author_id,note,new_status,created_at) VALUES
 (2020,2,'Assigned to N.L. Silva','In Progress','2024-09-12 10:00:00'),
 (2020,6,'Replaced RJ9 spiral cord',NULL,'2024-09-13 11:00:00'),
 (2020,6,'Tested audio loopback ok','Resolved','2024-09-13 15:00:00'),
 (2021,2,'Assigned to M.E. Perera','In Progress','2024-09-15 10:00:00'),
 (2021,5,'Rolled back faulty driver',NULL,'2024-09-16 10:00:00'),
 (2021,5,'Rebuilt Outlook cache',NULL,'2024-09-16 14:00:00'),
 (2021,5,'Issue confirmed fixed','Resolved','2024-09-17 16:30:00'),
 (2024,2,'Assigned to K.P. Ratnasiri','In Progress','2024-09-18 10:15:00'),
 (2024,3,'Inspected paper feed rollers',NULL,'2024-09-18 10:45:00'),
 (2024,3,'Awaiting spare roller assembly',NULL,'2024-09-18 14:00:00');

INSERT INTO notifications (user_id,ticket_id,title,subtitle,type,is_read,created_at) VALUES
 (1,2024,'Technician assigned to your ticket #TX-2024','K.P. Ratnasiri will visit your branch today.','info',0,NOW()),
 (1,2024,'Work started on ticket #TX-2024','Technician is on-site at Colombo Main Office.','success',0,NOW()),
 (1,2026,'High priority ticket #TX-2026 submitted','Awaiting coordinator assignment for Kandy Branch.','warning',0,NOW()),
 (1,2021,'Ticket #TX-2021 has been resolved and closed','Windows update issue resolved by M.E. Perera.','success',1,DATE_SUB(NOW(), INTERVAL 1 DAY)),
 (2,2026,'High priority ticket #TX-2026 pending dispatch','Awaiting coordinator assignment for Kandy Branch.','warning',0,NOW()),
 (2,2024,'Ticket #TX-2024 assigned to technician','Assigned to K.P. Ratnasiri for on-site visit.','info',1,DATE_SUB(NOW(), INTERVAL 2 HOUR)),
 (3,2024,'New task assigned #TX-2024','Printer not working at Colombo Main Office (High priority)','warning',0,NOW()),
 (3,2026,'New incident pending #TX-2026','VPN Connection failing across subnet at Kandy Branch','info',0,NOW()),
 (3,2021,'Ticket #TX-2021 resolved','Windows 11 update issue completed and verified','success',1,DATE_SUB(NOW(), INTERVAL 1 DAY));

