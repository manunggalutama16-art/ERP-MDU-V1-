# Procurement Management System - System Architecture & Database Schema

## 1. Tech Stack
- **Frontend:** React.js (Vite) + Tailwind CSS + Lucide Icons
- **Backend:** Node.js (Express)
- **Database:** PostgreSQL
- **Tools:** jsPDF (PDF Generation), SheetJS (Excel Export), JWT (Auth)

## 2. Project Structure
```text
/procurement-system
├── /backend
│   ├── /config (db.js)
│   ├── /controllers (vendorController.js, projectController.js, poController.js)
│   ├── /models (Schema definitions)
│   ├── /routes (api.js)
│   ├── /middleware (auth.js)
│   └── index.js
├── /frontend
│   ├── /src
│   │   ├── /components (Shared UI)
│   │   ├── /pages (Dashboard, Vendors, Projects, PO)
│   │   ├── /hooks (API calls)
│   │   └── App.jsx
└── .env
```

## 3. Database Schema (SQL)
```sql
-- Users Table
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) CHECK (role IN ('admin', 'user')) DEFAULT 'user'
);

-- Vendors Table
CREATE TABLE vendors (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    address TEXT,
    npwp VARCHAR(50),
    phone VARCHAR(50),
    contact_person VARCHAR(255),
    email VARCHAR(255)
);

-- Projects Table
CREATE TABLE projects (
    id SERIAL PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    location TEXT,
    client VARCHAR(255)
);

-- Purchase Orders Table
CREATE TABLE purchase_orders (
    id SERIAL PRIMARY KEY,
    po_number VARCHAR(50) UNIQUE NOT NULL,
    vendor_id INTEGER REFERENCES vendors(id),
    project_id INTEGER REFERENCES projects(id),
    top TEXT,
    delivery_location TEXT,
    status VARCHAR(50) DEFAULT 'Draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INTEGER REFERENCES users(id)
);

-- PO Items Table
CREATE TABLE po_items (
    id SERIAL PRIMARY KEY,
    po_id INTEGER REFERENCES purchase_orders(id) ON DELETE CASCADE,
    item_name TEXT NOT NULL,
    quantity DECIMAL NOT NULL,
    unit VARCHAR(50),
    price DECIMAL NOT NULL,
    total DECIMAL GENERATED ALWAYS AS (quantity * price) STORED
);
```
