import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    darkMode: 'class',

    safelist: [
        'text-emerald-600',
        'text-emerald-400',
        'bg-emerald-50',
        'bg-emerald-500/10',
        'text-rose-600',
        'text-rose-400',
        'bg-rose-50',
        'bg-rose-500/10',
        'dark:text-emerald-400',
        'dark:text-rose-400',
        'dark:bg-emerald-500/10',
        'dark:bg-rose-500/10',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: {
                    DEFAULT: '#4ECDC4',
                    50: '#f0fbf9',
                    100: '#d7f4ef',
                    200: '#afede2',
                    300: '#7be1d1',
                    400: '#4ECDC4',
                    500: '#34b2a8',
                    600: '#289088',
                    700: '#23736f',
                    800: '#1e5c5a',
                    900: '#1d4d4b',
                },
                dark: {
                    bg: 'rgb(23, 23, 23)',           // #171717 - Main background
                    surface: 'rgb(35, 35, 35)',      // #232323 - Elevated surface
                    elevated: 'rgb(57, 57, 57)',     // #393939 - More elevated
                    card: 'rgba(255, 255, 255, 0.03)', // Subtle card overlay
                    border: 'rgba(255, 255, 255, 0.08)', // Subtle borders
                },
            },
            backgroundColor: {
                'body-light': 'rgb(249, 250, 251)',  // Light gray background
                'body-dark': 'rgb(23, 23, 23)',      // #171717
            },
        },
    },

    plugins: [forms, typography],
};
