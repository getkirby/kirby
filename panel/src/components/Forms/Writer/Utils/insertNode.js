export default function insertNode(type, attrs, content, marks) {
	return (state, dispatch) => {
		const { tr } = state;
		const node = type.create(attrs, content, marks);

		tr.replaceSelectionWith(node).scrollIntoView();

		if (dispatch) {
			dispatch(tr);
		}

		return true;
	};
}
