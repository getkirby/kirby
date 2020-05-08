import Modal from "./Modal.vue";
import Padding from "../storybook/Padding.js";
import { action } from "@storybook/addon-actions";

export default {
  title: "UI | Interaction / Modal",
  decorators: [Padding],
  component: Modal
};

export const simple = () => ({
  template: `
    <k-backdrop class="flex items-center justify-center">
      <k-modal class="bg-light rounded-sm shadow-lg py-3 px-6">
        Hello
      </k-modal>
    </k-backdrop>
  `
});

export const buttons = () => ({
  methods: {
    onCancel: action("cancel"),
    onSubmit: action("submit")
  },
  template: `
    <k-backdrop class="flex items-center justify-center">
      <k-modal
        class="bg-light rounded-sm shadow-lg"
        @cancel="onCancel"
        @submit="onSubmit"
      >
        <template slot-scope="{ cancel, cancelButton, submit, submitButton }">
          <div class="p-6">
            This is the modal content
          </div>
          <hr>
          <footer class="flex justify-between">
            <k-button v-bind="cancelButton" class="py-3 px-6 flex-grow" @click="cancel">{{ cancelButton.text }}</k-button>
            <k-button v-bind="submitButton" class="py-3 px-6 flex-grow" @click="submit">{{ submitButton.text }}</k-button>
          </footer>
        </template>
      </k-modal>
    </k-backdrop>
  `
});

export const focustrap = () => ({
  methods: {
    onCancel: action("cancel"),
    onSubmit: action("submit")
  },
  template: `
    <div>
      <k-fieldset :fields="{
        text: {
          label: 'Text',
          type: 'text',
          autofocus: true
        },
        email: {
          label: 'Email',
          type: 'email'
        }
      }" />
      <k-backdrop class="flex items-center justify-center">
        <k-modal
          class="bg-light rounded-sm shadow-lg"
          @cancel="onCancel"
          @submit="onSubmit"
        >
          <template slot-scope="{ cancel, cancelButton, submit, submitButton }">
            <div class="p-6">

              <k-fieldset :fields="{
                text: {
                  label: 'Text',
                  type: 'text',
                  autofocus: true
                },
                email: {
                  label: 'Email',
                  type: 'email'
                }
              }" />

            </div>
            <hr>
            <footer class="flex justify-between">
              <k-button v-bind="cancelButton" class="py-3 px-6" @click="cancel">{{ cancelButton.text }}</k-button>
              <k-button v-bind="submitButton" class="py-3 px-6" @click="submit">{{ submitButton.text }}</k-button>
            </footer>
          </template>
        </k-modal>
      </k-backdrop>
    </div>
  `
});

export const inline = () => ({
  data() {
    return {
      edit: false
    }
  },
  methods: {
    save() {
      this.close();
      alert("Saved!");
    },
    open() {
      this.edit = true;
      this.$events.$once("click", this.close);
      this.$events.$once("keydown.esc", this.close);
    },
    close() {
      this.edit = false;
    }
  },
  template: `
    <div class="relative">
      <k-button icon="edit" class="py-3" @click.stop="open()">Click to edit …</k-button>
      <k-modal
        v-if="edit"
        class="bg-black absolute rounded text-white shadow-lg"
        @submit="onSubmit"
      >
        <div class="flex items-center" slot-scope="{ cancelButton, submitButton }">
          <k-input type="url" class="p-2" />
          <k-button v-bind="submitButton" class="p-2" @click="save" />
        </div>
      </k-modal>
    </div>
  `
});

export const notification = () => ({
  template: `
    <k-modal class="bg-black absolute rounded text-white shadow-lg">
      <template slot-scope="{ notification, closeNotification, error }">
        <k-notification
          v-if="notification"
          v-bind="notification"
          class="text-sm"
          @close="closeNotification()"
        />
        <p class="p-6">
          <k-button icon="alert" @click="error('Something went wrong')">Trigger notification</k-button>
        </p>
      </template>
    </k-modal>
  `
});

