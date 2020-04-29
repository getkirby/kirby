import Drawer from "./Drawer.vue";
import { action } from "@storybook/addon-actions";

export default {
  title: "UI | Dialog / Drawer",
  component: Drawer,
};

export const fromSide = () => ({
  methods: {
    close: action('close'),
    open: action('open'),
  },
  computed: {
    flow() {
      return "horizontal";
    }
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
        <k-drawer ref="drawer" :flow="flow">
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

export const fromBotton = () => ({
  extends: fromSide(),
  computed: {
    flow() {
      return "vertical";
    }
  }
});

export const withOptions = () => ({
  template: `
    <div>
      <k-view class="py-6">
        <k-button
          icon="open"
          @click="$refs.drawer.open()"
        >
          Open drawer
        </k-button>
        <k-drawer ref="drawer" :flow="flow">
          Content

          <k-button-group slot="options">
            <k-button color="blue">Just</k-button>
            <k-button color="red">three</k-button>
            <k-button color="yellow">buttons</k-button>
          </k-button-group>
        </k-drawer>
      </k-view>
    </div>
  `,
});

export const withContext = () => ({
  template: `
    <div>
      <k-view class="py-6">
        <k-button
          icon="open"
          @click="$refs.drawer.open()"
        >
          Open drawer
        </k-button>
        <k-drawer ref="drawer" :flow="flow">
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
      </k-view>
    </div>
  `,
});
