<template>
	<k-lab-examples>
		<k-lab-example label="Default">
			<k-login-code />
		</k-lab-example>
		<k-lab-example label="Prefilled">
			<k-login-code value="123456" />
		</k-lab-example>
		<k-lab-example label="Pending info">
			<k-login-code
				:pending="{ email: 'homer@simpson.com', challenge: 'totp' }"
			/>
		</k-lab-example>
	</k-lab-examples>
</template>
