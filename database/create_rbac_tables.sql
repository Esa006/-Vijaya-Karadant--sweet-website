-- RBAC Tables for Sweets Website
-- =============================================================

DROP TABLE IF EXISTS `user_permissions`;
DROP TABLE IF EXISTS `user_roles`;
DROP TABLE IF EXISTS `role_permissions`;
DROP TABLE IF EXISTS `permissions`;
DROP TABLE IF EXISTS `roles`;

CREATE TABLE IF NOT EXISTS `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `key_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key_name` (`key_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `role_permissions` (
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  PRIMARY KEY (`role_id`,`permission_id`),
  CONSTRAINT `fk_rp_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_rp_perm` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `user_roles` (
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  PRIMARY KEY (`user_id`,`role_id`),
  CONSTRAINT `fk_ur_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ur_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `user_permissions` (
  `user_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `type` enum('allow','deny') DEFAULT 'allow',
  PRIMARY KEY (`user_id`,`permission_id`),
  CONSTRAINT `fk_up_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_up_perm` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed initial data
-- =============================================================

INSERT IGNORE INTO `roles` (`name`, `slug`, `description`) VALUES 
('Super Admin', 'super_admin', 'Full access to everything'),
('Manager', 'manager', 'Can manage products, orders and customers'),
('Editor', 'editor', 'Can only manage products and news');

INSERT IGNORE INTO `permissions` (`name`, `key_name`, `description`) VALUES 
-- Products Module
('View Products', 'products:view', 'Ability to view products'),
('Create Products', 'products:create', 'Ability to create products'),
('Edit Products', 'products:edit', 'Ability to edit products'),
('Delete Products', 'products:delete', 'Ability to delete products'),
('Export Products', 'products:export', 'Ability to export products'),
-- Orders Module
('View Orders', 'orders:view', 'Ability to view orders'),
('Create Orders', 'orders:create', 'Ability to create orders'),
('Edit Orders', 'orders:edit', 'Ability to edit orders'),
('Delete Orders', 'orders:delete', 'Ability to delete orders'),
('Export Orders', 'orders:export', 'Ability to export orders'),
-- Customers Module
('View Customers', 'customers:view', 'Ability to view customers'),
('Create Customers', 'customers:create', 'Ability to create customers'),
('Edit Customers', 'customers:edit', 'Ability to edit customers'),
('Delete Customers', 'customers:delete', 'Ability to delete customers'),
('Export Customers', 'customers:export', 'Ability to export customers'),
-- Inventory Module
('View Inventory', 'inventory:view', 'Ability to view inventory'),
('Create Inventory', 'inventory:create', 'Ability to create inventory'),
('Edit Inventory', 'inventory:edit', 'Ability to edit inventory'),
('Delete Inventory', 'inventory:delete', 'Ability to delete inventory'),
('Export Inventory', 'inventory:export', 'Ability to export inventory'),
-- Reports Module
('View Reports', 'reports:view', 'Ability to view reports'),
('Create Reports', 'reports:create', 'Ability to create reports'),
('Edit Reports', 'reports:edit', 'Ability to edit reports'),
('Delete Reports', 'reports:delete', 'Ability to delete reports'),
('Export Reports', 'reports:export', 'Ability to export reports'),
-- System
('View Dashboard', 'dashboard:view', 'Ability to view dashboard'),
('Manage Settings', 'settings:manage', 'Ability to manage settings'),
('Manage Permissions', 'permissions:manage', 'Ability to manage permissions');

-- Assign all permissions to Manager role (except permissions)
INSERT IGNORE INTO `role_permissions` (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p
WHERE r.slug = 'manager' AND p.key_name LIKE 'products:%' OR p.key_name LIKE 'orders:%' OR p.key_name = 'dashboard:view';

-- Assign limited permissions to Editor role
INSERT IGNORE INTO `role_permissions` (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p
WHERE r.slug = 'editor' AND p.key_name IN ('dashboard:view', 'products:view', 'products:edit');

-- Assign Super Admin role to the first admin user found
INSERT IGNORE INTO `user_roles` (user_id, role_id)
SELECT u.id, r.id FROM users u, roles r
WHERE u.role = 'admin' AND r.slug = 'super_admin' LIMIT 1;
