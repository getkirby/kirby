<template>
	<k-lab-examples>
		<k-lab-example label="Methods: Multiple alternatives">
			<k-stack class="k-login-stack">
				<k-login-methods
					:methods="[
						{ type: 'password', label: 'Password', icon: 'lock', active: true },
						{ type: 'code', label: 'Email code', icon: 'email', active: false },
						{ type: 'github', label: 'GitHub', icon: 'github', active: false }
					]"
					@change="method = $event"
				/>
			</k-stack>
		</k-lab-example>
		<k-lab-example label="Methods: Single method (should not show)">
			<k-login-methods
				:methods="[
					{ type: 'password', label: 'Password', icon: 'lock', active: true }
				]"
			/>
		</k-lab-example>
		<k-lab-example label="Challenges: Multiple alternatives">
			<k-stack class="k-login-stack">
				<k-login-challenges
					:challenges="[
						{ type: 'email', label: 'Email code', icon: 'email', active: true },
						{
							type: 'totp',
							label: 'Authenticator app',
							icon: 'qr-code',
							active: false
						},
						{
							type: 'webauthn',
							label: 'Passkey',
							icon: 'lock',
							active: false
						}
					]"
					@change="method = $event"
				/>
			</k-stack>
		</k-lab-example>
		<k-lab-example label="Challenges: Single challenge (should not show)">
			<k-login-challenges
				:challenges="[
					{ type: 'email', label: 'Email code', icon: 'email', active: true }
				]"
			/>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	data: () => ({
		method: null
	})
};
</script>
