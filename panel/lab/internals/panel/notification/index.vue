<template>
	<k-lab-examples>
		<h2 class="h4">State</h2>
		<k-lab-example label="context" :code="false">
			<k-text>
				<p>
					<code>window.panel.notification.context</code> returns context where
					the notification should be displayed (view, dialog, drawer).
				</p>
				<k-code>{{ $panel.notification.context || "&nbsp;" }}</k-code>
				<k-button
					icon="megaphone"
					variant="filled"
					@click="$panel.notification.success('Yay')"
				/>
			</k-text>
		</k-lab-example>
		<k-lab-example label="details" :code="false">
			<k-text>
				<p>
					<code>window.panel.notification.details</code> returns an object with
					additional details to be shown with the notification. This is used for
					error details for example.
				</p>
				<k-code>{{ $panel.notification.details || "&nbsp;" }}</k-code>
				<k-button
					icon="megaphone"
					variant="filled"
					@click="
						$panel.notification.success({
							message: 'Yay',
							details: { foo: 'bar' }
						})
					"
				/>
			</k-text>
		</k-lab-example>
		<k-lab-example label="icon" :code="false">
			<k-text>
				<p>
					<code>window.panel.notification.icon</code> returns the custom icon,
					which might be defined for the notification. The default icon depends
					on the notification type.
				</p>
				<k-code>{{ $panel.notification.icon || "&nbsp;" }}</k-code>
				<k-button
					icon="megaphone"
					variant="filled"
					@click="
						$panel.notification.success({
							icon: 'edit',
							message: 'Edited'
						})
					"
				/>
			</k-text>
		</k-lab-example>
		<k-lab-example label="info" :code="false">
			<k-text>
				<k-code language="js"
					>window.panel.notification.info(notification);</k-code
				>
				<p>Displays an info notification</p>
				<k-button
					icon="megaphone"
					variant="filled"
					@click="$panel.notification.info('Did you know?')"
				/>
			</k-text>
		</k-lab-example>
		<k-lab-example label="message" :code="false">
			<k-text>
				<p>
					<code>window.panel.notification.message</code> returns the
					notification message
				</p>
				<k-code>{{ $panel.notification.message || "&nbsp;" }}</k-code>
				<k-button
					icon="megaphone"
					variant="filled"
					@click="
						$panel.notification.success({
							message: 'Hello'
						})
					"
				/>
			</k-text>
		</k-lab-example>
		<k-lab-example label="timeout" :code="false">
			<k-text>
				<p>
					<code>window.panel.notification.timeout</code> returns the timeout
					when the notification will automatically disappear again.
				</p>
				<k-code>{{ $panel.notification.timeout || "&nbsp;" }}</k-code>
				<k-button
					icon="megaphone"
					variant="filled"
					@click="
						$panel.notification.success({
							message: 'Yay',
							timeout: 2000
						})
					"
				/>
			</k-text>
		</k-lab-example>
		<k-lab-example label="type" :code="false">
			<k-text>
				<p>
					<code>window.panel.notification.type</code> returns the notification
					type (error, fatal, success)
				</p>
				<k-code>{{ $panel.notification.type || "&nbsp;" }}</k-code>
				<k-button
					icon="megaphone"
					variant="filled"
					@click="
						$panel.notification.success({
							message: 'Yay'
						})
					"
				/>
			</k-text>
		</k-lab-example>

		<h2 class="h4">Methods</h2>

		<k-lab-example label="close" :code="false">
			<k-text>
				<k-code language="js">window.panel.notification.close();</k-code>
				<p>Closes the current notification</p>
				<k-button
					icon="megaphone"
					variant="filled"
					@click="$panel.notification.success('Nice one!')"
				/>
				<k-button
					icon="cancel"
					variant="filled"
					@click="$panel.notification.close()"
				/>
			</k-text>
		</k-lab-example>
		<k-lab-example label="deprecated" :code="false">
			<k-text>
				<k-code language="js"
					>window.panel.notification.deprecated(notification);</k-code
				>
				<p>Used to show a deprecation warning in the console.</p>
				<k-button
					icon="megaphone"
					variant="filled"
					@click="$panel.notification.deprecated('Don`t use this anymore')"
				/>
			</k-text>
		</k-lab-example>
		<k-lab-example label="error" :code="false">
			<k-text>
				<k-code language="js"
					>window.panel.notification.error(notification);</k-code
				>
				<p>Displays an error notification.</p>
				<k-button
					icon="megaphone"
					variant="filled"
					@click="$panel.notification.error('Something went wrong')"
				/>
			</k-text>
		</k-lab-example>
		<k-lab-example label="fatal" :code="false">
			<k-text>
				<k-code language="js"
					>window.panel.notification.fatal(notification);</k-code
				>
				<p>Displays a fatal error in an iframe overlay for better isolation</p>
				<k-button
					icon="megaphone"
					variant="filled"
					@click="$panel.notification.fatal('Something went wrong')"
				/>
			</k-text>
		</k-lab-example>
		<k-lab-example label="open" :code="false">
			<k-text>
				<k-code language="js"
					>window.panel.notification.open(notification);</k-code
				>
				<p>Open a custom notification</p>
				<k-input :value="icon" type="text" @input="icon = $event" />
				<k-input :value="text" type="text" @input="text = $event" />
				<k-input :value="theme" type="text" @input="theme = $event" />
				<k-button
					icon="megaphone"
					variant="filled"
					@click="
						$panel.notification.open({
							message: text,
							icon,
							theme
						})
					"
				/>
			</k-text>
		</k-lab-example>
		<k-lab-example label="success" :code="false">
			<k-text>
				<k-code language="js"
					>window.panel.notification.success(notification);</k-code
				>
				<p>Displays a success notification</p>
				<k-button
					icon="megaphone"
					variant="filled"
					@click="$panel.notification.success('Nice one!')"
				/>
			</k-text>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	data() {
		return {
			icon: "check",
			text: "Yay",
			theme: "positive"
		};
	}
};
</script>
