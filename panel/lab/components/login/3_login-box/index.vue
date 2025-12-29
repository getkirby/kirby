<template>
	<k-lab-examples>
		<k-lab-example label="Default">
			<k-stack class="k-login-stack">
				<k-login-form :methods="['password']" />

				<p class="k-login-or"><span>or</span></p>

				<k-stack>
					<k-button icon="key" variant="filled" size="lg">
						Login via Passkey
					</k-button>
					<k-button icon="email" variant="filled" size="lg">
						Login via Email
					</k-button>
				</k-stack>
			</k-stack>
		</k-lab-example>
	</k-lab-examples>
</template>

<style>
.k-login-stack {
	max-width: 25rem;
	margin: 0 auto;
	gap: var(--spacing-6);
}
.k-login-form {
	padding: var(--spacing-6);
	background: var(--color-white);
	border-radius: var(--rounded);
}
.k-login-or {
	position: relative;
	text-align: center;
	color: var(--color-text-dimmed);
}
.k-login-or span {
	background: var(--panel-color-back);
	padding: 0 0.5rem;
}
.k-login-or::before {
	position: absolute;
	content: "";
	top: 50%;
	left: 0;
	height: 1px;
	background: var(--color-border);
	width: 100%;
	z-index: -1;
}
</style>
