<template>
  <k-field v-bind="$props" class="k-users-field">
    <k-button-group slot="options" class="k-field-options">
      <k-button
        v-if="more && !disabled"
        :icon="btnIcon"
        class="k-field-options-button"
        @click="open"
      >
        {{ btnLabel }}
      </k-button>
    </k-button-group>

    <template v-if="selected.length">
      <k-draggable
        :element="elements.list"
        :list="selected"
        :handle="true"
        :data-invalid="isInvalid"
        @end="onInput"
      >
        <component
          :is="elements.item"
          v-for="(user, index) in selected"
          :key="user.email"
          :sortable="!disabled && selected.length > 1"
          :text="user.text"
          :info="user.info"
          :link="link ? $api.users.link(user.id) : null"
          :image="user.image"
          :icon="user.icon"
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
      :data-invalid="isInvalid" 
      icon="users" 
      @click="open"
    >
      {{ empty || $t("field.users.empty") }}
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
        endpoint: this.endpoints.field,
        max: this.max,
        multiple: this.multiple,
        search: this.search,
        selected: this.selected.map(user => user.id)
      });
    }
  }
};
</script>

<style>
.k-users-field[data-disabled] * {
  pointer-events: all !important;
}
</style>
