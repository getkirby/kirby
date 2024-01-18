<template>
	<k-lab-examples>
		<k-lab-example label="Text">
			<k-tag>Foo</k-tag>
		</k-lab-example>
		<k-lab-example label="Removable">
			<k-tag :removable="true">Foo</k-tag>
		</k-lab-example>
		<k-lab-example label="Disabled">
			<k-tag :disabled="true" :removable="true">Foo</k-tag>
		</k-lab-example>
		<k-lab-example :flex="true" label="Icon">
			<k-tag :image="{ icon: 'heart' }" />
			<k-tag :image="{ icon: 'heart' }">Foo</k-tag>
			<k-tag :image="{ icon: 'heart' }" :removable="true">Foo</k-tag>
			<k-tag :image="{ icon: 'heart' }" :disabled="true" :removable="true">
				Foo
			</k-tag>
		</k-lab-example>
		<k-lab-example :flex="true" label="Image">
			<k-tag :image="{ src: 'https://picsum.photos/100/100/', crop: true }" />
			<k-tag :image="{ src: 'https://picsum.photos/100/100/', crop: true }">
				Foo
			</k-tag>
			<k-tag
				:image="{ src: 'https://picsum.photos/100/100/', crop: true }"
				:removable="true"
			>
				Foo
			</k-tag>
			<k-tag
				:disabled="true"
				:image="{ src: 'https://picsum.photos/100/100/', crop: true }"
				:removable="true"
			>
				Foo
			</k-tag>
		</k-lab-example>
	</k-lab-examples>
</template>
