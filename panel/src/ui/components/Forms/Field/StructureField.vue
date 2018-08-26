<template>
  <k-field v-bind="$props" class="k-structure-field" @click.native.stop>
    <template slot="options">
      <k-button
        v-if="!disabled"
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
          :list="items"
          :options="{
            disabled: disabled,
            forceFallback: true,
            handle: '.k-structure-item-handle',
            fallbackClass: 'sortable-fallback'
          }"
          :data-disabled="disabled"
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
              <k-button v-if="items.length > 1" class="k-structure-item-handle" icon="sort" />
              <div class="k-structure-item-content">
                <p
                  v-for="(field, fieldName) in fields"
                  v-if="field.type !== 'hidden'"
                  :key="fieldName"
                  :title="field.label"
                  class="k-structure-item-text"
                  @click="jump(index, fieldName)"
                >
                  <span class="k-structure-item-label">{{ field.label }}</span>
                  <template v-if="item[fieldName] !== undefined">
                    {{ displayText(field, item[fieldName]) || "–" }}
                  </template>
                </p>
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
import Field from "../Field.vue";
import dayjs from "dayjs";

export default {
  inheritAttrs: false,
  props: {
    ...Field.props,
    fields: Object,
    value: {
      type: Array,
      default() {
        return [];
      }
    }
  },
  data() {
    return {
      items:  this.value,
      active: null,
      trash:  null
    };
  },
  watch: {
    value(value) {
      this.items = value;
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
    },
    confirmRemove(index) {
      this.close();
      this.trash = index;
      this.$refs.remove.open();
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
}
.k-structure-item-content {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(0, 1fr));
  background: $color-white;
  align-items: center;
  overflow: hidden;
}
.k-structure-item-text {
  padding: .75rem 0.75rem;
  font-size: $font-size-small;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  border-right: 1px solid $color-background;
  cursor: pointer;
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
.k-structure-item-text:first-child {
  min-width: 100px;
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
