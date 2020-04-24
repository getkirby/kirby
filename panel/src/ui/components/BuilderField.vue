<template>
  <k-field
    :input="_uid"
    v-bind="$props"
    class="k-builder-field"
  >
    <k-draggable
      :handle="true"
      :options="dragOptions"
      :list="blocks"
      @end="onSort"
    >
      <k-builder-block 
        v-for="(block, blockIndex) in blocks"
        :key="blockIndex"
        :ref="'block-' + blockIndex"
        v-model="block.value"
        v-bind="fieldsets[block.type]"
        :index="blockIndex"
        @current="setCurrent(block, blockIndex + $event)"
        @input="onInput"
        @insert="$refs.create.open()"
        @remove="$refs.remove.open()"
        @preview="$refs.preview.open()"
      />
    </k-draggable>

    <!-- Add zone -->
    <k-empty 
      layout="list" 
      class="cursor-pointer flex justify-center"
      @click="
        setCurrent({}, blocks.length); 
        $refs.create.open();
      "
    >
      <k-button icon="add">Add block</k-button>
    </k-empty>

    <!-- Preview drawer -->
    <k-drawer 
      ref="preview" 
      :title="'Preview / ' + previewTitle" 
      flow="vertical"
    >
      <k-builder-preview v-bind="current" />
    </k-drawer>

    <!-- Create dialog -->
    <k-dialog
      ref="create"
      :submitButton="false"
    >
      <k-items
        :items="fieldsetItems"
        :sortable="false"
        @option="onInsert"
      />
    </k-dialog>

    <!-- Remove dialog -->
    <k-remove-dialog
      ref="remove"
      text="Do you really want to delete this block?"
      @submit="onRemove"
    />
  </k-field>
</template>

<script>
import Field from "./Field.vue";

export default {
  inheritAttrs: false,
  props: {
    ...Field.props,
    /**
     * Object of all available fieldset definitions
     */
    fieldsets: {
      type: Object,
      default() {
        return {};
      }
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
      current: {
        fieldset: {},
        block: {}
      }
    };
  },
  watch: {
    value(value) {
      this.blocks = value;
    }
  },
  computed: {
    dragOptions() {
      return {
        draggable: ".k-builder-block"
      };
    },
    fieldsetItems() {
      return Object.keys(this.fieldsets).map(key => {
        return {
          title: this.fieldsets[key].name || this.fieldsets[key].label,
          icon: false,
          image: false,
          fieldset: key,
          options: [
            { icon: "add", text: "Add" }
          ]
        };
      });
    },
    previewTitle() {
      if (this.current.fieldset.label) {
        return this.$helper.string.template(
          this.current.fieldset.label, 
          this.current.block.value
        );
      }

      return this.current.fieldset.name;
    }
  },
  methods: {
    onInput() {
      this.$emit("input", this.blocks);
    },
    onInsert(click, option) {
      this.blocks = this.blocks.splice(this.current.index, 0, {
        type: option.fieldset,
        value: {}
      });
      this.$refs.create.close();
      this.onInput();
      this.$nextTick(() => {
        this.$refs["block-" + this.current.index][0].open();
      });
    },
    onRemove(index) {
      this.blocks = this.blocks.splice(this.current.index, 1);
      this.$refs.remove.close();
      this.onInput();
    },
    onSort(event) {
      this.$emit("input", this.blocks);
    },
    setCurrent(block, index) {
      this.current = {
        fieldset: this.fieldsets[block.type] || {},
        block: block,
        index: index
      };
    }
  }
}
</script>

<style lang="scss">
.k-builder-block-content > header {
  display: flex;
  justify-content: space-between;
  align-content: center;
}
</style>
