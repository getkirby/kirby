
export default {
  bold: () => {
    return {
      shortcut: "b"
    };
  },
  code: () => {
    return {};
  },
  email: () => {
    return {
      shortcut: "e"
    };
  },
  file: (Toolbar, options) => {
    let dropdown = {};

    // merge options with defaults
    options = {
      ...{
        select: true,
        upload: true
      },
      ...options
    };

    if (options.select === false && options.upload === false) {
      return false;
    }

    // select option
    if (options.select) {
      dropdown.select = {
        args: "select",
        command: "file",
        icon: "check",
        label: Toolbar.$t("toolbar.button.file.select")
      };
    }

    // upload option
    if (options.upload) {
      dropdown.upload = {
        args: "upload",
        command: "file",
        icon: "upload",
        label: Toolbar.$t("toolbar.button.file.upload")
      };
    }

    return {
      icon: "attachment",
      dropdown: dropdown
    };
  },
  headings: (Toolbar, options) => {
    const levels = Array.from({ length: options.levels || 3 }, (v, k) => k + 1);

    return {
      icon: "title",
      dropdown: levels.map(level => {
        return {
          args: level,
          command: "heading",
          icon: "title",
          label: Toolbar.$t("toolbar.button.heading." + level, "Heading " + level)
        };
      })
    };
  },
  italic: () => {
    return {
      shortcut: "i"
    };
  },
  link: () => {
    return {
      icon: "url",
      shortcut: "k"
    };
  },
  ol: () => {
    return {
      args: "ol",
      command: "list",
      icon: "list-numbers",
    };
  },
  ul: () => {
    return {
      args: "ul",
      command: "list",
      icon: "list-bullet",
    };
  }
};
