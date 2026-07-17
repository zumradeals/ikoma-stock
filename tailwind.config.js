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
                sans: ['Manrope', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Couleur de marque — pointe vers la CSS var injectée par le layout,
                // personnalisable par société via Company::primary_color.
                brand: {
                    DEFAULT: 'var(--brand)',
                    dark:    'var(--brand-dark)',
                    wash:    'var(--brand-wash)',
                },
                // Fond chaud de l'app
                cream: '#FBF6F0',
                // Texte principal et secondaire
                ink: {
                    DEFAULT: '#211D1A',
                    soft:    '#6B6259',
                },
                // Séparateurs / bordures
                line: '#EAE0D4',
                // Statut : payé / livré
                success: {
                    DEFAULT: '#1F8A55',
                    wash:    '#E4F5EA',
                },
                // Statut : reste à payer
                gold: {
                    DEFAULT: '#B9790A',
                    wash:    '#FBF0D9',
                },
                // Statut : danger / annulée
                danger: {
                    DEFAULT: '#C0392B',
                    wash:    '#FBEAE7',
                },
                // Liens / informations secondaires
                info: {
                    DEFAULT: '#2454A8',
                    wash:    '#E9F0FB',
                },
                // Espace bureau / sidebar sombre
                charcoal: {
                    DEFAULT: '#201F22',
                    '2':     '#2B2A2E',
                    line:    '#3A383C',
                },
            },
            borderRadius: {
                pill: '99px',
            },
            boxShadow: {
                'brand-glow': '0 8px 16px -8px rgba(232,89,12,0.6)',
                'card':       '0 18px 40px -14px rgba(30,20,10,0.35)',
                'pos':        '0 30px 60px -24px rgba(20,15,10,0.55)',
            },
        },
    },

    plugins: [forms],
};
