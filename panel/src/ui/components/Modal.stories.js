import Modal from "./Modal.vue";
import Padding from "../../../storybook/theme/Padding.js";
import { action } from "@storybook/addon-actions";

export default {
  title: "UI | Interaction / Modal",
  decorators: [Padding],
  component: Modal
};

export const simple = () => ({
  template: `
    <k-modal>
      <div class="inline-flex bg-black text-white items-center text-sm rounded-sm shadow-lg p-3">
        <k-icon type="👋" class="mr-3" /> Hello
      </div>
    </k-modal>
  `
});

export const loading = () => ({
  template: `
    <k-modal>
      <div
        class="inline-flex bg-black text-white items-center text-sm rounded-sm shadow-lg"
        slot-scope="{ isLoading, startLoading, stopLoading }"
      >

        <template v-if="isLoading">
          <k-button
            :icon="{
              color: 'red-light',
              type: 'alert'
            }"
            class="p-3 mr-3"
            @click="stopLoading()"
          >
            Stop loading
          </k-button>
          <k-loader class="mr-3" />
        </template>

        <template v-else>
          <k-button
            :icon="{
              color: 'green-light',
              type: 'clock'
            }"
            class="p-3"
            @click="startLoading()"
          >
            Start loading
          </k-button>
        </template>

      </div>
    </k-modal>
  `
});

export const buttons = () => ({
  methods: {
    onCancel: action("cancel"),
    onSubmit: action("submit")
  },
  template: `
    <k-modal
      @cancel="onCancel"
      @submit="onSubmit"
    >
      <div
        slot-scope="{ cancel, cancelButton, submit, submitButton }"
        class="inline-flex bg-black text-white text-sm rounded-sm items-center shadow-lg"
      >
        <p class="p-3">
          This is the modal content
        </p>
        <k-button v-bind="cancelButton" class="p-3" @click="cancel">{{ cancelButton.text }}</k-button>
        <k-button v-bind="submitButton" class="p-3" @click="submit">{{ submitButton.text }}</k-button>
      </div>
    </k-modal>
  `
});

export const withInput = () => ({
  methods: {
    save() {
      alert("Saved!");
    },
  },
  template: `
    <k-modal>
      <div
        class="flex items-center justify-between bg-black absolute rounded-sm text-white shadow-lg"
        slot-scope="{ cancelButton, submitButton }"
      >
        <k-input type="url" class="p-3 text-sm" />
        <k-button v-bind="submitButton" class="p-3" @click="save" />
      </div>
    </k-modal>
  `
});

export const notification = () => ({
  extends: withInput(),
  template: `
    <k-modal>
      <div class="relative flex" slot-scope="{ cancelButton, closeNotification, notification, error, submitButton }">

        <div class="bg-black inline-flex rounded-sm text-white shadow-lg items-center justify-between">
          <k-input type="url" class="text-sm p-3" />
          <k-button v-bind="submitButton" class="p-3" @click="error('Something went wrong')" />
        </div>

        <k-notification
          v-if="notification"
          v-bind="notification"
          class="text-sm ml-3 rounded-sm shadow-lg"
          @close="closeNotification()"
        />
      </div>
    </k-modal>
  `
});

export const withBackdrop = () => ({
  methods: {
    save() {
      alert("Saved!");
    },
  },
  template: `
    <k-modal>
      <k-backdrop class="flex items-center justify-center" slot-scope="{ cancelButton, submitButton }">
        <div class="bg-black rounded-sm text-white shadow-lg flex items-center justify-between">
          <k-input type="url" class="p-3 text-sm" />
          <k-button v-bind="submitButton" class="p-3" @click="save" />
        </div>
      </k-backdrop />
    </k-modal>
  `
});

export const asOverlay = () => ({
  methods: {
    onCancel() {
      this.$refs.overlay.close();
    },
    onSubmit() {
      alert("Saved!");
      this.$refs.overlay.close();
    },
  },
  template: `
    <div>
      <k-button icon="open" @click="$refs.overlay.open()">Open</k-button>
      <k-overlay ref="overlay">
        <k-modal
          @cancel="onCancel"
          @submit="onSubmit"
        >
          <k-backdrop
            slot-scope="{ cancel, cancelButton, submit, submitButton }"
            class="flex items-center justify-center"
            @click="cancel"
          >
            <div
              class="bg-black rounded-sm text-white shadow-lg flex items-center justify-between"
              @click.stop
            >
              <k-input
                class="p-3 text-sm"
                type="url"
                @keydown.enter="submit"
              />
              <k-button
                v-bind="submitButton"
                class="p-3"
                @click="submit"
              />
            </div>
          </k-backdrop />
        </k-modal>
      </k-overlay>
    </div>
  `
});
