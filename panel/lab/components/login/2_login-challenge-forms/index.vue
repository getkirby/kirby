<template>
	<k-lab-examples>
		<k-lab-example label="Email challenge">
			<k-login-email-challenge-form
				:submit="{ label: 'Log In', icon: 'check' }"
				user="homer@simpson.com"
			/>
		</k-lab-example>
		<k-lab-example label="TOTP challenge">
			<k-login-totp-challenge-form
				:submit="{ label: 'Log In', icon: 'check' }"
				user="homer@simpson.com"
			/>
		</k-lab-example>
	</k-lab-examples>
</template>
