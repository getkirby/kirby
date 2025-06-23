<template>
	<k-lab-examples>
		<k-text>
			For Panel plugins, we provide <code>this.$panel.content</code> to fetch
			versions of the content, see if there are any diffs and more.
		</k-text>

		<k-lab-example label="Get a version's content" :code="false">
			<k-code language="javascript">this.$panel.content.version('latest');
this.$panel.content.version('changes');</k-code>
		</k-lab-example>

		<k-lab-example label="Check if there any diffs between latest and changes" :code="false">
			<k-code language="javascript">this.$panel.content.hasDiff();
this.$panel.content.diff();</k-code>
		</k-lab-example>

		<k-lab-example label="Discard content changes" :code="false">
			<k-code language="javascript">await this.$panel.content.discard();</k-code>
		</k-lab-example>

		<k-lab-example label="Check if the content is locked" :code="false">
			<k-code language="javascript">this.$panel.content.isLocked();

// get lock info
this.$panel.content.lock();</k-code>
		</k-lab-example>

		<k-lab-example label="Publish changes" :code="false">
			<k-code language="javascript">await this.$panel.content.publish();

// you can pass new values optionally
await this.$panel.content.publish({
  text: 'Changed text'
});</k-code>
		</k-lab-example>

		<k-lab-example label="Update changes" :code="false">
			<k-code language="javascript">await this.$panel.content.update({
  text: 'Changed text'
});


// Throttle updates: This is particularly useful if you bind
// this to an input event that gets fired often. All our fields
// update changes lazily to not hammer the server with requests.
this.$panel.content.updateLazy({
  text: 'Changed text'
});

// This method does not provide a response.
// You can listen to the global `content.save`` event
// if you need to trigger code after changes have been saved.
this.$panel.events.on("content.save", ({ values }) => {
  // do something here whenever content has been saved
});</k-code>
		</k-lab-example>
	</k-lab-examples>
</template>
