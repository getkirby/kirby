<template>
	<k-form-dialog
		ref="dialog"
		:fields="fields"
		:value="value"
		:submit-button="$t('insert')"
		@close="cancel"
		@input="value = $event"
		@submit="submit"
	/>
</template>

<script>
export default {
	data() {
		return {
			value: {
				email: null,
				text: null
			},
			fields: {
				email: {
					label: this.$t("email"),
					type: "email"
				},
				text: {
					label: this.$t("link.text"),
					type: "text"
				}
			}
		};
	},
	computed: {
		kirbytext() {
			return this.$panel.config.kirbytext;
		}
	},
	methods: {
		open(input, selection) {
			this.value.text = selection;
			this.$refs.dialog.open();
		},
		cancel() {
			this.$emit("cancel");
		},
		createKirbytext() {
			const email = this.value.email || "";
			if (this.value.text?.length > 0) {
				return `(email: ${email} text: ${this.value.text})`;
			} else {
				return `(email: ${email})`;
			}
		},
		createMarkdown() {
			const email = this.value.email || "";
			if (this.value.text?.length > 0) {
				return `[${this.value.text}](mailto:${email})`;
			} else {
				return `<${email}>`;
			}
		},
		submit() {
			// insert the link
			this.$emit(
				"submit",
				this.kirbytext ? this.createKirbytext() : this.createMarkdown()
			);

			// reset the form
			this.value = {
				email: null,
				text: null
			};

			// close the modal
			this.$refs.dialog.close();
		}
	}
};
</script>
