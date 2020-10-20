<template>
  <k-dialog
    ref="dialog"
    :cancel-button="false"
    :submit-button="false"
    class="k-block-selector"
    size="large"
  >
    <k-headline>{{ $t("field.builder.fieldsets.label") }}</k-headline>
    <div class="k-block-types">
      <k-button
        v-for="fieldset in fieldsets"
        :key="fieldset.name"
        :icon="fieldset.icon || 'box'"
        @click="add(fieldset.type)"
      >
        {{ fieldset.name }}
      </k-button>
    </div>
  </k-dialog>
</template>

<script>
export default {
  inheritAttrs: false,
  props: {
    endpoint: String,
    fieldsets: Object,
  },
  data() {
    return {
      index: 0
    }
  },
  methods: {
    add(type) {
      this.$emit("add", type, this.index);
      this.$refs.dialog.close();
    },
    open(index) {
      this.index = index;
      this.$refs.dialog.open();
    }
  }
};
</script>

<style lang="scss">
.k-block-selector .k-headline {
  margin-bottom: .75rem;
  margin-top: -.25rem;
}
.k-block-types {
  display: grid;
  grid-gap: .5rem;
  grid-template-columns: repeat(2, 1fr);
}
.k-block-types .k-button {
  display: grid;
  grid-template-columns: 2rem 1fr;
  align-items: top;
  background: $color-white;
  width: 100%;
  text-align: left;
  box-shadow: $shadow;
  padding: 0 .75rem 0 0;
  line-height: 1.5em;
}
.k-block-types .k-button .k-button-text {
  padding: .5rem 0;
}
.k-block-types .k-button .k-icon {
  width: 38px;
  height: 38px;
}
</style>
