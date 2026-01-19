import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
        './resources/js/**/*.js',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                mono: ['JetBrains Mono', 'Fira Code', ...defaultTheme.fontFamily.mono],
            },
            colors: {
                // Deep Industrial Steel
                steel: {
                    950: '#0a0a0b',
                    900: '#121214',
                    850: '#1a1a1d',
                    800: '#232326',
                    700: '#2d2d31',
                    600: '#3f3f46',
                    500: '#52525b',
                    400: '#71717a',
                    300: '#a1a1aa',
                    200: '#d4d4d8',
                    100: '#e4e4e7',
                },
                // Hot Metal Orange
                forge: {
                    400: '#ff9800',
                    500: '#ff6b35',
                    600: '#ff5722',
                    700: '#e64a19',
                    800: '#d84315',
                },
                // Arc Weld Blue
                weld: {
                    400: '#48cae4',
                    500: '#00b4d8',
                    600: '#0096c7',
                    700: '#0077b6',
                },
                // Plasma Purple
                plasma: {
                    500: '#9333ea',
                    600: '#7e22ce',
                    700: '#6b21a8',
                },
                // Safety Yellow
                safety: {
                    400: '#fbbf24',
                    500: '#f59e0b',
                    600: '#d97706',
                },
            },
            backgroundImage: {
                'gradient-steel': 'linear-gradient(135deg, #2d2d31 0%, #1a1a1d 100%)',
                'gradient-brushed': 'linear-gradient(90deg, #232326 0%, #2d2d31 25%, #232326 50%, #2d2d31 75%, #232326 100%)',
                'gradient-forge': 'linear-gradient(135deg, #ff5722 0%, #ff6b35 50%, #ff9800 100%)',
                'gradient-weld': 'linear-gradient(135deg, #0096c7 0%, #00b4d8 50%, #48cae4 100%)',
                'gradient-plasma': 'linear-gradient(135deg, #7e22ce 0%, #9333ea 50%, #a855f7 100%)',
                'texture-grid': 'linear-gradient(rgba(255, 255, 255, 0.02) 1px, transparent 1px), linear-gradient(90deg, rgba(255, 255, 255, 0.02) 1px, transparent 1px)',
                'texture-brushed': 'repeating-linear-gradient(90deg, transparent, transparent 2px, rgba(255, 255, 255, 0.02) 2px, rgba(255, 255, 255, 0.02) 4px)',
                'texture-diamond': 'radial-gradient(circle at 25% 25%, rgba(255, 255, 255, 0.03) 2%, transparent 0%), radial-gradient(circle at 75% 75%, rgba(255, 255, 255, 0.03) 2%, transparent 0%)',
            },
            backgroundSize: {
                'grid': '20px 20px',
                'diamond': '16px 16px',
            },
            boxShadow: {
                'glow-forge': '0 0 8px rgba(255, 107, 53, 0.4), 0 0 16px rgba(255, 107, 53, 0.2), 0 0 32px rgba(255, 107, 53, 0.1)',
                'glow-forge-lg': '0 0 12px rgba(255, 107, 53, 0.5), 0 0 24px rgba(255, 107, 53, 0.3), 0 0 48px rgba(255, 107, 53, 0.15)',
                'glow-weld': '0 0 8px rgba(0, 180, 216, 0.4), 0 0 16px rgba(0, 180, 216, 0.2)',
                'glow-weld-lg': '0 0 12px rgba(0, 180, 216, 0.5), 0 0 24px rgba(0, 180, 216, 0.3)',
                'glow-plasma': '0 0 12px rgba(147, 51, 234, 0.5), 0 0 24px rgba(147, 51, 234, 0.25)',
                'industrial': '0 4px 16px rgba(0, 0, 0, 0.4)',
                'industrial-lg': '0 8px 32px rgba(0, 0, 0, 0.6), 0 0 1px rgba(255, 107, 53, 0.1)',
                'industrial-xl': '0 12px 48px rgba(0, 0, 0, 0.7), 0 0 2px rgba(255, 107, 53, 0.15)',
            },
            keyframes: {
                'pulse-glow': {
                    '0%, 100%': { opacity: '1' },
                    '50%': { opacity: '0.6' },
                },
                'shimmer': {
                    '0%': { backgroundPosition: '-1000px 0' },
                    '100%': { backgroundPosition: '1000px 0' },
                },
                'scan': {
                    '0%': { transform: 'translateY(-100%)' },
                    '100%': { transform: 'translateY(100%)' },
                },
            },
            animation: {
                'pulse-glow': 'pulse-glow 2s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                'shimmer': 'shimmer 2s infinite',
                'scan': 'scan 2s ease-in-out infinite',
            },
            letterSpacing: {
                'industrial': '0.05em',
                'wide': '0.1em',
            },
            borderRadius: {
                'industrial': '4px',
            },
        },
    },

    plugins: [forms],
};
