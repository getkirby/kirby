<template>
  <nav v-if="buttonSetup.length" class="k-toolbar">
    <div class="k-toolbar-wrapper">
      <div class="k-toolbar-buttons">
        <template v-for="(button, buttonIndex) in buttonSetup">
          <!-- divider -->
          <template v-if="button === '|'">
            <span
              :key="buttonIndex"
              class="k-toolbar-divider"
            />
          </template>
          <!-- button from component -->
          <template v-else>
            <component
              :key="buttonIndex"
              :is="'k-toolbar-' + button + '-button'"
              tabindex="-1"
              v-on="$listeners"
            />
          </template>
        </template>
      </div>
    </div>
  </nav>
</template>

<script>
export default {
  props: {
    buttons: {
      type: [Boolean, Array],
      default: true,
    }
  },
  computed: {
    buttonSetup() {
      // disabled buttons
      if (this.buttons === false) {
        return [];
      }

      // default buttons
      if (this.buttons === true) {
        return [
          "headings",
          "bold",
          "italic",
          "|",
          "link",
          "email",
          "file",
          "|",
          "code",
          "ul",
          "ol"
        ];
      }

      // custom button setup
      return this.buttons;
    }
  },
  methods: {
    shortcut(key) {
      console.log(this.$children);
    }
  }
};
</script>

<style lang="scss">
.k-toolbar {
  background: $color-white;
  height: 36px;
}
.k-toolbar-wrapper {
  position: absolute;
  top: 0;
  right: 0;
  left: 0;
  max-width: 100%;
}
.k-toolbar-buttons {
  display: flex;
}
.k-toolbar-divider {
  width: 1px;
  background: $color-background;
}
.k-toolbar-button {
  width: 36px;
  height: 36px;
}
.k-toolbar-button:hover {
  background: rgba($color-background, 0.5);
}
</style>
