<template>
	<form class="k-login-form" @submit.prevent="submit">
		<div class="k-login-fields">
			<k-fieldset
				ref="fieldset"
				:fields="fields"
				:value="user"
				@input="user = $event"
			/>
		</div>

		<footer class="k-login-buttons">
			<k-checkbox-input
				v-if="hasRemember"
				:label="$t('login.remember')"
				:checked="user.remember"
				:value="user.remember"
				@input="user.remember = $event"
			/>

			<k-button
				:disabled="isLoading"
				v-bind="submitButton"
				:icon="isLoading ? 'loader' : submitButton.icon"
				class="k-login-button"
				size="lg"
				theme="positive"
				type="submit"
				variant="filled"
			/>
		</footer>
	</form>
</template>

<script>
export default {
	props: {
		hasPassword: {
			type: Boolean,
			default: true
		},
		hasRemember: {
			type: Boolean,
			default: true
		},
		isLoading: {
			type: Boolean,
			default: false
		},
		submitButton: {
			type: Object,
			default: () => ({
				icon: "check",
				text: window.panel.t("login")
			})
		},
		type: String,
		/**
		 * Values to prefill the inputs
		 */
		value: {
			type: Object,
			default: () => ({})
		}
	},
	emits: ["submit"],
	data() {
		return {
			user: {
				email: "",
				password: "",
				remember: false,
				...this.value
			}
		};
	},
	computed: {
		fields() {
			const fields = {
				email: {
					autofocus: true,
					label: this.$t("email"),
					type: "email",
					required: true,
					link: false
				}
			};

			if (this.hasPassword === true) {
				fields.password = {
					label: this.$t("password"),
					type: "password",
					minLength: 8,
					required: true,
					autocomplete: "current-password",
					counter: false
				};
			}

			return fields;
		}
	},
	methods: {
		focus() {
			this.$refs.fieldset.focus("email");
		},
		submit() {
			// clear field data that is not needed for login
			const payload = { ...this.user, method: this.type };

			if (this.hasPassword === false) {
				delete payload.password;
			}

			if (this.hasRemember === false) {
				payload.remember = false;
			}

			this.$emit("submit", payload);
		}
	}
};
</script>
