<template>
  <k-field v-bind="$props" class="k-structure-field" @click.native.stop>
    <template slot="options">
      <k-button
        v-if="more"
        ref="add"
        :id="_uid"
        icon="add"
        @click="add"
      >
        {{ $t("add") }}
      </k-button>
    </template>

    <template>
      <k-empty v-if="items.length === 0" icon="list-bullet" @click="add">
        {{ $t("field.structure.empty") }}
      </k-empty>

      <template v-else>

        <template v-if="active !== null">
          <div class="k-structure-backdrop" @click="escape" />
          <section class="k-structure-form">
            <header class="k-structure-form-header">
              <k-button icon="check" @click="close">{{ $t('confirm') }}</k-button>
              <k-pagination
                :total="items.length"
                :limit="1"
                :page="active + 1"
                :details="true"
                @paginate="paginate"
              />
            </header>
            <k-fieldset
              ref="form"
              :fields="fields"
              :validate="true"
              v-model="items[active]"
              class="k-structure-form-fields"
              @input="onInput"
              @submit="close(active)"
            />
          </section>
        </template>
        <template v-else>
          <table :data-sortable="isSortable" class="k-structure-table">
            <thead>
              <tr>
                <th class="k-structure-table-index">#</th>
                <th
                  v-for="(column, columnName) in columns"
                  :key="columnName + '-header'"
                  :data-width="column.width"
                  :data-align="column.align"
                  class="k-structure-table-column"
                >
                  {{ column.label }}
                </th>
                <th />
              </tr>
            </thead>
            <k-draggable
              v-model="items"
              :data-disabled="disabled"
              :options="{
                disabled: !isSortable,
                handle: '.k-structure-table-handle',
                forceFallback: true,
                fallbackClass: 'sortable-fallback'
              }"
              element="tbody"
              @input="onInput"
              @choose="close"
              @end="onInput"
            >
              <tr
                v-for="(item, index) in paginatedItems"
                :key="index"
                @click.stop
              >
                <td class="k-structure-table-index">
                  <k-button
                    v-if="isSortable"
                    class="k-structure-table-handle"
                    icon="sort"
                  />
                  <span>{{ indexOf(index) }}</span>
                </td>
                <td
                  v-for="(column, columnName) in columns"
                  :key="columnName"
                  :title="column.label"
                  :data-width="column.width"
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
                    />
                    <template v-else>
                      <p class="k-structure-table-text">
                        {{ column.before }} {{ displayText(fields[columnName], item[columnName]) || "–" }} {{ column.after }}
                      </p>
                    </template>
                  </template>
                </td>
                <td class="k-structure-table-option">
                  <k-button :tooltip="$t('remove')" icon="remove" @click="confirmRemove(index)" />
                </td>
              </tr>
            </k-draggable>
          </table>
          <k-pagination v-if="limit" v-bind="pagination" @paginate="paginateItems" />
        </template>

      </template>

      <k-dialog
        v-if="!disabled"
        ref="remove"
        :button="$t('delete')"
        theme="negative"
        @submit="remove"
      >
        <k-text>{{ $t("field.structure.delete.confirm") }}</k-text>
      </k-dialog>
    </template>

  </k-field>
</template>

<script>
import Vue from "vue";
import Field from "../Field.vue";
import dayjs from "dayjs";
import sorter from "@/ui/helpers/sort.js";

Array.prototype.sortBy = function(sortBy) {

  const sort      = sorter();
  const options   = sortBy.split(" ");
  const field     = options[0];
  const direction = options[1] || "asc";

  return this.sort((a, b) => {

    const valueA = String(a[field]).toLowerCase();
    const valueB = String(b[field]).toLowerCase();

    if (direction === "desc") {
      return sort(valueB, valueA);
    } else {
      return sort(valueA, valueB);
    }

  });

};

export default {
  inheritAttrs: false,
  props: {
    ...Field.props,
    columns: Object,
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
      items: this.makeItems(this.value),
      active: null,
      trash: null,
      page: 1
    };
  },
  computed: {
    more() {

      if (this.disabled === true) {
        return false;
      }

      if (this.max && this.items.length >= this.max) {
        return false;
      }

      return true;

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
      return {
        page: this.page,
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

      const index  = this.page - 1;
      const offset = index * this.limit;

      return this.items.slice(offset, offset + this.limit);
    }
  },
  watch: {
    value(value) {
      if (value != this.items) {
        this.items = this.makeItems(value);
      }
    }
  },
  mounted() {
    this.$events.$on('keydown.esc', this.escape);
    this.$events.$on('keydown.cmd.s', this.close);
  },
  destroyed() {
    this.$events.$off('keydown.esc', this.escape);
    this.$events.$off('keydown.cmd.s', this.close);
  },
  methods: {
    makeItems(value) {
      if (Array.isArray(value) === false) {
        return [];
      }

      return this.sort(value);
    },
    indexOf(index) {
      if (!this.limit) {
        return index + 1;
      } else {
        return (this.page - 1) * this.limit + index + 1;
      }
    },
    sort(items) {
      if (!this.sortBy) {
        return items;
      }

      return items.sortBy(this.sortBy);
    },
    previewExists(type) {
      if (Vue.options.components["k-" + type + "-field-preview"] !== undefined) {
        return true;
      }

      if (this.$options.components["k-" + type + "-field-preview"] !== undefined) {
        return true;
      }

      return false;
    },
    add() {

      if (this.disabled === true) {
        return false;
      }

      if (this.active !== null) {
        this.escape();
        return false;
      }

      let data = {};

      Object.keys(this.fields).forEach(fieldName => {
        const field = this.fields[fieldName];

        if (field.default) {
          data[fieldName] = field.default;
        }
      });

      this.items.push(data);

      this.onInput();

      this.$nextTick(() => {
        this.open(this.items.length - 1);
      });
    },
    close() {
      this.active = null;
      this.items = this.sort(this.items);
    },
    confirmRemove(index) {
      this.close();
      this.trash = index;
      this.$refs.remove.open();
    },
    columnIsEmpty(value) {

      if (value === undefined || value === null || value === "") {
        return true;
      }

      if (typeof value === "object" && Object.keys(value).length === 0 && value.constructor === Object) {
        return true;
      }

      if (value.length !== undefined && value.length === 0) {
        return true;
      }

      return false;

    },
    escape() {
      if (this.active !== null && this.items[this.active]) {

        let row     = Object.values(this.items[this.active]);
        let isEmpty = true;

        row.forEach(value => {
          if (this.columnIsEmpty(value) === false) {
            isEmpty = false;
          }
        });

        if (isEmpty === true) {
          this.discard();
          return;
        }

      }

      this.close();
    },
    discard() {
      this.trash  = this.active;
      this.active = null;
      this.remove();
    },
    focus() {
      this.$refs.add.focus();
    },
    isActive(index) {
      return this.active === index;
    },
    jump(index, field) {
      this.open(index, field);
    },
    displayText(field, value) {

      switch (field.type) {
        case "user": {
          return value.email;
        }
        case "date": {
          const date = dayjs(value);
          return date.isValid() ? date.format("YYYY-MM-DD") : "";
        }
        case "tags":
          return value.map(item => {
            return item.text;
          }).join(", ");
        case "checkboxes": {
          return value.map(item => {
            let text = item;

            field.options.forEach(option => {
              if (option.value === item) {
                text = option.text;
              }
            });

            return text;
          }).join(", ");
        }
        case "select": {
          const option = field.options.filter(item => item.value === value)[0];
          return option ? option.text : null;
        }
      }

      if (typeof value === "object" && value !== null) {
        return "…";
      }

      return value;
    },
    onInput() {
      this.$emit("input", this.items);
    },
    open(index, field) {
      this.active = index;

      this.$nextTick(() => {
        if (this.$refs.form) {
          this.$refs.form.focus(field);
        }
      });
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

    },
    toggle(index) {
      if (this.active === index) {
        this.close();
      } else {
        this.open(index);
      }
    },
  }
}
</script>

<style lang="scss">

$structure-item-height: 38px;

.k-structure-table {
  table-layout: fixed;
  width: 100%;
  background: #fff;
  font-size: $font-size-small;
  border-spacing: 0;
  box-shadow: $box-shadow-card;

  th, td {
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

  th {
    font-weight: 400;
    color: $color-dark-grey;
    padding: 0 .75rem;
    height: $structure-item-height;

    [dir="ltr"] & {
      text-align: left;
    }

    [dir="rtl"] & {
      text-align: right;
    }

  }

  th:last-child, td:last-child {
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
    background: rgba($color-background, .25);
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
  .k-structure-table-index > span {
    font-size: $font-size-tiny;
    color: $color-light-grey;
    padding-top: .15rem;
  }
  .k-structure-table-handle {
    width: $structure-item-height;
    display: none;
  }

  &[data-sortable] tr:hover .k-structure-table-index > span {
    display: none;
  }
  &[data-sortable] tr:hover .k-structure-table-handle {
    display: block;
  }

  .k-structure-table-option {
    width: $structure-item-height;
    text-align: center;
  }
  .k-structure-table-option .k-button {
    width: $structure-item-height;
    height: $structure-item-height;
  }

  .k-structure-table-text {
    padding: 0 .75rem;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  .sortable-ghost {
    background: $color-white;
    box-shadow: rgba($color-dark, 0.25) 0 5px 10px;
    outline: 2px solid $color-focus;
    margin-bottom: 2px;
  }
  .sortable-fallback {
    opacity: .25 !important;
    background: $color-white;
    display: table;
    table-layout: fixed;
    border-spacing: 0;
  }
  .sortable-fallback td:first-child {
    display: table-cell;
  }

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
  box-shadow: rgba($color-dark, .05) 0 0 0 3px;
  border: 1px solid $color-border;
  background: $color-background;
}
.k-structure-form-header {
  height: $structure-item-height;
  padding: 0 .75rem;
  border-bottom: 1px dashed $color-border;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.k-structure-form-fields {
  padding: 2rem 2.5rem 2.5rem;
}


</style>
