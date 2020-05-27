<template>
  <k-field :input="_uid" v-bind="$props">
    <!-- Add button -->
    <template v-slot:options>
      <k-button
        v-if="more"
        :id="_uid"
        :text="$t('add')"
        ref="addButton"
        icon="add"
        @click="openNewRowDrawer"
      />
    </template>

    <!-- Table -->
    <template v-if="rows.length">
      <k-table
        :columns="columns"
        :rows="paginatedRows"
        :sortable="isSortable"
        :index="(page - 1) * limit + 1"
        :options="options"
        @cell="onCell"
        @sort="onSort"
        @option="onOption"
      />

      <k-pagination
        v-bind="pagination"
        @paginate="page = $event.page"
      />
    </template>

    <!-- Empty state -->
    <k-empty
      v-else
      icon="list-bullet"
      @click="openNewRowDrawer"
    >
      {{ empty }}
    </k-empty>

    <!-- New Row Dialog -->
    <k-form-drawer
      ref="newRowDrawer"
      v-model="newRowModel"
      :fields="fields"
      :size="size"
      :submit-button="{ text: $t('add'), color: 'positive' }"
      :title="label + ' / ' + $t('add')"
      @cancel="closeNewRowDrawer"
      @submit="submitNewRow"
    />

    <!-- Edit Dialog -->
    <k-form-drawer
      ref="editRowDrawer"
      v-model="editRowModel"
      :autofocus="editRowModel === null"
      :fields="fields"
      :size="size"
      :submit-button="{ text: $t('confirm'), color: 'positive' }"
      :title="label + ' / ' + $t('edit')"
      @focus="focusEditRowField"
      @cancel="closeEditRowDrawer"
      @submit="submitEditRow"
    >
      <template v-slot:context>
        <k-pagination
          :details="true"
          :page="editRowIndex + 1"
          :limit="1"
          :total="rows.length"
          :dropdown="false"
          direction="vertical"
          @paginate="navigateRowDialog($event.page - 1)"
        />
      </template>
    </k-form-drawer>

    <!-- Remove Row Dialog -->
    <k-dialog
      ref="removeRowDialog"
      :submitButton="{
        icon: 'trash',
        text: 'Delete',
        color: 'red'
      }"
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
    empty: {
      type: String,
      default() {
        return this.$t("field.structure.empty");
      }
    },
    fields: Object,
    /**
     * Show X entries per pagination page
     */
    limit: {
      type: Number,
      default: 0
    },
    /**
     * Maximum count of entries
     */
    max: Number,
    /**
     * Add new entries to the top of the list
     */
    prepend: {
      type: Boolean,
      default: false
    },
    /**
     * Size of the drawer
     */
    size: String,
    /**
     * Can be sorted manually via drag-n-drop
     */
    sortable: {
      type: Boolean,
      default: true
    },
    /**
     * Sort automatically by this rule
     */
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
      editRowField: null,
      newRowIndex: null,
      newRowModel: {},
      removeRowIndex: null,
      removeRowModel: {},
      rows: this.sanitize(this.value),
      page: 1
    };
  },
  computed: {
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
    options() {
      return [
        {
          icon: "edit",
          text: this.$t("edit"),
          click: "openEditRowDrawer"
        },
        {
          icon: "copy",
          text: this.$t("duplicate"),
          click: "duplicateRow",
          disabled: !this.more
        },
        {
          icon: "trash",
          text: this.$t("delete"),
          click: "openRemoveRowDialog"
        },
      ];
    },
    paginatedRows() {
      if (!this.limit) {
        return this.rows;
      }

      return this.rows.slice(
        this.pagination.offset,
        this.pagination.offset + this.limit
      );
    },
    pagination() {
      let offset = 0;

      if (this.limit) {
        offset = (this.page - 1) * this.limit;
      }

      return {
        page: this.page,
        offset: offset,
        limit: this.limit,
        total: this.rows.length,
        align: "center",
        details: true
      };
    },
  },
  watch: {
    value(value) {
      if (value != this.rows) {
        this.rows = this.sanitize(value);
      }
    }
  },
  methods: {
    closeNewRowDrawer() {
      this.newRowIndex = null;
      this.newRowModel = {};
      this.$refs.newRowDrawer.close();
    },
    closeEditRowDrawer() {
      this.editRowIndex = null;
      this.editRowModel = {};
      this.editRowField = null;
      this.$refs.editRowDrawer.close();
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
    focusEditRowField(event, field, fieldName) {
      this.editRowField = fieldName;
    },
    navigateRowDialog(index) {
      if (index < 0 || index >= this.rows.length) {
        return;
      }

      const row = this.rows[index];
      this.setEditRowDrawer(row, index);
    },
    navigateRowDialogPrev() {
      this.navigateRowDialog(this.editRowIndex - 1)
    },
    navigateRowDialogNext() {
      this.navigateRowDialog(this.editRowIndex + 1)
    },
    onCell(cell) {
      this.editRowField = cell.columnIndex;
      const offset = (this.page - 1) * this.limit;
      const index  = cell.rowIndex + offset;
      this.openEditRowDrawer(cell.row, index);
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
    openEditRowDrawer(row, rowIndex) {
      this.setEditRowDrawer(row, rowIndex);
      this.$refs.editRowDrawer.open();
    },
    openNewRowDrawer() {
      this.newRowIndex = 0;
      this.newRowModel = {};
      this.$refs.newRowDrawer.open();
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
    setEditRowDrawer(row, rowIndex) {
      this.editRowIndex = rowIndex;
      this.editRowModel = this.$helper.clone(row);

      if (this.limit > 0) {
        this.page = Math.ceil((rowIndex + 1) / this.limit);
      }

      setTimeout(() => {
        this.$refs.editRowDrawer.focus(this.editRowField);
      }, 50);
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
      this.closeEditRowDrawer();
    },
    submitNewRow() {
      if (this.prepend === true){
        this.rows.unshift(this.newRowModel);
      } else {
        this.rows.push(this.newRowModel);
      }

      this.rows = this.sort(this.rows);
      this.onInput();
      this.closeNewRowDrawer();
    },
    submitRemoveRow() {
      if (this.removeRowIndex === null) {
        return false;
      }

      this.rows.splice(this.removeRowIndex, 1);
      this.rows = this.sort(this.rows);
      this.onInput();

      if (this.paginatedRows.length === 0 && this.page > 1) {
        this.page--;
      }

      this.closeRemoveRowDialog();
    }
  }
}
</script>
