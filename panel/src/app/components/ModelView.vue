<template>
  <k-view>
    <k-header
      :editable="rename"
      :tab="tab"
      :tabs="tabs"
      @edit="$emit('rename')"
    >
      {{ title }}
      <k-button-group slot="left">
        <slot name="options" />
        <k-languages-dropdown />
      </k-button-group>
      <k-prev-next
        slot="right"
        :prev="prev"
        :next="next"
      />
    </k-header>
    <k-sections :columns="columns" />
  </k-view>
</template>

<script>
export default {
  props: {
    columns: {
      type: Array,
      default() {
        return [];
      }
    },
    next: {
      type: String,
    },
    prev: {
      type: String,
    },
    rename: {
      type: Boolean,
      default: true
    },
    tab: {
      type: String,
      default: ""
    },
    tabs: {
      type: Array,
      default() {
        return [];
      }
    },
    title: {
      type: String,
      default: ""
    }
  },
  computed: {
    options() {
      return [];
    }
  }
};
</script>
