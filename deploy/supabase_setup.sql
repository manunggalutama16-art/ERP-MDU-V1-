-- ============================================
-- ERP Procurement MDU - Supabase Setup
-- Auth + RLS Policies + Edge Functions
-- ============================================

-- Enable UUID extension
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- ============================================
-- 1. Auth Schema (managed by Supabase)
-- ============================================
-- Supabase auto-creates auth schema
-- We just need to link our users table

-- ============================================
-- 2. Custom Types
-- ============================================
DO $$ BEGIN
    CREATE TYPE user_role AS ENUM ('admin', 'user');
EXCEPTION
    WHEN duplicate_object THEN null;
END $$;

DO $$ BEGIN
    CREATE TYPE po_status AS ENUM ('Draft', 'Printed', 'Signed', 'Invoiced', 'Completed');
EXCEPTION
    WHEN duplicate_object THEN null;
END $$;

DO $$ BEGIN
    CREATE TYPE project_status AS ENUM ('ACTIVE', 'ON HOLD', 'PENDING', 'COMPLETED');
EXCEPTION
    WHEN duplicate_object THEN null;
END $$;

DO $$ BEGIN
    CREATE TYPE attachment_type AS ENUM ('invoice_supplier', 'quotation', 'wet_signature', 'supporting');
EXCEPTION
    WHEN duplicate_object THEN null;
END $$;

-- ============================================
-- 3. Users Table (linked to Supabase Auth)
-- ============================================
CREATE TABLE IF NOT EXISTS public.users (
    id UUID PRIMARY KEY DEFAULT auth.uid(),
    email VARCHAR(255) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    role user_role DEFAULT 'user',
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- ============================================
-- 4. Vendors Table
-- ============================================
CREATE TABLE IF NOT EXISTS public.vendors (
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
-- 5. Projects Table
-- ============================================
CREATE TABLE IF NOT EXISTS public.projects (
    id SERIAL PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    location TEXT,
    client VARCHAR(255),
    pic VARCHAR(255),
    value_before_ppn NUMERIC(20,2) DEFAULT 0,
    value_inc_ppn NUMERIC(20,2) DEFAULT 0,
    status project_status DEFAULT 'PENDING',
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- ============================================
-- 6. Purchase Orders Table
-- ============================================
CREATE TABLE IF NOT EXISTS public.purchase_orders (
    id SERIAL PRIMARY KEY,
    po_number VARCHAR(50) UNIQUE NOT NULL,
    vendor_id INTEGER REFERENCES public.vendors(id) ON DELETE SET NULL,
    project_id INTEGER REFERENCES public.projects(id) ON DELETE SET NULL,
    top TEXT,
    delivery_location TEXT,
    status po_status DEFAULT 'Draft',
    quotation_attached BOOLEAN DEFAULT FALSE,
    approved BOOLEAN DEFAULT FALSE,
    subtotal NUMERIC(20,2) DEFAULT 0,
    ppn_percent NUMERIC(5,2) DEFAULT 11.00,
    ppn_amount NUMERIC(20,2) DEFAULT 0,
    grand_total NUMERIC(20,2) DEFAULT 0,
    notes TEXT,
    created_by UUID REFERENCES public.users(id) ON DELETE SET NULL,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- ============================================
-- 7. PO Items Table
-- ============================================
CREATE TABLE IF NOT EXISTS public.po_items (
    id SERIAL PRIMARY KEY,
    po_id INTEGER NOT NULL REFERENCES public.purchase_orders(id) ON DELETE CASCADE,
    item_name TEXT NOT NULL,
    quantity NUMERIC(10,2) NOT NULL,
    unit VARCHAR(50) DEFAULT 'Pcs',
    price NUMERIC(20,2) NOT NULL,
    total NUMERIC(20,2) GENERATED ALWAYS AS (quantity * price) STORED,
    sort_order INTEGER DEFAULT 0
);

-- ============================================
-- 8. PO Attachments Table
-- ============================================
CREATE TABLE IF NOT EXISTS public.po_attachments (
    id SERIAL PRIMARY KEY,
    po_id INTEGER NOT NULL REFERENCES public.purchase_orders(id) ON DELETE CASCADE,
    type attachment_type NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_size INTEGER,
    uploaded_by UUID REFERENCES public.users(id) ON DELETE SET NULL,
    uploaded_at TIMESTAMPTZ DEFAULT NOW()
);

-- ============================================
-- 9. PO Activity Log Table
-- ============================================
CREATE TABLE IF NOT EXISTS public.po_activity_log (
    id SERIAL PRIMARY KEY,
    po_id INTEGER NOT NULL REFERENCES public.purchase_orders(id) ON DELETE CASCADE,
    action VARCHAR(50) NOT NULL,
    description VARCHAR(255),
    created_by UUID REFERENCES public.users(id) ON DELETE SET NULL,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

-- ============================================
-- 10. System Settings Table
-- ============================================
CREATE TABLE IF NOT EXISTS public.system_settings (
    id SERIAL PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- ============================================
-- Indexes
-- ============================================
CREATE INDEX IF NOT EXISTS idx_purchase_orders_po_number ON public.purchase_orders(po_number);
CREATE INDEX IF NOT EXISTS idx_purchase_orders_status ON public.purchase_orders(status);
CREATE INDEX IF NOT EXISTS idx_purchase_orders_created_at ON public.purchase_orders(created_at);
CREATE INDEX IF NOT EXISTS idx_po_items_po_id ON public.po_items(po_id);
CREATE INDEX IF NOT EXISTS idx_po_attachments_po_id ON public.po_attachments(po_id);
CREATE INDEX IF NOT EXISTS idx_po_activity_log_po_id ON public.po_activity_log(po_id);

-- ============================================
-- Updated_at Triggers
-- ============================================
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$ language 'plpgsql';

CREATE TRIGGER update_users_updated_at BEFORE UPDATE ON public.users
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_vendors_updated_at BEFORE UPDATE ON public.vendors
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_projects_updated_at BEFORE UPDATE ON public.projects
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_purchase_orders_updated_at BEFORE UPDATE ON public.purchase_orders
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_system_settings_updated_at BEFORE UPDATE ON public.system_settings
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

-- ============================================
-- Row Level Security (RLS) Policies
-- ============================================

-- Enable RLS on all tables
ALTER TABLE public.users ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.vendors ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.projects ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.purchase_orders ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.po_items ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.po_attachments ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.po_activity_log ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.system_settings ENABLE ROW LEVEL SECURITY;

-- Users: Can read own profile, admins can read all
CREATE POLICY "Users can view own profile" ON public.users
    FOR SELECT USING (auth.uid() = id);

CREATE POLICY "Admins can view all users" ON public.users
    FOR SELECT USING (
        EXISTS (SELECT 1 FROM public.users WHERE id = auth.uid() AND role = 'admin')
    );

-- Vendors: All authenticated users can read, admins can modify
CREATE POLICY "Authenticated users can view vendors" ON public.vendors
    FOR SELECT USING (auth.role() = 'authenticated');

CREATE POLICY "Admins can insert vendors" ON public.vendors
    FOR INSERT WITH CHECK (
        EXISTS (SELECT 1 FROM public.users WHERE id = auth.uid() AND role = 'admin')
    );

CREATE POLICY "Admins can update vendors" ON public.vendors
    FOR UPDATE USING (
        EXISTS (SELECT 1 FROM public.users WHERE id = auth.uid() AND role = 'admin')
    );

CREATE POLICY "Admins can delete vendors" ON public.vendors
    FOR DELETE USING (
        EXISTS (SELECT 1 FROM public.users WHERE id = auth.uid() AND role = 'admin')
    );

-- Projects: All authenticated users can read, admins can modify
CREATE POLICY "Authenticated users can view projects" ON public.projects
    FOR SELECT USING (auth.role() = 'authenticated');

CREATE POLICY "Admins can insert projects" ON public.projects
    FOR INSERT WITH CHECK (
        EXISTS (SELECT 1 FROM public.users WHERE id = auth.uid() AND role = 'admin')
    );

CREATE POLICY "Admins can update projects" ON public.projects
    FOR UPDATE USING (
        EXISTS (SELECT 1 FROM public.users WHERE id = auth.uid() AND role = 'admin')
    );

CREATE POLICY "Admins can delete projects" ON public.projects
    FOR DELETE USING (
        EXISTS (SELECT 1 FROM public.users WHERE id = auth.uid() AND role = 'admin')
    );

-- Purchase Orders: All authenticated users can read, admins can modify
CREATE POLICY "Authenticated users can view POs" ON public.purchase_orders
    FOR SELECT USING (auth.role() = 'authenticated');

CREATE POLICY "Admins can insert POs" ON public.purchase_orders
    FOR INSERT WITH CHECK (
        EXISTS (SELECT 1 FROM public.users WHERE id = auth.uid() AND role = 'admin')
    );

CREATE POLICY "Admins can update POs" ON public.purchase_orders
    FOR UPDATE USING (
        EXISTS (SELECT 1 FROM public.users WHERE id = auth.uid() AND role = 'admin')
    );

CREATE POLICY "Admins can delete POs" ON public.purchase_orders
    FOR DELETE USING (
        EXISTS (SELECT 1 FROM public.users WHERE id = auth.uid() AND role = 'admin')
    );

-- PO Items: All authenticated users can read, admins can modify
CREATE POLICY "Authenticated users can view PO items" ON public.po_items
    FOR SELECT USING (auth.role() = 'authenticated');

CREATE POLICY "Admins can insert PO items" ON public.po_items
    FOR INSERT WITH CHECK (
        EXISTS (SELECT 1 FROM public.users WHERE id = auth.uid() AND role = 'admin')
    );

CREATE POLICY "Admins can update PO items" ON public.po_items
    FOR UPDATE USING (
        EXISTS (SELECT 1 FROM public.users WHERE id = auth.uid() AND role = 'admin')
    );

CREATE POLICY "Admins can delete PO items" ON public.po_items
    FOR DELETE USING (
        EXISTS (SELECT 1 FROM public.users WHERE id = auth.uid() AND role = 'admin')
    );

-- PO Attachments: All authenticated users can read, admins can modify
CREATE POLICY "Authenticated users can view attachments" ON public.po_attachments
    FOR SELECT USING (auth.role() = 'authenticated');

CREATE POLICY "Admins can insert attachments" ON public.po_attachments
    FOR INSERT WITH CHECK (
        EXISTS (SELECT 1 FROM public.users WHERE id = auth.uid() AND role = 'admin')
    );

CREATE POLICY "Admins can delete attachments" ON public.po_attachments
    FOR DELETE USING (
        EXISTS (SELECT 1 FROM public.users WHERE id = auth.uid() AND role = 'admin')
    );

-- PO Activity Log: All authenticated users can read, system can insert
CREATE POLICY "Authenticated users can view activity log" ON public.po_activity_log
    FOR SELECT USING (auth.role() = 'authenticated');

CREATE POLICY "Authenticated users can insert activity log" ON public.po_activity_log
    FOR INSERT WITH CHECK (auth.role() = 'authenticated');

-- System Settings: All authenticated users can read, admins can modify
CREATE POLICY "Authenticated users can view settings" ON public.system_settings
    FOR SELECT USING (auth.role() = 'authenticated');

CREATE POLICY "Admins can update settings" ON public.system_settings
    FOR UPDATE USING (
        EXISTS (SELECT 1 FROM public.users WHERE id = auth.uid() AND role = 'admin')
    );

-- ============================================
-- Insert Default Data
-- ============================================

-- Note: Admin user will be created via Supabase Auth UI
-- This is just a placeholder for the users table
INSERT INTO public.users (id, email, name, role) VALUES 
('00000000-0000-0000-0000-000000000000', 'admin@procurement.mdutama.com', 'Administrator', 'admin')
ON CONFLICT (id) DO NOTHING;

-- Default settings
INSERT INTO public.system_settings (setting_key, setting_value) VALUES
('company_name', 'ProcureCorp MDU'),
('company_address', 'Jl. Sudirman No. 45, Kav 12, Jakarta Selatan, Indonesia 12190'),
('signatory_name', 'Bambang Sujatmiko, MBA'),
('signatory_title', 'Chief Procurement Officer'),
('signature_position', 'right'),
('footer_note', 'Syarat & Ketentuan:
1. Barang harus sesuai dengan spesifikasi.
2. Pembayaran dilakukan via transfer bank.')
ON CONFLICT (setting_key) DO NOTHING;

-- Sample vendors
INSERT INTO public.vendors (name, address, npwp, phone, contact_person, email) VALUES
('PT Sinar Teknologi Utama', 'Jl. Sudirman Kav 45-46, Jakarta Selatan', '01.234.567.8-012.000', '+62 811 2233 4455', 'Budi Santoso', 'contact@sinartech.com'),
('CV Logistik Mandiri', 'Kawasan Industri Jababeka Phase III, Bekasi', '02.112.334.5-015.000', '+62 812 3344 5566', 'Siti Aminah', 'info@logistikmandiri.id'),
('PT Konstruksi Jaya Bersama', 'Jl. HR Rasuna Said Blok X-5 No. 13, Jakarta', '01.998.776.5-011.000', '+62 813 4455 6677', 'Andi Wijaya', 'support@konstruksijaya.com'),
('Firma ATK Sejahtera', 'Ruko Maspion IT, Gunung Sahari, Jakarta', '03.445.667.1-013.000', '+62 815 5566 7788', 'Dewi Lestari', 'sales@atksejahtera.net'),
('PT Media Digital Kreatif', 'Centennial Tower Lt. 12, Gatot Subroto, Jakarta', '01.554.443.2-012.000', '+62 817 6677 8899', 'Rizky Pratama', 'hello@mediadigital.id')
ON CONFLICT DO NOTHING;

-- Sample projects
INSERT INTO public.projects (code, name, location, client, pic, value_before_ppn, value_inc_ppn, status) VALUES
('PRJ-2023-001', 'Office Tower Expansion Ph-2', 'Jakarta Selatan', 'PT Propertiindo', 'Bambang Hermawan', 2500000000, 2775000000, 'ACTIVE'),
('PRJ-2023-005', 'Data Center Cooling System', 'Jakarta Pusat', 'PT Data Solusi', 'Rina Wijaya', 1800000000, 1998000000, 'ACTIVE'),
('PRJ-2023-008', 'Warehouse Automation', 'Bekasi', 'PT Logistik Cepat', 'Dedi Kurniawan', 950000000, 1054500000, 'PENDING'),
('PRJ-2024-001', 'Smart Office IoT Integration', 'Jakarta Barat', 'PT Modern Kantor', 'Sari Dewi', 320000000, 355200000, 'ACTIVE'),
('PRJ-2024-002', 'Fleet Management System', 'Tangerang', 'PT Armada Jaya', 'Andi Saputra', 480000000, 532800000, 'ON HOLD')
ON CONFLICT (code) DO NOTHING;
