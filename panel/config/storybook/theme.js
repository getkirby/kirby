import { create } from '@storybook/theming/create';

export default create({
  base: "light",

  colorPrimary: "#d16464",
  colorSecondary: "#7e9abf",

  // // UI
  appBg: "rgba(0,0,0, .0125)",
  // appContentBg: '#efefef',
  appBorderColor: "#dedede",
  appBorderRadius: 0,

  // // Typography
  fontBase:
    '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol"',
  fontCode:
    '"SFMono-Regular", Consolas, Liberation Mono, Menlo, Courier, monospace',

  // // Text colors
  textColor: "black",
  textInverseColor: "rgba(255,255,255,0.9)",

  brandTitle: "Kirby UI &<br/>Panel Internals",
  brandUrl: "https://getkirby.com",
  brandImage: false
});
