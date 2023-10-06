<template>
	<k-lab-examples>
		<k-lab-example :flex="true" label="bold">
			<k-text>
				<strong>strong</strong>
			</k-text>
			<k-text>
				<b>bold</b>
			</k-text>
		</k-lab-example>
		<k-lab-example :flex="true" label="italic">
			<k-text>
				<em>em</em>
			</k-text>
			<k-text>
				<i>italic</i>
			</k-text>
		</k-lab-example>
		<k-lab-example :flex="true" label="link">
			<k-text>
				<a href="">link</a>
			</k-text>
		</k-lab-example>
		<k-lab-example :flex="true" label="underline">
			<k-text>
				<u>underline</u>
			</k-text>
		</k-lab-example>
		<k-lab-example :flex="true" label="deleted">
			<k-text>
				<del>deleted</del>
			</k-text>
		</k-lab-example>
		<k-lab-example :flex="true" label="code">
			<k-text>
				<code>code</code>
			</k-text>
		</k-lab-example>
		<k-lab-example :flex="true" label="kbd">
			<k-text>
				<kbd>cmd+v</kbd>
			</k-text>
		</k-lab-example>
		<k-lab-example :flex="true" label="abbr">
			<k-text>
				<abbr title="Abbreviation">abbr</abbr>
			</k-text>
		</k-lab-example>
		<k-lab-example :flex="true" label="mark">
			<k-text>
				<mark>mark</mark>
			</k-text>
		</k-lab-example>
		<k-lab-example :flex="true" label="sup">
			<k-text>Superscript<sup>sup</sup></k-text>
		</k-lab-example>
		<k-lab-example :flex="true" label="sub">
			<k-text>Subscript<sub>sub</sub></k-text>
		</k-lab-example>
	</k-lab-examples>
</template>
