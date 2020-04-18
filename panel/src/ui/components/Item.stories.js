import Item from "./Item.vue";
import Padding from "../storybook/Padding.js";
import { action } from "@storybook/addon-actions";

export default {
  title: "Data / Item",
  component: Item,
  decorators: [Padding]
};

export const simple = () => ({
  data() {
    return {
      image: {
        url: "https://source.unsplash.com/user/erondu/1600x900"
      },
      title: "Item title"
    };
  },
  methods: {
    onFlag: action("flag"),
    onOption: action("option")
  },
  template: `
    <k-grid gutter="large">
      <k-column width="1/3">
        <k-headline class="mb-3">List item</k-headline>
        <k-item
          v-bind="$data"
          layout="list"
          @flag="onFlag"
          @option="onOption"
        />
      </k-column>
      <k-column width="1/3">
        <k-headline class="mb-3">Cardlet item</k-headline>
        <k-item
          v-bind="$data"
          layout="cardlet"
          @flag="onFlag"
          @option="onOption"
        />
      </k-column>
      <k-column width="1/3">
        <k-headline class="mb-3">Card item</k-headline>
        <k-item
          v-bind="$data"
          layout="card"
          @flag="onFlag"
          @option="onOption"
        />
      </k-column>
    </k-grid>
  `
});

export const info = () => ({
  extends: simple(),
  data() {
    return {
      info: "This is a nice item"
    }
  }
});

export const options = () => ({
  extends: info(),
  data() {
    return {
      options: [
        { icon: 'edit', text: 'Edit', click: 'edit' },
        { icon: 'trash', text: 'Delete', click: 'delete' }
      ]
    }
  }
});

export const link = () => ({
  extends: options(),
  data() {
    return {
      link: "https://getkirby.com",
    }
  }
});

export const flag = () => ({
  extends: link(),
  data() {
    return {
      flag: {
        icon: "circle"
      }
    }
  }
});

export const imageRatio = () => ({
  extends: flag(),
  data() {
    return {
      image: {
        ratio: "4/5"
      }
    }
  },
  methods: {
    option: action("option")
  }
});

export const imageBack = () => ({
  extends: imageRatio(),
  data() {
    return {
      image: {
        back: "pattern"
      }
    }
  }
});

export const imageCover = () => ({
  extends: imageBack(),
  data() {
    return {
      image: {
        cover: true
      }
    };
  }
});
