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
          :placeholder="search.min ? $t('search.min', { min: search.min }) : $t('search') + ' …'"
          @keydown.esc.stop="escape"
        >
      </k-dropdown-item>

      <div class="k-multiselect-options">
        <k-dropdown-item
          v-for="option in visible"
          :key="option.value"
          :icon="isSelected(option) ? 'check' : 'circle-outline'"
          :class="{
            'k-multiselect-option': true,
            'selected': isSelected(option),
            'disabled': !more
          }"
          @click.prevent="select(option)"
          @keydown.native.enter.prevent.stop="select(option)"
          @keydown.native.space.prevent.stop="select(option)"
        >
          <!-- eslint-disable-next-line vue/no-v-html -->
          <span v-html="option.display" />
          <!-- eslint-disable-next-line vue/no-v-html -->
          <span class="k-multiselect-value" v-html="option.info" />
        </k-dropdown-item>

        <k-dropdown-item
          v-if="filtered.length === 0"
          :disabled="true"
          class="k-multiselect-option"
        >
          {{ emptyLabel }}
        </k-dropdown-item>
      </div>

      <k-button
        v-if="visible.length < filtered.length"
        class="k-multiselect-more"
        @click.stop="limit = false"
      >
        {{ $t("search.all") }} ({{ filtered.length }})
      </k-button>
    </k-dropdown-content>
  </k-draggable>
</template>

<script>
import { required, minLength, maxLength } from "vuelidate/lib/validators";

export default {
  inheritAttrs: false,
  props: {
    id: [Number, String],
    disabled: Boolean,
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
    search: [Object, Boolean],
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
      limit: true,
      scrollTop: 0
    };
  },
  computed: {
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
    emptyLabel() {
      if (this.q) {
        return this.$t("search.results.none");
      }

      return this.$t("options.none");
    },
    filtered() {
      if (this.q && this.q.length >= (this.search.min || 0)) {
        return this.options
          .filter(option => this.isFiltered(option))
          .map(option => ({
            ...option,
            display: this.toHighlightedString(option.text),
            info: this.toHighlightedString(option.value)
          }));
      }

      return this.options.map(option => ({
        ...option,
        display: option.text,
        info: option.value
      }));
    },
    more() {
      return !this.max || this.state.length < this.max;
    },
    regex() {
      return new RegExp(`(${RegExp.escape(this.q)})`, "ig");
    },
    sorted() {
      if (this.sort === false) {
        return this.state;
      }

      let items = this.state;

      const index = x => this.options.findIndex(y => y.value === x.value);
      return items.sort((a, b) => index(a) - index(b));
    },
    visible() {
      if (this.limit) {
        return this.filtered.slice(0, this.search.display || this.filtered.length);
      }

      return this.filtered;
    },
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
      if (this.more === true) {
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
        this.limit = true;
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
    isFiltered(option) {
      return String(option.text).match(this.regex) ||
             String(option.value).match(this.regex);
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
    },
    toHighlightedString(string) {
      // make sure that no HTML exists before in the string
      // to avoid XSS when displaying via `v-html`
      string = this.$helper.string.stripHTML(string);
      return string.replace(this.regex, "<b>$1</b>")
    },
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

<style>
.k-multiselect-input {
  display: flex;
  flex-wrap: wrap;
  position: relative;
  font-size: var(--text-sm);
  min-height: 2.25rem;
  line-height: 1;
}
.k-multiselect-input .k-sortable-ghost {
  background: var(--color-focus);
}

.k-multiselect-input .k-dropdown-content {
  width: 100%;
}

.k-multiselect-search {
  margin-top: 0 !important;
  color: var(--color-white);
  background: var(--color-gray-900);
  border-bottom: 1px dashed rgba(255, 255, 255, .2);
}
.k-multiselect-search > .k-button-text {
  flex: 1;
  opacity: 1 !important;
}

.k-multiselect-search input {
  width: 100%;
  color: var(--color-white);
  background: none;
  border: none;
  outline: none;
  padding: .25rem 0;
  font: inherit;
}

.k-multiselect-options {
  position: relative;
  max-height: 275px;
  overflow-y: auto;
  padding: .5rem 0;
}

.k-multiselect-option {
  position: relative;
}
.k-multiselect-option.selected {
  color: var(--color-positive-light);
}

.k-multiselect-option.disabled:not(.selected) .k-icon {
  opacity: 0;
}
.k-multiselect-option b {
  color: var(--color-focus-light);
  font-weight: 700;
}

.k-multiselect-value {
  color: var(--color-gray-500);
  margin-left: .25rem;
}
.k-multiselect-value::before {
  content: " (";
}
.k-multiselect-value::after {
  content: ")";
}

.k-multiselect-input[data-layout="list"] .k-tag {
  width: 100%;
  margin-right: 0 !important;
}

.k-multiselect-more {
  width: 100%;
  padding: .75rem;
  color: rgba(255, 255, 255, .8);
  text-align: center;
  border-top: 1px dashed rgba(255, 255, 255, .2);
}
.k-multiselect-more:hover {
  color: var(--color-white);
}
</style>
