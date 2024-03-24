<template>
	<k-lab-examples>
		<k-lab-example label="Default">
			<k-login />
		</k-lab-example>
		<k-lab-example label="With password">
			<k-login :methods="['password']" />
		</k-lab-example>
		<k-lab-example label="Prefilled">
			<k-login
				:methods="['password']"
				:value="{
					email: 'homer@simpson.com',
					password: 'password1',
					remember: true
				}"
			/>
		</k-lab-example>
		<k-lab-example label="Toggle between login methods">
			<k-login :methods="['password', 'code']" />
		</k-lab-example>
		<k-lab-example label="Password reset">
			<k-login :methods="['password', 'password-reset']" />
		</k-lab-example>
	</k-lab-examples>
</template>
