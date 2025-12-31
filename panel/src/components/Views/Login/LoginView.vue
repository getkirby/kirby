<template>
	<k-panel-outside class="k-login-view">
		<k-stack class="k-login k-login-stack">
			<h1 class="sr-only">
				{{ $t("login") }}
			</h1>

			<k-login-alert v-if="issue" @click="issue = null">
				{{ issue }}
			</k-login-alert>

			<component
				:is="component"
				ref="form"
				v-bind="{ method, methods, pending, value }"
				@error="onError"
			/>

			<template v-if="alternativeMethods.length > 0 && !hasActiveChallenge">
				<p class="k-login-or"><span>or</span></p>

				<k-stack>
					<k-button
						v-for="method in alternativeMethods"
						:key="method.type"
						:icon="method.icon"
						:text="method.text"
						variant="filled"
						size="lg"
						@click="onChangeMethod(method.type)"
					/>
				</k-stack>
			</template>
		</k-stack>
	</k-panel-outside>
</template>

<script>
export const props = {
	props: {
		/**
		 * Current login method
		 * @since 6.0.0
		 */
		method: String,
		/**
		 * List of available login method names
		 */
		methods: {
			type: Array,
			default: () => []
		},
		/**
		 * Pending login data (user email, challenge type)
		 * @value { email: String, challenge: String }
		 */
		pending: {
			type: Object
		},
		/**
		 * Values to prefill the inputs
		 */
		value: {
			type: Object,
			default: () => ({})
		}
	}
};

/**
 * @internal
 */
export default {
	components: {
		"k-login-plugin-form": window.panel.plugins.login
	},
	mixins: [props],
	props: {
		/**
		 * Vue component name for the login form
		 * @since 6.0.0
		 */
		form: String
	},
	data() {
		return {
			issue: ""
		};
	},
	computed: {
		alternativeMethods() {
			return this.methods.filter((method) => method.type !== this.method);
		},
		component() {
			return window.panel.plugins.login ? "k-login-plugin-form" : this.form;
		},
		hasActiveChallenge() {
			return Boolean(this.pending.email);
		}
	},
	methods: {
		onChangeMethod(method) {
			this.$panel.view.refresh({ query: { method } });
		},
		async onError(error) {
			// reset from a challenge component back to Login
			if (error?.details?.challengeDestroyed === true) {
				await this.$panel.reload({ globals: ["system"] });
			}

			this.issue = error?.message ?? null;
		}
	}
};
</script>

<style>
.k-login-stack {
	max-width: 25rem;
	margin: 0 auto;
	gap: var(--spacing-6);
}

.k-login-stack .k-user-info {
	margin-bottom: var(--spacing-6);
}

.k-login-form {
	padding: var(--spacing-6);
	background: var(--color-white);
	border-radius: var(--rounded);
}
.k-login-form label abbr {
	visibility: hidden;
}

.k-login-buttons {
	--button-padding: var(--spacing-3);
	display: flex;
	gap: 1.5rem;
	align-items: center;
	justify-content: space-between;
	margin-top: var(--spacing-8);
}

.k-login-or {
	position: relative;
	text-align: center;
	color: var(--color-text-dimmed);
}
.k-login-or span {
	background: var(--panel-color-back);
	padding: 0 0.5rem;
}
.k-login-or::before {
	position: absolute;
	content: "";
	top: 50%;
	left: 0;
	height: 1px;
	background: var(--color-border);
	width: 100%;
	z-index: -1;
}
</style>
