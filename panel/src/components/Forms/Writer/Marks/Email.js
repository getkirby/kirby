import Mark from "../Mark";

export default class Email extends Mark {
  get button() {
    return {
      icon: "email",
      label: "Email"
    };
  }

  commands() {
    return {
      email: () => {
        this.editor.emit("email");
      },
      insertEmail: (attrs = {}) => {
        if (attrs.href) {
          return this.update(attrs);
        }
      },
      removeEmail: () => {
        return this.remove();
      },
      toggleEmail: (attrs = {}) => {
        if (attrs.href?.length > 0) {
          this.editor.command("insertEmail", attrs);
        } else {
          this.editor.command("removeEmail");
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
    return "email";
  }

  pasteRules({ type, utils }) {
    return [
      utils.pasteRule(/^[\w-.]+@([\w-]+\.)+[\w-]{2,4}$/gi, type, (url) => ({
        href: url
      }))
    ];
  }

  plugins() {
    return [
      {
        props: {
          handleClick: (view, pos, event) => {
            const attrs = this.editor.getMarkAttrs("email");

            if (
              attrs.href &&
              event.altKey === true &&
              event.target instanceof HTMLAnchorElement
            ) {
              event.stopPropagation();
              window.open(attrs.href);
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
        title: {
          default: null
        }
      },
      inclusive: false,
      parseDOM: [
        {
          tag: "a[href^='mailto:']",
          getAttrs: (dom) => ({
            href: dom.getAttribute("href").replace("mailto:", ""),
            title: dom.getAttribute("title")
          })
        }
      ],
      toDOM: (node) => [
        "a",
        {
          ...node.attrs,
          href: "mailto:" + node.attrs.href
        },
        0
      ]
    };
  }
}
