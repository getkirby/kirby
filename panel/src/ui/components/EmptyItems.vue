<template>
  <k-items
    :items="items"
    :layout="layout"
    class="k-empty-items"
  />
</template>

<script>
export default {
  props: {
    info: {
      type: [Boolean, String],
      default: false
    },
    layout: {
      type: String,
      default: "list"
    },
    limit: {
      type: Number,
      default: 10
    },
    ratio: {
      type: String,
      default: "1/1"
    }
  },
  computed: {
    items() {
      return [...Array(this.limit).keys()].map(item => {
        return {
          className: "k-empty-item",
          image: {
            url: "data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7",
            back: "background",
            ratio: this.ratio
          },
          info: Boolean(this.info) ? " " : false,
          styles: {
            "--title": this.placeholder(100, 200),
            "--info": this.placeholder(50, 100),
          },
          options: [{ icon: "dots", text: "Edit" }],
          title: " ",
        };
      });
    }
  },
  methods: {
    placeholder(min, max) {
      return (Math.random() * (max - min) + min) + "px";
    }
  }
};
</script>

<style lang="scss">
.k-empty-item {
  color: $color-gray-200;
  pointer-events: none;
}
.k-empty-item .k-item-figure {
  display: block;
  height: 100%;
  background: $color-gray-200;
  border-radius: $rounded-sm;
}
.k-empty-item .k-item-title,
.k-empty-item .k-item-info {
  position: relative;
  display: block;
  color: $color-gray-200;
}
.k-empty-item .k-item-title::after,
.k-empty-item .k-item-info::after {
  content: "";
  display: inline-block;
  height: .5rem;
  margin-top: 1px;
  vertical-align: middle;
  background: $color-gray-200;
}
.k-list-item.k-empty-item .k-item-title::after,
.k-list-item.k-empty-item .k-item-info::after {
  margin-top: -2px;
}
.k-empty-item .k-item-title::after {
  width: var(--title);
}
.k-empty-item .k-item-info::after {
  width: var(--info);
}
.k-empty-item .k-item-options {
  width: 2.5rem;
}
</style>
