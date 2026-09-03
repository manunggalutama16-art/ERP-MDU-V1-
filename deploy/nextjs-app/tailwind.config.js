/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './pages/**/*.{js,ts,jsx,tsx,mdx}',
    './components/**/*.{js,ts,jsx,tsx,mdx}',
    './app/**/*.{js,ts,jsx,tsx,mdx}',
  ],
  theme: {
    extend: {
      colors: {
        primary: '#091426',
        secondary: '#0058be',
        surface: '#fbf8fa',
        'surface-container': '#f0edef',
        'surface-container-low': '#f5f3f4',
        'surface-container-lowest': '#ffffff',
        'surface-container-high': '#eae7e9',
        'surface-container-highest': '#e4e2e3',
        'on-surface': '#1b1b1d',
        'on-surface-variant': '#45474c',
        'on-primary': '#ffffff',
        'on-secondary': '#ffffff',
        outline: '#75777d',
        'outline-variant': '#c5c6cd',
        error: '#ba1a1a',
      },
      spacing: {
        'sidebar-width': '260px',
      },
    },
  },
  plugins: [],
}
