<template>
  <k-field v-bind="$props" class="k-users-field">
    <template #options>
      <k-button-group class="k-field-options">
        <k-button
          v-if="more && !disabled"
          :icon="btnIcon"
          class="k-field-options-button"
          @click="open"
        >
          {{ btnLabel }}
        </k-button>
      </k-button-group>
    </template>

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
          <template #options>
            <k-button
              v-if="!disabled"
              icon="remove"
              @click="remove(index)"
            />
          </template>
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
import picker from "@/mixins/forms/picker.js";

export default {
  mixins: [picker]
};
</script>

<style>
.k-users-field[data-disabled] * {
  pointer-events: all !important;
}
</style>
