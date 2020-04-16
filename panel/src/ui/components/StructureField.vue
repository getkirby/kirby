<template>
  <k-field
    :input="_uid"
    v-bind="$props"
    class="k-structure-field"
  >

    <!-- Add button -->
    <template slot="options">
      <k-button
        v-if="more && currentIndex === null"
        :id="_uid"
        ref="add"
        icon="add"
        @click="addRow"
      >
        {{ $t("add") }}
      </k-button>
    </template>

    <!-- Table -->
    <k-table
      :columns="columns"
      :rows="rows"
      :sortable="sortable"
      :options="options"
      @cell="editRow($event.row, $event.rowIndex)"
      @sort="onSort"
      @option="onOption"
    />

    <!-- Dialogs -->
    <k-remove-dialog
      ref="removeDialog"
      :text="$t('field.structure.delete.confirm')"
      @submit="removeRow"
    />

    <k-drawer
      ref="editDialog"
      :title="label + ' / Edit row'"
      :submit-button="false"
      :cancel-button="false"
    >
      <k-form
        :fields="editFields"
        v-model="currentModel"
      />
    </k-drawer>

  </k-field>
</template>

<script>
import Field from "./Field.vue";

export default {
  inheritAttrs: false,
  props: {
    ...Field.props,
    columns: Object,
    duplicate: {
      type: Boolean,
      default: true
    },
    empty: String,
    fields: Object,
    limit: Number,
    max: Number,
    min: Number,
    sortable: {
      type: Boolean,
      default: true
    },
    sortBy: String,
    value: {
      type: Array,
      default() {
        return [];
      }
    }
  },
  data() {
    return {
      currentIndex: null,
      currentModel: {},
      rows: this.value,
      trash: null
    };
  },
  computed: {
    editFields() {
      return {
        platform: {
          label: "Platform",
          type: "text",
          width: "1/2"
        },
        url: {
          label: "URL",
          type: "url",
          width: "1/2"
        },
      };
    },
    isSortable() {
      if (this.sortBy) {
        return false;
      }

      if (this.limit) {
        return false;
      }

      if (this.disabled === true) {
        return false;
      }

      if (this.items.length <= 1) {
        return false;
      }

      if (this.sortable === false) {
        return false;
      }

      return true;
    },
    more() {
      if (this.disabled === true) {
        return false;
      }

      if (this.max && this.rows.length >= this.max) {
        return false;
      }

      return true;
    },
    options() {
      return [
        { icon: "edit", text: this.$t("edit"), click: "editRow" },
        { icon: "copy", text: this.$t("duplicate"), click: "duplicateRow" },
        { icon: "trash", text: this.$t("delete"), click: "confirmRemoveRow" },
      ];
    }
  },
  methods: {
    addRow() {

    },
    confirmRemoveRow(row, rowIndex) {
      this.$refs.removeDialog.open();
      this.trash = rowIndex;
    },
    duplicateRow(row, rowIndex) {
      this.rows.push(this.rows[rowIndex]);
      this.onInput();
    },
    editRow(row, rowIndex) {
      this.currentIndex = rowIndex;
      this.currentModel = row;

      this.$refs.editDialog.open();
    },
    focus() {
    },
    onInput() {
      this.$emit("input", this.rows);
    },
    onOption(option, row, rowIndex) {
      if (typeof this[option] === "function") {
        this[option](row, rowIndex);
      }
    },
    onSort() {
      this.onInput();
    },
    removeRow() {
      if (this.trash === null) {
        return false;
      }

      this.rows.splice(this.trash, 1);
      this.trash = null;
      this.$refs.removeDialog.close();

      this.onInput();

      // if (this.paginatedItems.length === 0 && this.page > 1) {
      //   this.page--;
      // }

      this.rows = this.sort(this.rows);
    },
    sort(rows) {
      if (!this.sortBy) {
        return rows;
      }

      return rows.sortBy(this.sortBy);
    },
  }
}
</script>
