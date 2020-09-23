<template>
  <k-field
    v-bind="$props"
    class="k-builder-field"
    @mouseenter.native="isHovered = true"
    @mouseleave.native="isHovered = false"
  >
    <k-dropdown slot="options">
      <k-button icon="cog" @click="$refs.options.toggle()" />
      <k-dropdown-content ref="options" align="right">
        <k-dropdown-item :disabled="isFull" icon="add" @click="select(blocks.length)">
          {{ $t('add') }}
        </k-dropdown-item>
        <k-dropdown-item :disabled="isEmpty" :icon="hasOpened ? 'collapse' : 'expand'" @click="toggleAll()">
          {{ hasOpened ? $t('collapse.all') : $t('expand.all') }}
        </k-dropdown-item>
        <hr>
        <k-dropdown-item :disabled="isEmpty" icon="trash" @click="$refs.removeAll.open()">
          {{ $t('delete.all') }}
        </k-dropdown-item>
      </k-dropdown-content>
    </k-dropdown>

    <template v-if="blocks.length === 0">
      <k-empty icon="box" @click="select(blocks.length)">
        {{ $t("field.builder.empty") }}
      </k-empty>
    </template>

    <template v-else>
      <k-draggable :handle="true" :list="blocks" element="k-grid" @end="sort">
        <k-column
          v-for="(block, index) in blocks"
          :key="block.id"
          :width="'1/' + columns"
          :data-disabled="fieldset(block).disabled"
          :data-translate="fieldset(block).translate"
          class="k-builder-column"
        >
          <k-builder-block-creator
            v-if="!isFull"
            :fieldsets="fieldsets"
            :vertical="columns > 1"
            @select="select(index)"
          />
          <details
            :class="'k-builder-block k-builder-fieldset-' + block.type"
            :open="isOpen(block)"
          >
            <summary class="k-builder-block-header" @click.prevent="toggle(block)">
              <k-sort-handle :icon="isHovered ? 'sort' : fieldset(block).icon || 'sort'" class="k-builder-block-handle" />
              <span class="k-builder-block-label">
                {{ $helper.string.template(fieldset(block).label, block) }}
              </span>
              <k-dropdown>
                <k-button
                  class="k-builder-block-options-toggle"
                  icon="dots"
                  @click="$refs['options-' + block.id][0].toggle()"
                />
                <k-dropdown-content :ref="'options-' + block.id" align="right">
                  <k-dropdown-item :disabled="isFull" icon="angle-up" @click="select(index)">
                    {{ $t("insert.before") }}
                  </k-dropdown-item>
                  <k-dropdown-item :disabled="isFull" icon="angle-down" @click="select(index + 1)">
                    {{ $t("insert.after") }}
                  </k-dropdown-item>
                  <hr>
                  <k-dropdown-item :disabled="isFull" icon="copy" @click="duplicate(block)">
                    {{ $t("duplicate") }}
                  </k-dropdown-item>
                  <hr>
                  <k-dropdown-item icon="trash" @click="onRemove(block)">
                    {{ $t("delete") }}
                  </k-dropdown-item>
                </k-dropdown-content>
              </k-dropdown>
            </summary>
            <div class="k-builder-block-body" v-if="isOpen(block)">
              <k-fieldset
                :ref="'fieldset-' + block.id"
                :fields="fields(block)"
                :value="block.content"
                class="k-builder-block-form"
                @input="update(index, $event)"
              />
            </div>
          </details>
          <k-builder-block-creator
            v-if="index === blocks.length - 1 && !isFull"
            :fieldsets="fieldsets"
            :last="true"
            :vertical="columns > 1"
            @select="select(blocks.length)"
          />
        </k-column>
      </k-draggable>
    </template>

    <k-dialog
      ref="fieldsets"
      :cancel-button="false"
      :submit-button="false"
      class="k-builder-fieldsets-dialog"
      size="medium"
    >
      <k-headline>{{ $t("field.builder.fieldsets.label") }}</k-headline>
      <ul class="k-builder-fieldsets">
        <li v-for="fieldset in fieldsets" :key="fieldset.name">
          <k-button :icon="fieldset.icon || 'box'" @click="add(fieldset.type)">
            {{ fieldset.name }}
          </k-button>
        </li>
      </ul>
    </k-dialog>

    <k-remove-dialog ref="remove" @submit="remove">
      {{ $t("field.builder.delete.confirm") }}
    </k-remove-dialog>

    <k-remove-dialog ref="removeAll" @submit="removeAll">
      {{ $t("field.builder.delete.all.confirm") }}
    </k-remove-dialog>

  </k-field>
</template>

<script>
import Field from "../Field.vue";
import BlockCreator from "./BuilderField/BlockCreator.vue";

export default {
  inheritAttrs: false,
  components: {
    "k-builder-block-creator": BlockCreator,
  },
  props: {
    ...Field.props,
    columns: Number,
    fieldsets: Object,
    max: {
      type: Number,
      default: null,
    },
    value: {
      type: Array,
      default() {
        return [];
      }
    }
  },
  data() {
    return {
      blocks: this.value,
      hasOpened: false,
      isHovered: false,
      nextIndex: this.value.length,
      opened: [],
      trash: null,
    };
  },
  computed: {
    isEmpty() {
      return this.blocks.length === 0;
    },
    isFull() {
      if (this.max === null) {
        return false;
      }

      return this.blocks.length >= this.max;
    }
  },
  watch: {
    opened(value) {
      this.hasOpened = value.length > 0;
    },
    value() {
      this.blocks = this.value;
    }
  },
  methods: {
    async add(type) {
      try {
        const block = await this.$api.get(this.endpoints.field + "/fieldsets/" + type);
        this.blocks.splice(this.nextIndex, 0, block);
        this.open(this.blocks[this.nextIndex]);
        this.$refs.fieldsets.close();
        this.onInput();
      } catch (e) {
        this.$refs.fieldsets.error(e.message);
      }
    },
    close(block) {
      this.opened = this.opened.filter(id => id !== block.id);
    },
    async duplicate(block) {
      const response = await this.$api.get(this.endpoints.field + "/uuid");
      const copy = {
        ...this.$helper.clone(block),
        id: response["uuid"]
      };
      this.blocks.push(copy);
      this.onInput();
    },
    fields(block) {
      const fields = this.fieldset(block).fields || {};

      if (Object.keys(fields).length === 0) {
        return {
          noFields: {
            type: "info",
            text: "This block has no fields"
          }
        };
      }

      Object.keys(fields).forEach(name => {
        let field = fields[name];

        field.section = this.name;
        field.endpoints = {
          field: this.endpoints.field + "/fieldsets/" + block.type + "/fields/" + name,
          section: this.endpoints.section,
          model: this.endpoints.model
        };

        fields[name] = field;
      });

      return fields;
    },
    fieldset(block) {
      return this.fieldsets[block.type];
    },
    isOpen(block) {
      return this.opened.includes(block.id);
    },
    remove() {
      if (this.trash === null) {
        return;
      }

      const index = this.blocks.findIndex(element => element.id === this.trash.id);

      if (index !== -1) {
        this.$delete(this.blocks, index);
        this.$refs.remove.close();
        this.onInput();
      }
    },
    removeAll() {
      this.blocks = [];
      this.opened = [];
      this.nextIndex = null;
      this.trash = null;
      this.onInput();
      this.$refs.removeAll.close();
    },
    sort() {
      this.onInput();
    },
    onInput() {
      console.log(this.blocks);
      this.$emit("input", this.blocks);
    },
    onRemove(block) {
      this.trash = block;
      this.$refs.remove.open();
    },
    open(block, focus = true) {
      if (this.isOpen(block) === false) {
        this.opened.push(block.id);

        if (focus) {
          this.$nextTick(() => {
            const fieldset = this.$refs["fieldset-" + block.id][0];
            if (fieldset) {
              fieldset.focus();
            }
          });
        }
      }
    },
    select(index) {
      this.nextIndex = index;

      if (Object.keys(this.fieldsets).length === 1) {
        const type = Object.values(this.fieldsets)[0].type;
        this.add(type);
      } else {
        this.$refs.fieldsets.open();
      }
    },
    toggle(block) {
      if (this.isOpen(block)) {
        this.close(block);
      } else {
        this.open(block);
      }
    },
    toggleAll() {
      this.opened = this.hasOpened ? [] : this.blocks.map(block => block.id);
    },
    uid(type) {
      return type + "_" + (+new Date) + "_" + this.$helper.string.random(6);
    },
    update(index, value) {
      this.$set(this.blocks[index].content, value);
      this.onInput();
    }
  }
};
</script>

<style lang="scss">
.k-builder-field {
  position: relative;
}
.k-builder-field > .k-grid {
  grid-gap: 2px;
}
.k-builder-column {
  position: relative;
}
.k-builder-block {
  position: relative;
  background: $color-white;
  box-shadow: $box-shadow-card;
}
.k-builder-field .k-sortable-ghost {
  outline: 2px solid $color-focus;
  cursor: grabbing;
  cursor: -moz-grabbing;
  cursor: -webkit-grabbing;
}
.k-builder-block-header {
  height: 36px;
  display: flex;
  align-items: center;
  cursor: pointer;
  list-style: none;
}
.k-builder-block-header::-webkit-details-marker {
  display: none;
}
.k-builder-block-header:focus  {
  position: relative;
  outline: none;
}
.k-builder-block-handle.k-sort-handle {
  width: 2.25rem;
  height: 36px;
}
.k-builder-block-handle.k-sort-handle > svg {
  opacity: .25;
}
.k-builder-block-handle.k-sort-handle:hover > svg {
  opacity: 1;
}
.k-builder-block-label {
  flex-grow: 1;
  font-size: $font-size-small;
}
.k-builder-block-options-toggle {
  display: flex;
  width: 2.5rem;
  height: 36px;
}
.k-builder-block-body {
  background: $color-background;
  border: 2px solid #fff;
  border-top: 0;
  line-height: 0;
}
.k-builder-block-form {
  padding: 1.5rem 2rem 2rem;
}

.k-builder-fieldsets-dialog .k-headline {
  margin-bottom: .75rem;
  margin-top: -.25rem;
}
.k-builder-fieldsets {
  display: grid;
  grid-gap: .5rem;
  grid-template-columns: repeat(2, 1fr);
}
.k-builder-fieldsets .k-button {
  background: $color-white;
  width: 100%;
  text-align: left;
  box-shadow: $box-shadow-card;
  height: 36px;
  padding: 0 .75rem;
}

</style>
