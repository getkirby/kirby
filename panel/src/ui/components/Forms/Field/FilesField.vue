<template>
  <k-field v-bind="$props" class="k-files-field">
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
        :options="dragOptions"
        @start="onStart"
        @end="onInput"
      >
        <component
          v-for="(file, index) in selected"
          :is="elements.item"
          :key="file.filename"
          :sortable="true"
          :text="file.filename"
          :link="file.link"
          :image="file.thumb ? { url: file.thumb, back: 'pattern' } : null"
          :icon="{ type: 'file', back: 'pattern' }"
        >
          <k-button
            slot="options"
            :tooltip="$t('remove')"
            icon="remove"
            @click="remove(index)"
          />
        </component>
      </k-draggable>
    </template>
    <k-empty
      v-else
      :layout="layout"
      icon="image"
      @click="open"
    >
      {{ $t('field.files.empty') }}
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
    dragOptions() {
      return {
        forceFallback: true,
        fallbackClass: "sortable-fallback",
        handle: ".k-sort-handle"
      };
    },
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
        max: this.max,
        multiple: this.multiple,
        parent: this.parent,
        selected: this.selected.map(file => file.id)
      });
    },
    remove(index) {
      this.selected.splice(index, 1);
      this.onInput();
    },
    focus() {},
    onStart() {
      this.$store.dispatch("drag", {});
    },
    onInput() {
      this.$store.dispatch("drag", null);
      this.$emit("input", this.selected);
    },
    select(files) {
      this.selected = files;
      this.onInput();
    }
  }
};
</script>
