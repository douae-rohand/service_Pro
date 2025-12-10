/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        'service-green': '#92B08B',
        'service-yellow': '#F3E293',
        'service-blue': '#4682B4',
        'service-light-blue': '#A5D4E6',
      },
    },
  },
  plugins: [],
}

