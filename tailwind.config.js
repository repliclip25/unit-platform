import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans:    ['Inter', ...defaultTheme.fontFamily.sans],
                display: ['Space Grotesk', 'Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    DEFAULT: '#f3c531',
                    deep:    '#d9a91f',
                    text:    '#1a1404',
                },
                navy: {
                    DEFAULT: '#0a0e1a',
                    2:       '#0d1220',
                    dark:    '#05070d',
                },
                cream: '#f4f1ea',
            },
        },
    },

    plugins: [forms],
};
