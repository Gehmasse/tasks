/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
        './resources/**/*.blade.php',
        './vendor/masmerise/livewire-toaster/resources/views/*.blade.php', // 👈
    ],
    theme: {
        extend: {},
    },
    plugins: [],
}
