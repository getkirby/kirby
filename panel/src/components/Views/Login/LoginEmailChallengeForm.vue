<template>
	<form
		class="k-login-form k-login-email-challenge-form"
		@submit.prevent="onSubmit"
	>
		<k-user-info :user="user" />
		<k-login-code type="email" :value="code" @input="code = $event" />

		<k-login-footer :back="true">
			<k-login-submit :icon="submit.icon" :label="submit.label" />
		</k-login-footer>
	</form>
</template>

<script>
/**
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
export default {
	props: {
		submit: Object,
		user: String
	},
	emits: ["submit"],
	data() {
		return {
			code: ""
		};
	},
	methods: {
		onSubmit() {
			this.$emit("submit", { code: this.code });
		}
	}
};
</script>
