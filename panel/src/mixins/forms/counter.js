export default {
	props: {
		counter: {
			type: Boolean,
			default: true
		}
	},
	computed: {
		counterOptions() {
			const value = this.counterValue ?? this.value;

			if (this.counter === false || this.disabled || !value) {
				return false;
			}

			return {
				count: Array.isArray(value) ? value.length : String(value).length,
				min: this.$props.min ?? this.$props.minlength,
				max: this.$props.max ?? this.$props.maxlength
			};
		},
		counterValue() {
			return null;
		}
	}
};
