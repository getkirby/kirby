import { wrapInList, liftListItem } from "prosemirror-schema-list";
import findParentNode from "./findParentNode";

function isList(node, schema) {
  return (
    node.type === schema.nodes.bulletList ||
    node.type === schema.nodes.orderedList ||
    node.type === schema.nodes.todoList
  );
}

export default function toggleList(listType, itemType) {
  return (state, dispatch, view) => {
    const { schema, selection } = state;
    const { $from, $to } = selection;
    const range = $from.blockRange($to);

    if (!range) {
      return false;
    }

    const parentList = findParentNode((node) => isList(node, schema))(
      selection
    );

    if (range.depth >= 1 && parentList && range.depth - parentList.depth <= 1) {
      if (parentList.node.type === listType) {
        return liftListItem(itemType)(state, dispatch, view);
      }

      if (
        isList(parentList.node, schema) &&
        listType.validContent(parentList.node.content)
      ) {
        const { tr } = state;
        tr.setNodeMarkup(parentList.pos, listType);

        if (dispatch) {
          dispatch(tr);
        }

        return false;
      }
    }

    return wrapInList(listType)(state, dispatch, view);
  };
}
