<template>
	<form
		class="k-login-form k-login-password-method-form"
		@submit.prevent="onSubmit"
	>
		<k-fieldset
			ref="fieldset"
			:fields="fields"
			:value="data"
			class="k-login-fields"
			@input="data = { ...data, ...$event }"
		/>

		<k-login-footer>
			<k-login-remember :checked="data.long" @input="data.long = $event" />
			<k-login-submit :icon="submit.icon" :label="submit.label" />
		</k-login-footer>
	</form>
</template>

<script>
export default {
	props: {
		submit: Object,
		value: Object
	},
	emits: ["submit"],
	data() {
		return {
			data: {
				email: "",
				password: "",
				long: false,
				...this.value
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
				},
				password: {
					label: this.$t("password"),
					type: "password",
					minLength: 8,
					required: true,
					autocomplete: "current-password",
					counter: false
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
