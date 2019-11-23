<template>
  <k-draggable
    :list="state"
    :options="dragOptions"
    :data-layout="layout"
    element="k-dropdown"
    class="k-multiselect-input"
    @click.native="$refs.dropdown.toggle"
    @end="onInput"
  >
    <k-tag
      v-for="tag in sorted"
      :ref="tag.value"
      :key="tag.value"
      :removable="true"
      @click.native.stop
      @remove="remove(tag)"
      @keydown.native.left="navigate('prev')"
      @keydown.native.right="navigate('next')"
      @keydown.native.down="$refs.dropdown.open"
    >
      {{ tag.text }}
    </k-tag>

    <k-dropdown-content
      slot="footer"
      ref="dropdown"
      @open="onOpen"
      @close="onClose"
      @keydown.native.esc.stop="close"
    >
      <k-dropdown-item
        v-if="search"
        icon="search"
        class="k-multiselect-search"
      >
        <input
          ref="search"
          v-model="q"
          @keydown.esc.stop="escape"
        >
      </k-dropdown-item>

      <div class="k-multiselect-options">
        <k-dropdown-item
          v-for="option in filtered"
          :key="option.value"
          :icon="isSelected(option) ? 'check' : 'circle-outline'"
          :class="{
            'k-multiselect-option': true,
            'selected': isSelected(option),
            'disabled': !addable
          }"
          @click.prevent="select(option)"
          @keydown.native.enter.prevent.stop="select(option)"
          @keydown.native.space.prevent.stop="select(option)"
        >
          <span v-html="option.display" />
          <span class="k-multiselect-value" v-html="option.info" />
        </k-dropdown-item>
      </div>
    </k-dropdown-content>

  </k-draggable>
</template>

<script>
import { required, minLength, maxLength } from "vuelidate/lib/validators";

export default {
  inheritAttrs: false,
  props: {
    disabled: Boolean,
    id: [Number, String],
    max: Number,
    min: Number,
    layout: String,
    options: {
      type: Array,
      default() {
        return [];
      }
    },
    required: Boolean,
    search: Boolean,
    separator: {
      type: String,
      default: ","
    },
    sort: Boolean,
    value: {
      type: Array,
      required: true,
      default() {
        return [];
      }
    }
  },
  data() {
    return {
      state: this.value,
      q: null,
      scrollTop: 0
    };
  },
  computed: {
    addable() {
      return !this.max || this.state.length < this.max;
    },
    draggable() {
      return this.state.length > 1 && !this.sort;
    },
    dragOptions() {
      return {
        disabled: !this.draggable,
        draggable: ".k-tag",
        delay: 1
      };
    },
    filtered() {
      if (this.q === null) {
        return this.options.map(option => ({
          ...option,
          display: option.text,
          info: option.value
        }));
      }

      const regex = new RegExp(`(${RegExp.escape(this.q)})`, "ig");

      return this.options
        .filter(option => {
          return String(option.text).match(regex) || String(option.value).match(regex);
        })
        .map(option => {
          return {
            ...option,
            display: String(option.text).replace(regex, "<b>$1</b>"),
            info: String(option.value).replace(regex, "<b>$1</b>")
          };
        });
    },
    sorted() {
      if (this.sort === false) {
        return this.state;
      }

      let items = this.state;

      const index = x => this.options.findIndex(y => y.value === x.value);
      return items.sort((a, b) => index(a) - index(b));
    }
  },
  watch: {
    value(value) {
      this.state = value;
      this.onInvalid();
    }
  },
  mounted() {
    this.onInvalid();
    this.$events.$on("click", this.close);
    this.$events.$on("keydown.cmd.s", this.close);
  },
  destroyed() {
    this.$events.$off("click", this.close);
    this.$events.$off("keydown.cmd.s", this.close);
  },
  methods: {
    add(option) {
      if (this.addable === true) {
        this.state.push(option);
        this.onInput();
      }
    },
    blur() {
      this.close();
    },
    close() {
      if (this.$refs.dropdown.isOpen === true) {
        this.$refs.dropdown.close();
      }
    },
    escape() {
      if (this.q) {
        this.q = null;
        return;
      }

      this.close();
    },
    focus() {
      this.$refs.dropdown.open();
    },
    index(option) {
      return this.state.findIndex(item => item.value === option.value);
    },
    isSelected(option) {
      return this.index(option) !== -1;
    },
    navigate(direction) {
      let current = document.activeElement;

      switch (direction) {
        case "prev":
          if (
            current &&
            current.previousSibling &&
            current.previousSibling.focus
          ) {
            current.previousSibling.focus();
          }
          break;
        case "next":
          if (
            current &&
            current.nextSibling &&
            current.nextSibling.focus
          ) {
            current.nextSibling.focus();
          }
          break;
      }
    },
    onClose() {
      if (this.$refs.dropdown.isOpen === false) {
        if (document.activeElement === this.$parent.$el) {
          this.q = null;
        }

        this.$parent.$el.focus();
      }
    },
    onInput() {
      this.$emit("input", this.sorted);
    },
    onInvalid() {
      this.$emit("invalid", this.$v.$invalid, this.$v);
    },
    onOpen() {
      this.$nextTick(() => {
        if (this.$refs.search && this.$refs.search.focus) {
          this.$refs.search.focus();
        }

        this.$refs.dropdown.$el.querySelector('.k-multiselect-options').scrollTop = this.scrollTop;
      });
    },
    remove(option) {
      this.state.splice(this.index(option), 1);
      this.onInput();
    },
    select(option) {
      this.scrollTop = this.$refs.dropdown.$el.querySelector('.k-multiselect-options').scrollTop;

      option = { text: option.text, value: option.value };

      if (this.isSelected(option)) {
        this.remove(option);
      } else {
        this.add(option);
      }
    }
  },
  validations() {
    return {
      state: {
        required: this.required ? required : true,
        minLength: this.min ? minLength(this.min) : true,
        maxLength: this.max ? maxLength(this.max) : true
      }
    };
  }
};
</script>

<style lang="scss">
.k-multiselect-input {
  display: flex;
  flex-wrap: wrap;
  position: relative;
  font-size: $font-size-small;
  min-height: 2.25rem;
  line-height: 1;
}
.k-multiselect-input .k-sortable-ghost {
  background: $color-focus;
}

.k-multiselect-input .k-dropdown-content {
  width: 100%;
}

.k-multiselect-search {
  margin-top: 0 !important;
  color: $color-white;
  background: $color-dark;
  border-bottom: 1px dashed rgba($color-white, 0.2);

  > .k-button-text {
    flex: 1;
  }

  input {
    width: 100%;
    color: $color-white;
    background: none;
    border: none;
    outline: none;
    padding: 0.25rem 0;
    font: inherit;
  }
}

.k-multiselect-options {
  position: relative;
  max-height: 240px;
  overflow-y: scroll;
  padding: 0.5rem 0;
}

.k-multiselect-option {
  position: relative;

  &.selected {
    color: $color-positive-on-dark;
  }

  &.disabled:not(.selected) .k-icon {
    opacity: 0;
  }

  b {
    color: $color-focus-on-dark;
    font-weight: 700;
  }
}

.k-multiselect-value {
  color: $color-light-grey;
  margin-left: 0.25rem;

  &::before {
    content: " (";
  }
  &::after {
    content: ")";
  }
}

.k-multiselect-input[data-layout="list"] .k-tag {
  width: 100%;
  margin-right: 0 !important;
}
</style>
