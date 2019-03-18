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
        :handle="true"
        :list="selected"
        :data-size="size"
        @end="onInput"
      >
        <component
          v-for="(page, index) in selected"
          :is="elements.item"
          :key="page.id"
          :sortable="selected.length > 1"
          :text="page.text"
          :info="page.info"
          :link="page.link"
          :icon="page.icon"
          :image="page.image"
        >
          <k-button slot="options" icon="remove" @click="remove(index)" />
        </component>
      </k-draggable>
    </template>
    <k-empty
      v-else
      :layout="layout"
      icon="page"
      @click="open"
    >
      {{ empty || $t('field.pages.empty') }}
    </k-empty>
    <k-pages-dialog ref="selector" @submit="select" />
  </k-field>
</template>

<script>
import Field from "../Field.vue";
import clone from "@/helpers/clone.js";

export default {
  inheritAttrs: false,
  props: {
    ...Field.props,
    empty: String,
    layout: String,
    max: Number,
    multiple: Boolean,
    size: String,
    value: {
      type: Array,
      default() {
        return [];
      }
    }
  },
  data() {
    return {
      selected: this.value
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
        }
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
        endpoint: this.endpoints.field,
        max: this.max,
        multiple: this.multiple,
        selected: clone(this.selected)
      });
    },
    remove(index) {
      this.selected.splice(index, 1);
      this.onInput();
    },
    focus() {},
    onInput() {
      this.$emit("input", this.selected);
    },
    select(files) {
      this.selected = files;
      this.onInput();
    }
  }
};
</script>
