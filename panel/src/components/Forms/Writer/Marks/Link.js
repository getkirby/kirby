export default {
  toolbar: {
    icon: "url",
    label: "Link",
    action: "link"
  },
  attrs: {
    href: {},
    title: {
      default: null
    },
    target: {
      default: null
    }
  },
  inclusive: false,
  parseDOM: [{
    tag: "a[href]", getAttrs(dom) {
      return {
        href: dom.getAttribute("href"),
        title: dom.getAttribute("title"),
        target: dom.getAttribute("target"),
      }
    }
  }],
  toDOM(node) {
    let a = document.createElement("a");

    if (node.attrs.title) {
      a.setAttribute("title", node.attrs.title);
    }

    if (node.attrs.target === "_blank") {
      a.setAttribute("target", "_blank");
      a.setAttribute("rel", "noopener noreferrer");
    }

    a.setAttribute("href", node.attrs.href);

    a.addEventListener("click", function (e) {
      if (e.altKey === true) {
        window.open(node.attrs.href);
      }
    });

    return a;
  }
};
