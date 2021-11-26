import { setBlockType } from "prosemirror-commands";
import nodeIsActive from "./nodeIsActive";

export default function (type, toggleType, attrs = {}) {
  return (state, dispatch, view) => {
    const isActive = nodeIsActive(state, type, attrs);

    if (isActive) {
      return setBlockType(toggleType)(state, dispatch, view);
    }

    return setBlockType(type, attrs)(state, dispatch, view);
  };
}
