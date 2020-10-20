<template>
  <div class="k-block-generic-box">
    <k-block-header
      :icon="fieldset.icon"
      :is-hidden="isHidden"
      :is-open="isOpen || isSticky"
      :label="previewLabel"
      :name="previewName"
      :tabs="tabs"
      :tab="tab"
      @open="open"
      @show="$emit('show')"
      @toggle="openOrClose"
    />
    <k-block-form
      v-if="isOpen"
      ref="form"
      :fields="fields"
      :value="content"
      @input="$emit('update', $event)"
    />
  </div>
</template>

<script>
export default {
  inheritAttrs: false,
  props: {
    attrs: [Array, Object],
    compact: Boolean,
    content: [Array, Object],
    endpoints: Object,
    fieldset: Object,
    id: String,
    isFull: Boolean,
    isHidden: Boolean,
    isOpen: Boolean,
    isSticky: Boolean,
    name: String,
    type: String,
  },
  data() {
    return {
      tab: null
    };
  },
  computed: {
    fields() {

      const tabId = this.tab || null;
      const tabs = this.fieldset.tabs;
      const tab = tabs[tabId] || Object.values(tabs)[0];
      const fields = tab.fields || {};

      if (Object.keys(fields).length === 0) {
        return {
          noFields: {
            type: "info",
            text: "This block has no fields"
          }
        };
      }

      Object.keys(fields).forEach(name => {
        let field = fields[name];

        field.section = this.name;
        field.endpoints = {
          field: this.endpoints.field + "/fieldsets/" + this.type + "/fields/" + name,
          section: this.endpoints.section,
          model: this.endpoints.model
        };

        fields[name] = field;
      });

      return fields;

    },
    firstTab() {
      return this.tabs[0];
    },
    previewLabel() {
      if (this.fieldset.label.length === 0) {
        return false;
      }

      if (this.fieldset.label === this.fieldset.name) {
        return false;
      }

      return this.$helper.string.template(this.fieldset.label, this.content);
    },
    previewName() {
      return this.fieldset.name;
    },
    tabs() {
      return Object.values(this.fieldset.tabs);
    }
  },
  methods: {
    close() {
      this.$emit("close");
    },
    focus() {
      if (this.$refs.form && typeof this.$refs.form.focus === "function") {
        this.$refs.form.focus();
      }
    },
    open(tab, focus = true) {
      this.tab = tab || this.firstTab.name;
      this.$emit("open");

      if (focus !== false) {
        setTimeout(() => {
          this.focus();
        }, 1);
      }
    },
    openOrClose(tab, focus = true) {
      if (this.isOpen === false) {
        this.open(tab);
      } else {
        this.close();
      }
    },
    remove() {
      this.$refs.remove.close();
      this.$emit("remove", this.id);
    }
  }
}
</script>

<style lang="scss">
.k-block-generic {
  padding: .75rem 0;
}
.k-block-generic-box {
  background: $color-white;
  box-shadow: $shadow;
  border: 1px solid $color-gray-300;
  border-radius: $rounded-sm;
}
</style>
