<template>
	<k-panel-outside class="k-login-view">
		<k-stack class="k-login k-login-stack">
			<h1 class="sr-only">
				{{ $t("login") }}
			</h1>

			<k-login-alert v-if="error" @click="error = null">
				{{ error.message }}
			</k-login-alert>

			<component
				:is="form.component"
				v-bind="form.props"
				@error="onError"
				@submit="onSubmit"
			/>

			<k-login-methods
				v-if="this.state !== 'pending'"
				:methods="methods"
				@change="onChangeMethod"
			/>
			<k-login-challenges
				v-else
				:challenges="challenges"
				@change="onChangeChallenge"
			/>
		</k-stack>
	</k-panel-outside>
</template>

<script>
/**
 * @internal
 */
export default {
	props: {
		/**
		 * Available challenges for the challenge switcher.
		 * @value [{ type, label, icon, active }]
		 */
		challenges: {
			type: Array,
			default: () => []
		},
		/**
		 * Form component definition from the backend.
		 * @value { component: String, props: Object }
		 */
		form: {
			type: Object,
			default: () => ({})
		},
		/**
		 * Available login methods for the method switcher.
		 * @value [{ type, label, icon, active }]
		 */
		methods: {
			type: Array,
			default: () => []
		},
		/**
		 * Current auth state: "inactive" | "pending"
		 */
		state: String
	},
	data() {
		return {
			error: null
		};
	},
	watch: {
		form() {
			this.error = null;
		}
	},
	methods: {
		onChangeMethod(method) {
			this.$panel.open("login/method/" + method.type);
		},
		onChangeChallenge(challenge) {
			this.$panel.open("login/challenge/" + challenge.type);
		},
		onError(error) {
			this.error = error;
		},
		async onSubmit(data) {
			try {
				// clear any existing error
				this.error = null;

				// submit the form data to the backend
				const response = await this.$panel.post(this.$panel.view.path, data);

				// if login was successful, the user will be redirected…
				if (response?.redirect) {
					await this.$panel.open(response.redirect);

					// …and we show a welcome notification
					this.$panel.notification.success({
						message: this.$t("welcome") + "!",
						icon: "smile"
					});
				}
			} catch (error) {
				this.onError(error);
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

.k-login-form {
	display: flex;
	flex-direction: column;
	gap: var(--spacing-6);
	padding: var(--spacing-6);
	background: light-dark(var(--color-white), var(--color-gray-950));
	border-radius: var(--rounded);
}
.k-login-form label abbr {
	visibility: hidden;
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
