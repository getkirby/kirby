<template>
  <div
    :data-compact="compact"
    :data-disabled="fieldset.disabled"
    :data-hidden="isHidden"
    :data-open="isOpen"
    :data-translate="fieldset.translate"
    :class="'k-builder-block k-builder-fieldset-' + type"
    @mouseenter="isHovered = true"
    @mouseleave="onMouseleave"
  >
    <div class="k-builder-block-header" @click.prevent="openOrClose">
      <k-sort-handle :icon="isHovered ? 'sort' : fieldset.icon || 'sort'" class="k-builder-block-handle" />

      <div class="k-builder-block-preview">
        <span class="k-builder-block-name">
          {{ name }}
        </span>
        <span v-if="label && !isOpen" class="k-builder-block-label">
          {{ label }}
        </span>
      </div>

      <nav
        v-if="isOpen && hasTabs"
        class="k-builder-block-tabs"
      >
        <k-button
          v-for="(tab, tabId) in fieldset.tabs"
          :key="tabId"
          :icon="tab.icon"
          :current="isCurrentTab(tabId)"
          class="k-builder-block-tab"
          @click.stop="open(tabId)"
        >
          {{ tab.label }}
        </k-button>
      </nav>

      <k-button
        v-if="isHidden"
        class="k-builder-block-status"
        icon="hidden"
        @click.stop="hideOrShow"
      />

      <k-dropdown class="k-builder-block-options">
        <k-button
          class="k-builder-block-options-toggle"
          icon="dots"
          @click="$refs.options.toggle()"
        />
        <k-dropdown-content ref="options" align="right">
          <k-dropdown-item :disabled="isFull" icon="angle-up" @click="$emit('prepend')">
            {{ $t("insert.before") }}
          </k-dropdown-item>
          <k-dropdown-item :disabled="isFull" icon="angle-down" @click="$emit('append')">
            {{ $t("insert.after") }}
          </k-dropdown-item>
          <template v-if="hasTabs">
            <hr>
            <k-dropdown-item
              v-for="(tab, tabId) in fieldset.tabs"
              :key="tabId"
              :icon="tab.icon || 'settings'"
              @click="open(tabId)"
            >
              {{ tab.label }}
            </k-dropdown-item>
          </template>
          <hr>
          <k-dropdown-item :icon="isHidden ? 'preview' : 'hidden'" @click="hideOrShow">
            {{ isHidden === true ? $t('show') : $t('hide') }}
          </k-dropdown-item>
          <k-dropdown-item :disabled="isFull" icon="copy" @click="$emit('duplicate')">
            {{ $t("duplicate") }}
          </k-dropdown-item>
          <hr>
          <k-dropdown-item icon="trash" @click="$refs.remove.open()">
            {{ $t("delete") }}
          </k-dropdown-item>
        </k-dropdown-content>
      </k-dropdown>
    </div>

    <div class="k-builder-block-body" v-if="isOpen">
      <k-fieldset
        ref="fields"
        :fields="fields"
        :value="content"
        class="k-builder-block-form"
        @input="$emit('update', $event)"
      />
    </div>

    <k-remove-dialog ref="remove" @submit="remove">
      {{ $t("field.builder.delete.confirm") }}
    </k-remove-dialog>

  </div>
</template>

<script>
export default {
  inheritAttrs: false,
  props: {
    attrs: [Array, Object],
    compact: Boolean,
    content: Object,
    endpoints: Object,
    fieldset: Object,
    id: String,
    isFull: Boolean,
    name: String,
    tabs: Object,
    type: String,
  },
  data() {
    return {
      isHovered: false,
      isOpen: false,
      tab: null,
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
    hasTabs() {
      return Object.keys(this.fieldset.tabs).length > 1
    },
    isHidden() {
      return this.attrs.hide === true;
    },
    label() {
      if (this.fieldset.label.length === 0) {
        return false;
      }

      if (this.fieldset.label === this.fieldset.name) {
        return false;
      }

      return this.$helper.string.template(this.fieldset.label, this.content);
    },
    name() {
      return this.fieldset.name;
    }
  },
  methods: {
    close() {
      this.isOpen = false;
      this.$emit("close");
    },
    hideOrShow() {
      if (this.isHidden === true) {
        this.$emit("show");
      } else {
        this.$emit("hide");
      }
    },
    isCurrentTab(tabId) {
      if (this.tab === undefined) {
        this.tab = tabId;
        return true;
      }
      return this.tab === tabId;
    },
    onMouseleave() {
      this.isHovered = false
      this.$refs.options.close();
    },
    open(tab, focus = true) {
      this.tab = tab;
      this.isOpen = true;
      this.$emit("open");

      if (focus !== false) {
        setTimeout(() => {
          this.$refs.fields.focus();
        });
      }
    },
    openOrClose() {
      if (this.isOpen === false) {
        this.open();
      } else {
        this.close();
      }
    },
    remove() {
      this.$refs.remove.close();
      this.$emit("remove", this.id);
    }
  }
};
</script>

<style lang="scss">
.k-builder-block {
  position: relative;
  background: $color-white;
  box-shadow: $shadow;
  margin-bottom: 1px;
  border-radius: $rounded-xs;
}
.k-builder-block:last-child {
  margin-bottom: 0;
}
.k-builder-block[data-disabled] {
  cursor: not-allowed;
  opacity: .4;
}
.k-builder-block[data-disabled] * {
  pointer-events: none;
}
.k-builder-block[data-open] {
  background: $color-background;
  box-shadow: $shadow, #fff 0 0 0 2px inset;
}
.k-builder-block-header {
  cursor: pointer;
  display: flex;
  align-items: stretch;
}
.k-builder-block[data-open] > .k-builder-block-header {
  border-bottom: 1px solid $color-gray-300;
}
.k-builder-block-handle.k-sort-handle {
  width: 2.5rem;
  height: 36px;
}
.k-builder-block-handle.k-sort-handle > svg {
  opacity: .25;
}
.k-builder-block-handle.k-sort-handle:hover > svg {
  opacity: 1;
}
.k-builder-block-preview {
  flex-grow: 1;
  min-width: 0;
  display: flex;
  align-items: center;
  line-height: 36px;
  font-size: $text-sm;
  height: 36px;
}
.k-builder-block-name {
  margin-right: .5rem;
}
.k-builder-block-label {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  color: $color-gray-500;
  max-width: 50%;
}

.k-builder-block[data-hidden]:not([data-open]) {
  background: rgba($color-white, .325);
}
.k-builder-block[data-hidden] .k-builder-block-status  {
  opacity: .325;
}
.k-builder-block[data-hidden] .k-builder-block-label {
  color: $color-gray-400;
}

.k-builder-block-status {
  line-height: 1;
  height: 36px;
  width: 2.5rem;
}
.k-builder-block-tabs {
  display: none;
  align-items: center;
  height: 36px;
  margin-right: .75rem;
}
@media screen and (min-width: $breakpoint-md) {
  .k-builder-block .k-builder-block-tabs {
    display: flex;
  }
}
.k-button.k-builder-block-tab {
  position: relative;
  display: flex;
  align-items: center;
  line-height: 1;
  font-size: $text-sm;
  color: $color-gray-600;
  padding: 0 .75rem;
  height: 100%;
}

.k-builder-block-tab[aria-current] {
  color: $color-black;
}
.k-builder-block-tab[aria-current]::after {
  position: absolute;
  bottom: -1px;
  left: .75rem;
  right: .75rem;
  height: 2px;
  background: $color-black;
  content: "";
}
.k-builder-block-options {
  width: 2.5rem;
  height: 36px;
  flex-shrink: 0;
}

.k-builder-block[data-compact] .k-builder-block-options {
  opacity: 0;
}
.k-builder-block[data-compact]:hover .k-builder-block-options {
  opacity: 1;
}

.k-builder-block-options-toggle {
  display: flex;
  width: 100%;
  height: 100%;
  align-items: center;
  line-height: 1;
}
.k-builder-block-form {
  padding: 1.5rem 2.5rem 2.5rem;
}
</style>
