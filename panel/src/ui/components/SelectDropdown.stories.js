import SelectDropdown from "./SelectDropdown.vue";
import Padding from "../storybook/Padding.js";
import { action } from "@storybook/addon-actions";

export default {
  title: "UI | Dropdown / Select Dropdown",
  decorators: [Padding],
  component: SelectDropdown
};

export const roles = () => ({
  data() {
    return {
      role: "admin"
    };
  },
  methods: {
    onChange(role, roleIndex) {
      action("change")(role, roleIndex);
      this.role = role.id;
    }
  },
  computed: {
    roles() {
      return [
        {
          id: "admin",
          text: "Admin",
          icon: "bolt",
          current: this.role === "admin"
        },
        {
          id: "editor",
          text: "Editor",
          icon: "bolt",
          current: this.role === "editor"
        },
        {
          id: "client",
          text: "Client",
          icon: "bolt",
          current: this.role === "client"
        }
      ];
    }
  },
  template: `
    <k-select-dropdown
      :options="roles"
      before="Role:"
      icon="bolt"
      @change="onChange"
    />
  `
});


export const languages = () => ({
  data() {
    return {
      language: "en"
    };
  },
  methods: {
    onChange(language, languageIndex) {
      action("change")(language, languageIndex);
      this.language = language.code;
    }
  },
  computed: {
    languages() {
      return [
        {
          code: "en",
          text: "English",
          current: this.language === "en"
        },
        "-",
        {
          code: "de",
          text: "Deutsch",
          current: this.language === "de"
        },
        {
          code: "es",
          text: "Español",
          current: this.language === "es"
        }
      ];
    }
  },
  template: `
    <k-select-dropdown
      :options="languages"
      icon="globe"
      @change="onChange"
    />
  `
});

