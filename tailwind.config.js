import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
        './app/Livewire/**/*.php',
        './app/View/**/*.php',
        './app/Models/**/*.php',
        './app/Services/**/*.php',
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    ],
    safelist: [
        { pattern: /^(bg|text|ring|border)-(emerald|amber|sky|rose|slate|teal|mint)-(50|100|200|500|600|700|800)$/ },
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['"Plus Jakarta Sans"', ...defaultTheme.fontFamily.sans],
                display: ['Fraunces', 'Georgia', ...defaultTheme.fontFamily.serif],
            },
            colors: {
                navy: {
                    50: '#f2f6fc',
                    100: '#e3ebf7',
                    200: '#c7d6ee',
                    300: '#9fb7df',
                    400: '#6f8fca',
                    500: '#4d6fb6',
                    600: '#3a5699',
                    700: '#2f457b',
                    800: '#223763',
                    900: '#172a4d',
                    950: '#0c1830',
                },
                brand: {
                    50: '#eef5ff',
                    100: '#d9e8ff',
                    200: '#bcd7ff',
                    300: '#8ebdff',
                    400: '#5998ff',
                    500: '#3374fb',
                    600: '#1d55f0',
                    700: '#1641d8',
                    800: '#1936af',
                    900: '#1b338a',
                },
                mint: {
                    50: '#effcf8',
                    100: '#d7f7ee',
                    200: '#b2eedd',
                    300: '#7fdec7',
                    400: '#4ac6ac',
                    500: '#27ab93',
                    600: '#1a8a78',
                    700: '#186f62',
                    800: '#175950',
                    900: '#164a43',
                },
                paper: '#f7f8fb',
            },
            boxShadow: {
                soft: '0 1px 2px rgba(16,24,40,.04), 0 8px 24px -12px rgba(23,42,77,.18)',
                lift: '0 2px 4px rgba(16,24,40,.04), 0 18px 40px -18px rgba(23,42,77,.35)',
                ring: '0 0 0 1px rgba(23,42,77,.06)',
            },
            borderRadius: {
                '4xl': '2rem',
            },
            keyframes: {
                marquee: { from: { transform: 'translateX(0)' }, to: { transform: 'translateX(-50%)' } },
                float: { '0%,100%': { transform: 'translateY(0)' }, '50%': { transform: 'translateY(-8px)' } },
                'fade-up': { from: { opacity: 0, transform: 'translateY(12px)' }, to: { opacity: 1, transform: 'none' } },
            },
            animation: {
                marquee: 'marquee 40s linear infinite',
                float: 'float 6s ease-in-out infinite',
                'fade-up': 'fade-up .6s cubic-bezier(.2,.7,.2,1) both',
            },
        },
    },
    plugins: [forms],
};
