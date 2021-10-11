import Extension from "../Extension";

export default class Toolbar extends Extension {
  constructor(options = {}) {
    super(options);
  }

  close() {
    this.visible = false;
    this.emit();
  }

  emit() {
    this.editor.emit("toolbar", {
      marks: this.marks,
      nodes: this.nodes,
      nodeAttrs: this.nodeAttrs,
      position: this.position,
      visible: this.visible
    });
  }

  init() {
    this.position = {
      left: 0,
      bottom: 0
    };

    this.visible = false;

    this.editor.on("blur", () => {
      this.close();
    });

    this.editor.on("deselect", () => {
      this.close();
    });

    this.editor.on("select", ({ hasChanged }) => {
      /**
       * If the selection did not change,
       * it does not need to be repositioned,
       * but the marks still need to be updated
       */
      if (hasChanged === false) {
        this.emit();
        return;
      }

      this.open();
    });
  }

  get marks() {
    return this.editor.activeMarks;
  }

  get nodes() {
    return this.editor.activeNodes;
  }

  get nodeAttrs() {
    return this.editor.activeNodeAttrs;
  }

  open() {
    this.visible = true;
    this.reposition();
    this.emit();
  }

  reposition() {
    const { from, to } = this.editor.selection;

    const start = this.editor.view.coordsAtPos(from);
    const end = this.editor.view.coordsAtPos(to, true);

    // The box in which the tooltip is positioned, to use as base
    const editorRect = this.editor.element.getBoundingClientRect();

    // Find a center-ish x position from the selection endpoints (when
    // crossing lines, end may be more to the left)
    let left = (start.left + end.left) / 2 - editorRect.left;
    let bottom = Math.round(editorRect.bottom - start.top);

    return (this.position = {
      bottom,
      left
    });
  }

  get type() {
    return "toolbar";
  }
}
