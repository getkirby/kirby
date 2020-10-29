import { setBlockType } from "prosemirror-commands";
import nodeIsActive from "./nodeIsActive";

export default function (type, toggletype, attrs = {}) {
  return (state, dispatch, view) => {
    const isActive = nodeIsActive(state, type, attrs)

    if (isActive) {
      return setBlockType(toggletype)(state, dispatch, view)
    }

    return setBlockType(type, attrs)(state, dispatch, view)
  }
}
