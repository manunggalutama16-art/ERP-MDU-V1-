-- ============================================
-- ERP Procurement MDU - PostgreSQL Schema
-- For Supabase Database
-- ============================================

-- Enable UUID extension (optional, for future use)
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- ============================================
-- Users Table
-- ============================================
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(10) DEFAULT 'user' CHECK (role IN ('admin', 'user')),
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- ============================================
-- Vendors Table
-- ============================================
CREATE TABLE IF NOT EXISTS vendors (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    address TEXT,
    npwp VARCHAR(50),
    npwp_file VARCHAR(255),
    phone VARCHAR(50),
    contact_person VARCHAR(255),
    email VARCHAR(255),
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- ============================================
-- Projects Table
-- ============================================
CREATE TABLE IF NOT EXISTS projects (
    id SERIAL PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    location TEXT,
    client VARCHAR(255),
    pic VARCHAR(255),
    value_before_ppn NUMERIC(20,2) DEFAULT 0,
    value_inc_ppn NUMERIC(20,2) DEFAULT 0,
    status VARCHAR(20) DEFAULT 'PENDING' CHECK (status IN ('ACTIVE', 'ON HOLD', 'PENDING', 'COMPLETED')),
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- ============================================
-- Purchase Orders Table
-- ============================================
CREATE TABLE IF NOT EXISTS purchase_orders (
    id SERIAL PRIMARY KEY,
    po_number VARCHAR(50) UNIQUE NOT NULL,
    vendor_id INTEGER REFERENCES vendors(id) ON DELETE SET NULL,
    project_id INTEGER REFERENCES projects(id) ON DELETE SET NULL,
    top TEXT,
    delivery_location TEXT,
    status VARCHAR(20) DEFAULT 'Draft' CHECK (status IN ('Draft', 'Printed', 'Signed', 'Invoiced', 'Completed')),
    quotation_attached BOOLEAN DEFAULT FALSE,
    approved BOOLEAN DEFAULT FALSE,
    subtotal NUMERIC(20,2) DEFAULT 0,
    ppn_percent NUMERIC(5,2) DEFAULT 11.00,
    ppn_amount NUMERIC(20,2) DEFAULT 0,
    grand_total NUMERIC(20,2) DEFAULT 0,
    notes TEXT,
    created_by INTEGER REFERENCES users(id) ON DELETE SET NULL,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- ============================================
-- PO Items Table
-- ============================================
CREATE TABLE IF NOT EXISTS po_items (
    id SERIAL PRIMARY KEY,
    po_id INTEGER NOT NULL REFERENCES purchase_orders(id) ON DELETE CASCADE,
    item_name TEXT NOT NULL,
    quantity NUMERIC(10,2) NOT NULL,
    unit VARCHAR(50) DEFAULT 'Pcs',
    price NUMERIC(20,2) NOT NULL,
    total NUMERIC(20,2) GENERATED ALWAYS AS (quantity * price) STORED,
    sort_order INTEGER DEFAULT 0
);

-- ============================================
-- PO Attachments Table
-- ============================================
CREATE TABLE IF NOT EXISTS po_attachments (
    id SERIAL PRIMARY KEY,
    po_id INTEGER NOT NULL REFERENCES purchase_orders(id) ON DELETE CASCADE,
    type VARCHAR(20) NOT NULL CHECK (type IN ('invoice_supplier', 'quotation', 'wet_signature', 'supporting')),
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_size INTEGER,
    uploaded_by INTEGER REFERENCES users(id) ON DELETE SET NULL,
    uploaded_at TIMESTAMPTZ DEFAULT NOW()
);

-- ============================================
-- PO Activity Log Table
-- ============================================
CREATE TABLE IF NOT EXISTS po_activity_log (
    id SERIAL PRIMARY KEY,
    po_id INTEGER NOT NULL REFERENCES purchase_orders(id) ON DELETE CASCADE,
    action VARCHAR(50) NOT NULL,
    description VARCHAR(255),
    created_by INTEGER REFERENCES users(id) ON DELETE SET NULL,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

-- ============================================
-- System Settings Table
-- ============================================
CREATE TABLE IF NOT EXISTS system_settings (
    id SERIAL PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- ============================================
-- Indexes for better performance
-- ============================================
CREATE INDEX IF NOT EXISTS idx_purchase_orders_po_number ON purchase_orders(po_number);
CREATE INDEX IF NOT EXISTS idx_purchase_orders_status ON purchase_orders(status);
CREATE INDEX IF NOT EXISTS idx_purchase_orders_created_at ON purchase_orders(created_at);
CREATE INDEX IF NOT EXISTS idx_purchase_orders_vendor_id ON purchase_orders(vendor_id);
CREATE INDEX IF NOT EXISTS idx_purchase_orders_project_id ON purchase_orders(project_id);
CREATE INDEX IF NOT EXISTS idx_po_items_po_id ON po_items(po_id);
CREATE INDEX IF NOT EXISTS idx_po_attachments_po_id ON po_attachments(po_id);
CREATE INDEX IF NOT EXISTS idx_po_activity_log_po_id ON po_activity_log(po_id);
CREATE INDEX IF NOT EXISTS idx_vendors_name ON vendors(name);
CREATE INDEX IF NOT EXISTS idx_projects_code ON projects(code);
CREATE INDEX IF NOT EXISTS idx_users_email ON users(email);

-- ============================================
-- Function to automatically update updated_at
-- ============================================
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$ language 'plpgsql';

-- ============================================
-- Triggers for updated_at
-- ============================================
CREATE TRIGGER update_users_updated_at BEFORE UPDATE ON users
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_vendors_updated_at BEFORE UPDATE ON vendors
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_projects_updated_at BEFORE UPDATE ON projects
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_purchase_orders_updated_at BEFORE UPDATE ON purchase_orders
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_system_settings_updated_at BEFORE UPDATE ON system_settings
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

-- ============================================
-- Insert default admin user (password: admin123)
-- ============================================
INSERT INTO users (name, email, password, role) VALUES 
('Administrator', 'admin@procurement.mdutama.com', '$2y$10$qwVxycCqwbyB2Cgu2H.6SOx/ZzAHn/GgRYnEk9Pl6GdQp16NXvQhO', 'admin')
ON CONFLICT (email) DO NOTHING;

-- ============================================
-- Insert default settings
-- ============================================
INSERT INTO system_settings (setting_key, setting_value) VALUES
('company_name', 'ProcureCorp MDU'),
('company_address', 'Jl. Sudirman No. 45, Kav 12, Jakarta Selatan, Indonesia 12190'),
('signatory_name', 'Bambang Sujatmiko, MBA'),
('signatory_title', 'Chief Procurement Officer'),
('signature_position', 'right'),
('footer_note', 'Syarat & Ketentuan:
1. Barang harus sesuai dengan spesifikasi.
2. Pembayaran dilakukan via transfer bank.')
ON CONFLICT (setting_key) DO NOTHING;

-- ============================================
-- Insert sample vendors
-- ============================================
INSERT INTO vendors (name, address, npwp, phone, contact_person, email) VALUES
('PT Sinar Teknologi Utama', 'Jl. Sudirman Kav 45-46, Jakarta Selatan', '01.234.567.8-012.000', '+62 811 2233 4455', 'Budi Santoso', 'contact@sinartech.com'),
('CV Logistik Mandiri', 'Kawasan Industri Jababeka Phase III, Bekasi', '02.112.334.5-015.000', '+62 812 3344 5566', 'Siti Aminah', 'info@logistikmandiri.id'),
('PT Konstruksi Jaya Bersama', 'Jl. HR Rasuna Said Blok X-5 No. 13, Jakarta', '01.998.776.5-011.000', '+62 813 4455 6677', 'Andi Wijaya', 'support@konstruksijaya.com'),
('Firma ATK Sejahtera', 'Ruko Maspion IT, Gunung Sahari, Jakarta', '03.445.667.1-013.000', '+62 815 5566 7788', 'Dewi Lestari', 'sales@atksejahtera.net'),
('PT Media Digital Kreatif', 'Centennial Tower Lt. 12, Gatot Subroto, Jakarta', '01.554.443.2-012.000', '+62 817 6677 8899', 'Rizky Pratama', 'hello@mediadigital.id')
ON CONFLICT DO NOTHING;

-- ============================================
-- Insert sample projects
-- ============================================
INSERT INTO projects (code, name, location, client, pic, value_before_ppn, value_inc_ppn, status) VALUES
('PRJ-2023-001', 'Office Tower Expansion Ph-2', 'Jakarta Selatan', 'PT Propertiindo', 'Bambang Hermawan', 2500000000, 2775000000, 'ACTIVE'),
('PRJ-2023-005', 'Data Center Cooling System', 'Jakarta Pusat', 'PT Data Solusi', 'Rina Wijaya', 1800000000, 1998000000, 'ACTIVE'),
('PRJ-2023-008', 'Warehouse Automation', 'Bekasi', 'PT Logistik Cepat', 'Dedi Kurniawan', 950000000, 1054500000, 'PENDING'),
('PRJ-2024-001', 'Smart Office IoT Integration', 'Jakarta Barat', 'PT Modern Kantor', 'Sari Dewi', 320000000, 355200000, 'ACTIVE'),
('PRJ-2024-002', 'Fleet Management System', 'Tangerang', 'PT Armada Jaya', 'Andi Saputra', 480000000, 532800000, 'ON HOLD')
ON CONFLICT (code) DO NOTHING;
