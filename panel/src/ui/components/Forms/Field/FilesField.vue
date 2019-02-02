<template>
  <k-field v-bind="$props" class="k-files-field">
    <k-button
      v-if="more"
      slot="options"
      icon="add"
      @click="open"
    >
      {{ $t('select') }}
    </k-button>
    <template v-if="selected.length">
      <k-draggable
        :element="elements.list"
        :list="selected"
        :data-size="size"
        :handle="true"
        @end="onInput"
      >
        <component
          v-for="(file, index) in selected"
          :is="elements.item"
          :key="file.filename"
          :sortable="selected.length > 1"
          :text="file.text"
          :link="file.link"
          :info="file.info"
          :image="file.image"
          :icon="file.icon"
        >
          <k-button
            slot="options"
            :tooltip="$t('remove')"
            icon="remove"
            @click="remove(index)"
          />
        </component>
      </k-draggable>
    </template>
    <k-empty
      v-else
      :layout="layout"
      icon="image"
      @click="open"
    >
      {{ empty || $t('field.files.empty') }}
    </k-empty>
    <k-files-dialog ref="selector" @submit="select" />
  </k-field>
</template>

<script>
import Field from "../Field.vue";

export default {
  inheritAttrs: false,
  props: {
    ...Field.props,
    empty: String,
    layout: String,
    max: Number,
    multiple: Boolean,
    parent: String,
    size: String,
    value: {
      type: Array,
      default() {
        return [];
      }
    }
  },
  data() {
    return {
      selected: this.value
    };
  },
  computed: {
    elements() {
      const layouts = {
        cards: {
          list: "k-cards",
          item: "k-card"
        },
        list: {
          list: "k-list",
          item: "k-list-item"
        }
      };

      if (layouts[this.layout]) {
        return layouts[this.layout];
      }

      return layouts["list"];
    },
    more() {
      if (!this.max) {
        return true;
      }

      return this.max > this.selected.length;
    }
  },
  watch: {
    value(value) {
      this.selected = value;
    }
  },
  created() {
    this.$events.$on("file.delete", this.unset);
  },
  destroyed() {
    this.$events.$off("file.delete", this.unset);
  },
  methods: {
    open() {
      return this.$api
        .get(this.endpoints.field)
        .then(files => {
          const selectedIds = this.selected.map(file => file.id);

          files = files.map(file => {
            file.selected = selectedIds.indexOf(file.id) !== -1;

            file.thumb = this.image || {};
            file.thumb.url = false;

            if (file.thumbs && file.thumbs.tiny) {
              file.thumb.url = file.thumbs.medium;
            }

            return file;
          });

          this.$refs.selector.open(files, {
            max: this.max,
            multiple: this.multiple
          });
        })
        .catch(() => {
          this.$store.dispatch(
            "notification/error",
            "The files query does not seem to be correct"
          );
        });
    },
    remove(index) {
      this.selected.splice(index, 1);
      this.onInput();
    },
    focus() {},
    onInput() {
      this.$emit("input", this.selected);
    },
    select(files) {
      this.selected = files;
      this.onInput();
    },
    unset(id) {
      this.selected = this.selected.filter(item => item.id !== id);
      this.onInput();
    }
  }
};
</script>
