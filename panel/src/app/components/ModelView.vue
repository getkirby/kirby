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
  </k-view>
</template>

<script>
export default {
  props: {
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
