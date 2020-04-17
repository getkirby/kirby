<template>
  <k-field
    :input="_uid"
    v-bind="$props"
    class="k-structure-field"
  >

    <!-- Add button -->
    <template slot="options">
      <k-button
        v-if="more"
        :id="_uid"
        ref="addButton"
        icon="add"
        @click="openNewRowDialog"
      >
        {{ $t("add") }}
      </k-button>
    </template>

    <!-- Table -->
    <k-table
      v-if="rows.length"
      :columns="columns"
      :rows="rows"
      :sortable="isSortable"
      :options="options"
      @cell="openEditRowDialog($event.row, $event.rowIndex)"
      @sort="onSort"
      @option="onOption"
    />

    <!-- Empty state -->
    <k-empty
      v-else
      icon="list-bullet"
      @click="openNewRowDialog"
    >
      {{ empty }}
    </k-empty>

    <!-- New Row Dialog -->
    <k-drawer
      ref="newRowDialog"
      :title="label + ' / Add'"
    >
      <k-form
        :fields="newRowFields"
        v-model="newRowModel"
        @cancel="closeNewRowDialog"
        @submit="submitNewRow"
      />
    </k-drawer>

    <!-- Edit Dialog -->
    <k-drawer
      ref="editRowDialog"
      :title="label + ' / Edit'"
    >
      <k-form
        :fields="editRowFields"
        v-model="editRowModel"
        @cancel="closeEditRowDialog"
        @submit="submitEditRow"
      />
    </k-drawer>

    <!-- Remove Row Dialog -->
    <k-remove-dialog
      ref="removeRowDialog"
      :text="$t('field.structure.delete.confirm')"
      @cancel="closeRemoveRowDialog"
      @submit="submitRemoveRow"
    />

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
    empty: {
      type: String,
      default() {
        return this.$t("field.structure.empty");
      }
    },
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
      editRowIndex: null,
      editRowModel: {},
      newRowIndex: null,
      newRowModel: {},
      removeRowIndex: null,
      removeRowModel: {},
      rows: this.sanitize(this.value),
    };
  },
  computed: {
    editRowFields() {
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

      if (this.rows.length <= 1) {
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
    newRowFields() {
      return this.editRowFields;
    },
    options() {
      return [
        { icon: "edit", text: this.$t("edit"), click: "openEditRowDialog" },
        { icon: "copy", text: this.$t("duplicate"), click: "duplicateRow" },
        { icon: "trash", text: this.$t("delete"), click: "openRemoveRowDialog" },
      ];
    }
  },
  watch: {
    value(value) {
      if (value != this.rows) {
        this.rows = this.sanitize(value);
      }
    }
  },
  methods: {
    closeNewRowDialog() {
      this.newRowIndex = null;
      this.newRowModel = {};
      this.$refs.newRowDialog.close();
    },
    closeEditRowDialog() {
      this.editRowIndex = null;
      this.editRowModel = {};
      this.$refs.editRowDialog.close();
    },
    closeRemoveRowDialog() {
      this.removeRowIndex = null;
      this.removeRowModel = {};
      this.$refs.removeRowDialog.close();
    },
    duplicateRow(row, rowIndex) {
      this.rows.push(this.$helper.clone(this.rows[rowIndex]));
      this.rows = this.sort(this.rows);
      this.onInput();
    },
    focus() {
      if (this.$refs.addButton && this.$refs.addButton.focus) {
        this.$refs.addButton.focus();
      }
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
    openEditRowDialog(row, rowIndex) {
      this.editRowIndex = rowIndex;
      this.editRowModel = this.$helper.clone(row);
      this.$refs.editRowDialog.open();
    },
    openNewRowDialog() {
      this.newRowIndex = 0;
      this.newRowModel = {};
      this.$refs.newRowDialog.open();
    },
    openRemoveRowDialog(row, rowIndex) {
      this.removeRowIndex = rowIndex;
      this.removeRowModel = row;
      this.$refs.removeRowDialog.open();
    },
    sanitize(rows) {
      if (Array.isArray(rows) === false) {
        return [];
      }

      return this.sort(rows);
    },
    sort(rows) {
      if (!this.sortBy) {
        return rows;
      }

      return rows.sortBy(this.sortBy);
    },
    submitEditRow() {
      this.$set(this.rows, this.editRowIndex, this.editRowModel);
      this.rows = this.sort(this.rows);
      this.onInput();
      this.closeEditRowDialog();
    },
    submitNewRow() {
      this.rows.push(this.newRowModel);
      this.rows = this.sort(this.rows);
      this.onInput();
      this.closeNewRowDialog();
    },
    submitRemoveRow() {
      if (this.removeRowIndex === null) {
        return false;
      }

      this.rows.splice(this.removeRowIndex, 1);
      this.onInput();
      this.closeRemoveRowDialog();

      // if (this.paginatedItems.length === 0 && this.page > 1) {
      //   this.page--;
      // }

      this.rows = this.sort(this.rows);
    },
  }
}
</script>
