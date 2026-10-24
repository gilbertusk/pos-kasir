import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/*
 * Blirik palette — taken from the green-rimmed enamel plates and cups of Indonesian warungs.
 * This file is the single source of truth for colors: change a ramp here and every page follows.
 *
 *   brand   hijau blirik  navigation, selected states, success ("lunas")
 *   kunyit  turmeric      the one primary call to action (Bayar) and warnings (stok menipis)
 *   enamel  neutrals      page background, surfaces, borders, text (900 = tinta)
 *   sambal  chili red     danger only: stok habis, errors, delete
 */
const brand = {
    50: '#F0F6F2',
    100: '#DCEBE1',
    200: '#B9D6C4',
    300: '#8DB9A0',
    400: '#5E9A7A',
    500: '#3D7D5D',
    600: '#2B6649',
    700: '#245540',
    800: '#1F4D3A',
    900: '#183B2D',
    950: '#0F261D',
};

const kunyit = {
    50: '#FEF8EC',
    100: '#FDEFD0',
    200: '#FADB9C',
    300: '#F6C766',
    400: '#F2B33D',
    500: '#E09A1F',
    600: '#BF7A12',
    700: '#9A5D10',
    800: '#7E4B13',
    900: '#683E14',
    950: '#3D220A',
};

const enamel = {
    50: '#F6F7F3',
    100: '#ECEFE9',
    200: '#DDE2DB',
    300: '#B9C1B9',
    400: '#6B766F',
    500: '#55625A',
    600: '#434F48',
    700: '#333D37',
    800: '#242C27',
    900: '#1B2620',
    950: '#111813',
};

const sambal = {
    50: '#FEF3F2',
    100: '#FDE4E1',
    200: '#FBCDC7',
    300: '#F6A69C',
    400: '#EE7465',
    500: '#DE4A39',
    600: '#C7351F',
    700: '#B42318',
    800: '#8E1F18',
    900: '#751F1A',
    950: '#400B08',
};

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],
    darkMode: 'class', // Disable automatic dark mode, only use if class added (which we won't)
    theme: {
        extend: {
            fontFamily: {
                sans: ['Plus Jakarta Sans', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand,
                kunyit,
                enamel,
                sambal,
                ink: enamel[900],

                // Existing markup still uses Tailwind's stock family names; they are remapped here
                // so every page follows the palette. Prefer the semantic names above in new code.
                primary: brand,
                emerald: brand,
                green: brand,
                orange: kunyit,
                amber: kunyit,
                yellow: kunyit,
                slate: enamel,
                gray: enamel,
                stone: enamel,
                red: sambal,
                rose: sambal,
            },
            borderRadius: {
                '3xl': '1.5rem',
                '4xl': '2rem',
            },
        },
    },

    plugins: [forms],
};
