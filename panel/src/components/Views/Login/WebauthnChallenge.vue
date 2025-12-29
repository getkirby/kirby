<template>
	<form class="k-login-form k-login-webauthn" @submit.prevent="onSubmit">
		<k-user-info
			v-if="pending.email"
			:user="pending.email"
			class="k-login-user"
		/>

		<p class="k-login-text">Use a passkey saved for this account.</p>

		<footer class="k-login-buttons">
			<k-button
				:text="$t('back')"
				icon="angle-left"
				link="/logout"
				size="lg"
				variant="filled"
				class="k-login-button k-login-back-button"
			/>

			<k-button
				:disabled="isProcessing || !isSupported"
				:icon="isProcessing ? 'loader' : 'fingerprint'"
				text="Sign in via passkey"
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
import { props as LoginProps } from "./LoginView.vue";
import {
	normalizePublicKeyOptions,
	serializeAssertionCredential,
	supported
} from "@/helpers/webauthn.js";

export default {
	mixins: [LoginProps],
	emits: ["error"],
	data() {
		return {
			isProcessing: false
		};
	},
	computed: {
		isSupported() {
			return supported();
		}
	},
	mounted() {
		this.onSubmit();
	},
	methods: {
		async onSubmit() {
			this.$emit("error", null);

			this.isProcessing = true;

			try {
				const options = normalizePublicKeyOptions(
					this.pending.data?.options ?? this.pending.data ?? {}
				);

				const assertion = await navigator.credentials.get(options);

				if (!assertion) {
					this.isProcessing = false;
					return;
				}

				const payload = serializeAssertionCredential(assertion);
				await this.$api.auth.verifyCode(payload);

				this.$panel.notification.success({
					message: this.$t("welcome") + "!",
					icon: "smile"
				});

				this.$panel.reload();
			} catch (error) {
				if (error?.name === "NotAllowedError") {
					error = { ...error, message: "Passkey request was cancelled." };
				}

				this.$emit("error", error);
			} finally {
				this.isProcessing = false;
			}
		}
	}
};
</script>
