/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      fontSize: {
      },
    },
  },
  plugins: [
    require('@tailwindcss/custom-forms')
  ],
};
