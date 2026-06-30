<template>
	<k-lab-examples>
		<k-lab-example label="k-login-alert">
			<k-login-alert v-if="alert" @click="alert = null">
				{{ alert }}
			</k-login-alert>
		</k-lab-example>
		<k-lab-example label="k-login-back">
			<k-login-back />
		</k-lab-example>
		<k-lab-example label="k-login-code: email">
			<k-login-code type="email" :value="code" @input="code = $event" />
		</k-lab-example>
		<k-lab-example label="k-login-code: totp">
			<k-login-code type="totp" :value="code" @input="code = $event" />
		</k-lab-example>
		<k-lab-example label="k-login-footer: only slot">
			<k-login-footer>
				<k-login-submit />
			</k-login-footer>
		</k-lab-example>
		<k-lab-example label="k-login-footer: with back: true">
			<k-login-footer :back="true">
				<k-login-submit />
			</k-login-footer>
		</k-lab-example>
		<k-lab-example label="k-login-remember">
			<k-login-remember :checked="remember" @input="remember = $event" />
		</k-lab-example>
		<k-lab-example label="k-login-submit" :flex="true">
			<k-login-submit />
			<k-login-submit label="Sign me in" icon="heart" />
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	data: () => ({
		alert: "Invalid email or password",
		code: "",
		remember: false
	})
};
</script>
