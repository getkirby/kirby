<template>
	<k-ui-examples>
		<h2 class="h4">State</h2>
		<k-ui-example label="context">
			<k-text>
				<p>
					<code>window.panel.notification.context</code> returns context where
					the notification should be displayed (view, dialog, drawer).
				</p>
				<k-code>{{ $panel.notification.context || "&nbsp;" }}</k-code>
				<k-button
					icon="alert"
					variant="filled"
					@click="$panel.notification.success('Yay')"
				/>
			</k-text>
		</k-ui-example>
		<k-ui-example label="details">
			<k-text>
				<p>
					<code>window.panel.notification.details</code> returns an object with
					additional details to be shown with the notification. This is used for
					error details for example.
				</p>
				<k-code>{{ $panel.notification.details || "&nbsp;" }}</k-code>
				<k-button
					icon="alert"
					variant="filled"
					@click="
						$panel.notification.success({
							message: 'Yay',
							details: { foo: 'bar' }
						})
					"
				/>
			</k-text>
		</k-ui-example>
		<k-ui-example label="icon">
			<k-text>
				<p>
					<code>window.panel.notification.icon</code> returns the custom icon,
					which might be defined for the notification. The default icon depends
					on the notification type.
				</p>
				<k-code>{{ $panel.notification.icon || "&nbsp;" }}</k-code>
				<k-button
					icon="alert"
					variant="filled"
					@click="
						$panel.notification.success({
							icon: 'edit',
							message: 'Edited'
						})
					"
				/>
			</k-text>
		</k-ui-example>
		<k-ui-example label="message">
			<k-text>
				<p>
					<code>window.panel.notification.message</code> returns the
					notification message
				</p>
				<k-code>{{ $panel.notification.message || "&nbsp;" }}</k-code>
				<k-button
					icon="alert"
					variant="filled"
					@click="
						$panel.notification.success({
							message: 'Hello'
						})
					"
				/>
			</k-text>
		</k-ui-example>
		<k-ui-example label="timeout">
			<k-text>
				<p>
					<code>window.panel.notification.timeout</code> returns the timeout
					when the notification will automatically disappear again.
				</p>
				<k-code>{{ $panel.notification.timeout || "&nbsp;" }}</k-code>
				<k-button
					icon="alert"
					variant="filled"
					@click="
						$panel.notification.success({
							message: 'Yay',
							timeout: 2000
						})
					"
				/>
			</k-text>
		</k-ui-example>
		<k-ui-example label="type">
			<k-text>
				<p>
					<code>window.panel.notification.type</code> returns the notification
					type (error, fatal, success)
				</p>
				<k-code>{{ $panel.notification.type || "&nbsp;" }}</k-code>
				<k-button
					icon="alert"
					variant="filled"
					@click="
						$panel.notification.success({
							message: 'Yay'
						})
					"
				/>
			</k-text>
		</k-ui-example>

		<h2 class="h4">Methods</h2>

		<k-ui-example label="close">
			<k-text>
				<k-ui-code language="js">window.panel.notification.close();</k-ui-code>
				<p>Closes the current notification</p>
				<k-button
					icon="alert"
					variant="filled"
					@click="$panel.notification.success('Nice one!')"
				/>
				<k-button
					icon="cancel"
					variant="filled"
					@click="$panel.notification.close()"
				/>
			</k-text>
		</k-ui-example>
		<k-ui-example label="deprecated">
			<k-text>
				<k-ui-code language="js"
					>window.panel.notification.deprecated(notification);</k-ui-code
				>
				<p>Used to show a deprecation warning in the console.</p>
				<k-button
					icon="alert"
					variant="filled"
					@click="$panel.notification.deprecated('Don`t use this anymore')"
				/>
			</k-text>
		</k-ui-example>
		<k-ui-example label="deprecated">
			<k-text>
				<k-ui-code language="js"
					>window.panel.notification.error(notification);</k-ui-code
				>
				<p>Displays an error notification.</p>
				<k-button
					icon="alert"
					variant="filled"
					@click="$panel.notification.error('Something went wrong')"
				/>
			</k-text>
		</k-ui-example>
		<k-ui-example label="fatal">
			<k-text>
				<k-ui-code language="js"
					>window.panel.notification.fatal(notification);</k-ui-code
				>
				<p>Displays a fatal error in an iframe overlay for better isolation</p>
				<k-button
					icon="alert"
					variant="filled"
					@click="$panel.notification.fatal('Something went wrong')"
				/>
			</k-text>
		</k-ui-example>
		<k-ui-example label="open">
			<k-text>
				<k-ui-code language="js"
					>window.panel.notification.open(notification);</k-ui-code
				>
				<p>Open a custom notification</p>
				<k-button
					icon="alert"
					variant="filled"
					@click="
						$panel.notification.open({ type: 'success', message: 'Nice one!' })
					"
				/>
			</k-text>
		</k-ui-example>
		<k-ui-example label="success">
			<k-text>
				<k-ui-code language="js"
					>window.panel.notification.success(notification);</k-ui-code
				>
				<p>Displays a success notification</p>
				<k-button
					icon="alert"
					variant="filled"
					@click="$panel.notification.success('Nice one!')"
				/>
			</k-text>
		</k-ui-example>
	</k-ui-examples>
</template>

<style>
.k-ui-examples h2 {
	margin-bottom: var(--spacing-6);
}
.k-ui-examples * + h2 {
	margin-top: var(--spacing-12);
}
</style>
