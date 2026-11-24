<template>
	<k-lab-examples>
		<k-lab-example label="Password method">
			<k-login-password-method-form
				:submit="{ label: 'Login', icon: 'check' }"
			/>
		</k-lab-example>
		<k-lab-example label="Password method (prefilled)">
			<k-login-password-method-form
				:submit="{ label: 'Login', icon: 'check' }"
				:value="{
					email: 'editor@example.com',
					password: '12345678',
					long: true
				}"
			/>
		</k-lab-example>
		<k-lab-example label="Code method">
			<k-login-code-method-form
				:submit="{ label: 'Send code', icon: 'email' }"
			/>
		</k-lab-example>
		<k-lab-example label="Code method (prefilled)">
			<k-login-code-method-form
				:submit="{ label: 'Send code', icon: 'email' }"
				:value="{ email: 'editor@example.com', long: true }"
			/>
		</k-lab-example>
		<k-lab-example label="Password reset method">
			<k-login-password-reset-method-form
				:submit="{ label: 'Send reset link', icon: 'email' }"
			/>
		</k-lab-example>
	</k-lab-examples>
</template>
