-- Upgrade an existing ICT Assist database without dropping application data.
USE ict_assist;

SET @profile_image_column_exists = (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'users'
    AND COLUMN_NAME = 'profile_image'
);

SET @add_profile_image_sql = IF(
  @profile_image_column_exists = 0,
  'ALTER TABLE users ADD COLUMN profile_image VARCHAR(255) NULL AFTER avatar',
  'SELECT 1'
);
PREPARE add_profile_image_stmt FROM @add_profile_image_sql;
EXECUTE add_profile_image_stmt;
DEALLOCATE PREPARE add_profile_image_stmt;

CREATE OR REPLACE VIEW v_tickets AS
SELECT t.id,
       CONCAT('TX-', t.id) AS ticket_no,
       t.title, t.description,
       c.name AS category,
       t.priority, t.status,
       d.name AS department,
       b.name AS branch,
       u.name AS created_by,
       u.profile_image AS created_by_photo,
       tu.name AS assigned_to,
       tu.profile_image AS assigned_to_photo,
       t.assigned_tech_id,
       t.attachment, t.created_at, t.updated_at, t.resolved_at
FROM tickets t
JOIN categories c ON c.id = t.category_id
JOIN users u ON u.id = t.created_by
LEFT JOIN departments d ON d.id = t.department_id
LEFT JOIN branches b ON b.id = t.branch_id
LEFT JOIN technicians te ON te.id = t.assigned_tech_id
LEFT JOIN users tu ON tu.id = te.user_id;

CREATE OR REPLACE VIEW v_technician_stats AS
SELECT te.id AS technician_id, u.name, u.avatar, u.profile_image, te.specialization, te.status,
       COALESCE(SUM(t.status = 'In Progress'), 0) AS active_count,
       COALESCE(SUM(t.status = 'Resolved'), 0) AS resolved_count,
       COUNT(t.id) AS assigned_total
FROM technicians te
JOIN users u ON u.id = te.user_id
LEFT JOIN tickets t ON t.assigned_tech_id = te.id
GROUP BY te.id, u.name, u.avatar, u.profile_image, te.specialization, te.status;
