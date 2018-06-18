<template>
  <kirby-draggable
    v-model="tags"
    :options="{disabled: !draggable, forceFallback: true, draggable: '.kirby-tag', delay: 1}"
    class="kirby-tags-input"
    @input="onInput"
  >
    <kirby-tag
      v-for="tag in tags"
      :ref="tag.value"
      :key="tag.value"
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
    </kirby-tag>
    <span slot="footer" class="kirby-tags-input-element">
      <kirby-autocomplete
        ref="autocomplete"
        :options="options"
        :skip="skip"
        @select="addTag"
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
          @input="newTag = $event.target.value; $refs.autocomplete.search(newTag)"
          @keydown.left="leaveInput"
          @keydown.enter="enter"
          @keydown.tab="tab"
          @keydown.backspace="leaveInput"
        >
      </kirby-autocomplete>
    </span>
  </kirby-draggable>
</template>

<script>
import {
  required,
  minLength,
  maxLength
} from "vuelidate/lib/validators";

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
      type: String,
      default: "tag"
    },
    id: [Number, String],
    max: Number,
    min: Number,
    name: [Number, String],
    separator: {
      type: String,
      default: ","
    },
    options: {
      type: Array,
      default() {
        return [];
      }
    },
    required: Boolean,
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
  mounted() {
    this.onInvalid();

    if (this.$props.autofocus) {
      this.focus();
    }
  },
  watch: {
    value(value) {
      this.tags = this.prepareTags(value);
      this.onInvalid();
    }
  },
  computed: {
    draggable() {
      return this.tags.length > 1;
    },
    hasChanged() {
      return true;
    },
    skip() {
      return this.tags.map(tag => tag.text);
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
      if (this.index(tag) === -1 && (!this.max || this.tags.length < this.max)) {
        this.tags.push(tag);
        this.onInput(this.tags);
      }

      this.newTag = null;
      this.$refs.autocomplete.close();
      this.$refs.input.focus();
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
    onInput(value) {
      this.$emit("input", value);
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
      let prev = this.get("prev");

      // remove tag and fire input event
      this.tags.splice(this.index(tag), 1);
      this.onInput(this.tags);

      if (prev) {
        prev.ref.focus();
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
}

</script>

<style lang="scss">
.kirby-tags-input {
  display: flex;
  flex-wrap: wrap;
}
.kirby-tags-input .sortable-ghost {
  background: $color-focus;
}
.kirby-tags-input-element {
  flex-grow: 1;
}
.kirby-tags-input-element input {
  font: inherit;
  border: 0;
  width: 100%;
  background: none;
}
.kirby-tags-input-element input:focus {
  outline: 0;
}
</style>
