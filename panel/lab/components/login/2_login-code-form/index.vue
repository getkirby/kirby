<template>
	<k-lab-examples>
		<k-lab-example label="Default">
			<k-login-code-form />
		</k-lab-example>
		<k-lab-example label="Prefilled">
			<k-login-code-form value="123456" />
		</k-lab-example>
		<k-lab-example label="Pending info">
			<k-login-code-form
				:pending="{ email: 'homer@simpson.com', challenge: 'totp' }"
			/>
		</k-lab-example>
		<k-lab-example label="Password reset">
			<k-login-code-form :methods="['password-reset']" />
		</k-lab-example>
	</k-lab-examples>
</template>
