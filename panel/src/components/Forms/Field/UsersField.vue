<template>
  <k-field v-bind="$props" class="k-users-field">
    <k-button
      v-if="more && !disabled"
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
        :handle="true"
        @end="onInput"
      >
        <component
          v-for="(user, index) in selected"
          :is="elements.item"
          :key="user.email"
          :sortable="!disabled && selected.length > 1"
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
          <k-button
            v-if="!disabled"
            slot="options"
            icon="remove"
            @click="remove(index)"
          />
        </component>
      </k-draggable>
    </template>
    <k-empty
      v-else
      icon="users"
      @click="open"
    >
      {{ empty || $t('field.users.empty') }}
    </k-empty>
    <k-users-dialog ref="selector" @submit="select" />
  </k-field>
</template>

<script>
import picker from "@/mixins/picker/field.js";

export default {
  mixins: [picker],
  methods: {
    open() {
      if (this.disabled) {
        return false;
      }

      this.$refs.selector.open({
        endpoint: "users",
        max: this.max,
        multiple: this.multiple,
        selected: this.selected.map(user => user.email)
      });
    }
  }
};
</script>

<style lang="scss">
.k-users-field[data-disabled] * {
  pointer-events: all !important;
}
</style>
