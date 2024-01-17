<template>
	<k-panel-outside :class="viewClass">
		<!-- <div> as single child for grid layout -->
		<div class="k-dialog k-login-dialog">
			<h1 class="sr-only">
				{{ $t("login") }}
			</h1>

			<k-login-alert v-if="issue" @click.native="issue = null">
				{{ issue }}
			</k-login-alert>

			{{ methods }}

			<menu>
				<k-button
					v-for="(method, index) in methods"
					:key="index"
					:text="method"
				/>
			</menu>

			<k-dialog-body>
				<k-login-code v-if="form === 'code'" v-bind="$props" @error="onError" />
				<k-login-plugin v-else :methods="methods" @error="onError" />
			</k-dialog-body>
		</div>
	</k-panel-outside>
</template>

<script>
import LoginForm from "@/components/Login/LoginPasswordForm.vue";

/**
 * @internal
 */
export default {
	components: {
		"k-login-plugin": window.panel.plugins.login ?? LoginForm
	},
	props: {
		methods: Array,
		pending: Object
	},
	data() {
		return {
			issue: ""
		};
	},
	computed: {
		form() {
			if (this.pending.email) {
				return "code";
			}

			return "login";
		},
		viewClass() {
			if (this.form === "code") {
				return "k-login-code-view";
			}

			return "k-login-view";
		}
	},
	created() {
		this.$store.dispatch("content/clear");
	},
	methods: {
		async onError(error) {
			if (error === null) {
				this.issue = null;
				return;
			}

			if (error.details.challengeDestroyed === true) {
				// reset from the LoginCode component back to Login
				await this.$reload({
					globals: ["$system"]
				});
			}

			this.issue = error.message;
		}
	}
};
</script>

<style>
.k-login-dialog {
	--dialog-color-back: var(--color-white);
	--dialog-shadow: var(--shadow);
	container-type: inline-size;
}

.k-login-fields {
	position: relative;
}

.k-login-toggler {
	position: absolute;
	top: -2px;
	inset-inline-end: calc(var(--spacing-2) * -1);
	z-index: 1;
	color: var(--link-color);
	padding-inline: var(--spacing-2);
	text-decoration: underline;
	text-decoration-color: var(--link-color);
	text-underline-offset: 1px;
	height: var(--height-xs);
	line-height: 1;
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
	margin-top: var(--spacing-10);
}
</style>
