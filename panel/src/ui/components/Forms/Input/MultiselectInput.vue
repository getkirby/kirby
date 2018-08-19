<template>
  <k-draggable
    v-model="state"
    element="k-dropdown"
    :options="{disabled: !draggable, forceFallback: true, draggable: '.k-tag', delay: 1}"
    :data-layout="layout"
    class="k-multiselect-input"
    @input="onInput"
    @click.native="$refs.dropdown.toggle"
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
      @open="$nextTick(() => { $refs.search.focus() })"
      @close="q = null"
    >
      <k-dropdown-item
        v-if="search"
        icon="search"
        class="k-multiselect-search"
      >
        <input ref="search" v-model="q" />
      </k-dropdown-item>

      <div class="k-multiselect-options">
        <k-dropdown-item
          v-for="option in filtered"
          :key="option.value"
          :icon="isSelected(option) ? 'check' : 'circle-o'"
          :class="{
            'k-multiselect-option': true,
            'selected': isSelected(option),
            'disabled': !addable
          }"
          @click="select(option)"
          @keydown.native.enter.prevent="select(option)"
          @keydown.native.space.prevent="select(option)"
        >
          <span v-html="option.display" />
          <span class="value" v-html="option.info" />
        </k-dropdown-item>
      </div>
    </k-dropdown-content>

  </k-draggable>
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
      default() {
        return [];
      }
    }
  },
  data() {
    return {
      state:  this.value,
      q:      null
    };
  },
  computed: {
    addable() {
      return !this.max || this.state.length < this.max;
    },
    draggable() {
      return this.state.length > 1 && !this.sort;
    },
    filtered() {

      if (this.q === null ) {
        return this.options.map(option => ({
          ...option,
          display: option.text,
          info: option.value
        }));
      }

      const regex = new RegExp(`(${this.q})`, "ig");

      return this.options.filter(option => {
        return option.text.match(regex) || option.value.match(regex)
      }).map(option => {
        return {
          ...option,
          display: option.text.replace(regex, "<b>$1</b>"),
          info:    option.value.replace(regex, "<b>$1</b>")
        }
      });

    },
    sorted() {
      if (this.sort === false) {
        return this.state;
      }

      const index = (x) => this.options.findIndex(y => y.value === x.value)
      return this.state.sort((a, b) => index(a) - index(b));
    }
  },
  watch: {
    value(value) {
      this.state = this.value;
      this.onInvalid();
    }
  },
  mounted() {
    this.onInvalid();
    this.$events.$on('click', this.close);
    this.$events.$on('keydown.cmd.s', this.close);
    this.$events.$on('keydown.esc', this.escape);
  },
  destroyed() {
    this.$events.$off('click', this.close);
    this.$events.$off('keydown.cmd.s', this.close);
    this.$events.$off('keydown.esc', this.escape);
  },
  methods: {
    add(option) {
      if (this.addable) {
        this.state.push(option);
        this.onInput(this.sorted);
      }
    },
    blur() {
      this.close();
    },
    close() {
      this.$refs.dropdown.close();
      this.q = null;
      this.$el.focus();
    },
    escape(e) {
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
          if (current && current.previousSibling) {
            current.previousSibling.focus();
          }
          break;
        case "next":
          if (current && current.nextSibling) {
            current.nextSibling.focus();
          }
          break;
      }
    },
    onInput(value) {
      this.$emit("input", value);
    },
    onInvalid() {
      this.$emit("invalid", this.$v.$invalid, this.$v);
    },
    remove(option) {
      this.state.splice(this.index(option), 1);
      this.onInput(this.sorted);
    },
    select(option) {
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
}

</script>

<style lang="scss">

.k-multiselect-input {
  display:       flex;
  flex-wrap:     wrap;
  position:      relative;
  font-size:     0.875rem;
  min-height:    1.75rem;
  line-height:   1;
}
.k-multiselect-input .sortable-ghost {
  background: $color-focus;
}

.k-multiselect-input .k-dropdown-content {
  width: 100%;
}

.k-multiselect-search {
  margin-top: 0 !important;
  color:      $color-black;
  background: $color-dark-grey;

   > .k-button-text {
    flex: 1;
  }

  input {
    width:      100%;
    background: none;
    border:     none;
    outline:    none;
    font-size:  1em;
  }
}


.k-multiselect-options {
  position: relative;
  max-height: 240px;
  overflow-y: scroll;
}

.k-multiselect-option {
  &.selected {
    color: $color-positive-on-dark;
  }

  &.disabled:not(.selected) .k-icon {
    opacity: 0;
  }

  .value {
    font-size: .8em;
    opacity:   .5;

    &::before { content: " (" }
    &::after  { content: ")"  }
  }

   b {
    color: $color-focus-on-dark;
    font-weight: 700;
  }
}

.k-multiselect-input[data-layout="list"] .k-tag {
  width: 100%;
  margin-right: 0 !important;
}
</style>
