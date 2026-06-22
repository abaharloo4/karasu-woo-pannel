/**
 * Tailwind Configuration for KarasuWooPannel
 * @version 1.0.1
 * @date 2026-06-23
 */
/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./panel/**/*.php",
    "./assets/js/**/*.js",
    "./includes/**/*.php"
  ],
  prefix: 'wsm-',
  theme: {
    extend: {
      fontFamily: {
        sans: ['Vazirmatn', 'sans-serif'],
      },
    },
  },
  plugins: [],
}
