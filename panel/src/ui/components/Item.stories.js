import Item from "./Item.vue";
import Padding from "../../../storybook/theme/Padding.js";
import { action } from "@storybook/addon-actions";

export default {
  title: "UI | Data / Item",
  component: Item,
  decorators: [Padding]
};

export const simple = () => ({
  data() {
    return {
      preview: {
        image: "https://source.unsplash.com/user/erondu/1600x900"
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
        { icon: 'trash', text: 'Delete', click: 'remove' }
      ]
    }
  }
});

export const singleOption = () => ({
  extends: info(),
  data() {
    return {
      options: [
        { icon: 'edit', text: 'Edit', click: 'edit' },
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
  computed: {
    draftFlag() {
      return {
        icon: {
          type: 'circle-outline',
          size: 'small',
        },
        color: 'red-light',
        class: 'k-status-button'
      };
    },
    unlistedFlag() {
      return {
        icon: {
          type: 'circle-half',
          size: 'small',
        },
        color: 'blue-light',
        class: 'k-status-button'
      };
    },
    listedFlag() {
      return {
        icon: {
          type: 'circle',
          size: 'small',
        },
        color: 'green-light',
        class: 'k-status-button'
      };
    }
  },
  template: `
    <k-auto-grid style="--gap: 3rem">
      <div>
        <k-headline class="mb-3">Draft</k-headline>
        <k-item
          v-bind="$data"
          :flag="draftFlag"
          class="mb-6"
        />
        <k-item
          v-bind="$data"
          :flag="draftFlag"
          class="mb-6"
          layout="cardlet"
        />
        <k-item
          v-bind="$data"
          :flag="draftFlag"
          class="mb-6"
          layout="card"
        />
      </div>

      <div>
        <k-headline class="mb-3">Unlisted</k-headline>
        <k-item
          v-bind="$data"
          :flag="unlistedFlag"
          class="mb-6"
        />
        <k-item
          v-bind="$data"
          :flag="unlistedFlag"
          class="mb-6"
          layout="cardlet"
        />
        <k-item
          v-bind="$data"
          :flag="unlistedFlag"
          class="mb-6"
          layout="card"
        />
      </div>

      <div>
        <k-headline class="mb-3">Listed</k-headline>
        <k-item
          v-bind="$data"
          :flag="listedFlag"
          class="mb-6"
        />
        <k-item
          v-bind="$data"
          :flag="listedFlag"
          class="mb-6"
          layout="cardlet"
        />
        <k-item
          v-bind="$data"
          :flag="listedFlag"
          class="mb-6"
          layout="card"
        />
      </div>
    </k-auto-grid>
  `
});

export const previewRatio = () => ({
  extends: flag(),
  data() {
    return {
      preview: {
        ratio: "4/5"
      }
    }
  },
  methods: {
    option: action("option")
  }
});

export const previewBack = () => ({
  extends: previewRatio(),
  data() {
    return {
      preview: {
        back: "pattern"
      }
    }
  }
});

export const previewCover = () => ({
  extends: previewBack(),
  data() {
    return {
      preview: {
        cover: true
      }
    };
  }
});
