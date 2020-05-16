import Drawer from "./Drawer.vue";
import { action } from "@storybook/addon-actions";
import Padding from "../../../storybook/theme/Padding.js";


export default {
  title: "UI | Dialog / Drawer",
  component: Drawer,
  decorators: [Padding]
};

export const regular = () => ({
  methods: {
    close: action('close'),
    open: action('open'),
  },
  computed: {
    loading() {
      return false;
    }
  },
  template: `
    <div>
      <k-text>
        <p>
          This is the main view below the drawer.
          The drawer is perfect to handle big interactions
          that are too extensive for dialogs and also might have their own dialogs.
        </p>
        <k-button
          icon="open"
          @click="$refs.drawer.open()"
        >
          Open drawer
        </k-button>
      </k-text>
      <k-drawer ref="drawer" :loading="loading">
        <k-form
          :fields="{
            firstName: {
              label: 'First name',
              type: 'text',
              width: '1/2'
            },
            lastName: {
              label: 'Last name',
              type: 'text',
              width: '1/2'
            },
            email: {
              label: 'Email',
              type: 'email'
            },
            text: {
              label: 'Text',
              type: 'textarea',
              size: 'large'
            }
          }"
        />
      </k-drawer>
    </div>
  `,
});

export const loading = () => ({
  extends: regular(),
  computed: {
    loading() {
      return true;
    }
  }
});

export const withContext = () => ({
  template: `
    <div>
      <k-button
        icon="open"
        @click="$refs.drawer.open()"
      >
        Open drawer
      </k-button>
      <k-drawer ref="drawer">
        Content

        <k-pagination
          :details="true"
          :page="1"
          :limit="1"
          :total="20"
          :dropdown="false"
          slot="context"
        />
      </k-drawer>
    </div>
  `,
});

export const successNotification = () => ({
  methods: {
    onSubmit() {
      this.$refs.drawer.success("Awesome stuff!");
    },
  },
  template: `
    <div>
      <k-button
        icon="open"
        @click="$refs.drawer.open()"
      >
        Open drawer
      </k-button>
      <k-drawer
        ref="drawer"
        @submit="onSubmit"
      >
        Click "OK" to trigger the notification
      </k-drawer>
    </div>
  `,
});

export const errorNotification = () => ({
  extends: successNotification(),
  methods: {
    onSubmit() {
      this.$refs.drawer.error("Oh oh! Something's seriously wrong here.");
    },
  },
});
