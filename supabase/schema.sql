-- ============================================================
-- Nexus Procurement - Skema Database
-- Cara pakai: Supabase Dashboard -> SQL Editor -> paste -> Run
-- Membuat tabel + Row Level Security + data contoh.
-- ============================================================

-- ---------- VENDORS (Master Vendor) ----------
create table if not exists public.vendors (
  id         uuid primary key default gen_random_uuid(),
  name       text not null,
  category   text default '',
  address    text default '',
  pic_name   text default '',
  npwp       text default '',
  phone      text default '',
  email      text default '',
  created_at timestamptz default now()
);

-- ---------- PROJECTS (Master Project) ----------
create table if not exists public.projects (
  id             uuid primary key default gen_random_uuid(),
  code           text not null unique,
  name           text not null,
  date           date default current_date,
  pm_name        text default '',          -- Nama Project Manager
  value_excl_ppn numeric default 0,        -- Nilai sebelum PPN
  value_incl_ppn numeric default 0,        -- Nilai setelah PPN
  address        text default '',          -- Alamat project
  gmap_url       text default '',          -- Link Google Maps
  top            text default '',          -- Tempo pembayaran (TOP)
  status         text default 'ACTIVE',
  created_at     timestamptz default now()
);

-- ---------- MIGRASI untuk database yang sudah ada (aman dijalankan berulang) ----------
-- Menambah kolom baru pada tabel projects.
alter table public.projects add column if not exists pm_name        text default '';
alter table public.projects add column if not exists value_incl_ppn numeric default 0;
alter table public.projects add column if not exists address        text default '';
alter table public.projects add column if not exists gmap_url       text default '';
alter table public.projects add column if not exists top            text default '';

-- Pindahkan data kolom lama 'pic' ke 'pm_name' lalu hapus kolom pic.
do $$
begin
  if exists (select 1 from information_schema.columns
             where table_schema = 'public' and table_name = 'projects' and column_name = 'pic')
     and exists (select 1 from information_schema.columns
             where table_schema = 'public' and table_name = 'projects' and column_name = 'pm_name') then
    update public.projects set pm_name = pic where pm_name = '' and pic <> '';
    alter table public.projects drop column pic;
  end if;
end $$;

-- ---------- PURCHASE ORDERS ----------
create table if not exists public.purchase_orders (
  id          uuid primary key default gen_random_uuid(),
  po_number   text not null unique,
  po_date     date default current_date,
  vendor_id   uuid references public.vendors(id) on delete set null,
  project_id  uuid references public.projects(id) on delete set null,
  top         text default '',
  ship_to     text default '',
  ppn_type    text default 'non' check (ppn_type in ('ppn', 'non')),
  approval    boolean default false,
  quotation   boolean default false,
  status      text default 'Draft',
  notes       text default '',
  subtotal    numeric default 0,
  tax_amount  numeric default 0,
  grand_total numeric default 0,
  created_by  uuid default auth.uid(),
  created_at  timestamptz default now()
);

create index if not exists idx_po_vendor  on public.purchase_orders(vendor_id);
create index if not exists idx_po_project on public.purchase_orders(project_id);

-- ---------- PO ITEMS ----------
create table if not exists public.po_items (
  id          uuid primary key default gen_random_uuid(),
  po_id       uuid not null references public.purchase_orders(id) on delete cascade,
  description text not null default '',
  qty         numeric default 1,
  unit        text default 'Pcs',
  unit_price  numeric default 0,
  line_total  numeric default 0,
  created_at  timestamptz default now()
);

create index if not exists idx_items_po on public.po_items(po_id);

-- ---------- COMPANY SETTINGS (Template PO) ----------
create table if not exists public.company_settings (
  id              int primary key default 1 check (id = 1),
  company_name    text default 'ProcureCorp Solutions',
  company_address text default '',
  company_npwp    text default '',
  logo_url        text default '',
  signer_name     text default '',
  signer_position text default '',
  signer_signature text default '',
  sign_position   text default 'left',
  footer_note     text default '',
  updated_at      timestamptz default now()
);

-- ============================================================
-- Row Level Security: hanya user yang sudah login (authenticated)
-- yang boleh membaca/menulis. Anon key TIDAK bisa akses tabel ini.
-- ============================================================
alter table public.vendors          enable row level security;
alter table public.projects         enable row level security;
alter table public.purchase_orders  enable row level security;
alter table public.po_items         enable row level security;
alter table public.company_settings enable row level security;

do $$
begin
  -- vendors
  if not exists (select 1 from pg_policies where tablename = 'vendors' and policyname = 'vendors_all_authenticated') then
    create policy vendors_all_authenticated on public.vendors
      for all to authenticated using (true) with check (true);
  end if;
  -- projects
  if not exists (select 1 from pg_policies where tablename = 'projects' and policyname = 'projects_all_authenticated') then
    create policy projects_all_authenticated on public.projects
      for all to authenticated using (true) with check (true);
  end if;
  -- purchase_orders
  if not exists (select 1 from pg_policies where tablename = 'purchase_orders' and policyname = 'po_all_authenticated') then
    create policy po_all_authenticated on public.purchase_orders
      for all to authenticated using (true) with check (true);
  end if;
  -- po_items
  if not exists (select 1 from pg_policies where tablename = 'po_items' and policyname = 'po_items_all_authenticated') then
    create policy po_items_all_authenticated on public.po_items
      for all to authenticated using (true) with check (true);
  end if;
  -- company_settings
  if not exists (select 1 from pg_policies where tablename = 'company_settings' and policyname = 'settings_all_authenticated') then
    create policy settings_all_authenticated on public.company_settings
      for all to authenticated using (true) with check (true);
  end if;
end $$;

-- ============================================================
-- DATA CONTOH (aman dijalankan berulang)
-- ============================================================
insert into public.vendors (name, category, address, pic_name, npwp, phone, email)
select * from (values
  ('PT Sinar Teknologi Utama',   'Technology & Hardware', 'Jl. Sudirman Kav 45-46, Jakarta Selatan', 'Budi Santoso',  '01.234.567.8-012.000', '+62 811 2233 4455', 'contact@sinartech.com'),
  ('CV Logistik Mandiri',        'Supply Chain',          'Kawasan Industri Jababeka Phase III, Bekasi', 'Siti Aminah', '02.112.334.5-015.000', '+62 812 3344 5566', 'info@logistikmandiri.id'),
  ('PT Konstruksi Jaya Bersama', 'Infrastructure',        'Jl. HR Rasuna Said Blok X-5 No. 13, Jakarta', 'Andi Wijaya', '01.998.776.5-011.000', '+62 813 4455 6677', 'support@konstruksijaya.com'),
  ('Firma ATK Sejahtera',        'Office Supplies',       'Ruko Maspion IT, Gunung Sahari, Jakarta', 'Dewi Lestari', '03.445.667.1-013.000', '+62 815 5566 7788', 'sales@atksejahtera.net'),
  ('PT Media Digital Kreatif',   'Marketing Services',    'Centennial Tower Lt. 12, Gatot Subroto, Jakarta', 'Rizky Pratama', '01.554.443.2-012.000', '+62 817 6677 8899', 'hello@mediadigital.id')
) as s(name, category, address, pic_name, npwp, phone, email)
where not exists (select 1 from public.vendors);

insert into public.projects (code, name, date, pm_name, value_excl_ppn, value_incl_ppn, address, gmap_url, top, status)
select * from (values
  ('PRJ-2023-001', 'Office Tower Expansion Ph-2',     '2024-01-12', 'Bambang Hermawan', 2500000000, 2775000000, 'Jl. Sudirman Kav 45-46, Jakarta Selatan', 'https://maps.google.com/?q=Jl.+Sudirman+Jakarta+Selatan', 'Net 30',   'ACTIVE'),
  ('PRJ-2023-005', 'Data Center Cooling System',      '2024-02-05', 'Siti Aminah',      1200000000, 1332000000, 'Kawasan Industri Jababeka Phase III, Bekasi', 'https://maps.google.com/?q=Kawasan+Industri+Jababeka+Bekasi', 'Net 45',   'ACTIVE'),
  ('PRJ-2024-012', 'Highway Lighting Rehabilitation', '2024-03-15', 'Agus Salim',        850000000,  943500000, 'Jl. Tol Jagorawi Km 12, Bogor', 'https://maps.google.com/?q=Jl.+Tol+Jagorawi+Bogor', 'Net 30',   'ON HOLD'),
  ('PRJ-2024-021', 'Solar Farm Installation Unit 4',  '2024-04-20', '',                 4200000000, 4662000000, 'Kawasan PLTS Terapung, Cirata', 'https://maps.google.com/?q=PLTS+Cirata', 'Net 60',   'PENDING'),
  ('PRJ-2024-025', 'Smart Warehouse Logistix',        '2024-05-10', 'Dewi Lestari',     1850000000, 2053500000, 'Kawasan Industri MM2100, Cikarang', 'https://maps.google.com/?q=MM2100+Cikarang', 'Net 30',   'ACTIVE')
) as s(code, name, date, pm_name, value_excl_ppn, value_incl_ppn, address, gmap_url, top, status)
where not exists (select 1 from public.projects);

insert into public.company_settings (id, company_name, company_address, company_npwp, signer_name, signer_position, footer_note)
select 1, 'ProcureCorp Solutions', 'Jl. Contoh No. 1, Jakarta', '00.000.000.0-000.000', 'Alex Henderson', 'Procurement Manager', 'Terima kasih atas kerja samanya. Mohon konfirmasi penerimaan PO ini.'
where not exists (select 1 from public.company_settings);
