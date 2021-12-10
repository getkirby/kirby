<template>
  <section
    :data-selected="isSelected"
    class="k-layout"
    tabindex="0"
    @click="$emit('select')"
  >
    <k-grid class="k-layout-columns">
      <k-layout-column
        v-for="(column, columnIndex) in columns"
        :key="column.id"
        :endpoints="endpoints"
        :fieldset-groups="fieldsetGroups"
        :fieldsets="fieldsets"
        v-bind="column"
        @input="
          $emit('updateColumn', {
            column,
            columnIndex,
            blocks: $event
          })
        "
      />
    </k-grid>
    <nav v-if="!disabled" class="k-layout-toolbar">
      <k-button
        v-if="settings"
        :tooltip="$t('settings')"
        class="k-layout-toolbar-button"
        icon="settings"
        @click="$refs.drawer.open()"
      />

      <k-dropdown>
        <k-button
          class="k-layout-toolbar-button"
          icon="angle-down"
          @click="$refs.options.toggle()"
        />
        <k-dropdown-content ref="options" align="right">
          <k-dropdown-item icon="angle-up" @click="$emit('prepend')">
            {{ $t("insert.before") }}
          </k-dropdown-item>
          <k-dropdown-item icon="angle-down" @click="$emit('append')">
            {{ $t("insert.after") }}
          </k-dropdown-item>
          <hr />
          <k-dropdown-item
            v-if="settings"
            icon="settings"
            @click="$refs.drawer.open()"
          >
            {{ $t("settings") }}
          </k-dropdown-item>
          <k-dropdown-item icon="copy" @click="$emit('duplicate')">
            {{ $t("duplicate") }}
          </k-dropdown-item>
          <hr />
          <k-dropdown-item
            icon="trash"
            @click="$refs.confirmRemoveDialog.open()"
          >
            {{ $t("field.layout.delete") }}
          </k-dropdown-item>
        </k-dropdown-content>
      </k-dropdown>
      <k-sort-handle />
    </nav>

    <k-form-drawer
      v-if="settings"
      ref="drawer"
      :tabs="tabs"
      :title="$t('settings')"
      :value="attrs"
      class="k-layout-drawer"
      icon="settings"
      @input="$emit('updateAttrs', $event)"
    />

    <k-remove-dialog
      ref="confirmRemoveDialog"
      :text="$t('field.layout.delete.confirm')"
      @submit="$emit('remove')"
    />
  </section>
</template>

<script>
import Column from "./Column.vue";

/**
 * @internal
 */
export default {
  components: {
    "k-layout-column": Column
  },
  props: {
    attrs: [Array, Object],
    columns: Array,
    disabled: Boolean,
    endpoints: Object,
    fieldsetGroups: Object,
    fieldsets: Object,
    id: String,
    isSelected: Boolean,
    settings: Object
  },
  computed: {
    tabs() {
      let tabs = this.settings.tabs;

      Object.entries(tabs).forEach(([tabName, tab]) => {
        Object.entries(tab.fields).forEach(([fieldName]) => {
          tabs[tabName].fields[fieldName].endpoints = {
            field: this.endpoints.field + "/fields/" + fieldName,
            section: this.endpoints.section,
            model: this.endpoints.model
          };
        });
      });

      return tabs;
    }
  }
};
</script>

<style>
.k-layout {
  --layout-border-color: var(--color-gray-300);
  --layout-toolbar-width: 2rem;

  position: relative;
  padding-inline-end: var(--layout-toolbar-width);
  background: #fff;
  box-shadow: var(--shadow);
}
[data-disabled="true"] .k-layout {
  padding-inline-end: 0;
}
.k-layout:not(:last-of-type) {
  margin-bottom: 1px;
}
.k-layout:focus {
  outline: 0;
}

/** Toolbar **/
.k-layout-toolbar {
  position: absolute;
  inset-block: 0;
  inset-inline-end: 0;
  width: var(--layout-toolbar-width);
  display: flex;
  flex-direction: column;
  font-size: var(--text-sm);
  background: var(--color-gray-100);
  border-inline-start: 1px solid var(--color-light);
  color: var(--color-gray-500);
}
.k-layout-toolbar:hover {
  color: var(--color-black);
}
.k-layout-toolbar-button {
  width: var(--layout-toolbar-width);
  height: var(--layout-toolbar-width);
}
.k-layout-toolbar .k-sort-handle {
  margin-top: auto;
  color: currentColor;
}

/** Columns **/
.k-layout-columns.k-grid {
  grid-gap: 1px;
  background: var(--layout-border-color);
  background: var(--color-gray-300);
}
.k-layout:not(:first-child) .k-layout-columns.k-grid {
  border-top: 0;
}
</style>
