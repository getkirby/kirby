import Padding from "../../../storybook/theme/Padding.js";

export default {
  title: "UI | Data / Item Figure",
  decorators: [Padding]
};

export const simple = () => ({
  computed: {
    preview() {
      return;
    }
  },
  template: `
    <div style="max-width: 20rem">
      <k-item-figure
        :preview="preview"
        layout="card"
      />
    </div>
  `
});

export const image = () => ({
  extends: simple(),
  computed: {
    preview() {
      return {
        image: 'https://source.unsplash.com/user/erondu/1600x900'
      };
    }
  }
});

export const imageBack = () => ({
  extends: simple(),
  computed: {
    preview() {
      return {
        image: 'https://source.unsplash.com/user/erondu/1600x900',
        back: 'pattern'
      };
    }
  }
});

export const imageCover = () => ({
  extends: simple(),
  computed: {
    preview() {
      return {
        image: 'https://source.unsplash.com/user/erondu/1600x900',
        cover: true
      };
    }
  }
});

export const imageRatio = () => ({
  extends: simple(),
  computed: {
    preview() {
      return {
        image: 'https://source.unsplash.com/user/erondu/1600x900',
        ratio: '16/9'
      };
    }
  }
});

export const icon = () => ({
  extends: simple(),
  computed: {
    preview() {
      return {
        icon: 'user'
      };
    }
  }
});

export const iconColor = () => ({
  extends: simple(),
  computed: {
    preview() {
      return {
        color: 'green-light',
        icon: 'image'
      };
    }
  }
});

export const iconBack = () => ({
  extends: simple(),
  computed: {
    preview() {
      return {
        back: 'pattern',
        color: 'green-light',
        icon: 'image'
      };
    }
  }
});

export const iconSize = () => ({
  extends: simple(),
  computed: {
    preview() {
      return {
        back: 'pattern',
        color: 'green-light',
        size: 'large',
        icon: 'image',
      };
    }
  }
});

export const backColor = () => ({
  extends: simple(),
  computed: {
    preview() {
      return {
        back: '#ff0000',
        icon: false
      };
    }
  }
});

export const noPreview = () => ({
  extends: simple(),
  computed: {
    preview() {
      return false;
    }
  }
});
