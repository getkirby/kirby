<template>
  <k-field :input="_uid" v-bind="$props" class="k-interval-field">
    <k-input
      ref="input"
      :id="_uid"
      v-bind="$props"
      theme="field"
    >
      <div
        class="k-text-input k-interval-display"             @click="$refs.dropdown.toggle()"
      >
        <template v-if="start && end">
          {{ displayStart }}
          <span class="divider">–</span>
          {{ displayEnd }}
        </template>
        <template v-else>
          &nbsp;
        </template>
      </div>

      <template slot="icon">
        <k-dropdown>
          <k-button
            v-if="start && end"
            icon="cancel"
            :tooltip="$t('date.select')"
            class="k-input-icon-button"
            tabindex="-1"
            @click="onReset"
          />
          <k-button
            v-else
            :icon="icon"
            :tooltip="$t('date.select')"
            class="k-input-icon-button"
            tabindex="-1"
            @click="$refs.dropdown.toggle()"
          />
          <k-dropdown-content ref="dropdown" align="right">
            <k-interval-input
              :from="start"
              :to="end"
              :disabled="disabled"
              @input="
                onInput($event);
                $refs.dropdown.close();
              "
            />
          </k-dropdown-content>
        </k-dropdown>
      </template>
    </k-input>
  </k-field>
</template>

<script>
import Field from "../Field.vue";
import Input from "../Input.vue";

export default {
  inheritAttrs: false,
  props: {
    ...Field.props,
    ...Input.props,
    icon: {
      type: String,
      default: "calendar"
    },
    display: String
  },
  data() {
    return {
      ...this.toDates(this.value),
      listeners: {
        ...this.$listeners,
        input: this.onInput
      }
    };
  },
  computed: {
    displayStart() {
      return this.start.format(this.display);
    },
    displayEnd() {
      return this.end.format(this.display);
    }
  },
  watch: {
    value(value) {
      let values = this.toDates(value);

      this.start = values.start;
      this.end   = values.end;
    }
  },
  methods: {
    toDates(value) {
      if (value == null) {
        return {
          start: null,
          end: null
        };
      }

      return {
        start: this.$library.dayjs(value[0]).startOf("day"),
        end:   this.$library.dayjs(value[1]).startOf("day")
      };
    },
    focus() {
      this.$refs.input.focus();
    },
    onReset() {
      this.start = null;
      this.end = null;
      this.$emit("input", null);
    },
    onInput(dates) {
      this.start = dates.from;
      this.end = dates.to;
      this.$emit("input", [this.start, this.end]);
    }
  }
};
</script>

<style lang="scss">
.k-interval-display {
  cursor: pointer;
  user-select: none;

  .divider {
    color: $color-light-grey;
    padding: 0 .5rem;
  }
}
</style>
