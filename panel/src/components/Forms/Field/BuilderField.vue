<template>
  <k-field
    v-bind="$props"
    class="k-builder-field"
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

    <k-draggable
      v-bind="draggableOptions"
      class="k-builder-blocks"
      @sort="onInput"
    >
      <details
        v-for="(block, index) in blocks"
        :key="block.id"
        :set="blockOptions = fieldset(block)"
        :data-disabled="blockOptions.disabled"
        :data-hidden="block.attrs.hide == true"
        :data-translate="blockOptions.translate"
        :class="'k-builder-block k-builder-fieldset-' + block.type"
        :open="isOpen(block)"
        @mouseenter="isHovered = block.id"
        @mouseleave="isHovered = false"
      >
        <summary class="k-builder-block-header" @click.prevent="toggle(block)">
          <div class="k-builder-block-header-container">
            <k-sort-handle :icon="isHovered === block.id ? 'sort' : blockOptions.icon || 'sort'" class="k-builder-block-handle" />
            <span class="k-builder-block-label">
              {{ $helper.string.template(blockOptions.label, block.content) }}
            </span>

            <nav
              v-if="isOpen(block) && hasTabs(blockOptions)"
              class="k-builder-block-tabs"
            >
              <k-button
                v-for="(tab, tabId) in blockOptions.tabs"
                :key="tabId"
                :current="isCurrentTab(block, tabId)"
                :icon="tab.icon || 'settings'"
                class="k-builder-block-tab"
                @click.stop="open(block, tabId)"
              >
                {{ tab.label }}
              </k-button>
            </nav>

            <k-button
              v-if="block.attrs.hide"
              class="k-builder-block-status"
              icon="hidden"
              @click.stop="toggleVisibility(block)"
            />

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
                <template v-if="hasTabs(blockOptions)">
                  <hr>
                  <k-dropdown-item
                    v-for="(tab, tabId) in blockOptions.tabs"
                    :key="tabId"
                    :current="isCurrentTab(block, tabId)"
                    :icon="tab.icon || 'settings'"
                    @click="open(block, tabId)"
                  >
                    {{ tab.label }}
                  </k-dropdown-item>
                </template>
                <hr>
                <k-dropdown-item :icon="block.attrs.hide ? 'preview' : 'hidden'" @click="toggleVisibility(block)">
                  {{ block.attrs.hide === true ? $t('show') : $t('hide') }}
                </k-dropdown-item>
                <k-dropdown-item :disabled="isFull" icon="copy" @click="duplicate(block)">
                  {{ $t("duplicate") }}
                </k-dropdown-item>
                <hr>
                <k-dropdown-item icon="trash" @click="onRemove(block)">
                  {{ $t("delete") }}
                </k-dropdown-item>
              </k-dropdown-content>
            </k-dropdown>
          </div>
        </summary>
        <div v-if="isOpen(block)" class="k-builder-block-body">
          <k-fieldset
            :ref="'fieldset-' + block.id"
            :fields="fields(block)"
            :value="block.content"
            class="k-builder-block-form"
            @input="updateContent(block, $event)"
          />
        </div>
      </details>

      <template #footer>
        <k-empty
          icon="box"
          class="k-builder-field-empty"
          @click="select(blocks.length)"
        >
          {{ empty || $t("field.builder.empty") }}
        </k-empty>
      </template>
    </k-draggable>

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
            {{ $helper.string.template(fieldset.label) }}
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

export default {
  inheritAttrs: false,
  props: {
    ...Field.props,
    empty: String,
    fieldsets: Object,
    group: String,
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
      tabs: {},
      trash: null,
    };
  },
  computed: {
    draggableOptions() {
      return {
        id: this._uid,
        handle: true,
        list: this.blocks,
        move: this.move,
        data: {
          fieldsets: this.fieldsets,
          isFull: this.isFull
        },
        options: {
          group: this.group
        }
      };
    },
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
      const tabId  = this.tabs[block.id] || null;
      const tabs   = this.fieldset(block).tabs;
      const tab    = tabs[tabId] || Object.values(tabs)[0];
      const fields = tab.fields || {};

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
    hasTabs(fieldset) {
      return Object.keys(fieldset.tabs).length > 1
    },
    isCurrentTab(block, tabId) {
      if (this.tabs[block.id] === undefined) {
        this.tabs[block.id] = tabId;
        return true;
      }
      return this.tabs[block.id] === tabId;
    },
    isOpen(block) {
      return this.opened.includes(block.id);
    },
    move(event) {
      // moving block between fields
      if (event.from !== event.to) {
        const block = event.draggedContext.element;
        const to    = event.relatedContext.component.componentData || event.relatedContext.component.$parent.componentData;

        // fieldset is not supported in target field
        if (Object.keys(to.fieldsets).includes(block.type) === false) {
          return false;
        }

        // target field has already reached max number of blocks
        if (to.isFull === true) {
          return false;
        }
      }

      return true;
    },
    onInput() {
      this.$emit("input", this.blocks);
    },
    onRemove(block) {
      this.trash = block;
      this.$refs.remove.open();
    },
    open(block, tabId, focus = true) {
      this.close(block);
      this.opened.push(block.id);

      // use the given tab id or the already selected tab or the first tab
      tabId = tabId || this.tabs[block.id] || Object.keys(this.fieldset(block).tabs)[0];
      this.tabs[block.id] = tabId;

      if (focus) {
        this.$nextTick(() => {
          const fieldset = this.$refs["fieldset-" + block.id][0];
          if (fieldset) {
            fieldset.focus();
          }
        });
      }
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
    toggleVisibility(block) {
      if (Array.isArray(block.attrs) === true) {
        this.$set(block, "attrs", {});
      }

      if (block.attrs.hide === true) {
        this.$set(block.attrs, "hide", false);
      } else {
        this.close(block);
        this.$set(block.attrs, "hide", true);
      }

      this.onInput();
    },
    updateContent(block, content) {
      this.$set(block, "content", content);
      this.onInput();
    }
  }
};
</script>

<style lang="scss">
.k-builder-field {
  position: relative;
}
.k-builder-field .k-sortable-ghost {
  outline: 2px solid $color-focus;
  cursor: grabbing;
  cursor: -moz-grabbing;
  cursor: -webkit-grabbing;
}
.k-builder-field-empty {
  cursor: pointer;

  .k-builder-field > .k-builder-blocks > &:not(:only-child) {
    display: none;
  }
}
.k-builder-block {
  position: relative;
  background: $color-white;
  box-shadow: $shadow;
  margin-bottom: 2px;
}
.k-builder-block:last-child {
  margin-bottom: 0;
}
.k-builder-block[data-disabled] {
  cursor: not-allowed;
  opacity: .4;
}
.k-builder-block[data-disabled] * {
  pointer-events: none;
}
.k-builder-block-header {
  height: 38px;
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
.k-builder-block-header-container {
  display: flex;
  align-items: center;
}
.k-builder-block-handle.k-sort-handle {
  width: 2.25rem;
  height: 38px;
}
.k-builder-block-handle.k-sort-handle > svg {
  opacity: .25;
}
.k-builder-block-handle.k-sort-handle:hover > svg {
  opacity: 1;
}
.k-builder-block-label {
  display: flex;
  flex-grow: 1;
  height: 36px;
  align-items: center;
  line-height: 1;
  font-size: $text-sm;
}
.k-builder-block[data-hidden]:not([open]) {
  background: rgba($color-white, .325);
}
.k-builder-block[data-hidden] .k-builder-block-status  {
  opacity: .325;
}
.k-builder-block[data-hidden] .k-builder-block-label {
  color: $color-gray-400;
}
.k-builder-block-label .k-icon {
  margin-left: .75rem;
}

.k-builder-block-tabs {
  display: none;
  align-items: center;
  margin-right: .5rem;
}
@media screen and (min-width: $breakpoint-md) {
  .k-builder-block[open] .k-builder-block-tabs {
    display: flex;
  }
}
.k-builder-block-tab {
  position: relative;
  padding: .5rem .75rem;
  height: 38px;
}
.k-builder-block-tab > * {
  position: relative;
  z-index: 1;
}
.k-builder-block-tab[aria-current]::before {
  content: "";
  position: absolute;
  top: 2px;
  left: 0;
  bottom: 0;
  right: 0;
  background: $color-background;
  border-top-left-radius: $rounded;
  border-top-right-radius: $rounded;
}

.k-builder-block-options-toggle {
  display: flex;
  width: 2.5rem;
  height: 36px;
}
.k-builder-block-body {
  background: $color-background;
  border: 2px solid $color-white;
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
  box-shadow: $shadow;
  height: 36px;
  padding: 0 .75rem;
}

</style>
