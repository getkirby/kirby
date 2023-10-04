<template>
	<k-ui-examples>
		<k-ui-example label="inline">
			<k-text> This is some <code>inline code</code> </k-text>
		</k-ui-example>
		<k-ui-example label="block">
			<k-text>
				<pre><code>This is a code block</code></pre>
			</k-text>
		</k-ui-example>
		<k-ui-example label="block with language">
			<k-text>
				<pre><code class="language-php">if ($page->title() === 'blog') echo 'hello'</code></pre>
			</k-text>
		</k-ui-example>
	</k-ui-examples>
</template>
