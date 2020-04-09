import Headline from "./Headline.vue";

export default {
  title: "Typography / Headline",
  component: Headline
};

// props: {
//   size: {
//       default: select('size', ['small', 'regular', 'large', 'huge'], 'regular')
//   },
//   tag: {
//       default: text('tag', 'h2')
//   },
//   text: {
//       default: text('text', 'Headline')
//   },
//   theme: {
//       default: select('theme', ['none', 'positive', 'negative'], 'none')
//   },
// }

export const small = () => ({
  template: '<k-headline size="small">This is a headline</k-headline>',
});

export const regular = () => ({
  template: '<k-headline>This is a headline</k-headline>',
});

export const large = () => ({
  template: '<k-headline size="large">This is a headline</k-headline>',
});

export const huge = () => ({
  template: '<k-headline size="huge">This is a headline</k-headline>',
});

export const tags = () => ({
  template: `
    <div>
      <k-headline tag="h1">H1</k-headline>
      <k-headline tag="h2">H2</k-headline>
      <k-headline tag="h3">H3</k-headline>
    </div>
  `
});


