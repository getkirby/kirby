<template>
	<k-lab-examples>
		<k-lab-example label="Slot">
			<k-tag>Foo</k-tag>
		</k-lab-example>
		<k-lab-example label="Text">
			<k-tag text="Foo" />
		</k-lab-example>
		<k-lab-example label="HTML">
			<k-tag :html="true" text="Tag with <i>HTML</i>" />
		</k-lab-example>
		<k-lab-example label="Removable">
			<k-tag :removable="true" text="Foo" />
		</k-lab-example>
		<k-lab-example label="Disabled">
			<k-tag :disabled="true" :removable="true" text="Foo" />
		</k-lab-example>
		<k-lab-example :flex="true" label="Icon">
			<k-tag :image="{ icon: 'heart' }" />
			<k-tag :image="{ icon: 'heart' }" text="Foo" />
			<k-tag :image="{ icon: 'heart' }" :removable="true" text="Foo" />
			<k-tag
				:disabled="true"
				:image="{ icon: 'heart' }"
				:removable="true"
				text="Foo"
			/>
		</k-lab-example>
		<k-lab-example :flex="true" label="Image">
			<k-tag :image="{ src: 'https://picsum.photos/100/100/', crop: true }" />
			<k-tag :image="{ src: 'https://picsum.photos/100/100/', crop: true }" text="Foo" />
			<k-tag
				:image="{ src: 'https://picsum.photos/100/100/', crop: true }"
				:removable="true"
				text="Foo"
			>
			<k-tag
				:disabled="true"
				:image="{ src: 'https://picsum.photos/100/100/', crop: true }"
				:removable="true"
				text="Foo"
			/>
		</k-lab-example>
	</k-lab-examples>
</template>
