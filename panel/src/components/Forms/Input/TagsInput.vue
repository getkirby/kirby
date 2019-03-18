<template>
  <k-draggable
    ref="box"
    :list="tags"
    :data-layout="layout"
    :options="dragOptions"
    class="k-tags-input"
    @end="onInput"
  >
    <k-tag
      v-for="(tag, tagIndex) in tags"
      :ref="tag.value"
      :key="tagIndex"
      :removable="true"
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
          ref="input"
          :autofocus="autofocus"
          :disabled="disabled || (max && tags.length >= max)"
          :id="id"
          :name="name"
          v-model.trim="newTag"
          autocomplete="off"
          type="text"
          @input="type($event.target.value)"
          @blur="blurInput"
          @keydown.meta.s="blurInput"
          @keydown.left="leaveInput"
          @keydown.enter="enter"
          @keydown.tab="tab"
          @keydown.backspace="leaveInput"
        >
      </k-autocomplete>
    </span>
  </k-draggable>
</template>

<script>
import { required, minLength, maxLength } from "vuelidate/lib/validators";

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
    layout: String,
    max: Number,
    min: Number,
    name: [Number, String],
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
        tag.icon = "tag";
        return tag;
      })
    };
  },
  computed: {
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
      return this.tags.map(tag => tag.text);
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
      if (string.length === 0) return;

      this.addTag({ text: string, value: string });
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
        case "next":
          if (!this.selected) return;
          currIndex = this.index(this.selected);
          nextIndex = position === "prev" ? currIndex - 1 : currIndex + 1;
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
        e.target.selectionStart === e.target.selectionEnd
      ) {
        this.navigate("last");
        this.$refs.autocomplete.close();
        e.preventDefault();
        e.target.blur();
      }
    },
    navigate(position) {
      var result = this.get(position);
      if (result) {
        result.ref.focus();
        this.selected = result.tag;
      } else if (position === "next") {
        this.$refs.input.focus();
        this.selected = null;
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
        prev.ref.focus();
      } else if (next) {
        next.ref.focus();
      } else {
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

<style lang="scss">
.k-tags-input {
  display: flex;
  flex-wrap: wrap;
}
.k-tags-input .k-sortable-ghost {
  background: $color-focus;
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
