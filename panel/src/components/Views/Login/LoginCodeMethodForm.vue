<template>
	<form
		class="k-login-form k-login-code-method-form"
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
