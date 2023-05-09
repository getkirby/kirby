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

			if (value === null || this.disabled || this.counter === false) {
				return false;
			}

			let count = 0;

			if (value) {
				if (Array.isArray(value)) {
					count = value.length;
				} else {
					count = String(value).length;
				}
			}
			return {
				count: count,
				min: this.min ?? this.minlength,
				max: this.max ?? this.maxlength
			};
		}
	}
};
