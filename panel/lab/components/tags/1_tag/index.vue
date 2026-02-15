<template>
	<k-lab-examples>
		<k-toggles-input
			:labels="true"
			:options="[
				{ value: 'dark', text: 'Dark', icon: 'moon' },
				{ value: 'light', text: 'Light', icon: 'sun' }
			]"
			:value="theme"
			@input="(theme = $event)"
		/>

		<k-lab-example label="Slot">
			<k-tag :theme="theme">Foo</k-tag>
		</k-lab-example>
		<k-lab-example label="Text">
			<k-tag :theme="theme" text="Foo" />
		</k-lab-example>
		<k-lab-example label="HTML">
			<k-tag :html="true" :theme="theme" text="Tag with <i>HTML</i>" />
		</k-lab-example>
		<k-lab-example label="Removable">
			<k-tag :removable="true" :theme="theme" text="Foo" />
		</k-lab-example>
		<k-lab-example label="Disabled">
			<k-tag :disabled="true" :removable="true" :theme="theme" text="Foo" />
		</k-lab-example>
		<k-lab-example :flex="true" label="Icon">
			<k-tag :image="{ icon: 'heart' }" :theme="theme" />
			<k-tag :image="{ icon: 'heart' }" :theme="theme" text="Foo" />
			<k-tag
				:image="{ icon: 'heart' }"
				:removable="true"
				:theme="theme"
				text="Foo"
			/>
			<k-tag
				:disabled="true"
				:image="{ icon: 'heart' }"
				:removable="true"
				:theme="theme"
				text="Foo"
			/>
		</k-lab-example>
		<k-lab-example :flex="true" label="Image">
			<k-tag
				:image="{ src: 'https://picsum.photos/100/100/', crop: true }"
				:theme="theme"
			/>
			<k-tag
				:image="{ src: 'https://picsum.photos/100/100/', crop: true }"
				:theme="theme"
				text="Foo"
			/>
			<k-tag
				:image="{ src: 'https://picsum.photos/100/100/', crop: true }"
				:removable="true"
				:theme="theme"
				text="Foo"
			/>
			<k-tag
				:disabled="true"
				:image="{ src: 'https://picsum.photos/100/100/', crop: true }"
				:removable="true"
				:theme="theme"
				text="Foo"
			/>
		</k-lab-example>
		<k-lab-example :flex="true" label="Image & Link">
			<k-tag
				link="https://getkirby.com"
				:image="{ src: 'https://picsum.photos/100/100/', crop: true }"
				:theme="theme"
			/>
			<k-tag
				link="https://getkirby.com"
				:image="{ src: 'https://picsum.photos/100/100/', crop: true }"
				:theme="theme"
				text="Foo"
			/>
			<k-tag
				link="https://getkirby.com"
				:image="{ src: 'https://picsum.photos/100/100/', crop: true }"
				:removable="true"
				:theme="theme"
				text="Foo"
			/>
			<k-tag
				link="https://getkirby.com"
				:disabled="true"
				:image="{ src: 'https://picsum.photos/100/100/', crop: true }"
				:removable="true"
				:theme="theme"
				text="Foo"
			/>
		</k-lab-example>
		<k-lab-example label="Too long">
			<k-tag
				:removable="true"
				:theme="theme"
				text="Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas sed diam eget risus varius blandit sit amet non magna. Vivamus sagittis lacus vel augue laoreet rutrum faucibus dolor auctor."
			/>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	data() {
		return {
			theme: "dark"
		};
	}
};
</script>

<style>
.k-lab-examples .k-toggles-input {
	margin-bottom: var(--spacing-12);
}
.k-lab-example-canvas {
	background: white;
}
</style>
