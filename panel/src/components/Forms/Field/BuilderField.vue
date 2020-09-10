<template>
  <k-field
    v-bind="$props"
    class="k-builder-field"
    @mouseenter.native="isHovered = true"
    @mouseleave.native="isHovered = false"
  >

    <k-dropdown slot="options">
      <k-button icon="add" @click="select(blocks.length)">Add</k-button>
    </k-dropdown>

    <template v-if="blocks.length === 0">
      <k-empty icon="box" @click="select(blocks.length)">
        No blocks yet
      </k-empty>
    </template>

    <template v-else>
      <k-draggable :handle="true" :list="blocks" element="k-grid" @end="sort">
        <k-column
          v-for="(block, index) in blocks"
          :key="block._uid"
          :width="'1/' + columns"
          class="k-builder-column"
        >
          <k-builder-block-creator
            :fieldsets="fieldsets"
            :vertical="columns > 1"
            @select="select(index)"
          />
          <details class="k-builder-block" :open="isOpen(block)">
            <summary class="k-builder-block-header" @click.prevent="toggle(block)">
              <k-sort-handle :icon="isHovered ? 'sort' : fieldsets[block._key].icon || 'sort'" class="k-builder-block-handle" />
              <span class="k-builder-block-label">
                {{ $helper.string.template(fieldsets[block._key].label, block) }}
              </span>
              <k-dropdown>
                <k-button
                  class="k-builder-block-options-toggle"
                  icon="dots"
                  @click="$refs['options-' + block._uid][0].toggle()"
                />
                <k-dropdown-content :ref="'options-' + block._uid" align="right">
                  <k-dropdown-item icon="copy" @click="duplicate(block)">Duplicate</k-dropdown-item>
                  <k-dropdown-item icon="trash" @click="onRemove(block)">Delete</k-dropdown-item>
                </k-dropdown-content>
              </k-dropdown>
            </summary>
            <div class="k-builder-block-body">
              <k-fieldset
                :fields="fields(block)"
                :value="block"
                class="k-builder-block-form"
                @input="update(index, $event)"
              />
            </div>
          </details>
        </k-column>
      </k-draggable>
    </template>

    <k-dialog
      ref="fieldsets"
      :cancel-button="false"
      :submit-button="false"
      class="k-builder-fieldsets-dialog"
    >
      <k-headline>Please, select a block type …</k-headline>
      <ul class="k-builder-fieldsets">
        <li v-for="fieldset in fieldsets" :key="fieldset.name">
          <k-button :icon="fieldset.icon || 'add'" @click="add(fieldset.key)">
            {{ fieldset.name }}
          </k-button>
        </li>
      </ul>
    </k-dialog>

    <k-dialog
      ref="remove"
      :submit-button="$t('delete')"
      theme="negative"
      @submit="remove"
    >
      <k-text>{{ $t("field.builder.delete.confirm") }}</k-text>
    </k-dialog>
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
      isHovered: false,
      nextIndex: this.value.length,
      opened: [],
      trash: null,
    };
  },
  watch: {
    value() {
      this.blocks = this.value;
    }
  },
  methods: {
    add(type) {
      this.blocks.splice(this.nextIndex, 0, {
        _key: type,
        _uid: this.uid(type)
      });

      this.open(this.blocks[this.nextIndex]);
      this.$refs.fieldsets.close();
      this.onInput();
    },
    close(block) {
      this.opened = this.opened.filter(id => id !== block._uid);
    },
    duplicate(block) {
      let copy = this.$helper.clone(block);
      copy["_uid"] = this.uid(block._key);
      this.blocks.push(copy);
      this.onInput();
    },
    fields(block) {
      const fields = this.fieldsets[block._key].fields || {};

      if (Object.keys(fields).length === 0) {
        return {
          noFields: {
            type: "info",
            text: "This block has no fields"
          }
        };
      }

      return fields;
    },
    isOpen(block) {
      return this.opened.includes(block._uid);
    },
    remove() {
      if (this.trash === null) {
        return;
      }

      const index = this.blocks.findIndex(element => element._uid === this.trash._uid);

      if (index !== -1) {
        this.$delete(this.blocks, index);
        this.$refs.remove.close();
        this.onInput();
      }
    },
    sort() {
      this.onInput();
    },
    onInput() {
      this.$emit("input", this.blocks);
    },
    onRemove(block) {
      this.trash = block;
      this.$refs.remove.open();
    },
    open(block) {
      if (this.isOpen(block) === false) {
        this.opened.push(block._uid);
      }
    },
    select(index) {
      this.nextIndex = index;

      if (Object.keys(this.fieldsets).length === 1) {
        const type = Object.values(this.fieldsets)[0].key;
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
    uid(type) {
      return type + "_" + (+new Date) + "_" + this.$helper.string.random(6);
    },
    update(index, value) {
      this.$set(this.blocks[index], value);
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
  grid-row-gap: .5rem;
  grid-column-gap: .5rem;
}
.k-builder-column {
  position: relative;
}
.k-builder-block {
  position: relative;
  background: $color-white;
  box-shadow: $box-shadow-card;
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
  width: 2rem;
  height: 36px;
  margin-right: .75rem;
  border-right: 1px $color-background solid;
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
