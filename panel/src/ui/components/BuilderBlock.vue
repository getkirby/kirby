<template>
  <k-column :width="width" class="k-builder-block">
    <article class="bg-white shadow px-2px rounded-sm">
      <header class="flex items-center cursor-pointer" @click="toggle">
        <!-- Sort handle -->
        <k-sort-handle class="k-builder-block-sort-handle" />

        <!-- Title -->
        <k-button
          :icon="isOpen ? 'angle-down' : 'angle-right'"
          class="k-builder-block-title px-3 text-left truncate"
        >
          {{ title }}
        </k-button>

        <!-- Options -->
        <k-options-dropdown
          :options="options"
          @option="onOption"
        />
      </header>

      <footer
        v-if="isOpen"
        class="k-builder-block-fields bg-background p-4"
      >
        <!-- Fields -->
        <k-fieldset
          ref="fieldset"
          v-model="block"
          :fields="fields"
          :validate="true"
          @input="onInput"
        />
      </footer>

    </article>
  </k-column>
</template>

<script>

export default {
  inheritAttrs: false,
  props: {
    name: String,
    label: String,
    fields: {
      type: Object,
      default: () => {
        return {};
      }
    },
    value: {
      type: Object,
      default: () => {
        return {};
      }
    },
    width: String
  },
  data() {
    return {
      isOpen: false,
      block: this.value
    }
  },
  watch: {
    value(value) {
      this.block = value;
    }
  },
  computed: {
    options() {
      return [
        {
          icon: 'preview',
          text: 'Preview'
        },
        {
          icon: 'edit',
          text: 'Edit block'
        },
        '-',
        {
          icon: 'angle-up',
          text: 'Insert above'
        },
        {
          icon: 'angle-down',
          text: 'Insert below'
        },
        {
          icon: 'copy',
          text: this.$t("duplicate")
        },
        '-',
        {
          icon: 'trash',
          text: 'Delete block'
        },
      ];
    },
    title() {
      if (this.label) {
        return this.$helper.string.template(this.label, this.block);
      }

      return this.name;
    }
  },
  methods: {
    onInput() {
      this.$emit("input", this.block);
    },
    onOption(option) {
      switch(option.icon) {
        case "preview":
          this.$emit("preview");
          break;
        case "edit":
          this.open();
          break;
        case "angle-up":
          this.$emit("insert", 0);
          break;
        case "angle-down":
          this.$emit("insert", 1);
          break;
        case "copy":
          this.$emit("duplicate");
          break;
        case "trash":
          this.$emit("remove");
          break;
      }
    },
    open() {
      this.isOpen = true;
    },
    toggle() {
      if (this.isOpen) {
        this.isOpen = false;
      } else {
        this.open();
      }
    }
  }
}
</script>

<style lang="scss">
.k-builder-block-sort-handle {
  border-right: 1px solid $color-background;
}
.k-builder-block-title {
  flex-grow: 1;
}
.k-builder-block-fields {
  border-bottom: 2px solid $color-white;
}
</style>
