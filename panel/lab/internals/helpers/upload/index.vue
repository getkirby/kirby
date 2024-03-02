<template>
	<k-lab-examples class="k-lab-helpers-examples">
		<k-lab-example label="$helper.upload()" :code="false">
			<k-text>
				<p>Uploads a file using XMLHttpRequest:</p>

				<!-- prettier-ignore -->
				<k-code language="javascript">this.$helper.upload(file, params): Promise</k-code>

				<ul>
					<li><code>file</code> (File): file to upload</li>
					<li>
						<code>params</code> (object): upload option

						<ul>
							<li>
								<code>url</code> (string): URL endpoint to sent the request to
							</li>
							<li>
								<code>method</code> (string): HTTP method (e.g.
								<code>"POST"</code>)
							</li>
							<li>
								<code>filename</code> (string): filename to use for the upload
							</li>
							<li><code>headers</code> (object): request headers</li>
							<li><code>attributes</code> (object): additional attributes</li>
							<li>
								<code>progress</code> (Function): callback whenever the progress
								changes
							</li>
							<li>
								<code>complete</code> (Function): callback when upload completed
							</li>
							<li>
								<code>success</code> (Function): callback when upload succeeded
							</li>
							<li>
								<code>error</code> (Function): callback when upload failed
							</li>
						</ul>
					</li>
				</ul>
			</k-text>
		</k-lab-example>
	</k-lab-examples>
</template>
