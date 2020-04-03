export default {
  title: "Bar"
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
