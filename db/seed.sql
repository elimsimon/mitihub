-- Seed data (password: password)
INSERT INTO users (name, email, password_hash, role) VALUES
('Admin User', 'admin@mitihub.local', '$2y$10$zRz.6oKblWJrq6ygiBrS0O0iQ7Qk8y2q2c2rO1kG.IK1G7uY6aYQe', 'admin'),
('Default Sponsor', 'sponsor@mitihub.local', '$2y$10$zRz.6oKblWJrq6ygiBrS0O0iQ7Qk8y2q2c2rO1kG.IK1G7uY6aYQe', 'sponsor'),
('Default School', 'school@mitihub.local', '$2y$10$zRz.6oKblWJrq6ygiBrS0O0iQ7Qk8y2q2c2rO1kG.IK1G7uY6aYQe', 'school');

-- create sponsor and school profiles mapping to users above
INSERT INTO sponsors (user_id, organization) 
SELECT id, 'Mitihub Sponsor Org' FROM users WHERE email='sponsor@mitihub.local';

INSERT INTO schools (user_id, school_name) 
SELECT id, 'Mitihub Test School' FROM users WHERE email='school@mitihub.local';
