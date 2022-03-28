<template>
  <k-field v-bind="$props" class="k-structure-field" @click.native.stop>
    <!-- Add button -->
    <template #options>
      <k-button
        v-if="more && currentIndex === null"
        :id="_uid"
        ref="add"
        :text="$t('add')"
        icon="add"
        @click="add"
      />
    </template>

    <!-- Form -->
    <template v-if="currentIndex !== null">
      <div class="k-structure-backdrop" @click="escape" />
      <section class="k-structure-form">
        <k-form
          ref="form"
          v-model="currentModel"
          :fields="formFields"
          class="k-structure-form-fields"
          @input="onInput"
          @submit="submit"
        />
        <footer class="k-structure-form-buttons">
          <k-button
            :text="$t('cancel')"
            icon="cancel"
            class="k-structure-form-cancel-button"
            @click="close"
          />
          <k-pagination
            v-if="currentIndex !== 'new'"
            :dropdown="false"
            :total="items.length"
            :limit="1"
            :page="currentIndex + 1"
            :details="true"
            :validate="beforePaginate"
            @paginate="paginate"
          />
          <k-button
            :text="$t(currentIndex !== 'new' ? 'confirm' : 'add')"
            icon="check"
            class="k-structure-form-submit-button"
            @click="submit"
          />
        </footer>
      </section>
    </template>

    <!-- Empty State -->
    <k-empty
      v-else-if="items.length === 0"
      :data-invalid="isInvalid"
      icon="list-bullet"
      @click="add"
    >
      {{ empty || $t("field.structure.empty") }}
    </k-empty>

    <!-- Table -->
    <template v-else>
      <table
        :data-invalid="isInvalid"
        :data-sortable="isSortable"
        class="k-structure-table"
      >
        <thead>
          <tr>
            <th class="k-structure-table-index">#</th>
            <th
              v-for="(column, columnName) in columns"
              :key="columnName + '-header'"
              :style="'width:' + width(column.width)"
              :data-align="column.align"
              class="k-structure-table-column"
            >
              {{ column.label }}
            </th>
            <th v-if="!disabled" />
          </tr>
        </thead>
        <k-draggable
          v-direction
          :list="items"
          :data-disabled="disabled"
          :options="dragOptions"
          :handle="true"
          element="tbody"
          @end="onInput"
        >
          <tr v-for="(item, index) in paginatedItems" :key="index" @click.stop>
            <td class="k-structure-table-index">
              <k-sort-handle v-if="isSortable" />
              <span class="k-structure-table-index-number">{{
                indexOf(index)
              }}</span>
            </td>
            <td
              v-for="(column, columnName) in columns"
              :key="columnName"
              :title="column.label"
              :style="'width:' + width(column.width)"
              :data-align="column.align"
              class="k-structure-table-column"
              @click="jump(index, columnName)"
            >
              <template v-if="columnIsEmpty(item[columnName]) === false">
                <component
                  :is="'k-' + column.type + '-field-preview'"
                  v-if="previewExists(column.type)"
                  :value="item[columnName]"
                  :column="column"
                  :field="fields[columnName]"
                  @input="update(index, columnName, $event)"
                />
                <template v-else>
                  <p class="k-structure-table-text">
                    {{ column.before }}
                    {{
                      displayText(fields[columnName], item[columnName]) || "–"
                    }}
                    {{ column.after }}
                  </p>
                </template>
              </template>
            </td>
            <td v-if="!disabled" class="k-structure-table-options">
              <template v-if="duplicate && more && currentIndex === null">
                <k-button
                  ref="actionsToggle"
                  :key="index"
                  icon="dots"
                  class="k-structure-table-options-button"
                  @click="$refs[index + '-actions'][0].toggle()"
                />
                <k-dropdown-content :ref="index + '-actions'" align="right">
                  <k-dropdown-item icon="copy" @click="duplicateItem(index)">
                    {{ $t("duplicate") }}
                  </k-dropdown-item>
                  <k-dropdown-item icon="remove" @click="confirmRemove(index)">
                    {{ $t("remove") }}
                  </k-dropdown-item>
                </k-dropdown-content>
              </template>
              <template v-else>
                <k-button
                  :tooltip="$t('remove')"
                  class="k-structure-table-options-button"
                  icon="remove"
                  @click="confirmRemove(index)"
                />
              </template>
            </td>
          </tr>
        </k-draggable>
      </table>
      <k-pagination
        v-if="limit"
        v-bind="pagination"
        @paginate="paginateItems"
      />
      <k-dialog
        v-if="!disabled"
        ref="remove"
        :submit-button="$t('delete')"
        theme="negative"
        @submit="remove"
      >
        <k-text>{{ $t("field.structure.delete.confirm") }}</k-text>
      </k-dialog>
    </template>
  </k-field>
</template>

<script>
import structure from "@/mixins/forms/structure.js";

export default {
  mixins: [structure],
  inheritAttrs: false,
  props: {
    columns: Object,
    duplicate: {
      type: Boolean,
      default: true
    },
    /**
     * The text, that is shown when the field has no entries.
     */
    empty: String,
    fields: Object,
    limit: Number,
    max: Number,
    min: Number,
    prepend: {
      type: Boolean,
      default: false
    },
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
      autofocus: null,
      items: this.makeItems(this.value),
      currentIndex: null,
      currentModel: null,
      trash: null,
      page: 1
    };
  },
  computed: {
    dragOptions() {
      return {
        disabled: !this.isSortable,
        fallbackClass: "k-sortable-row-fallback"
      };
    },
    formFields() {
      let fields = {};

      Object.keys(this.fields).forEach((name) => {
        let field = this.fields[name];

        field.section = this.name;
        field.endpoints = {
          field: this.endpoints.field + "+" + name,
          section: this.endpoints.section,
          model: this.endpoints.model
        };

        if (this.autofocus === null && field.autofocus === true) {
          this.autofocus = name;
        }

        fields[name] = field;
      });

      return fields;
    },
    more() {
      if (this.disabled === true) {
        return false;
      }

      if (this.max && this.items.length >= this.max) {
        return false;
      }

      return true;
    },
    isInvalid() {
      if (this.disabled === true) {
        return false;
      }

      if (this.min && this.items.length < this.min) {
        return true;
      }

      if (this.max && this.items.length > this.max) {
        return true;
      }

      return false;
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
    pagination() {
      let offset = 0;

      if (this.limit) {
        offset = (this.page - 1) * this.limit;
      }

      return {
        page: this.page,
        offset: offset,
        limit: this.limit,
        total: this.items.length,
        align: "center",
        details: true
      };
    },
    paginatedItems() {
      if (!this.limit) {
        return this.items;
      }

      return this.items.slice(
        this.pagination.offset,
        this.pagination.offset + this.limit
      );
    }
  },
  watch: {
    value(value) {
      if (value != this.items) {
        this.items = this.makeItems(value);
      }
    }
  },
  methods: {
    add() {
      if (this.disabled === true) {
        return false;
      }

      if (this.currentIndex !== null) {
        this.escape();
        return false;
      }

      let data = {};

      Object.keys(this.fields).forEach((fieldName) => {
        const field = this.fields[fieldName];
        if (field.default !== null) {
          data[fieldName] = this.$helper.clone(field.default);
        } else {
          data[fieldName] = null;
        }
      });

      this.currentIndex = "new";
      this.currentModel = data;

      this.createForm();
    },
    addItem(value) {
      if (this.prepend === true) {
        this.items.unshift(value);
      } else {
        this.items.push(value);
      }
    },
    beforePaginate() {
      return this.save(this.currentModel);
    },
    /**
     * Close the current structure field entry.
     * @public
     */
    close() {
      this.currentIndex = null;
      this.currentModel = null;

      this.$events.$off("keydown.esc", this.escape);
      this.$events.$off("keydown.cmd.s", this.submit);

      this.$store.dispatch("content/enable");
    },
    confirmRemove(index) {
      this.close();
      this.trash = index + this.pagination.offset;
      this.$refs.remove.open();
    },
    createForm(field) {
      this.$events.$on("keydown.esc", this.escape);
      this.$events.$on("keydown.cmd.s", this.submit);
      this.$store.dispatch("content/disable");

      this.$nextTick(() => {
        this.$refs.form?.focus(field || this.autofocus);
      });
    },
    duplicateItem(index) {
      this.addItem(this.items[index + this.pagination.offset]);
      this.onInput();
    },
    escape() {
      if (this.currentIndex === "new") {
        let row = Object.values(this.currentModel);
        let isEmpty = true;

        row.forEach((value) => {
          if (this.columnIsEmpty(value) === false) {
            isEmpty = false;
          }
        });

        if (isEmpty === true) {
          this.close();
          return;
        }
      }

      this.submit();
    },
    focus() {
      this.$refs.add?.focus?.();
    },
    indexOf(index) {
      if (!this.limit) {
        return index + 1;
      } else {
        return (this.page - 1) * this.limit + index + 1;
      }
    },
    isActive(index) {
      return this.currentIndex === index;
    },
    jump(index, field) {
      this.open(index + this.pagination.offset, field);
    },
    makeItems(value) {
      if (Array.isArray(value) === false) {
        return [];
      }

      return this.sort(value);
    },
    onInput() {
      this.$emit("input", this.items);
    },
    /**
     * Edit the structure field entry at `index` position
     * with field `field` focused.
     * @public
     * @param {number} index
     * @param {string} field
     */
    open(index, field) {
      this.currentIndex = index;
      this.currentModel = this.$helper.clone(this.items[index]);
      this.createForm(field);
    },
    paginate(pagination) {
      this.open(pagination.offset);
    },
    paginateItems(pagination) {
      this.page = pagination.page;
    },
    remove() {
      if (this.trash === null) {
        return false;
      }

      this.items.splice(this.trash, 1);
      this.trash = null;
      this.$refs.remove.close();
      this.onInput();

      if (this.paginatedItems.length === 0 && this.page > 1) {
        this.page--;
      }

      this.items = this.sort(this.items);
    },
    sort(items) {
      if (!this.sortBy) {
        return items;
      }

      return items.sortBy(this.sortBy);
    },
    async save() {
      if (this.currentIndex !== null && this.currentIndex !== undefined) {
        try {
          await this.validate(this.currentModel);

          if (this.currentIndex === "new") {
            this.addItem(this.currentModel);
          } else {
            this.items[this.currentIndex] = this.currentModel;
          }

          this.items = this.sort(this.items);
          this.onInput();

          return true;
        } catch (errors) {
          this.$store.dispatch("notification/error", {
            message: this.$t("error.form.incomplete"),
            details: errors
          });

          throw errors;
        }
      }
    },
    async submit() {
      try {
        await this.save();
        this.close();
      } catch (e) {
        // don't close
      }
    },
    async validate(model) {
      const errors = await this.$api.post(
        this.endpoints.field + "/validate",
        model
      );

      if (errors.length > 0) {
        throw errors;
      } else {
        return true;
      }
    },
    update(index, column, value) {
      this.items[index][column] = value;
      this.onInput();
    }
  }
};
</script>

<style>
.k-structure-field {
  --item-height: 38px;
}

.k-structure-table {
  position: relative;
  table-layout: fixed;
  width: 100%;
  background: #fff;
  font-size: var(--text-sm);
  border-spacing: 0;
  box-shadow: var(--shadow);
}
.k-structure-table th,
.k-structure-table td {
  border-inline-end: 1px solid var(--color-background);
  line-height: 1.25em;
  overflow: hidden;
  text-overflow: ellipsis;
}

.k-structure-table th,
.k-structure-table tr:not(:last-child) td {
  border-bottom: 1px solid var(--color-background);
}

.k-structure-table td:last-child {
  overflow: visible;
}

.k-structure-table th {
  position: sticky;
  top: 0;
  inset-inline: 0;
  width: 100%;
  height: var(--item-height);
  padding: 0 0.75rem;
  background: #fff;
  color: var(--color-gray-600);
  font-weight: 400;
  text-align: start;
  z-index: 1;
}

.k-structure-table th:last-child,
.k-structure-table td:last-child {
  width: var(--item-height);
  border-inline-end: 0;
}

.k-structure-table tbody tr:hover td {
  background: rgba(239, 239, 239, 0.25);
}

/* mobile */
@media screen and (max-width: 65em) {
  .k-structure-table td,
  .k-structure-table th {
    display: none;
  }

  .k-structure-table th:first-child,
  .k-structure-table th:nth-child(2),
  .k-structure-table th:last-child,
  .k-structure-table td:first-child,
  .k-structure-table td:nth-child(2),
  .k-structure-table td:last-child {
    display: table-cell;
  }

  .k-structure-table th.k-structure-table-column:nth-child(2),
  .k-structure-table td.k-structure-table-column:nth-child(2) {
    width: auto !important;
  }
}

/* alignment */
.k-structure-table .k-structure-table-column[data-align] {
  text-align: var(--align);
}
.k-structure-table .k-structure-table-column[data-align="right"] > .k-input {
  flex-direction: column;
  align-items: flex-end;
}

.k-structure-table .k-structure-table-index,
.k-structure-table .k-sort-handle,
.k-structure-table .k-structure-table-options,
.k-structure-table .k-structure-table-options-button {
  width: var(--item-height);
  height: var(--item-height);
}

.k-structure-table .k-structure-table-index {
  text-align: center;
}
.k-structure-table .k-structure-table-index-number {
  font-size: var(--text-xs);
  color: var(--color-gray-500);
  padding-top: 0.15rem;
}

.k-structure-table .k-sort-handle,
.k-structure-table[data-sortable="true"]
  tr:hover
  .k-structure-table-index-number {
  display: none;
}
.k-structure-table[data-sortable="true"] tr:hover .k-sort-handle {
  display: flex !important;
}

.k-structure-table .k-structure-table-options {
  position: relative;
  text-align: center;
}

.k-structure-table .k-structure-table-text {
  padding: 0 0.75rem;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.k-structure-table .k-sortable-ghost {
  background: var(--color-white);
  box-shadow: rgba(17, 17, 17, 0.25) 0 5px 10px;
  outline: 2px solid var(--color-focus);
  margin-bottom: 2px;
  cursor: grabbing;
  cursor: -moz-grabbing;
  cursor: -webkit-grabbing;
}

[data-disabled="true"] .k-structure-table {
  background: var(--color-background);
}
[data-disabled="true"] .k-structure-table th,
[data-disabled="true"] .k-structure-table td {
  background: var(--color-background);
  border-bottom: 1px solid var(--color-border);
  border-inline-end: 1px solid var(--color-border);
}
[data-disabled="true"] .k-structure-table td:last-child {
  overflow: hidden;
  text-overflow: ellipsis;
}
.k-structure-table .k-sortable-row-fallback {
  opacity: 0 !important;
}

.k-structure-backdrop {
  position: absolute;
  inset: 0;
  z-index: 2;
  height: 100vh;
}
.k-structure-form {
  position: relative;
  z-index: 3;
  border-radius: var(--rounded-xs);
  margin-bottom: 1px;
  box-shadow: rgba(17, 17, 17, 0.05) 0 0 0 3px;
  border: 1px solid var(--color-border);
  background: var(--color-background);
}

.k-structure-form-fields {
  padding: 1.5rem 1.5rem 2rem;
}

.k-structure-form-buttons {
  border-top: 1px solid var(--color-border);
  display: flex;
  justify-content: space-between;
}

.k-structure-form-buttons .k-pagination {
  display: none;
}
@media screen and (min-width: 65em) {
  .k-structure-form-buttons .k-pagination {
    display: flex;
  }
}

.k-structure-form-buttons .k-pagination > .k-button,
.k-structure-form-buttons .k-pagination > span {
  padding: 0.875rem 1rem !important;
}

.k-structure-form-cancel-button,
.k-structure-form-submit-button {
  padding: 0.875rem 1.5rem;
  line-height: 1rem;
  display: flex;
}
</style>
