<template>
  <k-field :input="_uid" v-bind="$props" class="k-user-field">

    <k-autocomplete
      v-if="!user"
      ref="autocomplete"
      :options="users"
      @close="reset"
      @select="setUser($event)"
    >
      <k-input
        ref="input"
        :id="_uid"
        :disabled="disabled"
        v-model="typed"
        type="text"
        theme="field"
        placeholder="Type to search for users …"
        icon="user"
        @keydown.enter.prevent
        @input="$refs.autocomplete.search($event)"
      />
    </k-autocomplete>

    <k-list-item
      v-else
      :image="{url: user.image, cover: true}"
      :link="user.link"
      :text="user.name"
      :icon="{type: 'user', back: 'black'}"
      element="div"
    >
      <k-button slot="options" icon="cancel" @click="removeUser" />
    </k-list-item>
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
    users: Array,
    icon: {
      type: String,
      default: "user"
    },
    value: Object
  },
  data() {
    return {
      typed: null,
      user: this.value
    }
  },
  watch: {
    value() {
      this.user = this.value;
    }
  },
  methods: {
    focus() {
      if (this.$refs.input) {
        this.$refs.input.focus();
      }
    },
    removeUser() {
      this.user = null;
      this.$emit("input", null);
    },
    reset() {
      this.typed = "";
      this.$refs.input.focus();
    },
    setUser(user) {
      this.user = user;
      this.$emit("input", user);
    }
  }
}
</script>

<style lang="scss">
.k-user-field .k-button-group {
  margin-right: 0;
}
.k-user-field .k-list-item {
  margin-bottom: 0;
}
</style>
