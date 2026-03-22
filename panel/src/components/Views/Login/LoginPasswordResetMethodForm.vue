<template>
	<form
		class="k-login-form k-login-password-reset-method-form"
		@submit.prevent="onSubmit"
	>
		<k-fieldset
			ref="fieldset"
			:fields="fields"
			:value="data"
			class="k-login-fields"
			@input="data = $event"
		/>

		<k-login-footer>
			<k-login-submit :icon="submit.icon" :label="submit.label" />
		</k-login-footer>
	</form>
</template>

<script>
export default {
	props: {
		submit: Object
	},
	emits: ["submit"],
	data() {
		return {
			data: {
				email: ""
			}
		};
	},
	computed: {
		fields() {
			return {
				email: {
					autofocus: true,
					label: this.$t("email"),
					type: "email",
					required: true,
					link: false
				}
			};
		}
	},
	methods: {
		onSubmit() {
			this.$emit("submit", this.data);
		}
	}
};
</script>
