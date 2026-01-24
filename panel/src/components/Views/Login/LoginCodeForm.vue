<template>
	<form class="k-login-form" @submit.prevent="submit">
		<k-user-info v-if="pending.email" :user="pending.email" />

		<k-text-field
			ref="input"
			:autofocus="true"
			:counter="false"
			:help="$t('login.code.text.' + pending.challenge)"
			:label="
				isResetForm
					? $t('login.code.label.password-reset')
					: $t('login.code.label.login')
			"
			:placeholder="$t('login.code.placeholder.' + pending.challenge)"
			:required="true"
			:value="code"
			autocomplete="one-time-code"
			icon="unlock"
			name="code"
			@input="code = $event"
		/>

		<footer class="k-login-buttons">
			<k-login-back-button />

			<k-button
				:disabled="isLoading"
				:icon="isLoading ? 'loader' : 'check'"
				:text="isResetForm ? $t('login.reset') : $t('login')"
				size="lg"
				type="submit"
				theme="positive"
				variant="filled"
				class="k-login-button"
			/>
		</footer>
	</form>
</template>

<script>
export default {
	props: {
		isLoading: {
			type: Boolean,
			default: false
		},
		pending: {
			type: Object
		}
	},
	emits: ["submit"],
	data() {
		return {
			code: ""
		};
	},
	computed: {
		isResetForm() {
			return this.pending?.mode === "password-reset";
		}
	},
	methods: {
		focus() {
			this.$refs.input.focus();
		},
		submit() {
			this.$emit("submit", { input: this.code });
		}
	}
};
</script>
