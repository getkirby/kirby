<template>
	<k-lab-examples>
		<h2 class="h4">State</h2>

		<k-box v-if="$panel.languages.length === 0" theme="warning">
			The state is going to be pretty empty in single-language mode
		</k-box>

		<k-lab-example label="code" :code="false">
			<k-text>
				<p>
					<code>window.panel.language.code</code> returns the code of the
					currently active language
				</p>
				<k-code>{{ $panel.language.code || "&nbsp;" }}</k-code>
			</k-text>
		</k-lab-example>
		<k-lab-example label="direction" :code="false">
			<k-text>
				<p>
					<code>window.panel.language.direction</code> Returns the reading
					direction of the current language
				</p>
				<k-code>{{ $panel.language.direction || "&nbsp;" }}</k-code>
			</k-text>
		</k-lab-example>
		<k-lab-example label="isDefault" :code="false">
			<k-text>
				<p>
					<code>window.panel.language.isDefault</code> True if the current
					language is the default language
				</p>
				<k-code>{{ $panel.language.isDefault || "&nbsp;" }}</k-code>
			</k-text>
		</k-lab-example>
		<k-lab-example label="name" :code="false">
			<k-text>
				<p>
					<code>window.panel.language.name</code> Returns the name of the
					current language
				</p>
				<k-code>{{ $panel.language.name || "&nbsp;" }}</k-code>
			</k-text>
		</k-lab-example>
		<k-lab-example label="rules" :code="false">
			<k-text>
				<p>
					<code>window.panel.language.rules</code> Returns the slug rules for
					the current language
				</p>
				<k-code>{{ $panel.language.rules || "&nbsp;" }}</k-code>
			</k-text>
		</k-lab-example>
	</k-lab-examples>
</template>
