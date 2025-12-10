<template>
	<k-panel-outside
		:class="form === 'code' ? 'k-login-code-view' : 'k-login-view'"
	>
		<!-- <div> as single child for grid layout -->
		<div class="k-dialog k-login k-login-dialog">
			<h1 class="sr-only">
				{{ $t("login") }}
			</h1>

			<k-login-alert v-if="issue" @click="issue = null">
				{{ issue }}
			</k-login-alert>

			<k-dialog-body>
				<k-login-code-form
					v-if="form === 'code'"
					v-bind="{ methods, pending, value: value.code }"
					@error="onError"
				/>
				<component
					:is="component"
					v-else
					v-bind="{ methods, value }"
					@error="onError"
				/>
			</k-dialog-body>
		</div>
	</k-panel-outside>
</template>

<script>
import { props as LoginCodeFormProps } from "./LoginCodeForm.vue";
import { props as LoginFormProps } from "./LoginForm.vue";

/**
 * @internal
 */
export default {
	components: {
		"k-login-plugin-form": window.panel.plugins.login
	},
	mixins: [LoginCodeFormProps, LoginFormProps],
	props: {
		/**
		 * Values to prefill the inputs
		 */
		value: {
			type: Object,
			default: () => ({
				code: "",
				email: "",
				password: ""
			})
		}
	},
	data() {
		return {
			issue: ""
		};
	},
	computed: {
		component() {
			return this.$panel.plugins.login ? "k-login-plugin-form" : "k-login-form";
		},
		form() {
			return this.pending.email ? "code" : "login";
		}
	},
	methods: {
		async onError(error) {
			if (error === null) {
				this.issue = null;
				return;
			}

			if (error.details.challengeDestroyed === true) {
				// reset from the LoginCode component back to Login
				await this.$panel.reload({
					globals: ["system"]
				});
			}

			this.issue = error.message;
		}
	}
};
</script>

<style>
.k-login {
	--dialog-color-back: light-dark(var(--color-white), var(--color-gray-950));
	--dialog-shadow: light-dark(var(--shadow), none);

	container-type: inline-size;
}

.k-login-buttons {
	--button-padding: var(--spacing-3);
	display: flex;
	gap: 1.5rem;
	align-items: center;
	justify-content: space-between;
	margin-top: var(--spacing-10);
}
</style>
