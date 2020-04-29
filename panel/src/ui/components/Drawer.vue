<template>
  <portal v-if="isOpen">
    <div
      :dir="$direction"
      class="k-drawer fixed flex items-center justify-end"
      @click="cancel"
    >

      <k-button
        v-if="cancelButton"
        icon="cancel"
        @click.stop="cancel()"
        class="k-drawer-cancel bg-black text-white p-3"
      />

      <div
        ref="box"
        class="k-drawer-box relative flex bg-background"
        @click.stop
      >
        <header class="k-drawer-header flex items-center justify-between px-6">
          <h2 class="text-sm font-normal truncate">
            {{ title }}
          </h2>

          <!-- Center slot in the drawer header -->
          <slot name="context" />

          <k-button
            v-if="submitButton"
            :icon="icon"
            :theme="theme"
            @click="submit()"
          >
            {{ submitButtonLabel }}
          </k-button>
        </header>
        <div class="k-drawer-body p-6">
          <slot />
        </div>
      </div>
    </div>
  </portal>
</template>

<script>
import Dialog from "./Dialog.vue";

export default {
  extends: Dialog,
  props: {
    title: {
      type: String,
      default: "Drawer"
    },
    submitButton: {
      type: [Boolean, String],
      default: false
    }
  },
  computed: {
    submitButtonLabel() {
      if (this.submitButton === false) {
        return false;
      }

      if (this.submitButton === true) {
        return this.$t("confirm");
      }

      return this.submitButton;
    }
  }
}
</script>

<style lang="scss">
.k-drawer {
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 100%;
  border: 0;
  z-index: z-index(drawer);
  transform: translate3d(0, 0, 0);
  background: rgba($color-black, 0.05);
}

.k-drawer-cancel {
  align-self: flex-start;
}

.k-drawer-box {
  flex-direction: column;
  width: 100%;
  height: 100%;
}
@media screen and (min-width: $breakpoint-md) {
  .k-drawer-box {
    width: 66.66%;
  }
}
@media screen and (min-width: $breakpoint-lg) {
  .k-drawer-box {
    width: 50%;
    max-width: 60rem;
  }
}
.k-drawer-box::before {
  content: "";
  position: absolute;
  top: 0;
  bottom: 0;
  left: -4.5rem;
  width: 4.5rem;
  background: -webkit-linear-gradient(left, rgba($color-black, 0), rgba($color-black, .075));
  pointer-events: none;
}

.k-drawer-header {
  height: 2.5rem;
  flex-shrink: 0;
  line-height: 1;
  border-bottom: 1px solid $color-gray-400;
}
.k-drawer-body {
  flex-grow: 1;
  overflow: auto;
}
</style>
