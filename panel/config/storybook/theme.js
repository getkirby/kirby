import { create } from '@storybook/theming/create';

export default create({
  base: 'light',

  colorPrimary: '#d16464',
  colorSecondary: '#7e9abf',

  // UI
  appBg: '#c7c7c7',
  appContentBg: '#efefef',
  appBorderColor: '#ccc',
  appBorderRadius: 0,

  // Typography
  fontBase: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol"',
  fontCode: '"SFMono-Regular", Consolas, Liberation Mono, Menlo, Courier, monospace',

  // Text colors
  textColor: 'black',
  textInverseColor: 'rgba(255,255,255,0.9)',

  // Toolbar default and active colors
  barTextColor: '#fff',
  barSelectedColor: '#f0c674',
  barBg: '#16171a',

  // Form colors
  inputBg: 'white',
  inputBorder: 'silver',
  inputTextColor: 'black',
  inputBorderRadius: 0,

  brandTitle: 'Kirby Panel docs',
  brandUrl: 'https://getkirby.com',
  brandImage: false,
});
