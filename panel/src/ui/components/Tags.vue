<template>
  <k-draggable
    ref="box"
    :list="tags"
    :data-layout="layout"
    :options="dragOptions"
    class="k-tags flex"
    @end="onInput"
  >
    <k-tag
      v-for="(tag, tagIndex) in tags"
      ref="tag"
      :key="tagIndex"
      :removable="removable"
      name="tag"
      @click.native.stop
      @blur.native="onSelect(null)"
      @focus.native="onSelect(tag)"
      @keydown.native.left="navigate('prev')"
      @keydown.native.right="navigate('next')"
      @dblclick.native="onEdit(tag, tagIndex)"
      @remove="onRemove(tagIndex)"
    >
      {{ tag.text }}
    </k-tag>
  </k-draggable>
</template>

<script>
export default {
  props: {
    layout: String,
    max: Number,
    removable: {
      type: Boolean,
      default: true
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
      tags: this.value,
      selected: null
    }
  },
  computed: {
    draggable() {
      return this.tags.length > 1;
    },
    dragOptions() {
      return {
        delay: 20,
        disabled: !this.draggable,
        draggable: ".k-tag"
      };
    },
  },
  watch: {
    value(value) {
      this.tags = value;
    }
  },
  methods: {
    add(tag) {
      if (
        this.indexOf(tag) === -1 &&
        (!this.max || this.tags.length < this.max)
      ) {
        this.tags.push(tag);
        this.onInput();
      }
    },
    get(position) {
      let current, index;

      switch (position) {
        case "first":
          return this.tagFor(0);
          break;
        case "last":
          return this.tagFor(this.tags.length - 1);
          break;
        case "prev":
          if (!this.selected) return;

          current = this.indexOf(this.selected);
          index   = current - 1;

          if (index < 0) return;

          break;
        case "next":
          if (!this.selected) return;

          current = this.indexOf(this.selected);
          index   = current + 1;

          if (index >= this.tags.length) return;

          break;
      }

      return this.tagFor(index);
    },
    focus(position) {
      const result = this.get(position);

      if (result) {
        result.ref.focus();
      }
    },
    indexOf(tag) {
      return this.tags.findIndex(item => item.value === tag.value);
    },
    navigate(position) {
      const result = this.get(position);

      if (result) {
        this.onSelect(result.tag);
        result.ref.focus();
        return;
      }

      // Navigating outside of tags list
      if (position === "next") {
        this.onSelect(null);
        this.$refs.tag[this.tags.length - 1].$el.blur();
        this.$emit("navigate-next");
      }
    },
    onEdit(tag, index) {
      this.onRemove(index);
      this.$emit('edit', tag);
    },
    onInput() {
      this.$emit("input", this.tags);
    },
    onRemove(index) {
       // get neighboring tags
      const prev = this.get("prev");
      const next = this.get("next");

      // remove tag and fire input event
      this.tags.splice(index, 1);
      this.onInput();

      // focus neighboring tag or emit blur
      if (prev) {
        this.onSelect(prev.tag);
        prev.ref.focus();
      } else if (next) {
        this.onSelect(next.tag);
      } else {
        this.onSelect(null);
        this.$emit("blur");
      }
    },
    onSelect(tag) {
      this.selected = tag;
    },
    tagFor(index) {
      const tag = this.tags[index];
      const ref = this.$refs.tag[index]

      return {
        ref: ref,
        tag: tag,
        index: index
      };
    },
  }
};
</script>

<style lang="scss">
.k-tags:not([data-layout="list"]) > .k-tag + .k-tag {
  margin-left: .2rem;
}
.k-tags[data-layout="list"] {
  flex-wrap: wrap;
}
.k-tags[data-layout="list"] > .k-tag {
  width: 100%;
}
.k-tags[data-layout="list"] > .k-tag + .k-tag {
  margin-top: .2rem;
}
.k-tags .k-sortable-ghost {
  background: $color-focus;
  box-shadow: none;
}
</style>
