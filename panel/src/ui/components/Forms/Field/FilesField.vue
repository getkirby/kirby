<template>
  <k-field v-bind="$props" class="k-files-field">
    <k-button
      v-if="more"
      slot="options"
      icon="add"
      @click="open"
    >
      Select a file
    </k-button>
    <template v-if="selected.length">
      <k-draggable
        :element="elements.list"
        :list="selected"
        @end="onInput"
      >
        <component
          v-for="(file, index) in selected"
          :is="elements.item"
          :key="file.filename"
          :text="file.filename"
          :link="file.link"
          :image="{ url: file.url }"
        >
          <k-button slot="options" icon="remove" @click="remove(index)" />
        </component>
      </k-draggable>
    </template>
    <k-empty v-else icon="image" @click="open">
      No files selected yet
    </k-empty>
    <k-files-dialog ref="selector" @submit="select" />
  </k-field>
</template>

<script>
import Field from "../Field.vue";

export default {
  inheritAttrs: false,
  props: {
    ...Field.props,
    layout: String,
    max: Number,
    multiple: Boolean,
    parent: String,
    value: {
      type: Array,
    }
  },
  data() {
    return {
      selected: this.value,
    };
  },
  computed: {
    elements() {
      const layouts = {
        cards: {
          list: "k-cards",
          item: "k-card"
        },
        list: {
          list: "k-list",
          item: "k-list-item"
        },
      };

      if (layouts[this.layout]) {
        return layouts[this.layout];
      }

      return layouts["list"];
    },
    more() {
      if (!this.max) {
        return true;
      }

      return this.max > this.selected.length;
    }
  },
  watch: {
    value(value) {
      this.selected = value;
    }
  },
  methods: {
    open() {
      this.$refs.selector.open({
        max: this.max,
        multiple: this.multiple,
        parent: this.parent,
        selected: this.selected.map(file => file.id),
      });
    },
    remove(index) {
      this.selected.splice(index, 1);
      this.onInput();
    },
    focus() {
      this.$refs.input.focus();
    },
    onInput() {
      this.$emit("input", this.selected);
    },
    select(files) {
      this.selected = files;
      this.onInput();
    },
  }
}
</script>

<style lang="scss">

.k-files-picker .k-dialog-box {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
}
.k-files-picker .k-dialog-body {
  max-height: none;
  flex-grow: 1;
}

</style>
