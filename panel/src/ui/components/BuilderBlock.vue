<template>
  <article class="k-builder-block">
    
    <!-- Sort handle -->
    <k-sort-handle
      class="k-builder-block-sort-handle"
    />

    <div class="k-builder-block-content bg-white shadow p-2px mb-2 rounded-sm">
      <header class="flex items-center px-3 cursor-pointer" @click="toggle">
        <k-button 
          :icon="isOpen ? 'angle-down' : 'angle-right'" 
          class="k-builder-block-title text-left"
        >
          {{ title }}
        </k-button>
        <k-options-dropdown
          :options="options"
          @option="onOption"
        />
      </header>

      <footer 
        v-if="isOpen" 
        class="k-builder-block-fields bg-background p-4"
      >
        <k-fieldset
          ref="fieldset"
          v-model="block"
          :fields="fields"
          :validate="true"
          @input="onInput"
        />
      </footer>
    </div>

  </article>
</template>

<script>

export default {
  inheritAttrs: false,
  props: {
    name: String,
    label: String,
    index: Number,
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
    }
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
          text: 'Insert below',
          indexOffset: 1
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
      this.$emit("current", option.indexOffset || 0);

      switch(option.icon) {
        case "preview":
          this.$emit("preview");
          break;
        case "edit":
          this.open();
          break;
        case "angle-up":
          this.$emit("insert");
          break;
        case "angle-down":
          this.$emit("insert");
          break;
        case "trash":
          this.$emit("remove");
          break;
      }
    },
    open() {
      this.isOpen = true;
      this.$nextTick(() => {
        this.$refs.fieldset.focus();
      });
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
.k-builder-block-sort-handle.k-sort-handle {
  position: absolute;
  opacity: 0;
  width: 4rem;
  height: 2.5rem;
  left: 0;
  z-index: 2;
}
.k-builder-block:hover .k-builder-block-sort-handle {
  opacity: 1;
}
.k-builder-block-title {
  flex-grow: 1;
}
</style>
