-- ============================================
-- ERP Procurement MDU - Database Schema
-- MySQL for Niagahoster
-- ============================================

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Vendors Table
CREATE TABLE IF NOT EXISTS vendors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    address TEXT,
    npwp VARCHAR(50),
    npwp_file VARCHAR(255),
    phone VARCHAR(50),
    contact_person VARCHAR(255),
    email VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Projects Table
CREATE TABLE IF NOT EXISTS projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    location TEXT,
    client VARCHAR(255),
    pic VARCHAR(255),
    value_before_ppn DECIMAL(20,2) DEFAULT 0,
    value_inc_ppn DECIMAL(20,2) DEFAULT 0,
    status ENUM('ACTIVE', 'ON HOLD', 'PENDING', 'COMPLETED') DEFAULT 'PENDING',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Purchase Orders Table
CREATE TABLE IF NOT EXISTS purchase_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    po_number VARCHAR(50) UNIQUE NOT NULL,
    vendor_id INT,
    project_id INT,
    top TEXT,
    delivery_location TEXT,
    status ENUM('Draft', 'Printed', 'Signed', 'Invoiced', 'Completed') DEFAULT 'Draft',
    quotation_attached BOOLEAN DEFAULT FALSE,
    approved BOOLEAN DEFAULT FALSE,
    subtotal DECIMAL(20,2) DEFAULT 0,
    ppn_percent DECIMAL(5,2) DEFAULT 11.00,
    ppn_amount DECIMAL(20,2) DEFAULT 0,
    grand_total DECIMAL(20,2) DEFAULT 0,
    notes TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (vendor_id) REFERENCES vendors(id) ON DELETE SET NULL,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- PO Items Table
CREATE TABLE IF NOT EXISTS po_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    po_id INT NOT NULL,
    item_name TEXT NOT NULL,
    quantity DECIMAL(10,2) NOT NULL,
    unit VARCHAR(50) DEFAULT 'Pcs',
    price DECIMAL(20,2) NOT NULL,
    total DECIMAL(20,2) GENERATED ALWAYS AS (quantity * price) STORED,
    sort_order INT DEFAULT 0,
    FOREIGN KEY (po_id) REFERENCES purchase_orders(id) ON DELETE CASCADE
);

-- PO Attachments Table
CREATE TABLE IF NOT EXISTS po_attachments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    po_id INT NOT NULL,
    type ENUM('invoice_supplier', 'quotation', 'wet_signature', 'supporting') NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_size INT,
    uploaded_by INT,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (po_id) REFERENCES purchase_orders(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL
);

-- PO Activity Log Table
CREATE TABLE IF NOT EXISTS po_activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    po_id INT NOT NULL,
    action VARCHAR(50) NOT NULL,
    description VARCHAR(255),
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (po_id) REFERENCES purchase_orders(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- System Settings Table
CREATE TABLE IF NOT EXISTS system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default admin user (password: admin123)
INSERT INTO users (name, email, password, role) VALUES 
('Administrator', 'admin@procurement.mdutama.com', '$2y$10$qwVxycCqwbyB2Cgu2H.6SOx/ZzAHn/GgRYnEk9Pl6GdQp16NXvQhO', 'admin')
ON DUPLICATE KEY UPDATE name=name;

-- Insert default settings
INSERT INTO system_settings (setting_key, setting_value) VALUES
('company_name', 'ProcureCorp MDU'),
('company_address', 'Jl. Sudirman No. 45, Kav 12, Jakarta Selatan, Indonesia 12190'),
('signatory_name', 'Bambang Sujatmiko, MBA'),
('signatory_title', 'Chief Procurement Officer'),
('signature_position', 'right'),
('footer_note', 'Syarat & Ketentuan:\n1. Barang harus sesuai dengan spesifikasi.\n2. Pembayaran dilakukan via transfer bank.')
ON DUPLICATE KEY UPDATE setting_value=setting_value;

-- Insert sample vendors
INSERT INTO vendors (name, address, npwp, phone, contact_person, email) VALUES
('PT Sinar Teknologi Utama', 'Jl. Sudirman Kav 45-46, Jakarta Selatan', '01.234.567.8-012.000', '+62 811 2233 4455', 'Budi Santoso', 'contact@sinartech.com'),
('CV Logistik Mandiri', 'Kawasan Industri Jababeka Phase III, Bekasi', '02.112.334.5-015.000', '+62 812 3344 5566', 'Siti Aminah', 'info@logistikmandiri.id'),
('PT Konstruksi Jaya Bersama', 'Jl. HR Rasuna Said Blok X-5 No. 13, Jakarta', '01.998.776.5-011.000', '+62 813 4455 6677', 'Andi Wijaya', 'support@konstruksijaya.com'),
('Firma ATK Sejahtera', 'Ruko Maspion IT, Gunung Sahari, Jakarta', '03.445.667.1-013.000', '+62 815 5566 7788', 'Dewi Lestari', 'sales@atksejahtera.net'),
('PT Media Digital Kreatif', 'Centennial Tower Lt. 12, Gatot Subroto, Jakarta', '01.554.443.2-012.000', '+62 817 6677 8899', 'Rizky Pratama', 'hello@mediadigital.id')
ON DUPLICATE KEY UPDATE name=name;

-- Insert sample projects
INSERT INTO projects (code, name, location, client, pic, value_before_ppn, value_inc_ppn, status) VALUES
('PRJ-2023-001', 'Office Tower Expansion Ph-2', 'Jakarta Selatan', 'PT Propertiindo', 'Bambang Hermawan', 2500000000, 2775000000, 'ACTIVE'),
('PRJ-2023-005', 'Data Center Cooling System', 'Jakarta Pusat', 'PT DataCenter Nusantara', 'Siti Aminah', 1200000000, 1332000000, 'ACTIVE'),
('PRJ-2024-012', 'Highway Lighting Rehabilitation', 'Bekasi', 'Dinas PUPR', 'Agus Salim', 850000000, 943500000, 'ON HOLD'),
('PRJ-2024-021', 'Solar Farm Installation Unit 4', 'Surabaya', 'PT Energi Terbarukan', NULL, 4200000000, 4662000000, 'PENDING'),
('PRJ-2024-025', 'Smart Warehouse Logistix', 'Cikarang', 'PT Logistik Nusantara', 'Dewi Lestari', 1850000000, 2053500000, 'ACTIVE')
ON DUPLICATE KEY UPDATE code=code;

-- Insert sample POs
INSERT INTO purchase_orders (po_number, vendor_id, project_id, top, delivery_location, status, quotation_attached, approved, subtotal, ppn_amount, grand_total, created_by) VALUES
('PO-2023-0895', 1, 1, 'Net 30', 'Jakarta Selatan', 'Signed', TRUE, TRUE, 142300000, 156530000, 298830000, 1),
('PO-2023-0894', 4, 2, 'COD', 'Jakarta Pusat', 'Printed', FALSE, FALSE, 980000, 107800, 1087800, 1),
('PO-2024-001', 2, 3, 'Net 15', 'Bekasi', 'Draft', TRUE, FALSE, 1250000000, 137500000, 1387500000, 1),
('PO-2024-002', 3, 4, 'Net 45', 'Surabaya', 'Draft', FALSE, FALSE, 2100000000, 231000000, 2331000000, 1)
ON DUPLICATE KEY UPDATE po_number=po_number;

-- Insert sample PO items
INSERT INTO po_items (po_id, item_name, quantity, unit, price, sort_order) VALUES
(1, 'Infrastructure Upgrade Phase 2', 1, 'Set', 1250000000, 1),
(1, 'Server Hardware - Gen 10 Performance Node', 12, 'Unit', 85500000, 2),
(1, 'Networking Switch - 48 Port 10GbE', 4, 'Unit', 42750000, 3),
(1, 'Installation & Commissioning Services', 1, 'Lot', 45000000, 4),
(2, 'Office Supplies Package', 50, 'Box', 19600, 1)
ON DUPLICATE KEY UPDATE id=id;
