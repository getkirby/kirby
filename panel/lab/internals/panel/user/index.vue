<template>
	<k-lab-examples>
		<h2 class="h4">State</h2>
		<k-lab-example label="email" :code="false">
			<k-text>
				<p>
					<code>window.panel.user.email</code> returns the email address of the
					authenticated user
				</p>
				<k-code>{{ $panel.user.email }}</k-code>
			</k-text>
		</k-lab-example>
		<k-lab-example label="id" :code="false">
			<k-text>
				<p>
					<code>window.panel.user.id</code> returns the unique ID of the
					authenticated user
				</p>
				<k-code>{{ $panel.user.id }}</k-code>
			</k-text>
		</k-lab-example>
		<k-lab-example label="language" :code="false">
			<k-text>
				<p>
					<code>window.panel.user.language</code> returns the preferred
					interface language of the authenticated user
				</p>
				<k-code>{{ $panel.user.language }}</k-code>
			</k-text>
		</k-lab-example>
		<k-lab-example label="role" :code="false">
			<k-text>
				<p>
					<code>window.panel.user.role</code> returns the role of the
					authenticated user
				</p>
				<k-code>{{ $panel.user.role }}</k-code>
			</k-text>
		</k-lab-example>
		<k-lab-example label="username" :code="false">
			<k-text>
				<p>
					<code>window.panel.user.username</code> returns the username of the
					authenticated user. The username is either a custom name or the email
					address as fallback.
				</p>
				<k-code>{{ $panel.user.username }}</k-code>
			</k-text>
		</k-lab-example>
	</k-lab-examples>
</template>
