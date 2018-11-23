<template>
  <k-field v-bind="$props" class="k-users-field">
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
        :options="dragOptions"
        @start="onStart"
        @end="onInput"
      >
        <component
          v-for="(user, index) in selected"
          :is="elements.item"
          :key="user.email"
          :sortable="true"
          :text="user.username"
          :link="$api.users.link(user.id)"
          :image="
            user.avatar ?
              {
                url: user.avatar.url,
                back: 'pattern',
                cover: true
              }
              :
              null
          "
          :icon="{
            type: 'user',
            back: 'black'
          }"
        >
          <k-button slot="options" icon="remove" @click="remove(index)" />
        </component>
      </k-draggable>
    </template>
    <k-empty v-else icon="users" @click="open">
      {{ $t('field.users.empty') }}
    </k-empty>
    <k-users-dialog ref="selector" @submit="select" />
  </k-field>
</template>

<script>
import Field from "../Field.vue";

export default {
  inheritAttrs: false,
  props: {
    ...Field.props,
    max: Number,
    multiple: Boolean,
    value: {
      type: Array,
      default() {
        return [];
      }
    }
  },
  data() {
    return {
      layout: "list",
      selected: this.value,
    };
  },
  computed: {
    dragOptions() {
      return {
        forceFallback: true,
        fallbackClass: "sortable-fallback",
        handle: ".k-sort-handle",
      };
    },
    elements() {
      return {
        list: "k-list",
        item: "k-list-item"
      };
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
  methods: {
    open() {
      this.$refs.selector.open({
        max: this.max,
        multiple: this.multiple,
        selected: this.selected.map(user => user.email),
      });
    },
    remove(index) {
      this.selected.splice(index, 1);
      this.onInput();
    },
    focus() {

    },
    onStart() {
      this.$store.dispatch("drag", {});
    },
    onInput() {
      this.$store.dispatch("drag", null);
      this.$emit("input", this.selected);
    },
    select(files) {
      this.selected = files;
      this.onInput();
    },
  }
}
</script>
