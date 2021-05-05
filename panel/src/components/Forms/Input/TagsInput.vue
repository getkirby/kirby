<template>
  <k-draggable
    ref="box"
    :list="tags"
    :data-layout="layout"
    :options="dragOptions"
    :dir="direction"
    class="k-tags-input"
    @end="onInput"
  >
    <k-tag
      v-for="(tag, tagIndex) in tags"
      :ref="tag.value"
      :key="tagIndex"
      :removable="!disabled"
      name="tag"
      @click.native.stop
      @blur.native="selectTag(null)"
      @focus.native="selectTag(tag)"
      @keydown.native.left="navigate('prev')"
      @keydown.native.right="navigate('next')"
      @dblclick.native="edit(tag)"
      @remove="remove(tag)"
    >
      {{ tag.text }}
    </k-tag>
    <span slot="footer" class="k-tags-input-element">
      <k-autocomplete
        ref="autocomplete"
        :options="options"
        :skip="skip"
        @select="addTag"
        @leave="$refs.input.focus()"
      >
        <input
          :id="id"
          ref="input"
          v-model.trim="newTag"
          :autofocus="autofocus"
          :disabled="disabled || (max && tags.length >= max)"
          :name="name"
          autocomplete="off"
          type="text"
          @input="type($event.target.value)"
          @blur="blurInput"
          @keydown.meta.s="blurInput"
          @keydown.left.exact="leaveInput"
          @keydown.enter.exact="enter"
          @keydown.tab.exact="tab"
          @keydown.backspace.exact="leaveInput"
        >
      </k-autocomplete>
    </span>
  </k-draggable>
</template>

<script>
import { required, minLength, maxLength } from "vuelidate/lib/validators";
import direction from "@/helpers/direction.js";

export default {
  inheritAttrs: false,
  props: {
    autofocus: Boolean,
    accept: {
      type: String,
      default: "all"
    },
    disabled: Boolean,
    icon: {
      type: [String, Boolean],
      default: "tag"
    },
    id: [Number, String],
    /**
     * You can set the layout to `list` to extend the width of each tag 
     * to 100% and show them in a list. This is handy in narrow columns 
     * or when a list is a more appropriate design choice for the input 
     * in general.
     */
    layout: String,
    /**
     * The maximum number of accepted tags
     */
    max: Number,
    /**
     * The minimum number of required tags
     */
    min: Number,
    name: [Number, String],
    /**
     * Options will be shown in the autocomplete dropdown 
     * as soon as you start typing.
     */
    options: {
      type: Array,
      default() {
        return [];
      }
    },
    required: Boolean,
    separator: {
      type: String,
      default: ","
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
      tags: this.prepareTags(this.value),
      selected: null,
      newTag: null,
      tagOptions: this.options.map(tag => {
        if (this.icon && this.icon.length > 0) {
          tag.icon = this.icon;
        }
        return tag;
      }, this)
    };
  },
  computed: {
    direction() {
      return direction(this);
    },
    dragOptions() {
      return {
        delay: 1,
        disabled: !this.draggable,
        draggable: ".k-tag"
      };
    },
    draggable() {
      return this.tags.length > 1;
    },
    skip() {
      return this.tags.map(tag => tag.value);
    }
  },
  watch: {
    value(value) {
      this.tags = this.prepareTags(value);
      this.onInvalid();
    }
  },
  mounted() {
    this.onInvalid();

    if (this.$props.autofocus) {
      this.focus();
    }
  },
  methods: {
    addString(string) {
      if (!string) {
        return;
      }

      string = string.trim();

      if (string.includes(this.separator)) {
        string.split(this.separator).forEach(tag => {
          this.addString(tag);
        });

        return;
      }

      if (string.length === 0) {
        return;
      }

      if (this.accept === "options") {
        const option = this.options.filter(
          option => option.text === string
        )[0];

        if (!option) {
          return;
        }

        this.addTag(option);
      } else {
        this.addTag({ text: string, value: string });
      }
    },
    addTag(tag) {
      this.addTagToIndex(tag);
      this.$refs.autocomplete.close();
      this.$refs.input.focus();
    },
    addTagToIndex(tag) {
      if (this.accept === "options") {
        const option = this.options.filter(
          option => option.value === tag.value
        )[0];

        if (!option) {
          return;
        }
      }

      if (
        this.index(tag) === -1 &&
        (!this.max || this.tags.length < this.max)
      ) {
        this.tags.push(tag);
        this.onInput();
      }

      this.newTag = null;
    },
    blurInput(event) {
      let related = event.relatedTarget || event.explicitOriginalTarget;

      if (
        this.$refs.autocomplete.$el &&
        this.$refs.autocomplete.$el.contains(related)
      ) {
        return;
      }

      if (this.$refs.input.value.length) {
        this.addTagToIndex(this.$refs.input.value);
        this.$refs.autocomplete.close();
      }
    },
    edit(tag) {
      this.newTag = tag.text;
      this.$refs.input.select();
      this.remove(tag);
    },
    enter(event) {
      if (!this.newTag || this.newTag.length === 0) {
        return true;
      }

      event.preventDefault();
      this.addString(this.newTag);
    },
    focus() {
      this.$refs.input.focus();
    },
    get(position) {
      let nextIndex = null;
      let currIndex = null;

      switch (position) {
        case "prev":
          if (!this.selected) return;

          currIndex = this.index(this.selected);
          nextIndex = currIndex - 1;

          if (nextIndex < 0) return;
          break;

        case "next":
          if (!this.selected) return;

          currIndex = this.index(this.selected);
          nextIndex = currIndex + 1;

          if (nextIndex >= this.tags.length) return;
          break;

        case "first":
          nextIndex = 0;
          break;

        case "last":
          nextIndex = this.tags.length - 1;
          break;

        default:
          nextIndex = position;
          break;
      }

      let nextTag = this.tags[nextIndex];

      if (nextTag) {
        let nextRef = this.$refs[nextTag.value];

        if (nextRef && nextRef[0]) {
          return {
            ref: nextRef[0],
            tag: nextTag,
            index: nextIndex
          };
        }
      }

      return false;
    },
    index(tag) {
      return this.tags.findIndex(item => item.value === tag.value);
    },
    onInput() {
      this.$emit("input", this.tags);
    },
    onInvalid() {
      this.$emit("invalid", this.$v.$invalid, this.$v);
    },
    leaveInput(e) {
      if (
        e.target.selectionStart === 0 &&
        e.target.selectionStart === e.target.selectionEnd &&
        this.tags.length !== 0
      ) {
        this.$refs.autocomplete.close();
        this.navigate("last");
        e.preventDefault();
      }
    },
    navigate(position) {
      var result = this.get(position);
      if (result) {
        result.ref.focus();
        this.selectTag(result.tag);
      } else if (position === "next") {
        this.$refs.input.focus();
        this.selectTag(null);
      }
    },
    prepareTags(value) {
      if (Array.isArray(value) === false) {
        return [];
      }

      return value.map(tag => {
        if (typeof tag === "string") {
          return {
            text: tag,
            value: tag
          };
        } else {
          return tag;
        }
      });
    },
    remove(tag) {
      // get neighboring tags
      const prev = this.get("prev");
      const next = this.get("next");

      // remove tag and fire input event
      this.tags.splice(this.index(tag), 1);
      this.onInput();

      if (prev) {
        this.selectTag(prev.tag);
        prev.ref.focus();
      } else if (next) {
        this.selectTag(next.tag);
      } else {
        this.selectTag(null);
        this.$refs.input.focus();
      }
    },
    select() {
      this.focus();
    },
    selectTag(tag) {
      this.selected = tag;
    },
    tab(event) {
      if (this.newTag && this.newTag.length > 0) {
        event.preventDefault();
        this.addString(this.newTag);
      }
    },
    type(value) {
      this.newTag = value;
      this.$refs.autocomplete.search(value);
    }
  },
  validations() {
    return {
      tags: {
        required: this.required ? required : true,
        minLength: this.min ? minLength(this.min) : true,
        maxLength: this.max ? maxLength(this.max) : true
      }
    };
  }
};
</script>

<style>
.k-tags-input {
  display: flex;
  flex-wrap: wrap;
}
.k-tags-input .k-sortable-ghost {
  background: var(--color-focus);
}
.k-tags-input-element {
  flex-grow: 1;
  flex-basis: 0;
  min-width: 0;
}
.k-tags-input:focus-within .k-tags-input-element {
  flex-basis: 4rem;
}
.k-tags-input-element input {
  font: inherit;
  border: 0;
  width: 100%;
  background: none;
}
.k-tags-input-element input:focus {
  outline: 0;
}
.k-tags-input[data-layout="list"] .k-tag {
  width: 100%;
  margin-right: 0 !important;
}
</style>
