<script>
import FormDialog from "./FormDialog.vue";

export default {
	extends: FormDialog,
	watch: {
		"value.name"(name) {
			if (this.fields.code.disabled) {
				return;
			}

			this.onNameChanges(name);
		},
		"value.code"(code) {
			if (this.fields.code.disabled) {
				return;
			}

			this.value.code = this.$helper.slug(code, [this.$panel.system.ascii]);
			this.onCodeChanges(this.value.code);
		}
	},
	methods: {
		onCodeChanges(code) {
			if (!code) {
				return (this.value.locale = null);
			}

			if (code.length >= 2) {
				// if the locale value entered has a hyphen
				// it divides the text and capitalizes the hyphen after it
				// code: en-us > locale: en_US
				if (code.indexOf("-") !== -1) {
					let segments = code.split("-");
					let locale = [segments[0], segments[1].toUpperCase()];
					this.value.locale = locale.join("_");
				} else {
					// if the entered language code exists
					// matches the locale values in the languages defined in the system
					let locales = this.$panel.system.locales ?? [];
					this.value.locale = locales?.[code];
				}
			}
		},
		onNameChanges(name) {
			this.value.code = this.$helper
				.slug(name, [this.value.rules, this.$panel.system.ascii])
				.substr(0, 2);
		}
	}
};
</script>
