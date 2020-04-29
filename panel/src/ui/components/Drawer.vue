<template>
  <portal v-if="isOpen">
    <div
      :dir="$direction"
      :data-flow="flow"
      class="k-drawer fixed flex items-center justify-end"
      @click="cancel"
    >
      <div
        ref="box"
        class="k-drawer-box relative flex"
        @click.stop
      >
        <header class="k-drawer-header flex items-center justify-between px-6">
          <h2 class="text-sm font-normal truncate">
            {{ title }}
          </h2>

          <!-- Center slot in the drawer header -->
          <slot name="context" />

          <!-- Slot to replace the option buttons on the right of the header -->
          <slot name="options">
            <k-button-group>
              <k-button
                v-if="cancelButton"
                icon="cancel"
                @click="cancel()"
              >{{ cancelButtonLabel }}</k-button>
              <k-button
                v-if="submitButton"
                :icon="icon"
                :theme="theme"
                @click="submit()"
              >{{ submitButtonLabel }}</k-button>
            </k-button-group>
          </slot>
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
    flow: {
      type: String,
      default: "horizontal"
    },
    submitButton: {
      type: [Boolean, String],
      default: false
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
.k-drawer[data-flow="vertical"] {
  flex-direction: column;
}

.k-drawer-box {
  flex-direction: column;
  width: 100%;
  background: $color-background;
}
[data-flow="vertical"]  .k-drawer-box {
  height: 66.66%;
}
[data-flow="horizontal"] .k-drawer-box {
  height: 100%;
}

@media screen and (min-width: $breakpoint-md) {
  [data-flow="horizontal"] .k-drawer-box {
    width: 66.66%;
  }
}
@media screen and (min-width: $breakpoint-lg) {
  [data-flow="horizontal"] .k-drawer-box {
    width: 50%;
    max-width: 60rem;
  }
}

.k-drawer-box::before {
  content: "";
  position: absolute;
  pointer-events: none;
  background: -webkit-linear-gradient(var(--start), rgba($color-black, 0), rgba($color-black, .075));
}
[data-flow="horizontal"] .k-drawer-box::before {
  --start: left;
  top: 0;
  bottom: 0;
  left: -4.5rem;
  width: 4.5rem;
}
[data-flow="vertical"] .k-drawer-box::before {
  --start: top;
  left: 0;
  right: 0;
  top: -4.5rem;
  height: 4.5rem;
}

.k-drawer-header {
  height: 2.5rem;
  flex-shrink: 0;
  line-height: 1;
  border-bottom: 1px solid $color-gray-300;
}
.k-drawer-body {
  flex-grow: 1;
  overflow: auto;
}
</style>
