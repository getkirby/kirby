<template>
  <k-overlay ref="overlay" :visible="visible">
    <k-modal
      ref="modal"
      :autofocus="autofocus"
      :cancel-button="cancelButtonConfig"
      :loading="loading"
      :submit-button="submitButtonConfig"
      @cancel="cancel"
      @submit="submit"
    >
      <k-backdrop
        :dir="$direction"
        :data-size="size"
        :data-loading="isLoading"
        slot-scope="{
          cancel,
          cancelButton,
          closeNotification,
          isLoading,
          notification,
          submit,
          submitButton
        }"
        class="k-drawer flex items-center justify-center justify-end"
        @click="cancel"
      >
        <k-loader
          v-if="isLoading"
          class="k-drawer-loader text-white p-3"
        />
        <k-button
          v-else-if="cancelButton"
          icon="cancel"
          class="k-drawer-cancel text-white p-3"
          @click.stop="cancel"
        />
        <div
          class="k-drawer-box relative flex bg-background"
          @click.stop
        >
          <k-notification
            v-if="notification"
            v-bind="notification"
            class="k-drawer-notification text-sm px-3"
            @close="closeNotification()"
          />
          <header
            v-else
            class="k-drawer-header flex flex-shrink-0 items-center justify-between pl-6"
          >
            <span class="flex flex-grow items-center">
              <h2 class="flex-shrink-0 text-sm font-normal truncate">
                {{ title }}
              </h2>

              <!-- Center slot in the drawer header -->
              <div class="flex-grow flex items-center justify-center">
                <slot name="context" />
              </div>
            </span>

            <k-button
              v-if="submitButton"
              v-bind="submitButton"
              class="k-drawer-submit-button py-3 px-6 text-black"
              @click="submit"
            />
          </header>

          <div class="k-drawer-body p-6 flex-grow">
            <slot>
              <k-text>{{ text }}</k-text>
            </slot>
          </div>
        </div>
      </k-backdrop>
    </k-modal>
  </k-overlay>
</template>

<script>
import DialogFoundation from "./DialogFoundation.vue";

export default {
  extends: DialogFoundation,
  props: {
    title: {
      type: String,
      default: "Drawer"
    }
  }
}
</script>

<style lang="scss">
.k-drawer-cancel,
.k-drawer-loader {
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
  .k-drawer[data-size="small"] .k-drawer-box {
    width: 40%;
    max-width: 40rem;
  }
  .k-drawer[data-size="large"] .k-drawer-box {
    width: 80%;
    max-width: 60rem;
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

.k-drawer-notification.k-notification p {
  line-height: 1.25;
}

.k-drawer-header {
  height: 2.5rem;
  border-bottom: 1px solid lighten($color-border, 8%);
}

.k-drawer-body {
  overflow: auto;
}


</style>
