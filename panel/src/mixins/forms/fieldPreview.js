export default {
	inheritAttrs: false,
	props: {
		column: {
			type: Object,
			default: () => ({})
		},
		field: Object,
		value: {}
	}
};
