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
        <k-draggable
          v-model="items"
          :data-disabled="disabled"
          :options="{
            disabled: !isSortable,
            forceFallback: true,
            handle: '.k-structure-item-handle',
            fallbackClass: 'sortable-fallback'
          }"
          element="ul"
          class="k-structure"
          @input="onInput"
          @choose="close"
          @end="onInput"
        >
          <li
            v-for="(item, index) in items"
            :key="index"
            :data-active="isActive(index)"
            class="k-structure-item"
            @click.stop
          >
            <div v-if="!isActive(index)" class="k-structure-item-wrapper">
              <k-button v-if="isSortable" class="k-structure-item-handle" icon="sort" />

              <div class="k-structure-item-content">
                <div
                  v-for="(column, columnName) in columns"
                  :key="columnName"
                  :title="column.label"
                  :data-width="column.width"
                  :data-align="column.align"
                  class="k-structure-item-text"
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
                      <span class="k-structure-item-label">{{ column.label }}</span>
                      {{ column.before }} {{ displayText(fields[columnName], item[columnName]) || "–" }} {{ column.after}}
                    </template>

                  </template>
                  <template v-else>
                    <span class="k-structure-item-label">{{ column.label }}</span>
                    -
                  </template>
                </div>
              </div>

              <nav v-if="!disabled" class="k-structure-item-options">
                <k-button icon="trash" class="k-structure-option" @click="confirmRemove(index)" />
              </nav>
            </div>
            <div v-if="!disabled && isActive(index)" class="k-structure-form">
              <div class="k-structure-backdrop" @click="escape" />
              <k-fieldset
                ref="form"
                :fields="fields"
                :validate="true"
                v-model="items[index]"
                class="k-structure-fieldset"
                @input="onInput"
                @submit="close(index)"
              />
            </div>
          </li>
        </k-draggable>
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
import Vue from "vue";
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

    if (direction === "desc") {
      return sort(b[field], a[field], {
        insensitive: true,
      });
    } else {
      return sort(a[field], b[field], {
        insensitive: true,
      });
    }

  });

};

export default {
  inheritAttrs: false,
  components: {
    "k-email-field-preview": EmailFieldPreview,
    "k-files-field-preview": FilesFieldPreview,
    "k-url-field-preview": UrlFieldPreview
  },
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
        if (this.$refs.form[0]) {
          this.$refs.form[0].focus(field);
        }
      });
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

.k-structure-field {
  list-style: none;
}

.k-structure-item {
  position: relative;
  margin-bottom: 2px;
  box-shadow: $box-shadow-card;
}
.k-structure-item-label {
  display: block;
  font-size: $font-size-tiny;
  color: $color-dark-grey;
  margin-bottom: .25rem;
}
.k-structure-item.sortable-ghost {
  background: $color-inset;
  box-shadow: rgba($color-dark, 0.25) 0 5px 10px;
  outline: 2px solid $color-focus;
  margin-bottom: 2px;
  z-index: 1;
}
.k-structure-item.sortable-fallback {
  opacity: .25 !important;
}

.k-structure-item[data-active] {
  position: relative;
  z-index: 1;
}
.k-structure-item-handle {
  position: absolute;
  width: $structure-item-height;
  height: 100%;
  left: -$structure-item-height;
  opacity: 0;
  cursor: -webkit-grab;
  will-change: opacity;
  transition: opacity .3s;
}
.k-structure:hover .k-structure-item-handle {
  opacity: .25;
}
.k-structure-item-handle:active {
  cursor: -webkit-grabbing;
}
.k-structure-item:hover .k-structure-item-handle {
  opacity: 1;
}

.k-structure-item-wrapper {
  display: flex;
}
.k-structure-item-content {
  flex-grow: 1;
  display: flex;
  background: $color-white;
  align-items: center;
  overflow: hidden;
}
.k-structure-item-text {
  flex-shrink: 0;
  flex-grow: 1;
  flex-basis: 0;
  height: 100%;
  padding: .75rem 0.75rem;
  font-size: $font-size-small;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  border-right: 1px solid $color-background;
  cursor: pointer;
}
.k-structure-item-text[data-align="center"] {
  text-align: center;
}
.k-structure-item-text[data-align="right"] {
  text-align: right;
}

.k-structure-item-text[data-width="1/2"] {
  flex-basis: 50%;
}
.k-structure-item-text[data-width="1/3"] {
  flex-basis: 33.33%;
}
.k-structure-item-text[data-width="1/4"] {
  flex-basis: 25%;
}
.k-structure-item-text[data-width="2/3"] {
  flex-basis: 66.66%;
}
.k-structure-item-text[data-width="3/4"] {
  flex-basis: 75%;
}

.k-structure-item-text:first-child {
  min-width: 100px;
}
.k-structure-item-text:hover {
  background: rgba($color-background, .5);
}
.k-structure-item-text:not(:first-child) {
  display: none;

  @media screen and (min-width: 45rem) {
    display: block;
  }
}

.k-structure-content {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.k-structure-content,
.k-structure-option {
  padding: 0.5rem 0.75rem;
  line-height: 1em;
}
.k-structure-item-options {
  display: flex;
  background: #fff;
  flex-shrink: 0;
}
.k-structure-option {
  width: 2.5rem;
}
.k-structure-backdrop {
  position: fixed;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  z-index: 0;
}
.k-structure-fieldset {
  position: relative;
  background: $color-background;
  padding: 2rem 2rem 3rem;
  border-radius: $border-radius;
  z-index: 1;
  box-shadow: rgba($color-dark, .15) 0 0px 20px;
}

</style>
