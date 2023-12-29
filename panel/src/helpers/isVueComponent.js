import { h } from "vue";

export default function (comp) {
	const vnode = h(comp);

	if (!vnode.type) {
		return false;
	}

	// Check if it's just an HTML Element
	if (typeof vnode.type === "string") {
		return false;
	}

	// A component that has render or setup property
	if (vnode.type.setup || vnode.type.render) {
		return true;
	}

	return false;
}
