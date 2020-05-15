import FileRenameDialog from "./FileRenameDialog.vue";
import FileRemoveDialog from "./FileRemoveDialog.vue";
import LanguageCreateDialog from "./LanguageCreateDialog.vue";
import LanguageRemoveDialog from "./LanguageRemoveDialog.vue";
import LanguageUpdateDialog from "./LanguageUpdateDialog.vue";
import PageCreateDialog from "./PageCreateDialog.vue";
import PageDuplicateDialog from "./PageDuplicateDialog.vue";
import PageRemoveDialog from "./PageRemoveDialog.vue";
import PageRenameDialog from "./PageRenameDialog.vue";
import PageStatusDialog from "./PageStatusDialog.vue";
import PageTemplateDialog from "./PageTemplateDialog.vue";
import PageUrlDialog from "./PageUrlDialog.vue";
import RegistrationDialog from "./RegistrationDialog.vue";
import SiteRenameDialog from "./SiteRenameDialog.vue";
import UserCreateDialog from "./UserCreateDialog.vue";
import UserEmailDialog from "./UserEmailDialog.vue";
import UserLanguageDialog from "./UserLanguageDialog.vue";
import UserPasswordDialog from "./UserPasswordDialog.vue";
import UserRemoveDialog from "./UserRemoveDialog.vue";
import UserRenameDialog from "./UserRenameDialog.vue";
import UserRoleDialog from "./UserRoleDialog.vue";
import Padding from "@/ui/storybook/Padding.js";
import { action } from "@storybook/addon-actions";

export default {
  title: "App | Dialogs",
  decorators: [Padding],
};

const DialogStory = () => ({
  components: {
    "k-story-dialog": FileRemoveDialog,
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

export const FileRemove = () => ({
  extends: DialogStory(),
  components: {
    "k-story-dialog": FileRemoveDialog
  },
  methods: {
    open() {
      this.$refs.dialog.open("pages/photography", "some-file.jpg");
    }
  }
});

export const FileRename = () => ({
  extends: FileRemove(),
  components: {
    "k-story-dialog": FileRenameDialog
  }
});

export const LanguageCreate = () => ({
  extends: DialogStory(),
  components: {
    "k-story-dialog": LanguageCreateDialog
  }
});

export const LanguageRemove = () => ({
  extends: DialogStory(),
  components: {
    "k-story-dialog": LanguageRemoveDialog
  },
  methods: {
    open() {
      this.$refs.dialog.open("de");
    }
  }
});

export const LanguageUpdate = () => ({
  extends: DialogStory(),
  components: {
    "k-story-dialog": LanguageUpdateDialog
  },
  methods: {
    open() {
      this.$refs.dialog.open("de");
    }
  }
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
    "k-story-dialog": PageUrlDialog
  }
});

export const Registration = () => ({
  extends: DialogStory(),
  components: {
    "k-story-dialog": RegistrationDialog
  }
});

export const SiteRename = () => ({
  extends: DialogStory(),
  components: {
    "k-story-dialog": SiteRenameDialog
  }
});

export const UserCreate = () => ({
  extends: DialogStory(),
  components: {
    "k-story-dialog": UserCreateDialog
  }
});

export const UserEmail = () => ({
  extends: DialogStory(),
  components: {
    "k-story-dialog": UserEmailDialog
  },
  methods: {
    open() {
      this.$refs.dialog.open("xyz");
    }
  }
});

export const UserLanguage = () => ({
  extends: UserEmail(),
  components: {
    "k-story-dialog": UserLanguageDialog
  },
});

export const UserPassword = () => ({
  extends: UserEmail(),
  components: {
    "k-story-dialog": UserPasswordDialog
  },
});

export const UserRemove = () => ({
  extends: UserEmail(),
  components: {
    "k-story-dialog": UserRemoveDialog
  },
});

export const UserRename = () => ({
  extends: UserEmail(),
  components: {
    "k-story-dialog": UserRenameDialog
  }
});

export const UserRole = () => ({
  extends: UserEmail(),
  components: {
    "k-story-dialog": UserRoleDialog
  }
});
