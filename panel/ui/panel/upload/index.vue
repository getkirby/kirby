<template>
	<k-ui-examples>
		<h2 class="h4">State</h2>
		<k-ui-example label="state">
			<k-text>
				<k-code>{{ $panel.upload.state() }}</k-code>
			</k-text>
		</k-ui-example>

		<h2 class="h4">Methods</h2>

		<k-ui-example label="close">
			<k-text>
				<k-ui-code language="js">window.panel.upload.close();</k-ui-code>
				<p>Closes the upload and clears the queue</p>
			</k-text>
		</k-ui-example>
		<k-ui-example label="open">
			<k-text>
				<k-ui-code language="js">window.panel.upload.open(options);</k-ui-code>
				<p>Opens the upload dialog with the given options</p>
				<k-button
					icon="upload"
					variant="filled"
					@click="$panel.upload.open()"
				/>
			</k-text>
		</k-ui-example>
		<k-ui-example label="pick">
			<k-text>
				<k-ui-code language="js">window.panel.upload.pick(options);</k-ui-code>
				<p>
					Opens the system file picker. Once files have been selected, the
					upload dialog will open to edit the upload queue and start the upload.
				</p>
				<k-button
					icon="upload"
					variant="filled"
					@click="$panel.upload.pick()"
				/>
			</k-text>
		</k-ui-example>
		<k-ui-example label="remove">
			<k-text>
				<k-ui-code language="js">window.panel.upload.remove(fileId);</k-ui-code>
				<p>Removes a file from the queue</p>
			</k-text>
		</k-ui-example>
		<k-ui-example label="reset">
			<k-text>
				<k-ui-code language="js">window.panel.upload.reset();</k-ui-code>
				<p>Resets the queue</p>
			</k-text>
		</k-ui-example>
		<k-ui-example label="select">
			<k-text>
				<k-ui-code language="js">window.panel.upload.select(files);</k-ui-code>
				<p>Selects additional files (needs a FileList)</p>
			</k-text>
		</k-ui-example>
		<k-ui-example label="set">
			<k-text>
				<k-ui-code language="js">window.panel.upload.set(state);</k-ui-code>
				<p>Overwrites the current uploader state</p>
			</k-text>
		</k-ui-example>
		<k-ui-example label="start">
			<k-text>
				<k-ui-code language="js">window.panel.upload.start();</k-ui-code>
				<p>Starts the upload of all files in the queue</p>
			</k-text>
		</k-ui-example>
		<k-ui-example label="state">
			<k-text>
				<k-ui-code language="js">window.panel.upload.state();</k-ui-code>
				<p>Returns the current uploader state</p>
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
