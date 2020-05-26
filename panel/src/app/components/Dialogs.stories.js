import { action } from "@storybook/addon-actions";
import Padding from "../../../storybook/theme/Padding.js";

import PageCreateDialog from "./PageCreateDialog.vue";
import PageDuplicateDialog from "./PageDuplicateDialog.vue";
import PageRemoveDialog from "./PageRemoveDialog.vue";
import PageRenameDialog from "./PageRenameDialog.vue";
import PageSlugDialog from "./PageSlugDialog.vue";
import PageStatusDialog from "./PageStatusDialog.vue";
import PageTemplateDialog from "./PageTemplateDialog.vue";
import SiteRenameDialog from "./SiteRenameDialog.vue";

export default {
  title: "App | Dialogs",
  decorators: [Padding],
};

const DialogStory = () => ({
  components: {
    "k-story-dialog": UserRoleDialog,
  },
  methods: {
    onSubmit: action("submit"),
    open() {
      this.$refs.dialog.open();
    },
  },
  template: `
    <div>
      <k-button @click="open">Open</k-button>
      <k-story-dialog ref="dialog" @submit="onSubmit" />
    </div>
  `
});

export const PageCreate = () => ({
  extends: DialogStory(),
  components: {
    "k-story-dialog": PageCreateDialog
  },
  methods: {
    open() {
      this.$refs.dialog.open("photography", "some-section");
    }
  }
});

export const PageDuplicate = () => ({
  extends: DialogStory(),
  components: {
    "k-story-dialog": PageDuplicateDialog
  },
  methods: {
    open() {
      this.$refs.dialog.open("photography");
    }
  }
});

export const PageRemove = () => ({
  extends: DialogStory(),
  components: {
    "k-story-dialog": PageRemoveDialog
  },
  methods: {
    open() {
      this.$refs.dialog.open("photography");
    }
  }
});

export const PageRename = () => ({
  extends: DialogStory(),
  components: {
    "k-story-dialog": PageRenameDialog
  },
  methods: {
    open() {
      this.$refs.dialog.open("photography");
    }
  }
});

export const PageStatus = () => ({
  extends: PageRename(),
  components: {
    "k-story-dialog": PageStatusDialog
  }
});

export const PageTemplate = () => ({
  extends: PageRename(),
  components: {
    "k-story-dialog": PageTemplateDialog
  }
});

export const PageUrl = () => ({
  extends: PageRename(),
  components: {
    "k-story-dialog": PageSlugDialog
  }
});

export const SiteRename = () => ({
  extends: DialogStory(),
  components: {
    "k-story-dialog": SiteRenameDialog
  }
});

