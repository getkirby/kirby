import Drawer from "./Drawer.vue";
import { action } from "@storybook/addon-actions";

export default {
  title: "Dialog / Drawer",
  component: Drawer,
};

export const regular = () => ({
  methods: {
    close: action('close'),
    open: action('open'),
  },
  template: `
    <div>
      <k-topbar />
      <k-view class="py-6">
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
        <k-drawer ref="drawer">
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
      </k-view>
    </div>
  `,
});

