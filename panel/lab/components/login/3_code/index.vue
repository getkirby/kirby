<template>
	<k-lab-examples>
		<k-lab-example label="Mode: login (default)">
			<k-login-code-form email="example@getkirby.com" />
		</k-lab-example>
		<k-lab-example label="Mode: login (default) & Challenge: totp">
			<k-login-code-form challenge="totp" email="example@getkirby.com" />
		</k-lab-example>
		<k-lab-example label="Mode: passwort-reset">
			<k-login-code-form email="example@getkirby.com" mode="password-reset" />
		</k-lab-example>
		<k-lab-example label="Mode: passwort-reset & Challenge: totp">
			<k-login-code-form
				challenge="totp"
				email="example@getkirby.com"
				mode="password-reset"
			/>
		</k-lab-example>
	</k-lab-examples>
</template>
