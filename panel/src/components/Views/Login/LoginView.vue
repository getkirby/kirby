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
				is="k-legacy-plugin-form"
				v-if="hasLegacyPluginForm"
				:value="value"
				@error="onError"
			/>
			<component
				v-else
				:is="form.component"
				ref="form"
				v-bind="form.props"
				:is-loading="$panel.view.isLoading"
				:value="value"
				@submit="onSubmit"
			/>

			<template v-if="!isChallenge && alternatives.length > 0">
				<p class="k-login-or"><span>or</span></p>

				<k-stack>
					<k-button
						v-for="alternative in alternatives"
						:key="alternative.type"
						v-bind="alternative"
						variant="filled"
						size="lg"
						@click="onAlternative(alternative.type)"
					/>
				</k-stack>
			</template>
		</k-stack>
	</k-panel-outside>
</template>

<script>
/**
 * @internal
 */
export default {
	components: {
		"k-legacy-plugin-form": window.panel.plugins.login
	},
	props: {
		/**
		 * List of available login alternatives
		 * @since 6.0.0
		 */
		alternatives: {
			type: Array,
			default: () => []
		},
		/**
		 * Type of currently displayed auth method/challenge
		 * @since 6.0.0
		 */
		current: {
			type: String
		},
		/**
		 * Form component to be rendered
		 * @since 6.0.0
		 */
		form: {
			type: Object,
			required: true
		},
		/**
		 * Values to prefill the inputs
		 */
		value: {
			type: Object,
			default: () => ({})
		}
	},
	data() {
		return {
			issue: ""
		};
	},
	computed: {
		hasLegacyPluginForm() {
			return Boolean(window.panel.plugins.login);
		},
		isChallenge() {
			return Boolean(this.form.props.pending.email);
		}
	},
	methods: {
		async onAlternative(current) {
			await this.$panel.view.refresh({ query: { current } });
			this.$refs.form.focus?.();
		},
		async onSubmit(data) {
			this.issue = null;

			try {
				await this.$panel.view.post({ ...data, current: this.current });

				this.$panel.notification.success({
					message: this.$t("welcome") + "!",
					icon: "smile"
				});
			} catch (error) {
				// reset from the LoginCode component back to Login
				if (error?.details?.challengeDestroyed === true) {
					await this.$panel.reload({ globals: ["system"] });
				}

				this.issue = error?.message;
			}
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
	background: light-dark(var(--color-white), var(--color-gray-950));
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
