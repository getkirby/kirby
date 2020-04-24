<template>
  <portal v-if="isOpen">
    <div
      :dir="$direction"
      :data-flow="flow"
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
    flow: {
      type: String,
      default: "horizontal"
    }
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
.k-drawer[data-flow="vertical"] {
  flex-direction: column;
}

.k-drawer-box {
  position: relative;
  display: flex;
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
}
[data-flow="horizontal"] .k-drawer-box::before {
  top: 0;
  bottom: 0;
  left: -4.5rem;
  width: 4.5rem;
  background: -webkit-linear-gradient(left, rgba(#000, 0), rgba(#000, .075));
}
[data-flow="vertical"] .k-drawer-box::before {
  left: 0;
  right: 0;
  top: -4.5rem;
  height: 4.5rem;
  background: -webkit-linear-gradient(top, rgba(#000, 0), rgba(#000, .075));
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
