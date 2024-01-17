<template>
	<k-lab-examples>
		<k-lab-example :flex="true" label="Button">
			<k-login-button />
			<k-login-button :loading="true" />
			<k-login-button text="Rest password" />
		</k-lab-example>
		<k-lab-example label="Alert">
			<k-login-alert>Oh oh, that didn't work</k-login-alert>
		</k-lab-example>
	</k-lab-examples>
</template>
