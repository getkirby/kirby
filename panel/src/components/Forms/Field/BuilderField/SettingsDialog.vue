<template>
  <k-dialog
    ref="dialog"
    :cancel-button="false"
    :submit-button="false"
    class="k-builder-block-settings-dialog"
    size="large"
  >
    <nav class="k-builder-block-settings-tabs">
      <div>
        <h2>{{ $t('settings') }}</h2>
        <k-button
          v-for="(tab, tabKey) in settings"
          :key="tabKey"
          :icon="tab.icon"
          :current="currentTab == tab"
          class="k-builder-block-settings-tab"
          @click="currentTab = tab"
        >
          {{ tab.label }}
        </k-button>
      </div>
      <div class="k-builder-block-settings-visibility">
        <k-toggle-input v-model="visible" :text="['Draft', 'Published']" />
      </div>
    </nav>
    <div class="k-builder-block-settings-body">
      <k-form
        :fields="currentTab.fields"
        :value="attrs"
        @input="$emit('input', $event)"
        @submit="onSubmit"
      />
    </div>
  </k-dialog>
</template>

<script>
import DialogMixin from "@/mixins/dialog.js";

export default {
  inheritAttrs: false,
  mixins: [DialogMixin],
  props: {
    block: Object,
    settings: [Object, Array],
  },
  data() {
    return {
      currentTab: Object.values(this.settings)[0],
      visible: false
    }
  },
  computed: {
    attrs() {
      if (Array.isArray(this.block.attrs)) {
        return {
          class: "",
          id: "",
          name: ""
        };
      }

      return this.block.attrs;
    }
  },
  methods: {
    onSubmit() {
      this.$refs.dialog.close();
      this.$emit("submit");
    }
  }
};
</script>

<style lang="scss">

.k-builder-block-settings-dialog .k-dialog-body {
  padding: 0;
  display: flex;
}
.k-builder-block-settings-tabs {
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  flex-grow: 1;
  flex-basis: 25%;
  background: $color-white;
}
.k-builder-block-settings-tabs h2 {
  font-size: $font-size-tiny;
  font-weight: 400;
  text-transform: uppercase;
  letter-spacing: .1em;
  padding: .75rem;
  color: $color-dark-grey;
}
.k-builder-block-settings-tab {
  width: 100%;
  text-align: left;
  padding: .5rem .75rem;
}
.k-builder-block-settings-tab[aria-current] {
  background: rgba(#000, .05);
}
.k-builder-block-settings-visibility {
  font-size: $font-size-small;
  border-top: 1px solid $color-background;
}
.k-builder-block-settings-visibility .k-toggle-input {
  padding: .75rem;
}
.k-builder-block-settings-visibility .k-toggle-input-label {
  padding-left: .5rem;
}
.k-builder-block-settings-body {
  flex-grow: 3;
  flex-basis: 75%;
  flex-shrink: 0;
  padding: 1.5rem 3rem 2rem;
}

</style>
