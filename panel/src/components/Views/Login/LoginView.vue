<template>
	<k-panel-outside class="k-login-view">
		<!-- <div> as single child for grid layout -->
		<div class="k-dialog k-login k-login-dialog">
			<h1 class="sr-only">
				{{ $t("login") }}
			</h1>

			<k-login-alert v-if="issue" @click="issue = null">
				{{ issue }}
			</k-login-alert>

			<k-dialog-body>
				<component
					:is="component"
					v-bind="{ method, methods, pending, value }"
					@error="onError"
				/>
			</k-dialog-body>

			<template v-if="alternativeMethods.length > 1">
				<hr />
				<k-dialog-footer>
					<k-button
						:dropdown="true"
						text="Sign in via"
						size="xs"
						@click="$refs.methods.toggle()"
					/>
					<k-dropdown
						ref="methods"
						:options="alternativeMethods"
						align-x="start"
					/>
				</k-dialog-footer>
			</template>
		</div>
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
			type: Object,
			default: () => ({ challenge: "email" })
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
			return this.methods.map((method) => ({
				text: this.$t(`login.method.${method}.label`),
				current: method === this.method
			}));
		},
		component() {
			return window.panel.plugins.login ? "k-login-plugin-form" : this.form;
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

.k-login-dialog hr {
	border-top: 1px solid var(--color-border);
}
.k-login-dialog .k-dialog-footer {
	padding-block: var(--spacing-3);
	text-align: end;
}
</style>
