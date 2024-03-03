<template>
	<k-lab-examples>
		<h2 class="h4">State</h2>
		<k-lab-example label="code" :code="false">
			<k-text>
				<p>
					<code>window.panel.translation.code</code> returns the current
					language code for the active translation
				</p>
				<k-code>{{ $panel.translation.code }}</k-code>
			</k-text>
		</k-lab-example>
		<k-lab-example label="date" :code="false">
			<k-text>
				<p>
					<code>window.panel.translation.data</code> returns the entire object
					with all messages and other infor about the translation
				</p>
				<k-code language="js">{{ $panel.translation.data }}</k-code>
			</k-text>
		</k-lab-example>
		<k-lab-example label="direction" :code="false">
			<k-text>
				<p>
					<code>window.panel.translation.direction</code> returns the reading
					direction (ltr or rtl)
				</p>
				<k-code>{{ $panel.translation.direction }}</k-code>
			</k-text>
		</k-lab-example>
		<k-lab-example label="name" :code="false">
			<k-text>
				<p>
					<code>window.panel.translation.name</code> returns the name of the
					translation
				</p>
				<k-code>{{ $panel.translation.name }}</k-code>
			</k-text>
		</k-lab-example>
	</k-lab-examples>
</template>
