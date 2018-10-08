<template>
  <k-field v-bind="$props" class="k-pages-field">
    <k-button
      v-if="more"
      slot="options"
      icon="add"
      @click="open"
    >
      {{ $t('select') }}
    </k-button>
    <template v-if="selected.length">
      <k-draggable
        :element="elements.list"
        :list="selected"
        @end="onInput"
      >
        <component
          v-for="(page, index) in selected"
          :is="elements.item"
          :key="page.id"
          :text="page.title"
          :link="$api.pages.link(page.id)"
          :icon="{
            type: 'page',
            back: 'black'
          }"
        >
          <k-button slot="options" icon="remove" @click="remove(index)" />
        </component>
      </k-draggable>
    </template>
    <k-empty v-else icon="page" @click="open">
      {{ $t('field.pages.empty') }}
    </k-empty>
    <k-pages-dialog ref="selector" @submit="select" />
  </k-field>
</template>

<script>
import Field from "../Field.vue";

export default {
  inheritAttrs: false,
  props: {
    ...Field.props,
    max: Number,
    multiple: Boolean,
    value: {
      type: Array,
      default() {
        return [];
      }
    }
  },
  data() {
    return {
      layout: "list",
      selected: this.value,
    };
  },
  computed: {
    elements() {
      return {
        list: "k-list",
        item: "k-list-item"
      };
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
        selected: this.selected.map(page => page.id),
      });
    },
    remove(index) {
      this.selected.splice(index, 1);
      this.onInput();
    },
    focus() {

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
