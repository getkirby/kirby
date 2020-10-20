import { TextSelection } from "prosemirror-state";
import { DOMSerializer } from "prosemirror-model";
import { toggleMark } from "prosemirror-commands";


export function marker(schema, view, mark, attrs) {
  if (schema.marks[mark]) {
    toggleMark(schema.marks[mark], attrs)(view.state, view.dispatch);
  }

  view.focus();
};

export function getActiveMarks(schema, state, markTypes) {
  let marks = [];

  markTypes.forEach(markType => {
    const mark = schema.marks[markType];

    if (mark && markIsActive(state, mark)) {
      marks.push(mark.name);
    }
  });

  return marks;
};

export function markIsActive(state, type) {
  const {
    from,
    $from,
    to,
    empty,
  } = state.selection;

  if (empty) {
    return !!type.isInSet(state.storedMarks || $from.marks());
  }

  return !!state.doc.rangeHasMark(from, to, type);
};

export function getHTML(state, code) {

  const div = document.createElement('div');
  const fragment = DOMSerializer
    .fromSchema(state.schema)
    .serializeFragment(state.doc.content);

  div.appendChild(fragment);

  return code ? div.innerText : div.innerHTML;

};

export function getMarkAttrs(state, type) {
  const { from, to } = state.selection
  let marks = []

  state.doc.nodesBetween(from, to, node => {
    marks = [...marks, ...node.marks]
  })

  const mark = marks.find(markItem => markItem.type.name === type)

  if (mark) {
    return mark.attrs
  }

  return {}
};
