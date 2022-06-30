export default {
	inheritAttrs: false,
	props: {
		column: {
			type: Object,
			default() {
				return {};
			}
		},
		field: Object,
		value: {}
	}
};
