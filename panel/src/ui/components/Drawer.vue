<template>
  <portal v-if="isOpen">
    <div
      :dir="$direction"
      class="k-drawer"
      @click="cancel"
    >
      <div
        ref="box"
        class="k-drawer-box"
        @click.stop
      >
        <header class="k-drawer-header">
          <h2 class="k-drawer-title">
            {{ title }}
          </h2>

          <k-button
            icon="cancel"
            @click="close()"
          />
        </header>
        <div class="k-drawer-body">
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
  },
}
</script>

<style lang="scss">
.k-drawer {
  position: fixed;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 100%;
  border: 0;
  display: flex;
  align-items: center;
  justify-content: flex-end;
  z-index: z-index(drawer);
  transform: translate3d(0, 0, 0);
  background: rgba($color-black, .05);
}
.k-drawer-box {
  position: relative;
  display: flex;
  flex-direction: column;
  width: 100%;
  height: 100%;
  background: $color-background;
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
  pointer-events: none;
  background: -webkit-linear-gradient(left, rgba(#000, 0), rgba(#000, .075));
}
.k-drawer-header {
  height: 2.5rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-shrink: 0;
  line-height: 1;
  border-bottom: 1px solid rgba(#000, .05);
}
.k-drawer-header .k-button {
  width: 2.5rem;
  height: 2.5rem;
}
.k-drawer-title {
  font-size: $text-sm;
  font-weight: 400;
  padding: 0 1.5rem;
}
.k-drawer-body {
  flex-grow: 1;
  padding: 1.5rem;
  overflow: auto;
}
</style>
