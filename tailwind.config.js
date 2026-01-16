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

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: {
                    DEFAULT: 'rgb(48 85 191)',      // #3055bf - Texto
                    50: 'rgb(210 222 255)',         // #d2deff - Fundo do botão
                    100: 'rgb(195 210 255)',        // Hover
                    200: 'rgb(180 198 255)',        // Active
                    300: 'rgb(135, 166, 255)',
                    400: 'rgb(92, 128, 235)',
                    500: 'rgb(48 85 191)',          // Same as DEFAULT
                    600: 'rgb(38, 68, 153)',
                    700: 'rgb(30, 54, 122)',
                    800: 'rgb(22, 40, 92)',
                    900: 'rgb(15, 27, 61)',
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
