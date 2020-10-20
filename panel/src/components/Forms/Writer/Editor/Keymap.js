/** Keymaps */
import CodeKeymap from "../Keymaps/Code.js";
import DefaultKeymap from "../Keymaps/Default.js";

export default function (props) {
  return props.code === true ? CodeKeymap(props) : DefaultKeymap(props);
};
