export default function insertNode(type, attrs, content, marks) {
	return (state, dispatch) => {
		dispatch(
			state.tr
				.replaceSelectionWith(type.create(attrs, content, marks))
				.scrollIntoView()
		);
	};
}
