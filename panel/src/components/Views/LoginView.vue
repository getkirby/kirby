<template>
	<k-panel>
		<k-view align="center" :class="viewClass">
			<!-- <div> as a wrapper so that <k-view>
			     has a single child for Flexbox layout -->
			<div>
				<h1 class="sr-only">
					{{ $t("login") }}
				</h1>

				<k-login-alert v-if="issue" @click="issue = null">
					{{ issue }}
				</k-login-alert>

				<k-login-code v-if="form === 'code'" v-bind="$props" @error="onError" />
				<k-login-plugin v-else :methods="methods" @error="onError" />
			</div>
		</k-view>
	</k-panel>
</template>

<script>
import LoginForm from "../Forms/Login.vue";

export default {
	components: {
		"k-login-plugin": window.panel.plugins.login || LoginForm
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
.k-login-fields {
	position: relative;
}

.k-login-toggler {
	position: absolute;
	top: 0;
	inset-inline-end: 0;
	z-index: 1;

	text-decoration: underline;
	font-size: 0.875rem;
}

.k-login-form label abbr {
	visibility: hidden;
}

.k-login-buttons {
	display: flex;
	align-items: center;
	justify-content: flex-end;
	padding: 1.5rem 0;
}

.k-login-button {
	padding: 0.5rem 1rem;
	font-weight: 500;
	transition: opacity 0.3s;
	margin-inline-end: -1rem;
}

.k-login-button span {
	opacity: 1;
}

.k-login-button[disabled] {
	opacity: 0.25;
}

.k-login-back-button,
.k-login-checkbox {
	display: flex;
	align-items: center;
	flex-grow: 1;
}

.k-login-back-button {
	margin-inline-start: -1rem;
}

.k-login-checkbox {
	padding: 0.5rem 0;
	font-size: var(--text-sm);
	cursor: pointer;
}

.k-login-checkbox .k-checkbox-text {
	opacity: 0.75;
	transition: opacity 0.3s;
}

.k-login-checkbox:hover span,
.k-login-checkbox:focus span {
	opacity: 1;
}
</style>
