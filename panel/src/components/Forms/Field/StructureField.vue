<template>
  <k-field v-bind="$props" class="k-structure-field" @click.native.stop>

    <!-- Add button -->
    <template slot="options">
      <k-button
        v-if="more && currentIndex === null"
        ref="add"
        :id="_uid"
        icon="add"
        @click="add"
      >
        {{ $t("add") }}
      </k-button>
    </template>

    <!-- Form -->
    <template v-if="currentIndex !== null">
      <div class="k-structure-backdrop" @click="escape" />
      <section class="k-structure-form">
        <k-form
          ref="form"
          :fields="formFields"
          v-model="currentModel"
          class="k-structure-form-fields"
          @input="onInput"
          @submit="submit"
        />
        <footer class="k-structure-form-buttons">
          <k-button class="k-structure-form-cancel-button" icon="cancel" @click="close">{{ $t('cancel') }}</k-button>
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
          <k-button class="k-structure-form-submit-button" icon="check" @click="submit">{{ $t(currentIndex !== 'new' ? 'confirm' : 'add') }}</k-button>
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
            <th />
          </tr>
        </thead>
        <k-draggable
          :list="items"
          :data-disabled="disabled"
          :options="dragOptions"
          :handle="true"
          element="tbody"
          @end="onInput"
        >
          <tr
            v-for="(item, index) in paginatedItems"
            :key="index"
            @click.stop
          >
            <td class="k-structure-table-index">
              <k-sort-handle v-if="isSortable" />
              <span class="k-structure-table-index-number">{{ indexOf(index) }}</span>
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
                  v-if="previewExists(column.type)"
                  :is="'k-' + column.type + '-field-preview'"
                  :value="item[columnName]"
                  :column="column"
                  :field="fields[columnName]"
                  @input="update(index, columnName, $event)"
                />
                <template v-else>
                  <p class="k-structure-table-text">
                    {{ column.before }} {{ displayText(fields[columnName], item[columnName]) || "–" }} {{ column.after }}
                  </p>
                </template>
              </template>
            </td>
            <td class="k-structure-table-options">
              <template v-if="duplicate && more && currentIndex === null">
                <k-button
                  ref="actionsToggle"
                  :key="index"
                  icon="dots"
                  class="k-structure-table-options-button"
                  @click="$refs[index + '-actions'][0].toggle()"
                />
                <k-dropdown-content :ref="index + '-actions'" align="right">
                  <k-dropdown-item icon="copy" @click="duplicateItem(index)">{{ $t('duplicate') }}</k-dropdown-item>
                  <k-dropdown-item icon="remove" @click="confirmRemove(index)">{{ $t('remove') }}</k-dropdown-item>
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
      <k-pagination v-if="limit" v-bind="pagination" @paginate="paginateItems" />
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
import Field from "../Field.vue";

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

      Object.keys(this.fields).forEach(name => {
        let field = this.fields[name];

        field.section = this.name;
        field.endpoints = {
          field: this.endpoints.field + "+" + name,
          section: this.endpoints.section,
          model: this.endpoints.model
        };

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

      Object.keys(this.fields).forEach(fieldName => {
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
    close() {
      this.currentIndex = null;
      this.currentModel = null;

      this.$events.$off("keydown.esc", this.escape);
      this.$events.$off("keydown.cmd.s", this.submit);

      this.$store.dispatch("content/enable");
    },
    columnIsEmpty(value) {
      if (value === undefined || value === null || value === "") {
        return true;
      }

      if (
        typeof value === "object" &&
        Object.keys(value).length === 0 &&
        value.constructor === Object
      ) {
        return true;
      }

      if (value.length !== undefined && value.length === 0) {
        return true;
      }

      return false;
    },
    confirmRemove(index) {
      this.close();
      this.trash = index;
      this.$refs.remove.open();
    },
    createForm(field) {
      this.$events.$on("keydown.esc", this.escape);
      this.$events.$on("keydown.cmd.s", this.submit);
      this.$store.dispatch("content/disable");

      this.$nextTick(() => {
        if (this.$refs.form) {
          this.$refs.form.focus(field);
        }
      });
    },
    displayText(field, value) {
      switch (field.type) {
        case "user": {
          return value.email;
        }
        case "date": {
          const date = this.$library.dayjs(value);
          const format = field.time === true ? "YYYY-MM-DD HH:mm" : "YYYY-MM-DD";
          return date.isValid() ? date.format(format) : "";
        }
        case "tags":
        case "multiselect":
          return value
            .map(item => {
              return item.text;
            })
            .join(", ");
        case "checkboxes": {
          return value
            .map(item => {
              let text = item;

              field.options.forEach(option => {
                if (option.value === item) {
                  text = option.text;
                }
              });

              return text;
            })
            .join(", ");
        }
        case "radio":
        case "select": {
          const option = field.options.filter(item => item.value === value)[0];
          return option ? option.text : null;
        }
      }

      if (typeof value === "object" && value !== null) {
        return "…";
      }

      return value.toString();
    },
    duplicateItem(index) {
      this.addItem(this.items[index]);
      this.onInput();
    },
    escape() {
      if (this.currentIndex === "new") {
        let row = Object.values(this.currentModel);
        let isEmpty = true;

        row.forEach(value => {
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
      if (this.$refs.add && this.$refs.add.focus) {
        this.$refs.add.focus();
      }
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
    previewExists(type) {
      return this.$helper.isComponent(`k-${type}-field-preview`);
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
    save() {
      if (this.currentIndex !== null && this.currentIndex !== undefined) {
        return this.validate(this.currentModel)
          .then(() => {
            if (this.currentIndex === "new") {
              this.addItem(this.currentModel);
            } else {
              this.items[this.currentIndex] = this.currentModel;
            }

            this.items = this.sort(this.items);
            this.onInput();

            return true;
          })
          .catch(errors => {
            this.$store.dispatch("notification/error", {
              message: this.$t("error.form.incomplete"),
              details: errors
            });

            throw errors;
          });
      } else {
        return Promise.resolve();
      }
    },
    submit() {
      this.save()
        .then(this.close)
        .catch(() => {
          // don't close
        });
    },
    validate(model) {
      return this.$api
        .post(this.endpoints.field + "/validate", model)
        .then(errors => {
          if (errors.length > 0) {
            throw errors;
          } else {
            return true;
          }
        });
    },
    width(fraction) {
      if (!fraction) {
        return "auto";
      }
      const parts = fraction.toString().split("/");

      if (parts.length !== 2) {
        return "auto";
      }

      const a = Number(parts[0]);
      const b = Number(parts[1]);

      return parseFloat(100 / b * a, 2).toFixed(2) + "%";
    },
    update(index, column, value) {
      this.items[index][column] = value;
      this.onInput();
    }
  }
};
</script>

<style lang="scss">
$structure-item-height: 38px;

.k-structure-table {
  position: relative;
  table-layout: fixed;
  width: 100%;
  background: #fff;
  font-size: $font-size-small;
  border-spacing: 0;
  box-shadow: $box-shadow-card;

  th,
  td {
    border-bottom: 1px solid $color-background;
    line-height: 1.25em;
    overflow: hidden;
    text-overflow: ellipsis;

    [dir="ltr"] & {
      border-right: 1px solid $color-background;
    }

    [dir="rtl"] & {
      border-left: 1px solid $color-background;
    }
  }

  td:last-child {
    overflow: visible;
  }

  th {
    position: sticky;
    top: 0;
    right: 0;
    left: 0;
    width: 100%;
    background: #fff;
    font-weight: 400;
    z-index: 1;
    color: $color-dark-grey;
    padding: 0 0.75rem;
    height: $structure-item-height;

    [dir="ltr"] & {
      text-align: left;
    }

    [dir="rtl"] & {
      text-align: right;
    }
  }

  th:last-child,
  td:last-child {
    width: $structure-item-height;

    [dir="ltr"] & {
      border-right: 0;
    }

    [dir="rtl"] & {
      border-left: 0;
    }
  }

  tr:last-child td {
    border-bottom: 0;
  }

  tbody tr:hover td {
    background: rgba($color-background, 0.25);
  }

  /* mobile */
  @media screen and (max-width: $breakpoint-medium) {
    td,
    th {
      display: none;
    }

    th:first-child,
    th:nth-child(2),
    th:last-child,
    td:first-child,
    td:nth-child(2),
    td:last-child {
      display: table-cell;
    }
  }

  /* alignment */
  .k-structure-table-column[data-align="center"] {
    text-align: center;
  }
  .k-structure-table-column[data-align="right"] {
    [dir="ltr"] & {
      text-align: right;
    }

    [dir="rtl"] & {
      text-align: left;
    }
  }
  .k-structure-table-column[data-align="right"] > .k-input {
    flex-direction: column;
    align-items: flex-end;
  }

  /* column widths */
  .k-structure-table-column[data-width="1/2"] {
    width: 50%;
  }
  .k-structure-table-column[data-width="1/3"] {
    width: 33.33%;
  }
  .k-structure-table-column[data-width="1/4"] {
    width: 25%;
  }
  .k-structure-table-column[data-width="1/5"] {
    width: 20%;
  }
  .k-structure-table-column[data-width="1/6"] {
    width: 16.66%;
  }
  .k-structure-table-column[data-width="1/8"] {
    width: 12.5%;
  }
  .k-structure-table-column[data-width="1/9"] {
    width: 11.11%;
  }
  .k-structure-table-column[data-width="2/3"] {
    width: 66.66%;
  }
  .k-structure-table-column[data-width="3/4"] {
    width: 75%;
  }

  .k-structure-table-index {
    width: $structure-item-height;
    text-align: center;
  }
  .k-structure-table-index-number {
    font-size: $font-size-tiny;
    color: $color-light-grey;
    padding-top: 0.15rem;
  }

  .k-sort-handle {
    width: $structure-item-height;
    height: $structure-item-height;
    display: none;
  }

  &[data-sortable] tr:hover .k-structure-table-index-number {
    display: none;
  }
  &[data-sortable] tr:hover .k-sort-handle {
    display: flex !important;
  }

  .k-structure-table-options {
    position: relative;
    width: $structure-item-height;
    text-align: center;
    height: $structure-item-height;
  }
  .k-structure-table-options-button {
    width: $structure-item-height;
    height: $structure-item-height;
  }

  .k-structure-table-text {
    padding: 0 0.75rem;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  .k-sortable-ghost {
    background: $color-white;
    box-shadow: rgba($color-dark, 0.25) 0 5px 10px;
    outline: 2px solid $color-focus;
    margin-bottom: 2px;
    cursor: grabbing;
    cursor: -moz-grabbing;
    cursor: -webkit-grabbing;
  }
}

.k-sortable-row-fallback {
  opacity: 0 !important;
}

.k-structure-backdrop {
  position: absolute;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  z-index: 2;
  height: 100vh;
}
.k-structure-form {
  position: relative;
  z-index: 3;
  border-radius: $border-radius;
  margin-bottom: 1px;
  box-shadow: rgba($color-dark, 0.05) 0 0 0 3px;
  border: 1px solid $color-border;
  background: $color-background;
}

.k-structure-form-fields {
  padding: 1.5rem 1.5rem 2rem;
}

.k-structure-form-buttons {
  border-top: 1px solid $color-border;
  display: flex;
  justify-content: space-between;
}

.k-structure-form-buttons .k-pagination {
  display: none;
  @media screen and (min-width: $breakpoint-medium) {
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
