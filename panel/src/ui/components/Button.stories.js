import { action } from "@storybook/addon-actions";
import Padding from "../../../storybook/theme/Padding.js";

export default {
  title: "UI | Navigation / Button",
  decorators: [Padding]
};

export const onlyText = () => ({
  methods: {
    action: action('click')
  },
  template: '<k-button @click="action">Text Button</k-button>',
});

export const textAndIcon = () => ({
  extends: onlyText(),
  template: '<k-button icon="edit" @click="action">Icon & Text</k-button>',
});

export const onlyIcon = () => ({
  extends: onlyText(),
  template: '<k-button icon="edit" @click="action" />',
});

export const link = () => ({
  template: '<k-button icon="url" link="https://getkirby.com">Link</k-button>'
});

export const positive = () => ({
  extends: onlyText(),
  template: `
    <k-button icon="check" theme="positive" @click="action">
      Nice one!
    </k-button>
  `
});

export const negative = () => ({
  extends: onlyText(),
  template: `
    <k-button icon="trash" theme="negative" @click="action">
      Uh oh!
    </k-button>
  `
});

export const disabled = () => ({
  extends: onlyText(),
  template: `
    <k-button :disabled="true" icon="trash" @click="action">
      Disabled button
    </k-button>
  `
});

export const customColor = () => ({
  extends: onlyText(),
  template: `
    <div>
      <k-button color="yellow" icon="star" @click="action">
        Activate sunshine
      </k-button>
      <br/><br/>
      <k-button color="#ff0000" icon="heart" @click="action">
        Spread love
      </k-button>
    </div>
  `
});

export const textAsProp = () => ({
  extends: onlyText(),
  template: `
    <k-button text="Text Button" @click="action" />
  `,
});

export const textFalse = () => ({
  extends: onlyText(),
  template: `
    <k-button :text="false" @click="action" />
  `,
});
