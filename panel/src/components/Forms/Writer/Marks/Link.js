import Mark from "../Mark";

export default class Link extends Mark {
  get button() {
    return {
      icon: "url",
      label: window.panel.$t("toolbar.button.link")
    };
  }

  commands() {
    return {
      link: () => {
        this.editor.emit("link", this.editor);
      },
      insertLink: (attrs = {}) => {
        if (attrs.href) {
          return this.update(attrs);
        }
      },
      removeLink: () => {
        return this.remove();
      },
      toggleLink: (attrs = {}) => {
        if (attrs.href?.length > 0) {
          this.editor.command("insertLink", attrs);
        } else {
          this.editor.command("removeLink");
        }
      }
    };
  }

  get defaults() {
    return {
      target: null
    };
  }

  get name() {
    return "link";
  }

  pasteRules({ type, utils }) {
    return [
      utils.pasteRule(
        /https?:\/\/(www\.)?[-a-zA-Z0-9@:%._+~#=]{1,256}\.[a-zA-Z]{2,}\b([-a-zA-Z0-9@:%_+.~#?&//=,]*)/gi,
        type,
        (url) => ({ href: url })
      )
    ];
  }

  plugins() {
    return [
      {
        props: {
          handleClick: (view, pos, event) => {
            const attrs = this.editor.getMarkAttrs("link");

            if (
              attrs.href &&
              event.altKey === true &&
              event.target instanceof HTMLAnchorElement
            ) {
              event.stopPropagation();
              window.open(attrs.href, attrs.target);
            }
          }
        }
      }
    ];
  }

  get schema() {
    return {
      attrs: {
        href: {
          default: null
        },
        target: {
          default: null
        },
        title: {
          default: null
        }
      },
      inclusive: false,
      parseDOM: [
        {
          tag: "a[href]:not([href^='mailto:'])",
          getAttrs: (dom) => ({
            href: dom.getAttribute("href"),
            target: dom.getAttribute("target"),
            title: dom.getAttribute("title")
          })
        }
      ],
      toDOM: (node) => [
        "a",
        {
          ...node.attrs,
          rel: "noopener noreferrer"
        },
        0
      ]
    };
  }
}
