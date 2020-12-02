<template>
  <k-drawer
    ref="drawer"
    :icon="icon"
    :tabs="tabs"
    :tab="tab"
    :title="title"
    class="k-form-drawer"
    @close="$emit('close')"
    @open="$emit('open')"
    @tab="tab = $event"
  >
    <template #options>
      <slot name="options" />
    </template>
    <template #default>
      <k-box v-if="Object.keys(fields).length === 0" theme="info">
        {{ empty }}
      </k-box>
      <k-form
        v-else
        ref="form"
        :autofocus="true"
        :fields="fields"
        :value="$helper.clone(value)"
        @input="$emit('input', $event)"
      />
    </template>
  </k-drawer>
</template>

<script>
export default {
  inheritAttrs: false,
  props: {
    empty: {
      type: String,
      default() {
        return "Missing field setup"
      }
    },
    icon: String,
    tabs: Object,
    title: String,
    type: String,
    value: Object
  },
  data() {
    return {
      tab: null
    };
  },
  computed: {
    fields() {
      const tabId  = this.tab || null;
      const tabs   = this.tabs;
      const tab    = tabs[tabId] || this.firstTab;
      const fields = tab.fields || {};

      return fields;
    },
    firstTab() {
      return Object.values(this.tabs)[0];
    }
  },
  methods: {
    close() {
      this.$refs.drawer.close();
    },
    focus() {
      if (this.$refs.form && typeof this.$refs.form.focus === "function") {
        this.$refs.form.focus();
      }
    },
    open(tab, focus = true) {
      this.$refs.drawer.open();
      this.tab = tab || this.firstTab.name;

      if (focus !== false) {
        setTimeout(() => {
          this.focus();
        }, 1);
      }
    }
  }
}
</script>
