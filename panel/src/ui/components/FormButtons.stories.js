import FormButtons from "./FormButtons.vue";
import { action } from "@storybook/addon-actions";

export default {
  title: "UI | Form / Foundation / Form Buttons",
  component: FormButtons
};

export const regular = () => ({
  methods: {
    onRevert: action("revert"),
    onSave: action("save")
  },
  template: `
    <k-form-buttons
      @revert="onRevert"
      @save="onSave"
    />
  `,
});

export const saving = () => ({
  extends: regular(),
  template: `
    <k-form-buttons
      :saving="true"
      @revert="onRevert"
      @save="onSave"
    />
  `,
});

export const locked = () => ({
  methods: {
    onUnlock: action("unlock"),
  },
  template: `
    <k-form-buttons
      :lock="{
        email: 'example@getkirby.com',
        unlockable: true
      }"
      mode="lock"
      @unlock="onUnlock"
    />
  `,
});

export const blocked = () => ({
  methods: {
    onUnlock: action("unlock"),
  },
  template: `
    <k-form-buttons
      :lock="{
        email: 'example@getkirby.com',
        unlockable: false
      }"
      mode="lock"
      @unlock="onUnlock"
    />
  `,
});

export const unlocked = () => ({
  methods: {
    onDownload: action("download"),
    onResolve: action("resolve"),
  },
  template: `
    <k-form-buttons
      mode="unlock"
      @download="onDownload"
      @resolve="onResolve"
    />
  `,
});

