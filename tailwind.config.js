/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./resources/views.welcome.blade.php",
    "./node_modules/flowbite/**/*.js"
  ],
  theme: {
    extend: {},
  },
  plugins: [
    require('flowbite/plugin')
  ],
}
