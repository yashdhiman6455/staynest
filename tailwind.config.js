import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    theme: {
        extend: {
            colors: {
                brand: {
                    50: '#fff7ed',
                    100: '#ffedd5',
                    200: '#fed7aa',
                    300: '#fdba74',
                    400: '#fb923c',
                    500: '#f97316',
                    600: '#ea580c',
                    700: '#c2410c',
                    800: '#9a3412',
                    900: '#7c2d12',
                },
                night: {
                    50: '#fafaf9',
                    100: '#f5f5f4',
                    200: '#e7e5e4',
                    300: '#d6d3d1',
                    400: '#a8a29e',
                    500: '#78716c',
                    600: '#57534e',
                    700: '#44403c',
                    800: '#292524',
                    900: '#1c1917',
                },
            },
            fontFamily: {
                sans: ['"Plus Jakarta Sans"', 'Inter', ...defaultTheme.fontFamily.sans],
                display: ['Fraunces', '"Plus Jakarta Sans"', ...defaultTheme.fontFamily.sans],
            },
            boxShadow: {
                card: '0 1px 2px rgb(28 25 23 / 0.06), 0 8px 24px -12px rgb(28 25 23 / 0.18)',
                lift: '0 2px 4px rgb(28 25 23 / 0.08), 0 16px 40px -16px rgb(28 25 23 / 0.28)',
            },
            borderRadius: {
                xl: '1rem',
                '2xl': '1.25rem',
                '3xl': '1.75rem',
            },
        },
    },
    plugins: [],
};
