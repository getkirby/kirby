<template>
	<k-lab-examples>
		<k-lab-example label="Default">
			<k-login-form />
		</k-lab-example>
		<k-lab-example label="With password">
			<k-login-form :methods="['password']" />
		</k-lab-example>
		<k-lab-example label="Prefilled">
			<k-login-form
				:methods="['password']"
				:value="{
					email: 'homer@simpson.com',
					password: 'password1',
					remember: true
				}"
			/>
		</k-lab-example>
		<k-lab-example label="Toggle between login methods">
			<k-login-form :methods="['password', 'code']" />
		</k-lab-example>
		<k-lab-example label="Password reset">
			<k-login-form :methods="['password', 'password-reset']" />
		</k-lab-example>
	</k-lab-examples>
</template>
