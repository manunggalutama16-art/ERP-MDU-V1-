---
name: Nexus Procurement
colors:
  surface: '#fbf8fa'
  surface-dim: '#dcd9db'
  surface-bright: '#fbf8fa'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f5f3f4'
  surface-container: '#f0edef'
  surface-container-high: '#eae7e9'
  surface-container-highest: '#e4e2e3'
  on-surface: '#1b1b1d'
  on-surface-variant: '#45474c'
  inverse-surface: '#303032'
  inverse-on-surface: '#f3f0f2'
  outline: '#75777d'
  outline-variant: '#c5c6cd'
  surface-tint: '#545f73'
  primary: '#091426'
  on-primary: '#ffffff'
  primary-container: '#1e293b'
  on-primary-container: '#8590a6'
  inverse-primary: '#bcc7de'
  secondary: '#0058be'
  on-secondary: '#ffffff'
  secondary-container: '#2170e4'
  on-secondary-container: '#fefcff'
  tertiary: '#00190e'
  on-tertiary: '#ffffff'
  tertiary-container: '#00301e'
  on-tertiary-container: '#00a472'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#d8e3fb'
  primary-fixed-dim: '#bcc7de'
  on-primary-fixed: '#111c2d'
  on-primary-fixed-variant: '#3c475a'
  secondary-fixed: '#d8e2ff'
  secondary-fixed-dim: '#adc6ff'
  on-secondary-fixed: '#001a42'
  on-secondary-fixed-variant: '#004395'
  tertiary-fixed: '#6ffbbe'
  tertiary-fixed-dim: '#4edea3'
  on-tertiary-fixed: '#002113'
  on-tertiary-fixed-variant: '#005236'
  background: '#fbf8fa'
  on-background: '#1b1b1d'
  surface-variant: '#e4e2e3'
typography:
  display-lg:
    fontFamily: Inter
    fontSize: 32px
    fontWeight: '700'
    lineHeight: 40px
    letterSpacing: -0.02em
  headline-md:
    fontFamily: Inter
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
    letterSpacing: -0.01em
  headline-sm:
    fontFamily: Inter
    fontSize: 20px
    fontWeight: '600'
    lineHeight: 28px
  body-lg:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
  body-md:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 20px
  body-sm:
    fontFamily: Inter
    fontSize: 12px
    fontWeight: '400'
    lineHeight: 16px
  label-md:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '600'
    lineHeight: 20px
  label-sm:
    fontFamily: Inter
    fontSize: 12px
    fontWeight: '600'
    lineHeight: 16px
    letterSpacing: 0.05em
  data-tabular:
    fontFamily: Inter
    fontSize: 13px
    fontWeight: '400'
    lineHeight: 18px
rounded:
  sm: 0.125rem
  DEFAULT: 0.25rem
  md: 0.375rem
  lg: 0.5rem
  xl: 0.75rem
  full: 9999px
spacing:
  unit: 4px
  xs: 4px
  sm: 8px
  md: 16px
  lg: 24px
  xl: 32px
  container-margin: 24px
  gutter: 16px
  sidebar-width: 260px
---

## Brand & Style

This design system is engineered for high-utility enterprise environments where efficiency, security, and clarity are paramount. The brand personality is **authoritative yet accessible**, emphasizing organizational trust and systematic precision. 

The aesthetic follows a **Corporate Modern** style. It prioritizes information density without sacrificing legibility. We employ a rigorous grid-based approach with subtle tonal layering to differentiate between global navigation, data views, and action panels. The interface is intentionally quiet, allowing critical data points and status indicators to command the user's attention.

## Colors

The palette is anchored by **Deep Navy (#1E293B)**, used for structural elements like sidebar navigation and primary headers to establish a foundation of stability. **Professional Blue (#3B82F6)** serves as the primary action color, guiding users toward interactive elements and primary buttons.

Functional colors are utilized strictly for status communication:
- **Emerald Green (#10B981)** signals successful procurement stages, approved invoices, and completed shipments.
- **Amber (#F59E0B)** alerts users to pending approvals or required documentation.

The background uses a cool-toned off-white (#F8FAFC) to reduce eye strain during prolonged use, while borders (#E2E8F0) maintain a crisp, organized structure for data-heavy layouts.

## Typography

**Inter** is the exclusive typeface for this design system, chosen for its exceptional legibility in data-dense environments and its neutral, systematic character.

- **Headlines:** Use Semi-Bold (600) or Bold (700) weights with slight negative letter-spacing for a compact, authoritative look.
- **Body Text:** The standard for most content is `body-md` (14px). This allows for high information density without compromising readability.
- **Data Labels:** Use `label-sm` in uppercase for table headers and small metadata tags to create clear visual separation from dynamic content.
- **Tabular Data:** A specific 13px variant is used for high-density tables to maximize row visibility on standard 1080p displays.

## Layout & Spacing

The design system utilizes a **4px base grid** to ensure mathematical consistency across all components.

### Grid Strategy
- **Desktop:** A 12-column fluid grid for main content areas, with fixed margins of 24px.
- **Sidebar:** A fixed-width navigation rail at 260px to provide persistent access to procurement modules (Invoices, Vendors, RFPs).
- **Data Density:** High-density views (tables) use 8px vertical padding, while standard forms and informational pages use 16px to 24px padding to provide breathing room.

### Breakpoints
- **Desktop (1280px+):** Full sidebar and multi-column forms.
- **Tablet (768px - 1279px):** Collapsed sidebar (icon only) and stacked form fields.
- **Mobile (<767px):** Bottom navigation or hamburger menu, full-width cards replacing table rows.

## Elevation & Depth

To maintain a professional and "flat" corporate feel, depth is communicated through **tonal layering and low-contrast outlines** rather than heavy shadows.

- **Level 0 (Surface):** Background color (#F8FAFC).
- **Level 1 (Card/Container):** Pure White (#FFFFFF) with a 1px border (#E2E8F0). This is used for main data tables and form containers.
- **Level 2 (Overlays):** Modals and dropdowns use a very soft, diffused shadow (0px 4px 12px rgba(30, 41, 59, 0.08)) to indicate they are temporary surfaces above the main application plane.
- **Active State:** Elements being dragged or interacted with may use a subtle Professional Blue (#3B82F6) glow or 2px border to indicate focus.

## Shapes

The design system adopts a **Soft (0.25rem)** roundedness. This provides a modern touch that feels approachable while maintaining the geometric rigor expected of an enterprise procurement tool.

- **Buttons & Inputs:** 4px (0.25rem) border-radius.
- **Cards & Large Containers:** 8px (0.5rem) border-radius.
- **Status Badges:** 12px or fully rounded (pill) to distinguish them from interactive buttons.
- **Selection Indicators:** Vertical bars (4px width) used on the left side of active sidebar items.

## Components

### Buttons
- **Primary:** Professional Blue background, White text. High-contrast for main actions like "Submit Purchase Order."
- **Secondary:** White background, Navy border (#1E293B), Navy text. Used for "Cancel" or "Save Draft."
- **Ghost:** No background/border, Navy text. Used for tertiary actions within tables.

### Data Tables (High-Density)
- **Header:** Light gray background (#F1F5F9), `label-sm` (uppercase) typography.
- **Rows:** White background, 1px bottom border. Hover state uses a 2% Deep Navy tint.
- **Alignment:** Numerical data (prices, quantities) is always right-aligned. Status badges are center-aligned.

### Input Fields
- **Default:** 1px border (#E2E8F0), 14px text.
- **Focus:** 1px Professional Blue border with a 2px soft blue outer glow.
- **Validation:** Error states use a Crimson border with helper text below the field.

### Status Badges
- Small, pill-shaped components with low-opacity backgrounds (e.g., 10% opacity of the status color) and 100% opacity text for high legibility and a refined look.

### Sidebar Navigation
- Deep Navy (#1E293B) background.
- Active state: Professional Blue (#3B82F6) left-accent bar and slightly lighter navy background tint.
- Icons: Linear, 20px size, consistent stroke weight.