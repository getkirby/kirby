export default {
	props: {
		blueprint: String,
		help: String,
		lock: [Boolean, Object],
		name: String,
		parent: String,
		timestamp: Number
	},
	methods: {
		load() {
			return this.$api.get(this.parent + "/sections/" + this.name);
		}
	}
};
