import Bar from "./Bar.vue";

export default {
  title: "Layout / Bar",
  component: Bar,
  parameters: {
    notes:
      "The k-bar can be used to create all sorts of toolbars with the option to have three different slots for buttons or other elements: left, center and right."
  }
};

export const leftSlot = () => ({
  template: `
    <k-bar>
      <template slot="left">
        This is left
      </template>
    </k-bar>
  `,
});

export const centerSlot = () => ({
  template: `
    <k-bar>
      <template slot="center">
        This is in the center
      </template>
    </k-bar>
  `,
});

export const rightSlot = () => ({
  template: `
    <k-bar>
      <template slot="right">
        This is on the right
      </template>
    </k-bar>
  `,
});

export const leftAndRight = () => ({
  template: `
    <k-bar>
      <template slot="left">
        This is left
      </template>
      <template slot="right">
        This is right
      </template>
    </k-bar>
  `,
});

export const allSlots = () => ({
  template: `
    <k-bar>
      <template slot="left">
        This is left
      </template>
      <template slot="center">
        This is in the center
      </template>
      <template slot="right">
        This is right
      </template>
    </k-bar>
  `,
});
