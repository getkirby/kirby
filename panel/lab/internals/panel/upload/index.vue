<template>
	<k-lab-examples>
		<h2 class="h4">State</h2>
		<k-lab-example label="state" :code="false">
			<k-text>
				<k-code>{{ $panel.upload.state() }}</k-code>
			</k-text>
		</k-lab-example>

		<h2 class="h4">Methods</h2>

		<k-lab-example label="close" :code="false">
			<k-text>
				<k-code language="js">window.panel.upload.close();</k-code>
				<p>Closes the upload and clears the queue</p>
			</k-text>
		</k-lab-example>
		<k-lab-example label="open" :code="false">
			<k-text>
				<k-code language="js">window.panel.upload.open(options);</k-code>
				<p>Opens the upload dialog with the given options</p>
				<k-button
					icon="upload"
					variant="filled"
					@click="$panel.upload.open()"
				/>
			</k-text>
		</k-lab-example>
		<k-lab-example label="pick" :code="false">
			<k-text>
				<k-code language="js">window.panel.upload.pick(options);</k-code>
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
		</k-lab-example>
		<k-lab-example label="remove" :code="false">
			<k-text>
				<k-code language="js">window.panel.upload.remove(fileId);</k-code>
				<p>Removes a file from the queue</p>
			</k-text>
		</k-lab-example>
		<k-lab-example label="reset" :code="false">
			<k-text>
				<k-code language="js">window.panel.upload.reset();</k-code>
				<p>Resets the queue</p>
			</k-text>
		</k-lab-example>
		<k-lab-example label="select" :code="false">
			<k-text>
				<k-code language="js">window.panel.upload.select(files);</k-code>
				<p>Selects additional files (needs a FileList)</p>
			</k-text>
		</k-lab-example>
		<k-lab-example label="set" :code="false">
			<k-text>
				<k-code language="js">window.panel.upload.set(state);</k-code>
				<p>Overwrites the current uploader state</p>
			</k-text>
		</k-lab-example>
		<k-lab-example label="start" :code="false">
			<k-text>
				<k-code language="js">window.panel.upload.start();</k-code>
				<p>Starts the upload of all files in the queue</p>
			</k-text>
		</k-lab-example>
		<k-lab-example label="state" :code="false">
			<k-text>
				<k-code language="js">window.panel.upload.state();</k-code>
				<p>Returns the current uploader state</p>
			</k-text>
		</k-lab-example>
	</k-lab-examples>
</template>
