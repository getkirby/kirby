<template>
	<k-lab-examples>
		<k-box theme="text">
			<k-text>
				<p>
					Kirby ships with <code>dayjs</code> bundled: a minimalist JavaScript
					library that parses, validates, manipulates, and displays dates and
					times.
				</p>

				<p>
					In addition to exposing all the
					<a href="https://day.js.org/">core functionality</a>, Kirby also
					provides some methods to parse/interpret, format and validate dates
					and times.
				</p>
			</k-text>
		</k-box>
		<k-lab-example label="dayjs" :code="false">
			<k-code language="javascript">this.$library.dayjs() // now</k-code>
		</k-lab-example>
	</k-lab-examples>
</template>
