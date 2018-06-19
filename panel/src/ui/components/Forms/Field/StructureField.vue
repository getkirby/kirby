<template>
  <kirby-field :input="_uid" v-bind="$props" class="kirby-structure-field">
    <template slot="options">
      <kirby-button
        ref="add"
        :id="_uid"
        icon="add"
        @click="add"
      >
        {{ "Add" | t("add") }}
      </kirby-button>
    </template>

    <template>
      <kirby-box v-if="items.length === 0" theme="button">
        <kirby-button icon="add" @click="add">{{ "Click to add the first item …" | t("structure.add.first") }}</kirby-button>
      </kirby-box>
      <kirby-draggable
        v-else
        v-model="items"
        :data-disabled="disabled"
        :options="{disabled: disabled, forceFallback: true, handle: '.kirby-structure-item-handle'}"
        element="ul"
        class="kirby-structure"
        @input="onInput"
        @choose="active = null"
        @end="onInput"
      >
        <li
          v-for="(item, index) in items"
          :key="index"
          :data-active="isActive(index)"
          class="kirby-structure-item"
        >
          <div class="kirby-structure-item-wrapper">
            <kirby-button class="kirby-structure-item-handle" icon="sort" />
            <div class="kirby-structure-item-content">
              <p
                v-for="(field, fieldName) in fields"
                :key="fieldName"
                :title="field.label"
                class="kirby-structure-item-text"
                @click="jump(index, fieldName)"
              >
                {{ displayText(item[fieldName])  }}
              </p>
            </div>
            <nav v-if="!disabled" class="kirby-structure-item-options">
              <kirby-button :icon="isActive(index) ? 'angle-up' : 'angle-down'" class="kirby-structure-option" @click="toggle(index)" />
              <kirby-button icon="trash" class="kirby-structure-option" @click="confirmRemove(index)" />
            </nav>
          </div>
          <div v-if="!disabled" v-show="isActive(index)" class="kirby-structure-form">
            <kirby-fieldset
              ref="form"
              :fields="fields"
              v-model="items[index]"
              class="kirby-structure-fieldset"
              @input="onInput"
            />
          </div>
        </li>
      </kirby-draggable>
      <kirby-dialog
        v-if="!disabled"
        ref="remove"
        button="Delete"
        theme="negative"
        @submit="remove"
      >
        <kirby-text>{{ "Do you really want to delete this item?" | t("structure.delete.confirm") }}</kirby-text>
      </kirby-dialog>
    </template>

  </kirby-field>
</template>

<script>
import Field from "../Field.vue";

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
      items: this.value,
      active: null
    };
  },
  watch: {
    value(value) {
      this.items = value;
    }
  },
  methods: {
    add() {
      let data = {};

      Object.keys(this.fields).forEach(fieldName => {
        const field = this.fields[fieldName];

        if (field.default) {
          data[fieldName] = field.default;
        }
      });

      this.items.push(data);
      this.active = this.items.length - 1;
      this.onInput();
    },
    confirmRemove(index) {
      this.active = index;
      this.$refs.remove.open();
    },
    focus() {
      this.$refs.add.focus();
    },
    isActive(index) {
      return this.active === index;
    },
    jump(index, field) {
      this.active = index;
      this.$nextTick(() => {
        this.$refs.form[index].focus(field);
      });
    },
    displayText(value) {
      if (typeof value === "object") {
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
    remove() {
      this.items.splice(this.active, 1);
      this.active = null;
      this.$refs.remove.close();
      this.onInput();
    },
    toggle(index) {
      if (this.active === index) {
        this.active = null;
      } else {
        this.active = index;
        this.$nextTick(() => {
          this.$refs.form[index].focus();
        });
      }
    },
  }
}
</script>

<style lang="scss">
.kirby-structure-field {
  list-style: none;
}

.kirby-structure-item {
  position: relative;
  margin-bottom: 2px;
  box-shadow: $box-shadow-card;
}
.kirby-structure-item:last-child {
  margin-bottom: 0;
}
.kirby-structure-item.sortable-ghost {
  background: $color-inset;
  box-shadow: $color-border 0 0 0 1px inset, $box-shadow-inset;
}
.kirby-structure-item.sortable-ghost * {
  visibility: hidden;
}

.kirby-structure-item[data-active] {
  position: relative;
  z-index: 1;
  box-shadow: rgba($color-dark, .1) 0 0px 30px, $color-backdrop 0 0 0 3000px;
}
.kirby-structure-item-handle {
  width: 2rem;
  background: $color-white;
  cursor: -webkit-grab;
  flex-shrink: 0;
  border-right: 1px solid $color-background;
}
.kirby-structure-item-handle:active {
  cursor: -webkit-grabbing;
}
.kirby-structure-item-handle svg {
  opacity: 0.25;
}
.kirby-structure-item-wrapper {
  display: flex;
}
.kirby-structure-item-content {
  flex-grow: 1;
}
.kirby-structure-item-content {
  display: grid;
  grid-column-gap: 2px;
  grid-template-columns: repeat(auto-fit, minmax(0, 1fr));
  background: $color-white;
  height: calc(2.25rem + 1px);
}
.kirby-structure-item-text {
  padding: 0 0.75rem;
  display: flex;
  align-items: center;
  font-size: $font-size-small;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  border-right: 1px solid $color-background;
}
.kirby-structure-item-text:not(:first-child) {
  display: none;
  color: $color-dark-grey;
  font-size: $font-size-small;

  @media screen and (min-width: 45rem) {
    display: flex;
  }
}
.kirby-structure-content {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.kirby-structure-item-text:first-child {
  min-width: 100px;
}
.kirby-structure-content,
.kirby-structure-option {
  padding: 0.5rem 0.75rem;
  line-height: 1em;
}
.kirby-structure-item-options {
  display: flex;
  background: #fff;
  flex-shrink: 0;
}
.kirby-structure-option {
  width: 2.5rem;
}
.kirby-structure-form {
  position: relative;
  background: $color-background;
  padding: 2rem 2rem 3rem;

  &::before {
    position: absolute;
    content: "";
    top: 0;
    right: 2.5rem;
    margin-right: -6px;
  }
}
.kirby-structure-item:last-child .kirby-structure-form {
  border-bottom: 1px solid $color-border;
}
</style>
