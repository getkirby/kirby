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
        {{ "Add" | t("add") }}
      </k-button>
    </template>

    <template>
      <k-empty v-if="items.length === 0" icon="list-bullet" @click="add">
        {{ $t("structure.empty") }}
      </k-empty>

      <template v-else>

        <template v-if="active !== null">
          <div class="k-structure-backdrop" @click="escape" />
          <section class="k-structure-form">
            <header class="k-structure-form-header">
              <k-button slot="left" icon="check" @click="close">{{ $t('confirm') }}</k-button>
              <k-pagination
                slot="right"
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
                v-for="(item, index) in items"
                :key="index"
                @click.stop
              >
                <td class="k-structure-table-index">
                  <k-button
                    v-if="isSortable"
                    class="k-structure-table-handle"
                    icon="sort"
                    @click="open(index)"
                  />
                  <span>{{ index + 1 }}</span>
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
                    />
                    <template v-else>
                      <p class="k-structure-table-text">
                        {{ column.before }} {{ displayText(fields[columnName], item[columnName]) || "–" }} {{ column.after }}
                      </p>
                    </template>
                  </template>
                </td>
                <td class="k-structure-table-option">
                  <k-button icon="remove" @click="confirmRemove(index)" />
                </td>
              </tr>
            </k-draggable>
          </table>
        </template>

      </template>

      <k-dialog
        v-if="!disabled"
        ref="remove"
        :button="$t('delete')"
        theme="negative"
        @submit="remove"
      >
        <k-text>{{ "Do you really want to delete this item?" | t("structure.delete.confirm") }}</k-text>
      </k-dialog>
    </template>

    <k-dialog
      ref="escapeDialog"
      :button="$t('discard')"
      theme="negative"
      icon="trash"
      @submit="discard"
    >
      {{ "Do you really want to discard this item?" | t("structure.discard.confirm") }}
    </k-dialog>

  </k-field>
</template>

<script>
import Field from "../Field.vue";
import dayjs from "dayjs";
import sorter from "@/ui/helpers/sort.js";

// Field Previews
import FilesFieldPreview from "../Previews/FilesFieldPreview.vue";
import EmailFieldPreview from "../Previews/EmailFieldPreview.vue";
import UrlFieldPreview from "../Previews/UrlFieldPreview.vue";

Array.prototype.sortBy = function(sortBy) {

  const sort      = sorter();
  const options   = sortBy.split(" ");
  const field     = options[0];
  const direction = options[1] || "asc";

  return this.sort((a, b) => {

    const valueA = a[field].toLowerCase();
    const valueB = b[field].toLowerCase();

    if (direction === "desc") {
      return sort(valueB, valueA);
    } else {
      return sort(valueA, valueB);
    }

  });

};

export default {
  components: {
    "k-email-field-preview": EmailFieldPreview,
    "k-files-field-preview": FilesFieldPreview,
    "k-url-field-preview": UrlFieldPreview
  },
  inheritAttrs: false,
  props: {
    ...Field.props,
    columns: Object,
    fields: Object,
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
      items: this.sort(this.value),
      active: null,
      trash: null
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
    }
  },
  watch: {
    value(value) {

      if (value != this.items) {
        this.items = this.sort(value);
      }

    }
  },
  mounted() {
    this.$events.$on('keydown.esc', this.escape);
    this.$events.$on('keydown.cmd.s', this.close);
    this.$events.$on("field.structure.close", this.escape);
  },
  destroyed() {
    this.$events.$off('keydown.esc', this.escape);
    this.$events.$off('keydown.cmd.s', this.close);
    this.$events.$off("field.structure.close", this.escape);
  },
  methods: {
    sort(items) {
      if (!this.sortBy) {
        return items;
      }

      return items.sortBy(this.sortBy);
    },
    previewExists(type) {
      return this.$options.components['k-' + type + '-field-preview'] !== undefined;
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

      if (value.length !== undefined && value.length === 0) {
        return true;
      }

      return false;

    },
    escape() {
      if (this.active !== null && this.items[this.active]) {
        if (Object.keys(this.items[this.active]).length === 0) {
          this.$refs.escapeDialog.open();
          return;
        }
      }

      this.close();
    },
    discard() {
      this.trash  = this.active;
      this.active = null;
      this.remove();
      this.$refs.escapeDialog.close();
    },
    focus() {
      this.$refs.add.focus();
    },
    isActive(index) {
      return this.active === index;
    },
    jump(index, field) {
      this.$events.$emit("field.structure.close");
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
        case "checkboxes": {
          return value.map(item => {
            return item.text;
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
    onItemInput(index, value) {
      this.items[index] = value;
      this.$emit("input", this.items);
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
    remove() {

      if (this.trash === null) {
        return false;
      }

      this.items.splice(this.trash, 1);
      this.trash = null;
      this.$refs.remove.close();
      this.onInput();
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
    border-right: 1px solid $color-background;
    border-bottom: 1px solid $color-background;
    line-height: 1.25em;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  th {
    text-align: left;
    font-weight: 400;
    color: $color-dark-grey;
    padding: 0 .75rem;
    height: $structure-item-height;
  }

  th:last-child, td:last-child {
    border-right: 0;
    width: $structure-item-height;
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
    text-align: right;
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
  position: fixed;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  z-index: 0;
}
.k-structure-form {
  position: relative;
  border-radius: $border-radius;
  margin-bottom: 1px;
  z-index: 1;
  outline: 2px solid rgba(#000, .05);
  border: 1px solid $color-border;
  background: $color-background;
}
.k-structure-form-header {
  height: $structure-item-height;
  padding: 0 1.5rem;
  border-bottom: 1px dashed $color-border;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.k-structure-form-fields {
  padding: 1.5rem 1.5rem 2rem;
}


</style>
